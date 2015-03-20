################################################################################
# Copyright 2005-2013 CENTREON
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give CENTREON 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of CENTREON choice, provided that 
# CENTREON also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

package centreon::script::centcore;

use strict;
use File::Copy;
use File::Path qw(mkpath);
use centreon::script;
use centreon::common::db;
use centreon::common::misc;

use base qw(centreon::script);

my %handlers = ('TERM' => {}, 'HUP' => {}, 'DIE' => {});
use vars qw($centreon_config);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centcore",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
        noroot => 1
    );

    bless $self, $class;

    $self->{echo} = "echo";
    $self->{ssh} = "ssh";
    $self->{scp} = "scp";
    $self->{rsync} = "rsync";
    $self->{rsyncWT} = $self->{rsync};
    $self->{sudo} = "sudo";
    $self->{timeout} = 5;
    $self->{cmd_timeout} = 5; 
    
    $self->{ssh} .= " -o ConnectTimeout=$self->{timeout} -o StrictHostKeyChecking=yes -o PreferredAuthentications=publickey -o ServerAliveInterval=10 -o ServerAliveCountMax=3 -o Compression=yes ";
    $self->{rsync} .= " --timeout=$self->{timeout} ";
    $self->{scp} .= " -o ConnectTimeout=$self->{timeout} -o StrictHostKeyChecking=yes -o PreferredAuthentications=publickey -o ServerAliveInterval=10 -o ServerAliveCountMax=3 -o Compression=yes ";

    
    $self->{timeBetween2SyncPerf} = 60;
    $self->{perfdataSync} = 0;
    $self->{logSync} = 0;
    $self->{enable_broker_stats} = 0;
    $self->{stop} = 1;
    $self->{reload} = 1;

    $self->{timeSyncPerf} = 0;
    $self->{difTime} = 10;
    
    %{$self->{commandBuffer}} = ();
    
    $self->set_signal_handlers;

    return $self;
}

sub init {
    my $self = shift;
    $self->SUPER::init();

    $self->{cmdFile} = $self->{centreon_config}->{VarLib} . "/centcore.cmd";
    $self->{cmdDir} = $self->{centreon_config}->{VarLib} . "/centcore/";
    $self->{centreonDir} = $self->{centreon_config}->{CentreonDir};
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{TERM}->{$self} = sub { $self->handle_TERM() };

    $SIG{__DIE__} = \&class_handle_DIE;
    $handlers{DIE}->{$self} = sub { $self->handle_DIE($_[0]) };

    $SIG{HUP} = \&class_handle_HUP;
    $handlers{HUP}->{$self} = sub { $self->handle_HUP() };
}

sub class_handle_TERM {
    foreach (keys %{$handlers{TERM}}) {
        &{$handlers{TERM}->{$_}}();
    }
}

sub class_handle_DIE {
    my ($msg) = @_;

    foreach (keys %{$handlers{DIE}}) {
        &{$handlers{DIE}->{$_}}($msg);
    }
}

sub class_handle_HUP {
    foreach (keys %{$handlers{HUP}}) {
        &{$handlers{HUP}->{$_}}();
    }
}

sub handle_HUP {
    my $self = shift;

    $self->{logger}->writeLogInfo("Receiving order to reload...");
    $self->{reload} = 0;
}

sub handle_TERM {
    my $self = shift;
    $self->{logger}->writeLogInfo("$$ Receiving order to stop...");
    $self->{stop} = 0;
}

sub handle_DIE {
    my $self = shift;
    my $msg = shift;

    $self->{logger}->writeLogInfo("Receiving die: $msg");
    $self->{logger}->writeLogInfo("Dont die...");
}

sub reload {
    my $self = shift;
    
    if (defined($self->{log_file})) {
        $self->{logger}->file_mode($self->{log_file});
    }
    $self->{logger}->redirect_output();
    
    # Get Config
    unless (my $return = do $self->{config_file}) {
        $self->{logger}->writeLogError("couldn't parse $self->{config_file}: $@") if $@;
        $self->{logger}->writeLogError("couldn't do $self->{config_file}: $!") unless defined $return;
        $self->{logger}->writeLogError("couldn't run $self->{config_file}") unless $return;
    } else {
        $self->{centreon_config} = $centreon_config;
    }
    
    if ($self->{centreon_config}->{centreon_db} ne $self->{centreon_dbc}->db() ||
        $self->{centreon_config}->{db_host} ne $self->{centreon_dbc}->host() ||
        $self->{centreon_config}->{db_user} ne $self->{centreon_dbc}->user() ||
        $self->{centreon_config}->{db_passwd} ne $self->{centreon_dbc}->password() ||
        $self->{centreon_config}->{db_port} ne $self->{centreon_dbc}->port()) {
        $self->{logger}->writeLogInfo("Database config had been modified");
        $self->{centreon_dbc}->disconnect();
        $self->{centreon_dbc}->db($self->{centreon_config}->{centreon_db});
        $self->{centreon_dbc}->host($self->{centreon_config}->{db_host});
        $self->{centreon_dbc}->user($self->{centreon_config}->{db_user});
        $self->{centreon_dbc}->password($self->{centreon_config}->{db_passwd});
        $self->{centreon_dbc}->port($self->{centreon_config}->{db_port});
    }
}

###########################################################
# Function to move command file on temporary file
#
sub moveCmdFile($){
    my $self = shift;
    my $cmdfile = $_[0];

    if (move($cmdfile, $cmdfile."_read")) {
        return(1);
    } else {
        $self->{logger}->writeLogError("Cannot move $cmdfile to ".$cmdfile."_read");
        return(0);
    }
}

# -------------------
#      Functions 
# -------------------

sub getNagiosConfigurationField($$){
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT " . $_[1] . " FROM `cfg_nagios` WHERE `nagios_server_id` = '" . $_[0] . "' AND nagios_activate = '1'");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return undef;
    }
    my $data = $sth->fetchrow_hashref();
    return $data->{$_[1]};
}

sub getLocalServerID(){
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `id` FROM `nagios_server` WHERE `localhost` = '1' ORDER BY activate DESC LIMIT 1");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return undef;
    }
    my $id = $sth->fetchrow_hashref();
    return $id->{'id'};
}

sub getServerConfig($){
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT * FROM `nagios_server` WHERE `id` = '" . $_[0] . "' AND `activate` = '1' LIMIT 1");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return undef;
    }
    my $data = $sth->fetchrow_hashref();

    # Get Nagios User
    $data->{'nagios_user'} = $self->getNagiosConfigurationField($_[0], 'nagios_user');
    return $data;
}

##################################
## Check SSH Port Value
#
sub checkSSHPort($) {
    my ($value) = @_;
    my $port;

    if (defined($value) && $value) {
        $port = $value;
    } else {
        $port = 22;
    }
    return $port;
}

################################################
## Send an external command on a remote server.
## Param : id_remote_server, external command
#
sub sendExternalCommand($$) {
    my $self = shift;
    # Init Parameters
    my ($id, $cmd) = @_;
    my ($lerror, $stdout, $cmd2);

    # Get server informations
    my $server_info = $self->getServerConfig($id);
    my $port = checkSSHPort($server_info->{'ssh_port'});

    # Get command file 
    my $command_file = $self->getNagiosConfigurationField($id, "command_file");

    # check if ip address is defined
    if (defined($server_info->{'ip_address'})) {
        $cmd =~ s/\\/\\\\/g;
        if ($server_info->{'localhost'} == 1) {
            my $result = waitPipe($command_file);
            if ($result == 0) {
                $self->{logger}->writeLogInfo("External command on Central Server: ($id) : \"".$cmd."\"");

                $cmd2 = "$self->{echo} \"".$cmd."\" >> ".$command_file;
                ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd2,
                                                                      logger => $self->{logger},
                                                                      timeout => $self->{cmd_timeout}
                    );
                if ($lerror == -1) {
                    $self->{logger}->writeLogError("Could not write into pipe file ".$command_file." on poller ".$id);
                }
            } else {
                $self->{logger}->writeLogError("Cannot write external command on central server : \"".$cmd."\"");
            }
        } else {
            $cmd =~ s/\'/\'\\\'\'/g;
            $self->{logger}->writeLogInfo("External command : ".$server_info->{'ip_address'}." ($id) : \"".$cmd."\"");
            $cmd2 = "$self->{ssh} -q ". $server_info->{'ip_address'} ." -p $port \"$self->{echo} '".$cmd."' >> ".$command_file."\"";
            ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd2,
                                                                  logger => $self->{logger},
                                                                  timeout => $self->{cmd_timeout}
                );
            if ($lerror == -1) {
                $self->{logger}->writeLogError("Could not write into pipe file ".$command_file." on poller ".$id);
            }
        }

        if (defined($stdout) && $stdout){
            $self->{logger}->writeLogInfo("Result : $stdout");
        }

    } else {
        $self->{logger}->writeLogError("Ip address not defined for poller $id");
    }
}

#######################################
## Wait Nagios Pipe availability
#
sub waitPipe($) {
    my ($pipe) = @_;
    my $i = 0;
    while (! -p $pipe) {
        sleep(1);
        $i++;
        if ($i >= 30) {
            return 1;
        }
    }
    return 0;
}

##################################################
# Send config files to a remote server 
#
sub sendConfigFile($){
    my $self = shift;
    # Init Values
    my $id = $_[0];
    my ($lerror, $stdout, $cmd);

    my $cfg_dir = $self->getNagiosConfigurationField($id, "cfg_dir");
    my $server_info = $self->getServerConfig($id);
    my $port = checkSSHPort($server_info->{'ssh_port'});

    if (!defined($cfg_dir) || $cfg_dir =~ //) {
        $self->{logger}->writeLogError("Engine configuration file is empty for poller $id. Please check nagios.cfg file.");
        return;
    }

    my $origin = $self->{centreonDir} . "/filesGeneration/nagiosCFG/".$id."/*";
    my $dest = $server_info->{'ip_address'}.":$cfg_dir";

    # Send data with SCP
    $self->{logger}->writeLogInfo("Start: Send config files on poller $id");
    $cmd = "$self->{scp} -P $port $origin $dest 2>&1";
    
    ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                  logger => $self->{logger},
                                                                  timeout => 300
                                                                  );
    
    $self->{logger}->writeLogInfo("Result : $stdout");
    $self->{logger}->writeLogInfo("End: Send config files on poller $id");

    # Send configuration for Centreon Broker
    if ( -e $self->{centreonDir}  . "/filesGeneration/broker/".$id) {
        # Check availability of broker files.
        my $count = 0;
        opendir(my $dh, $self->{centreonDir} . "/filesGeneration/broker/".$id);
        while(readdir $dh) {
            $count++;
        }
        closedir $dh;

        if ($count > 2) {
            $self->{logger}->writeLogDebug("Start: Send Centreon Broker config files on poller $id");

            if ($server_info->{'localhost'} == 0) {
                $cfg_dir = $server_info->{'centreonbroker_cfg_path'};
                $origin = $self->{centreonDir} . "/filesGeneration/broker/".$id."/*.xml";
                $dest = $server_info->{'ip_address'}.":$cfg_dir";
                $cmd = "$self->{scp} -P $port $origin $dest 2>&1";
                ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                      logger => $self->{logger},
                                                                      timeout => 300
                                                                      );
                $self->{logger}->writeLogInfo("Result : $stdout");
            } else {
                $cmd = "cp $origin $cfg_dir 2>&1";
                ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                      logger => $self->{logger},
                                                                      timeout => 60
                                                                      );
                $self->{logger}->writeLogInfo("Result : $stdout");
            }
            $self->{logger}->writeLogDebug("End: Send Centreon Broker config files on poller $id");
        }
    }
}

##################################################
# Function for initialize Nagios :
# Parameters :
#   - start
#   - restart
#   - stop
#
sub initEngine($$){
    my $self = shift;
    my $id = $_[0];
    my $options = $_[1];
    my ($lerror, $cmd, $stdout);

    # Get configuration
    my $conf = $self->getServerConfig($id);
    my $port = checkSSHPort($conf->{'ssh_port'});

    if (!defined($conf)) {
        $self->{logger}->writeLogError("Poller $id doesn't exists...");
        $self->{logger}->writeLogError("Cannot manage undefined poller...");
        return ;
    }

    if (defined($conf->{'ip_address'}) && $conf->{'ip_address'}) {
        # Launch command
        $cmd = "$self->{ssh} -p $port ". $conf->{'ip_address'} ." $self->{sudo} ".$conf->{'init_script'}." ".$options;
        ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => 120
                                                              );
    } else {
        $self->{logger}->writeLogError("Cannot $options Engine for poller $id");
    }

    # Logs Actions
    $self->{logger}->writeLogInfo("Init Script : '$self->{sudo} ".$conf->{'init_script'}." ".$options."' On poller ".$conf->{'ip_address'}." ($id)");
    my $line;
    if (defined($stdout)) {
        foreach $line (split(/\n/, $stdout)){
            $self->{logger}->writeLogDebug("Engine : ".$line);
        }
    }
}

##################################################
# Function for synchronize SNMP trap configuration
# 
sub syncTraps($) {
    my $self = shift;
    my $id = $_[0];
    my ($lerror, $stdout, $cmd);

    if ($id != 0) {
        # Get configuration
        my $ns_server = $self->getServerConfig($id);
        my $port = checkSSHPort($ns_server->{'ssh_port'});

        if ($id != 0 && $ns_server->{'localhost'} == 0) {
            $cmd = "$self->{scp} -P $port /etc/snmp/centreon_traps/$id/centreontrapd.sdb $ns_server->{'ip_address'}:/etc/snmp/centreon_traps/ 2>&1";
            $self->{logger}->writeLogDebug($cmd);
            
            ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                  logger => $self->{logger},
                                                                  timeout => 300
                                                                  );
            if (defined($stdout) && $stdout){
                $self->{logger}->writeLogInfo("Result : $stdout");
            }
        }
    } else {
        # synchronize Archives for all pollers
        my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `id`, `snmp_trapd_path_conf` FROM `nagios_server` WHERE `activate` = '1' AND `localhost` = '0'");
        return if ($status == -1);
        while (my $server = $sth->fetchrow_hashref()) {
            # Get configuration
            my $ns_server = $self->getServerConfig($server->{'id'});
            my $port = checkSSHPort($ns_server->{'ssh_port'});

            if ($id == 0) {
                $cmd = "$self->{scp} -P $port /etc/snmp/centreon_traps/$id/centreontrapd.sdb $ns_server->{'ip_address'}:$ns_server->{'snmp_trapd_path_conf'} 2>&1";
                $self->{logger}->writeLogDebug($cmd);
                ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                      logger => $self->{logger},
                                                                      timeout => 300
                                                                      );
                if (defined($stdout) && $stdout){
                    $self->{logger}->writeLogInfo("Result : $stdout");
                }
            }
        }
    }
}

###################################
## Sync engine Logs Archives in the 
## central Server 
#
sub syncArchives($) {
    my $self = shift;
    my $id = $_[0];
    my ($lerror, $stdout, $cmd);

    # Get configuration
    my $ns_server = $self->getServerConfig($id);
    my $port = checkSSHPort($ns_server->{'ssh_port'});

    if ($id != 0) {
        $self->{logger}->writeLogInfo("Begin synchronize all archives of poller " . $id);
        # Sync Archive for one poller
        if (! -d $self->{centreon_config}->{VarLib} . "/log/".$ns_server->{'id'}."/archives/") {
            if (! -d $self->{centreon_config}->{VarLib} . "/log/".$ns_server->{'id'}."/archives/") {
                mkpath $self->{centreon_config}->{VarLib} . "/log/".$ns_server->{'id'}."/archives/";
            }
        }
        my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `log_archive_path` FROM `cfg_nagios` WHERE `nagios_server_id` = '".$id."' AND `nagios_activate` = '1'");
        if ($status == -1) {
            $self->{logger}->writeLogError("Can't get archive path for poller ".$ns_server->{'id'}." (".$ns_server->{'ns_address_ip'}.")");
            return ;
        }
        my $data = $sth->fetchrow_hashref();
        # Archive Sync
        $cmd = "$self->{rsyncWT} --port=$port -c ". $ns_server->{'ip_address'}. ":".$data->{'log_archive_path'}."/*.log $self->{centreon_config}->{VarLib}/log/".$ns_server->{'id'}."/archives/ 2>> /dev/null";
        $self->{logger}->writeLogDebug($cmd);
        ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => 300
            );
    } else {
        # synchronize Archives for all pollers
        my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `id` FROM `nagios_server` WHERE `activate` = '1' AND `localhost` = '0'");
        if ($status == -1) {
            return ;
        }
        $self->{logger}->writeLogInfo("Receive Order to synchronize all archives of all pollers");
        while (my $server = $sth->fetchrow_hashref()) {
            $self->syncArchives($server->{'id'});
        }
    }
}

##################################
## Get Monitoring Engine.
#
sub getInfos($) {
    my $self = shift;
    my $id = $_[0];
    my ($lerror, $stdout, $cmd);

    # Get configuration
    my $ns_server = $self->getServerConfig($id);
    my $port = checkSSHPort($ns_server->{'ssh_port'});

    if (defined($ns_server->{'ip_address'}) && $ns_server->{'ip_address'}) {
        # Launch command
        if (defined($ns_server->{'localhost'}) && $ns_server->{'localhost'}) {
            $cmd = "$self->{sudo} ".$ns_server->{'nagios_bin'};
            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => 60
                                                              );
        } else {
            $cmd = "$self->{ssh} -p $port ". $ns_server->{'ip_address'} ." ".$ns_server->{'nagios_bin'};
            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => 60
                                                              );
        }
        my @tab = split("\n", $stdout);
        foreach my $str (@tab) {
            if ($str =~ m/(Nagios) Core ([\.0-9]*[a-zA-Z0-9\-\.]+)/) {
                $self->{logger}->writeLogInfo("Engine: $1");
                $self->{logger}->writeLogInfo("Version: $2");
                $self->updateEngineInformation($id, $1, $2);
                last;
            }
            if ($str =~ m/(Centreon Engine) ([\.0-9]*[a-zA-Z0-9\-\.]+)/) {
                $self->{logger}->writeLogInfo("Engine: $1");
                $self->{logger}->writeLogInfo("Version: $2");
                $self->updateEngineInformation($id, $1, $2);
                last;
            } 
        }
    } else {
        $self->{logger}->writeLogError("Cannot get informations for poller $id");
    }
}

###############################
## Update Engine informations
#
sub updateEngineInformation($$$) {
    my $self = shift;
    my $id = $_[0];
    my $engine_name = $_[1]; 
    my $engine_version = $_[2];
    
    $self->{centreon_dbc}->query("UPDATE `nagios_server` SET `engine_name` = '$engine_name', `engine_version` = '$engine_version' WHERE `id` = '$id'");    
}

################################
## Reload CentreonTrapd Daemon
#
sub reloadCentreonTrapd($) {
    my $self = shift;
    my $id = $_[0];
    my ($lerror, $stdout, $cmd);

    # Get configuration
    my $ns_server = $self->getServerConfig($id);
    my $port = checkSSHPort($ns_server->{'ssh_port'});

    if (defined($ns_server->{'ip_address'}) && $ns_server->{'ip_address'}
        && defined($ns_server->{'init_script_centreontrapd'}) && $ns_server->{'init_script_centreontrapd'} ne "") {
        # Launch command
        if (defined($ns_server->{'localhost'}) && $ns_server->{'localhost'}) {
            $cmd = "$self->{sudo} ".$ns_server->{'init_script_centreontrapd'}." restart";
            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                  logger => $self->{logger},
                                                                  timeout => 120
                                                                  );
        } else {
            $cmd = "$self->{ssh} -p $port ". $ns_server->{'ip_address'} ." $self->{sudo} ".$ns_server->{'init_script_centreontrapd'}." reload";
            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                  logger => $self->{logger},
                                                                  timeout => 60
                                                                  );
        }
        $self->{logger}->writeLogInfo("Reload CentreonTrapd on poller $id ($ns_server->{'ip_address'})");
    } else {
        $self->{logger}->writeLogError("Cannot reload CentreonTrapd for poller $id");
    }
}

####################################
## Parse request
#
sub parseRequest($){
    my $self = shift;
    my ($action) = @_;

    if (!$action) {
        return ;
    }
    
    # Checks keys for launching commands 
    if ($action =~ /^RESTART\:([0-9]*)/){
        $self->initEngine($1, "restart");
    } elsif ($action =~ /^RELOAD\:([0-9]*)/){
        $self->initEngine($1, "reload");
    } elsif ($action =~ /^FORCERELOAD\:([0-9]*)/){
        $self->initEngine($1, "force-reload");
    } elsif ($action =~ /^START\:([0-9]*)/){
        $self->initEngine($1, "start");
    } elsif ($action =~ /^STOP\:([0-9]*)/){
        $self->initEngine($1, "stop");
    } elsif ($action =~ /^SENDCFGFILE\:([0-9]*)/){
        $self->sendConfigFile($1);
    } elsif ($action =~ /^SYNCTRAP\:([0-9]*)/){
        $self->syncTraps($1);
    } elsif ($action =~ /^RELOADCENTREONTRAPD\:([0-9]*)/){
        $self->reloadCentreonTrapd($1);
    } elsif ($action =~ /^SYNCARCHIVES\:([0-9]*)/){
        $self->syncArchives($1);
    } elsif ($action =~ /^EXTERNALCMD\:([0-9]*)\:(.*)/){
        $self->storeCommands($1, $2);
    } elsif ($action =~ /^GETINFOS\:([0-9]*)/){
        $self->getInfos($1);
    }
}

############################################
## Check Centcore Configuration Profile
#
sub checkProfile() {
    my $self = shift;
    
    my $request = "SELECT * FROM options WHERE `key` IN ('centcore_cmd_timeout')";
    my ($status, $sth) =  $self->{centreon_dbc}->query($request);
    return -1 if ($status == -1);
    while ((my $data = $sth->fetchrow_hashref())) {
        if (defined($data->{'key'}) && $data->{'key'} ne "" && defined($data->{'value'}) && $data->{'value'} ne "") {
            if ($data->{'key'} eq "centcore_cmd_timeout") {
                $self->{cmd_timeout} = $data->{'value'};
            }
        }
    }
    return 0;
}

# Check if debug has been enable into GUI
sub checkDebugFlag {
    my $self = shift;

    my $request = "SELECT value FROM options WHERE `key` IN ('debug_centcore')";
    my ($status, $sth) =  $self->{centreon_dbc}->query($request);
    return -1 if ($status == -1);
    my $data = $sth->fetchrow_hashref();
    if (defined($data->{'value'}) && $data->{'value'} == 1) {
        if (!$self->{logger}->is_debug()) {
            $self->{logger}->severity("debug");
            $self->{logger}->writeLogInfo("Enable Debug in Centcore");
        }
    } else {
        if ($self->{logger}->is_debug()) {
            $self->{logger}->set_default_severity();
            $self->{logger}->writeLogInfo("Disable Debug in Centcore. Set default severity");
        }
    }
    return 0;
}

# Store commands in order to group commands to send.
sub storeCommands($$) {
    my $self = shift;
    my ($poller_id, $command) = @_;
    
    if (!defined($self->{commandBuffer}{$poller_id})) {
        $self->{commandBuffer}{$poller_id} = "";
    }
    $self->{commandBuffer}{$poller_id} .= $command . "\n";
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->{logger}->redirect_output();
    $self->{logger}->writeLogInfo("Starting centcore engine...");

    $self->{centreon_dbc} = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                                      host => $self->{centreon_config}->{db_host},
                                                      port => $self->{centreon_config}->{db_port},
                                                      user => $self->{centreon_config}->{db_user},
                                                      password => $self->{centreon_config}->{db_passwd},
                                                      force => 0,
                                                      logger => $self->{logger});
    $self->checkDebugFlag();

    while ($self->{stop}) {
        if ($self->{reload} == 0) {
            $self->{logger}->writeLogInfo("Reload in progress...");
            $self->reload();
            $self->{reload} = 1;
        }
        # Read Centcore.cmd
        if (-e $self->{cmdFile}) {
            if ($self->moveCmdFile($self->{cmdFile}) && open(FILE, "< $self->{cmdFile}"."_read")) {
                while (<FILE>){
                    $self->parseRequest($_);
                }
                my $poller;
                foreach $poller (keys(%{$self->{commandBuffer}})) {
                    if (length($self->{commandBuffer}{$poller}) != 0) {
                        $self->sendExternalCommand($poller, $self->{commandBuffer}{$poller});
                        $self->{commandBuffer}{$poller} = "";
                    }
                }
                close(FILE);
                $self->{logger}->writeLogError("Error When removing ".$self->{cmdFile}."_read file : $!") if (!unlink($self->{cmdFile}."_read"));
            }
        }

        # Read Centcore Directory
        if (-d $self->{cmdDir}) {
            opendir(my $dh, $self->{cmdDir});
            while (my $file = readdir($dh)) {
                if ($file ne "." && $file ne ".." && $file ne "") {
                    if ($self->moveCmdFile($self->{cmdDir} . $file) && open(FILE, "< ". $self->{cmdDir} . $file . "_read")) {
                        while (<FILE>){
                            $self->parseRequest($_);
                        }
                        my $poller;
                        foreach $poller (keys(%{$self->{commandBuffer}})) {
                            if (length($self->{commandBuffer}{$poller}) != 0) {
                                $self->sendExternalCommand($poller, $self->{commandBuffer}{$poller});
                                $self->{commandBuffer}{$poller} = "";
                            }
                        }
                        close(FILE);
                        $self->{logger}->writeLogError("Error When removing ".$self->{cmdDir}.$file."_read file : $!") if (!unlink($self->{cmdDir}.$file."_read"));
                    }
                }
            }
            closedir $dh;
        }

        if (defined($self->{timeSyncPerf}) && $self->{timeSyncPerf}) {
            $self->{difTime} = time() - $self->{timeSyncPerf};
        }

        # Get PerfData on Nagios Poller
        if ((defined($self->{difTime}) && $self->{timeBetween2SyncPerf} <= $self->{difTime}) || $self->{timeSyncPerf} == 0){
            # Check Activity profile Status
            $self->checkProfile();

            # Check debug Flag
            $self->checkDebugFlag();

            $self->{timeSyncPerf} = time();
        }

        sleep(1);
    }

    $self->{logger}->writeLogInfo("Centcore stop...");
}

1;

__END__


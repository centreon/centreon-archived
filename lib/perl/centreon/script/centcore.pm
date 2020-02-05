################################################################################
# Copyright 2005-2019 Centreon
# Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
# As a special exception, the copyright holders of this program give Centreon 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of Centreon choice, provided that 
# Centreon also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
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
    $self->{service} = "service";
    $self->{engineInitScript} = 'centengine';
    $self->{timeout} = 5;
    $self->{cmd_timeout} = 5;
    $self->{illegal_characters} = "";

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

    if (!defined $self->{centreon_config}->{VarLib} || $self->{centreon_config}->{VarLib} eq '') {
        $self->{centreon_config}->{VarLib} = '/var/lib/centreon';
    }
    if (!defined $self->{centreon_config}->{CentreonDir} || $self->{centreon_config}->{CentreonDir} eq '') {
        $self->{centreon_config}->{CentreonDir} = '/usr/share/centreon';
    }
    if (!defined $self->{centreon_config}->{CacheDir} || $self->{centreon_config}->{CacheDir} eq '') {
        $self->{centreon_config}->{CacheDir} = '/var/cache/centreon';
    }
    $self->{cmdFile} = $self->{centreon_config}->{VarLib} . "/centcore.cmd";
    $self->{cmdDir} = $self->{centreon_config}->{VarLib} . "/centcore/";
    $self->{centreonDir} = $self->{centreon_config}->{CentreonDir};
    $self->{cacheDir} = $self->{centreon_config}->{CacheDir};
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

############################################
## Get all broker statistics
#
sub getAllBrokerStats {
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query(
        "SELECT `id` FROM `nagios_server` WHERE `localhost` = '0' AND `ns_activate` = '1'"
    );
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return -1;
    }
    while (my $data = $sth->fetchrow_hashref()) {
        if (!$self->{stop}) {
            return ;
        }
        if ($self->{enable_broker_stats} == 1) {
            $self->getBrokerStats($data->{id});
        }
    }
    return 0;
}

###########################################
## Get a instant copy of the broker stat 
## fifo
#
sub getBrokerStats($) {
    my $self = shift;
    my ($poller_id) = @_;
    my $port = "";
    my $statPipe = "/tmp/.centreon-broker-stats.dat";
    my $destFile = $self->{centreon_config}->{VarLib} . "/broker-stats";
    my $server_info;
    my ($lerror, $stdout, $cmd);

    # Check Cache directory
    if (!-d $destFile) {
        $self->{logger}->writeLogInfo("Create data directory for broker-stats: $destFile");
        mkpath($destFile);
    }

    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT config_name, cache_directory "
        . "FROM cfg_centreonbroker "
        . "WHERE stats_activate='1' "
        . "AND ns_nagios_server = '" . $poller_id . "'");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error poller broker pipe");
        return -1;
    }
    while (my $data = $sth->fetchrow_hashref()) {

        # Get poller Configuration
        $server_info = $self->getServerConfig($poller_id);
        $port = checkSSHPort($server_info->{ssh_port});

        # Copy the stat file into a buffer
        my $statistics_file = $data->{cache_directory} . "/" . $data->{config_name} . "-stats.json";
        $cmd = "$self->{ssh} -q $server_info->{ns_ip_address} -p $port 'cat \"" . $statistics_file . "\" > $statPipe'";
        ($lerror, $stdout) = centreon::common::misc::backtick(
            command => $cmd,
            logger => $self->{logger},
            timeout => $self->{cmd_timeout}
        );
        if ($lerror == -1) {
            $self->{logger}->writeLogError(
                "Could not read pipe " . $statistics_file . " on poller ".$server_info->{ns_ip_address}
            );
        }
        if (defined($stdout) && $stdout) {
            $self->{logger}->writeLogInfo("Result : $stdout");
        }

        $cmd = "$self->{scp} -P $port $server_info->{ns_ip_address}:$statPipe "
            . "$destFile/broker-stats-$poller_id.dat >> /dev/null";
        # Get the stats file
        ($lerror, $stdout) = centreon::common::misc::backtick(
            command => $cmd,
            logger => $self->{logger},
            timeout => $self->{cmd_timeout}
        );
        if ($lerror == -1 && defined($stdout) && $stdout) {
            $self->{logger}->writeLogError("Result : $stdout");
        }
    }
    return 0;
}

# -------------------
#      Functions
# -------------------

sub getEngineConfigurationField($$) {
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query(
        "SELECT " . $_[1] . " FROM `cfg_nagios` WHERE `nagios_server_id` = '" . $_[0] . "' AND nagios_activate = '1'"
    );
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return undef;
    }
    my $data = $sth->fetchrow_hashref();
    return $data->{$_[1]};
}

sub getLocalServerID(){
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query(
        "SELECT `id` FROM `nagios_server` WHERE `localhost` = '1' ORDER BY ns_activate DESC LIMIT 1"
    );
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return undef;
    }
    my $id = $sth->fetchrow_hashref();
    return $id->{'id'};
}

sub getServerConfig($){
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query(
        "SELECT * FROM `nagios_server` WHERE `id` = '" . $_[0] . "' AND `ns_activate` = '1' LIMIT 1"
    );
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return undef;
    }
    my $data = $sth->fetchrow_hashref();

    # Get Engine User
    $data->{'nagios_user'} = $self->getEngineConfigurationField($_[0], 'nagios_user');

    my ($status, $sth) = $self->{centreon_dbc}->query(
        "SELECT remote_server_id FROM `rs_poller_relation` WHERE `poller_server_id` = '" . $_[0] . "'"
    );
    if ($status != -1) {
        $data->{'additonal_remotes'} = $sth->fetchrow_hashref();
    }
    return $data;
}

##################################
## Run Import / Export Worker
#
sub startWorker($) {

    my $self = shift;
    my ($lerror, $stdout, $cmd_line);
    my ($status, $sth) = $self->{centreon_dbc}->query(
        "SELECT * FROM `contact` WHERE `contact_admin` = '1' AND `contact_activate` = '1' LIMIT 1"
    );
    if ($status == -1) {
        $self->{logger}->writeLogError("Error selecting admin from db for starting worker");
        return undef;
    }

    my $data = $sth->fetchrow_hashref();
    my $username = $data->{'contact_alias'};
    my $passwordEnc = $data->{'contact_passwd'};
    # parse md5 password
    if ($passwordEnc =~ m/^md5__(.*)/) {
        $passwordEnc = $1;
    }

    my $cmdexec = "$self->{centreonDir}/bin/centreon -u %s -p %s -w -o CentreonWorker -a processQueue";
    $self->{logger}->writeLogDebug("cmd: " . sprintf($cmdexec, '<admin_user>', '<admin_md5_password>'));
    ($lerror, $stdout) = centreon::common::misc::backtick(
        command => sprintf($cmdexec, $username, $passwordEnc),
        logger => $self->{logger},
        timeout => $self->{cmd_timeout}
    );
    if (defined($stdout) && $stdout) {
        if ($lerror == -1) {
            $self->{logger}->writeLogError("Result : $stdout");
        } else {
            $self->{logger}->writeLogDebug("Result : $stdout");
        }
    }
    return undef;
}

##################################
## Run Remote Create Task
#
sub createRemote($) {

    my $self = shift;
    my $taskId = $_[0];
    if (!$taskId){
    return undef;
    }
    my ($lerror, $stdout, $cmd_line);
    my ($status, $sth) = $self->{centreon_dbc}->query(
        "SELECT * FROM `contact` WHERE `contact_admin` = '1' AND `contact_activate` = '1' LIMIT 1"
    );
    if ($status == -1) {
        $self->{logger}->writeLogError("Error selecting admin from db for starting worker");
        return undef;
    }

    my $data = $sth->fetchrow_hashref();
    my $username = $data->{'contact_alias'};
    my $passwordEnc = $data->{'contact_passwd'};
    # parse md5 password
    if ($passwordEnc =~ m/^md5__(.*)/) {
        $passwordEnc = $1;
    }

    my $cmdexec = "$self->{centreonDir}/bin/centreon -u %s -p %s "
        . "-w -o CentreonWorker -a createRemoteTask -v '" . $taskId . "'";
    $self->{logger}->writeLogDebug("cmd: " . sprintf($cmdexec, '<admin_user>', '<admin_md5_password>'));
    ($lerror, $stdout) = centreon::common::misc::backtick(
        command => sprintf($cmdexec, $username, $passwordEnc),
        logger => $self->{logger},
        timeout => $self->{cmd_timeout}
    );
    if (defined($stdout) && $stdout) {
        if ($lerror == -1) {
            $self->{logger}->writeLogError("Result : $stdout");
        } else {
            $self->{logger}->writeLogDebug("Result : $stdout");
        }
    }
    return undef;
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

######################################################
## Remove illegal characters from an external command.
## Param : command line
#
sub removeIllegalCharacters($) {
    my $self = shift;
    my ($cmdLine) = @_;

    return $cmdLine if (!defined($self->{illegal_characters}) || $self->{illegal_characters} eq '');

    $cmdLine =~ s/[\Q$self->{illegal_characters}\E]//g;

    return $cmdLine;
}

################################################
## Send an external command on a remote server.
## Param : id_remote_server, external command
#
sub sendExternalCommand($$) {
    my $self = shift;
    # Init Parameters
    my ($id, $cmd) = @_;
    my ($lerror, $return_code, $stdout, $cmd2, $cmd_line);

    # Get server informations
    my $server_info = $self->getServerConfig($id);
    my $port = checkSSHPort($server_info->{ssh_port});

    # Get command file
    my $command_file = $self->getEngineConfigurationField($id, "command_file");

    # check if ip address is defined
    if (defined($server_info->{ns_ip_address})) {
        $cmd =~ s/\\/\\\\/g;
        if ($server_info->{localhost} == 1) {
            my $result = waitPipe($command_file);

            if ($result == 0) {
                # split $cmd in order to send it in multiple line
                my $count = 0;
                foreach my $cmd1 (split(/\n/, $cmd)) {
                    if ($count >= 200) {
                        $cmd2 = "$self->{echo} \"".$cmd_line."\" >> ".$command_file;
                        $self->{logger}->writeLogInfo("External command on Central Server: ($id) : \"".$cmd_line."\"");
                        ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                            command => $cmd2,
                            logger => $self->{logger},
                            timeout => $self->{cmd_timeout}
                        );
                        $cmd_line = "";
                        $count = 0;
                    } else {
                        $cmd_line .= $self->removeIllegalCharacters($cmd1) . "\n";
                    }
                    $count++;
                }
                if ($count gt 0) {
                    $cmd2 = "$self->{echo} \"".$cmd_line."\" >> ".$command_file;
                    $self->{logger}->writeLogInfo("External command on Central Server: ($id) : \"".$cmd_line."\"");
                    ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                        command => $cmd2,
                        logger => $self->{logger},
                        timeout => $self->{cmd_timeout}
                    );
                    $cmd_line = "";
                    $count = 0;
                }
                if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                    $self->{logger}->writeLogError(
                        "Could not write into pipe file " . $command_file . " on poller " . $id
                    );
                }
            } else {
                $self->{logger}->writeLogError(
                    'Cannot write external command on central server : "' . $cmd_line . '"'
                );
            }
        } else {
            $cmd =~ s/\'/\'\\\'\'/g;

            # split $cmd in order to send it in multiple line
            my $count = 0;
            my $totalCount = 0;
            my @splittedCommands = split(/\n/, $cmd);
            my $countCommands = @splittedCommands;
            foreach my $cmd1 (@splittedCommands) {
                if (defined($server_info->{remote_id})
                    && $server_info->{remote_id} != 0
                    && $self->{instance_mode} ne "remote"
                    && $server_info->{remote_server_centcore_ssh_proxy} == 1
                ) {
                    $cmd_line .= 'EXTERNALCMD:' . $id . ':' . $self->removeIllegalCharacters($cmd1) . "\n";
                } else {
                    $cmd_line .= $self->removeIllegalCharacters($cmd1) . "\n";
                }
                $count++;
                $totalCount++;

                if ($count >= 200 || $totalCount == $countCommands) {
                    if (defined($server_info->{remote_id}) 
                        && $server_info->{remote_id} != 0
                        && $self->{instance_mode} ne "remote"
                        && $server_info->{remote_server_centcore_ssh_proxy} == 1
                    ) {
                        # Forward commands to Remote Server Master
                        my $remote_server = $self->getServerConfig($server_info->{remote_id});
                        $port = checkSSHPort($remote_server->{ssh_port});
                        $cmd_line =~ s/^\s+|\s+$//g;
                        $cmd2 = "$self->{ssh} -q " . $remote_server->{ns_ip_address} . " -p $port "
                            . "\"$self->{echo} '" . $cmd_line . "' >> " . $self->{cmdDir} . time() . "-sendcmd\"";
                        $self->{logger}->writeLogInfo(
                            "Sending external command using Remote Server: " . $remote_server->{name}
                        );

                        ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                            command => $cmd2,
                            logger => $self->{logger},
                            timeout => $self->{cmd_timeout},
                            wait_exit => 1
                        );
                        
                        if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                            $self->{logger}->writeLogError(sprintf(
                                "Couldn't send external command %s on Remote Server: %s (%d)", 
                                $cmd_line,
                                $remote_server->{name},
                                $remote_server->{id}
                                )
                            );

                            # Try with additionnals Remote Server
                            if ($server_info->{additonal_remotes}) {
                                foreach my $additional_remote_id ($server_info->{additonal_remotes}) {
                                    my $additional_remote_config = $self->getServerConfig($additional_remote_id->{remote_server_id});
                                    $port = checkSSHPort($additional_remote_config->{ssh_port});
                                    $self->{logger}->writeLogInfo(
                                        "Try to use additional Remote Server: " . $additional_remote_config->{name}
                                    );
                                    $cmd2 = "$self->{ssh} -q " . $additional_remote_config->{ns_ip_address} . " -p $port "
                                        . "\"$self->{echo} '" . $cmd_line . "' >> " . $self->{cmdDir} . time() . "-sendcmd\"";
                                    $self->{logger}->writeLogInfo(
                                        "Sending external command using Remote Server: "
                                        . $additional_remote_config->{name}
                                    );

                                    ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                                        command => $cmd2,
                                        logger => $self->{logger},
                                        timeout => $self->{cmd_timeout},
                                        wait_exit => 1
                                    );

                                    if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                                        $self->{logger}->writeLogError(sprintf(
                                            "Couldn't send external command %s on Remote Server: %s (%d)", 
                                            $cmd_line,
                                            $additional_remote_config->{name},
                                            $additional_remote_config->{id}
                                            )
                                        );
                                    } else {
                                        # Commands sent, stop loop
                                        $self->{logger}->writeLogInfo(
                                            "External command using Remote Server: " . $additional_remote_config->{name} . " sent"
                                        );
                                        last;
                                    }
                                }
                            }
                        } else {
                            $self->{logger}->writeLogInfo(
                                "External command using Remote Server: " . $remote_server->{name} . " sent"
                            );
                        }
                    } else {
                        # Send commands directly to poller
                        $cmd2 = "$self->{ssh} -q " . $server_info->{ns_ip_address} . " -p $port "
                            . "\"$self->{echo} '" . $cmd_line."' >> " . $command_file . "\"";
                        $self->{logger}->writeLogInfo(
                            "External command : " . $server_info->{ns_ip_address} . " ($id) : \"" . $cmd_line . "\""
                        );
                        ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                            command => $cmd2,
                            logger => $self->{logger},
                            timeout => $self->{cmd_timeout}
                        );
                        if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                            $self->{logger}->writeLogError(sprintf(
                                "Could not write into pipe file " . $command_file . " on poller: " . $id
                            ));
                        }
                    }

                    $cmd_line = "";
                    $count = 0;
                }
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
## Wait Centreon Engine Pipe availability
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

##
# Checks if rotation occurred,
#
sub checkRotation($$$$$) {
    my $self = shift;
    my $instanceId = $_[0];
    my $lastUpdate = $_[1];
    my $remoteConnection = $_[2];
    my $localLogFile = $_[3];
    my $port = $_[4];
    my ($lerror, $stdout, $cmd);

    my $archivePath = $self->getEngineConfigurationField($instanceId, 'log_archive_path');
    my $getLastCmd = 'echo "$(find '.$archivePath.' -type f -exec stat -c "%Z:%n" {} \; | sort | tail -1)"';
    $cmd = "$self->{ssh} -p $port -q $remoteConnection '$getLastCmd'";

    ($lerror, $stdout) = centreon::common::misc::backtick(
        command => $cmd,
        logger => $self->{logger},
        timeout => 120
    );
    my $updateTime = $1;
    my $fileName = $2;
    if (defined($updateTime) && defined($lastUpdate) && $updateTime > $lastUpdate) {
        $cmd = "$self->{scp} -P $port $remoteConnection:$fileName $localLogFile.rotate > /dev/null";

        ($lerror, $stdout) = centreon::common::misc::backtick(
            command => $cmd,
            logger => $self->{logger},
            timeout => 120
        );
        $self->{logger}->writeLogInfo("Info: copied rotated file for instance $instanceId");
    }
}

##################################################
# Send config files to a poller
#
sub sendConfigFile($) {
    my $self = shift;
    # Init Values
    my $id = $_[0];
    my ($lerror, $return_code, $stdout, $cmd);

    my $cfg_dir = $self->getEngineConfigurationField($id, "cfg_dir");
    my $server_info = $self->getServerConfig($id);
    my ($origin, $dest, $remote_server);
    my $port = checkSSHPort($server_info->{ssh_port});

    if (!defined($cfg_dir) || $cfg_dir =~ //) {
        $self->{logger}->writeLogError(
            "Engine configuration file is empty for poller $id. Please check centengine.cfg file."
        );
        return;
    }

    # Send configuration for Centreon Engine
    if (defined($server_info->{remote_id})
        && $server_info->{remote_id} != 0
        && $self->{instance_mode} ne "remote"
        && $server_info->{remote_server_centcore_ssh_proxy} == 1
    ) {
        $remote_server = $self->getServerConfig($server_info->{remote_id});
        $self->{logger}->writeLogInfo(
            'Send Centreon Engine config files ' .
            'on poller "' . $server_info->{name} . '" (' . $server_info->{id} . ') ' .
            'using Remote Server: ' . $remote_server->{name} . ' (' . $remote_server->{id} . ')'
        );

        $origin = $self->{cacheDir} . "/config/engine/" . $id;
        $dest = $remote_server->{'ns_ip_address'} . ":" . $self->{cacheDir} . "/config/engine";
        $port = checkSSHPort($remote_server->{ssh_port});
        $cmd = "$self->{scp} -r -P $port $origin $dest 2>&1";

        ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
            command => $cmd,
            logger => $self->{logger},
            timeout => 300,
            wait_exit => 1
        );
        
        if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
            $self->{logger}->writeLogError(sprintf(
                "Couldn't send Centreon Engine config files on Remote Server: %s (%d)", 
                $remote_server->{'name'},
                $remote_server->{'id'}
                )
            );
            
            # Try with additionnals Remote Server
            if ($server_info->{'additonal_remotes'}) {
                foreach my $additional_remote_id ($server_info->{additonal_remotes}) {
                    my $additional_remote_config = $self->getServerConfig($additional_remote_id->{remote_server_id});
                    $self->{logger}->writeLogInfo(
                        "Try to use additional Remote Server: " . $additional_remote_config->{name}
                    );
                    $dest = $additional_remote_config->{'ns_ip_address'} . ":" . $self->{cacheDir} . "/config/engine";
                    $port = checkSSHPort($additional_remote_config->{ssh_port});
                    $cmd = "$self->{scp} -r -P $port $origin $dest 2>&1";

                    ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                        command => $cmd,
                        logger => $self->{logger},
                        timeout => 300,
                        wait_exit => 1
                    );

                    if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                        $self->{logger}->writeLogError(sprintf(
                            "Couldn't send Centreon Engine config files on Remote Server: %s (%d)",
                            $additional_remote_config->{name},
                            $additional_remote_config->{id}
                            )
                        );
                    } else {
                        # Commands sent, stop loop
                        $self->{logger}->writeLogInfo(sprintf(
                            "Centreon Engine configuration using Remote Server: %s sent",
                            $additional_remote_config->{name}
                        ));
                        last;
                    }
                }
            }
        }
    } else {
        $cfg_dir = $self->getEngineConfigurationField($id, "cfg_dir");
        $origin = $self->{cacheDir} . "/config/engine/" . $id . "/*";
        $dest = $server_info->{'ns_ip_address'} . ":$cfg_dir";
        $cmd = "$self->{scp} -P $port $origin $dest 2>&1";

        # Send data with SCP
        $self->{logger}->writeLogInfo(
            'Send Centreon Engine config files ' .
            'on poller "' . $server_info->{name} . '" (' . $server_info->{id} . ')'
        );
        
        ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
            command => $cmd,
            logger => $self->{logger},
            timeout => 300
        );
        
        if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
            $self->{logger}->writeLogError('Send Centreon Engine config files problems');
        }
    }

    if (defined($stdout) && $stdout) {
        $self->{logger}->writeLogInfo("Result : $stdout");
    }

    # Send configuration for Centreon Broker
    if (-e $self->{cacheDir}  . "/config/broker/" . $id) {
        # Check availability of broker files.
        my $count = 0;
        opendir(my $dh, $self->{cacheDir} . "/config/broker/" . $id);
        while(readdir $dh) {
            $count++;
        }
        closedir $dh;

        if ($count > 2) {
            if ($server_info->{localhost} == 0) {
                if (defined($server_info->{remote_id}) 
                    && $server_info->{remote_id} != 0 
                    && $self->{instance_mode} ne "remote"
                    && $server_info->{remote_server_centcore_ssh_proxy} == 1
                ) {
                    $remote_server = $self->getServerConfig($server_info->{remote_id});
                    $self->{logger}->writeLogInfo(
                        'Send Centreon Broker config files ' .
                        'on poller "' . $server_info->{name} . '" (' . $server_info->{id} . ') ' .
                        'using remote server "' . $remote_server->{name} . '" (' . $remote_server->{id} . ')'
                    );
                    $origin = $self->{cacheDir} . "/config/broker/" . $id;
                    $dest = $remote_server->{'ns_ip_address'} . ":" . $self->{cacheDir} . "/config/broker";
                    $cmd = "$self->{scp} -r -P $port $origin $dest 2>&1";

                    ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                        command => $cmd,
                        logger => $self->{logger},
                        timeout => 300,
                        wait_exit => 1
                    );
                    
                    if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                        $self->{logger}->writeLogError(sprintf(
                            "Couldn't send Centreon Broker config files on Remote Server: %s (%d)", 
                            $remote_server->{'name'},
                            $remote_server->{'id'}
                            )
                        );
                        
                        # Try with additionnals Remote Server
                        if ($server_info->{'additonal_remotes'}) {
                            foreach my $additional_remote_id ($server_info->{additonal_remotes}) {
                                my $additional_remote_config = $self->getServerConfig($additional_remote_id->{remote_server_id});
                                $self->{logger}->writeLogInfo(
                                    "Try to use additional Remote Server: " . $additional_remote_config->{name}
                                );
                                $dest = $additional_remote_config->{'ns_ip_address'} . ":" . $self->{centreonDir} . "/filesGeneration/broker";
                                $cmd = "$self->{scp} -r -P $port $origin $dest 2>&1";

                                ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                                    command => $cmd,
                                    logger => $self->{logger},
                                    timeout => 300,
                                    wait_exit => 1
                                );

                                if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                                    $self->{logger}->writeLogError(sprintf(
                                         "Couldn't send Centreon Broker config files on Remote Server: %s (%d)",
                                        $additional_remote_config->{name},
                                        $additional_remote_config->{id}
                                        )
                                    );
                                } else {
                                    # Commands sent, stop loop
                                    $self->{logger}->writeLogInfo(
                                        "Centreon Broker config using Remote Server: " . $additional_remote_config->{name} . " sent"
                                    );
                                    last;
                                }
                            }
                        }
                    }
                } else {
                    $self->{logger}->writeLogInfo(
                        'Send Centreon Broker config files ' .
                        'on poller "' . $server_info->{name} . '" (' . $server_info->{id} . ')'
                    );
                    $cfg_dir = $server_info->{'centreonbroker_cfg_path'};
                    $origin = $self->{cacheDir} . "/config/broker/" . $id . "/*.*";
                    $dest = $server_info->{ns_ip_address}.":$cfg_dir";
                    $cmd = "$self->{scp} -P $port $origin $dest 2>&1";

                    ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                        command => $cmd,
                        logger => $self->{logger},
                        timeout => 300
                    );
                    if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                        $self->{logger}->writeLogError('Send Centreon Broker config files problems');
                    }
                }
            } else {
                $self->{logger}->writeLogInfo(
                    'Send Centreon Broker config files ' .
                    'on poller "' . $server_info->{name} . '" (' . $server_info->{id} . ')'
                );
                $cmd = "cp $origin $cfg_dir 2>&1";
                ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                    command => $cmd,
                    logger => $self->{logger},
                    timeout => 60
                );

                if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                    $self->{logger}->writeLogError('Send Centreon Broker config files problems');
                }
            }

            if (defined($stdout) && $stdout) {
                $self->{logger}->writeLogInfo("Result : $stdout");
            }
        }
    }

    # send command on Remote Server to export configuration
    if (defined($server_info->{remote_id}) 
        && $server_info->{remote_id} != 0 
        && $self->{instance_mode} ne "remote"
        && $server_info->{remote_server_centcore_ssh_proxy} == 1
    ) {
        $remote_server = $self->getServerConfig($server_info->{remote_id});
        $self->{logger}->writeLogDebug(
            'Send command on Remote Server "' . $remote_server->{name} . '" to export configuration'
        );
        $port = checkSSHPort($remote_server->{ssh_port});
        $cmd = "$self->{ssh} -p $port " . $remote_server->{'ns_ip_address'}  . " "
            . "'echo \"SENDCFGFILE:" . $id . "\" >> $self->{cmdDir}/" . time() . "-sendcmd'";
        ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
            command => $cmd,
            logger => $self->{logger},
            timeout => 300,
            wait_exit => 1
        );

        if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
            $self->{logger}->writeLogError(sprintf(
                "Couldn't forward SENDCFGFILE order on Remote Server: %s (%d)", 
                $remote_server->{name},
                $remote_server->{id}
            ));

            # Try with additionnals Remote Server
            if ($server_info->{additonal_remotes}) {
                foreach my $additional_remote_id ($server_info->{additonal_remotes}) {
                    my $additional_remote_config = $self->getServerConfig($additional_remote_id->{remote_server_id});
                    $self->{logger}->writeLogInfo(
                        "Try to use additional Remote Server: " . $additional_remote_config->{name}
                    );

                    $port = checkSSHPort($additional_remote_config->{ssh_port});
                    $cmd = "$self->{ssh} -p $port " . $additional_remote_config->{'ns_ip_address'}
                        . " 'echo \"SENDCFGFILE:" . $id . "\" >> $self->{cmdDir}/" . time() . "-sendcmd'";
                    ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                        command => $cmd,
                        logger => $self->{logger},
                        timeout => 300,
                        wait_exit => 1
                    );

                    if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                        $self->{logger}->writeLogError(sprintf(
                            "Couldn't forward SENDCFGFILE order on Remote Server: %s (%d)", 
                            $remote_server->{name},
                            $remote_server->{id}
                        ));
                    } else {
                        # Commands sent, stop loop
                        $self->{logger}->writeLogInfo(
                            "SENDCFGFILE order sent to Remote Server: " . $additional_remote_config->{name}
                        );
                        last;
                    }
                }
            }
        }
    }
}

##################################################
# Send export files to a Remote Server
#
sub sendExportFile($) {
    my $self = shift;
    # Init Values
    my $id = $_[0];
    my $taskId = $_[1];
    if (!$id || !$taskId){
        return undef;
    }
    my ($lerror, $return_code, $stdout, $cmd);

    my $server_info = $self->getServerConfig($id);
    my $port = checkSSHPort($server_info->{ssh_port});
    my $cfg_dir = $server_info->{'centreonbroker_cfg_path'};

    if (!defined($cfg_dir) || $cfg_dir =~ //) {
        $self->{logger}->writeLogError(
            "Engine configuration file is empty for poller $id. Please check nagios.cfg file."
        );
        return;
    }

    unless (-e $self->{cacheDir}  . "/config/export/" . $id) {
        $self->{logger}->writeLogInfo(
            "Export directory is empty for Remote Server " . $server_info->{name} . " " .
            $self->{cacheDir} . "/config/export/$id."
        );
        return;
    }

    my $origin = $self->{cacheDir} . "/config/export/" . $id . "/*";
    my $dest = $server_info->{'ns_ip_address'} . ":" . $self->{cacheDir} . "/config/remote-data/";

    # Send data with rSync
    $self->{logger}->writeLogInfo('Export files on Remote Server "' . $server_info->{name} . '" (' . $id . ')');

    $cmd = "$self->{rsync} -ra -e '$self->{ssh} -o port=$port' $origin $dest 2>&1";
    ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
        command => $cmd,
        logger => $self->{logger},
        timeout => 300,
        wait_exit => 1
    );

    if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
        $self->{logger}->writeLogError(sprintf(
            "Couldn't export files on Remote Server: %s (%d)",
            $server_info->{name},
            $id
        ));
    }
    if (defined($stdout) && $stdout){
        $self->{logger}->writeLogInfo("Result : $stdout");
    }

    $self->createRemote($taskId);
}

##################################################
# Function for initialize Centreon Engine
# Parameters :
#   - start
#   - restart
#   - stop
#
sub initEngine($$$) {
    my $self = shift;
    my $id = $_[0]; # poller id
    my $options = $_[1]; # restart, reload, stop...
    my $action = $_[2]; # full command
    my ($lerror, $cmd, $return_code, $stdout);

    # Get configuration
    my $conf = $self->getServerConfig($id);
    my $port = checkSSHPort($conf->{ssh_port});

    if (!defined($conf)) {
        $self->{logger}->writeLogError("Poller $id doesn't exist...");
        $self->{logger}->writeLogError("Cannot manage undefined poller...");
        return;
    }

    if (defined($conf->{ns_ip_address}) && $conf->{ns_ip_address}) {
        # Launch command
        if (defined($conf->{remote_id}) 
            && $conf->{remote_id} != 0 
            && $self->{instance_mode} ne "remote"
            && $conf->{remote_server_centcore_ssh_proxy} == 1
        ) {
            my $remote_server = $self->getServerConfig($conf->{remote_id});
            $action =~ s/^\s+|\s+$//g;
            $port = checkSSHPort($remote_server->{ssh_port});
            $cmd = "$self->{ssh} -p $port " . $remote_server->{ns_ip_address} . " 'echo \"$action\" >> $self->{cmdDir}" . time() . "-sendcmd'";
            
            $self->{logger}->writeLogInfo(sprintf(
                'Send command %s to Centreon Engine on poller %s (%d) using remote server %s (%d)',
                $action,
                $conf->{name},
                $conf->{id},
                $remote_server->{name},
                $remote_server->{id}
            ));

            ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                command => $cmd,
                logger => $self->{logger},
                timeout => 120,
                wait_exit => 1
            );

            if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                $self->{logger}->writeLogError(sprintf(
                    "Couldn't sent command %s to Centreon Engine on poller %s (%d) using remote server %s (%d)",
                    $action,
                    $conf->{name},
                    $conf->{id},
                    $remote_server->{name},
                    $remote_server->{id}
                ));

                # Try with additionnals Remote Server
                if ($conf->{additonal_remotes}) {
                    foreach my $additional_remote_id ($conf->{additonal_remotes}) {
                        my $additional_remote_config = $self->getServerConfig($additional_remote_id->{remote_server_id});
                        $self->{logger}->writeLogInfo(
                            "Try to use additional Remote Server: " . $additional_remote_config->{name}
                        );

                        $port = checkSSHPort($additional_remote_config->{ssh_port});
                        $cmd = "$self->{ssh} -p $port " . $additional_remote_config->{ns_ip_address} 
                            . " 'echo \"$action\" >> $self->{cmdDir}" . time() . "-sendcmd'";

                        $self->{logger}->writeLogInfo(
                            "Sending external command using Remote Server: "
                            . $additional_remote_config->{name}
                        );

                        ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                            command => $cmd,
                            logger => $self->{logger},
                            timeout => $self->{cmd_timeout},
                            wait_exit => 1
                        );

                        if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                            $self->{logger}->writeLogError(sprintf(
                                "Couldn't sent command %s to Centreon Engine on poller %s (%d) using remote server %s (%d)",
                                $action,
                                $conf->{name},
                                $conf->{id},
                                $additional_remote_config->{name},
                                $additional_remote_config->{id}
                            ));
                        } else {
                            # Commands sent, stop loop
                            $self->{logger}->writeLogInfo(sprintf(
                                "%s command to Centreon Engine on poller %s (%d) using Remote Server %s (%d) sent",
                                $action,
                                $conf->{name},
                                $conf->{id},
                                $additional_remote_config->{name},
                                $additional_remote_config->{id}
                            ));
                            last;
                        }
                    }
                }
            }
        } else {
            $cmd = '';
            if ($conf->{localhost} == 0) {
                $cmd = "$self->{ssh} -p $port $conf->{ns_ip_address} ";
            }
            $cmd .= $self->getEngineCommand($conf, $options);
            $self->{logger}->writeLogInfo(
                'Init Script : "' . $cmd . '" ' .
                'on poller "' . $conf->{name} . '" (' . $id . ')'
            );
            ($lerror, $stdout) = centreon::common::misc::backtick(
                command => $cmd,
                logger => $self->{logger},
                timeout => 120
            );
            if (defined($stdout)) {
                foreach my $line (split(/\n/, $stdout)) {
                    $self->{logger}->writeLogDebug("Engine : " . $line);
                }
            }
        }
    } else {
        $self->{logger}->writeLogError("Cannot $options Engine for poller $id");
    }
}

##################################################
# Function to reload Centreon Broker
# Arguments:
#     $id: int Poller id to reload
#
sub reloadBroker($) {
    my $self = shift;
    my $id = $_[0];
    my ($lerror, $stdout, $conf, $command);

    # Get configuration
    $conf = $self->getServerConfig($id);

    if ($conf->{localhost} == 1) {
        $command = "$self->{sudo} $conf->{broker_reload_command}";
        $self->{logger}->writeLogInfo(
            'Init Script : "' . $command . '" ' .
            'on poller "' . $conf->{name} . '" (' . $id . ')'
        );

        ($lerror, $stdout) = centreon::common::misc::backtick(
            command => $command,
            logger => $self->{logger},
            timeout => 10
        );

        if (defined($stdout)) {
            foreach my $line (split(/\n/, $stdout)) {
                $self->{logger}->writeLogDebug("Broker : " . $line);
            }
        }
    }
}

##################################################
# Function to generate Centreon Engine command :
# Arguments:
#     $pollerConf: array Poller configuration get in database (nagios_server table)
#     $action: string Name of the action (restart, reload, stop...)
#
sub getEngineCommand($$) {
    my $self = shift;
    my $pollerConf = $_[0];
    my $action = $_[1];
    my $command;

    if ($action eq 'start') {
        $command = "$self->{sudo} $pollerConf->{engine_start_command}";
    } elsif ($action eq 'stop') {
        $command = "$self->{sudo} $pollerConf->{engine_stop_command}";
    } elsif ($action eq 'restart') {
        $command = "$self->{sudo} $pollerConf->{engine_restart_command}";
    } elsif ($action eq 'reload') {
        $command = "$self->{sudo} $pollerConf->{engine_reload_command}";
    } else {
        $command = "$self->{sudo} $self->{service} $self->{engineInitScript} $action";
    }

    return $command;
}

##################################################
# Function for synchronize SNMP trap configuration
#
sub syncTraps($) {
    my $self = shift;
    my $id = $_[0];
    my ($lerror, $stdout, $return_code, $cmd);

    my $query = "SELECT `id` FROM `nagios_server` WHERE `ns_activate` = '1' " ;
    if ($id != 0) {
        $query .= "AND id = '" . $id . "'";
    } else {
        $query .= "AND `localhost` = '0'";
    }
    $self->{logger}->writeLogDebug($query);
    my ($status, $sth) = $self->{centreon_dbc}->query($query);

    return if ($status == -1);
    while (my $server = $sth->fetchrow_hashref()) {
        # Get configuration
        my $ns_server = $self->getServerConfig($server->{id});
        my $port = checkSSHPort($ns_server->{ssh_port});
        my $remote_server;

        if (defined($ns_server->{remote_id}) 
            && $ns_server->{remote_id} != 0 
            && $self->{instance_mode} ne "remote"
            && $ns_server->{remote_server_centcore_ssh_proxy} == 1
        ) {
            #
            # Send SNMP trap configuration database
            #
            $remote_server = $self->getServerConfig($ns_server->{remote_id});
            $port = checkSSHPort($remote_server->{ssh_port});
            $cmd = "$self->{scp} -r -P $port /etc/snmp/centreon_traps/$id "
                . "$remote_server->{'ns_ip_address'}:/etc/snmp/centreon_traps/";

            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                command => $cmd,
                logger => $self->{logger},
                timeout => 300,
                wait_exit => 1
            );
            
            if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                $self->{logger}->writeLogError(sprintf(
                    "Couldn't send SNMP trap configuration for poller %d on Remote Server: %s (%d)",
                    $id,
                    $remote_server->{name},
                    $remote_server->{id}
                ));
            }

            # Try with additionnals Remote Server
            if ($ns_server->{additonal_remotes}) {
                foreach my $additional_remote_id ($ns_server->{additonal_remotes}) {
                    my $additional_remote_config = $self->getServerConfig($additional_remote_id->{remote_server_id});
                    $self->{logger}->writeLogInfo(
                        "Try to use additional Remote Server: " . $additional_remote_config->{name}
                    );

                    $port = checkSSHPort($additional_remote_config->{ssh_port});
                    $cmd = "$self->{scp} -r -P $port /etc/snmp/centreon_traps/$id "
                        . "$additional_remote_config->{'ns_ip_address'}:/etc/snmp/centreon_traps/";

                    $self->{logger}->writeLogDebug($cmd);
                    ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                        command => $cmd,
                        logger => $self->{logger},
                        timeout => 300,
                        wait_exit => 1
                    );

                    if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                        $self->{logger}->writeLogError(sprintf(
                            "Couldn't send SNMP trap configuration for poller %d on Remote Server: %s (%d)",
                            $id,
                            $additional_remote_config->{name},
                            $additional_remote_config->{id}
                        ));
                    } else {
                        # Commands sent, stop loop
                        $self->{logger}->writeLogInfo(sprintf(
                            "SNMP trap configuration for poller %d using Remote Server: %s sent",
                            $id,
                            $additional_remote_config->{name}
                        ));
                        last;
                    }
                }
            }

            #
            # Send synchronization order to Remote Server
            #
            $port = checkSSHPort($remote_server->{ssh_port});
            $cmd = "$self->{ssh} -p $port " . $remote_server->{'ns_ip_address'}
                . " 'echo \"SYNCTRAP:" . $id . "\" >> $self->{cmdDir}/" . time() . "-sendcmd'";

            ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                command => $cmd,
                logger => $self->{logger},
                timeout => 300,
                wait_exit => 1
            );
            
            if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                $self->{logger}->writeLogError(sprintf(
                    "Couldn't send SNMP trap synchronization order to Remote Server: %s (%d)",
                    $remote_server->{name},
                    $remote_server->{id}
                ));
            }

            # Try with additionnals Remote Server
            if ($ns_server->{additonal_remotes}) {
                foreach my $additional_remote_id ($ns_server->{additonal_remotes}) {
                    my $additional_remote_config = $self->getServerConfig($additional_remote_id->{remote_server_id});
                    $self->{logger}->writeLogInfo(
                        "Try to use additional Remote Server: " . $additional_remote_config->{name}
                    );

                    $port = checkSSHPort($additional_remote_config->{ssh_port});
                    $cmd = "$self->{ssh} -p $port " . $additional_remote_config->{'ns_ip_address'}
                        . " 'echo \"SYNCTRAP:" . $id . "\" >> $self->{cmdDir}/" . time() . "-sendcmd'";

                    $self->{logger}->writeLogDebug($cmd);
                    ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                        command => $cmd,
                        logger => $self->{logger},
                        timeout => 300,
                        wait_exit => 1
                    );

                    if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                        $self->{logger}->writeLogError(sprintf(
                            "Couldn't send SNMP trap synchronization order to Remote Server: %s (%d)",
                            $id,
                            $additional_remote_config->{name},
                            $additional_remote_config->{id}
                        ));
                    } else {
                        # Commands sent, stop loop
                        $self->{logger}->writeLogInfo(sprintf(
                            "SNMP trap synchronization order for Remote Server: %s sent",
                            $additional_remote_config->{name}
                        ));
                        last;
                    }
                }
            }
        } else {
            $cmd = "$self->{scp} -P $port /etc/snmp/centreon_traps/$id/centreontrapd.sdb "
                . "$ns_server->{ns_ip_address}:$ns_server->{snmp_trapd_path_conf} 2>&1";
            
            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                command => $cmd,
                logger => $self->{logger},
                timeout => 300
            );
            if (defined($stdout) && $stdout){
                $self->{logger}->writeLogInfo("Result : $stdout");
            }
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
    my $port = checkSSHPort($ns_server->{ssh_port});

    if (defined($ns_server->{ns_ip_address}) && $ns_server->{ns_ip_address}) {
        # Launch command
        if (defined($ns_server->{localhost}) && $ns_server->{localhost}) {
            $cmd = $ns_server->{nagios_bin} . ' -V';
            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout) = centreon::common::misc::backtick(
                command => $cmd,
                logger => $self->{logger},
                timeout => 60
            );
        } else {
            $cmd = "$self->{ssh} -p $port " . $ns_server->{ns_ip_address} . " '$ns_server->{nagios_bin} -V";
            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout) = centreon::common::misc::backtick(
                command => $cmd,
                logger => $self->{logger},
                timeout => 60
            );
        }
    
        if ($stdout =~ /Centreon Engine ([\.0-9]*[a-zA-Z0-9\-\.]+)/ms) {
            $self->{logger}->writeLogInfo("Engine: Centreon Engine");
            $self->{logger}->writeLogInfo("Version: $1");
            $self->updateEngineInformation($id, "Centreon Engine", $1);
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

    $self->{centreon_dbc}->query(
        "UPDATE `nagios_server` " .
        "SET `engine_name` = '$engine_name', `engine_version` = '$engine_version' " .
        "WHERE `id` = '$id'"
    );
}

################################
## Reload CentreonTrapd Daemon
#
sub initCentreonTrapd {
    my ($self, $id, $start_type, $action) = @_;
    my ($lerror, $stdout, $return_code, $cmd);

    # Get configuration
    my $ns_server = $self->getServerConfig($id);
    my $port = checkSSHPort($ns_server->{ssh_port});

    if (defined($ns_server->{ns_ip_address})
        && defined($ns_server->{init_script_centreontrapd})
        && $ns_server->{init_script_centreontrapd} ne ""
    ) {
        if (defined($ns_server->{localhost}) && $ns_server->{localhost}) {
            # Reload/Restart Centreontrapd locally
            $cmd = "$self->{sudo} $self->{service} " . $ns_server->{init_script_centreontrapd} . " " . $start_type;
            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                command => $cmd,
                logger => $self->{logger},
                timeout => 120
            );
        } else {
            if (defined($ns_server->{remote_id}) 
                && $ns_server->{remote_id} != 0 
                && $self->{instance_mode} ne "remote"
                && $ns_server->{remote_server_centcore_ssh_proxy} == 1
            ) {
                # Reload/Restart Centreontrapd on poller using Remote Server as proxy
                my $remote_server = $self->getServerConfig($ns_server->{remote_id});
                $action =~ s/^\s+|\s+$//g;
                $port = checkSSHPort($remote_server->{ssh_port});
                $cmd = "$self->{ssh} -p $port " . $remote_server->{ns_ip_address} . " 'echo \"$action\" >> $self->{cmdDir}" . time() . "-sendcmd'";
                $self->{logger}->writeLogDebug(sprintf(
                    "Try to %s Centreontrapd on poller %s (%d) using Remote Server: %s",
                    $start_type,
                    $ns_server->{name},
                    $ns_server->{id},
                    $remote_server->{name}
                ));
                $self->{logger}->writeLogDebug($cmd);

                ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                    command => $cmd,
                    logger => $self->{logger},
                    timeout => 60,
                    wait_exit => 1
                );

                if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                    $self->{logger}->writeLogError(sprintf(
                        "Couldn't %s Centreontrapd on Remote Server: %s (%d)", 
                        $start_type,
                        $remote_server->{name},
                        $remote_server->{id}
                    ));

                    # Try with additionnals Remote Server
                    if ($ns_server->{additonal_remotes}) {
                        foreach my $additional_remote_id ($ns_server->{additonal_remotes}) {
                            my $additional_remote_config = $self->getServerConfig($additional_remote_id->{remote_server_id});
                            $self->{logger}->writeLogDebug(sprintf(
                                "Try to %s Centreontrapd on poller %s (%d) using Remote Server: %s",
                                $start_type,
                                $ns_server->{name},
                                $ns_server->{id},
                                $additional_remote_config->{name}
                            ));
                            $port = checkSSHPort($additional_remote_config->{ssh_port});
                            $cmd = "$self->{ssh} -p $port " . $additional_remote_config->{ns_ip_address} . " 'echo \"$action\" >> $self->{cmdDir}" . time() . "-sendcmd'";
                            $self->{logger}->writeLogInfo(sprintf(
                                "Try to %s Centreontrapd on poller %s (%d) using Remote Server: %s",
                                $start_type,
                                $ns_server->{name},
                                $ns_server->{id},
                                $additional_remote_config->{name}
                            ));

                            ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                                command => $cmd,
                                logger => $self->{logger},
                                timeout => $self->{cmd_timeout},
                                wait_exit => 1
                            );

                            if ($lerror != 0 || (defined($return_code)  && $return_code != 0)) {
                                $self->{logger}->writeLogError(sprintf(
                                    "Couldn't %s Centreontrapd on Remote Server: %s (%d)", 
                                    $start_type,
                                    $additional_remote_config->{name},
                                    $additional_remote_config->{id}
                                ));
                            } else {
                                # Commands sent, stop loop
                                $self->{logger}->writeLogInfo(
                                    "External command using Remote Server: " . $additional_remote_config->{name} . " sent"
                                );
                                last;
                            }
                        }
                    }
                }
            } else {
                # Reload/Restart Centreontrapd on Poller/Remote Server using ssh
                $cmd = "$self->{ssh} -p $port " . $ns_server->{ns_ip_address} . " $self->{sudo} $self->{service} "
                    . $ns_server->{init_script_centreontrapd} . " " . $start_type;
                $self->{logger}->writeLogDebug($cmd);
                ($lerror, $stdout, $return_code) = centreon::common::misc::backtick(
                    command => $cmd,
                    logger => $self->{logger},
                    timeout => 60
                );
            }
        }
        $self->{logger}->writeLogInfo($start_type . " CentreonTrapd on poller $id ($ns_server->{ns_ip_address})");
    } else {
        $self->{logger}->writeLogError("Cannot " . $start_type . " CentreonTrapd for poller $id");
    }
}

####################################
## Parse request
#
sub parseRequest($) {
    my $self = shift;
    my ($action) = @_;

    if (!$action) {
        return ;
    }

    # Checks keys for launching commands
    if ($action =~ /^RESTART\:([0-9]*)/){
        $self->initEngine($1, "restart", $action);
    } elsif ($action =~ /^RELOAD\:([0-9]*)/){
        $self->initEngine($1, "reload", $action);
    } elsif ($action =~ /^START\:([0-9]*)/){
        $self->initEngine($1, "start", $action);
    } elsif ($action =~ /^STOP\:([0-9]*)/){
        $self->initEngine($1, "stop", $action);
    } elsif ($action =~ /^RELOADBROKER\:([0-9]*)/){
        $self->reloadBroker($1);
    } elsif ($action =~ /^SENDCFGFILE\:([0-9]*)/){
        $self->sendConfigFile($1);
    } elsif ($action =~ /^SENDEXPORTFILE\:([0-9]*)\:(.*)/){
        $self->sendExportFile($1, $2);
    } elsif ($action =~ /^SYNCTRAP\:([0-9]*)/){
        $self->syncTraps($1);
    } elsif ($action =~ /^RESTARTCENTREONTRAPD\:([0-9]*)/){
        $self->initCentreonTrapd($1, 'restart', $action);
    } elsif ($action =~ /^RELOADCENTREONTRAPD\:([0-9]*)/){
        $self->initCentreonTrapd($1, 'reload', $action);
    } elsif ($action =~ /^EXTERNALCMD\:([0-9]*)\:(.*)/){
        $self->storeCommands($1, $2);
    } elsif ($action =~ /^GETINFOS\:([0-9]*)/){
        $self->getInfos($1);
    } elsif ($action =~ /^STARTWORKER\:([0-9]*)/){
        $self->startWorker($1);
    } elsif ($action =~ /^CREATEREMOTETASK\:([0-9]*)/){
        $self->createRemote($1);
    }
}

############################################
## Check Centcore Configuration Profile
#
sub checkProfile() {
    my $self = shift;
    
    my $request = "SELECT * FROM options "
        . "WHERE `key` IN ('enable_perfdata_sync', 'enable_logs_sync', "
        . "'centcore_cmd_timeout', 'enable_broker_stats', 'centcore_illegal_characters')";
    my ($status, $sth) =  $self->{centreon_dbc}->query($request);
    return -1 if ($status == -1);
    while ((my $data = $sth->fetchrow_hashref())) {
        if (defined($data->{key}) && $data->{key} ne "" && defined($data->{value}) && $data->{value} ne "") {
            if ($data->{key} eq "enable_perfdata_sync") {
                $self->{perfdataSync} = $data->{value};
            }
            if ($data->{key} eq "enable_logs_sync") {
                $self->{logSync} = $data->{value};
            }
            if ($data->{key} eq "centcore_cmd_timeout") {
                $self->{cmd_timeout} = $data->{value};
            }
            if ($data->{key} eq "enable_broker_stats") {
                $self->{enable_broker_stats} = $data->{value};
            }
            if ($data->{key} eq "centcore_illegal_characters") {
                $self->{illegal_characters} = $data->{value};
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
    if (defined($data->{value}) && $data->{value} == 1) {
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

    $self->{centreon_dbc} = centreon::common::db->new(
        db => $self->{centreon_config}->{centreon_db},
        host => $self->{centreon_config}->{db_host},
        port => $self->{centreon_config}->{db_port},
        user => $self->{centreon_config}->{db_user},
        password => $self->{centreon_config}->{db_passwd},
        force => 0,
        logger => $self->{logger}
    );
    $self->checkDebugFlag();
    $self->{logger}->writeLogInfo("Instance type: " . $self->{instance_mode});    
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
                foreach my $poller (keys(%{$self->{commandBuffer}})) {
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
                if ($file ne "." && $file ne ".." && $file ne "" && $file !~ /.*_read$/	&& $file !~ /^\..*/) {
                    if ($self->moveCmdFile($self->{cmdDir} . $file) && open(FILE, "< ". $self->{cmdDir} . $file . "_read")) {
                        while (<FILE>){
                            $self->parseRequest($_);
                        }
                        foreach my $poller (keys(%{$self->{commandBuffer}})) {
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

        # Get PerfData on Engine Poller
        if ((defined($self->{difTime}) && $self->{timeBetween2SyncPerf} <= $self->{difTime}) || $self->{timeSyncPerf} == 0){
            # Check Activity profile Status
            $self->checkProfile();
            
            # Check debug Flag
            $self->checkDebugFlag();

            $self->getAllBrokerStats();
                  
            $self->{timeSyncPerf} = time();
        }

        sleep(1);
    }
    
    $self->{logger}->writeLogInfo("Centcore stop...");
}

1;

__END__

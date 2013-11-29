
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

############################################
## Get all perfdata files
#
sub GetAllNagiosServerPerfData {
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `id` FROM `nagios_server` WHERE `localhost` = '0' AND `ns_activate` = '1'");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return -1;
    }
    while (my $data = $sth->fetchrow_hashref()) {
        if (!$self->{stop}) {
            return ;
        }
        if ($self->{perfdataSync} == 1) {
            $self->GetPerfData($data->{'id'});
        }
        if ($self->{logSync} == 1) {
            $self->GetLogFile($data->{'id'});
        }
        if ($self->{enable_broker_stats} == 1) {
            $self->getBrokerStats($data->{'id'});
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

    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT cbi.config_value FROM cfg_centreonbroker_info as cbi, cfg_centreonbroker as cb WHERE cb.config_id = cbi.config_id AND cbi.config_group = 'stats' AND cbi.config_key = 'fifo' AND cb.ns_nagios_server = '".$poller_id."'");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error poller broker pipe");
        return -1;
    }
    while (my $data = $sth->fetchrow_hashref()) {

        # Get poller Configuration
        $server_info = $self->getServerConfig($poller_id);
        $port = checkSSHPort($server_info->{'ssh_port'});

        # Copy the stat file into a buffer

        $cmd = "$self->{ssh} -q $server_info->{'ns_ip_address'} -p $port 'cat \"$data->{config_value}\" > $statPipe'";
        ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => $self->{cmd_timeout}
                                                              );
        if ($lerror == -1) {
            $self->{logger}->writeLogError("Could not read pipe ".$data->{'config_value'}." on poller ".$server_info->{'ns_ip_address'});
        }         
        if (defined($stdout) && $stdout) {
            $self->{logger}->writeLogInfo("Result : $stdout");
        }

        $cmd = "$self->{scp} -P $port $server_info->{'ns_ip_address'}:$statPipe $destFile/broker-stats-$poller_id.dat >> /dev/null";
        # Get the stats file
        ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => $self->{cmd_timeout}
                                                              );
        if (defined($stdout) && $stdout) {
            $self->{logger}->writeLogInfo("Result : $stdout");
        }
    }
    return 0;
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

    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `id` FROM `nagios_server` WHERE `localhost` = '1' ORDER BY ns_activate DESC LIMIT 1");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return undef;
    }
    my $id = $sth->fetchrow_hashref();
    return $id->{'id'};
}

sub getServerConfig($){
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT * FROM `nagios_server` WHERE `id` = '" . $_[0] . "' AND `ns_activate` = '1' LIMIT 1");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return undef;
    }
    my $data = $sth->fetchrow_hashref();

    # Get Nagios User
    $data->{'nagios_user'} = $self->getNagiosConfigurationField($_[0], 'nagios_user');
    return $data;
}

#####################################
## Get perfdata file path
#
sub getPerfDataFile($$){
    my $self = shift;
    my ($filename);
    my ($poller_id) = @_;

    # Create request
    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `nagios_perfdata` FROM `nagios_server` WHERE `id` = '".$poller_id."' LIMIT 1");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting perfdata file");
        return "";
    }
    my $data = $sth->fetchrow_hashref();
    $filename = $data->{'nagios_perfdata'};

    return $filename;
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
sub sendExternalCommand($$){
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
    if (defined($server_info->{'ns_ip_address'})) {
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
            $self->{logger}->writeLogInfo("External command : ".$server_info->{'ns_ip_address'}." ($id) : \"".$cmd."\"");
	    $cmd2 = "$self->{ssh} -q ". $server_info->{'ns_ip_address'} ." -p $port \"$self->{echo} '".$cmd."' >> ".$command_file."\"";
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


#######################################
## Get perfdata on a specific poller
#
sub GetPerfData($){
    my $self = shift;
    # init Values
    my ($id) = @_;
    my ($lerror, $stdout, $cmd);

    # Get Server Infos
    my $server_info = $self->getServerConfig($id);
    my $port = checkSSHPort($server_info->{'ssh_port'});;
 
    my $distantconnexion = $server_info->{'ns_ip_address'};

    # Where is perfdata file on remote poller 
    my $distantperffile = $self->getPerfDataFile($id);
    my $distantperffile_buffer = $distantperffile . ".buff";

    if (!defined($distantperffile_buffer) || !$distantperffile_buffer) {
        $self->{logger}->writeLogError("perfdata file not configured for poller $id ($distantconnexion)");
        return;
    }

    # Build destination directory reserved for this poller
    my $localbasevardir = $self->{centreon_config}->{VarLib} . "/perfdata/$id";

    # check if directory exists
    if (!-d $localbasevardir) {
        mkpath $localbasevardir;
    }

    my $localtmpperffile = "$localbasevardir/service-perfdata";
    my $localperffile = $self->getPerfDataFile($self->getLocalServerID());
    my $move_cmd = "rm -f $distantperffile_buffer 2> /dev/null; cp $distantperffile $distantperffile_buffer 2> /dev/null ; echo \"# New File\" > $distantperffile";

    # Get Perfdata file
    if (!defined($distantperffile)) {
        $self->{logger}->writeLogError("Cannot get perfdata file. Unkown perfdata file on poller $id");
        return ;
    }
    
    # Rename perfdata file
    $cmd = "$self->{ssh} ". $server_info->{'ns_ip_address'} ." -p $port '".$move_cmd."'";
    $self->{logger}->writeLogDebug($cmd);
    
    ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                          logger => $self->{logger},
                                                          timeout => 120
                                                          );
    if (defined($stdout) && $stdout){
        $self->{logger}->writeLogInfo("Result : $stdout");
    }

    # Get Perfdata File
    $self->{logger}->writeLogDebug("$self->{scp} -P $port $distantconnexion:$distantperffile_buffer $localtmpperffile");
    $cmd = "$self->{scp} -P $port $distantconnexion:$distantperffile_buffer $localtmpperffile 2>> /dev/null";
    ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                          logger => $self->{logger},
                                                          timeout => 300
                                                          );
    if (defined($stdout) && $stdout){
        $self->{logger}->writeLogInfo("Result : $stdout");
    }

    # Write data from distant poller on local file for centstorage
    if (-f $localtmpperffile){
        # Concat poller perfdata to central perfdata.
        $self->{logger}->writeLogDebug("cat $localtmpperffile >> $localperffile");
        $cmd = "cat $localtmpperffile >> $localperffile";
        
        ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => 300
                                                             );

        # Remove old file
        if (!unlink($localtmpperffile)) {
            $self->{logger}->writeLogError("Cannot Remove performance data file : $localtmpperffile");
        }
    }
}

##
# Checks if rotation occured,
#
sub checkRotation($$$$$) {
    my $self = shift;
    my $instanceId = $_[0];
    my $lastUpdate = $_[1];
    my $remoteConnection = $_[2];
    my $localLogFile = $_[3];
    my $port = $_[4];
    my ($lerror, $stdout, $cmd);
    
    my $archivePath = $self->getNagiosConfigurationField($instanceId, 'log_archive_path');
    my $getLastCmd = 'echo "$(find '.$archivePath.' -type f -exec stat -c "%Z:%n" {} \; | sort | tail -1)"';
    $cmd = "$self->{ssh} -p $port -q $remoteConnection '".$getLastCmd."'";
    
    ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                          logger => $self->{logger},
                                                          timeout => 120
                                                          );
    my $updateTime = $1;
    my $fileName = $2;
    if (defined($updateTime) && defined($lastUpdate) && $updateTime > $lastUpdate) {
        $cmd = "$self->{scp} -P $port $remoteConnection:$fileName $localLogFile.rotate > /dev/null";
        
        ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => 120
                                                              );
        $self->{logger}->writeLogInfo("Info: copied rotated file for instance $instanceId");
    }
}

##################################
## Get Log files on the specific 
## poller
#
sub GetLogFile($) {
    my $self = shift;
    # Init values
    my $id = $_[0];
    my $last_access;
    my ($lerror, $stdout, $cmd);

    # Get Server informations
    my $server_info = $self->getServerConfig($id);
    my $port = checkSSHPort($server_info->{'ssh_port'});
    
    # Check configuration
    my $distantconnexion = $server_info->{'ns_ip_address'};
    if (!defined($distantconnexion)) {
        $self->{logger}->writeLogError("IP address not defined for poller $id");
        return -1;
    }

    # Set local directory
    my $localDir = $self->{centreon_config}->{VarLib} . "/log/$id/";

    # Create tmp directory
    mkpath $localDir if (!-d $localDir);

    # Get logs if dir exists
    if (-d $localDir) {
        # Get distant log file path
        my $distantlogfile = $self->getNagiosConfigurationField($id, "log_file");;
        my $locallogfile = $localDir."nagios.log";

        # check if we can do the transfert
        if (defined($distantconnexion) && defined($distantlogfile)) {
            # Check if nagios.log file is up to date
            my $flag;
            if (-f $localDir.".nagios.log.flag") {
                # Cet old flag
                $cmd = "cat " . $localDir . ".nagios.log.flag";
                ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                      logger => $self->{logger},
                                                                      timeout => 120
                                                                      );
                $stdout =~ s/\n//g;

                $self->checkRotation($id, $stdout, $distantconnexion, $locallogfile, $port);

                # Check update 
                $cmd = "$self->{ssh} -p $port -q $distantconnexion 'stat  -c \'STAT_RESULT=%Y\' $distantlogfile'";
                $self->{logger}->writeLogDebug("Get Log Files - stat: $cmd");
                
                ($lerror, my $last_access) = centreon::common::misc::backtick(command => $cmd,
                                                                              logger => $self->{logger},
                                                                              timeout => 120
                                                                              );
                
                $last_access =~ /STAT_RESULT=(\d+)/;
                $last_access = $1;
                $self->{logger}->writeLogDebug("Get Log File - stat: Finished");

                # Check buffer
                if ($stdout !~ $last_access) {
                    $flag = 1;
                } else {
                    $flag = 0;
                }
            } else {
                $flag = 1;
            }

            if ($flag == 1) {
                # Get file with rsync
                $cmd = "$self->{scp} -P $port $distantconnexion:$distantlogfile $locallogfile > /dev/null";
                ($lerror, $stdout, my $exit_code) = centreon::common::misc::backtick(command => $cmd,
                                                                                     logger => $self->{logger},
                                                                                     timeout => 300,
                                                                                     wait_exit => 1
                                                                                     );
                $self->{logger}->writeLogDebug($cmd);
                if (($exit_code >> 8) != 0) {
                    $self->{logger}->writeLogError("Cannot get log file or log file doesn't exists on poller $id");
                }
            }
            # Update or create time buffer
            $cmd = "echo '$last_access' > ".$localDir.".nagios.log.flag";
            ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                  logger => $self->{logger},
                                                                  timeout => $self->{cmd_timeout}
                                                                  );
        }
    } else {
        $self->{logger}->writeLogError("Unable to create $localDir. Can get nagios log file for poller $id");
    }
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
    my $dest = $server_info->{'ns_ip_address'}.":$cfg_dir";

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
                $dest = $server_info->{'ns_ip_address'}.":$cfg_dir";
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

    if (defined($conf->{'ns_ip_address'}) && $conf->{'ns_ip_address'}) {
        # Launch command
        $cmd = "$self->{ssh} -p $port ". $conf->{'ns_ip_address'} ." $self->{sudo} ".$conf->{'init_script'}." ".$options;
        ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => 120
                                                              );
    } else {
        $self->{logger}->writeLogError("Cannot $options Engine for poller $id");
    }

    # Logs Actions
    $self->{logger}->writeLogInfo("Init Script : '$self->{sudo} ".$conf->{'init_script'}." ".$options."' On poller ".$conf->{'ns_ip_address'}." ($id)");
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
            $cmd = "$self->{scp} -P $port /etc/snmp/centreon_traps/$id/centreontrapd.sdb $ns_server->{'ns_ip_address'}:/etc/snmp/centreon_traps/ 2>&1";
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
        my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `id`, `snmp_trapd_path_conf` FROM `nagios_server` WHERE `ns_activate` = '1' AND `localhost` = '0'");
        return if ($status == -1);
        while (my $server = $sth->fetchrow_hashref()) {
            # Get configuration
            my $ns_server = $self->getServerConfig($server->{'id'});
            my $port = checkSSHPort($ns_server->{'ssh_port'});

            if ($id == 0) {
                $cmd = "$self->{scp} -P $port /etc/snmp/centreon_traps/$id/centreontrapd.sdb $ns_server->{'ns_ip_address'}:$ns_server->{'snmp_trapd_path_conf'} 2>&1";
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
## Test Engine configuration
#
sub testConfig($) {
    my $self = shift;
    my $id = $_[0];
    my ($lerror, $stdout, $cmd);

    my $cfg_dir = $self->getNagiosConfigurationField($id, "cfg_dir");
    my $data = $self->getServerConfig($id);
    my $port = checkSSHPort($data->{'ssh_port'});
    my $distantconnexion = $data->{'ns_ip_address'};
    $cmd = "$self->{ssh} -p ".$port." $distantconnexion $self->{sudo} ".$data->{'nagios_bin'}." -v $cfg_dir/nagios.cfg";
    ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                          logger => $self->{logger},
                                                          timeout => 60
                                                          );
    $self->{logger}->writeLogInfo("Test Config Result: $stdout");
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
        $cmd = "$self->{rsyncWT} --port=$port -c ". $ns_server->{'ns_ip_address'}. ":".$data->{'log_archive_path'}."/*.log $self->{centreon_config}->{VarLib}/log/".$ns_server->{'id'}."/archives/ 2>> /dev/null";
        $self->{logger}->writeLogDebug($cmd);
        ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => 300
                                                              );
    } else {
        # synchronize Archives for all pollers
        my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `id` FROM `nagios_server` WHERE `ns_activate` = '1' AND `localhost` = '0'");
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

    if (defined($ns_server->{'ns_ip_address'}) && $ns_server->{'ns_ip_address'}) {
        # Launch command
        if (defined($ns_server->{'localhost'}) && $ns_server->{'localhost'}) {
            $cmd = "$self->{sudo} ".$ns_server->{'nagios_bin'};
            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                              logger => $self->{logger},
                                                              timeout => 60
                                                              );
        } else {
            $cmd = "$self->{ssh} -p $port ". $ns_server->{'ns_ip_address'} ." ".$ns_server->{'nagios_bin'};
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

    if (defined($ns_server->{'ns_ip_address'}) && $ns_server->{'ns_ip_address'}
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
            $cmd = "$self->{ssh} -p $port ". $ns_server->{'ns_ip_address'} ." $self->{sudo} ".$ns_server->{'init_script_centreontrapd'}." reload";
            $self->{logger}->writeLogDebug($cmd);
            ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                  logger => $self->{logger},
                                                                  timeout => 60
                                                                  );
        }
        $self->{logger}->writeLogInfo("Reload CentreonTrapd on poller $id ($ns_server->{'ns_ip_address'})");
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
    } elsif ($action =~ /^TEST\:([0-9]*)/){
        # Experimental
        $self->testConfig($1);
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
    
    my $request = "SELECT * FROM options WHERE `key` IN ('enable_perfdata_sync', 'enable_logs_sync', 'centcore_cmd_timeout', 'enable_broker_stats')";
    my ($status, $sth) =  $self->{centreon_dbc}->query($request);
    return -1 if ($status == -1);
    while ((my $data = $sth->fetchrow_hashref())) {
        if (defined($data->{'key'}) && $data->{'key'} ne "" && defined($data->{'value'}) && $data->{'value'} ne "") {
            if ($data->{'key'} eq "enable_perfdata_sync") {
                $self->{perfdataSync} = $data->{'value'};
            } 
            if ($data->{'key'} eq "enable_logs_sync") {
                $self->{logSync} = $data->{'value'};
            }
            if ($data->{'key'} eq "centcore_cmd_timeout") {
                $self->{cmd_timeout} = $data->{'value'};
            }
            if ($data->{'key'} eq "enable_broker_stats") {
                $self->{enable_broker_stats} = $data->{'value'};
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

            $self->GetAllNagiosServerPerfData();        
            $self->{timeSyncPerf} = time();
        }

        sleep(1);
    }
    
    $self->{logger}->writeLogInfo("Centcore stop...");
}

1;

__END__


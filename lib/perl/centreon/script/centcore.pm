
package centreon::script::centcore;

use strict;
use File::Copy;
use File::Path qw(mkpath);
use centreon::script;
use centreon::common::db;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centcore",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
        noroot => 1
    );

    bless $self, $class;

    $installedPath = "@CENTREON_DIR@";
    $cmdFile = "@CENTREON_VARLIB@/centcore.cmd";
    $cmdDir = "@CENTREON_VARLIB@/centcore/";
    $VARLIB = "@CENTREON_VARLIB@";

    $echo = "/bin/echo";
    $ssh = "@BIN_SSH@";
    $scp = "@BIN_SCP@";
    $rsync = "rsync";
    $rsyncWT = $rsync;
    $sudo = "sudo";

    $ssh    .= " -o ConnectTimeout=$timeout -o StrictHostKeyChecking=yes -o PreferredAuthentications=publickey -o ServerAliveInterval=10 -o ServerAliveCountMax=3 -o Compression=yes ";
    $rsync  .= " --timeout=$timeout ";
    $scp    .= " -o ConnectTimeout=$timeout -o StrictHostKeyChecking=yes -o PreferredAuthentications=publickey -o ServerAliveInterval=10 -o ServerAliveCountMax=3 -o Compression=yes ";

    $timeout = 5; 
    $timeBetween2SyncPerf = 60;
    $perfdataSync = 0;
    $logSync = 0;
    $stop = 1;

    $timeSyncPerf = 0;
    $difTime = 10;
    
    $self->set_signal_handlers;

    return $self;
}

use vars qw($debug $LOG %status $generalcounter $stop $method $perfdataSync $logSync $timeBetween2SyncPerf);
use vars qw($con $ssh $scp $rsync $rsyncWT $echo $sudo $installedPath $timeout);
use vars qw(%commandBuffer);

#######################################
# Include Configuration Data

sub init_config {
    my $file = $_[0];
    my $type = $_[1];
    
    unless (my $return = do $file) {
        writeLogFile("couldn't parse $file: $@") if $@;
        writeLogFile("couldn't do $file: $!") unless defined $return;
        writeLogFile("couldn't run $file") unless $return;
        if ($type == 1) {
            writeLogFile("Quit program");
            exit(1);
        }
    }
}

sub catch_zap {
    $stop = 0;
    writeLogFile("Receiving order to stop...");
}

sub catch_reload {
    writeLogFile("Receiving order to reload...");
    init_config("@CENTREON_ETC@/conf.pm", 0);
    open my $centcore_fh, '>>', $LOG;
    open STDOUT, '>&', $centcore_fh;
    open STDERR, '>&', $centcore_fh;
}

###########################################################
# Init signal actions
#
$SIG{'TERM'}      = \&catch_zap;
$SIG{'HUP'}      = \&catch_reload;

###########################################################
# Function to move command file on temporary file
#
sub moveCmdFile($){
    my $CMDFILE = $_[0];
    if (move($CMDFILE, $CMDFILE."_read")) {
        return(1);
    } else {
        writeLogFile("Cannot move $CMDFILE to ".$CMDFILE."_read");
        return(0);
    }
}

###########################################################
# Function to remove temporary command file 
#
sub removeTmpCmdFile($){
    my $CMDFILE = $_[0];
    if (unlink($CMDFILE."_read")){
        return(1);
    } else {
        writeLogFile("Can't remove temporary command file.");
        return(0);
    }
}

############################################
## Get all perfdata files
#
sub GetAllNagiosServerPerfData(){
    CheckMySQLConnexion();
    my $sth2 = $con->prepare("SELECT `id` FROM `nagios_server` WHERE `localhost` = '0' AND `ns_activate` = '1'");
    if (!$sth2->execute()) {
        writeLogFile("Error when getting server properties : ".$sth2->errstr);
        return ;
    }
    while (my $data = $sth2->fetchrow_hashref()) {
        if (!$stop) {
            $sth2->finish();
            $con->disconnect();
            return ;
        }
        if ($perfdataSync == 1) {
            GetPerfData($data->{'id'});
        }
        if ($logSync == 1) {
            GetLogFile($data->{'id'});
        }
        getBrokerStats($data->{'id'});
    }
    $sth2->finish();
    $con->disconnect();
    return;
}

###########################################
## Get a instant copy of the broker stat 
## fifo
#
sub getBrokerStats($) {
    my ($poller_id) = @_;
    my $port = "";
    my $statPipe = "/tmp/.centreon-broker-stats.dat";
    my $destFile = $VARLIB."/broker-stats";
    my $server_info;

    # Check Cache directory
    if (!-d $destFile) {
        writeLogFile("Create data directory for broker-stats: $destFile");
        mkpath($destFile);
    }

    # Check MySQL Configuration 
    CheckMySQLConnexion();

    my $sth2 = $con->prepare("SELECT cbi.config_value FROM cfg_centreonbroker_info as cbi, cfg_centreonbroker as cb WHERE cb.config_id = cbi.config_id AND cbi.config_group = 'stats' AND cbi.config_key = 'fifo' AND cb.ns_nagios_server = '".$poller_id."'");
    if (!$sth2->execute()) {
        writeLogFile("Error poller broker pipe : ".$sth2->errstr);
        return ;
    }
    while (my $data = $sth2->fetchrow_hashref()) {

        # Get poller Configuration
        $server_info = getServerConfig($poller_id);
        $port = checkSSHPort($server_info->{'ssh_port'});

        # Copy the stat file into a buffer
        my $stdout = `$ssh -q $server_info->{'ns_ip_address'} -p $port 'cat \"$data->{'config_value'}" > $statPipe'`;
        if (defined($stdout) && $stdout){
            writeLogFile("Result : $stdout\n");
        }

        # Get the stats file
        $stdout = `$scp -P $port $server_info->{'ns_ip_address'}:$statPipe $destFile/broker-stats-$poller_id.dat >> /dev/null`;
        if (defined($stdout) && $stdout){
            writeLogFile("Result : $stdout\n");
        }
    }
    return;
}

# -------------------
#      Functions 
# -------------------

sub getNagiosConfigurationField($$){
    CheckMySQLConnexion();
    my $sth2 = $con->prepare("SELECT ".$_[1]." FROM `cfg_nagios` WHERE `nagios_server_id` = '".$_[0]."' AND nagios_activate = '1'");
    if (!$sth2->execute()) {
        writeLogFile("Error when getting server properties : ".$sth2->errstr);
    }
    my $data = $sth2->fetchrow_hashref();
    $sth2->finish();
    return $data->{$_[1]};
}

sub getLocalOptionsField($){
    CheckMySQLConnexion();
    my $sth2 = $con->prepare("SELECT `value` FROM `options` WHERE `key` LIKE '".$_[0]."' LIMIT 1");
    if (!$sth2->execute()) {
        writeLogFile("Error when getting general options properties : ".$sth2->errstr);
    }
    my $data = $sth2->fetchrow_hashref();
    $sth2->finish();
    return $data->{'value'};
}

sub getLocalServerID(){
    CheckMySQLConnexion();
    my $sth2 = $con->prepare("SELECT `id` FROM `nagios_server` WHERE `localhost` = '1'");
    if (!$sth2->execute()) {
        writeLogFile("Error when getting server properties : ".$sth2->errstr);
    }
    my $id = $sth2->fetchrow_hashref();
    $sth2->finish();
    return $id->{'id'};
}

sub getServerConfig($){
    CheckMySQLConnexion();
    my $sth2 = $con->prepare("SELECT * FROM `nagios_server` WHERE `id` = '".$_[0]."' AND `ns_activate` = '1'");
    if (!$sth2->execute()) {
        writeLogFile("Error when getting server properties : ".$sth2->errstr."\n");
    }
    my $data = $sth2->fetchrow_hashref();
    $sth2->finish();
    
    # Get Nagios User
    $data->{'nagios_user'} = getNagiosConfigurationField($_[0], 'nagios_user');
    return $data;
}

#####################################
## Get perfdata file path
#
sub getPerfDataFile($$){
    my ($filename, $sth2, $data);
    my ($con, $poller_id) = @_;

    # Create request
    my $request = "SELECT `nagios_perfdata` FROM `nagios_server` WHERE `id` = '".$poller_id."'";
    $sth2 = $con->prepare($request);
    if (!$sth2->execute()) {
        writeLogFile("Error when getting perfdata file : " . $sth2->errstr . "");
        return "";
    }
    $data = $sth2->fetchrow_hashref();
    $filename = $data->{'nagios_perfdata'};

    # Free
    $sth2->finish();
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
    # Init Parameters
    my ($id, $cmd) = @_;
    my $stdout;

    # Get server informations
    my $server_info = getServerConfig($id);
    my $port = checkSSHPort($server_info->{'ssh_port'});
    
    # Get command file 
    my $command_file = getNagiosConfigurationField($id, "command_file");

    # check if ip address is defined
    if (defined($server_info->{'ns_ip_address'})) {
        $cmd =~ s/\\/\\\\/g;
        if ($server_info->{'localhost'} == 1) {
            my $result = waitPipe($command_file);
            if ($result == 0) {
                writeLogFile("External command on Central Server: ($id) : \"".$cmd."\"");
                my $cmd = "echo \"".$cmd."\" >> ".$command_file."\n";
                $stdout = `$cmd`;        
            } else {
                writeLogFile("Cannot write external command on central server : \"".$cmd."\"");
            }
        } else {
            writeLogFile("External command : ".$server_info->{'ns_ip_address'}." ($id) : \"".$cmd."\"");
            my $cmd = "$ssh -q ". $server_info->{'ns_ip_address'} ." -p $port 'echo \"".$cmd."\" >> ".$command_file."'\n";
            $stdout = `$cmd`;
        }

        if (defined($stdout) && $stdout){
            writeLogFile("Result : $stdout\n");
        }

    } else {
        writeLogFile("Ip address not defined for poller $id");
    }
    
    $commandBuffer{$id} = "";
}

#######################################
## Wait Nagios Pipe availability
#
sub waitPipe($) {
    my ($pid) = @_;
    my $i = 0;
    while (! -p $pid) {
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
    # init Values
    my ($id) = @_;

    # Check MySQL Connection
    CheckMySQLConnexion();
    
    # Get Server Infos
    my $server_info = getServerConfig($id);
    my $port = checkSSHPort($server_info->{'ssh_port'});;
 
    my $distantconnexion = $server_info->{'ns_ip_address'};

    # Where is perfdata file on remote poller 
    my $distantperffile = getPerfDataFile($con, $id);
    my $distantperffile_buffer = $distantperffile . ".buff";

    if (!defined($distantperffile_buffer) || !$distantperffile_buffer) {
        writeLogFile("perfdata file not configured for poller $id ($distantconnexion)");
        return;
    }

    # Build destination directory reserved for this poller
    my $localbasevardir = "$VARLIB/perfdata/$id";

    # check if directory exists
    if (!-d $localbasevardir) {
        mkpath $localbasevardir;
    }

    my $localtmpperffile = "$localbasevardir/service-perfdata";
    my $localperffile = getPerfDataFile($con, getLocalServerID());
    my $move_cmd = "rm -f $distantperffile_buffer 2> /dev/null; cp $distantperffile $distantperffile_buffer 2> /dev/null ; echo \"# New File\" > $distantperffile";

    # Get Perfdata file
    if (!defined($distantperffile)) {
        writeLogFile("Cannot get perfdata file. Unkown perfdata file on poller $id");
    } else {
        # Rename perfdata file
        my $cmd = "$ssh ". $server_info->{'ns_ip_address'} ." -p $port '".$move_cmd."'";
        writeLogFile($cmd) if ($debug);
        my $stdout = `$cmd`;
        if (defined($stdout) && $stdout){
            writeLogFile("Result : $stdout\n");
        }

        # Get Perfdata File
        writeLogFile("$scp -P $port $distantconnexion:$distantperffile_buffer $localtmpperffile") if ($debug);
        $stdout = `$scp -P $port $distantconnexion:$distantperffile_buffer $localtmpperffile 2>> /dev/null`;
        if (defined($stdout) && $stdout){
            writeLogFile("Result : $stdout\n");
        }

        # Write data from distant poller on local file for centstorage
        if (-f $localtmpperffile){
            # Concat poller perfdata to central perfdata.
            writeLogFile("cat $localtmpperffile >> $localperffile") if ($debug);
            `cat $localtmpperffile >> $localperffile`;

            # Remove old file
            if (!unlink($localtmpperffile)) {
                writeLogFile("Cannot Remove performance data file : $localtmpperffile");
            }
        }
    }
}

##
# Checks if rotation occured,
#
sub checkRotation($$$$$) {
   my $instanceId = $_[0];
   my $lastUpdate = $_[1];
   my $remoteConnection = $_[2];
   my $localLogFile = $_[3];
   my $port = $_[4];

   my $archivePath = getNagiosConfigurationField($instanceId, 'log_archive_path');
   my $getLastCmd = 'echo "$(find '.$archivePath.' -type f -exec stat -c "%Z:%n" {} \; | sort | tail -1)"';
   my $check_cmd = "$ssh -p $port -q $remoteConnection '".$getLastCmd."'";
   my $result = `$check_cmd`;
   $result =~ /(\d+):(.+)/;
   my $updateTime = $1;
   my $fileName = $2;
   if (defined($updateTime) && defined($lastUpdate) && $updateTime > $lastUpdate) {
       my $cmd = "$scp -P $port $remoteConnection:$fileName $localLogFile.rotate > /dev/null";
       `$cmd`;
       writeLogFile("Info: copied rotated file for instance $instanceId");
   }
}

##################################
## Get Log files on the specific 
## poller
#
sub GetLogFile($){
    # Init values
    my $id = $_[0];
    my $last_access;

    # Get Server informations
    my $server_info = getServerConfig($id);
    my $port = checkSSHPort($server_info->{'ssh_port'});
    
    # Check configuration
    my $distantconnexion = $server_info->{'ns_ip_address'};
    if (!defined($distantconnexion)) {
        writeLogFile("IP address not defined for poller $id");    
    }

    # Set local directory
    my $localDir = $VARLIB."/log/$id/";

    # Create tmp directory
    mkpath $localDir if (!-d $localDir);

    # Get logs if dir exists
    if (-d $localDir){

    # Get distant log file path
    my $distantlogfile = getNagiosConfigurationField($id, "log_file");;
    my $locallogfile = $localDir."nagios.log";

    # check if we can do the transfert
    if (defined($distantconnexion) && defined($distantlogfile)) {        
        # Check if nagios.log file is up to date
        my $flag;
        if (-f $localDir.".nagios.log.flag") {
            # Cet old flag
            my $cmd = $localDir.".nagios.log.flag";
            my $value = `cat $cmd`;
            $value =~ s/\n//g;

            checkRotation($id, $value, $distantconnexion, $locallogfile, $port);

            # Check update 
            my $check_cmd = "$ssh -p $port -q $distantconnexion 'stat  -c \'STAT_RESULT=%Y\' $distantlogfile'";
            writeLogFile("Get Log Files - stat: $check_cmd") if ($debug);
            $last_access = `$check_cmd`;
            $last_access =~ /STAT_RESULT=(\d+)/;
            $last_access = $1;
            writeLogFile("Get Log File - stat: Finished") if ($debug);

            # Check buffer
            if ($value !~ $last_access) {
                $flag = 1;
            } else {
                $flag = 0;
            }

            } else {
                $flag = 1;
            }

            if ($flag == 1) {
                # Get file with rsync
                my $cmd = "$scp -P $port $distantconnexion:$distantlogfile $locallogfile > /dev/null";
                `$cmd`;
                writeLogFile($cmd) if ($debug);
                if ($? ne 0) {
                    writeLogFile("Cannot get log file or log file doesn't exists on poller $id");
                }
            }

            # Update or create time buffer
            my $buffer_cmd = "echo '$last_access' > ".$localDir.".nagios.log.flag";
            `$buffer_cmd`; 
        }
    } else {
        writeLogFile("Unable to create $localDir. Can get nagios log file for poller $id");
    }
}

##################################################
# Send config files to a remote server 
#
sub sendConfigFile($){
    # Init Values
    my $id = $_[0];
    my $debug = 0;

    my $cfg_dir = getNagiosConfigurationField($id, "cfg_dir");
    my $server_info = getServerConfig($id);
    my $port = checkSSHPort($server_info->{'ssh_port'});

    if (!defined($cfg_dir) || $cfg_dir =~ //) {
        writeLogFile("Engine configuration file is empty for poller $id. Please check nagios.cfg file.");
        return;
    }

    my $origin = $installedPath."/filesGeneration/nagiosCFG/".$id."/*";
    my $dest = $server_info->{'ns_ip_address'}.":$cfg_dir";

    # Send data with SCP
    writeLogFile("Start: Send config files on poller $id");
    my $cmd = "$scp -P $port $origin $dest > /dev/null";
    my $stdout = `$cmd`;
    if (defined($stdout) && $stdout){
        writeLogFile("Result : $stdout\n");
    }
    writeLogFile("End: Send config files on poller $id");

    # Send configuration for Centreon Broker
    if ( -e $installedPath."/filesGeneration/broker/".$id) {
        # Check availability of broker files.
        my $count = 0;
        opendir(my $dh, $installedPath."/filesGeneration/broker/".$id);
        while(readdir $dh) {
            $count++;
        }
        closedir $dh;

        if ($count > 2) {
            writeLogFile("Start: Send Centreon Broker config files on poller $id") if ($debug);

            if ($server_info->{'localhost'} == 0) {
                $cfg_dir = $server_info->{'centreonbroker_cfg_path'};
                $origin = $installedPath."/filesGeneration/broker/".$id."/*.xml";
                $dest = $server_info->{'ns_ip_address'}.":$cfg_dir";
                $cmd = "$scp -P $port $origin $dest 2>&1";
                my $stdout = `$cmd`;
                if (defined($stdout) && $stdout) {
                    writeLogFile("Result : $stdout\n");
                }
            } else {
                my $cmd = "cp $origin $cfg_dir 2>&1";
                my $stdout = `$cmd`;
                if (defined($stdout) && $stdout) {
                    writeLogFile("Result : $stdout\n");
                }
            }
            writeLogFile("End: Send Centreon Broker config files on poller $id") if ($debug);
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
    my $id = $_[0];
    my $options = $_[1];
    my ($cmd, $stdout);

    # Get configuration
    my $conf = getServerConfig($id);
    my $port = checkSSHPort($conf->{'ssh_port'});

    if (!defined($conf)) {
        writeLogFile("Poller $id doesn't exists...");
        writeLogFile("Cannot manage undefined poller...");
        return;
    }

    if (defined($conf->{'ns_ip_address'}) && $conf->{'ns_ip_address'}) {
        # Launch command
        $cmd = "$ssh -p $port ". $conf->{'ns_ip_address'} ." $sudo ".$conf->{'init_script'}." ".$options;
        $stdout = `$cmd`;
    } else {
        writeLogFile("Cannot $options Nagios for poller $id");
    }

    # Logs Actions
    writeLogFile("Init Script : '$sudo ".$conf->{'init_script'}." ".$options."' On poller ".$conf->{'ns_ip_address'}." ($id)");
    my $line;
    if (defined($stdout)) {
        foreach $line (split(/\n/, $stdout)){
            writeLogFile("Engine : ".$line);
        }
    }
}

##################################################
# Function for synchronize SNMP trap configuration
# 
sub syncTraps($) {
    my $id = $_[0];
    my $stdout;

    # Check MySQL Connexion
    CheckMySQLConnexion();

    if ($id != 0) {
        # Get configuration
        my $ns_server = getServerConfig($id);
        my $port = checkSSHPort($ns_server->{'ssh_port'});

        if ($id != 0 && $ns_server->{'localhost'} == 0) {
            my $cmd = "$scp -P $port /etc/snmp/centreon_traps/*.ini $ns_server->{'ns_ip_address'}:/etc/snmp/centreon_traps/";
            writeLogFile($cmd) if ($debug);
            $stdout = `$cmd`;
            if (defined($stdout) && $stdout){
                writeLogFile("Result : $stdout\n");
            }

            writeLogFile("ls -l /etc/snmp/centreon_traps/*.conf 2>> /dev/null | wc -l") if ($debug);
            my $ls = `ls -l /etc/snmp/centreon_traps/*.conf 2>> /dev/null | wc -l`;
            if ($ls > 1) {
                writeLogFile("$rsync --port=$port -c /etc/snmp/centreon_traps/*.conf ". $ns_server->{'ns_ip_address'} .":/etc/snmp/centreon_traps/") if ($debug);                
                $stdout = `$rsync --port=$port -c /etc/snmp/centreon_traps/*.conf $ns_server->{'ns_ip_address'}:/etc/snmp/centreon_traps/`;
                if (defined($stdout) && $stdout){
                    writeLogFile("Result : $stdout\n");
                }
            }
        }
    } else {
        # synchronize Archives for all pollers
        my $sth2 = $con->prepare("SELECT `id` FROM `nagios_server` WHERE `ns_activate` = '1' AND `localhost` = '0'");
        if (!$sth2->execute) {
            writeLogFile("Error:" . $sth2->errstr);
            return ;
        } else {
            while (my $server = $sth2->fetchrow_hashref()) {
                # Get configuration
                my $ns_server = getServerConfig($server->{'id'});
                my $port = checkSSHPort($ns_server->{'ssh_port'});

                if ($id == 0) {
            my $cmd = "$scp -P $port /etc/snmp/centreon_traps/*.ini $ns_server->{'ns_ip_address'}:/etc/snmp/centreon_traps/";
                    writeLogFile($cmd) if ($debug);
                    $stdout = `$cmd`;
                    if (defined($stdout) && $stdout){
                        writeLogFile("Result : $stdout\n");
                    }
            $cmd = "ls -l /etc/snmp/centreon_traps/*.conf 2>> /dev/null | wc -l";
                    writeLogFile($cmd) if ($debug);
                    my $ls = `$cmd`;
                    if ($ls > 1) {
            $cmd = "$rsync --port=$port -c /etc/snmp/centreon_traps/*.conf $ns_server->{'ns_ip_address'}:/etc/snmp/centreon_traps/";
                        writeLogFile($cmd) if ($debug);
                        $stdout = `$cmd`;
                        if (defined($stdout) && $stdout){
                            writeLogFile("Result : $stdout\n");
                        }
                    }
                }
            }
        }
    }
}

###################################
## Test Engine configuration
#
sub testConfig($) {
    my $id = $_[0];

    my $cfg_dir = getNagiosConfigurationField($id, "cfg_dir");
    my $data = getServerConfig($id);
    my $port = checkSSHPort($data->{'ssh_port'});
    my $distantconnexion = $data->{'ns_ip_address'};
    my $cmd = "$ssh -p ".$port." $distantconnexion $sudo ".$data->{'nagios_bin'}." -v $cfg_dir/nagios.cfg";
    my $stdout = `$cmd`;
    writeLogFile("$stdout\n");
}

###################################
## Sync engine Logs Archives in the 
## central Server 
#
sub syncArchives($) {
    my $id = $_[0];

    # Check MySQL Connexion
    CheckMySQLConnexion();

    # Get configuration
    my $ns_server = getServerConfig($id);
    my $port = checkSSHPort($ns_server->{'ssh_port'});

    if ($id != 0) {
        # Sync Archive for one poller
        if (! -d "$VARLIB/log/".$ns_server->{'id'}."/archives/") {
            if (! -d "$VARLIB/log/".$ns_server->{'id'}."/archives/") {
                mkpath "$VARLIB/log/".$ns_server->{'id'}."/archives/";
            }
        }
        my $sth = $con->prepare("SELECT `log_archive_path` FROM `cfg_nagios` WHERE `nagios_server_id` = '".$id."' AND `nagios_activate` = '1'");
        if ($sth->execute()) {
            my $data = $sth->fetchrow_hashref();

            # Archive Sync
        my $cmd = "$rsync --port=$port -c ". $ns_server->{'ns_ip_address'}. ":".$data->{'log_archive_path'}."/*.log $VARLIB/log/".$ns_server->{'id'}."/archives/";
        writeLogFile($cmd) if ($debug);
            `$rsyncWT --port=$port -c $ns_server->{'ns_ip_address'}:$data->{'log_archive_path'}/*.log $VARLIB/log/$ns_server->{'id'}/archives/ 2>> /dev/null`;
        } else {
            print "Can't get archive path for poller ".$ns_server->{'id'}." (".$ns_server->{'ns_address_ip'}.") -> ".$sth->errstr;
        }
    } else {
        # synchronize Archives for all pollers
        my $sth2 = $con->prepare("SELECT `id` FROM `nagios_server` WHERE `ns_activate` = '1' AND `localhost` = '0'");
        if (!$sth2->execute) {
            writeLogFile("Error:" . $sth2->errstr);
            return ;
        } else { 
            while (my $server = $sth2->fetchrow_hashref()) {
                writeLogFile("Receive Order to synchronize all archives of all pollers");
                syncArchives($server->{'id'});
            }
        }
    }
}

##################################
## Get Monitoring Engine.
#
sub getInfos($) {
    my $id = $_[0];

    # Check MySQL Connexion
    CheckMySQLConnexion();

    # Get configuration
    my $ns_server = getServerConfig($id);
    my $port = checkSSHPort($ns_server->{'ssh_port'});

    if (defined($ns_server->{'ns_ip_address'}) && $ns_server->{'ns_ip_address'}) {
        # Launch command
        my $cmd = "$ssh -p $port ". $ns_server->{'ns_ip_address'} ." ".$ns_server->{'nagios_bin'}."";
    writeLogFile($cmd) if ($debug);
        my $stdout = `$cmd`;
    
        my @tab = split("\n", $stdout);
        foreach my $str (@tab) {
            if ($str =~ m/(Nagios) Core ([\.0-9]*[a-zA-Z0-9\-\.]+)/) {
                writeLogFile("Engine: $1");
                writeLogFile("Version: $2");
                last;
            }
        if ($str =~ m/(Centreon Engine) ([\.0-9]*[a-zA-Z0-9\-\.]+)/) {
                writeLogFile("Engine: $1");
                writeLogFile("Version: $2");
                last;
            } 
        }
    } else {
        writeLogFile("Cannot get informations for poller $id");
    }
}

################################
## Restart SNMPTT Daemon
#
sub restartSNMPTT($) {
    my $id = $_[0];
    my $stdout = "";
    my $cmd = "";
    my $debug = 0;

    # Check MySQL Connexion
    CheckMySQLConnexion();

    # Get configuration
    my $ns_server = getServerConfig($id);
    my $port = checkSSHPort($ns_server->{'ssh_port'});

    if (defined($ns_server->{'ns_ip_address'}) && $ns_server->{'ns_ip_address'}
        && defined($ns_server->{'init_script_snmptt'}) && $ns_server->{'init_script_snmptt'} ne "") {
        # Launch command
        if (defined($ns_server->{'localhost'}) && $ns_server->{'localhost'}) {
            $cmd = "$sudo ".$ns_server->{'init_script_snmptt'}." restart";
            writeLogFile($cmd) if ($debug);
            $stdout = `$cmd`;
        } else {
            $cmd = "$ssh -p $port ". $ns_server->{'ns_ip_address'} ." $sudo ".$ns_server->{'init_script_snmptt'}." restart";
            writeLogFile($cmd) if ($debug);
            $stdout = `$cmd`;
        }
        writeLogFile("Restart SNMPTT on poller $id ($ns_server->{'ns_ip_address'})");
    } else {
        writeLogFile("Cannot restart SNMPTT for poller $id");
    }
}

####################################
## Parse request
#
sub parseRequest($){
    my ($action) = @_;
    if (!$action) {
        return ;
    }
    
    # Checks keys for launching commands 
    if ($action =~ /^RESTART\:([0-9]*)/){
        initEngine($1, "restart");
    } elsif ($action =~ /^RELOAD\:([0-9]*)/){
        initEngine($1, "reload");
    } elsif ($action =~ /^START\:([0-9]*)/){
        initEngine($1, "start");
    } elsif ($action =~ /^STOP\:([0-9]*)/){
        initEngine($1, "stop");
    } elsif ($action =~ /^SENDCFGFILE\:([0-9]*)/){
        sendConfigFile($1);
    } elsif ($action =~ /^TEST\:([0-9]*)/){
        # Experimental
        testConfig($1);
    } elsif ($action =~ /^SYNCTRAP\:([0-9]*)/){
        syncTraps($1);
    } elsif ($action =~ /^RESTARTSNMPTT\:([0-9]*)/){
        restartSNMPTT($1);
    } elsif ($action =~ /^SYNCARCHIVES\:([0-9]*)/){
        syncArchives($1);
    } elsif ($action =~ /^EXTERNALCMD\:([0-9]*)\:(.*)/){
        storeCommands($1, $2);
    } elsif ($action =~ /^GETINFOS\:([0-9]*)/){
        getInfos($1);
    }
}

############################################
## Check Centcore Configuration Profile
#
sub checkProfile() {
    # Check MySQL Connexion
    CheckMySQLConnexion();
    
    my $request = "SELECT * FROM options WHERE `key` IN ('enable_perfdata_sync', 'enable_logs_sync')";
    my $sth = $con->prepare($request);
    if ($sth->execute()) {
        my $data;
        while ($data = $sth->fetchrow_hashref()) {
            if (defined($data->{'key'}) && $data->{'key'} ne "" && defined($data->{'value'}) && $data->{'value'} ne "") {
                if ($data->{'key'} eq "enable_perfdata_sync") {
                    $perfdataSync = $data->{'value'};
                } 
                if ($data->{'key'} eq "enable_logs_sync") {
                    $logSync = $data->{'value'};
                }
            }
        }
    }
    return 0;
}

# Check if debug has been enable into GUI
sub checkDebugFlag($) {
    my ($display) = @_;

    # Check MySQL Connexion
    CheckMySQLConnexion();
    my $oldDebug = $debug;

    my $request = "SELECT * FROM options WHERE `key` IN ('debug_centcore')";
    my $sth = $con->prepare($request);
    if ($sth->execute()) {
        my $data = $sth->fetchrow_hashref();
        if (defined($data->{'value'}) && $data->{'value'} == 1) {
            $debug = 1;
            if ($debug ne $oldDebug && $display) {
                writeLogFile("Enable Debug in Centcore");
            }
        } else {
            $debug = 0;
            if ($debug ne $oldDebug && $display) {
                writeLogFile("Disable Debug in Centcore");
            }
        }
    }
}

# Store commands in order to group commands to send.
sub storeCommands($$) {
    my ($poller_id, $command) = @_;
    
    if (!defined($commandBuffer{$poller_id})) {
        $commandBuffer{$poller_id} = "";
    }
    $commandBuffer{$poller_id} .= $command . "\n";
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->{logger}->redirect_output();
    $self->{logger}->writeLogInfo("Starting centcore engine...");

    checkDebugFlag(0);
        
    while ($stop) {   
        # Read Centcore.cmd
        if (-e $cmdFile) {
            if (moveCmdFile($cmdFile) && open(FILE, "< $cmdFile"."_read")) {
                while (<FILE>){
                    parseRequest($_);
                }
                my $poller;
                foreach $poller (keys(%commandBuffer)) {
                    if (length($commandBuffer{$poller}) != 0) {
                        sendExternalCommand($poller, $commandBuffer{$poller});
                        $commandBuffer{$poller} = "";
                    }
                }
            }
            close(FILE);
            $self->{logger}->writeLogError("Error When removing ".$cmdFile."_read file : $!") if (!unlink($cmdFile."_read"));
        }
            
        # Read Centcore Directory
        if (-d $cmdDir) {
            opendir(my $dh, $cmdDir);
            while (my $file = readdir($dh)) {
                if ($file ne "." && $file ne ".." && $file ne "") {
                    if (moveCmdFile($cmdDir.$file) && open(FILE, "< ".$cmdDir.$file."_read")) {
                        while (<FILE>){
                            parseRequest($_);
                        }
                        my $poller;
                        foreach $poller (keys(%commandBuffer)) {
                            if (length($commandBuffer{$poller}) != 0) {
                                sendExternalCommand($poller, $commandBuffer{$poller});
                                $commandBuffer{$poller} = "";
                            }
                        }
                        close(FILE);
                        $self->{logger}->writeLogError("Error When removing ".$cmdDir.$file."_read file : $!") if (!unlink($cmdDir.$file."_read"));
                    }
                }
            }
            closedir $dh;
        }
            
        if (defined($timeSyncPerf) && $timeSyncPerf) {
            $difTime = time() - $timeSyncPerf;
        }
            
        # Get PerfData on Nagios Poller
        if ((defined($difTime) && $timeBetween2SyncPerf <= $difTime) || $timeSyncPerf == 0){
            # Check Activity profile Status
            checkProfile();
            
            # Check debug Flag
            checkDebugFlag(1);

            GetAllNagiosServerPerfData();        
            $timeSyncPerf = time();
        }

        sleep(1);
    }
    
    $self->{logger}->writeLogInfo("Centcore stop...");
}

1;

__END__


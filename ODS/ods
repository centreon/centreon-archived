#! /usr/bin/perl -w
###################################################################
# Oreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/gpl.txt
#
# Developped by : Julien Mathis - Romain Le Merlus
#
###################################################################
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
#    For information : contact@merethis.com
####################################################################
#
# Script init
#

use strict;
use warnings;
use DBI;
use threads;
use threads::shared;
use RRDs;
use File::Copy;

my $installedPath = "@OREON_PATH@/ODS/";

my $LOG = $installedPath."var/ods.log";
my $PID = $installedPath."var/ods.pid";

# Init Globals
use vars qw($debug $LOG %status $generalcounter);
use vars qw($mysql_user $mysql_passwd $mysql_host $mysql_database_oreon $mysql_database_ods);
use vars qw($con_oreon $con_ods);

$debug = 0;

# Flag for stoping ods
my $stop : shared = 1;

# Ods Stats
my $lineRead : shared = 0;
my $valueRecorded : shared = 0;

# Init value
my ($file, $line, @line_tab, @data_service, $hostname, $service_desc, $metric_id, $configuration);

# Init status tab
%status = ('OK' => '0', 'WARNING' => '1', 'CRITICAL' => '2', 'UNKNOWN' => '3', 'PENDING' => '4');

# Include Configuration Data
require $installedPath."etc/conf.pm";

sub catch_zap {
	$stop = 0;
	writeLogFile("Receiving order to stop...\n");
}

sub writeLogFile($){
	open (LOG, ">> ".$LOG) || print "can't write $LOG: $!";
	print LOG time()." - ".$_[0];
	close LOG or warn $!;
}

# Starting ODS Engine
writeLogFile("Starting ODS engine...\n");
writeLogFile("PID : ".$$."\n");

# checking if pid file exists.
if (-x $PID){
	writeLogFile("ods already runnig. can't launch again....\n");
	exit(2);
}

# Writing PID
open (PID, ">> ".$PID) || print "can't write PID : $!";
print PID $$ ;
close PID or warn $!;

# Set signals
$SIG{INT}  = \&catch_zap;

require $installedPath."lib/misc.pm";
require $installedPath."lib/purge.pm";
require $installedPath."lib/getHostData.pm";
require $installedPath."lib/getServiceData.pm";
require $installedPath."lib/indentifyService.pm";
require $installedPath."lib/verifyHostServiceIdName.pm";
require $installedPath."lib/identifyMetric.pm";
require $installedPath."lib/updateFunctions.pm";

sub CheckMySQLConnexion(){
	while ((!defined($con_oreon) || !$con_oreon->ping) && (!defined($con_ods) || !$con_ods->ping)){
		if (!defined($con_oreon)) {
			$con_oreon = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
		} else {
			sleep(2);
			undef($con_oreon);
			$con_oreon = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});			
		}
		if (!defined($con_ods)) {
			$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
		} else {
			sleep(2);
			undef($con_ods);
			$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
		}
	}
}

sub CheckMySQLConnexionForODS(){
	while (!defined($con_ods) || !$con_ods->ping){
		if (!defined($con_ods)) {
			$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
		} else {
			sleep(2);
			undef($con_ods);
			$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
		}
	}
}

sub CheckMySQLConnexionForOreon(){
	while (!defined($con_oreon) || !$con_oreon->ping){
		if (!defined($con_oreon)) {
			$con_oreon = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
		} else {
			sleep(2);
			undef($con_oreon);
			$con_oreon = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});			
		}
	}
}

########################################
# return perfdata file path
########################################

sub getPerfDataFile(){
	my ($filename, $sth2, $data, $con_ods);
	$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	$sth2 = $con_ods->prepare("SELECT perfdata_file FROM config");
	writeLogFile("Error when getting perfdata file : " . $sth2->errstr . "\n") if (!$sth2->execute);
	$data = $sth2->fetchrow_hashref();
	undef($sth2);
	$filename = $data->{'perfdata_file'};
	undef($data);
	undef($con_ods);
	return $filename;
}

########################################
# Move perfdata file to tmp file 
########################################

sub movePerfDataFile($){
	if (copy($_[0], $_[0]."_read")){
		writeLogFile("Error When removing service-perfdata file : $!") if (!unlink($_[0]));
		return(1);
	} else {
		writeLogFile("Error When moving data in tmp read file : $!");
		return(0);
	}
}

########################################
# Get ODS config data  
########################################

sub getConfig(){
	my ($sth2, $data, $con_ods);
	$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	$sth2 = $con_ods->prepare("SELECT auto_drop,drop_file,perfdata_file FROM config");
	writeLogFile("Error when getting drop and perfdata properties : ".$sth2->errstr."\n")if (!$sth2->execute);
	$data = $sth2->fetchrow_hashref();	
	undef($sth2);
	undef($con_ods);
	return($data);
}

sub GetPerfData(){
	# Init Var
	my ($line_tab, $sth2, $data, $flag_drop, $sleeptime);
	use vars qw($con_oreon $con_ods);
	CheckMySQLConnexion();				
	my $PFDT = getPerfDataFile();
	while ($stop) {
		if (-r $PFDT || -r $PFDT.".bckp"){
			# Move perfdata File befor reading		

			# Penser a mire le fichier de backup !

			if (movePerfDataFile($PFDT) && open(PFDT, "< $PFDT"."_read")){
				$data = getConfig();
				$PFDT = $data->{'perfdata_file'};
				$flag_drop = 1;
				if ($data->{'auto_drop'} == 1 && defined($data->{'drop_file'})){
					if (!open(DROP, ">> ".$data->{'drop_file'})){
						$flag_drop = 0;
						writeLogFile("can't write in ".$data->{'drop_file'}." : $!");
					}
				} else {
					$flag_drop = 0;
				}
				undef($data);
				while (<PFDT>){
					$lineRead++;
					if (!$stop){
						writeLogFile("can't write in ".$installedPath."var/service-perfdata.bckp : $!") if (!open(BCKP, ">> ".$installedPath."/var/service-perfdata.bckp"));
						while (<PFDT>){
							print BCKP $_;
						}
						return;
					}
					print DROP $_  if ($flag_drop == 1);
			    	@line_tab = split('\t');
			    	$line_tab[2] =~ s/\\/\#BS\#/g;
			    	$line_tab[2] =~ s/\//\#S\#/g;
			    	if (defined($line_tab[5]) && ($line_tab[5] ne '' && $line_tab[5] ne "\n")){
						CheckMySQLConnexion();
						checkAndUpdate(@line_tab);
					}
					undef($line_tab);
				}
				close(PFDT);
				# Remove Read File
				writeLogFile("Error When removing service-perfdata file : $!") if (!unlink($PFDT."_read"));
				# Drop Data
				close(DROP) if ($flag_drop == 1);
				undef($line_tab);
				undef($flag_drop);
			} else {
				writeLogFile("Error When reading data in tmp read file : $!");
			}
		}
		my $i,
		$sleeptime = getSleepTime();
		for ($i = 0; $i <= $sleeptime && $stop; $i++){
			sleep(1);	
		}
		undef($sleeptime);
		undef($i);
	}
} 

########################################
# Check if nagios restart and if we 
# must to check configuration and 
# launch purge process  
########################################

sub CheckRestart(){
	my ($last_restart_stt, $last_restart, $sth2, $data, $y);
	use vars qw($con_oreon $con_ods);
	
	CheckMySQLConnexion();
	$y = 1;
	while($stop){
		CheckMySQLConnexion();
		$last_restart = getLastRestart();
    	$last_restart_stt = getLastRestartInMemory();
		if (!$last_restart_stt || $last_restart ne $last_restart_stt){
			check_HostServiceID();
			if (getPurgeConfig()){
				CheckMySQLDrain();
				DeleteOldRrdDB();
			}
			saveLastRestartInMemory($last_restart);
		}
		$y++;
		sleep(5);
	}
}

sub purgeMysqlData(){
	;
}

sub checkAndUpdate($){
	my $data_service;
	if ($_[5]){
		if ($_[1] =~ /[a-zA-Z]*_Module/){
			@data_service = identify_hidden_service($_[1], $_[2]); # return index_id and storage
			$valueRecorded = identify_hidden_metric($_[5], $data_service[0], $_[4], $_[0], $data_service[1], $valueRecorded); # perfdata index status time type
		} else {
			@data_service = identify_service($_[1], $_[2]); # return index_id and storage
			$valueRecorded = identify_metric($_[5], $data_service[0], $_[4], $_[0], $data_service[1], $valueRecorded); # perfdata index status time type
		}
	}
	undef(@data_service);
}


sub CheckNagiosStats(){
	while ($stop){
		sleep(1);
	}
}

# launch all threads
my $threadPerfdata 			= 	threads->new("GetPerfData");
my $threadCheckRestart		= 	threads->new("CheckRestart");
#my $threadCheckNagiosStats	= 	threads->new("CheckNagiosStats");

# here make statistics
my $y = 0;
my ($lineReadpermin, $valueRecordedpermin, $lastlineRead, $lastvalueRecorded);
$lastlineRead = 0;
$lastvalueRecorded = 0;

my $sth2;
while ($stop){
	if ($y % 60 eq 0){
		CheckMySQLConnexion();
		if ($lastlineRead){
			$lineReadpermin = $lineRead - $lastlineRead;
		} else {
			$lineReadpermin = $lineRead;
		}
		$lastlineRead = $lineRead;
		if ($lastvalueRecorded){
			$valueRecordedpermin = $valueRecorded - $lastvalueRecorded;
		} else {
			$valueRecordedpermin = $valueRecorded;
		}
		$lastvalueRecorded = $valueRecorded;
		$sth2 = $con_ods->prepare("UPDATE statistics SET `lineRead` = '$lineReadpermin', `valueReccorded` = '$valueRecordedpermin' LIMIT 1");
		writeLogFile("Error when getting drop and perfdata properties : ".$sth2->errstr."\n")if (!$sth2->execute);
		
		# Purge MySQL data for not having a too big database.
		
		purgeMysqlData()
		
	}
	$y++;
	sleep(1);
}

# Waiting All threads
$threadPerfdata->join;
$threadCheckRestart->join;
#$threadCheckNagiosStats->join;

# Write in log file 
writeLogFile("Stopping ODS engine...\n");

# Delete PID File
writeLogFile("Error When removing pid file : $!") if (!unlink($PID));

exit(1);
#! /usr/bin/perl -w
###################################################################
# Oreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/gpl.txt
#
# Developped by : Julien Mathis - jmathis@merethis.com
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

#use strict;
#use warnings;
use DBI;
use threads;
use threads::shared;
use RRDs;
use File::Copy;

my $installedPath = "/srv/oreon/ODS/";

my $LOG = $installedPath."var/ods.log";
my $PID = $installedPath."var/ods.pid";

# Init Globals
use vars qw($debug $mysql_user $mysql_passwd $mysql_host $mysql_database_oreon $mysql_database_ods $LOG %status $generalcounter);

$debug = 0;

my $stop : shared;
$stop = 1;

# Init value
my ($file, $line, @line_tab, @data_service, $hostname, $service_desc, $metric_id, $configuration);
%status = ('OK' => '0', 'WARNING' => '1', 'CRITICAL' => '2', 'UNKNOWN' => '3', 'PENDING' => '4');

require $installedPath."etc/conf.pm";

sub catch_zap {
	$stop = 0;
	writeLogFile($LOG, "Somebody sent me a kill signal...\n");
	writeLogFile($LOG, "Stopping ODS engine...\n");
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
require $installedPath."lib/getPerfData.pm";
require $installedPath."lib/getHostData.pm";
require $installedPath."lib/getServiceData.pm";
require $installedPath."lib/indentifyService.pm";
require $installedPath."lib/verifyHostServiceIdName.pm";
require $installedPath."lib/identifyMetric.pm";
require $installedPath."lib/updateFunctions.pm";


sub GetPerfData(){
	my ($line_tab, $sth2, $data, $flag_drop, $sleeptime);
	
	CheckMySQLConnexion();	
	$sth2 = $con_ods->prepare("SELECT perfdata_file FROM config");
	if (!$sth2->execute) {writeLogFile("Error when getting perfdata file : " . $sth2->errstr . "\n");}
	$data = $sth2->fetchrow_hashref();
	my $PFDT = $data->{'perfdata_file'};
	undef($sth2);
	undef($data);

	while ($stop) {
		if (-r $PFDT){
			if (copy($PFDT, $PFDT."_read")){
				if (!unlink($PFDT)){writeLogFile("Error When removing service-perfdata file : $!");}
			} else {
				writeLogFile("Error When moving data in tmp read file : $!");
			}
			if (open(PFDT, "< $PFDT"."_read")){
				CheckMySQLConnexion();
				$sth2 = $con_ods->prepare("SELECT auto_drop,drop_file,perfdata_file FROM config");
				if (!$sth2->execute) {writeLogFile("Error when getting drop and perfdata properties : ".$sth2->errstr."\n");}
				$data = $sth2->fetchrow_hashref();	
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
					if (!stop){
						if (!open(BCKP, ">> /srv/oreon/ODS/var/perfdata.bckp")){
							writeLogFile("can't write in /srv/oreon/ODS/var/perfdata.bckp : $!");
						}
						while (<PFDT>){
							print BCKP $_;
						}
						return;
					}
					if ($debug){
						writeLogFile($_);
					}
					if ($flag_drop == 1){print DROP $_ ;}
			    	@line_tab = split('\t');
			    	if (defined($line_tab[5]) && ($line_tab[5] ne '' && $line_tab[5] ne "\n")){
						CheckMySQLConnexion();
						checkAndUpdate(@line_tab);
					}
					$line_tab[5] = '';
				}
				close(PFDT);
				if (!unlink($PFDT."_read")){
					writeLogFile("Error When removing service-perfdata file : $!");
				}
				if ($flag_drop == 1){close(DROP);}
				undef($line_tab);
				undef($flag_drop);
			} else {
				writeLogFile("Error When reading data in tmp read file : $!");
			}
		}
		$sleeptime = getSleepTime();
		for (my $i = 0; $i <= $sleeptime && $stop; $i++){
			sleep(1);	
		}
		undef($sleeptime);
		undef($i);
	}
} 

sub CheckRestart(){
	my ($sth2, $data, $purgeinterval);
	my $last_restart;
	my $last_restart_stt;
	
	CheckMySQLConnexion();
	$sth2 = $con_oreon->prepare("SELECT oreon_path FROM general_opt LIMIT 1");
	if (!$sth2->execute) {writeLogFile("Error when getting oreon Path : " . $sth2->errstr . "\n");}
	$data = $sth2->fetchrow_hashref();
	my $STOPFILE = $data->{'oreon_path'} . "ODS/stopods.flag";
	undef($sth2);
	undef($data);
	
	while($stop){
		CheckMySQLConnexion();
		$last_restart = getLastRestart();
    	$last_restart_stt = getLastRestartInMemory();
		if (!$last_restart_stt || $last_restart ne $last_restart_stt){
			CheckMySQLDrain();
			purgeRrdDB() if (getPurgeConfig());	
			check_HostServiceID();	
		}
		
		$purgeinterval = getPurgeInterval();
		for (my $i = 0;$i <= $purgeinterval && $stop;$i++){
			sleep(1);	
		}
		undef($purgeinterval);
		undef($i);	
	}
}

my $thread_perfdata 		= threads->new("GetPerfData");
my $thread_check_restart	= threads->new("CheckRestart");

while ($stop){
	sleep(1);
}

$thread_perfdata->join;
$thread_check_restart->join;

writeLogFile("Stopping ODS engine...\n");

if (!unlink($PID)){writeLogFile("Error When removing pid file : $!");}

exit(1);
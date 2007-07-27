#! /usr/bin/perl -w 
###################################################################
# Oreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

use strict;
use DBI;
use POSIX;

my $installedPath = "/srv/oreon/ODS/";
my $LOG ="/var/log/ods.log";

# Init Globals
use vars qw($len_storage_rrd $RRDdatabase_path $LOG %stat $con_ods $con_oreon $generalcounter);

# Init value
my ($file, $line, @line_tab, @data_service, $hostname, $service_desc, $metric_id, $configuration);
%stat = ('0' => 'OK', '1' => 'WARNING', '2' => 'CRITICAL', '3' => 'UNKNOWN', '4' => 'PENDING');

# Init var

my $PFDT = "/root/service-perfdata";
my $mysql_user = "root";
my $mysql_passwd = "";
my $mysql_host = "localhost";
my $mysql_database = "perfparse";


sub writeLogFile($){
	open (LOG, ">> ".$LOG) || print "can't write $LOG: $!";
	print LOG time()." - ".$_[0];
	close LOG or warn $!;
}

my $connexion = DBI->connect("DBI:mysql:database=".$mysql_database.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});		

print "- Open Connexion : ok\n";
if (open (FILE, ">> ".$PFDT) || print "can't write $PFDT: $!"){
	print "- Open File successfull\n";
	print "Preparing MySQL request... (This operation may be very long... be patient).\n";
	my ($sth2, $data);
	$sth2 = $connexion->prepare("SELECT * FROM `perfdata_service_bin`");
	if (!$sth2->execute) {writeLogFile("Error when getting data : " . $sth2->errstr . "\n");}
	print "- Request Executed\n";
	my ($host_name, $service_description, $status, $time, $perfdata, $metric);
	print "- service-perfdata file is creating.... \n";
	while ($data = $sth2->fetchrow_hashref()){
		if ($host_name && $service_description && $host_name eq $data->{'host_name'} && $service_description eq $data->{'service_description'}) {
			
			my $sth3 = $connexion->prepare("SELECT unit FROM `perfdata_service_metric` WHERE `host_name` = '".$host_name."' AND `service_description` = '".$service_description."' AND `metric` = '".$data->{'metric'}."' LIMIT 1");
			if (!$sth3->execute) {writeLogFile("Error when getting data : " . $sth3->errstr . "\n");}
			my $metric_data = $sth3->fetchrow_hashref();
			undef($sth3);
			
			my $unit;
			if (!defined($metric_data->{'unit'})){
				$unit = "";
			} else { 
				$unit = $metric_data->{'unit'};	
			}
			$perfdata .= " ".$data->{'metric'}."=".$data->{'value'}.$unit;
			undef($unit);
			undef($metric_data);
		} else {
			if ($time){
				print FILE $time."\t".$host_name."\t".$service_description."\tauto insert\t".$stat{$status}."\t".$perfdata."\n";
				undef($host_name);
				undef($service_description);
				undef($status);
				undef($perfdata);
				undef($time);
				undef($metric);
			}			
			$data->{'ctime'} =~ /([0-9]*)\-([0-9]*)\-([0-9]*)\ ([0-9]*)\:([0-9]*)\:([0-9]*)/;
			$time = mktime($6, $5, $4, $3, $2, $1 - 1900);
			$host_name = $data->{'host_name'};
			$service_description = $data->{'service_description'};
			$status = $data->{'state'};
			$metric =  $data->{'metric'};
			
			my $sth3 = $connexion->prepare("SELECT unit FROM `perfdata_service_metric` WHERE `host_name` = '".$host_name."' AND `service_description` = '".$service_description."' AND `metric` = '".$metric."' LIMIT 1");
			if (!$sth3->execute) {writeLogFile("Error when getting data : " . $sth3->errstr . "\n");}
			my $metric_data = $sth3->fetchrow_hashref();
			undef($sth3);
			
			my $unit;
			if (!defined($metric_data->{'unit'})){
				$unit = "";
			} else { 
				$unit = $metric_data->{'unit'};	
			}
			$perfdata = $data->{'metric'}."=".$data->{'value'}.$unit;
			undef($unit);
			undef($metric_data);
		}
	}
}
print "- File creation succeded !\n";
exit;
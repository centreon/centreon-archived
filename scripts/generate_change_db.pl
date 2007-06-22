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

use strict;
use DBI;
use RRDs;

my $installedPath = "@OREON_PATH@/ODS/";

# Init Globals
use vars qw($len_storage_rrd $RRDdatabase_path $mysql_user $mysql_passwd $mysql_host $mysql_database_oreon $mysql_database_ods $LOG %status $con_ods $con_oreon $generalcounter);

# Init value
my ($file, $line, @line_tab, @data_service, $hostname, $service_desc, $metric_id, $configuration);
%status = ('OK' => '0', 'WARNING' => '1', 'CRITICAL' => '2', 'UNKNOWN' => '3', 'PENDING' => '4');

require $installedPath."etc/conf.pm";
require $installedPath."lib/misc.pm";

sub writeLogFile($){
	print  time()." - ".$_[0];
}

sub getMyServiceField($$)	{
	my $service_id = $_[0];
	my $field = $_[1];
	
	if (!defined($service_id) || !$service_id){
		return 1;
	}
		
	while(1){
		my $sth1 = $con_oreon->prepare("SELECT ".$field.", service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
    	if (!$sth1->execute) {writeLogFile("Error When ods get service field : " . $sth1->errstr . "\n");}
   		my $data = $sth1->fetchrow_hashref();
    	if (defined($data->{$field}) && $data->{$field}){
  			undef($service_id);
  			undef($sth1);
  			return $data->{$field};
    	} elsif ($data->{'service_template_model_stm_id'}){
			$service_id = $data->{'service_template_model_stm_id'};
    	} else {
			last;
		}
	}
}

sub getServiceCheckInterval($){ # metric_id
	
	my $sth1 = $con_ods->prepare("SELECT index_id FROM metrics WHERE metric_id = '".$_[0]."'");
    if (!$sth1->execute){writeLogFile("Error where getting service interval : ".$sth1->errstr."\n");}
    my $data_metric = $sth1->fetchrow_hashref();
    
    $sth1 = $con_ods->prepare("SELECT service_id FROM index_data WHERE id = '".$data_metric->{'index_id'}."'");
    if (!$sth1->execute) {writeLogFile("Error where getting service interval 2 : ".$sth1->errstr."\n");}
    my $data_hst_svc = $sth1->fetchrow_hashref();
 	
 	undef($sth1);
    undef($data_metric);
    
    my $return = getMyServiceField($data_hst_svc->{'service_id'}, "service_normal_check_interval");
    undef($data_hst_svc);
    return $return;
}

CheckMySQLConnexion();

my ($sth2, $data, $ERR);

print $ARGV[0] . "\n";

if ($ARGV[0] eq "-a"){
    undef($ARGV);
    $sth2 = $con_ods->prepare("SELECT metric_id FROM metrics ORDER BY metric_id");
    if (!$sth2->execute) {writeLogFile("Error when getting metrics list : " . $sth2->errstr . "\n");}
    my $t;
    for ($t = 0;$data = $sth2->fetchrow_hashref();$t++){
		$ARGV[$t] = $data->{'metric_id'};
    }
    undef($sth2);
    undef($t);
    undef($data);
}

$sth2 = $con_ods->prepare("SELECT * FROM config");
if (!$sth2->execute) {writeLogFile("Error when getting perfdata file : " . $sth2->errstr . "\n");}
$data = $sth2->fetchrow_hashref();
$RRDdatabase_path = $data->{'RRDdatabase_path'};
$len_storage_rrd = $data->{'len_storage_rrd'};

for (my $i = 0; defined($ARGV[$i]) ; $i++ ){
	$sth2 = $con_ods->prepare("SELECT * FROM metrics WHERE metric_id = '".$ARGV[$i]."'");
	if (!$sth2->execute) {writeLogFile("Error when getting perfdata file : " . $sth2->errstr . "\n");}
	$data = $sth2->fetchrow_hashref();
	my $index_id = $data->{'index_id'};
	my $metric_name = $data->{'metric_name'};
	undef($sth2);
	undef($data);
	my $interval = getServiceCheckInterval($ARGV[$i]) * 60 + 30;
	print 'interval : $interval \n';
	$sth2 = $con_ods->prepare("SELECT * FROM data_bin WHERE id_metric = '".$ARGV[$i]."'");
	if (!$sth2->execute) {writeLogFile("Error when getting perfdata file : " . $sth2->errstr . "\n");}
	my $flag = 0;
	my $cpt = 0;
	while ($data = $sth2->fetchrow_hashref()){
		if (!$flag){
			system("mv ".$RRDdatabase_path.$ARGV[$i].".rrd ".$RRDdatabase_path."old/".$ARGV[$i].".rrd");
			my $begin = $data->{'ctime'} - 200;
			RRDs::create($RRDdatabase_path.$ARGV[$i].".rrd", "-b ".$begin, "-s ".$interval, "DS:$metric_name:GAUGE:".$interval.":U:U", "RRA:AVERAGE:0.5:1:".$len_storage_rrd, "RRA:MIN:0.5:12:".$len_storage_rrd, "RRA:MAX:0.5:12:".$len_storage_rrd);
			print "Creation de la Base : " .$RRDdatabase_path.$ARGV[$i].".rrd\n";
			print "begin : " .$begin ." \n";
			print "Interval : ".$interval."\n";
			$ERR = RRDs::error;
			if ($ERR){writeLogFile("ERROR while creating $_[0]$_[1].rrd : $ERR\n");}
			undef($begin);
			$flag++;
		}
		#print $data->{'ctime'}."\n";
		RRDs::update ($RRDdatabase_path.$ARGV[$i].".rrd" , "--template", $metric_name, $data->{'ctime'}.":".$data->{'value'});
		$ERR = RRDs::error;
		if ($ERR){writeLogFile("ERROR while updating ".$RRDdatabase_path.$ARGV[$i]."_new.rrd at ".$data->{'ctime'}." -> ".$data->{'value'}." : $ERR\n");}
		$cpt++;
	}
	print "$cpt value insert\n";
}

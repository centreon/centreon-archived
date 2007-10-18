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

sub getPurgeInterval(){
	my $data;
	my $purge_interval;

	$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	my $sth2 = $con_ods->prepare("SELECT purge_interval FROM config");
	if (!$sth2->execute) {writeLogFile("Error - getPurgeInterval : " . $sth2->errstr . "\n");}
	$data = $sth2->fetchrow_hashref();
	if (!defined($data->{'purge_interval'}) || !$data->{'purge_interval'}){
		$purge_interval = 10;
	} else {
		$purge_interval = $data->{'purge_interval'};
	} 
	undef($sth2);	
	undef($data);
	return $purge_interval;
}

sub getRRDdatabase_path(){
	my $data;
	my $RRDdatabase_path;

	$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	my $sth2 = $con_ods->prepare("SELECT RRDdatabase_path FROM config");
	if (!$sth2->execute) {writeLogFile("Error - RRDdatabase_path : " . $sth2->errstr . "\n");}
	$data = $sth2->fetchrow_hashref();
	$RRDdatabase_path = $data->{'RRDdatabase_path'};
	undef($sth2);	
	undef($data);
	return $RRDdatabase_path;
}

sub getLenStorageDB(){
	my $data;
	my $len_storage_rrd;

	$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	my $sth2 = $con_ods->prepare("SELECT len_storage_rrd FROM config");
	if (!$sth2->execute) {writeLogFile("Error - len_storage_rrd : " . $sth2->errstr . "\n");}
	$data = $sth2->fetchrow_hashref();
	if (!defined($data->{'len_storage_rrd'}) || !$data->{'len_storage_rrd'}){
		$len_storage_rrd = 10;
	} else {
		$len_storage_rrd = $data->{'len_storage_rrd'};
	} 
	undef($sth2);	
	undef($data);
	return $len_storage_rrd;
}


sub getSleepTime(){
	my $data;
	my $sleep_time;

	$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	my $sth2 = $con_ods->prepare("SELECT sleep_time FROM config");
	if (!$sth2->execute) {writeLogFile("Error - getSleepTime : " . $sth2->errstr . "\n");}
	$data = $sth2->fetchrow_hashref();
	if (!defined($data->{'sleep_time'}) || !$data->{'sleep_time'}){
		$sleep_time = 10;
	} else {
		$sleep_time = $data->{'sleep_time'};
	} 
	undef($sth2);	
	undef($data);
	return $sleep_time;
}

1;
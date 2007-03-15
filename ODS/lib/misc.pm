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

# Check if MySQL connexion (ODS and oreon) are OK

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

sub getPurgeInterval(){
	my $data;
	my $purge_interval;
	my $sth2 = $con_ods->prepare("SELECT purge_interval FROM config");
	if (!$sth2->execute) {writeLogFile("Error : " . $sth2->errstr . "\n");}
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

sub getSleepTime(){
	my $data;
	my $sleep_time;
	my $sth2 = $con_ods->prepare("SELECT sleep_time FROM config");
	if (!$sth2->execute) {writeLogFile("Error : " . $sth2->errstr . "\n");}
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
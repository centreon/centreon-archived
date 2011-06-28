################################################################################
# Copyright 2005-2011 MERETHIS
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
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
# For more information : contact@centreon.com
# 
# SVN : $URL$
# SVN : $Id$
#
####################################################################################

sub getPurgeInterval() {
	my $data;
	my $purge_interval;

	CreateConnexionForOreon();
	
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

	CreateConnexionForOreon();
	
	my $sth2 = $con_ods->prepare("SELECT RRDdatabase_path FROM config");
	if (!$sth2->execute()) {
		writeLogFile("Error - RRDdatabase_path : " . $sth2->errstr . "\n");
	}
	$data = $sth2->fetchrow_hashref();
	$RRDdatabase_path = $data->{'RRDdatabase_path'};
	undef($sth2);	
	undef($data);
	return $RRDdatabase_path;
}

sub getRRDdatabase_status_path(){
	my $data;
	my $RRDdatabase_status_path;
	
	CreateConnexionForOreon();
	
	my $sth2 = $con_ods->prepare("SELECT RRDdatabase_status_path FROM config");
	if (!$sth2->execute()) {
		writeLogFile("Error - RRDdatabase_path : " . $sth2->errstr . "\n");
	}
	$data = $sth2->fetchrow_hashref();
	$RRDdatabase_status_path = $data->{'RRDdatabase_status_path'};
	undef($sth2);	
	undef($data);
	return $RRDdatabase_status_path;
}

sub getLenStorageDB(){
	my $data;
	my $len_storage_rrd;

	CreateConnexionForOreon();
	
	my $sth2 = $con_ods->prepare("SELECT len_storage_rrd FROM config");
	if (!$sth2->execute()) {
		writeLogFile("Error - len_storage_rrd : " . $sth2->errstr . "\n");
	}
	$data = $sth2->fetchrow_hashref();
	if (!defined($data->{'len_storage_rrd'}) || !$data->{'len_storage_rrd'}){
		$len_storage_rrd = 10;
	} else {
		$len_storage_rrd = $data->{'len_storage_rrd'};
	} 
	undef($sth2);	
	undef($data);
	return $len_storage_rrd * 60 * 60 * 24;
}


sub getSleepTime(){
	my $data;
	my $sleep_time;

	CreateConnexionForOreon();

	my $sth2 = $con_ods->prepare("SELECT sleep_time FROM config");
	if (!$sth2->execute()) {
		writeLogFile("Error - getSleepTime : " . $sth2->errstr . "\n");
	}
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
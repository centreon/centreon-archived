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
use RRDs;

my $installedPath = "@OREON_PATH@/ODS/";

# Init Globals
use vars qw($len_storage_rrd $RRDdatabase_path $mysql_user $mysql_passwd $mysql_host $mysql_database_oreon $mysql_database_ods $LOG %status $con_ods $con_oreon $generalcounter);

# Init value
my ($file, $line, @line_tab, @data_service, $hostname, $service_desc, $metric_id, $configuration);

require $installedPath."etc/conf.pm";
require $installedPath."lib/misc.pm";

sub writeLogFile($){
	print  time()." - ".$_[0];
}

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

CheckMySQLConnexion();

my ($sth2, $data, $ERR, $sth_rrd, $data_rrd);

$sth2 = $con_ods->prepare("SELECT * FROM config");
if (!$sth2->execute) {writeLogFile("Error when getting perfdata file : " . $sth2->errstr . "\n");}
$data = $sth2->fetchrow_hashref();
$RRDdatabase_path = $data->{'RRDdatabase_path'};
$len_storage_rrd = $data->{'len_storage_rrd'};
$sth_rrd = $con_oreon->prepare("SELECT rrdtool_path_bin FROM general_opt");
if (!$sth_rrd->execute) {writeLogFile("Error when getting rrdtool_path_bin : " . $sth_rrd->errstr . "\n");}
$data_rrd = $sth_rrd->fetchrow_hashref();

$sth2 = $con_ods->prepare("SELECT metric_id, metric_name FROM metrics ORDER BY metric_id");
if (!$sth2->execute) {writeLogFile("Error when getting metrics list : " . $sth2->errstr . "\n");}
my $t;
for ($t = 0;$data = $sth2->fetchrow_hashref();$t++){
	system($data_rrd->{rrdtool_path_bin}." tune ".$RRDdatabase_path.$data->{'metric_id'}.".rrd --data-source-rename metric:".$data->{'metric_name'});
}
undef($sth2);
undef($t);
undef($data);



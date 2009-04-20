################################################################################
# Copyright 2005-2009 MERETHIS
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
   
# Get last time restart of nagios

sub getLastRestart(){
	
	# Create connection
	$con = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	if (defined($con)) {
		my $sth1_oreon = $con->prepare("SELECT `last_restart` FROM `nagios_server`");
	    if (!$sth1_oreon->execute()) {
	    	writeLogFile("Error - getLastRestart : " . $sth1_oreon->errstr . "\n");
	    }
	    my $data_oreon = $sth1_oreon->fetchrow_hashref();
	    undef($sth1_oreon);
	    $con->disconnect();
	    return $data_oreon->{'last_restart'};
	} else {
		return 0;
	}
}

# Get last time restart of nagios

sub getLastRestartInMemory(){
	$con = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	my $sth = $con->prepare("SELECT last_restart FROM statistics");
    if (!$sth->execute) {
    	writeLogFile("Error - getLastRestartInMemory :" . $sth->errstr . "\n");
    }
    my $data = $sth->fetchrow_hashref();
    undef($sth);
    $con->disconnect();
    return $data->{'last_restart'};
}

sub saveLastRestartInMemory($){
	$con = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	my $sth = $con->prepare("UPDATE statistics SET `last_restart` = '".$_[0]."'");
    if (!$sth->execute) {
    	writeLogFile("Error - saveLastRestartInMemory : " . $sth->errstr . "\n");
    }
    $con->disconnect();
    undef($sth);
}

# Get if purge is activ

sub getPurgeConfig(){
	$con = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	my $sth = $con->prepare("SELECT autodelete_rrd_db FROM config");
    if (!$sth->execute) {
    	writeLogFile("Error - getPurgeConfig :" . $sth->errstr . "\n");
    }
    my $data = $sth->fetchrow_hashref();
    undef($sth);
    $con->disconnect();
    return $data->{'autodelete_rrd_db'};
}

# Get repository of RRDTool db

sub getStorageDir(){
	$con = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	my $sth = $con->prepare("SELECT RRDdatabase_path FROM config");
    if (!$sth->execute) {
    	writeLogFile("Error - getStorageDir : " . $sth->errstr . "\n");
   	}
    my $data = $sth->fetchrow_hashref();
    undef($sth);
    $con->disconnect();
    return $data->{'RRDdatabase_path'};
}

# Delete RRDTool Database if thy were not link with data in ODS DB.

sub DeleteOldRrdDB(){
	my ($data, %base);
	$conods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	my $sth = $conods->prepare("SELECT metric_id FROM metrics");
    if (!$sth->execute) {
    	writeLogFile("Error:" . $sth->errstr . "\n");
    }
    while ($data = $sth->fetchrow_hashref()){
     	$base{$data->{'metric_id'}.".rrd"} = 1;
    }
    undef($sth);
    undef($data);
    $some_dir = getStorageDir();
    opendir(DIR, $some_dir) || die "can't opendir $some_dir: $!";
    my @files = grep { $_ ne '.' and $_ ne '..' } readdir DIR; 
    closedir DIR;
    for (@files) {
		if (!defined($base{$_})){
			if (-d $some_dir."/".$_){
				;
			} else { 
				if (unlink($some_dir."/".$_)){
					writeLogFile("Warning : ".$some_dir."/".$_." removed \n");
				} else {
					writeLogFile("Error : Unable to remove ".$some_dir.$_ ."\n");
				}
			}
		}
	}
	$conods->disconnect();
	undef($some_dir);
	undef(@files);
	undef($data);
	undef(%base);
}


# Check if host or service have change their name and description. 
# If hosts or services have change, it update their id.
   
sub check_HostServiceID(){
	my ($data, $host_name, $service_description, $purge_mod);
	$con = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	my $sth1 = $con->prepare("SELECT * FROM index_data ORDER BY host_name");
	if (!$sth1->execute) {
		writeLogFile("Error : " . $sth1->errstr . "\n");
	}
    while ($data = $sth1->fetchrow_hashref()){
    	$host_name = getHostName($data->{'host_id'});
    	$service_description = getServiceName($data->{'service_id'});
    	if (defined($host_name) && $host_name && defined($service_description) && $service_description && defined($data->{'host_name'}) && defined($data->{'service_description'}) && (($host_name ne $data->{'host_name'}) || ($service_description ne $data->{'service_description'}))){
    		$str = 	"UPDATE index_data SET `host_name` = '".$host_name."', `service_description` = '".$service_description."' WHERE `host_id` = '".$data->{'host_id'}."' AND `service_id` = '".$data->{'service_id'}."'";
    		my $sth2 = $con->prepare($str);
    		writeLogFile("Error:" . $sth2->errstr . "\n") if (!$sth2->execute);
    		undef($sth2);
    	}  
    }
	if (defined($last_restart) && $last_restart){
		$sth1 = $con->prepare("UPDATE statistics SET `last_restart` = '".$last_restart."'");
		if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
		undef($sth1);
	}
	$con->disconnect();
}

1;
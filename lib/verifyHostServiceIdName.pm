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

# Get last time restart of nagios

sub getLastRestart(){

	CheckMySQLConnexion();
	
    my $sth1_oreon = $con_oreon->prepare("SELECT `last_restart` FROM `nagios_server`");
	if (!$sth1_oreon->execute()) {
	    writeLogFile("Error - getLastRestart : " . $sth1_oreon->errstr);
	}
	my $data_oreon = $sth1_oreon->fetchrow_hashref();
	undef($sth1_oreon);
	if (defined($data_oreon->{'last_restart'})) {
 		return $data_oreon->{'last_restart'};
    } else {
		return 0;
    }
}

# Get last time restart of nagios

sub getLastRestartInMemory(){

	CheckMySQLConnexion();

    my $sth = $con_ods->prepare("SELECT last_restart FROM statistics");
    if (!$sth->execute) {
    	writeLogFile("Error - getLastRestartInMemory :" . $sth->errstr);
    }
    my $data = $sth->fetchrow_hashref();
    undef($sth);
    return $data->{'last_restart'};
}

sub saveLastRestartInMemory($){
	CheckMySQLConnexion();

    my $sth = $con_ods->prepare("UPDATE statistics SET `last_restart` = '".$_[0]."'");
    if (!$sth->execute) {
    	writeLogFile("Error - saveLastRestartInMemory : " . $sth->errstr);
    }
    undef($sth);
}

# Get if purge is activ

sub getPurgeConfig(){

	CheckMySQLConnexion();

    my $sth = $con_ods->prepare("SELECT autodelete_rrd_db FROM config");
    if (!$sth->execute) {
    	writeLogFile("Error - getPurgeConfig :" . $sth->errstr);
    }
    my $data = $sth->fetchrow_hashref();
    undef($sth);
    return $data->{'autodelete_rrd_db'};
}

# Get repository of RRDTool db

sub getStorageDir(){
	CheckMySQLConnexion();

    my $sth = $con_ods->prepare("SELECT RRDdatabase_path FROM config");
    if (!$sth->execute) {
    	writeLogFile("Error - getStorageDir : " . $sth->errstr);
    }
    my $data = $sth->fetchrow_hashref();
    undef($sth);
    return $data->{'RRDdatabase_path'};
}

# Delete RRDTool Database if thy were not link with data in ODS DB.

sub DeleteOldRrdDB(){
    my ($data, %base);

    # Connection to MySQL DB
	CheckMySQLConnexion();

    my $sth = $con_ods->prepare("SELECT metric_id FROM metrics");
    if (!$sth->execute) {
    	writeLogFile("Error:" . $sth->errstr);
    }
    while ($data = $sth->fetchrow_hashref()){
     	$base{$data->{'metric_id'}.".rrd"} = 1;
    }
    undef($sth);
    undef($data);

    $some_dir = getStorageDir();
    opendir(DIR, $some_dir) || die "can't opendir $some_dir: $!";
    @files = grep { $_ ne '.' and $_ ne '..' } readdir DIR; 
    closedir DIR;
    for (@files) {
		if (!defined($base{$_})){
		    if (!-d $some_dir."/".$_){
				if (unlink($some_dir."/".$_)){
				    writeLogFile("Sync : purge: ".$some_dir."/".$_." removed");
				} else {
				    writeLogFile("Sync : Error -> Unable to remove ".$some_dir.$_);
				}
		    }
		}
    }
    undef($some_dir);
    undef(@files);
    undef($data);
    undef(%base);
}


# Check if host or service have change their name and description. 
# If hosts or services have change, it update their id.
   
sub check_HostServiceID() {
    my ($data, $host_name, $service_description, $purge_mod, %serviceCache, %hostCache);

    writeLogFile("Sync : Nagios restart - Process start");

    # connection to MySQL DB
   	CheckMySQLConnexion();

    # Create Service Cache 
    my $sth = $con_oreon->prepare("SELECT service_description, service_id FROM service WHERE service_register = '1'");
    if (!$sth->execute()) {
        writeLogFile("Sync | Cache Service : Error -> " . $sth->errstr . "\n");
    }
    while ($data = $sth->fetchrow_hashref()) {
		$serviceCache{$data->{'service_id'}} = $data->{'service_description'};
    }
    undef($data);
    undef($sth);

    # Create Host Cache
    $sth = $con_oreon->prepare("SELECT host_name, host_id FROM host WHERE host_register = '1'");
    if (!$sth->execute()) {
        writeLogFile("Sync | Cache Host : Error -> " . $sth->errstr . "\n");
    }
    while ($data = $sth->fetchrow_hashref()) {
		$hostCache{$data->{'host_id'}} = $data->{'host_name'};
    }
    undef($data);
    undef($sth);

    # Get index data in buffer
    my $sth1 = $con_ods->prepare("SELECT host_name, host_id, service_description, service_id FROM index_data ORDER BY host_name");
    if (!$sth1->execute()) {
	writeLogFile("Sync : Error -> " . $sth1->errstr . "\n");
    }
    while ($data = $sth1->fetchrow_hashref()) {
		if (defined($data->{'host_id'})) {	    
		    if (defined($hostCache{$data->{'host_id'}})) {
				$host_name = $hostCache{$data->{'host_id'}};
		    }	 
		    if (defined($serviceCache{$data->{'service_id'}})) {
				$service_description = $serviceCache{$data->{'service_id'}};
		    }
		    if (defined($host_name) && $host_name && defined($service_description) && $service_description && defined($data->{'host_name'}) && defined($data->{'service_description'}) && (($host_name ne $data->{'host_name'}) || ($service_description ne $data->{'service_description'}))){
				$str = "UPDATE index_data SET `host_name` = '".$host_name."', `service_description` = '".$service_description."' WHERE `host_id` = '".$data->{'host_id'}."' AND `service_id` = '".$data->{'service_id'}."'";
				my $sth2 = $con_ods->prepare($str);
				writeLogFile("Error:" . $sth2->errstr . "\n") if (!$sth2->execute);
				undef($sth2);
		    }
		    undef($host_name);
		    undef($service_description);
		}
    }
    if (defined($last_restart) && $last_restart) {
		$sth1 = $con_ods->prepare("UPDATE statistics SET `last_restart` = '".$last_restart."'");
		if (!$sth1->execute) {
		    writeLogFile("Error:" . $sth1->errstr . "\n");
		}
		undef($sth1);
    }
    undef(%hostCache);
    undef(%serviceCache);
    writeLogFile("Sync : Process end");
}

1;
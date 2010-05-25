################################################################################
# Copyright 2005-2010 MERETHIS
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


# Get host id in Centreon Data base.
# need in paramter : host_name, DBcnx

sub getHostID($$){

    my $con = $_[1];

    # Request
    my $sth2 = $con->prepare("SELECT `host_id` FROM `host` WHERE `host_name` = '".$_[0]."' AND `host_register` = '1'");
    if (!$sth2->execute) {
	writeLogFile("Error:" . $sth2->errstr . "\n");
    }

    my $data_host = $sth2->fetchrow_hashref();
    my $host_id = $data_host->{'host_id'};
    $sth2->finish();

    # free data
    undef($data_host);
    undef($con);
    
    # return host_id
    return $host_id;
}

# Get host name in oreon Data base.
# need in paramter : host_id

sub getHostName($){
    return 0 if (!$_[0]);

    my $con = CreateConnexionForOreon();

    my $sth2 = $con->prepare("SELECT `host_name` FROM `host` WHERE `host_id` = '".$_[0]."' AND `host_register` = '1'");
    if (!$sth2->execute) {
	writeLogFile("Error:" . $sth2->errstr . "\n");
    }

    my $data_host = $sth2->fetchrow_hashref();
    my $host_name = $data_host->{'host_name'};
    undef($data_host);
    $sth2->finish();
    $con->disconnect();

    return $host_name;
}

# Get service id in oreon Data base.
# need in paramter : host_id, service_description

sub getServiceID($$){
    $_[1] =~ s/\&/\&amp\;/g;

    my $con = CreateConnexionForOreon();								
    my $sth2 = $con->prepare(	"SELECT service_id FROM service, host_service_relation hsr ".
				"WHERE hsr.host_host_id = '".$_[0]."' AND hsr.service_service_id = service_id ".
				"AND service_description = '".$_[1]."' AND `service_register` = '1' LIMIT 1");

    if (!$sth2->execute) {
	writeLogFile("Error when getting service id : " . $sth2->errstr . "\n");
    }
    my $data = $sth2->fetchrow_hashref();
    $sth2->finish();
    if (!defined($data->{'service_id'}) && !$data->{'service_id'}){
	$sth2 = $con->prepare(	"SELECT service_id FROM hostgroup_relation hgr, service, host_service_relation hsr" .
				" WHERE hgr.host_host_id = '".$_[0]."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
				" AND service_id = hsr.service_service_id AND service_description = '".$_[1]."' AND `service_register` = '1'");
	if (!$sth2->execute) {writeLogFile("Error when getting service id 2 : " . $sth2->errstr . "\n");}
	my $data2 = $sth2->fetchrow_hashref();
	$service_id = $data2->{'service_id'};
	undef($data);
	undef($data2);
	undef($sth2);
	if (defined($service_id)){
	    $con->disconnect();
	    return $service_id;
	} else {
	    $con->disconnect();
	    return 0;
	}
    } else {
	$service_id = $data->{'service_id'};
	undef($data);
	$con->disconnect();
	return $service_id;
    }
}

# Get Service Name in oreon database
# need parameters : service_id

sub getServiceName($){	
    if ($_[0]){
	my $con = CreateConnexionForOreon();
	my $sth2 = $con->prepare("SELECT service_description FROM service WHERE service_id = '".$_[0]."' AND `service_register` = '1'");
	if (!$sth2->execute) {
	    writeLogFile("Error getting service name : " . $sth2->errstr . "\n");
	}
	my $data = $sth2->fetchrow_hashref();
	my $service_description = $data->{'service_description'};
	undef($data);

	if (defined($service_description)){
	    $sth2->finish();
	    $con->disconnect();
	    return $service_description;
	} else {
	    return 0;
	}
    } else {
	return 0;
    }
}

# get a field for a service in oreon
# need parameters : service_id and field_name

sub getMyServiceField($$)	{
    my $service_id = $_[0];
    my $field = $_[1];

    my $con = CreateConnexionForOreon();
    while(1){
	my $sth1 = $con->prepare("SELECT ".$field.", service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
    	if (!$sth1->execute) {
	    writeLogFile("Error When ods get service field : " . $sth1->errstr . "\n");
    	}
	my $data = $sth1->fetchrow_hashref();
    	if (defined($data->{$field}) && $data->{$field}){
	    undef($service_id);
	    $sth1->finish();
	    $con->disconnect();
	    return $data->{$field};
    	} elsif ($data->{'service_template_model_stm_id'}){
	    $service_id = $data->{'service_template_model_stm_id'};
    	} else {
	    last;
	}
    }
}

# Return normal check interval for a service
# Parameters :
#  metric_id, dbcnx

sub getServiceCheckInterval($$){ # metric_id

    my $conO = $_[1];

    # Get service id
    $sth1 = $conO->prepare("SELECT service_id FROM index_data, metrics WHERE metric_id = '".$_[0]."' AND metrics.index_id = index_data.id ");
    if (!$sth1->execute) {
	writeLogFile("Error where getting service interval 2 : ".$sth1->errstr."\n");
    }
    my $data_hst_svc = $sth1->fetchrow_hashref();
    $sth1->finish();
    undef($sth1);

    # Get recursively data in service conf
    my $return = getMyServiceField($data_hst_svc->{'service_id'}, "service_normal_check_interval");
    undef($data_hst_svc);

    # Check if DB result is empty
    if (!defined($return)) {
	$return = 3;
    }

    return $return;
}

sub getServiceCheckIntervalWithSVCid($) { # metric_id
    my $conO = CreateConnexionForCentstorage();

    $sth1 = $conO->prepare("SELECT service_id FROM index_data WHERE id = '".$_[0]."'");
    if (!$sth1->execute) {writeLogFile("Error where getting service interval 2 : ".$sth1->errstr."\n");}
    my $data_hst_svc = $sth1->fetchrow_hashref();
    $sth1->finish();
    $conO->disconnect();
    undef($sth1);
    undef($data_metric);

    my $return = getMyServiceField($data_hst_svc->{'service_id'}, "service_normal_check_interval");
    undef($data_hst_svc);
    return $return;
}

sub getServiceCheckIntervalFromService($) { # service_id
    my $conO = CreateConnexionForCentstorage();

    $sth1 = $conO->prepare("SELECT service_id FROM index_data WHERE id = '".$_[0]."'");
    writeLogFile("Error where getting service interval 2 : ".$sth1->errstr."\n") if (!$sth1->execute);
    my $data_hst_svc = $sth1->fetchrow_hashref(); 
    $sth1->finish();

    my $return = getMyServiceField($data_hst_svc->{'service_id'}, "service_normal_check_interval");
    $conO->disconnect();
    undef($data_hst_svc);
    return $return;
}

1;

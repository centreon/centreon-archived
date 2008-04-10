###################################################################
# Centreon is developped with GPL Licence 2.0 
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

sub getServiceCheckInterval($){ # metric_id
	my $conO = CreateConnexionForCentstorage();
	my $sth1 = $conO->prepare("SELECT index_id FROM metrics WHERE metric_id = '".$_[0]."'");
    if (!$sth1->execute){writeLogFile("Error where getting service interval : ".$sth1->errstr."\n");}
    my $data_metric = $sth1->fetchrow_hashref();
    $sth1->finish();
    	
    $sth1 = $conO->prepare("SELECT service_id FROM index_data WHERE id = '".$data_metric->{'index_id'}."'");
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

sub getServiceCheckIntervalWithSVCid($){ # metric_id
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

sub getServiceCheckIntervalFromService($){ # service_id
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
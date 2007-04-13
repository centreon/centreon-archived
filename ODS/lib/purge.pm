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
 
sub CheckRestart(){
	my ($sth2, $data, $purgeinterval);
	my $last_restart;
	my $last_restart_stt;
	
	CheckMySQLConnexion();
	$sth2 = $con_oreon->prepare("SELECT oreon_path FROM general_opt LIMIT 1");
	if (!$sth2->execute) {writeLogFile("Error when getting oreon Path : " . $sth2->errstr . "\n");}
	$data = $sth2->fetchrow_hashref();
	my $STOPFILE = $data->{'oreon_path'} . "ODS/stopods.flag";
	undef($sth2);
	undef($data);
	
	while(2){
		CheckMySQLConnexion();
		$last_restart = getLastRestart();
    	$last_restart_stt = getLastRestartInMemory();
		if (!$last_restart_stt || $last_restart ne $last_restart_stt){
			CheckMySQLDrain();
			purgeRrdDB() if (getPurgeConfig());	
			check_HostServiceID();	
		}
		
		$purgeinterval = getPurgeInterval();
		for (my $i = 0;$i <= $purgeinterval;$i++){
			# Check if ods must leave
			return () if (-r $STOPFILE);
			# Sleep Time between To check
			sleep(1);	
		}
		undef($purgeinterval);
		undef($i);	
	}
}

sub CheckMySQLDrain(){
	my %base;
	my $data;
	my $data_hg;
	my %srv_list;
	my $sth3;
	
	my $sth2 = $con_oreon->prepare("SELECT service_service_id, host_host_id FROM host_service_relation WHERE hostgroup_hg_id IS NULL ");
	if (!$sth2->execute) {writeLogFile("Error in Drain function 2 : " . $sth2->errstr . "\n");}
	while ($data = $sth2->fetchrow_hashref()){
		$srv_list{$data->{'host_host_id'} ."_". $data->{'service_service_id'}} = 1;
	}
	undef($data);
	undef($sth2);

	$sth2 = $con_oreon->prepare("SELECT hostgroup_hg_id, service_service_id FROM host_service_relation WHERE hostgroup_hg_id IS NOT NULL ");
	if (!$sth2->execute) {writeLogFile("Error in Drain function 2 : " . $sth2->errstr . "\n");}
	while ($data = $sth2->fetchrow_hashref()){
		$sth3 = $con_oreon->prepare("SELECT * FROM hostgroup_relation WHERE hostgroup_hg_id = '".$data->{'hostgroup_hg_id'}."'");
		if (!$sth3->execute) {writeLogFile("Error in Drain function 2 : " . $sth2->errstr . "\n");}
		while ($data_hg = $sth3->fetchrow_hashref()){
			$srv_list{$data_hg->{'host_host_id'} ."_". $data->{'service_service_id'}} = 1;		
		}
	}
	undef($data);
	undef($sth2);
	undef($sth3);
	undef($data_hg);
	
	$sth2 = $con_ods->prepare("SELECT host_id, service_id FROM index_data WHERE `host_name` != 'OSL_Module' AND `host_name` != 'META_Module'");
	if (!$sth2->execute) {writeLogFile("Error in Drain function 3 : " . $sth2->errstr . "\n");}
	while ($data = $sth2->fetchrow_hashref()){
		if ($data->{'service_id'} && $data->{'host_id'} && !defined($srv_list{$data->{'host_id'}."_".$data->{'service_id'}})){
			my $sth2 = $con_ods->prepare("DELETE FROM index_data WHERE `service_id` = '".$data->{'service_id'}."' AND host_id = '".$data->{'host_id'}."'");
			if (!$sth2->execute) {writeLogFile("Error in Drain function 4 :" . $sth2->errstr . "\n");}
		}
	}
	undef($sth2);
	undef(%srv_list);
	undef($data);
	undef(%base);
}
 
1;
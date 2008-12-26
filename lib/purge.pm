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
 
sub CheckMySQLDrain(){
	my ($data, $data_hg, $sth3, %base, %srv_list);
	
	$con_oreon = DBI->connect("DBI:mysql:database=".$mysql_database_oreon.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	$con_ods = DBI->connect("DBI:mysql:database=".$mysql_database_ods.";host=".$mysql_host, $mysql_user, $mysql_passwd, {'RaiseError' => 0, 'PrintError' => 0, 'AutoCommit' => 1});
	
	my $sth2 = $con_oreon->prepare("SELECT service_service_id, host_host_id FROM host_service_relation WHERE hostgroup_hg_id IS NULL ");
	if (!$sth2->execute) {writeLogFile("Error in Drain function 2 : " . $sth2->errstr . "\n");}
	while ($data = $sth2->fetchrow_hashref()){
		$srv_list{$data->{'host_host_id'} ."_". $data->{'service_service_id'}} = 1;
	}
	$sth2->finish();
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
		$sth3->finish();
	}
	$sth2->finish();
	undef($data);
	undef($sth2);
	undef($sth3);
	undef($data_hg);
	
	my $flg = 0;
	my $sth;
	$sth = $con_ods->prepare("SELECT host_id, service_id FROM index_data WHERE `host_name` NOT LIKE '_Module_%'");
	if (!$sth->execute) {writeLogFile("Error in Drain function 3 : " . $sth->errstr . "\n");}
	while ($data = $sth->fetchrow_hashref()){
		if ($data->{'service_id'} && $data->{'host_id'} && !defined($srv_list{$data->{'host_id'}."_".$data->{'service_id'}})){
			my $data_svc;
			my $sth1 = $con_ods->prepare("SELECT metric_id FROM index_data, metrics WHERE index_data.host_id = '".$data->{'host_id'}."' AND index_data.service_id = '".$data->{'service_id'}."' AND metrics.index_id = index_data.id");
			if (!$sth1->execute) {writeLogFile("Error in Drain function 3 : " . $sth1->errstr . "\n");}
			while ($data_svc = $sth1->fetchrow_hashref()){
				$sth2 = $con_ods->prepare("DELETE FROM data_bin WHERE data_bin.id_metric = '".$data_svc->{'metric_id'}."'");	
				if (!$sth2->execute) {
					writeLogFile("Error when deleting Data for host ".$data->{'host_id'}." and svc ".$data->{'service_id'}." m : ".$data_svc->{'metric_id'}." :" . $sth2->errstr . "\n");
				}
				$sth2->finish();
				undef($sth2);
				$sth2 = $con_ods->prepare("DELETE FROM metrics WHERE metric_id = '".$data_svc->{'metric_id'}."'");	
				if (!$sth2->execute) {
					writeLogFile("Error when deleting Metrics for host ".$data->{'host_id'}." and svc ".$data->{'service_id'}." m : ".$data_svc->{'metric_id'}." : " . $sth2->errstr . "\n");
				}
				$sth2->finish();
				undef($sth2);
				$t++;
			}
			
			if ($t){
				$sth2 = $con_ods->prepare("DELETE FROM index_data WHERE `service_id` = '".$data->{'service_id'}."' AND host_id = '".$data->{'host_id'}."'");
				if (!$sth2->execute) {
					writeLogFile("Error when index for host ".$data->{'host_id'}." and svc ".$data->{'service_id'}." :" . $sth2->errstr . "\n");
				}
				$sth2->finish();
			}
			undef($sth2);
			undef($data_svc);
		}
	}
	$sth->finish();
	undef($sth);
	undef($sth2);
	undef(%srv_list);
	undef($data);
	undef(%base);
}
 
1;
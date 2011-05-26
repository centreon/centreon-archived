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
 
sub CheckMySQLDrain(){
    my ($data, $data_hg, $sth3, %base, %srv_list, %metricToDel);

    # Connecte MySQL To centreon and centstorage
	CheckMySQLConnexion();

    # Get services by hosts
    my $sth2 = $con_oreon->prepare("SELECT service_service_id, host_host_id FROM host_service_relation WHERE hostgroup_hg_id IS NULL ");
    if (!$sth2->execute) {
	writeLogFile("Error in Drain function 2 : " . $sth2->errstr . "\n");
    }
    while ($data = $sth2->fetchrow_hashref()){
	$srv_list{$data->{'host_host_id'} ."_". $data->{'service_service_id'}} = 1;
    }
    $sth2->finish();
    undef($data);
    undef($sth2);

    # Get service by Hostgroups
    $sth2 = $con_oreon->prepare("SELECT hostgroup_hg_id, service_service_id FROM host_service_relation WHERE hostgroup_hg_id IS NOT NULL ");
    if (!$sth2->execute) {
	writeLogFile("Error in Drain function 2 : " . $sth2->errstr);
    }
    while ($data = $sth2->fetchrow_hashref()){
	$sth3 = $con_oreon->prepare("SELECT * FROM hostgroup_relation WHERE hostgroup_hg_id = '".$data->{'hostgroup_hg_id'}."'");
	if (!$sth3->execute) {
	    writeLogFile("Error in Drain function 2 : " . $sth2->errstr);
	}
	while ($data_hg = $sth3->fetchrow_hashref()){
	    $srv_list{$data_hg->{'host_host_id'} ."_". $data->{'service_service_id'}} = 1;		
	}
	$sth3->finish();
	undef($sth3);
    }
    $sth2->finish();
    undef($data);
    undef($sth2);
    undef($data_hg);

    my $flg = 0;
    my $sth;
    $sth = $con_ods->prepare("SELECT host_id, service_id FROM index_data WHERE `host_name` NOT LIKE '_Module_%'");
    if (!$sth->execute) {
	writeLogFile("Error in Drain function 3 : " . $sth->errstr);
    } else {
	while ($data = $sth->fetchrow_hashref()) {
	    if ($data->{'service_id'} && $data->{'host_id'} && !defined($srv_list{$data->{'host_id'}."_".$data->{'service_id'}})){
		my $data_svc;
		my $t = 0;
		my $sth1 = $con_ods->prepare("SELECT metric_id FROM index_data, metrics WHERE index_data.host_id = '".$data->{'host_id'}."' AND index_data.service_id = '".$data->{'service_id'}."' AND metrics.index_id = index_data.id");
		if (!$sth1->execute) {
		    writeLogFile("Error in Drain function 3 : " . $sth1->errstr);
		}
		while ($data_svc = $sth1->fetchrow_hashref()) {
		    # Add Metric to delete in buffer
		    $sth2 = $con_ods->prepare("DELETE FROM metrics WHERE metric_id = '".$data_svc->{'metric_id'}."'");	
		    if (!$sth2->execute) {
			writeLogFile("Error when deleting Metrics for host ".$data->{'host_id'}." and svc ".$data->{'service_id'}." m : ".$data_svc->{'metric_id'}." : " . $sth2->errstr);
		    }
		    $sth2->finish();
		    undef($sth2);
		    $t++;
		}

		if ($t){
		    $sth2 = $con_ods->prepare("DELETE FROM index_data WHERE `service_id` = '".$data->{'service_id'}."' AND host_id = '".$data->{'host_id'}."'");
		    if (!$sth2->execute) {
			writeLogFile("Error when index for host ".$data->{'host_id'}." and svc ".$data->{'service_id'}." :" . $sth2->errstr);
		    }
		    $sth2->finish();
		}
		undef($sth2);
		undef($data_svc);
	    }
	}
    }

    $sth->finish();
    undef($sth);
    undef($sth2);
    undef(%srv_list);
    undef($data);
    undef(%base);
    $con_oreon->disconnect();
    $con_ods->disconnect();
}
 
1;

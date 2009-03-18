<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id: centAcl.php 7240 2008-12-11 18:46:04Z jmathis $
 * 
 */
 	
 	
 	
	include_once "DB.php";
	include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once $centreon_path."/www/DBconnect.php";
	include_once $centreon_path."/www/DBNDOConnect.php";
	include_once $centreon_path."/cron/centAcl-Func.php";
	
	/*
	 * Init values
	 */
	
	$debug = 0;
		
	$tabGroups = array();
	$DBRESULT1 =& $pearDB->query(	"SELECT DISTINCT acl_groups.acl_group_id, acl_resources.acl_res_id " .
									"FROM acl_res_group_relations, `acl_groups`, `acl_resources` " .
									"WHERE acl_groups.acl_group_id = acl_res_group_relations.acl_group_id " .
									"AND acl_res_group_relations.acl_res_id = acl_resources.acl_res_id " .
									"AND acl_groups.acl_group_activate = '1' ".			
									"AND acl_resources.changed = '1'");
	while ($result =& $DBRESULT1->fetchRow())
		$tabGroups[$result["acl_group_id"]] = 1;
	
	/*
	 * Purge data
	 */
	$strBegin = "INSERT INTO `centreon_acl` ( `host_name` , `service_description` , `host_id` , `service_id`,`group_id` ) VALUES ";

	$cpt = 0;	
	foreach ($tabGroups as $acl_group_id => $acl_res_id){
		$tabElem = array();		

		/*
		 * Delete old data for this group
		 */
		$DBRESULT =& $pearDBndo->query("DELETE FROM `centreon_acl` WHERE `group_id` = '".$acl_group_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
		/*
		 * Select 
		 */
				
		$DBRESULT2 =& $pearDB->query("SELECT `acl_resources`.`acl_res_id` FROM `acl_res_group_relations`, `acl_resources` " .
									"WHERE `acl_res_group_relations`.`acl_group_id` = '".$acl_group_id."' " .
									"AND `acl_res_group_relations`.acl_res_id = `acl_resources`.acl_res_id " .
									"AND `acl_resources`.acl_res_activate = '1'");			
		if ($debug)
			$time_start = microtime_float2();
		while ($res2 =& $DBRESULT2->fetchRow()){
			$Host = array();
			/* ------------------------------------------------------------------ */

			/*
			 * Get all Hosts 
			 */
			$DBRESULT3 =& $pearDB->query("SELECT host_id, host_name FROM `host`, `acl_resources_host_relations` WHERE acl_res_id = '".$res2["acl_res_id"]."' AND acl_resources_host_relations.host_host_id = host.host_id AND host.host_register = '1' AND host.host_activate = '1'");
		  	while ($h =& $DBRESULT3->fetchRow())
				$Host[$h["host_id"]] = $h["host_name"];
			$DBRESULT3->free();
				
		  	/*
		  	 * Get all host in hostgroups
		  	 */
			$DBRESULT3 =& $pearDB->query("SELECT `hg_id` FROM `hostgroup`, `acl_resources_hg_relations` WHERE acl_res_id = '".$res2["acl_res_id"]."' AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id");
	  		while ($hostgroup =& $DBRESULT3->fetchRow()){
				$DBRESULT4 =& $pearDB->query("SELECT host_host_id, host_name FROM `hostgroup_relation`, `host` WHERE hostgroup_hg_id = '".$hostgroup["hg_id"]."' AND host.host_id = hostgroup_relation.host_host_id");
	  			while ($host_hostgroup = $DBRESULT4->fetchRow())
					$Host[$host_hostgroup["host_host_id"]] = $host_hostgroup["host_name"];
	  		}
			$DBRESULT3->free();
	  		
	  		/*
	  		 * Get All exclude Hosts
	  		 */
	  		$DBRESULT3 =& $pearDB->query("SELECT `host_id` FROM `host`, `acl_resources_hostex_relations` WHERE acl_res_id = '".$res2["acl_res_id"]."' AND acl_resources_hostex_relations.host_host_id = host.host_id");
			if ($DBRESULT3->numRows())
		  		while ($h =& $DBRESULT3->fetchRow())
					if (isset($Host[$h["host_id"]]))
						unset($Host[$h["host_id"]]);
			$DBRESULT3->free();
			
			if (!count($Host)) {
				$flag_all_host = 0;
				$DBRESULT3 =& $pearDB->query("SELECT arsr.sc_id FROM acl_resources_sc_relations arsr, acl_resources ar WHERE arsr.acl_res_id = '".$res2["acl_res_id"]."' " .
											"AND arsr.acl_res_id = ar.acl_res_id " .
											"AND ar.acl_res_activate");
				if ($DBRESULT3->numRows())
					$flag_all_host = 1;
				$DBRESULT3->free();
				if (!$flag_all_host) {
					$DBRESULT3 =& $pearDB->query("SELECT arsr.sg_id FROM acl_resources_sg_relations arsr, acl_resources ar WHERE arsr.acl_res_id = '".$res2["acl_res_id"]."' " .
											"AND arsr.acl_res_id = ar.acl_res_id " .
											"AND ar.acl_res_activate = '1'");
					if ($DBRESULT3->numRows())
						$flag_all_host = 1;
					$DBRESULT3->free();
				}
				if ($flag_all_host) {
					$DBRESULT3 =& $pearDB->query("SELECT host_id, host_name FROM `host` WHERE host_register = '1' AND host_activate = '1'");
					while ($h =& $DBRESULT3->fetchRow())
						$Host[$h["host_id"]] = $h["host_name"];
					$DBRESULT3->free();
				}
			}
			
			
			$str = "";	
			foreach ($Host as $key => $value){
				$tab = getAuthorizedServicesHost2($key, $acl_group_id, $res2["acl_res_id"]);
				foreach ($tab as $desc => $id){
					if (!isset($tabElem[$value]))
						$tabElem[$value] = array();
					$tabElem[$value][$desc] = $key.",".$id;
				}	 
				unset($tab);
			}

			/*
			 * get all Service groups
			 */
			$DBRESULT3 =& $pearDB->query(	"SELECT host_name, host_id, service_description, service_id FROM `acl_resources_sg_relations`, `servicegroup_relation`, `host`, `service` " .
											"WHERE acl_res_id = '".$res2["acl_res_id"]."' " .
											"AND host.host_id = servicegroup_relation.host_host_id " .
											"AND service.service_id = servicegroup_relation.service_service_id " .
											"AND servicegroup_relation.servicegroup_sg_id = acl_resources_sg_relations.sg_id " .
											"AND service_activate = '1'");
			if ($DBRESULT3->numRows()) {
		  		while ($h =& $DBRESULT3->fetchRow()){
					if (!isset($tabElem[$h["host_name"]]))
						$tabElem[$h["host_name"]] = array();
		  			$tabElem[$h["host_name"]][$h["service_description"]] = $h["host_id"].",".$h["service_id"];
		  		}
			}
			$DBRESULT3->free();
			unset($Host);
			/* ------------------------------------------------------------------ */
		}
		$DBRESULT2->free();

		if ($debug) {
			$time_end = microtime_float2(); 
			$now = $time_end - $time_start; 
			print round($now,3) . _(" seconds \n");
		}
		
		if (count($tabElem)){
			foreach ($tabElem as $host => $svc_list){
				foreach ($svc_list as $desc => $t){
					if ($str != "")
						$str .= ', ';
					$id_tmp = split(",", $t);
					$str .= "('".$host."', '".$desc."', '".$id_tmp[0]."' , '".$id_tmp[1]."' , ".$acl_group_id.") ";
				}
			}
			$DBRESULTNDO =& $pearDBndo->query($strBegin.$str);
			if (PEAR::isError($DBRESULTNDO)) {
				print "DB Error : ".$DBRESULTNDO->getDebugInfo()."<br />";
			} else {
				$DBRESULT3 =& $pearDB->query("UPDATE `acl_resources` SET `changed` = '0'");
				if (PEAR::isError($DBRESULT3))
					print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
			}
		}	
		$cpt++;
	}
?>
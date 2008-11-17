<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * safety, contents, performance, merchantability, non-infringement or suitability for
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if OREON has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 * 
 */
	
	include_once "DB.php";
	include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once $centreon_path."/www/DBconnect.php";
	include_once $centreon_path."/www/DBNDOConnect.php";
	include_once $centreon_path."/www/include/common/common-Func.php";
	include_once $centreon_path."/www/include/common/common-Func-ACL.php";
	
	/*
	 * Init values
	 */
	
	$debug = 0;
	
	/*
	 * Init functions
	 */
	function microtime_float2() 	{
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}
	
	$tabGroups = array();
	$DBRESULT1 =& $pearDB->query(	"SELECT DISTINCT acl_groups.acl_group_id, acl_resources.acl_res_id " .
									"FROM acl_res_group_relations, `acl_groups`, `acl_resources` " .
									"WHERE acl_groups.acl_group_id = acl_res_group_relations.acl_group_id " .
									"AND acl_res_group_relations.acl_res_id = acl_resources.acl_res_id " .
									"AND `acl_resources`.`acl_res_activate` = '1' " .
									"AND acl_groups.acl_group_activate = '1' ".			
									"AND acl_resources.changed = '1'");
	while ($result =& $DBRESULT1->fetchRow())
		$tabGroups[$result["acl_group_id"]] = 1;
	
	/*
	 * Purge datas
	 */
	$strBegin = "INSERT INTO `centreon_acl` ( `host_name` , `service_description` , `group_id` ) VALUES ";

	$cpt = 0;	
	foreach ($tabGroups as $acl_group_id => $acl_res_id){
		$tabElem = array();
		
		if ($cpt == 0) {
			/*
			 * Delete old datas for this groups
			 */
			$DBRESULT =& $pearDBndo->query("DELETE FROM `centreon_acl` WHERE `group_id` = '".$acl_group_id."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";			
		}
		
		/*
		 * Select 
		 */
		
		$Host = array();
		$DBRESULT2 =& $pearDB->query("SELECT `acl_res_id` FROM `acl_res_group_relations` WHERE `acl_group_id` = '".$acl_group_id."'");			
		if ($debug)
			$time_start = microtime_float2();
		while ($res2 =& $DBRESULT2->fetchRow()){
	
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
			
			$str = "";	
			foreach ($Host as $key => $value){
				$tab = getAuthorizedServicesHost($key, $acl_group_id, $res2["acl_res_id"]);
				foreach ($tab as $desc => $id){
					if (!isset($tabElem[$value]))
						$tabElem[$value] = array();
					$tabElem[$value][$desc] = 1;
				}	 
				unset($tab);
			}

			/*
			 * get all Service groups
			 */
			$DBRESULT3 =& $pearDB->query(	"SELECT host_name, host_id, service_description FROM `acl_resources_sg_relations`, `servicegroup_relation`, `host`, `service` " .
											"WHERE acl_res_id = '".$res2["acl_res_id"]."' " .
												"AND host.host_id = servicegroup_relation.host_host_id " .
												"AND service.service_id = servicegroup_relation.service_service_id " .
												"AND servicegroup_relation.servicegroup_sg_id = acl_resources_sg_relations.sg_id");
			if ($DBRESULT3->numRows()) {
		  		while ($h =& $DBRESULT3->fetchRow()){
					if (!isset($tabElem[$h["host_id"]]))
						$tabElem[$h["host_id"]] = array();
		  			$tabElem[$h["host_name"]][$h["service_description"]] = 1;
		  		}
			}
			$DBRESULT3->free();
			
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
					$str .= "('".$host."', '".$desc."', ".$acl_group_id.") ";
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
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
 * SVN : $Id$
 * 
 */
 	
	include_once "DB.php";
	include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once $centreon_path."/cron/centAcl-Func.php";
	include_once $centreon_path."/www/class/centreonDB.class.php";
	
	/*
	 * Init values
	 */
	$debug = 0;
		
	/*
	 * Init DB connections
	 */
	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");
	$pearDBndo 	= new CentreonDB("ndo");
	
	/* ************************************************
	 *  Caching of all Data
	 * 
	 */
	print "Prepare Cache\n";
	
	$hostCache = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1'");
	while ($h =& $DBRESULT->fetchRow()) {
		$hostCache[$h["host_id"]] = $h["host_name"];
	}
	$DBRESULT->free();
	unset($h);
	
	/*
	 * Get all included Hosts 
	 */
	$hostIncCache = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name, acl_res_id FROM `host`, `acl_resources_host_relations` WHERE acl_resources_host_relations.host_host_id = host.host_id AND host.host_register = '1' AND host.host_activate = '1'");
  	while ($h =& $DBRESULT->fetchRow()) {
  		if (!isset($hostIncCache[$h["acl_res_id"]]))
  			$hostIncCache[$h["acl_res_id"]] = array();
  		$hostIncCache[$h["acl_res_id"]][$h["host_id"]] = $h["host_name"];
  	}
	$DBRESULT->free();
	
	/*
	 * Get all excluded Hosts 
	 */
	$hostExclCache = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name, acl_res_id FROM `host`, `acl_resources_hostex_relations` WHERE acl_resources_hostex_relations.host_host_id = host.host_id AND host.host_register = '1' AND host.host_activate = '1'");
  	while ($h =& $DBRESULT->fetchRow()) {
  		if (!isset($hostExclCache[$h["acl_res_id"]]))
  			$hostExclCache[$h["acl_res_id"]] = array();
  		$hostExclCache[$h["acl_res_id"]][$h["host_id"]] = $h["host_name"];
  	}
	$DBRESULT->free();
	
	/*
	 * Service Cache
	 */
	$svcCache = array();
	$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM `service` WHERE service_register = '1'");
  	while ($s =& $DBRESULT->fetchRow()) {
  		$svcCache[$s["service_id"]] = $s["service_description"];
  	}
	$DBRESULT->free();
	
	/*
	 * Host Service relation
	 */
	$hsRelation = array();
	$hgsRelation = array();
	$DBRESULT =& $pearDB->query("SELECT hostgroup_hg_id, host_host_id, service_service_id FROM host_service_relation");
	while ($sr =& $DBRESULT->fetchRow()) {
		if (isset($sr["host_host_id"]) && $sr["host_host_id"]) {
			if (!isset($hsRelation[$sr["host_host_id"]]))
				$hsRelation[$sr["host_host_id"]] = array();
			$hsRelation[$sr["host_host_id"]][$sr["service_service_id"]] = 1;			
		} else {
		/*
			if (!isset($hgsRelation[$sr["hostgroup_hg_id"]]))
				$hgsRelation[$sr["hostgroup_hg_id"]] = array();
			$hgsRelation[$sr["hostgroup_hg_id"]][$sr["service_service_id"]] = 1;
		*/
		}
		
	}
	$DBRESULT->free();
	
	/*
	 * Host Host relation 
	 */
	$hostHGRelation = array();
	$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup_relation");
	while ($hg =& $DBRESULT->fetchRow()) {
		if (!isset($hostHGRelation[$hg["hostgroup_hg_id"]]))
			$hostHGRelation[$hg["hostgroup_hg_id"]] = array();
		$hostHGRelation[$hg["hostgroup_hg_id"]][$hg["host_host_id"]] = $hg["host_host_id"];
	}
	$DBRESULT->free();
	unset($hg);
	
	/*
	 * Create Servive template modele Cache
	 */
	$svcTplCache = array();
	$DBRESULT =& $pearDB->query("SELECT service_template_model_stm_id, service_id FROM service");
	while ($tpl =& $DBRESULT->fetchRow()) {
		$svcTplCache[$tpl["service_id"]] = $tpl["service_template_model_stm_id"];
	}
	$DBRESULT->free();
	unset($tpl);
	
	$svcCatCache = array();
	$DBRESULT =& $pearDB->query("SELECT sc_id, service_service_id FROM `service_categories_relation`");
	while ($res =& $DBRESULT->fetchRow()) {
		if (!isset($svcCatCache[$res["service_service_id"]] ))
			$svcCatCache[$res["service_service_id"]]  = array();
		$svcCatCache[$res["service_service_id"]][$res["sc_id"]] = 1;
	}
	$DBRESULT->free();
	unset($res);
	
	print "End preparing Cache\n";
			
	/*
	 * Begin to build ACL
	 */
	$tabGroups = array();
	$DBRESULT1 =& $pearDB->query(	"SELECT DISTINCT acl_groups.acl_group_id, acl_resources.acl_res_id " .
									"FROM acl_res_group_relations, `acl_groups`, `acl_resources` " .
									"WHERE acl_groups.acl_group_id = acl_res_group_relations.acl_group_id " .
									"AND acl_res_group_relations.acl_res_id = acl_resources.acl_res_id " .
									"AND acl_groups.acl_group_activate = '1' ".			
									"AND acl_resources.changed = '1'");
	while ($result =& $DBRESULT1->fetchRow()) {
		$tabGroups[$result["acl_group_id"]] = 1;		
	}
	$DBRESULT1->free();
	unset($result);
	
	/*
	 * Purge data
	 */
	$strBegin = "INSERT INTO `centreon_acl` ( `host_name` , `service_description` , `host_id` , `service_id`,`group_id` ) VALUES ";

	$cpt = 0;	
	foreach ($tabGroups as $acl_group_id => $acl_res_id){
		$tabElem = array();		

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
			if (isset($hostIncCache[$res2["acl_res_id"]])) {
				foreach ($hostIncCache[$res2["acl_res_id"]] as $host_id => $host_name) {
					$Host[$host_id] = $host_name;
				}				
			}
			
		  	/*
		  	 * Get all host in hostgroups
		  	 */
			$DBRESULT =& $pearDB->query("SELECT `hg_id` FROM `hostgroup`, `acl_resources_hg_relations` WHERE acl_res_id = '".$res2["acl_res_id"]."' AND acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id");
	  		while ($hostgroup =& $DBRESULT->fetchRow()){
				if (isset($hostHGRelation[$hostgroup["hg_id"]])) {
					foreach ($hostHGRelation[$hostgroup["hg_id"]] as $host_id) {
						if ($hostCache[$host_id])
							$Host[$host_id] = $hostCache[$host_id];	
						else
							print "Host $host_id unknown !\n";
					}
				}
	  		}
			$DBRESULT->free();
	  		
	  		if (isset($hostExclCache[$res2["acl_res_id"]])) {
		  		foreach ($hostExclCache[$res2["acl_res_id"]] as $host_id => $host_name) {
					unset($Host[$host_id]);
		  		}
	  		}

			/*
			 * Give Authorized Categories
			 */
			$authorizedCategories = getAuthorizedCategories($acl_group_id, $res2["acl_res_id"]);
			
			$str = "";	
			foreach ($Host as $key => $value) {
				$tab = getAuthorizedServicesHost($key, $acl_group_id, $res2["acl_res_id"], $authorizedCategories);
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
			print round($now,3) . " " . _("seconds") . "\n";
		}
		
		/*
		 * Delete old data for this group
		 */
		$DBRESULT =& $pearDBndo->query("DELETE FROM `centreon_acl` WHERE `group_id` = '".$acl_group_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";		
		
		if (count($tabElem)) {
			foreach ($tabElem as $host => $svc_list){
				foreach ($svc_list as $desc => $t){
					if ($str != "")
						$str .= ', ';
					$id_tmp = split(",", $t);
					$desc = str_replace("#S#", "/", $desc);
					$desc = str_replace("#BS#", "\\", $desc);
					$str .= "('".$host."', '".$desc."', '".$id_tmp[0]."' , '".$id_tmp[1]."' , ".$acl_group_id.") ";
				}
			}
			
			/*
			 * Delete old data for this group
			 */
			$DBRESULT =& $pearDBndo->query("DELETE FROM `centreon_acl` WHERE `group_id` = '".$acl_group_id."'");
			
			/*
			 * Insert datas
			 */
			$DBRESULTNDO =& $pearDBndo->query($strBegin.$str);
			if (PEAR::isError($DBRESULTNDO)) {
				print "DB Error : ".$DBRESULTNDO->getDebugInfo()."<br />";
			} else {
				;//$DBRESULT3 =& $pearDB->query("UPDATE `acl_resources` SET `changed` = '0'");
			}
		}	
		$cpt++;
	}
?>
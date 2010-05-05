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
 
 	/*
	 * Init functions
	 */
	function microtime_float2() 	{
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}
	
	/*
	 * Return enable categories for this resource access
	 */
	function getAuthorizedCategories($groupstr, $res_id) {
		global $pearDB;
		
		if (strlen($groupstr) == 0)
			return array();
			
		$tab_categories = array();
		$request = "SELECT sc_id " .
					"FROM acl_resources_sc_relations, acl_res_group_relations, acl_resources " .
					"WHERE acl_resources_sc_relations.acl_res_id = acl_res_group_relations.acl_res_id " .
						"AND acl_res_group_relations.acl_group_id IN (".$groupstr.") " .
						"AND acl_resources_sc_relations.acl_res_id = acl_resources.acl_res_id " .
						"AND acl_resources.acl_res_id = '".$res_id."' " .
						"AND acl_res_activate = '1'";
		$DBRESULT =& $pearDB->query($request);
		while ($res =& $DBRESULT->fetchRow()) {
			$tab_categories[$res["sc_id"]] = $res["sc_id"];			
		}
		$DBRESULT->free();
	  	unset($res);
	  	unset($DBRESULT);		
	  	return $tab_categories;
	}
	
	function getServiceTemplateCategoryList($service_id = NULL)	{
		global $pearDB, $svcTplCache, $svcCatCache;

		$tabCategory = array();
		
		if (!$service_id) 
			return;
		
		if (isset($svcCatCache[$service_id])) {
			foreach ($svcCatCache[$service_id] as $ct_id => $flag) {
				$tabCategory[$ct_id] = $ct_id;
			} 
		}

		/*
		 * Init Table of template
		 */
		while (1) {
			if (isset($svcTplCache[$service_id]) && $svcTplCache[$service_id]) {
				if (isset($svcCatCache[$service_id])) {
					foreach ($svcCatCache[$service_id] as $ct_id => $flag) {
						$tabCategory[$ct_id] = $ct_id;
					} 
				}
				$service_id = $svcTplCache[$service_id];
			}
			else {
				return $tabCategory;
			}	
		}
	}
	
	function getACLSGForHost($pearDB, $host_id, $groupstr){
		global $svcCache, $sgCache;
		
		if (!$pearDB || !isset($host_id))
			return ;

		$svc = array();
        if (isset($sgCache[$groupstr])) {
            foreach ($sgCache[$groupstr] as $key => $tab) {
                foreach ($tab as $hostId => $tab2) {
                    if ($host_id == $hostId) {
                        foreach ($tab2 as $svcDesc => $svcId) {
                            $svc[$svcDesc] = $svcId;
                        }
                    }
                }
            }
        }
		return $svc;
		/*
		 * Init Acl Table
		 */
		$svc = array();
		$condition = "";
		if ($groupstr != "") {
			$condition = " WHERE `acl_group_id` IN (".$groupstr.") AND ";
		}			
		else {
			$condition = " WHERE ";
		}
		
		$DBRESULT =& $pearDB->query("SELECT argr.`acl_res_id` FROM `acl_res_group_relations` argr, `acl_resources` ar ".$condition." " .
									"argr.acl_res_id = ar.acl_res_id " .
									"AND ar.acl_res_activate = '1'");
		while ($res =& $DBRESULT->fetchRow()) {
			$DBRESULT2 =& $pearDB->query(	"SELECT `service_service_id` " .
											"FROM `servicegroup`, `acl_resources_sg_relations`, `servicegroup_relation` " .
											"WHERE `acl_res_id` = '".$res["acl_res_id"]."' " .
											"AND `acl_resources_sg_relations`.`sg_id` = `servicegroup`.`sg_id` " .
											"AND `servicegroup_relation`.`servicegroup_sg_id` = `servicegroup`.`sg_id` " .
											"AND `servicegroup_relation`.`host_host_id` = '".$host_id."'");	
			while ($service =& $DBRESULT2->fetchRow()) {
				if (isset($svcCache[$service["service_service_id"]])) {
					$svc[$svcCache[$service["service_service_id"]]] = $service["service_service_id"];
				}
			}
			$DBRESULT2->free();
		}
		$DBRESULT->free();
		return $svc;
	}
	
	function getAuthorizedServicesHost($host_id, $groupstr, $res_id, $authorizedCategories){
		global $pearDB, $svcCache, $hostCache;
		
		$tab_svc 	= getMyHostServicesByName($host_id);
		
		/*
		 * Get Service Groups
		 */
		$svc_SG 	= getACLSGForHost($pearDB, $host_id, $groupstr);
		
		$tab_services = array();
		if (count($authorizedCategories)) {
			if ($tab_svc) {
				foreach ($tab_svc as $svc_descr => $svc_id) {
					$tab = getServiceTemplateCategoryList($svc_id);
					foreach ($tab as $t){
						if (isset($authorizedCategories[$t])) {					
							$tab_services[$svc_descr] = $svc_id;
						}
					}
				}
			}
		}
		else if (hostIsAuthorized($host_id, $groupstr)){
			$tab_services = $tab_svc;
			if ($svc_SG) {
				foreach ($svc_SG as $key => $value) {
					$tab_services[$key] = $value;
				}
			}	
		}
		return $tab_services;
	}
	
	function hostIsAuthorized($host_id, $group_id) {
		global $pearDB;
		
		$query = "SELECT rhr.host_host_id " .
				"FROM acl_resources_host_relations rhr, acl_resources res, acl_res_group_relations rgr " .
				"WHERE rhr.acl_res_id = res.acl_res_id " .
				"AND res.acl_res_id = rgr.acl_res_id " .
				"AND rgr.acl_group_id = '".$group_id."' " .
				"AND rhr.host_host_id = '".$host_id."' " .
				"AND res.acl_res_activate = '1'";
		$DBRES =& $pearDB->query($query);
		if ($DBRES->numRows())
			return true;
		
		$query2 = "SELECT hgr.host_host_id FROM " .
				"hostgroup_relation hgr, acl_resources_hg_relations rhgr, acl_resources res, acl_res_group_relations rgr " .
				"WHERE rhgr.acl_res_id = res.acl_res_id " .
				"AND res.acl_res_id = rgr.acl_res_id " .
				"AND rgr.acl_group_id = '".$group_id."' " .
				"AND hgr.hostgroup_hg_id = rhgr.hg_hg_id " .
				"AND hgr.host_host_id = '".$host_id."' " .
				"AND res.acl_res_activate = '1' " .
				"AND hgr.host_host_id NOT IN (SELECT host_host_id FROM acl_resources_hostex_relations WHERE acl_res_id = rhgr.acl_res_id)";
		
		$DBRES2 =& $pearDB->query($query2);
		if (PEAR::isError($DBRES2))
			print "DB Error : ".$DBRES2->getDebugInfo()."<br />";
		if ($DBRES2->numRows())
			return true;				
		
		return false;
	}

	/*
	 * Retreive service description
	 */	
	function getMyHostServicesByName($host_id = NULL)	{
		global $pearDB, $hsRelation, $svcCache;
		
		if (!$host_id) 
			return;
		
		$hSvs = array();
		if (isset($hsRelation[$host_id])) {
			foreach ($hsRelation[$host_id] as $service_id => $flag) {
				$service_description = str_replace('#S#', '/', $svcCache[$service_id]);
				$service_description = str_replace('#BS#', '\\', $service_description);
				$hSvs[$service_description] = html_entity_decode($service_id, ENT_QUOTES);
			}
		}
		return $hSvs;
	}
?>
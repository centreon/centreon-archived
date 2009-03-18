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
		
	function getAuthorizedCategories2($groupstr, $res_id){
		global $pearDB;
		
		if (strlen($groupstr) == 0)
			return array();
			
		$tab_categories = array();				
		$DBRES =& $pearDB->query("SELECT acl_res_id FROM `acl_resources` WHERE acl_res_id = '".$res_id."' AND acl_res_activate = '1'");
			
		if (!$DBRES->numRows())
			return array();
		$request = "SELECT sc_id " .
					"FROM acl_resources_sc_relations, acl_res_group_relations " .
					"WHERE acl_resources_sc_relations.acl_res_id = acl_res_group_relations.acl_res_id " .
					"AND acl_res_group_relations.acl_group_id IN (".$groupstr.") " .
					"AND acl_resources_sc_relations.acl_res_id = '$res_id'";
					
		$DBRESULT =& $pearDB->query($request);
		while ($res =& $DBRESULT->fetchRow())			
			$tab_categories[$res["sc_id"]] = $res["sc_id"];		
	  	unset($res);
	  	unset($DBRESULT);		
	  	return $tab_categories;
	}
	
	function getServiceTemplateList3($service_id = NULL)	{
		global $pearDB;

		if (!$service_id) 
			return;
		/*
		 * Init Table of template
		 */
		$strTemplate = "'$service_id'";
		while (1)	{
			/*
			 * Get template Informations
			 */
			$DBRESULT =& $pearDB->query("SELECT service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$row =& $DBRESULT->fetchRow();
			if (isset($row["service_template_model_stm_id"]) && $row["service_template_model_stm_id"]){
				if ($strTemplate)
					$strTemplate .= ', ';
				$strTemplate .= "'".$row["service_template_model_stm_id"]."'";
				$service_id = $row["service_template_model_stm_id"];
			} else
				return $strTemplate;
		}
	}
	
	function getMyServiceName2($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		while (1) {
			$DBRESULT =& $pearDB->query("SELECT service_description, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$row =& $DBRESULT->fetchRow();
			if ($row["service_description"])	{
				$row["service_description"] = str_replace('#S#', "/", $row["service_description"]);
				$row["service_description"] = str_replace('#BS#', "\\", $row["service_description"]);
				return html_entity_decode($row["service_description"], ENT_QUOTES);
			} else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
	}
	
	function getACLSGForHost2($pearDB, $host_id, $groupstr){
		if (!$pearDB || !isset($host_id))
			return ;


		/*
		 * Init Acl Table
		 */
		$svc = array();
		
		$str_topo = "";
		$condition = "";		
		
		$condition = "";
		if ($groupstr != "")
			$condition = " WHERE `acl_group_id` IN (".$groupstr.") AND ";			
		else
			$condition = " WHERE ";
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
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while ($service =& $DBRESULT2->fetchRow())
				$svc[getMyServiceName2($service["service_service_id"])] = $service["service_service_id"];
			$DBRESULT2->free();
		}
		$DBRESULT->free();
		return $svc;
	}
	
	function getServicesCategories2($str){
		global $pearDB;
		
		$tab = array();
		$DBRESULT =& $pearDB->query("SELECT `sc_id` FROM `service_categories_relation` WHERE `service_service_id` IN (".$str.")");
		while ($res =& $DBRESULT->fetchRow())
			$tab[$res["sc_id"]] = $res["sc_id"];
		unset($res);		
		unset($DBRESULT);
		return $tab;
	}
	
	function getAuthorizedServicesHost2($host_id, $groupstr, $res_id){
		global $pearDB;
		
		$tab_svc 	= getMyHostServicesByName2($host_id);

		/*
		 * Get categories
		 */
		$tab_cat    = getAuthorizedCategories2($groupstr, $res_id);		


		/*
		 * Get Service Groups
		 */
		$svc_SG 	= getACLSGForHost2($pearDB, $host_id, $groupstr);
		
		$tab_services = array();
		if (count($tab_cat) || count($svc_SG)){
			if ($tab_svc) {
				foreach ($tab_svc as $svc_descr => $svc_id){
					$tmp = getServiceTemplateList3($svc_id);
					$tab = getServicesCategories2($tmp);					
					foreach ($tab as $t){
						if (isset($tab_cat[$t])) {							
							$tab_services[$svc_descr] = $svc_id;
						}
					}
				}
			}
			if ($svc_SG)
				foreach ($svc_SG as $key => $value)
					$tab_services[$key] = $value;
		} else {
			$tab_services = $tab_svc;	
		}
	  	return $tab_services;
	}
	
	function getMyHostServicesByName2($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$hSvs = array();
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM service, host_service_relation hsr WHERE hsr.host_host_id = '".$host_id."' AND hsr.service_service_id = service_id AND service_activate = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($elem =& $DBRESULT->fetchRow())	{
			$elem["service_description"] = str_replace('#S#', '/', $elem["service_description"]);
			$elem["service_description"] = str_replace('#BS#', '\\', $elem["service_description"]);
			$hSvs[$elem["service_description"]] = html_entity_decode($elem["service_id"], ENT_QUOTES);
		}
		$DBRESULT->free();
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM hostgroup_relation hgr, service, host_service_relation hsr" .
				" WHERE hgr.host_host_id = '".$host_id."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
				" AND service_id = hsr.service_service_id");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($elem =& $DBRESULT->fetchRow())
			$hSvs[$elem["service_description"]]	= html_entity_decode($elem["service_id"], ENT_QUOTES);
		$DBRESULT->free();
		return $hSvs;
	}
 
 
 ?>
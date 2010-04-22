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
	 * function table_not_exists()
	 * - This function test if a table exist in database.
	 *
	 * @param	string	$table_name (the name of the table to test)
	 * @return	int		0 			(return 0 if the table exists)
	 */
	function isUserAdmin($sid = NULL){
		if (!isset($sid))
			return ;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT contact_admin, contact_id FROM session, contact WHERE session.session_id = '".$sid."' AND contact.contact_id = session.user_id");
		$admin =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		
		$DBRESULT =& $pearDB->query("SELECT count(*) FROM `acl_group_contacts_relations` WHERE contact_contact_id = '".$admin["contact_id"]."'");
		$admin2 =& $DBRESULT->fetchRow();
		$DBRESULT->free();

		if ($admin["contact_admin"])
			return 1 ;
		return 0;
	}
	
	/*
	 * 
	 *
	 * <code>
	 * 
	 * </code>
	 *
	 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
	 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
	 * @return{TAB}int{TAB}Ma valeur de retour
	 */
	function getUserIdFromSID($sid = NULL){
		if (!isset($sid))
			return ;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT contact_id FROM session, contact WHERE session.session_id = '".$sid."' AND contact.contact_id = session.user_id");
		$admin =& $DBRESULT->fetchRow();
		unset($DBRESULT);
		if (isset($admin["contact_id"]))
			return $admin["contact_id"];
		return 0;
	}

	function table_not_exists($table_name) {
		global $pearDBndo;

		$DBRESULT =& $pearDBndo->query("SHOW TABLES LIKE '".$table_name."'");		
				
		if ($DBRESULT->numRows() > 0) {
			return 0;
		}
	}

	function myDecode($arg)	{
		return html_entity_decode($arg, ENT_QUOTES);
	}
	
	function getStatusColor($pearDB){
		$colors = array();
		$DBRESULT =& $pearDB->query("SELECT * FROM `options` WHERE `key` LIKE 'color%'");
		while ($c =& $DBRESULT->fetchRow())
			$colors[$c["key"]] = myDecode($c["value"]);
		$DBRESULT->free();
		return $colors;
	}

	function tidySearchKey($search, $advanced_search){
		if ($advanced_search == 1){
			if (isset($search) && !strstr($search, "*") && !strstr($search, "%"))
				$search = "'".$search."'";
			else if (isset($search) && isset($search[0]) && isset($search[strlen($search) - 1]) && $search[0] == "%" && $search[strlen($search) - 1] == "%")
				$search = str_replace("%", "", $search);
			else if (strpos($search, "%"))
				$search = str_replace("%", "*", $search);
		}
		return $search;
	}

	#
	## SMARTY
	#

	function initSmartyTpl($path = NULL, $tpl = NULL, $subDir = NULL)	{
		if (!$tpl)
			return;
		$tpl->template_dir = $path . $subDir;
		$tpl->compile_dir = "../GPL_LIB/SmartyCache/compile";
		$tpl->config_dir = "../GPL_LIB/SmartyCache/config";
		$tpl->cache_dir = "../GPL_LIB/SmartyCache/cache";

		$tpl->caching = 0;
		$tpl->compile_check = true;
		$tpl->force_compile = true;
		return $tpl;
	}

	function initSmartyTplForPopup($path = NULL, $tpl = NULL, $subDir = NULL, $centreon_path = NULL)	{
		if (!$tpl)
			return;
		$tpl->template_dir = $path . $subDir;
		$tpl->compile_dir = "$centreon_path/GPL_LIB/SmartyCache/compile";
		$tpl->config_dir = "$centreon_path/GPL_LIB/SmartyCache/config";
		$tpl->cache_dir = "$centreon_path/GPL_LIB/SmartyCache/cache";

		$tpl->caching = 0;
		$tpl->compile_check = true;
		$tpl->force_compile = true;
		return $tpl;
	}

	function initSmartyTplForLogs($path = NULL, $tpl = NULL)	{
		if (!$tpl)
			return;
		$tpl->template_dir = $path;
		$tpl->compile_dir = "../../../GPL_LIB/SmartyCache/compile";
		$tpl->config_dir = "../../../GPL_LIB/SmartyCache/config";
		$tpl->cache_dir = "../../../GPL_LIB/SmartyCache/cache";

		$tpl->caching = 0;
		$tpl->compile_check = true;
		$tpl->force_compile = true;
		return $tpl;
	}
	
	/*
	 * FORM VALIDATION
	 */
	function myTrim($str)	{
		global $form;
		$str = rtrim($str,'\\');
		return (trim($str));
	}

	/*
	 * Hosts Functions
	 */

	function getMyHostTemplateModel($host_id = NULL)	{
		global $pearDB;
		
		if (!$host_id) 
			return;
		
		$DBRESULT =& $pearDB->query("SELECT host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		if ($row["host_template_model_htm_id"])
			return $row["host_template_model_htm_id"];
		else
			NULL;
	}

	function getMyHostName($host_id = NULL)	{
		global $pearDB;
		
		if (!$host_id) 
			return;
		$DBRESULT =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$host_id."' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		if ($row["host_name"])
			return $row["host_name"];
	}

	function isAHostTpl($host_id = NULL)	{
		global $pearDB;
		
		if (!$host_id) 
			return;
		$DBRESULT =& $pearDB->query("SELECT host_register FROM host WHERE host_id = '".$host_id."' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		if ($row["host_register"])
			return true;
		else
			return false;
	}

	function getMyHostAddress($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_address, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["host_address"])
				return html_entity_decode($row["host_address"], ENT_QUOTES);
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}

	function getMyHostAddressByName($host_name = NULL)	{
		if (!$host_name) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_address, host_template_model_htm_id FROM host WHERE host_name = '".$host_name."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["host_address"])
				return html_entity_decode($row["host_address"], ENT_QUOTES);
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}

	function getMyHostParents($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_template_model_htm_id AS tpl FROM host WHERE host_id = '".$host_id."'");
			$host = clone($DBRESULT->fetchRow());
			$DBRESULT =& $pearDB->query("SELECT hpr.host_parent_hp_id FROM host_hostparent_relation hpr WHERE hpr.host_host_id = '".$host_id."'");
			if ($DBRESULT->numRows())
				return $DBRESULT;
			else if (isset($host["tpl"]) && $host["tpl"])
				$host_id = $host["tpl"];
			else
				return $DBRESULT;
		}
	}

	function getMyHostGroups($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$hgs = array();
		$DBRESULT =& $pearDB->query("SELECT hg.hg_name, hgr.hostgroup_hg_id FROM hostgroup hg, hostgroup_relation hgr WHERE hgr.host_host_id = '".$host_id."' AND hgr.hostgroup_hg_id = hg.hg_id");
		while ($hg =& $DBRESULT->fetchRow())
			$hgs[$hg["hostgroup_hg_id"]] = html_entity_decode($hg["hg_name"], ENT_QUOTES);
		return $hgs;
	}

	// Search community in Host, Host Tpl, Hostgroup, General Options
	function getMySnmpCommunity($host_id = NULL)	{
		if (!$host_id) 
			return;
		global $pearDB;
		$host_id_bkp = $host_id;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_snmp_community, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["host_snmp_community"])
				return $row["host_snmp_community"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else	{
				$hgs = getMyHostGroups($host_id_bkp);
				foreach ($hgs as $key=>$value)	{
					$DBRESULT =& $pearDB->query("SELECT hg_snmp_community FROM hostgroup WHERE hg_id = '".$key."' LIMIT 1");
					$row =& $DBRESULT->fetchRow();
					if ($row["hg_snmp_community"])
						return html_entity_decode($row["hg_snmp_community"], ENT_QUOTES);
				}
				$DBRESULT =& $pearDB->query("SELECT value FROM options WHERE `key` = 'snmp_community' LIMIT 1");
				$row =& $DBRESULT->fetchRow();
				if (isset($row["value"]))
					return html_entity_decode($row["value"], ENT_QUOTES);
				return NULL;
			}
		}
		return NULL;
	}

	// Search version in Host, Host Tpl, Hostgroup, General Options
	function getMySnmpVersion($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$host_id_bkp = $host_id;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_snmp_version, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["host_snmp_version"])
				return $row["host_snmp_version"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else	{
				$hgs = getMyHostGroups($host_id_bkp);
				foreach ($hgs as $key=>$value)	{
					$DBRESULT =& $pearDB->query("SELECT hg_snmp_version FROM hostgroup WHERE hg_id = '".$key."' LIMIT 1");
					$row =& $DBRESULT->fetchRow();
					if ($row["hg_snmp_version"])
						return html_entity_decode($row["hg_snmp_version"], ENT_QUOTES);
				}
				$DBRESULT =& $pearDB->query("SELECT value FROM options WHERE `key` = 'snmp_version' LIMIT 1");
				$row =& $DBRESULT->fetchRow();
				if (isset($row["value"]))
					return html_entity_decode($row["value"], ENT_QUOTES);
				else
					break;
				break;
			}
		}
		return NULL;
	}

	function getMyHostField($host_id = NULL, $field)	{
		if (!$host_id) 
			return;
		global $pearDB;
		
		$DBRESULT =& $pearDB->query("SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = '".$host_id."' ORDER BY `order` ASC");
		while ($row =& $DBRESULT->fetchRow()) {
			$DBRESULT2 =& $pearDB->query("SELECT `".$field."` FROM host WHERE host_id = '". $row['host_tpl_id']."'");
			while ($row2 =& $DBRESULT2->fetchRow()) {
				if (isset($row2[$field]) && $row2[$field])
					return $row2[$field];
				if ($tmp = getMyHostField($row['host_tpl_id'], $field))
					return $tmp;
			}
		}
		return NULL;
	}
	

	function getMyHostFieldOnHost($host_id = NULL, $field)	{
		global $pearDB;
		
		if (!$host_id) 
			return;
		
		$DBRESULT =& $pearDB->query("SELECT `".$field."` FROM host WHERE host_id = '".$host_id."' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		if (isset($row[$field]) && $row[$field])
			return $row[$field];
		else
			return 0;
		
	}

	/*
	 *  This functions is called recursively until it finds an ehi field
	 */
	function getMyHostExtendedInfoFieldFromMultiTemplates($host_id, $field){
		if (!$host_id) return NULL;
		global $pearDB;		
		$rq = "SELECT host_tpl_id " .
			"FROM host_template_relation " .
			"WHERE host_host_id = '".$host_id."' " .
			"ORDER BY `order`";
		$DBRESULT =& $pearDB->query($rq);
		while ($row =& $DBRESULT->fetchRow()) {
			$rq2 = "SELECT ehi.`".$field."` " .
				"FROM extended_host_information ehi " .
				"WHERE ehi.host_host_id = '".$row['host_tpl_id']."' LIMIT 1";
			$DBRESULT2 =& $pearDB->query($rq2);
			$row2 =& $DBRESULT2->fetchRow();
			if (isset($row2[$field]) && $row2[$field])
				return $row2[$field];
			else {
				if ($result_field = getMyHostExtendedInfoFieldFromMultiTemplates($row['host_tpl_id'], $field)) {
					return $result_field;
				}
			}
		}
		return NULL;
	}
	
	function getVersion() {
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT `value` FROM `options` WHERE `key` = 'nagios_version' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		return $row["value"];	
	}

	function getMyHostMacroFromMultiTemplates($host_id, $field){
		if (!$host_id) 
			return NULL;
		global $pearDB;		
		
		$rq = "SELECT host_tpl_id " .
			"FROM host_template_relation " .
			"WHERE host_host_id = '".$host_id."' " .
			"ORDER BY `order`";
		$DBRESULT =& $pearDB->query($rq);
		while ($row =& $DBRESULT->fetchRow()) {
			$rq2 = 	"SELECT macro.host_macro_value " .
					"FROM on_demand_macro_host macro " .
					"WHERE macro.host_host_id = '".$row["host_tpl_id"]."' AND macro.host_macro_name = '\$_HOST".$field."\$' LIMIT 1";
			$DBRESULT2 =& $pearDB->query($rq2);
			$row2 =& $DBRESULT2->fetchRow();
			if (isset($row2["host_macro_value"]) && $row2["host_macro_value"]) {
				$macroValue = str_replace("#S#", "/", $row2["host_macro_value"]);
				$macroValue = str_replace("#BS#", "\\", $macroValue);
			    return $macroValue;
			}
			else {
				if ($result_field = getMyHostMacroFromMultiTemplates($row['host_tpl_id'], $field)) {
					$macroValue = str_replace("#S#", "/", $result_field);
					$macroValue = str_replace("#BS#", "\\", $macroValue);
				    return $macroValue;
				}
			}
		}
		return NULL;
	}
	
	function getMyHostMacro($host_id = NULL, $field)	{
		if (!$host_id) 
			return;
		global $pearDB, $oreon;
		
		$version = getVersion();
		
		if ($version < 3) {
			while (1)	{
				$DBRESULT =& $pearDB->query("SELECT macro.host_macro_value, h.host_template_model_htm_id FROM host h, on_demand_macro_host macro WHERE macro.host_host_id = '".$host_id."' AND h.host_id = '".$host_id."' AND macro.host_macro_name = '\$_HOST".$field."\$' LIMIT 1");
				$row =& $DBRESULT->fetchRow();
				if (isset($row["host_macro_value"]) && $row["host_macro_value"])
					return $row["host_macro_value"];
				else if ($row["host_template_model_htm_id"])
					$host_id = $row["host_template_model_htm_id"];
				else
					return NULL;
			}
		} else if ($version >= 3) {			
			$rq = 	"SELECT macro.host_macro_value " .
					"FROM on_demand_macro_host macro " .
					"WHERE macro.host_host_id = '".$host_id."' AND macro.host_macro_name = '\$_HOST".$field."\$' LIMIT 1";
			$DBRESULT =& $pearDB->query($rq);
			$row =& $DBRESULT->fetchRow();
			if (isset($row["host_macro_value"]) && $row["host_macro_value"]) {
				$macroValue = str_replace("#S#", "/", $row["host_macro_value"]);
				$macroValue = str_replace("#BS#", "\\", $macroValue);
			    return $macroValue;
			}
			else {
				return getMyHostMacroFromMultiTemplates($host_id, $field);
			}
		}
			
	}
	
	function getMyServiceCategories($service_id = NULL) {
		global $pearDB, $oreon;
	
		if (!$service_id)
	    	return;
	  
	  	while (1) {
	  		$DBRESULT =& $pearDB->query("SELECT sc.sc_id FROM service_categories_relation scr, service_categories sc WHERE scr.service_service_id = '".$service_id."' AND sc.sc_id = scr.sc_id AND sc.sc_activate = '1'");
	    	if ($DBRESULT->numRows()) {
	    		$tabSC = array();
	      		while ($row =& $DBRESULT->fetchRow())
					$tabSC[$row["sc_id"]] = $row["sc_id"];
	      		return $tabSC;
	    	} else {
	      		$DBRESULT =& $pearDB->query("SELECT service_template_model_stm_id FROM service WHERE service_id = '".$service_id."'");
	      		$row =& $DBRESULT->fetchRow();
	      		if ($row["service_template_model_stm_id"])
					$service_id = $row["service_template_model_stm_id"];
	      		else
					return array();
	    	}
	  	}
	}
	
	function getMyCategorieName($sc_id = NULL) {
	  if (!$sc_id)
	    return;
	  global $pearDB, $oreon;
	
	  $DBRESULT =& $pearDB->query("SELECT sc_name FROM service_categories WHERE sc_id = '".$sc_id."'");
	  $row =& $DBRESULT->fetchRow();
	  return $row["sc_name"];
	}
	
	
	function getMyServiceMacro($service_id = NULL, $field)	{
		if (!$service_id) 
			return;
		global $pearDB, $oreon;
		
		$version = getVersion();
		
		if ($version < 3) {
			while (1)	{
				$DBRESULT =& $pearDB->query("SELECT macro.svc_macro_value, s.service_template_model_stm_id FROM service s, on_demand_macro_service macro WHERE macro.service_service_id = '".$service_id."' AND s.service_id = '".$service_id."' AND macro.svc_macro_name = 'macro.svc_macro_name = '\$_SERVICE".$field."\$' LIMIT 1");
				$row =& $DBRESULT->fetchRow();
				if (isset($row["svc_macro_value"]) && $row["svc_macro_value"])
					return $row["svc_macro_value"];
				else if ($row["service_template_model_stm_id"])
					$service_id = $row["service_template_model_stm_id"];
				else
					return NULL;
			}
		} else if ($version >= 3) {			
			$rq = 	"SELECT macro.svc_macro_value " .
					"FROM on_demand_macro_service macro " .
					"WHERE macro.svc_svc_id = '".$service_id."' AND macro.svc_macro_name = '\$_SERVICE".$field."\$' LIMIT 1";								
			$DBRESULT =& $pearDB->query($rq);
			$row = $DBRESULT->fetchRow();
			if (isset($row["svc_macro_value"]) && $row["svc_macro_value"]) {
				$macroValue = str_replace("#S#", "/", $row['svc_macro_value']);
                $macroValue = str_replace("#BS#", "\\", $macroValue);
			    return $macroValue;
			}
			else {
				$service_id = getMyServiceField($service_id, "service_template_model_stm_id");
				return getMyServiceMacro($service_id, $field);
			}
		}
	}

	function getMyHostExtendedInfoField($host_id = NULL, $field)	{
		if (!$host_id) return;
		global $pearDB, $oreon;
		
		$version = getVersion();
		
		if ($version < 3) {
			while (1)	{
				$DBRESULT =& $pearDB->query("SELECT ehi.`".$field."`, h.host_template_model_htm_id FROM host h, extended_host_information ehi WHERE ehi.host_host_id = '".$host_id."' AND h.host_id = '".$host_id."' LIMIT 1");
				$row =& $DBRESULT->fetchRow();
				if (isset($row[$field]) && $row[$field])
					return $row[$field];
				else if ($row["host_template_model_htm_id"])
					$host_id = $row["host_template_model_htm_id"];
				else
					return NULL;
			}
		} else if ($version >= 3) {			
			$rq = 	"SELECT ehi.`".$field."` " .
					"FROM extended_host_information ehi " .
					"WHERE ehi.host_host_id = '".$host_id."' LIMIT 1";								
			$DBRESULT =& $pearDB->query($rq);
			$row =& $DBRESULT->fetchRow();
			if (isset($row[$field]) && $row[$field])
				return $row[$field];
			else {
				return getMyHostExtendedInfoFieldFromMultiTemplates($host_id, $field);
			}
		}
	}

	function getMyHostExtendedInfoImage($host_id = NULL, $field, $flag1stLevel = NULL)	{
		global $pearDB, $oreon;
		
		if (!$host_id) 	
			return;

		if (isset($flag1stLevel) && $flag1stLevel) {
			$rq = "SELECT ehi.`".$field."` " .
					"FROM extended_host_information ehi " .
					"WHERE ehi.host_host_id = '".$host_id."' LIMIT 1";								
			$DBRESULT =& $pearDB->query($rq);
			$row =& $DBRESULT->fetchRow();
			if (isset($row[$field]) && $row[$field])	{
				$DBRESULT2 =& $pearDB->query("SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr WHERE vi.img_id = '".$row[$field]."' AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1");
				$row2 =& $DBRESULT2->fetchRow();
				if (isset($row2["dir_alias"]) && isset($row2["img_path"]) && $row2["dir_alias"] && $row2["img_path"])
					return $row2["dir_alias"]."/".$row2["img_path"];
			}
			else {
				if ($result_field = getMyHostExtendedInfoImage($host_id, $field))
					return $result_field;
			}
			return NULL;
		} else {
			$rq = "SELECT host_tpl_id " .
				"FROM host_template_relation " .
				"WHERE host_host_id = '".$host_id."' " .
				"ORDER BY `order`";
			$DBRESULT =& $pearDB->query($rq);
			while ($row =& $DBRESULT->fetchRow()) {
				$rq2 = "SELECT ehi.`".$field."` " .
						"FROM extended_host_information ehi " .
						"WHERE ehi.host_host_id = '".$row['host_tpl_id']."' LIMIT 1";								
				$DBRESULT2 =& $pearDB->query($rq2);
				$row2 =& $DBRESULT2->fetchRow();
				if (isset($row2[$field]) && $row2[$field])	{
					$DBRESULT3 =& $pearDB->query("SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr WHERE vi.img_id = '".$row2[$field]."' AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1");
					$row3 =& $DBRESULT3->fetchRow();
					if (isset($row3["dir_alias"]) && isset($row3["img_path"]) && $row3["dir_alias"] && $row3["img_path"])
						return $row3["dir_alias"]."/".$row3["img_path"];
				}
				else {
					if ($result_field = getMyHostExtendedInfoImage($row['host_tpl_id'], $field)) 					
						return $result_field;
				}
			}
			return NULL;
		}
	}
	
	function getImageFilePath($image_id)	{
		global $pearDB, $oreon;
		
		if (!$image_id) 	
			return;
			
		if (isset($image_id) && $image_id)	{
			$DBRESULT2 =& $pearDB->query("SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr WHERE vi.img_id = '".$image_id."' AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1");
			$row2 =& $DBRESULT2->fetchRow();
			if (isset($row2["dir_alias"]) && isset($row2["img_path"]) && $row2["dir_alias"] && $row2["img_path"])
				return $row2["dir_alias"]."/".$row2["img_path"];
		}
	}

	function getMyHostTemplateModels($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$tplArr = array();
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_name, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["host_name"])
				$tplArr[$host_id] = html_entity_decode($row["host_name"], ENT_QUOTES);
			else
				break;
			if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
		return ($tplArr);
	}

	function getMyHostMultipleTemplateModels($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$tplArr = array();
		$DBRESULT =& $pearDB->query("SELECT host_tpl_id FROM `host_template_relation` WHERE host_host_id = '".$host_id."' ORDER BY `order`");
		while($row =& $DBRESULT->fetchRow())	{
			$DBRESULT2 =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$row['host_tpl_id']."' LIMIT 1");
			$hTpl =& $DBRESULT2->fetchRow();			
			$tplArr[$row['host_tpl_id']] = html_entity_decode($hTpl["host_name"], ENT_QUOTES);
		}		
		return ($tplArr);
	}

	#
	## HOST GROUP
	#

	function getMyHostGroupName($hg_id = NULL)	{
		if (!$hg_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT hg_name FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		if ($row["hg_name"])
			return html_entity_decode($row["hg_name"], ENT_QUOTES);
		return NULL;
	}

	function getMyHostGroupHosts($hg_id = NULL, $searchHost = NULL)	{
		global $pearDB;
		
		if (!$hg_id) 
			return;
		
		$searchSTR = "";
		if (isset($searchHost) && $searchHost != "")
			$searchSTR = " AND h.host_name LIKE '%$searchHost%' ";
		
		$hosts = array();
		$DBRESULT =& $pearDB->query("SELECT hgr.host_host_id " .
									"FROM hostgroup_relation hgr, host h " .
									"WHERE hgr.hostgroup_hg_id = '".$hg_id."' " .
									"AND h.host_id = hgr.host_host_id $searchSTR " .
									"ORDER by h.host_name");
		while ($elem =& $DBRESULT->fetchRow())
			$hosts[$elem["host_host_id"]] = $elem["host_host_id"];
		$DBRESULT->free();
		unset($elem);
		return $hosts;
	}

	function getMyHostGroupCommunity($hg_id = NULL)	{
		global $pearDB;
		
		if (!$hg_id) 
			return;
		$DBRESULT =& $pearDB->query("SELECT hg_snmp_community FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		if ($row["hg_snmp_community"])
			return html_entity_decode($row["hg_snmp_community"], ENT_QUOTES);
		return NULL;
	}

	function getMyHostGroupVersion($hg_id = NULL)	{
		global $pearDB;
		
		if (!$hg_id) 
			return;
		$DBRESULT =& $pearDB->query("SELECT hg_snmp_version FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		if ($row["hg_snmp_version"])
			return html_entity_decode($row["hg_snmp_version"], ENT_QUOTES);
		return NULL;
	}

	#
	## SERVICE GROUP
	#

	function getMyServiceGroupName($sg_id = NULL)	{
		if (!$sg_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT sg_name FROM servicegroup WHERE sg_id = '".$sg_id."' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		if ($row["sg_name"])
			return html_entity_decode($row["sg_name"], ENT_QUOTES);
		return NULL;
	}

	function getMyServiceGroupServices($sg_id = NULL)	{
		global $pearDB;
		if (!$sg_id) 
			return;
		/*
		 * ServiceGroups by host
		 */
		$svs = array();
		$DBRESULT =& $pearDB->query("SELECT service_description, service_id, host_host_id, host_name " .
									"FROM servicegroup_relation, service, host " .
									"WHERE servicegroup_sg_id = '".$sg_id."' " .
									"AND servicegroup_relation.servicegroup_sg_id = servicegroup_sg_id " .
									"AND service.service_id = servicegroup_relation.service_service_id " .
									"AND servicegroup_relation.host_host_id = host.host_id " .
									"AND servicegroup_relation.host_host_id IS NOT NULL");
		while ($elem =& $DBRESULT->fetchRow())	{
			$svs[$elem["host_host_id"]."_".$elem["service_id"]] = db2str($elem["service_description"]) . ":::" . $elem["host_name"];
		}
		
		/*
		 * ServiceGroups by hostGroups
		 */		
		$DBRESULT =& $pearDB->query("SELECT service_description, service_id, hostgroup_hg_id, hg_name " .
									"FROM servicegroup_relation, service, hostgroup " .
									"WHERE servicegroup_sg_id = '".$sg_id."' " .
									"AND servicegroup_relation.servicegroup_sg_id = servicegroup_sg_id " .
									"AND service.service_id = servicegroup_relation.service_service_id " .
									"AND servicegroup_relation.hostgroup_hg_id = hostgroup.hg_id " .
									"AND servicegroup_relation.hostgroup_hg_id IS NOT NULL");
		while ($elem =& $DBRESULT->fetchRow())	{
			$hosts = getMyHostGroupHosts($elem["hostgroup_hg_id"]);
			foreach ($hosts as $key => $value)
				$svs[$key."_".$elem["service_id"]] = db2str($elem["service_description"]) . ":::" . $value;
		}
		$DBRESULT->free();
		return $svs;
	}

	function getMyServiceGroupActivateServices($sg_id = NULL)	{
		global $pearDB;
		if (!$sg_id) 
			return;
		/*
		 * ServiceGroups by host
		 */
		$svs = array();
		$DBRESULT =& $pearDB->query("SELECT service_description, service_id, host_host_id, host_name " .
									"FROM servicegroup_relation, service, host " .
									"WHERE servicegroup_sg_id = '".$sg_id."' " .
									"AND servicegroup_relation.servicegroup_sg_id = servicegroup_sg_id " .
									"AND service.service_id = servicegroup_relation.service_service_id " .
									"AND servicegroup_relation.host_host_id = host.host_id " .
									"AND servicegroup_relation.host_host_id IS NOT NULL " .
									"AND service.service_activate = '1'");
		while ($elem =& $DBRESULT->fetchRow())	{
			$svs[$elem["host_host_id"]."_".$elem["service_id"]] = db2str($elem["service_description"]) . ":::" . $elem["host_name"];
		}
		
		/*
		 * ServiceGroups by hostGroups
		 */		
		$DBRESULT =& $pearDB->query("SELECT service_description, service_id, hostgroup_hg_id, hg_name " .
									"FROM servicegroup_relation, service, hostgroup " .
									"WHERE servicegroup_sg_id = '".$sg_id."' " .
									"AND servicegroup_relation.servicegroup_sg_id = servicegroup_sg_id " .
									"AND service.service_id = servicegroup_relation.service_service_id " .
									"AND servicegroup_relation.hostgroup_hg_id = hostgroup.hg_id " .
									"AND servicegroup_relation.hostgroup_hg_id IS NOT NULL " .
									"AND service.service_activate = '1'");
		while ($elem =& $DBRESULT->fetchRow())	{
			$hosts = getMyHostGroupHosts($elem["hostgroup_hg_id"]);
			foreach ($hosts as $key => $value)
				$svs[$key."_".$elem["service_id"]] = db2str($elem["service_description"]) . ":::" . $value;
		}
		$DBRESULT->free();
		return $svs;
	}

	#
	## SERVICE
	#

	function getMyServiceField($service_id = NULL, $field)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT `".$field."`, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			$field_result = $row[$field];
			if ($row[$field])
				return $row[$field];
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
	}

	function getMyServiceExtendedInfoField($service_id = NULL, $field)	{
		if (!$service_id) return;
		global $pearDB;
		while(1) {
			$DBRESULT =& $pearDB->query("SELECT `extended_service_information`.`".$field."`, `service`.`service_template_model_stm_id` FROM `service`, `extended_service_information` WHERE `extended_service_information`.`service_service_id` = '".$service_id."' AND `service`.`service_id` = '".$service_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			$field_result = $row[$field];
			if ($row[$field])
				return $row[$field];
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
	}

	function getMyServiceExtendedInfoImage($service_id = NULL, $field)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT s.service_template_model_stm_id, `".$field."` FROM service s, extended_service_information esi WHERE s.service_id = '".$service_id."' AND esi.service_service_id = s.service_id LIMIT 1");
			$row =& $DBRESULT->fetchRow();			
			if (isset($row[$field]) && $row[$field])	{
				$DBRESULT =& $pearDB->query("SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr WHERE vi.img_id = '".$row[$field]."' AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1");
				$row =& $DBRESULT->fetchRow();
				if (isset($row["dir_alias"]) && isset($row["img_path"]) && $row["dir_alias"] && $row["img_path"])
					return $row["dir_alias"]."/".$row["img_path"];
			}
			else	{
				if ($row["service_template_model_stm_id"])
					$service_id = $row["service_template_model_stm_id"];
				else
					return NULL;
			}
		}
	}



	function getMyServiceName($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		while (1) {
			$DBRESULT =& $pearDB->query("SELECT service_description, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["service_description"])	{
				return html_entity_decode(db2str($row["service_description"]), ENT_QUOTES);
			} else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
	}

	function getMyServiceAlias($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT service_alias, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["service_alias"])	{
				return html_entity_decode(db2str($row["service_alias"]), ENT_QUOTES);
			}
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
	}

	function getMyServiceGraphID($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT esi.graph_id, service_template_model_stm_id FROM service, extended_service_information esi WHERE service_id = '".$service_id."' AND esi.service_service_id = service_id LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["graph_id"])
				return $row["graph_id"];
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
		return NULL;
	}

	function getMyServiceID($service_description = NULL, $host_id = NULL, $hg_id = NULL)	{
		if (!$service_description && (!$host_id || !$hg_id)) 
			return;
		global $pearDB;
		$service_description = str2db($service_description);
		if ($host_id)	{
			$DBRESULT =& $pearDB->query("SELECT service_id FROM service, host_service_relation hsr " .
									"WHERE hsr.host_host_id = '".$host_id."' AND hsr.service_service_id = service_id " .
									"AND service_description = '".$service_description."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			# Service is directely link to a host, no problem
			if ($row["service_id"])
				return $row["service_id"];
			# The Service might be link with a HostGroup
			$DBRESULT =& $pearDB->query("SELECT service_id FROM hostgroup_relation hgr, service, host_service_relation hsr" .
									" WHERE hgr.host_host_id = '".$host_id."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
									" AND service_id = hsr.service_service_id AND service_description = '".$service_description."'");
			$row =& $DBRESULT->fetchRow();
			if ($row["service_id"])
				return $row["service_id"];
		}
		if ($hg_id)	{
			$DBRESULT =& $pearDB->query("SELECT service_id FROM service, host_service_relation hsr WHERE hsr.hostgroup_hg_id = '".$hg_id."' AND hsr.service_service_id = service_id AND service_description = '".$service_description."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["service_id"])
				return $row["service_id"];
		}
		return NULL;
	}

	function getMyHostServices($host_id = NULL)	{
		global $pearDB;
		
		if (!$host_id) 
			return;
		
		$hSvs = array();
		
		/*
		 * Get Services attached to hosts
		 */
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM service, host_service_relation hsr WHERE hsr.host_host_id = '".$host_id."' AND hsr.service_service_id = service_id");
		while ($elem =& $DBRESULT->fetchRow())	{
			$hSvs[$elem["service_id"]] = html_entity_decode(db2str($elem["service_description"]), ENT_QUOTES);
		}
		$DBRESULT->free();
		
		/*
		 * Get Services attached to hostgroups
		 */
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM hostgroup_relation hgr, service, host_service_relation hsr" .
				" WHERE hgr.host_host_id = '".$host_id."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
				" AND service_id = hsr.service_service_id");
		while ($elem =& $DBRESULT->fetchRow()){
			$hSvs[$elem["service_id"]]	= html_entity_decode(db2str($elem["service_description"]), ENT_QUOTES);
		}
		$DBRESULT->free();
		asort($hSvs);
		return $hSvs;
	}
	
	function getMyHostActiveServices($host_id = NULL, $search = NULL)	{
		global $pearDB;
		
		if (!$host_id) 
			return;
		
		$hSvs = array();
		
		$searchSTR = "";
		if (isset($search) && $search)
			$searchSTR = " AND `service_description` LIKE '%$search%'";
		
		/*
		 * Get Services attached to hosts
		 */
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM service, host_service_relation hsr WHERE hsr.host_host_id = '".$host_id."' AND hsr.service_service_id = service_id AND service_activate = '1' $searchSTR");
		while ($elem =& $DBRESULT->fetchRow())	{
			$hSvs[$elem["service_id"]] = html_entity_decode(db2str($elem["service_description"]), ENT_QUOTES);
		}
		$DBRESULT->free();
		
		/*
		 * Get Services attached to hostgroups
		 */
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM hostgroup_relation hgr, service, host_service_relation hsr" .
				" WHERE hgr.host_host_id = '".$host_id."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
				" AND service_id = hsr.service_service_id AND service_activate = '1' $searchSTR ");
		while ($elem =& $DBRESULT->fetchRow()) {
			$hSvs[$elem["service_id"]]	= html_entity_decode(db2str($elem["service_description"]), ENT_QUOTES);
		}
		$DBRESULT->free();
		asort($hSvs);
		return $hSvs;
	}

	function getMyHostServicesByName($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$hSvs = array();
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM service, host_service_relation hsr WHERE hsr.host_host_id = '".$host_id."' AND hsr.service_service_id = service_id");
		while ($elem =& $DBRESULT->fetchRow())	{
			$hSvs[db2str($elem["service_description"])] = html_entity_decode($elem["service_id"], ENT_QUOTES);
		}
		$DBRESULT->free();
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM hostgroup_relation hgr, service, host_service_relation hsr" .
				" WHERE hgr.host_host_id = '".$host_id."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
				" AND service_id = hsr.service_service_id");
		while ($elem =& $DBRESULT->fetchRow())
			$hSvs[db2str($elem["service_description"])]	= html_entity_decode($elem["service_id"], ENT_QUOTES);
		$DBRESULT->free();
		return $hSvs;
	}

	function getAllMyServiceHosts($service_id = NULL)	{
		if (!$service_id) 
			return;
		global $pearDB;
		$hosts = array();
		$DBRESULT =& $pearDB->query("SELECT host_host_id, hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		while ($elem =& $DBRESULT->fetchRow())	{
			if ($elem["host_host_id"])
				$hosts[$elem["host_host_id"]] = $elem["host_host_id"];
			else if ($elem["hostgroup_hg_id"]){
				$DBRESULT2 =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$elem["hostgroup_hg_id"]."'");
				while ($elem2 =& $DBRESULT2->fetchRow())
					$hosts[$elem2["host_host_id"]] = $elem2["host_host_id"];
				$DBRESULT2->free();
			}
		}
		$DBRESULT->free();
		return $hosts;
	}

	function getMyServiceHosts($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		$hosts = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT host_host_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		while ($elem =& $DBRESULT->fetchRow())
			if ($elem["host_host_id"])
				$hosts[$elem["host_host_id"]] = $elem["host_host_id"];
		$DBRESULT->free();
		return $hosts;
	}

	function getMyServiceHostGroups($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		$hgs = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		while ($elem =& $DBRESULT->fetchRow())
			if ($elem["hostgroup_hg_id"])
				$hgs[$elem["hostgroup_hg_id"]] = $elem["hostgroup_hg_id"];
		$DBRESULT->free();
		return $hgs;
	}

	function getMyServiceTPLID($service_description = NULL)	{
		if (!$service_description) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT service_id FROM service WHERE service_description = '".htmlentities(str2db($service_description), ENT_QUOTES)."' AND service_register = '0' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		if ($row["service_id"])
			return $row["service_id"];
		return NULL;
	}

	function isACheckGraphService($service_id = NULL)	{
		if (!$service_id)	return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT command_command_id, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["command_command_id"])	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM command WHERE command_id = '".$row["command_command_id"]."' LIMIT 1");
				$row2 =& $DBRESULT2->fetchRow();
				if (strstr($row2["command_name"], "check_graph_"))
					return true;
				else
					return false;
			}
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				return NULL;
		}
		return NULL;
	}

	function getMyServiceTemplateModels($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		$tplArr = array();
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT service_description, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["service_description"])
				$tplArr[$service_id] = html_entity_decode(db2str($row["service_description"]), ENT_QUOTES);
			else
				break;
			if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
		return ($tplArr);
	}

	#
	## COMMAND
	#

	function getMyCheckCmdName($service_id = NULL)	{
		if (!$service_id)	return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT command_command_id, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["command_command_id"])	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM command WHERE command_id = '".$row["command_command_id"]."' LIMIT 1");
				$row2 =& $DBRESULT2->fetchRow();
				return ($row2["command_name"]);
			}
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				return NULL;
		}
		return NULL;
	}

	function getMyCheckCmdArg($service_id = NULL)	{
		if (!$service_id)	return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT command_command_id_arg, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["command_command_id_arg"])	{
				return (db2str($row["command_command_id_arg"]));
			}
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				return NULL;
		}
		return NULL;
	}

	function getMyCheckCmdParam($service_id = NULL)	{
		if (!$service_id)	return;
		global $pearDB;
		$cmd = NULL;
		$arg = NULL;
		$DBRESULT =& $pearDB->query("SELECT command_command_id, command_command_id_arg FROM service WHERE service_id = '".$service_id."' LIMIT 1");
		$row =& $DBRESULT->fetchRow();
		if ($row["command_command_id_arg"] && !$row["command_command_id"])	{
			$cmd = getMyCheckCmdName($service_id);
			return $cmd.db2str($row["command_command_id_arg"]);
		}
		else if($row["command_command_id"] && !$row["command_command_id_arg"])	{
			$DBRESULT2 =& $pearDB->query("SELECT command_name FROM command WHERE command_id = '".$row["command_command_id"]."' LIMIT 1");
			$row2 =& $DBRESULT2->fetchRow();
			// Uncomment if we want to take the template arg by default if it's not define in the current service 
			//$arg = getMyCheckCmdArg($service_id);
			return $row2["command_name"].$arg;
		}
		else if($row["command_command_id"] && $row["command_command_id_arg"])	{
			$DBRESULT2 =& $pearDB->query("SELECT command_name FROM command WHERE command_id = '".$row["command_command_id"]."' LIMIT 1");
			$row2 =& $DBRESULT2->fetchRow();
			return $row2["command_name"].db2str($row["command_command_id_arg"]);
		}
		else
			return NULL;
	}
	
	#
	## Upload conf needs
	#

	function getMyHostID($host_name = NULL)	{
		if (!$host_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT host_id FROM host WHERE host_name = '".htmlentities($host_name, ENT_QUOTES)."' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["host_id"];
		}
		return NULL;
	}

	function getMyHostGroupID($hostgroup_name = NULL)	{
		if (!$hostgroup_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT hg_id FROM hostgroup WHERE hg_name = '".htmlentities(str2db($hostgroup_name), ENT_QUOTES)."' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["hg_id"];
		}
		return NULL;
	}

	function getMyServiceGroupID($servicegroup_name = NULL)	{
		if (!$servicegroup_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT sg_id FROM servicegroup WHERE sg_name = '".htmlentities(str2db($servicegroup_name), ENT_QUOTES)."' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["sg_id"];
		}
		return NULL;
	}

	function getMyContactID($contact_name = NULL)	{
		if (!$contact_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT contact_id FROM contact WHERE contact_name = '".htmlentities($contact_name, ENT_QUOTES)."' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["contact_id"];
		}
		return NULL;
	}

	function getMyContactGroupID($cg_name = NULL)	{
		if (!$cg_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT cg_id FROM contactgroup WHERE cg_name = '".htmlentities($cg_name, ENT_QUOTES)."' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["cg_id"];
		}
		return NULL;
	}

	function getMyCommandID($command_name = NULL)	{
		if (!$command_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT command_id FROM command WHERE command_name = '".htmlentities($command_name, ENT_QUOTES)."' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["command_id"];
		}
		return NULL;
	}

	function getMyTPID($tp_name = NULL)	{
		if (!$tp_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT tp_id FROM timeperiod WHERE tp_name = '".htmlentities($tp_name, ENT_QUOTES)."' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["tp_id"];
		}
		return NULL;
	}

	#
	## GRAPHS
	#

	function getDefaultMetaGraph ($meta_id = NULL)	{
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT graph_id FROM meta_service WHERE meta_id = '".$meta_id."' LIMIT 1");
		$gt =& $DBRESULT->fetchRow();
		if ($gt["graph_id"])
			return $gt["graph_id"];
		else {
			$DBRESULT =& $pearDB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
			if ($DBRESULT->numRows())	{
				$gt =& $DBRESULT->fetchRow();
				return $gt["graph_id"];
			}
		}
		$DBRESULT =& $pearDB->query("SELECT graph_id FROM giv_graphs_template LIMIT 1");
		if ($DBRESULT->numRows())	{
			$gt =& $DBRESULT->fetchRow();
			return $gt["graph_id"];
		}
		return NULL;
	}

	function getDefaultGraph($service_id = NULL, $rrdType = NULL) {
		global $pearDB;
		
		$gt["graph_id"] = getMyServiceGraphID($service_id);
		if ($gt["graph_id"])
			return $gt["graph_id"];
		else {
			$command_id = getMyServiceField($service_id, "command_command_id");
			$DBRESULT =& $pearDB->query("SELECT graph_id FROM command WHERE `command_id` = '".$command_id."'");
			if ($DBRESULT->numRows())	{
				$gt =& $DBRESULT->fetchRow();
				return $gt["graph_id"];
			}	
		}
		$DBRESULT =& $pearDB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$gt =& $DBRESULT->fetchRow();
			return $gt["graph_id"];
		}
		return NULL;
	}

	function getDefaultDS()	{
		global $pearDB;
		$ds = array();
		$DBRESULT =& $pearDB->query("SELECT compo_id FROM giv_components_template WHERE default_tpl1 = '1' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$ds =& $DBRESULT->fetchRow();
			return $ds["compo_id"];
		}
		return NULL;
	}

	# Nagios Images
	function return_image_list($mode = 0, $rep = NULL, $full = true, $origin_path = NULL) {
		global $pearDB;
		
		$images = array();
		
		if ($full)
			$images = array(NULL => NULL);
		
		$is_not_an_image = array(".","..","README","readme","LICENCE","licence");
		$is_a_valid_image = array(
			0 => array('png'=>'png'),
			1 => array('gif'=>'gif', 'png'=>'png', 'jpg'=>'jpg'),
			2 => array('gif'=>'gif', 'png'=>'png', 'jpg'=>'jpg', 'gd2'=>'gd2')
		);
		
		$DBRESULT =& $pearDB->query("SELECT img_id, img_name, img_path, dir_name FROM view_img_dir, view_img, view_img_dir_relation vidr WHERE img_id = vidr.img_img_id AND dir_id = vidr.dir_dir_parent_id ORDER BY dir_name, img_name");
		$dir_name = NULL;
		$dir_name2 = NULL;
		$cpt = 1;
		while ($elem =& $DBRESULT->fetchRow())	{
			$dir_name = $elem["dir_name"];
			if ($dir_name2 != $dir_name)	{
				$dir_name2 = $dir_name;
				$images["REP_".$cpt] = $dir_name;
				$cpt++;
			}
			$ext = NULL;
			$pinfo = pathinfo($elem["img_path"]);
			if (isset($pinfo["extension"]) && isset($is_a_valid_image[$mode][$pinfo["extension"]]))
				$ext = "&nbsp;(".$pinfo["extension"].")";
			$images[$elem["img_id"]] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".html_entity_decode($elem["img_name"], ENT_NOQUOTES).$ext;
		}
		return ($images);
	}

	function getLangs(){
		$langs = array();
		$chemintotal = "./locale/";
		$default = "en_US";
		
		$langs["en_US"] = "en_US";
		if ($handle  = opendir($chemintotal))   {
		    while ($file = readdir($handle))
		    	if (is_dir("$chemintotal/$file") && strcmp($file, ".") && strcmp($file, ".."))
		      		$langs[$file] = $file;
			closedir($handle);
		}
		return $langs;
	}
	
	function getLangsByDir($chemintotal){
		$langs = "";
		if ($handle  = opendir($chemintotal))   {
		    while ($file = readdir($handle))
		    	if (!is_dir("$chemintotal/$file") && strcmp($file, "index.php") && strcmp($file, "index.html") && strcmp($file, "index.ihtml")) {
					$tab = split('\.', $file);
		      		$langs .= "-".$tab[0] . " ";
		      	}
			closedir($handle);
		}
		return $langs;
	}
	
	function getAllHostgroups(){
		global $pearDB;
		$hgs = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM hostgroup ORDER BY `hg_name`");
		while ($hg =& $DBRESULT->fetchRow())
			$hgs[$hg["hg_id"]] = $hg["hg_name"];
		return $hgs;
	}
	
	function service_has_graph($host, $service){
		global $pearDBO;
		if (is_numeric($host) && is_numeric($service)){
			$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` WHERE host_id = '".$host."' AND service_id = '".$service."'");
			if($DBRESULT->numRows() > 0)
				return true;
		}
		if (!is_numeric($host) && !is_numeric($service)){
			$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` WHERE host_name = '".$host."' AND service_description = '".$service."'");
			
			if($DBRESULT->numRows() > 0)
				return true;
		}
		return false;	
	}
	
	function host_has_one_or_more_GraphService($host_id){
		global $pearDBO, $lca, $is_admin;
	
		$services = getMyHostServices($host_id);
		foreach ($services as $svc_id => $svc_name)
			if (service_has_graph($host_id, $svc_id) && ($is_admin || (!$is_admin && isset($lca["LcaHost"][$host_id][$svc_id]))))
				return true;
		return false;	
	}
	
	function SGIsNotEmpty($sg_id){
		global $pearDBO;
		$data = getMyServiceGroupServices($sg_id);
		return count($data);
	}
	
	function HG_has_one_or_more_host($hg_id){
		global $pearDBO, $pearDB, $pearDBndo, $access, $is_admin, $lca, $hoststr, $servicestr;
				
		if ($is_admin)
			$DBRESULT =& $pearDB->query("SELECT hgr.host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$hg_id."'");
		else
			$DBRESULT =& $pearDB->query("SELECT hgr.host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$hg_id."' AND hgr.host_host_id IN ($hoststr)");
		while ($h =& $DBRESULT->fetchRow()) {
			if ($is_admin)
				return true;
			else
				$DBRESULT2 =& $pearDBO->query("SELECT service_id FROM index_data WHERE index_data.host_id = '".$h["host_host_id"]."' AND service_id IN ($servicestr)");
			while ($flag =& $DBRESULT2->fetchRow())
					return true;
			
		}
		return false;	
	}
	
	function getMyHostServiceID($service_id = NULL){
		if (!$service_id) 
			return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT host_id FROM host h,host_service_relation hsr WHERE h.host_id = hsr.host_host_id AND hsr.service_service_id = '".$service_id."' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["host_id"];
		}
		return NULL;		
	}
	
	if (!function_exists('getNDOInformations')){
		function getNDOInformations(){
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT db_name, db_prefix, db_user, db_pass, db_host FROM cfg_ndo2db LIMIT 1;");
			$conf_ndo =& $DBRESULT->fetchRow();
			unset($DBRESULT);
			return $conf_ndo;		
		}
	}

	/* 
	 * function getNDOPrefix()
	 * - This function return NDOPrefix tables.
	 *
	 * @return	string	$conf_ndo["db_prefix"]	(string contains prefix like "nagios_")
	 */		
	
	function getNDOPrefix(){
		global $pearDB;
		
		$DBRESULT =& $pearDB->query("SELECT db_prefix FROM cfg_ndo2db LIMIT 1");
		if(PEAR::isError($DBRESULT)) {
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
		$conf_ndo =& $DBRESULT->fetchRow();
		unset($DBRESULT);
		return $conf_ndo["db_prefix"];		
	}
	
	
	/* Ajax tests */
	function get_error($motif){
		$buffer = null;
		$buffer .= '<reponse>';
		$buffer .= $motif;
		$buffer .= '</reponse>';
		header('Content-Type: text/xml');
		echo $buffer;
		exit(0);
	}

	function check_injection($sid){
		if (!isset($sid))
			$sid = $_GET["sid"];
		/*
		 * 
		if (preg_match("/\</", strtoupper($sid), $matches) ||
			preg_match("/\>/", strtoupper($sid), $matches) ||
			preg_match("/\;/", strtoupper($sid), $matches) ||
			preg_match("/UNION/", strtoupper($sid), $matches) ||
			preg_match("/ALL/", strtoupper($sid), $matches) ||
			preg_match("/OR/", strtoupper($sid), $matches) ||
			preg_match("/AND/", strtoupper($sid), $matches) ||
			preg_match("/ORDER/", strtoupper($sid), $matches) ||
			preg_match("/SELECT/", strtoupper($sid), $matches) ||
			preg_match("/WHERE/", strtoupper($sid), $matches)) {
			get_error(_('sql injection detected'));
			return 1;
		}*/
		return 0;
	}
	/* End Ajax Test */

	function isHostLocalhost($pearDB, $host_name = NULL){
		if (!isset($host_name))
			return 0;
		$DBRESULT =& $pearDB->query("SELECT `localhost` FROM nagios_server, ns_host_relation, host WHERE host.host_name = '$host_name' AND host.host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = nagios_server.id LIMIT 1");
		$nagios_server =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		if (isset($nagios_server['localhost']))
			return $nagios_server['localhost'];
		return 0;
	}
	
	function isPollerLocalhost($pearDB, $id = NULL){
		if (!isset($id))
			return 0;
		$DBRESULT =& $pearDB->query("SELECT `localhost` FROM nagios_server WHERE nagios_server.id = '$id' LIMIT 1");
		$nagios_server =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		if (isset($nagios_server['localhost']))
			return $nagios_server['localhost'];
		return 0;
	}
	
	function GetMyHostPoller($pearDB, $host_name = NULL){
		if (!isset($host_name))
			return 0;
		$DBRESULT =& $pearDB->query("SELECT `id` FROM nagios_server, ns_host_relation, host WHERE host.host_name = '$host_name' AND host.host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = nagios_server.id LIMIT 1");
		$nagios_server =& $DBRESULT->fetchRow();
		if (isset($nagios_server['id']))
			return $nagios_server['id'];
		return 0;
	}
	
	function check_session($sid, $pearDB){
		if (isset($sid) && !check_injection($sid)){
			$sid = htmlentities($sid);
			$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
			if ($session =& $res->fetchRow()){
				return $session["user_id"];
			} else
				get_error('bad session id');		
		} else
			get_error('need session identifiant !');
		return 0;
	}
	
	/*
	 * This functions purges the var, remove all the quotes
	 * and everything that comes after a semi-colon
	 */
	function purgeVar($myVar){
		$myVar = str_replace("\'", '', $myVar);	
		$myVar = str_replace("\"", '', $myVar);	
		$tab_myVar = split(";", $myVar);
		$mhost = $tab_myVar[0];
		unset($tab_myVar);
		return $myVar;
	}
	

	function db2str($string) {
		$string = str_replace('#BR#', "\\n", $string);
		$string = str_replace('#T#', "\\t", $string);
		$string = str_replace('#R#', "\\r", $string);
		$string = str_replace('#S#', "/", $string);
		$string = str_replace('#BS#', "\\", $string);
		return $string;
	}

	function str2db($string) {
		$string = str_replace("\\n", '#BR#', $string);
		$string = str_replace("\\t", '#T#', $string);
		$string = str_replace("\\r", '#R#', $string);
		$string = str_replace("/", '#S#', $string);
		$string = str_replace("\\", '#BS#', $string);
		return $string;
	}


?>
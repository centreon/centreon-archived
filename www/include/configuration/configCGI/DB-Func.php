<?php
/*
 * Copyright 2005-2010 MERETHIS
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
	function testCgiExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('cgi_id');
		$DBRESULT =& $pearDB->query("SELECT cgi_name, cgi_id FROM cfg_cgi WHERE cgi_name = '".htmlentities($name, ENT_QUOTES)."'");
		$cgi =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $cgi["cgi_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $cgi["cgi_id"] != $id)
			return false;
		else
			return true;
	}	
	
	function enableCGIInDB ($cgi_id = null)	{
		if (!$cgi_id) return;
		global $pearDB;
		global $oreon;
		$DBRESULT =& $pearDB->query("UPDATE cfg_cgi SET cgi_activate = '0'");
		$DBRESULT =& $pearDB->query("UPDATE cfg_cgi SET cgi_activate = '1' WHERE cgi_id = '".$cgi_id."'");		
	}
	
	function disableCGIInDB ($cgi_id = null)	{
		if (!$cgi_id) return;
		global $pearDB;
		global $oreon;
		$DBRESULT =& $pearDB->query("UPDATE cfg_cgi SET cgi_activate = '0' WHERE cgi_id = '".$cgi_id."'");
		$DBRESULT =& $pearDB->query("SELECT MAX(cgi_id) FROM cfg_cgi WHERE cgi_id != '".$cgi_id."'");
		$maxId =& $DBRESULT->fetchRow();
		if (isset($maxId["MAX(cgi_id)"]))	{
			$DBRESULT =& $pearDB->query("UPDATE cfg_cgi SET cgi_activate = '1' WHERE cgi_id = '".$maxId["MAX(cgi_id)"]."'");
		}
	}
	
	function deleteCGIInDB ($cgi = array())	{
		global $pearDB;
		foreach($cgi as $key=>$value)
			$DBRESULT =& $pearDB->query("DELETE FROM cfg_cgi WHERE cgi_id = '".$key."'");
		$DBRESULT =& $pearDB->query("SELECT cgi_id FROM cfg_cgi WHERE cgi_activate = '1'");		  
		if (!$DBRESULT->numRows())	{
			$DBRESULT =& $pearDB->query("SELECT MAX(cgi_id) FROM cfg_cgi");
			$cgi_id = $DBRESULT->fetchRow();
			$DBRESULT =& $pearDB->query("UPDATE cfg_cgi SET cgi_activate = '1' WHERE cgi_id = '".$cgi_id["MAX(cgi_id)"]."'");
		}
	}
	
	function multipleCGIInDB ($cgi = array(), $nbrDup = array())	{
		foreach($cgi as $key=>$value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM cfg_cgi WHERE cgi_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["cgi_id"] = '';
			$row["cgi_activate"] = '0';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "cgi_name" ? ($cgi_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testCgiExistence($cgi_name))	{
					$val ? $rq = "INSERT INTO cfg_cgi VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
				}
			}
		}
	}
	
	function updateCGIInDB ($cgi_id = NULL)	{
		if (!$cgi_id) return;
		updateCGI($cgi_id);
	}	
	
	function insertCGIInDB ($ret = array())	{
		$cgi_id = insertCGI($ret);
		return ($cgi_id);
	}
	
	function insertCGI($ret = array())	{
		global $form;
		global $pearDB;
		global $oreon;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `cfg_cgi` ( `cgi_id` , `cgi_name` , `main_config_file` , `physical_html_path` , `url_html_path` , " .
				"`nagios_check_command` , `use_authentication` , `default_user_name` , `authorized_for_system_information` , " .
				"`authorized_for_system_commands` , `authorized_for_configuration_information` , `authorized_for_all_hosts` , " .
				"`authorized_for_all_host_commands` , `authorized_for_all_services` , `authorized_for_all_service_commands` , " .
				"`statusmap_background_image` , `default_statusmap_layout` , `statuswrl_include` , `default_statuswrl_layout` , " .
				"`refresh_rate` , `host_unreachable_sound` , `host_down_sound` , `service_critical_sound` , `service_warning_sound` , " .
				"`service_unknown_sound` , `ping_syntax` , `cgi_comment` , `cgi_activate` )" .
				" VALUES (";
		$rq .= "NULL, ";
        isset($ret["cgi_name"]) && $ret["cgi_name"] != NULL ? $rq .= "'".htmlentities($ret["cgi_name"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["main_config_file"]) && $ret["main_config_file"] != NULL ? $rq .= "'".htmlentities($ret["main_config_file"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["physical_html_path"]) && $ret["physical_html_path"] != NULL ? $rq .= "'".htmlentities($ret["physical_html_path"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["url_html_path"]) && $ret["url_html_path"] != NULL ? $rq .= "'".htmlentities($ret["url_html_path"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["nagios_check_command"]) && $ret["nagios_check_command"] != NULL ? $rq .= "'".htmlentities($ret["nagios_check_command"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["use_authentication"]["use_authentication"]) && $ret["use_authentication"]["use_authentication"] != NULL ? $rq .= "'".$ret["use_authentication"]["use_authentication"]."', " : $rq .= "NULL, ";
        isset($ret["default_user_name"]) && $ret["default_user_name"] != NULL ? $rq .= "'".htmlentities($ret["default_user_name"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["authorized_for_system_information"]) && $ret["authorized_for_system_information"] != NULL ? $rq .= "'".htmlentities($ret["authorized_for_system_information"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["authorized_for_system_commands"]) && $ret["authorized_for_system_commands"] != NULL ? $rq .= "'".htmlentities($ret["authorized_for_system_commands"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["authorized_for_configuration_information"]) && $ret["authorized_for_configuration_information"] != NULL ? $rq .= "'".htmlentities($ret["authorized_for_configuration_information"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["authorized_for_all_hosts"]) && $ret["authorized_for_all_hosts"] != NULL ? $rq .= "'".htmlentities($ret["authorized_for_all_hosts"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["authorized_for_all_host_commands"]) && $ret["authorized_for_all_host_commands"] != NULL ? $rq .= "'".htmlentities($ret["authorized_for_all_host_commands"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["authorized_for_all_services"]) && $ret["authorized_for_all_services"] != NULL ? $rq .= "'".htmlentities($ret["authorized_for_all_services"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["authorized_for_all_service_commands"]) && $ret["authorized_for_all_service_commands"] != NULL ? $rq .= "'".htmlentities($ret["authorized_for_all_service_commands"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["statusmap_background_image"]) && $ret["statusmap_background_image"] != NULL ? $rq .= "'".htmlentities($ret["statusmap_background_image"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["default_statusmap_layout"]) && $ret["default_statusmap_layout"] != NULL ? $rq .= "'".htmlentities($ret["default_statusmap_layout"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["statuswrl_include"]) && $ret["statuswrl_include"] != NULL ? $rq .= "'".htmlentities($ret["statuswrl_include"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["default_statuswrl_layout"]) && $ret["default_statuswrl_layout"] != NULL ? $rq .= "'".htmlentities($ret["default_statuswrl_layout"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["refresh_rate"]) && $ret["refresh_rate"] != NULL ? $rq .= "'".htmlentities($ret["refresh_rate"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["host_unreachable_sound"]) && $ret["host_unreachable_sound"] != NULL ? $rq .= "'".htmlentities($ret["host_unreachable_sound"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["host_down_sound"]) && $ret["host_down_sound"] != NULL ? $rq .= "'".htmlentities($ret["host_down_sound"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["service_critical_sound"]) && $ret["service_critical_sound"] != NULL ? $rq .= "'".htmlentities($ret["service_critical_sound"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["service_warning_sound"]) && $ret["service_warning_sound"] != NULL ? $rq .= "'".htmlentities($ret["service_warning_sound"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["service_unknown_sound"]) && $ret["service_unknown_sound"] != NULL ? $rq .= "'".htmlentities($ret["service_unknown_sound"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["ping_syntax"]) && $ret["ping_syntax"] != NULL ? $rq .= "'".htmlentities($ret["ping_syntax"], ENT_QUOTES)."', " : $rq .= "NULL, ";
        isset($ret["cgi_comment"]) && $ret["cgi_comment"] != NULL ? $rq .= "'".htmlentities($ret["cgi_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["cgi_activate"]["cgi_activate"]) && $ret["cgi_activate"]["cgi_activate"] != NULL ? $rq .= "'".$ret["cgi_activate"]["cgi_activate"]."')" : $rq .= "'0')";
		$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(cgi_id) FROM cfg_cgi");
		$cgi_id = $DBRESULT->fetchRow();
		if (isset($ret["cgi_activate"]["cgi_activate"]) && $ret["cgi_activate"]["cgi_activate"])	{
			$DBRESULT =& $pearDB->query("UPDATE cfg_cgi SET cgi_activate = '0' WHERE cgi_id != '".$cgi_id["MAX(cgi_id)"]."'");
			$oreon->CGIcfg = array();
			$DBRESULT =& $pearDB->query("SELECT * FROM `cfg_cgi` WHERE `cgi_activate` = '1' LIMIT 1");
			$oreon->CGIcfg = $DBRESULT->fetchRow();
		}
		return ($cgi_id["MAX(cgi_id)"]);
	}
	
	function updateCGI($cgi_id = null)	{
		if (!$cgi_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE cfg_cgi SET ";
        isset($ret["cgi_name"]) && $ret["cgi_name"] != NULL ? $rq .= "cgi_name = '".htmlentities($ret["cgi_name"], ENT_QUOTES)."', " : $rq .= "cgi_name = NULL, ";
        isset($ret["main_config_file"]) && $ret["main_config_file"] != NULL ? $rq .= "main_config_file = '".htmlentities($ret["main_config_file"], ENT_QUOTES)."', " : $rq .= "main_config_file = NULL, ";
        isset($ret["physical_html_path"]) && $ret["physical_html_path"] != NULL ? $rq .= "physical_html_path = '".htmlentities($ret["physical_html_path"], ENT_QUOTES)."', " : $rq .= "physical_html_path = NULL, ";
        isset($ret["url_html_path"]) && $ret["url_html_path"] != NULL ? $rq .= "url_html_path = '".htmlentities($ret["url_html_path"], ENT_QUOTES)."', " : $rq .= "url_html_path = NULL, ";
        isset($ret["nagios_check_command"]) && $ret["nagios_check_command"] != NULL ? $rq .= "nagios_check_command = '".htmlentities($ret["nagios_check_command"], ENT_QUOTES)."', " : $rq .= "nagios_check_command = NULL, ";
        isset($ret["use_authentication"]["use_authentication"]) && $ret["use_authentication"]["use_authentication"] != NULL ? $rq .= "use_authentication = '".$ret["use_authentication"]["use_authentication"]."', " : $rq .= "use_authentication = NULL, ";
        isset($ret["default_user_name"]) && $ret["default_user_name"] != NULL ? $rq .= "default_user_name = '".htmlentities($ret["default_user_name"], ENT_QUOTES)."', " : $rq .= "default_user_name = NULL, ";
        isset($ret["authorized_for_system_information"]) && $ret["authorized_for_system_information"] != NULL ? $rq .= "authorized_for_system_information = '".htmlentities($ret["authorized_for_system_information"], ENT_QUOTES)."', " : $rq .= "authorized_for_system_information = NULL, ";
        isset($ret["authorized_for_system_commands"]) && $ret["authorized_for_system_commands"] != NULL ? $rq .= "authorized_for_system_commands = '".htmlentities($ret["authorized_for_system_commands"], ENT_QUOTES)."', " : $rq .= "authorized_for_system_commands = NULL, ";
        isset($ret["authorized_for_configuration_information"]) && $ret["authorized_for_configuration_information"] != NULL ? $rq .= "authorized_for_configuration_information = '".htmlentities($ret["authorized_for_configuration_information"], ENT_QUOTES)."', " : $rq .= "authorized_for_configuration_information = NULL, ";
        isset($ret["authorized_for_all_hosts"]) && $ret["authorized_for_all_hosts"] != NULL ? $rq .= "authorized_for_all_hosts = '".htmlentities($ret["authorized_for_all_hosts"], ENT_QUOTES)."', " : $rq .= "authorized_for_all_hosts = NULL, ";
        isset($ret["authorized_for_all_host_commands"]) && $ret["authorized_for_all_host_commands"] != NULL ? $rq .= "authorized_for_all_host_commands = '".htmlentities($ret["authorized_for_all_host_commands"], ENT_QUOTES)."', " : $rq .= "authorized_for_all_host_commands = NULL, ";
        isset($ret["authorized_for_all_services"]) && $ret["authorized_for_all_services"] != NULL ? $rq .= "authorized_for_all_services = '".htmlentities($ret["authorized_for_all_services"], ENT_QUOTES)."', " : $rq .= "authorized_for_all_services = NULL, ";
        isset($ret["authorized_for_all_service_commands"]) && $ret["authorized_for_all_service_commands"] != NULL ? $rq .= "authorized_for_all_service_commands = '".htmlentities($ret["authorized_for_all_service_commands"], ENT_QUOTES)."', " : $rq .= "authorized_for_all_service_commands = NULL, ";
        isset($ret["statusmap_background_image"]) && $ret["statusmap_background_image"] != NULL ? $rq .= "statusmap_background_image = '".htmlentities($ret["statusmap_background_image"], ENT_QUOTES)."', " : $rq .= "statusmap_background_image = NULL, ";
        isset($ret["default_statusmap_layout"]) && $ret["default_statusmap_layout"] != NULL ? $rq .= "default_statusmap_layout = '".htmlentities($ret["default_statusmap_layout"], ENT_QUOTES)."', " : $rq .= "default_statusmap_layout = NULL, ";
        isset($ret["statuswrl_include"]) && $ret["statuswrl_include"] != NULL ? $rq .= "statuswrl_include = '".htmlentities($ret["statuswrl_include"], ENT_QUOTES)."', " : $rq .= "statuswrl_include = NULL, ";
        isset($ret["default_statuswrl_layout"]) && $ret["default_statuswrl_layout"] != NULL ? $rq .= "default_statuswrl_layout = '".htmlentities($ret["default_statuswrl_layout"], ENT_QUOTES)."', " : $rq .= "default_statuswrl_layout = NULL, ";
        isset($ret["refresh_rate"]) && $ret["refresh_rate"] != NULL ? $rq .= "refresh_rate = '".htmlentities($ret["refresh_rate"], ENT_QUOTES)."', " : $rq .= "refresh_rate = NULL, ";
        isset($ret["host_unreachable_sound"]) && $ret["host_unreachable_sound"] != NULL ? $rq .= "host_unreachable_sound = '".htmlentities($ret["host_unreachable_sound"], ENT_QUOTES)."', " : $rq .= "host_unreachable_sound = NULL, ";
        isset($ret["host_down_sound"]) && $ret["host_down_sound"] != NULL ? $rq .= "host_down_sound = '".htmlentities($ret["host_down_sound"], ENT_QUOTES)."', " : $rq .= "host_down_sound = NULL, ";
        isset($ret["service_critical_sound"]) && $ret["service_critical_sound"] != NULL ? $rq .= "service_critical_sound = '".htmlentities($ret["service_critical_sound"], ENT_QUOTES)."', " : $rq .= "service_critical_sound = NULL, ";
        isset($ret["service_warning_sound"]) && $ret["service_warning_sound"] != NULL ? $rq .= "service_warning_sound = '".htmlentities($ret["service_warning_sound"], ENT_QUOTES)."', " : $rq .= "service_warning_sound = NULL, ";
        isset($ret["service_unknown_sound"]) && $ret["service_unknown_sound"] != NULL ? $rq .= "service_unknown_sound = '".htmlentities($ret["service_unknown_sound"], ENT_QUOTES)."', " : $rq .= "service_unknown_sound = NULL, ";
        isset($ret["ping_syntax"]) && $ret["ping_syntax"] != NULL ? $rq .= "ping_syntax = '".htmlentities($ret["ping_syntax"], ENT_QUOTES)."', " : $rq .= "ping_syntax = NULL, ";
        isset($ret["cgi_comment"]) && $ret["cgi_comment"] != NULL ? $rq .= "cgi_comment = '".htmlentities($ret["cgi_comment"], ENT_QUOTES)."', " : $rq .= "cgi_comment = NULL, ";
		isset($ret["cgi_activate"]["cgi_activate"]) && $ret["cgi_activate"]["cgi_activate"] != NULL ? $rq .= "cgi_activate = '".$ret["cgi_activate"]["cgi_activate"]."' " : $rq .= "cgi_activate = '0' ";
		$rq .= "WHERE cgi_id = '".$cgi_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if ($ret["cgi_activate"]["cgi_activate"])
			enableCGIInDB($cgi_id);
	}
?>
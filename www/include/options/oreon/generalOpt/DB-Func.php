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

	if (!isset($oreon))
		exit();

	function updateOption($pearDB, $key, $value) {
		/*
		 * Purge
		 */
		$DBRESULT =& $pearDB->query("DELETE FROM `options` WHERE `key` = '$key'");
		
		/*
		 * Add
		 */
		$DBRESULT =& $pearDB->query("INSERT INTO `options` (`key`, `value`) VALUES ('$key', '$value')");
	}

	function is_valid_path($path) {
		if (is_dir($path))
		    return true;
		 else
		 	return false;
	 }

	function is_readable_path($path) {
		if (is_dir($path) && is_readable($path))
			return true;
		return false;
	}

	function is_executable_binary($path)  {
		if (is_file($path) && is_executable($path))
			return true;
		return false;
	}

	function is_writable_path($path)      {
		if (is_dir($path) && is_writable($path))
			return true;
		return false;
	}

	function is_writable_file($path)      {
		if (is_file($path) && is_writable($path))
			return true;
		return false;
	}

	function is_writable_file_if_exist($path = NULL)      {
		if (!$path) return true;
		if (is_file($path) && is_writable($path))
			return true;
		return false;
	}

	function updateGeneralOptInDB ($gopt_id = NULL)	{
		if (!$gopt_id) 
			return;
		updateGeneralOpt($gopt_id);
	}

	function updateNagiosConfigData($gopt_id = null)	{
		global $form, $pearDB, $oreon;
		
		$ret = array();
		$ret = $form->getSubmitValues();
		
		updateOption($pearDB, "nagios_path_img", isset($ret["nagios_path_img"]) && $ret["nagios_path_img"] != NULL ? htmlentities($ret["nagios_path_img"], ENT_QUOTES) : "NULL");
		updateOption($pearDB, "nagios_path_plugins", isset($ret["nagios_path_plugins"]) && $ret["nagios_path_plugins"] != NULL ? htmlentities($ret["nagios_path_plugins"], ENT_QUOTES) : "NULL");
		updateOption($pearDB, "monitoring_engine", isset($ret["monitoring_engine"]) && $ret["monitoring_engine"] != NULL ? $ret["monitoring_engine"] : "NULL");
		updateOption($pearDB, "mailer_path_bin", isset($ret["mailer_path_bin"]) && $ret["mailer_path_bin"] != NULL ? htmlentities($ret["mailer_path_bin"], ENT_QUOTES) : "NULL");
		
		$oreon->initOptGen($pearDB);
	}

	function updateSNMPConfigData($gopt_id = null)	{
		global $form, $pearDB, $oreon;
		
		$ret = array();
		$ret = $form->getSubmitValues();
		
		updateOption($pearDB, "snmp_community", isset($ret["snmp_community"]) && $ret["snmp_community"] != NULL ? $ret["snmp_community"] : "NULL");
		updateOption($pearDB, "snmp_version", isset($ret["snmp_version"]) && $ret["snmp_version"] != NULL ? $ret["snmp_version"] : "NULL");
		updateOption($pearDB, "snmp_trapd_path_conf", isset($ret["snmp_trapd_path_conf"]) && $ret["snmp_trapd_path_conf"] != NULL ? $ret["snmp_trapd_path_conf"] : "NULL");
		updateOption($pearDB, "snmptt_unknowntrap_log_file", isset($ret["snmptt_unknowntrap_log_file"]) && $ret["snmptt_unknowntrap_log_file"] != NULL ? $ret["snmptt_unknowntrap_log_file"] : "NULL");
		updateOption($pearDB, "snmpttconvertmib_path_bin", isset($ret["snmpttconvertmib_path_bin"]) && $ret["snmpttconvertmib_path_bin"] != NULL ? $ret["snmpttconvertmib_path_bin"] : "NULL");
		updateOption($pearDB, "perl_library_path", isset($ret["perl_library_path"]) && $ret["perl_library_path"] != NULL ? $ret["perl_library_path"] : "NULL");
		
		$oreon->initOptGen($pearDB);
	}

	function updateDebugConfigData($gopt_id = null)	{
		global $form, $pearDB, $oreon;

		$ret = array();
		$ret = $form->getSubmitValues();

		updateOption($pearDB, "debug_path", isset($ret["debug_path"]) && $ret["debug_path"] != NULL ? $ret["debug_path"]: "NULL");
		updateOption($pearDB, "debug_auth", isset($ret["debug_auth"]) && $ret["debug_auth"] != NULL ? $ret["debug_auth"]: "NULL");
		updateOption($pearDB, "debug_nagios_import", isset($ret["debug_nagios_import"]) && $ret["debug_nagios_import"] != NULL ? $ret["debug_nagios_import"]: "NULL");
		updateOption($pearDB, "debug_rrdtool", isset($ret["debug_rrdtool"]) && $ret["debug_rrdtool"] != NULL ? $ret["debug_rrdtool"]: "NULL");
		updateOption($pearDB, "debug_ldap_import", isset($ret["debug_ldap_import"]) && $ret["debug_ldap_import"] != NULL ? $ret["debug_ldap_import"]: "NULL");
		updateOption($pearDB, "debug_sql", isset($ret["debug_sql"]) && $ret["debug_sql"] != NULL ? $ret["debug_sql"]: "NULL");

		$oreon->initOptGen($pearDB);
	}

	function updateLdapConfigData($gopt_id = null)	{
		global $form, $pearDB, $oreon;
				
		$ret = array();
		$ret = $form->getSubmitValues();

		updateOption($pearDB, "ldap_host", isset($ret["ldap_host"]) && $ret["ldap_host"] != NULL ? htmlentities($ret["ldap_host"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "ldap_port", isset($ret["ldap_port"]) && $ret["ldap_port"] != NULL ? htmlentities($ret["ldap_port"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "ldap_base_dn", isset($ret["ldap_base_dn"]) && $ret["ldap_base_dn"] != NULL ? htmlentities($ret["ldap_base_dn"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "ldap_login_attrib", isset($ret["ldap_login_attrib"]) && $ret["ldap_login_attrib"] != NULL ? htmlentities($ret["ldap_login_attrib"], ENT_QUOTES): "");
		updateOption($pearDB, "ldap_ssl", isset($ret["ldap_ssl"]["ldap_ssl"]) && $ret["ldap_ssl"]["ldap_ssl"] != NULL ? htmlentities($ret["ldap_ssl"]["ldap_ssl"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "ldap_auth_enable", isset($ret["ldap_auth_enable"]["ldap_auth_enable"]) && $ret["ldap_auth_enable"]["ldap_auth_enable"] != NULL ? htmlentities($ret["ldap_auth_enable"]["ldap_auth_enable"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "ldap_search_user", isset($ret["ldap_search_user"]) && $ret["ldap_search_user"] != NULL ? htmlentities($ret["ldap_search_user"], ENT_QUOTES): "");
		updateOption($pearDB, "ldap_search_user_pwd", isset($ret["ldap_search_user_pwd"]) && $ret["ldap_search_user_pwd"] != NULL ? htmlentities($ret["ldap_search_user_pwd"], ENT_QUOTES): "");
		updateOption($pearDB, "ldap_search_filter", isset($ret["ldap_search_filter"]) && $ret["ldap_search_filter"] != NULL ? htmlentities($ret["ldap_search_filter"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "ldap_search_timeout", isset($ret["ldap_search_timeout"]) && $ret["ldap_search_timeout"] != NULL ? htmlentities($ret["ldap_search_timeout"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "ldap_search_limit", isset($ret["ldap_search_limit"]) && $ret["ldap_search_limit"] != NULL ? htmlentities($ret["ldap_search_limit"], ENT_QUOTES): "NULL");		
		updateOption($pearDB, "ldap_protocol_version", isset($ret["ldap_protocol_version"]) && $ret["ldap_protocol_version"] != NULL ? htmlentities($ret["ldap_protocol_version"], ENT_QUOTES): "NULL");
	
		$oreon->initOptGen($pearDB);
	}

	function updateColorsConfigData($gopt_id = null)	{
		global $form, $pearDB, $oreon;
			
		$ret = array();
		$ret = $form->getSubmitValues();
		
		updateOption($pearDB, "color_up", isset($ret["color_up"]) && $ret["color_up"] != NULL ? htmlentities($ret["color_up"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "color_down", isset($ret["color_down"]) && $ret["color_down"] != NULL ? htmlentities($ret["color_down"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "color_unreachable", isset($ret["color_unreachable"]) && $ret["color_unreachable"] != NULL ? htmlentities($ret["color_unreachable"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "color_ok", isset($ret["color_ok"]) && $ret["color_ok"] != NULL ? htmlentities($ret["color_ok"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "color_warning", isset($ret["color_warning"]) && $ret["color_warning"] != NULL ? htmlentities($ret["color_warning"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "color_critical", isset($ret["color_critical"]) && $ret["color_critical"] != NULL ? htmlentities($ret["color_critical"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "color_line_critical", isset($ret["color_line_critical"]) && $ret["color_line_critical"] != NULL ? htmlentities($ret["color_line_critical"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "color_pending", isset($ret["color_pending"]) && $ret["color_pending"] != NULL ? htmlentities($ret["color_pending"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "color_unknown", isset($ret["color_unknown"]) && $ret["color_unknown"] != NULL ? htmlentities($ret["color_unknown"], ENT_QUOTES): "NULL ");
		updateOption($pearDB, "color_ack", isset($ret["color_ack"]) && $ret["color_ack"] != NULL ? htmlentities($ret["color_ack"], ENT_QUOTES): "NULL ");
		updateOption($pearDB, "color_downtime", isset($ret["color_downtime"]) && $ret["color_downtime"] != NULL ? htmlentities($ret["color_downtime"], ENT_QUOTES): "NULL ");
		updateOption($pearDB, "color_host_down", isset($ret["color_host_down"]) && $ret["color_host_down"] != NULL ? htmlentities($ret["color_host_down"], ENT_QUOTES): "NULL ");
		updateOption($pearDB, "color_host_unreachable", isset($ret["color_host_unreachable"]) && $ret["color_host_unreachable"] != NULL ? htmlentities($ret["color_host_unreachable"], ENT_QUOTES): "NULL ");
		
		$oreon->initOptGen($pearDB);
	}

	function updateGeneralConfigData($gopt_id = null)	{
		global $form, $pearDB, $oreon;
		
		$ret = array();
		$ret = $form->getSubmitValues();
		
		if (!isset($ret["session_expire"]) || $ret["session_expire"] == 0)
			$ret["session_expire"] = 2;
		
		updateOption($pearDB, "oreon_path", isset($ret["oreon_path"]) && $ret["oreon_path"] != NULL ? htmlentities($ret["oreon_path"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "oreon_web_path", isset($ret["oreon_web_path"]) && $ret["oreon_web_path"] != NULL ? htmlentities($ret["oreon_web_path"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "oreon_refresh", isset($ret["oreon_refresh"]) && $ret["oreon_refresh"] != NULL ? htmlentities($ret["oreon_refresh"], ENT_QUOTES): "NULL");		
		updateOption($pearDB, "session_expire", isset($ret["session_expire"]) && $ret["session_expire"] != NULL ? htmlentities($ret["session_expire"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "maxViewMonitoring", isset($ret["maxViewMonitoring"]) && $ret["maxViewMonitoring"] != NULL ? htmlentities($ret["maxViewMonitoring"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "maxViewConfiguration", isset($ret["maxViewConfiguration"]) && $ret["maxViewConfiguration"] != NULL ? htmlentities($ret["maxViewConfiguration"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "AjaxTimeReloadMonitoring", isset($ret["AjaxTimeReloadMonitoring"]) && $ret["AjaxTimeReloadMonitoring"] != NULL ? htmlentities($ret["AjaxTimeReloadMonitoring"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "AjaxTimeReloadStatistic", isset($ret["AjaxTimeReloadStatistic"]) && $ret["AjaxTimeReloadStatistic"] != NULL ? htmlentities($ret["AjaxTimeReloadStatistic"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "AjaxFirstTimeReloadMonitoring", isset($ret["AjaxFirstTimeReloadMonitoring"]) && $ret["AjaxFirstTimeReloadMonitoring"] != NULL ? htmlentities($ret["AjaxFirstTimeReloadMonitoring"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "AjaxFirstTimeReloadStatistic", isset($ret["AjaxFirstTimeReloadStatistic"]) && $ret["AjaxFirstTimeReloadStatistic"] != NULL ? htmlentities($ret["AjaxFirstTimeReloadStatistic"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "template", isset($ret["template"]) && $ret["template"] != NULL ? htmlentities($ret["template"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "enable_gmt", isset($ret["enable_gmt"]["yes"]) && $ret["enable_gmt"]["yes"] != NULL ? htmlentities($ret["enable_gmt"]["yes"], ENT_QUOTES): "0");
		updateOption($pearDB, "gmt", isset($ret["gmt"]) && $ret["gmt"] != NULL ? htmlentities($ret["gmt"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "problem_sort_type", isset($ret["problem_sort_type"]) && $ret["problem_sort_type"] != NULL ? htmlentities($ret["problem_sort_type"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "problem_sort_order", isset($ret["problem_sort_order"]) && $ret["problem_sort_order"] != NULL ? htmlentities($ret["problem_sort_order"], ENT_QUOTES): "NULL");
		updateOption($pearDB, "enable_autologin", isset($ret["enable_autologin"]["yes"]) && $ret["enable_autologin"]["yes"] != NULL ? htmlentities($ret["enable_autologin"]["yes"], ENT_QUOTES): "0");
		updateOption($pearDB, "display_autologin_shortcut", isset($ret["display_autologin_shortcut"]["yes"]) && $ret["display_autologin_shortcut"]["yes"] != NULL ? htmlentities($ret["display_autologin_shortcut"]["yes"], ENT_QUOTES): "0");

		$oreon->initOptGen($pearDB);
	}

	function updateRRDToolConfigData($gopt_id = null)	{
		global $form, $pearDB, $oreon;
		
		$ret = array();
		$ret = $form->getSubmitValues();
		
		updateOption($pearDB, "rrdtool_path_bin", isset($ret["rrdtool_path_bin"]) && $ret["rrdtool_path_bin"] != NULL ? htmlentities($ret["rrdtool_path_bin"], ENT_QUOTES) : "NULL");
		updateOption($pearDB, "rrdtool_version", isset($ret["rrdtool_version"]) && $ret["rrdtool_version"] != NULL ? htmlentities($ret["rrdtool_version"], ENT_QUOTES) : "NULL");
		
		updateOption($pearDB, "rrdtool_title_font", isset($ret["rrdtool_title_font"]) && $ret["rrdtool_title_font"] != NULL ? htmlentities($ret["rrdtool_title_font"], ENT_QUOTES) : "NULL");
		updateOption($pearDB, "rrdtool_title_fontsize", isset($ret["rrdtool_title_fontsize"]) && $ret["rrdtool_title_fontsize"] != NULL ? htmlentities($ret["rrdtool_title_fontsize"], ENT_QUOTES) : "NULL");
		
		updateOption($pearDB, "rrdtool_unit_font", isset($ret["rrdtool_unit_font"]) && $ret["rrdtool_unit_font"] != NULL ? htmlentities($ret["rrdtool_unit_font"], ENT_QUOTES) : "NULL");
		updateOption($pearDB, "rrdtool_unit_fontsize", isset($ret["rrdtool_unit_fontsize"]) && $ret["rrdtool_unit_fontsize"] != NULL ? htmlentities($ret["rrdtool_unit_fontsize"], ENT_QUOTES) : "NULL");
		
		updateOption($pearDB, "rrdtool_axis_font", isset($ret["rrdtool_axis_font"]) && $ret["rrdtool_axis_font"] != NULL ? htmlentities($ret["rrdtool_axis_font"], ENT_QUOTES) : "NULL");
		updateOption($pearDB, "rrdtool_axis_fontsize", isset($ret["rrdtool_axis_fontsize"]) && $ret["rrdtool_axis_fontsize"] != NULL ? htmlentities($ret["rrdtool_axis_fontsize"], ENT_QUOTES) : "NULL");
		
		updateOption($pearDB, "rrdtool_watermark_font", isset($ret["rrdtool_watermark_font"]) && $ret["rrdtool_watermark_font"] != NULL ? htmlentities($ret["rrdtool_watermark_font"], ENT_QUOTES) : "NULL");
		updateOption($pearDB, "rrdtool_watermark_fontsize", isset($ret["rrdtool_watermark_fontsize"]) && $ret["rrdtool_watermark_fontsize"] != NULL ? htmlentities($ret["rrdtool_watermark_fontsize"], ENT_QUOTES) : "NULL");
		
		updateOption($pearDB, "rrdtool_legend_font", isset($ret["rrdtool_legend_font"]) && $ret["rrdtool_legend_font"] != NULL ? htmlentities($ret["rrdtool_legend_font"], ENT_QUOTES) : "NULL");
		updateOption($pearDB, "rrdtool_legend_fontsize", isset($ret["rrdtool_legend_fontsize"]) && $ret["rrdtool_legend_fontsize"] != NULL ? htmlentities($ret["rrdtool_legend_fontsize"], ENT_QUOTES) : "NULL");
		
		$oreon->initOptGen($pearDB);
	}
	
	function updateODSConfigData()	{
		global $form, $pearDBO;
		
		$ret = array();
		$ret = $form->getSubmitValues();
		if (!isset($ret["len_storage_rrd"]))
			$ret["len_storage_rrd"] = 1;
		if (!isset($ret["len_storage_mysql"]))
			$ret["len_storage_mysql"] = 1;
		if (!isset($ret["autodelete_rrd_db"]))
			$ret["autodelete_rrd_db"] = 0;
		if ($ret["sleep_time"] <= 10)
			$ret["sleep_time"] = 10;
		if ($ret["purge_interval"] <= 20)
			$ret["purge_interval"] = 20;
		if (!isset($ret["auto_drop"]))
			$ret["auto_drop"] = "0";
		if (!isset($ret["archive_log"]))
			$ret["archive_log"] = "0";
		if (!$ret["purge_interval"])
			$ret["purge_interval"] = 60;
		if ($ret["RRDdatabase_path"][strlen($ret["RRDdatabase_path"]) - 1] != "/")
			$ret["RRDdatabase_path"] .= "/";
		$rq = "UPDATE `config` SET `RRDdatabase_path` = '".$ret["RRDdatabase_path"]."',  `RRDdatabase_status_path` = '".$ret["RRDdatabase_status_path"]."',
				`RRDdatabase_nagios_stats_path` = '".$ret["RRDdatabase_nagios_stats_path"]."',
				`len_storage_rrd` = '".$ret["len_storage_rrd"]."',
				`len_storage_mysql` = '".$ret["len_storage_mysql"]."',
				`autodelete_rrd_db` = '".$ret["autodelete_rrd_db"]."',
				`sleep_time` = '".$ret["sleep_time"]."',
				`purge_interval` = '".$ret["purge_interval"]."',
				`auto_drop` = '".$ret["auto_drop"]."',
				`drop_file` = '".$ret["drop_file"]."',
				`perfdata_file` = '".$ret["perfdata_file"]."',
				`archive_log` = '".$ret["archive_log"]."',
				`nagios_log_file` = '".$ret["nagios_log_file"]."',
				`archive_retention` = '".$ret["archive_retention"]."',
				`storage_type` = '".$ret["storage_type"]."' WHERE `id` = 1 LIMIT 1 ;";
		$DBRESULT =& $pearDBO->query($rq);
	}	
	
	function updateCASConfigData($gopt_id = null)	{
		global $form, $pearDB, $oreon;
		
		$ret = array();
		$ret = $form->getSubmitValues();
		
		updateOption($pearDB, "auth_cas_enable", isset($ret["auth_cas_enable"]["auth_cas_enable"]) && $ret["auth_cas_enable"]["auth_cas_enable"] != NULL ? $ret["auth_cas_enable"]["auth_cas_enable"] : "NULL");
		updateOption($pearDB, "cas_server", isset($ret["cas_server"]) && $ret["cas_server"] != NULL ? $ret["cas_server"] : "NULL");
		updateOption($pearDB, "cas_port", isset($ret["cas_port"]) && $ret["cas_port"] != NULL ? $ret["cas_port"] : "NULL");
		updateOption($pearDB, "cas_url", isset($ret["cas_url"]) && $ret["cas_url"] != NULL ? $ret["cas_url"] : "NULL");
		updateOption($pearDB, "cas_version", isset($ret["cas_version"]) && $ret["cas_version"] != NULL ? $ret["cas_version"] : "NULL");
	
		$oreon->initOptGen($pearDB);
	}
	
	function updateReportingTimePeriodInDB() {
		global $form, $pearDB;
		
		$ret = $form->getSubmitValues();
		(isset($ret["dayList"]["report_Monday"])) ? $ret["dayList"]["report_Monday"] = 1  : $ret["dayList"]["report_Monday"] = 0;
		(isset($ret["dayList"]["report_Tuesday"])) ? $ret["dayList"]["report_Tuesday"] = 1  : $ret["dayList"]["report_Tuesday"] = 0;
		(isset($ret["dayList"]["report_Wednesday"])) ? $ret["dayList"]["report_Wednesday"] = 1  : $ret["dayList"]["report_Wednesday"] = 0;
		(isset($ret["dayList"]["report_Thursday"])) ? $ret["dayList"]["report_Thursday"] = 1  : $ret["dayList"]["report_Thursday"] = 0;
		(isset($ret["dayList"]["report_Friday"])) ? $ret["dayList"]["report_Friday"] = 1  : $ret["dayList"]["report_Friday"] = 0;
		(isset($ret["dayList"]["report_Saturday"])) ? $ret["dayList"]["report_Saturday"] = 1  : $ret["dayList"]["report_Saturday"] = 0;
		(isset($ret["dayList"]["report_Sunday"])) ? $ret["dayList"]["report_Sunday"] = 1  : $ret["dayList"]["report_Sunday"] = 0;
		
		foreach ($ret["dayList"] as $key => $value){ 	
			$query = "UPDATE `contact_param` SET `cp_value` = '".$ret["dayList"][$key]."' WHERE `cp_contact_id` IS NULL AND `cp_key` = '$key'";
			$DBRESULT =& $pearDB->query($query);
		}
		
		$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_hour_start"]."' WHERE cp_contact_id IS NULL AND cp_key = 'report_hour_start'";
		$DBRESULT =& $pearDB->query($query);
		
		$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_minute_start"]."' WHERE cp_contact_id IS NULL AND cp_key = 'report_minute_start'";
		$DBRESULT =& $pearDB->query($query);
		
		$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_hour_end"]."' WHERE cp_contact_id IS NULL AND cp_key = 'report_hour_end'";
		$DBRESULT =& $pearDB->query($query);
		
		$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_minute_end"]."' WHERE cp_contact_id IS NULL AND cp_key = 'report_minute_end'";
		$DBRESULT =& $pearDB->query($query);
	}
	
?>
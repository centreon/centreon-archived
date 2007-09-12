<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit();

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
		if (!$gopt_id) return;
		updateGeneralOpt($gopt_id);
	}

	function updateNagiosConfigData($gopt_id = null)	{
		if (!$gopt_id) return;
		global $form, $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `general_opt` SET ";
		$rq .= "nagios_path = ";
		isset($ret["nagios_path"]) && $ret["nagios_path"] != NULL ? $rq .= "'".htmlentities($ret["nagios_path"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "nagios_path_bin = ";
		isset($ret["nagios_path_bin"]) && $ret["nagios_path_bin"] != NULL ? $rq .= "'".htmlentities($ret["nagios_path_bin"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "nagios_init_script = ";
		isset($ret["nagios_init_script"]) && $ret["nagios_init_script"] != NULL ? $rq .= "'".htmlentities($ret["nagios_init_script"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "nagios_path_img = ";
		isset($ret["nagios_path_img"]) && $ret["nagios_path_img"] != NULL ? $rq .= "'".htmlentities($ret["nagios_path_img"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "nagios_path_plugins = ";
		isset($ret["nagios_path_plugins"]) && $ret["nagios_path_plugins"] != NULL ? $rq .= "'".htmlentities($ret["nagios_path_plugins"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "nagios_version = ";
		isset($ret["nagios_version"]) && $ret["nagios_version"] != NULL ? $rq .= "'".$ret["nagios_version"]."', ": $rq .= "NULL, ";
		$rq .= "mailer_path_bin = ";
		isset($ret["mailer_path_bin"]) && $ret["mailer_path_bin"] != NULL ? $rq .= "'".htmlentities($ret["mailer_path_bin"], ENT_QUOTES)."', ": $rq .= "NULL ";
		$rq .= "ndo_base_name = ";
		isset($ret["ndo_base_name"]) && $ret["ndo_base_name"] != NULL ? $rq .= "'".htmlentities($ret["ndo_base_name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ndo_activate = ";
		isset($ret["ndo_activate"]["ndo_activate"]) && $ret["ndo_activate"]["ndo_activate"] != NULL ? $rq .= "'".htmlentities($ret["ndo_activate"]["ndo_activate"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE gopt_id = '".$gopt_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$oreon->optGen = array();
		$DBRESULT =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$oreon->optGen = $DBRESULT->fetchRow();
		$oreon->user->version = $ret["nagios_version"];
	}

	function updateSNMPConfigData($gopt_id = null)	{
		if (!$gopt_id) return;
		global $form, $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `general_opt` SET ";
		$rq .= "snmp_community = ";
		isset($ret["snmp_community"]) && $ret["snmp_community"] != NULL ? $rq .= "'".$ret["snmp_community"]."', ": $rq .= "NULL, ";
		$rq .= "snmp_version = ";
		isset($ret["snmp_version"]) && $ret["snmp_version"] != NULL ? $rq .= "'".$ret["snmp_version"]."', ": $rq .= "NULL, ";
		$rq .= "snmp_trapd_path_conf = ";
		isset($ret["snmp_trapd_path_conf"]) && $ret["snmp_trapd_path_conf"] != NULL ? $rq .= "'".$ret["snmp_trapd_path_conf"]."' ": $rq .= "NULL ";
		$rq .= ", snmpttconvertmib_path_bin = ";
		isset($ret["snmpttconvertmib_path_bin"]) && $ret["snmpttconvertmib_path_bin"] != NULL ? $rq .= "'".$ret["snmpttconvertmib_path_bin"]."' ": $rq .= "NULL ";
		$rq .= ", perl_library_path = ";
		isset($ret["perl_library_path"]) && $ret["perl_library_path"] != NULL ? $rq .= "'".$ret["perl_library_path"]."' ": $rq .= "NULL ";
		$rq .= "WHERE gopt_id = '".$gopt_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}

	function updateDebugConfigData($gopt_id = null)	{
		if (!$gopt_id) return;
		global $form, $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `general_opt` SET ";
		$rq .= "debug_path = ";
		isset($ret["debug_path"]) && $ret["debug_path"] != NULL ? $rq .= "'".$ret["debug_path"]."', ": $rq .= "NULL, ";
		$rq .= "debug_auth = ";
		isset($ret["debug_auth"]) && $ret["debug_auth"] != NULL ? $rq .= "'".$ret["debug_auth"]."', ": $rq .= "NULL, ";
		$rq .= "debug_nagios_import = ";
		isset($ret["debug_nagios_import"]) && $ret["debug_nagios_import"] != NULL ? $rq .= "'".$ret["debug_nagios_import"]."', ": $rq .= "NULL, ";
		$rq .= "debug_rrdtool = ";
		isset($ret["debug_rrdtool"]) && $ret["debug_rrdtool"] != NULL ? $rq .= "'".$ret["debug_rrdtool"]."', ": $rq .= "NULL, ";
		$rq .= "debug_ldap_import = ";
		isset($ret["debug_ldap_import"]) && $ret["debug_ldap_import"] != NULL ? $rq .= "'".$ret["debug_ldap_import"]."', ": $rq .= "NULL, ";
		$rq .= "debug_inventory = ";
		isset($ret["debug_inventory"]) && $ret["debug_inventory"] != NULL ? $rq .= "'".$ret["debug_inventory"]."' ": $rq .= "NULL ";
		$rq .= "WHERE gopt_id = '".$gopt_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$oreon->optGen = array();
	}

	function updateLdapConfigData($gopt_id = null)	{
		if (!$gopt_id) return;
		global $form, $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `general_opt` SET ";
 		$rq .= "ldap_host = ";
		isset($ret["ldap_host"]) && $ret["ldap_host"] != NULL ? $rq .= "'".htmlentities($ret["ldap_host"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ldap_port = ";
		isset($ret["ldap_port"]) && $ret["ldap_port"] != NULL ? $rq .= "'".htmlentities($ret["ldap_port"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ldap_base_dn = ";
		isset($ret["ldap_base_dn"]) && $ret["ldap_base_dn"] != NULL ? $rq .= "'".htmlentities($ret["ldap_base_dn"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ldap_login_attrib = ";
		isset($ret["ldap_login_attrib"]) && $ret["ldap_login_attrib"] != NULL ? $rq .= "'".htmlentities($ret["ldap_login_attrib"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ldap_ssl = ";
		isset($ret["ldap_ssl"]["ldap_ssl"]) && $ret["ldap_ssl"]["ldap_ssl"] != NULL ? $rq .= "'".htmlentities($ret["ldap_ssl"]["ldap_ssl"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ldap_auth_enable = ";
		isset($ret["ldap_auth_enable"]["ldap_auth_enable"]) && $ret["ldap_auth_enable"]["ldap_auth_enable"] != NULL ? $rq .= "'".htmlentities($ret["ldap_auth_enable"]["ldap_auth_enable"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ldap_search_user = ";
		isset($ret["ldap_search_user"]) && $ret["ldap_search_user"] != NULL ? $rq .= "'".htmlentities($ret["ldap_search_user"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ldap_search_user_pwd = ";
		isset($ret["ldap_search_user_pwd"]) && $ret["ldap_search_user_pwd"] != NULL ? $rq .= "'".htmlentities($ret["ldap_search_user_pwd"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ldap_search_filter = ";
		isset($ret["ldap_search_filter"]) && $ret["ldap_search_filter"] != NULL ? $rq .= "'".htmlentities($ret["ldap_search_filter"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ldap_search_timeout = ";
		isset($ret["ldap_search_timeout"]) && $ret["ldap_search_timeout"] != NULL ? $rq .= "'".htmlentities($ret["ldap_search_timeout"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ldap_search_limit = ";
		isset($ret["ldap_search_limit"]) && $ret["ldap_search_limit"] != NULL ? $rq .= "'".htmlentities($ret["ldap_search_limit"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE gopt_id = '".$gopt_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}

	function updateColorsConfigData($gopt_id = null)	{
		if (!$gopt_id) return;
		global $form, $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `general_opt` SET ";
		$rq .= "color_up = ";
		isset($ret["color_up"]) && $ret["color_up"] != NULL ? $rq .= "'".htmlentities($ret["color_up"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "color_down = ";
		isset($ret["color_down"]) && $ret["color_down"] != NULL ? $rq .= "'".htmlentities($ret["color_down"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "color_unreachable = ";
		isset($ret["color_unreachable"]) && $ret["color_unreachable"] != NULL ? $rq .= "'".htmlentities($ret["color_unreachable"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "color_ok = ";
		isset($ret["color_ok"]) && $ret["color_ok"] != NULL ? $rq .= "'".htmlentities($ret["color_ok"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "color_warning = ";
		isset($ret["color_warning"]) && $ret["color_warning"] != NULL ? $rq .= "'".htmlentities($ret["color_warning"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "color_critical = ";
		isset($ret["color_critical"]) && $ret["color_critical"] != NULL ? $rq .= "'".htmlentities($ret["color_critical"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "color_pending = ";
		isset($ret["color_pending"]) && $ret["color_pending"] != NULL ? $rq .= "'".htmlentities($ret["color_pending"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "color_unknown = ";
		isset($ret["color_unknown"]) && $ret["color_unknown"] != NULL ? $rq .= "'".htmlentities($ret["color_unknown"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE gopt_id = '".$gopt_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}

	function updateGeneralConfigData($gopt_id = null)	{
		if (!$gopt_id) return;
		global $form, $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `general_opt` SET ";
		$rq .= "oreon_path = ";
		isset($ret["oreon_path"]) && $ret["oreon_path"] != NULL ? $rq .= "'".htmlentities($ret["oreon_path"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "oreon_web_path = ";
		isset($ret["oreon_web_path"]) && $ret["oreon_web_path"] != NULL ? $rq .= "'".htmlentities($ret["oreon_web_path"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "oreon_refresh = ";
		isset($ret["oreon_refresh"]) && $ret["oreon_refresh"] != NULL ? $rq .= "'".htmlentities($ret["oreon_refresh"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "session_expire = ";
		isset($ret["session_expire"]) && $ret["session_expire"] != NULL ? $rq .= "'".htmlentities($ret["session_expire"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "maxViewMonitoring = ";
		isset($ret["maxViewMonitoring"]) && $ret["maxViewMonitoring"] != NULL ? $rq .= "'".htmlentities($ret["maxViewMonitoring"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "maxViewConfiguration = ";
		isset($ret["maxViewConfiguration"]) && $ret["maxViewConfiguration"] != NULL ? $rq .= "'".htmlentities($ret["maxViewConfiguration"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "AjaxTimeReloadMonitoring = ";
		isset($ret["AjaxTimeReloadMonitoring"]) && $ret["AjaxTimeReloadMonitoring"] != NULL ? $rq .= "'".htmlentities($ret["AjaxTimeReloadMonitoring"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "AjaxTimeReloadStatistic = ";
		isset($ret["AjaxTimeReloadStatistic"]) && $ret["AjaxTimeReloadStatistic"] != NULL ? $rq .= "'".htmlentities($ret["AjaxTimeReloadStatistic"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "AjaxFirstTimeReloadMonitoring = ";
		isset($ret["AjaxFirstTimeReloadMonitoring"]) && $ret["AjaxFirstTimeReloadMonitoring"] != NULL ? $rq .= "'".htmlentities($ret["AjaxFirstTimeReloadMonitoring"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "AjaxFirstTimeReloadStatistic = ";
		isset($ret["AjaxFirstTimeReloadStatistic"]) && $ret["AjaxFirstTimeReloadStatistic"] != NULL ? $rq .= "'".htmlentities($ret["AjaxFirstTimeReloadStatistic"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "template = ";
		isset($ret["template"]) && $ret["template"] != NULL ? $rq .= "'".htmlentities($ret["template"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "gmt = ";
		isset($ret["gmt"]) && $ret["gmt"] != NULL ? $rq .= "'".htmlentities($ret["gmt"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "problem_sort_type = ";
		isset($ret["problem_sort_type"]) && $ret["problem_sort_type"] != NULL ? $rq .= "'".htmlentities($ret["problem_sort_type"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "problem_sort_order = ";
		isset($ret["problem_sort_order"]) && $ret["problem_sort_order"] != NULL ? $rq .= "'".htmlentities($ret["problem_sort_order"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE gopt_id = '".$gopt_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}

	function updateRRDToolConfigData($gopt_id = null)	{
		if (!$gopt_id) return;
		global $form, $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `general_opt` SET ";
		$rq .= "rrdtool_path_bin = ";
		isset($ret["rrdtool_path_bin"]) && $ret["rrdtool_path_bin"] != NULL ? $rq .= "'".htmlentities($ret["rrdtool_path_bin"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "rrdtool_version = ";
		isset($ret["rrdtool_version"]) && $ret["rrdtool_version"] != NULL ? $rq .= "'".htmlentities($ret["rrdtool_version"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE gopt_id = '".$gopt_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
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
		if (!isset($ret["fast_parsing"]))
			$ret["fast_parsing"] = "0";
		if (!$ret["purge_interval"])
			$ret["purge_interval"] = 60;
		if ($ret["RRDdatabase_path"][strlen($ret["RRDdatabase_path"]) - 1] != "/")
			$ret["RRDdatabase_path"] .= "/";
		$rq = "UPDATE `config` SET `RRDdatabase_path` = '".$ret["RRDdatabase_path"]."',
				`len_storage_rrd` = '".$ret["len_storage_rrd"]."',
				`len_storage_mysql` = '".$ret["len_storage_mysql"]."',
				`autodelete_rrd_db` = '".$ret["autodelete_rrd_db"]."',
				`sleep_time` = '".$ret["sleep_time"]."',
				`purge_interval` = '".$ret["purge_interval"]."',
				`auto_drop` = '".$ret["auto_drop"]."',
				`drop_file` = '".$ret["drop_file"]."',
				`perfdata_file` = '".$ret["perfdata_file"]."',
				`archive_log` = '".$ret["archive_log"]."',
				`fast_parsing` = '".$ret["fast_parsing"]."',
				`nagios_log_file` = '".$ret["nagios_log_file"]."',
				`archive_retention` = '".$ret["archive_retention"]."',
				`storage_type` = '".$ret["storage_type"]."' WHERE `id` = 1 LIMIT 1 ;";
		$DBRESULT =& $pearDBO->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}	
	
?>
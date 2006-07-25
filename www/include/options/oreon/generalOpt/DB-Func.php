<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	function updateGeneralOptInDB ($gopt_id = NULL)	{
		if (!$gopt_id) return;
		updateGeneralOpt($gopt_id);
	}

	function updateGeneralOpt($gopt_id = null)	{
		if (!$gopt_id) return;
		global $form;
		global $pearDB;
		global $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `general_opt` SET ";
		$rq .= "nagios_path = ";
		isset($ret["nagios_path"]) && $ret["nagios_path"] != NULL ? $rq .= "'".htmlentities($ret["nagios_path"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "nagios_path_bin = ";
		isset($ret["nagios_path_bin"]) && $ret["nagios_path_bin"] != NULL ? $rq .= "'".htmlentities($ret["nagios_path_bin"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "nagios_path_img = ";
		isset($ret["nagios_path_img"]) && $ret["nagios_path_img"] != NULL ? $rq .= "'".htmlentities($ret["nagios_path_img"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "nagios_path_plugins = ";
		isset($ret["nagios_path_plugins"]) && $ret["nagios_path_plugins"] != NULL ? $rq .= "'".htmlentities($ret["nagios_path_plugins"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "nagios_version = ";
		isset($ret["nagios_version"]) && $ret["nagios_version"] != NULL ? $rq .= "'".$ret["nagios_version"]."', ": $rq .= "NULL, ";
		$rq .= "snmp_community = ";
		isset($ret["snmp_community"]) && $ret["snmp_community"] != NULL ? $rq .= "'".$ret["snmp_community"]."', ": $rq .= "NULL, ";
		$rq .= "snmp_version = ";
		isset($ret["snmp_version"]) && $ret["snmp_version"] != NULL ? $rq .= "'".$ret["snmp_version"]."', ": $rq .= "NULL, ";
		$rq .= "snmp_trapd_path_daemon = ";
		isset($ret["snmp_trapd_path_daemon"]) && $ret["snmp_trapd_path_daemon"] != NULL ? $rq .= "'".$ret["snmp_trapd_path_daemon"]."', ": $rq .= "NULL, ";
		$rq .= "snmp_trapd_path_conf = ";
		isset($ret["snmp_trapd_path_conf"]) && $ret["snmp_trapd_path_conf"] != NULL ? $rq .= "'".$ret["snmp_trapd_path_conf"]."', ": $rq .= "NULL, ";
		$rq .= "mailer_path_bin = ";
		isset($ret["mailer_path_bin"]) && $ret["mailer_path_bin"] != NULL ? $rq .= "'".htmlentities($ret["mailer_path_bin"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "rrdtool_path_bin = ";
		isset($ret["rrdtool_path_bin"]) && $ret["rrdtool_path_bin"] != NULL ? $rq .= "'".htmlentities($ret["rrdtool_path_bin"], ENT_QUOTES)."', ": $rq .= "NULL, ";		
		$rq .= "rrdtool_version = ";
		isset($ret["rrdtool_version"]) && $ret["rrdtool_version"] != NULL ? $rq .= "'".htmlentities($ret["rrdtool_version"], ENT_QUOTES)."', ": $rq .= "NULL, ";		
		$rq .= "oreon_path = ";
		isset($ret["oreon_path"]) && $ret["oreon_path"] != NULL ? $rq .= "'".htmlentities($ret["oreon_path"], ENT_QUOTES)."', ": $rq .= "NULL, ";		
		$rq .= "oreon_web_path = ";
		isset($ret["oreon_web_path"]) && $ret["oreon_web_path"] != NULL ? $rq .= "'".htmlentities($ret["oreon_web_path"], ENT_QUOTES)."', ": $rq .= "NULL, ";		
		$rq .= "oreon_rrdbase_path = ";
		isset($ret["oreon_rrdbase_path"]) && $ret["oreon_rrdbase_path"] != NULL ? $rq .= "'".htmlentities($ret["oreon_rrdbase_path"], ENT_QUOTES)."', ": $rq .= "NULL, ";		
		$rq .= "oreon_refresh = ";
		isset($ret["oreon_refresh"]) && $ret["oreon_refresh"] != NULL ? $rq .= "'".htmlentities($ret["oreon_refresh"], ENT_QUOTES)."', ": $rq .= "NULL, ";		
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
		isset($ret["color_unknown"]) && $ret["color_unknown"] != NULL ? $rq .= "'".htmlentities($ret["color_unknown"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "session_expire = ";
		isset($ret["session_expire"]) && $ret["session_expire"] != NULL ? $rq .= "'".htmlentities($ret["session_expire"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "perfparse_installed = ";
		isset($ret["perfparse_installed"]["perfparse_installed"]) && $ret["perfparse_installed"]["perfparse_installed"] != NULL ? $rq .= "'".$ret["perfparse_installed"]["perfparse_installed"]."', ": $rq .= "NULL, ";
		$rq .= "maxViewMonitoring = ";
		isset($ret["maxViewMonitoring"]) && $ret["maxViewMonitoring"] != NULL ? $rq .= "'".htmlentities($ret["maxViewMonitoring"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "maxViewConfiguration = ";
		isset($ret["maxViewConfiguration"]) && $ret["maxViewConfiguration"] != NULL ? $rq .= "'".htmlentities($ret["maxViewConfiguration"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "template = ";
		isset($ret["template"]) && $ret["template"] != NULL ? $rq .= "'".htmlentities($ret["template"], ENT_QUOTES)."', ": $rq .= "NULL, ";
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
		isset($ret["ldap_auth_enable"]["ldap_auth_enable"]) && $ret["ldap_auth_enable"]["ldap_auth_enable"] != NULL ? $rq .= "'".htmlentities($ret["ldap_auth_enable"]["ldap_auth_enable"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE gopt_id = '".$gopt_id."'";
		$pearDB->query($rq);
		$oreon->optGen = array();
		$res =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$oreon->optGen = $res->fetchRow();
		$oreon->user->version = $ret["nagios_version"];
	}
?>
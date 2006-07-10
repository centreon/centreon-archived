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
		$rq .= "nagios_path = '".htmlentities($ret["nagios_path"], ENT_QUOTES)."', " .
				"nagios_path_bin = '".htmlentities($ret["nagios_path_bin"], ENT_QUOTES)."', " .
				"nagios_path_img = '".htmlentities($ret["nagios_path_img"], ENT_QUOTES)."', " .
				"nagios_path_plugins = '".htmlentities($ret["nagios_path_plugins"], ENT_QUOTES)."', " .
				"nagios_version = '".$ret["nagios_version"]."', " .
				"snmp_community = '".$ret["snmp_community"]."', " .
				"snmp_version = '".$ret["snmp_version"]."', " .
				"snmp_trapd_path_daemon = '".$ret["snmp_trapd_path_daemon"]."', " .
				"snmp_trapd_path_conf = '".$ret["snmp_trapd_path_conf"]."', " .
				"mailer_path_bin = '".htmlentities($ret["mailer_path_bin"], ENT_QUOTES)."', " .
				"rrdtool_path_bin = '".htmlentities($ret["rrdtool_path_bin"], ENT_QUOTES)."', " .
				"rrdtool_version = '".htmlentities($ret["rrdtool_version"], ENT_QUOTES)."', " .
				"oreon_path = '".htmlentities($ret["oreon_path"], ENT_QUOTES)."', " .
				"oreon_web_path = '".htmlentities($ret["oreon_web_path"], ENT_QUOTES)."', " .
				"oreon_rrdbase_path = '".htmlentities($ret["oreon_rrdbase_path"], ENT_QUOTES)."', " .
				"oreon_refresh = '".htmlentities($ret["oreon_refresh"], ENT_QUOTES)."', " .
				"color_up = '".htmlentities($ret["color_up"], ENT_QUOTES)."', " .
				"color_down = '".htmlentities($ret["color_down"], ENT_QUOTES)."', " .
				"color_unreachable = '".htmlentities($ret["color_unreachable"], ENT_QUOTES)."', " .
				"color_ok = '".htmlentities($ret["color_ok"], ENT_QUOTES)."', " .
				"color_warning = '".htmlentities($ret["color_warning"], ENT_QUOTES)."', " .
				"color_critical = '".htmlentities($ret["color_critical"], ENT_QUOTES)."', " .
				"color_pending = '".htmlentities($ret["color_pending"], ENT_QUOTES)."', " .
				"color_unknown = '".htmlentities($ret["color_unknown"], ENT_QUOTES)."', " .
				"session_expire = '".htmlentities($ret["session_expire"], ENT_QUOTES)."', " .
				"perfparse_installed = '".htmlentities($ret["perfparse_installed"]["perfparse_installed"], ENT_QUOTES)."', " .
				"maxViewMonitoring = '".htmlentities($ret["maxViewMonitoring"], ENT_QUOTES)."', " .
				"maxViewConfiguration = '".htmlentities($ret["maxViewConfiguration"], ENT_QUOTES)."', " .
				"template = '".htmlentities($ret["template"], ENT_QUOTES)."', " .
				"ldap_host = '".htmlentities($ret["ldap_host"], ENT_QUOTES)."', " .
				"ldap_port = '".htmlentities($ret["ldap_port"], ENT_QUOTES)."', " .
				"ldap_base_dn = '".htmlentities($ret["ldap_base_dn"], ENT_QUOTES)."', " .
				"ldap_login_attrib = '".htmlentities($ret["ldap_login_attrib"], ENT_QUOTES)."', " .
				"ldap_ssl = '".htmlentities($ret["ldap_ssl"]["ldap_ssl"], ENT_QUOTES)."', " .
				"ldap_auth_enable = '".htmlentities($ret["ldap_auth_enable"]["ldap_auth_enable"], ENT_QUOTES)."' " .
				"WHERE gopt_id = '".$gopt_id."'";
		$pearDB->query($rq);
		$oreon->optGen = array();
		$res =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen = $res->fetchRow();
		$oreon->user->version = $ret["nagios_version"];
	}
?>
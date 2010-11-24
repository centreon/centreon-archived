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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/class/centreonXMLBGRequest.class.php $
 * SVN : $Id: centreon.class.php 9656 2010-01-04 09:05:23Z jmathis $
 *
 */

/*
 * Need Centreon Configuration file
 */
require_once "@CENTREON_ETC@/centreon.conf.php";
//require_once "/etc/centreon/centreon.conf.php";
require_once $centreon_path . '/www/autoloader.php';


/*
 * Class for XML/Ajax request
 *
 */
class CentreonXMLBGRequest
{

	/*
	 * Objects
	 */
	var $DB;
	var $DBC;
	var $DBNdo;

	var $XML;
	var $GMT;

	var $hostObj;
	var $serviceObj;
	var $monObj;

	var $access;
	var $session_id;

	/*
	 * Variables
	 */
	var $ndoPrefix;
	var $buffer;
	var $debug;
	var $compress;
	var $header;
	var $is_admin;
	var $user_id;
	var $grouplist;
	var $grouplistStr;
	var $general_opt;
	var $class;
	var $stateType;
	var $statusHost;
	var $statusService;
	var $colorHost;
	var $colorHostInService;
	var $colorService;

	/*
	 * Filters
	 */
	var $defaultPoller;
	var $defaultHostgroups;

	/*
	 * Class constructor
	 *
	 * <code>
	 * $obj = new CentreonBGRequest($_GET["session_id"], 1, 1, 0, 1);
	 * </code>
	 *
	 * $session_id 	char 	session id
	 * $dbneeds		bool 	flag for enable ndo connexion
	 * $headType	bool 	send XML header
	 * $debug		bool 	debug flag.
	 * $compress	bool 	compress enable.
	 */
	public function __construct($session_id, $dbNeeds, $headerType, $debug, $compress = null)
	{
		if (!isset($debug)) {
			$this->debug = 0;
		}

		(!isset($headerType)) ? $this->header = 1 : $this->header = $headerType;
		(!isset($compress)) ? $this->compress = 1 : $this->compress = $compress;

		if (!isset($session_id)) {
			print "Your might check your session id";
			exit(1);
		} else {
			$this->session_id = htmlentities($session_id, ENT_QUOTES, "UTF-8");
		}

		/*
		 * Enable Database Connexions
		 */
		$this->DB 		= new CentreonDB();
		$this->DBC 		= new CentreonDB("centstorage");
		if ($dbNeeds) {
			$this->DBNdo= new CentreonDB("ndo");
		}

		/*
		 * Init Objects
		 */
		$this->hostObj		= new CentreonHost($this->DB);
		$this->serviceObj	= new CentreonService($this->DB);

		/*
		 * Init Object Monitoring
		 */
		$this->monObj 		= new CentreonMonitoring();

		/*
		 * Timezone management
		 */
		$this->GMT = new CentreonGMT($this->DB);
		$this->GMT->getMyGMTFromSession($this->session_id, $this->DB);

		/*
		 * XML class
		 */
		$this->XML = new CentreonXML();

		/*
		 * Get Centreon Status DB prefix
		 */
		$this->ndoPrefix = $this->getNDOPrefix();

		/*
		 * ACL init
		 */
		$this->getUserIdFromSID();
		$this->isUserAdmin();
		$this->access = new CentreonACL($this->user_id, $this->is_admin);
		$this->grouplist = $this->access->getAccessGroups();
		$this->grouplistStr = $this->access->getAccessGroupsString();

		/*
		 * Init Color table
		 */
		$this->getStatusColor();

		/*
		 * Init class
		 */
		$this->classLine = "list_one";

		/*
		 * Init Tables
		 */
		$this->stateType	= array("1" => "H", "0" => "S");
		$this->statusHost 	= array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
		$this->statusService= array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
		$this->colorHost 	= array(0 => $this->general_opt["color_up"], 1 => $this->general_opt["color_down"], 2 => $this->general_opt["color_unreachable"]);
		$this->colorService	= array(0 => $this->general_opt["color_ok"], 1 => $this->general_opt["color_warning"], 2 => $this->general_opt["color_critical"], 3 => $this->general_opt["color_unknown"], 4 => $this->general_opt["color_pending"]);
		$this->colorHostInService = array(0 => "normal", 1 => "#FD8B46", 2 => "normal");
	}


	/*
	 * Update session table for this user.
	 * 	=> value used for logout session
	 */
	public function reloadSession()
	{
		$DBRESULT2 =& $this->DB->query("UPDATE `session` SET `last_reload` = '".time()."', `ip_address` = '".$_SERVER["REMOTE_ADDR"]."' WHERE CONVERT(`session_id` USING utf8) = '".$this->session_id."' LIMIT 1");
	}

	/*
	 * Check if user is admin
	 */
	private function isUserAdmin()
	{
		$DBRESULT =& $this->DB->query("SELECT contact_admin, contact_id FROM contact WHERE contact.contact_id = '".$this->user_id."' LIMIT 1");
		$admin =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		if ($admin["contact_admin"])
			$this->is_admin = 1;
		else
			$this->is_admin = 0;
	}

	/*
	 * Get user id from session_id
	 */
	protected function getUserIdFromSID()
	{
		$DBRESULT =& $this->DB->query("SELECT user_id FROM session WHERE session_id = '".$this->session_id."' LIMIT 1");
		$admin =& $DBRESULT->fetchRow();
		unset($DBRESULT);
		if (isset($admin["user_id"])) {
			$this->user_id = $admin["user_id"];
		}
	}

	/*
	 * Get Status Color
	 */
	protected function getStatusColor()
	{
		$this->general_opt = array();
		$DBRESULT =& $this->DB->query("SELECT * FROM `options` WHERE `key` LIKE 'color%'");
		while ($c =& $DBRESULT->fetchRow()) {
			$this->general_opt[$c["key"]] = myDecode($c["value"]);
		}
		$DBRESULT->free();
		unset($c);
	}

	/*
	 * Init NDO prefix
	 */
	protected function getNDOPrefix()
	{
		$DBRESULT =& $this->DB->query("SELECT db_prefix FROM cfg_ndo2db LIMIT 1");
		$conf_ndo =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		unset($DBRESULT);
		return $conf_ndo["db_prefix"];
	}

	/*
	 * Send headers information for web server
	 */
	public function header()
	{
		global $HTTP_ACCEPT_ENCODING;

		if (headers_sent()){
	        $encoding = false;
	    } else if (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false){
	        $encoding = 'x-gzip';
	    } else if (strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false){
	        $encoding = 'gzip';
	    } else {
	        $encoding = false;
	    }

		header('Content-Type: text/xml');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: no-cache, must-revalidate');
		if ($this->compress && $encoding)
			header('Content-Encoding: '.$encoding);
	}

	public function getNextLineClass()
	{
		if ($this->classLine == "list_one") {
			$this->classLine = "list_two";
		} else {
			$this->classLine = "list_one";
		}
		return $this->classLine;
	}

	public function getDefaultFilters()
	{
		$this->defaultPoller = NULL;
		$this->defaultHostgroups = NULL;
		$DBRESULT = $this->DB->query("SELECT cp_value, cp_key FROM contact_param WHERE cp_key IN ('monitoring_default_hostgroups', 'monitoring_default_poller') AND cp_contact_id = '".$this->user_id."'");
		if ($DBRESULT->numRows()) {
			while ($tmpRow =& $DBRESULT->fetchRow()) {
				if ($tmpRow['cp_key'] == "monitoring_default_hostgroups") {
					$this->defaultHostgroups = $tmpRow['cp_value'];
				}
				if ($tmpRow['cp_key'] == "monitoring_default_poller") {
					$this->defaultPoller = $tmpRow['cp_value'];
				}
			}
			$DBRESULT->free();
			unset($tmpRow);
		}
	}

	public function setInstanceHistory($instance)
	{
		$this->DB->query("DELETE FROM `contact_param` WHERE `cp_key` = 'MONITORING_POLLER_ID' AND `cp_contact_id` = '$this->user_id'");
		$this->DB->query("INSERT INTO `contact_param` SET `cp_key` = 'MONITORING_POLLER_ID', `cp_contact_id` = '" . $this->user_id . "', `cp_value` = '$instance'");
	}

	public function setHostGroupsHistory($hg)
	{
		$this->DB->query("DELETE FROM `contact_param` WHERE `cp_key` = 'monitoring_default_hostgroups' AND `cp_contact_id` = '$this->user_id'");
		$this->DB->query("INSERT INTO `contact_param` SET `cp_key` = 'monitoring_default_hostgroups', `cp_contact_id` = '" . $this->user_id . "', `cp_value` = '$hg'");
	}

	public function checkArgument($name, $tab, $defaultValue)
	{
		if (isset($name) && isset($tab)) {
			if (isset($tab[$name])) {
				return CentreonDB::escape($tab[$name]);
			}
			else {
				return CentreonDB::escape($defaultValue);
			}
		}
	}

	public function prepareObjectName($name)
	{
		$name = str_replace("/", "#S#", $name);
		$name = str_replace("\\", "#BS#", $name);
		return $name;
	}
}
?>
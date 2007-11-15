<?php
/**
Centreon is developped with GPL Licence 2.0 :
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

require_once("User.class.php");

class Oreon	{
		
	var $user;
	var $Nagioscfg;
	var $optGen;
	var $redirectTo;
	var $modules;
	var $plugins;
	var $status_graph_service;
	var $status_graph_host;
	var $historyPage;
  	var $historySearch;
	var $historyLimit;
  	var $search_type_service;
	var $search_type_host;
  
	function Oreon($user = NULL, $pages = array())	{
		global $pearDB;
		
		$this->user = $user;
		$this->initNagiosCFG($pearDB);
		$this->initOptGen($pearDB);
		
		# Grab Modules
		$this->modules = array();
		$handle = opendir("./modules");	
		while (false !== ($filename = readdir($handle)))	{
			if ($filename != "." && $filename != "..")	{
				$this->modules[$filename]["name"] = $filename;
				if (is_dir("./modules/".$filename."/generate_files/"))
					$this->modules[$filename]["gen"] = true;
				else
					$this->modules[$filename]["gen"] = false;
				if (is_dir("./modules/".$filename."/sql/"))
					$oreon->modules[$filename]["sql"] = true;
				else
					$this->modules[$filename]["sql"] = false;
				if (is_dir("./modules/".$filename."/lang/"))
					$this->modules[$filename]["lang"] = true;
				else
					$this->modules[$filename]["lang"] = false;
			}
		}
		closedir($handle);
	}
	
	function createHistory(){
  		$this->historyPage = array();
  		$this->historySearch = array();
  		$this->historyLimit = array();
  		$this->search_type_service = 1;
  		$this->search_type_host = 1;
  	}
	
	function initNagiosCFG($pearDB = NULL)	{
		if (!$pearDB)	return;
		$this->Nagioscfg = array();
		$res =& $pearDB->query("SELECT * FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
		$this->Nagioscfg = $res->fetchRow();	
	}
	
	function initOptGen($pearDB = NULL)	{
		if (!$pearDB)	return;
		$this->optGen = array();
		$res =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$this->optGen = $res->fetchRow();	
	}
}
?>
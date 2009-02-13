<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

require_once("User.class.php");
require_once("centreonGMT.class.php");
require_once("centreonLogAction.class.php");

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
	var $CentreonGMT;
	var $CentreonLogAction;
	var $svc_svc_search;
	var $svc_host_search;
	var $poller;
  
	function Oreon($user = NULL, $pages = array())	{
		global $pearDB;
		
		/*
		 * Get User informations
		 */
		$this->user = $user;
		
		/*
		 * Get Local nagios.cfg file
		 */
		$this->initNagiosCFG($pearDB);
		
		/*
		 * Get general options
		 */
		$this->initOptGen($pearDB);
		
		/*
		 * Grab Modules
		 */
		$this->creatModuleList($pearDB);
	
		/*
		 * Create GMT object
		 */
		$this->CentreonGMT = new CentreonGMT();
	
		/*
		 * Create LogAction object
		 */
		$this->CentreonLogAction = new CentreonLogAction($user);
		
		/*
		 * Init Poller id
		 */
		$this->poller = 0;
	}
	
	
	
	function creatModuleList($pearDB){
		$this->modules = array();
		$DBRESULT =& $pearDB->query("SELECT `name`, `sql_files`, `lang_files`, `php_files` FROM `modules_informations`");
		while ($result =& $DBRESULT->fetchRow()){
			$this->modules[$result["name"]] = array("name"=>$result["name"], "gen"=>false, "sql"=>$result["sql_files"], "lang"=>$result["lang_files"]);
			if (is_dir("./modules/".$result["name"]."/generate_files/"))
				$this->modules[$result["name"]]["gen"] = true;
		}
		$DBRESULT->free();
	}
	
	function createHistory(){
  		$this->historyPage = array();
  		$this->historySearch = array();
  		$this->historyLimit = array();
  		$this->search_type_service = 1;
  		$this->search_type_host = 1;
  	}
	
	function initNagiosCFG($pearDB = NULL)	{
		
		if (!$pearDB)	
			return;
		
		$this->Nagioscfg = array();
		$DBRESULT =& $pearDB->query("SELECT * FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
		$this->Nagioscfg = $DBRESULT->fetchRow();
		$DBRESULT->free();	
	}
	
	function initOptGen($pearDB = NULL)	{
		
		if (!$pearDB)	
			return;
		
		$this->optGen = array();
		$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
		while ($opt =& $DBRESULT->fetchRow()) {
			$this->optGen[$opt["key"]] = $opt["value"];
		}
		$DBRESULT->free();
		unset($opt);
	}
	
}
?>
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
  	var $historySearchService;
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
  		$this->historySearchService = array();
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
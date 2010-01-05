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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/class/centreonXMLBGRequest.class.php $
 * SVN : $Id: centreon.class.php 9656 2010-01-04 09:05:23Z jmathis $
 * 
 */

/*
 * Need Centreon Configuration file
 */
//require_once "@CENTREON_ETC@/centreon.conf.php";
require_once "/etc/centreon/centreon.conf.php";

/*
 * this class need also others classes
 */
require_once $centreon_path."www/class/centreonDuration.class.php";
require_once $centreon_path."www/class/centreonGMT.class.php";
require_once $centreon_path."www/class/centreonACL.class.php";
require_once $centreon_path."www/class/centreonDB.class.php";
require_once $centreon_path."www/class/centreonHost.class.php";
require_once $centreon_path."www/class/centreonService.class.php";
require_once $centreon_path."www/class/centreonSession.class.php";

/*
 * Class for XML/Ajax request
 * 
 */	
class CentreonXMLBGRequest	{

	/*
	 * Objects
	 */
	var $DB;
	var $DBC;
	
	var $XML;
	var $GMT;
	
	var $hostObj;
	var $serviceObj;
	
	var $session_id;
	
	/*
	 * Variables
	 */
	var $debug;
	var $compress;
	var $user_id;
	var $general_opt;
	var $filename;
	var $commandLine;
	var $dbPath;
	var $width;
	var $height;
	var $index;
	var $indexData;
	
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
	 */
	function CentreonGraph($session_id, $index, $debug, $compress = NULL) {
		if (!isset($debug))
			$this->debug = 0;
		
		(!isset($compress)) ? $this->compress = 1 : $this->compress = $compress;
		
		if (!isset($session_id)) {
			print "Your might check your session id";
			exit(1);
		} else {
			$this->session_id = htmlentities($session_id, ENT_QUOTES);
		}
		
		$this->index = htmlentities($index, ENT_QUOTES);
		
		/*
		 * Enable Database Connexions
		 */
		$this->DB 		= new CentreonDB();
		$this->DBC 		= new CentreonDB("centstorage");
		
		/*
		 * Init Objects
		 */
		$this->hostObj		= new CentreonHost($this->DB);
		$this->serviceObj	= new CentreonService($this->DB);
	
		/*
		 * Timezone management
		 */
		$this->GMT = new CentreonGMT($this->DB);
		$this->GMT->getMyGMTFromSession($this->session_id, $this->DB);		
		
		/*
		 * Set Command line
		 */
		$this->commandLine = "";
		
		/*
		 * Set parameters
		 */
		$this->width = 500;
		$this->height = 120;

		/*
		 * Get index data
		 */
		$this->getIndexData();
		$this->setFilename();

		$this->getRRDToolPath();
	}

	private function setFilename() {
		$this->filename = $this->indexData["host_name"]. "-".$this->indexData["service_description"];
		$this->filename = str_replace("#S#", "/", $this->filename);
		$this->filename = str_replace("#BS#", "\\", $this->filename);
	}

	/*
	 * Get index Data
	 */
	private function getIndexData() {
		$DBRESULT =& $this->DBC->query("SELECT * FROM index_data WHERE id = '".$this->index."' LIMIT 1");
		if (!$DBRESULT->numRows()) {
			$this->indexData = 0;
		} else {
			$this->indexData =& $DBRESULT->fetchRow();
			$this->indexData["host_name"] = str_replace("#S#", "/", $this->indexData["host_name"]);
			$this->indexData["host_name"] = str_replace("#BS#", "\\", $this->indexData["host_name"]);
			$this->indexData["service_description"] = str_replace("#S#", "/", $this->indexData["service_description"]);
			$this->indexData["service_description"] = str_replace("#BS#", "\\", $this->indexData["service_description"]);
		}
		$DBRESULT->free();	
	}
	
	/*
	 * Set General options 
	 */
	public function setGeneralOption($options) {
		$this->general_opt = $options;
	}	

	/*
	 * Set Filename in object in order to send it in the header
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
		$this->filename = str_replace("#S#", "/", $this->filename);
		$this->filename = str_replace("#BS#", "\\", $this->filename);
	}
	
	
	/*
	 * Get user id from session_id
	 */
	private function getUserIdFromSID() {
		$DBRESULT =& $this->DB->query("SELECT user_id FROM session WHERE session_id = '".$this->session_id."' LIMIT 1");
		$admin =& $DBRESULT->fetchRow();
		unset($DBRESULT);
		if (isset($admin["user_id"])) {
			$this->user_id = $admin["user_id"];
		}
	}
	
	/*
	 * Send headers information for web server
	 */
	public function header() {
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
 		
		header("Content-Type: image/png");
		header("Content-Transfer-Encoding: binary");
		header("Content-Disposition: attachment; filename=\"$this->filename\";");
		if ($this->compress && $encoding)
			header('Content-Encoding: '.$encoding);
	}
	
	public function displayError() {
		$image = imagecreate(250,100);
		$fond = imagecolorallocate($image,0xEF,0xF2,0xFB);		
		header("Content-Type: image/gif");
		imagegif($image);
		exit;
	}
	
	public function initCommandLine() {
		$this->commandLine = $this->general_opt["rrdtool_path_bin"];
		$this->commandLine .= " graph - "; 
	}
	
	public function endCommandLine() {
		$this->commandLine .= " 2>&1"; 
	}
	
	public function addCommandLineTimeLimit($start, $end) {
		$this->commandLine .= " --start=".$start." --end=".$end." ";
	}
	
	/*
	 * Concat command line parameters
	 */
	public function fillCommandLine($args) {
		$this->commandLine .= $args;
	}
	
	public function displayImageFlow() {
		$this->escapeCommand();
		$this->logCommandLine();
		/*
		 * Send header
		 */
		$this->header();
		
		/*
		 * Send Binary Data
		 */
		$fp = popen($this->commandLine  , 'r');
		if (isset($fp) && $fp ) {
			$str ='';
			while (!feof ($fp)) {
		  		$buffer = fgets($fp, 4096);
		 		$str = $str . $buffer ;
			}
			print $str;
		}
	}
	
	public function logCommandLine() {
		if ($this->general_opt['debug_rrdtool'])
			error_log("[" . date("d/m/Y H:s") ."] RDDTOOL : ".$this->commandLine." \n", 3, $this->general_opt["debug_path"]."rrdtool.log");
	}
	
	public function checkArgument($name, $tab, $defaultValue) {
		if (isset($name) && isset($tab)) {
			if (isset($tab[$name]))
				return htmlentities($tab[$name], ENT_QUOTES);
			else
				return htmlentities($defaultValue, ENT_QUOTES);
		}
	}
	
	public function setTimezone() {
		if ($this->GMT->used())
			$this->commandLine = "export TZ='CMT".$this->GMT->getMyGMTForRRD()."' ; ".$this->commandLine;
	}
	
	public function escapeCommand() {
		$this->commandLine = ereg_replace("(\\\$|`)", "", $this->commandLine);
	}
	
	public function getRRDToolPath(){
		$DBRESULT =& $this->DBC->query("SELECT RRDdatabase_path FROM config LIMIT 1");
		$config =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		$this->dbPath = $config["RRDdatabase_path"];
		unset($config);
	}

	public function setWidth($width) {
		$this->width = $width;
	}

	public function setHeight($height) {
		$this->height = $height;
	}
}
?>
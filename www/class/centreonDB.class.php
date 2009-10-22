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
 
/*
 * Integrate Pear Class
 */
require_once ("DB.php");

class CentreonDB {		
	private $db_type = "mysql";
	private $retry;
	private $privatePearDB;
	private $dsn;
	private $options;
	private $centreon_path;
	private $log;
	
	/*
	 *  Constructor only accepts 1 parameter which can be :
	 *  - centreon or NULL
	 *  - centstorage
	 *  - ndo
	 */
    function CentreonDB($db = "centreon", $retry = 3) {	
		
		include("@CENTREON_ETC@/centreon.conf.php");
			
		require_once $centreon_path."/www/class/centreonLog.class.php";
		$this->log = new CentreonLog();
	
		$this->centreon_path = $centreon_path;
		$this->retry = $retry;				
		$this->options = array('debug' => 2,'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);
		switch (strtolower($db)) {
			case "centreon" : 				
				$this->connectToCentreon($conf_centreon);
				$this->connect();
				break;
			case "centstorage" :
				$this->connectToCentstorage($conf_centreon);
				$this->connect();
				break;
			case "ndo" : 
				$this->connectToCentreon($conf_centreon);
				$this->connect();
				$this->connectToNDO($conf_centreon);
				$this->connect();
				break;
			case "default" : 
				$this->connectToCentreon($conf_centreon);
				$this->connect();
				break;
		}		
    }
    
	private function displayConnectionErrorPage() {
		echo "<img src='./img/centreon.gif'><br/>";
		echo "<b>" . _("Connection failed, please contact your administrator") . "</b>";		
		exit;
	}    
    
    /*
     *  Get info to connect to Centreon DB
     */
    private function connectToCentreon($conf_centreon) {		
		$this->dsn = array(
	    	'phptype'  => $this->db_type,
	    	'username' => $conf_centreon["user"],
	    	'password' => $conf_centreon["password"],
	    	'hostspec' => $conf_centreon["hostCentreon"],
	    	'database' => $conf_centreon["db"],
		);		
    }
    
    /*
     *  Get info to connect to Centstorage DB
     */
    private function connectToCentstorage($conf_centreon) {
    	$this->dsn = array(
	    	'phptype'  => $this->db_type,
	    	'username' => $conf_centreon["user"],
	    	'password' => $conf_centreon["password"],
	    	'hostspec' => $conf_centreon["hostCentstorage"],
	    	'database' => $conf_centreon["dbcstg"],
		);
    }
    
    /*
     *  Get info to connect to NDO DB
     */
    private function connectToNDO($conf_centreon) {		
		$DBRESULT =& $this->privatePearDB->query("SELECT db_name, db_prefix, db_user, db_pass, db_host FROM cfg_ndo2db LIMIT 1;");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$confNDO = $DBRESULT->fetchRow();
		unset($DBRESULT);
		
		$this->dsn = array(
	    	'phptype'  => $this->db_type,
	    	'username' => $confNDO['db_user'],
	    	'password' => $confNDO['db_pass'],
	    	'hostspec' => $confNDO['db_host'],
	    	'database' => $confNDO['db_name'],
		);
    }
    
    /*
     *  The connection is established here
     */
    public function connect() {    	    	    	
    	
    	$this->privatePearDB =& DB::connect($this->dsn, $this->options);
		$i = 0;
		while (PEAR::isError($this->privatePearDB) && ($i < $this->retry)) {
			$this->privatePearDB =& DB::connect($this->dsn, $this->options);
			$i++;
		}
		if ($i == $this->retry) {
			$this->log->insertLog(2, $this->privatePearDB->getMessage() . " (retry : $i)");
			$this->displayConnectionErrorPage();
		} else {	
			$this->privatePearDB->setFetchMode(DB_FETCHMODE_ASSOC);
		}
    }
    
    /*
     *  Disconnection
     */
    public function disconnect() {
    	$this->privatePearDB->disconnect();
    }
    
    public function toString() {
    	return $this->privatePearDB->toString();
    }
    
    /*
     *  Query
     */
    public function query($query_string = NULL) {    	
    	global $oreon;
    	
    	$DBRES = $this->privatePearDB->query($query_string);
    	if (PEAR::isError($DBRES))
    		$this->log->insertLog(2, $DBRES->getMessage() . " QUERY : " . $query_string);
    	return $DBRES;
    }
    
    /*
     * Check NDO user grants
     */

	public function hasGrants($grant = "") {
		if ($grant == "")
			return 0;
		
		$db_name = $this->dsn["database"];
		 
		$DBRESULT =& $this->query("show grants"); 
		while ($result =& $DBRESULT->fetchRow()) {
			foreach ($result as $key => $value)
				;
			$expr = "/GRANT\ ([a-zA-Z\_\-\,\ ]*)\ ON `".$db_name."`.\*/";				
			if (preg_match($expr, $value, $matches)) {
				if ($matches[1] == "ALL PRIVILEGES" || strstr($matches[1], $grant)) {
					return 1;
				}
			}
		}
	}

}
?>
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

require_once ("DB.php");

class CentreonDB {		
	private $db_type = "mysql";
	private $retry;
	private $privatePearDB;
	private $dsn;
	private $options;
	
	/*
	 *  Constructor only accepts 1 parameter which can be :
	 *  - centreon or NULL
	 *  - centstorage
	 *  - ndo
	 */
    function CentreonDB($db = "centreon", $retry = 3) {
		include("/etc/centreon/centreon.conf.php");

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
			echo "retry : $i times <br />";//log error with the number of attempts
			echo $this->privatePearDB->getMessage() . "<br />";	
		}
		else	
			$this->privatePearDB->setFetchMode(DB_FETCHMODE_ASSOC);
    }
    
    /*
     *  Disconnection
     */
    public function disconnect() {
    	$this->privatePearDB->disconnect();
    }
    
    /*
     *  Query
     */
    public function query($query_string = NULL) {    	
    	$DBRES = $this->privatePearDB->query($query_string);
    	if (PEAR::isError($DBRES))
    		$DBRES->getMessage();//log error
    	return $DBRES;
    }
}
?>
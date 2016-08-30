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
 */

class procedures_DB_Connector {
	private $db_type = "mysql";
	private $retry;
	private $privatePearDB;
	private $dsn;
	private $options;
	private $log;
	public $debug;

	/*
	 *  Constructor only accepts 1 parameter which can be :
	 *  - centreon or NULL
	 *  - centstorage
	 *  - ndo
	 */
    function procedures_DB_Connector($retry = 3, $db_name, $db_user, $db_host, $db_password) {
		$this->retry = $retry;
		$this->options = array('debug' => 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);
		$this->log = new CentreonLog();
		$this->connectToWiki($db_name, $db_user, $db_host, $db_password);
		$this->connect();
		$this->debug = 0;
    }

	private function displayConnectionErrorPage() {
		echo "<center><b>" . _("Connection to Wiki database failed, please contact your administrator or read the Centreon online documentation to configure wiki access") . "</b></center>";
		exit;
	}

    /*
     *  Get info to connect to Centreon DB
     */
    private function connectToWiki($db_name, $db_user, $db_host, $db_password) {
		$this->dsn = array(
	    	'phptype'  => $this->db_type,
	    	'username' => $db_user,
	    	'password' => $db_password,
	    	'hostspec' => $db_host,
	    	'database' => $db_name,
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

    	if ($this->debug) {
    		$query = str_replace("`", "", $query_string);
    		$query = str_replace("'", "\'", $query);
    		$query = str_replace("*", "\*", $query);
    		exec("echo '$query' >> $log_centreon/procedure.log");
    	}
    	$DBRES = $this->privatePearDB->query($query_string);
    	if (PEAR::isError($DBRES))
    		$this->log->insertLog(2, $DBRES->getMessage() . " QUERY : " . $query_string);
    	return $DBRES;
    }
}
?>

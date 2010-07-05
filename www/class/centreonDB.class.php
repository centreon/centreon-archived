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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

require_once ("DB.php");

class CentreonDB
{
	protected $db_type = "mysql";
	protected $db_port = "3306";
	protected $retry;
	protected $privatePearDB;
	protected $dsn;
	protected $options;
	protected $centreon_path;
	protected $log;
	/*
	 * Statistics
	 */
	protected $requestExecuted;
	protected $requestSuccessful;
	protected $lineRead;
	protected $debug;

	/**
	 *  Constructor only accepts 1 parameter which can be :
	 *  - centreon or NULL
	 *  - centstorage
	 *  - ndo
	 *
	 * @return void
	 */
    public function __construct($db = "centreon", $retry = 3)
    {
		include("@CENTREON_ETC@/centreon.conf.php");

		require_once $centreon_path."/www/class/centreonLog.class.php";
		$this->log = new CentreonLog();

		$this->centreon_path = $centreon_path;
		$this->retry = $retry;
		$this->options = array('debug' => 2,'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);

		/*
		 * Add possibility to change SGDB port
		 */
		if (isset($conf_centreon["port"]) && $conf_centreon["port"] != "") {
			$this->db_port = $conf_centreon["port"];
		}

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
		/*
		 * Init request statistics
		 */
		$this->requestExecuted = 0;
		$this->requestSuccessful = 0;
		$this->lineRead = 0;

		$this->debug = 1;
    }

	/**
     * Display error page
     *
     * @access protected
	 * @return	void
     */
    protected function displayConnectionErrorPage()
    {
		echo "<img src='./img/centreon.gif'><br/>";
		echo "<b>" . _("Connection failed, please contact your administrator") . "</b>";
		exit;
	}

    /**
     * estrablish centreon DB connector
     *
     * @access protected
	 * @return	void
     */
	protected function connectToCentreon($conf_centreon)
	{
		if (!isset($conf_centreon["port"])) {
			$conf_centreon["port"] = "3306";
		}

		$this->dsn = array(
	    	'phptype'  => $this->db_type,
	    	'username' => $conf_centreon["user"],
	    	'password' => $conf_centreon["password"],
	    	'hostspec' => $conf_centreon["hostCentreon"].":".$conf_centreon["port"],
	    	'database' => $conf_centreon["db"],
		);
    }

    /**
     * estrablish Centstorage DB connector
     *
     * @access protected
	 * @return	void
     */
	protected function connectToCentstorage($conf_centreon)
	{
    	if (!isset($conf_centreon["port"])) {
			$conf_centreon["port"] = "3306";
		}

    	$this->dsn = array(
	    	'phptype'  => $this->db_type,
	    	'username' => $conf_centreon["user"],
	    	'password' => $conf_centreon["password"],
	    	'hostspec' => $conf_centreon["hostCentstorage"].":".$conf_centreon["port"],
	    	'database' => $conf_centreon["dbcstg"],
		);
    }

    /**
     * estrablish NDO DB connector
     *
     * @access protected
	 * @return	void
     */
    protected function connectToNDO($conf_centreon)
    {
		$DBRESULT =& $this->privatePearDB->query("SELECT db_name, db_prefix, db_user, db_pass, db_host, db_port FROM cfg_ndo2db LIMIT 1;");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$confNDO = $DBRESULT->fetchRow();
		unset($DBRESULT);

		if (!isset($confNDO['db_port'])) {
			$confNDO['db_port'] = "3306";
		}

		$this->dsn = array(
	    	'phptype'  => $this->db_type,
	    	'username' => $confNDO['db_user'],
	    	'password' => $confNDO['db_pass'],
	    	'hostspec' => $confNDO['db_host'].":".$confNDO['db_port'],
	    	'database' => $confNDO['db_name'],
		);
    }

    /**
     * estrablish DB connector
     *
     * @access protected
	 * @return	void
     */
	protected function connect()
	{
    	$this->privatePearDB =& DB::connect($this->dsn, $this->options);
		$i = 0;
		while (PEAR::isError($this->privatePearDB) && ($i < $this->retry)) {
			$this->privatePearDB =& DB::connect($this->dsn, $this->options);
			$i++;
		}
		if ($i == $this->retry) {
			if ($this->debug)
				$this->log->insertLog(2, $this->privatePearDB->getMessage() . " (retry : $i)");
			$this->displayConnectionErrorPage();
		} else {
			$this->privatePearDB->setFetchMode(DB_FETCHMODE_ASSOC);
		}
    }

	/**
     * Disconnect DB connector
     *
     * @access protected
	 * @return	void
     */
	protected function disconnect()
	{
    	$this->privatePearDB->disconnect();
    }

    /**
     * To string method
     *
     * @access protected
     * @return string
     */
    protected function toString()
    {
    	return $this->privatePearDB->toString();
    }

    /**
     * launch a query
     *
     * @access protected
	 * @param	string	$query_string	query
	 * @return	object	query result
     */
	protected function query($query_string = null)
	{
		$this->requestExecuted++;
    	$DBRES = $this->privatePearDB->query($query_string);
    	if (PEAR::isError($DBRES)) {
    		if ($this->debug) {
				$this->log->insertLog(2, $DBRES->getMessage() . " QUERY : " . $query_string);
    		}
    	} else {
			$this->requestSuccessful++;
    	}
    	return $DBRES;
    }

    /**
     * Check NDO user grants
     * Check if user is able to modify schema.
     *
     * @access protected
	 * @param	char	$grant	User Name
	 * @return	int		result flag
     */
	protected function hasGrants($grant = "")
	{
		if ($grant == "") {
			return 0;
		}

		$db_name = $this->dsn["database"];
		$db_nameSec = str_replace("_", "\\\_", $this->dsn["database"]);
		$db_nameSec = str_replace("-", "\\\-", $db_nameSec);

		$DBRESULT =& $this->query("show grants");
		while ($result =& $DBRESULT->fetchRow()) {
			foreach ($result as $key => $value)
				;
			$expr = "/GRANT\ ([a-zA-Z\_\-\,\ ]*)\ ON `".$db_name."`.\*/";
			$expr2 = "/GRANT\ ([a-zA-Z\_\-\,\ ]*)\ ON `".$db_nameSec."`.\*/";
			if (preg_match($expr, $value, $matches) || preg_match($expr2, $value, $matches)) {
				if ($matches[1] == "ALL PRIVILEGES" || strstr($matches[1], $grant)) {
					return 1;
				}
			}
		}
	}
}
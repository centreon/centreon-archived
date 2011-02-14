<?php
/**
 * Copyright 2005-2011 MERETHIS
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

class CentreonDbPdo extends CentreonDB
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($dbname = "centreon", $retry = 3)
    {
        parent::__construct($dbname, $retry);
    }

	/**
     *  The connection is established here
     *
     *  @return void
     */
    public function connect()
    {
        try {
            $this->db = new CentreonPdo($this->dsn['phptype'].":"."dbname=".$this->dsn['database'] . ";host=".$this->dsn['hostspec'] . ";port=".$this->dsn['port'],
                                $this->dsn['username'],
                                $this->dsn['password'],
                                $this->options);
        }
        catch (PDOException $e) {
            echo $e->getMessage();
        }
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
	    	'hostspec' => $conf_centreon["hostCentreon"],
		    'port'	   => $conf_centreon["port"],
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
	    	'hostspec' => $conf_centreon["hostCentstorage"],
    		'port'	   => $conf_centreon["port"],
	    	'database' => $conf_centreon["dbcstg"],
		);
    }


	/**
     *  Get info to connect to NDO DB
     *
     *  @return void
     */
    protected function connectToNDO($conf_centreon)
    {
		$DBRESULT = $this->db->query("SELECT db_port, db_name, db_prefix, db_user, db_pass, db_host FROM cfg_ndo2db LIMIT 1;");
		$confNDO = $DBRESULT->fetchRow();

        if (!isset($confNDO["db_port"]) || !$confNDO["db_port"]) {
			$confNDO["db_port"] = "3306";
		}

		$this->dsn = array(
	    	'phptype'  => $this->db_type,
	    	'username' => $confNDO['db_user'],
	    	'password' => $confNDO['db_pass'],
	    	'hostspec' => $confNDO['db_host'],
			'port'     => $confNDO['db_port'],
	    	'database' => $confNDO['db_name'],
		);
    }

    /**
     *  Removes quotes from values
     *
     *  @return string
     */
    public function quote($str)
    {
        return $this->db->quote($str);
    }

	/**
     *  Query
     *
     *  @return void
     */
    public function query($queryString = NULL)
    {
    	/*
    	 * LOG all request
    	 */
    	if ($this->debug) {
	    	$string = str_replace("`", "", $queryString);
	    	$string = str_replace('*', "\*", $string);
	    	$this->log->insertLog(2, " QUERY : " . $string);
    	}

    	/*
    	 * Launch request
    	 */
    	try {
    	    $dbres = $this->db->query($queryString);
    	    $this->queryNumber++;
    	    $this->successQueryNumber++;
    	}
    	catch (PDOException $e) {
    	    echo $e->getMessage();
    	}
    	return $dbres;
    }
}
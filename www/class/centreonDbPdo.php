<?php

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
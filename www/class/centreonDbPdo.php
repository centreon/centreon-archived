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
            $this->db = new CentreonMap_PDO($this->dsn['phptype'].":"."dbname=".$this->dsn['database'] . ";host=".$this->dsn['hostspec'],
                                $this->dsn['username'],
                                $this->dsn['password'],
                                $this->options);
        }
        catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

	/**
     *  Get info to connect to NDO DB
     *
     *  @return void
     */
    protected function connectToNDO($conf_centreon)
    {
		$DBRESULT = $this->db->query("SELECT db_name, db_prefix, db_user, db_pass, db_host FROM cfg_ndo2db LIMIT 1;");
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
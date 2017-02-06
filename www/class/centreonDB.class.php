<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

include_once(realpath(dirname(__FILE__) . "/../../config/centreon.config.php"));
require_once realpath(dirname(__FILE__) . "/centreonDBStatement.class.php");

class CentreonDB extends \PDO
{

    private static $instance = array();
    protected $db_type = "mysql";
    protected $db_port = "3306";
    protected $retry;
    protected $db;
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
     * Constructor
     *
     * @param string $db | centreon, centstorage, or ndo
     * @param int $retry
     * @param bool $silent | when silent is set to false, it will display an HTML error msg,
     *                       otherwise it will throw an Exception
     * @return void
     */
    public function __construct($db = "centreon", $retry = 3, $silent = true)
    {
        try {
            $conf_centreon['hostCentreon'] = hostCentreon;
            $conf_centreon['hostCentstorage'] = hostCentstorage;
            $conf_centreon['user'] = user;
            $conf_centreon['password'] = password;
            $conf_centreon['db'] = db;
            $conf_centreon['dbcstg'] = dbcstg;
            $conf_centreon['port'] = port;

            require_once _CENTREON_PATH_ . "/www/class/centreonLog.class.php";
            $this->log = new CentreonLog();

            $this->centreon_path = _CENTREON_PATH_;
            $this->retry = $retry;

            $this->options = array(
                //PDO::ATTR_CASE => PDO::CASE_LOWER
            );

            /*
             * Add possibility to change SGDB port
             */
            if (isset($conf_centreon["port"]) && $conf_centreon["port"] != "") {
                $this->db_port = $conf_centreon["port"];
            }

            switch (strtolower($db)) {
                case "centstorage":
                    $this->connectToCentstorage($conf_centreon);
                    $this->connect();
                    break;
                case "centreon":
                case "default":
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

            $this->debug = 0;
            if (false === $silent) {
                $this->debug = 1;
            }

            parent::__construct($this->dsn['phptype'].":"."dbname=".$this->dsn['database'] .
                ";host=".$this->dsn['hostspec'] . ";port=".$this->dsn['port'],
                $this->dsn['username'],
                $this->dsn['password'],
                $this->options
            );
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('CentreonDBStatement', array($this->db)));
        } catch (Exception $e) {
            if (false === $silent && php_sapi_name() != "cli") {
                $this->displayConnectionErrorPage($e->getMessage());
            } else {
                throw new Exception($e->getMessage());
            }
        }
    }
    
    public function autoCommit($val)
    {
        $this->db->autoCommit($val);
    }
    
    public function prepare($query)
    {
        return $this->db->prepare($query);
    }
    
    public function executeMultiple($stmt, $arrayValues)
    {
        return $this->db->executeMultiple($stmt, $arrayValues);
    }
    
    public function autoPrepare($query)
    {
        return $this->db->autoPrepare($query);
    }
    
    public function commit()
    {
        $this->db->commit();
    }
    
    public function execute($stmt, $arrayValues)
    {
        return $stmt->execute($arrayValues);
    }
    
    public function rollback()
    {
        $this->db->rollback();
    }
    
    /**
     *
     * @return type
     */
    public function getMessage()
    {
        return $this->db->getMessage();
    }
    
    /**
     *
     * @return type
     */
    public function getCode()
    {
        return $this->db->getCode();
    }

    /**
     * Display error page
     *
     * @access protected
     * @return  void
     */
    protected function displayConnectionErrorPage($msg = null)
    {
        if (!$msg) {
            $msg = _("Connection failed, please contact your administrator");
        }
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
              <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
                <head>
                <style type="text/css">
                       div.Error{background-color:#fa6f6c;border:1px #AEAEAE solid;width: 500px;}
                       div.Error{border-radius:4px;}
                       div.Error{padding: 15px;}
                       a, div.Error{font-family:"Bitstream Vera Sans", arial, Tahoma, "Sans serif";font-weight: bold;}
                </style>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <title>Centreon</title>
              </head>
                <body>
                  <center>
                  <div style="padding-top:150px;padding-bottom:50px;">
                        <img src="./img/centreon.png" alt="Centreon"/><br/>
                  </div>
                  <div class="Error">' . $msg . '</div>
                  <div style="padding: 50px;"><a href="#" onclick="location.reload();">Refresh Here</a></div>
                  </center>
                </body>
              </html>';
        exit;
    }

    /**
     * establish centreon DB connector
     *
     * @access protected
     * @return  void
     */
    protected function connectToCentreon($conf_centreon)
    {
        if (!isset($conf_centreon["port"])) {
            $conf_centreon["port"] = "3306";
        }

        $this->dsn = array(
            'phptype' => $this->db_type,
            'username' => $conf_centreon["user"],
            'password' => $conf_centreon["password"],
            'hostspec' => $conf_centreon["hostCentreon"],
            'port'     => $conf_centreon["port"],
            'database' => $conf_centreon["db"],
        );
    }

    /**
     * establish Centstorage DB connector
     *
     * @access protected
     * @return  void
     */
    protected function connectToCentstorage($conf_centreon)
    {
        if (!isset($conf_centreon["port"])) {
            $conf_centreon["port"] = "3306";
        }

        $this->dsn = array(
            'phptype' => $this->db_type,
            'username' => $conf_centreon["user"],
            'password' => $conf_centreon["password"],
            'hostspec' => $conf_centreon["hostCentstorage"],
            'port'     => $conf_centreon["port"],
            'database' => $conf_centreon["dbcstg"],
        );
    }

    /**
     *  The connection is established here
     *
     *  @return void
     */
    public function connect()
    {
        try {
            $this->db = new \Pdo(
                $this->dsn['phptype'].":"."dbname=".$this->dsn['database'] .
                ";host=".$this->dsn['hostspec'] . ";port=".$this->dsn['port'],
                $this->dsn['username'],
                $this->dsn['password'],
                $this->options
            );
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('CentreonDBStatement', array($this->db)));
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Disconnect DB connector
     *
     * @access public
     * @return  void
     */
    public function disconnect()
    {
        $this->db = null;
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
     * To string method
     *
     * @access public
     * @return string
     */
    public function toString()
    {
        return $this->db->toString();
    }

    /**
     * Escapes a string for query
     *
     * @access public
     * @param string $str
     * @param bool $htmlSpecialChars | htmlspecialchars() is used when true
     * @return string
     */
    public static function escape($str, $htmlSpecialChars = false)
    {
        if ($htmlSpecialChars) {
            $str = htmlspecialchars($str);
        }

        $escapedStr = addslashes($str);

        return $escapedStr;
    }

    /**
     *  Query
     *
     *  @return void
     */
    public function query($queryString = null, $parameters = null)
    {
        if (!is_null($parameters) && !is_array($parameters)) {
            $parameters = array($parameters);
        }

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
        $sth = null;
        try {
            $this->db->query("SET NAMES 'utf8'");
            $sth = $this->db->prepare($queryString);
            $sth->execute($parameters);
            $this->queryNumber++;
            $this->successQueryNumber++;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return $sth;
    }

    /**
     * launch a getAll
     *
     * @access public
     * @param   string  $query_string   query
     * @return  object  getAll result
     */
    public function getAll($query_string = null, $placeHolders = array())
    {
        $rows = array();
        $this->requestExecuted++;

        try {
            $result = $this->db->query($query_string);
            $rows = $result->fetchAll();
            $this->requestSuccessful++;
        } catch (\Exception $e) {
            if ($this->debug) {
                $this->log->insertLog(2, $e->getMessage() . " QUERY : " . $query_string);
            }
        }

        return $rows;
    }

    /**
     * Factory for singleton
     *
     * @param string $name The name of centreon datasource
     * @throws Exception
     * @return CentreonDB
     */
    public static function factory($name = "centreon")
    {
        if (!in_array($name, array('centreon', 'centstorage', 'ndo'))) {
            throw new Exception("The datasource isn't defined in configuration file.");
        }
        if (!isset(self::$instance[$name])) {
            self::$instance[$name] = new CentreonDB($name);
        }
        return self::$instance[$name];
    }

    /**
     * return number of rows
     *
     */
    public function numberRows()
    {
        $number = 0;
        $DBRESULT = $this->query("SELECT FOUND_ROWS() AS number");
        $data = $DBRESULT->fetch();
        if (isset($data["number"])) {
            $number = $data["number"];
        }
        return $number;
    }
    
    /*
     * checks if there is malicious injection 
     */
    public static function checkInjection($sString)
    {
        return 0;
    }

    /*
     * return database Properties
     *
     * <code>
     * $dataCentreon = getProperties();
     * </code>
     *
     * @return array dbsize, numberOfRow, freeSize
     */
    public function getProperties()
    {
        $unitMultiple = 1024*1024;

        $info = array(
            'version' => null,
            'engine' => null,
            'dbsize' => 0,
            'rows' => 0,
            'datafree' => 0,
            'indexsize' => 0
        );
        /*
         * Get Version
         */
        if ($res = $this->db->query("SELECT VERSION() AS mysql_version")) {
            $row = $res->fetchRow();
            $version = $row['mysql_version'];
            $info['version'] = $row['mysql_version'];
            if ($DBRESULT = $this->db->query("SHOW TABLE STATUS FROM `".$this->dsn['database']."`")) {
                while ($data = $DBRESULT->fetch()) {
                    $info['dbsize'] += $data['Data_length'] + $data['Index_length'];
                    $info['indexsize'] += $data['Index_length'];
                    $info['rows'] += $data['Rows'];
                    $info['datafree'] += $data['Data_free'];
                }
                $DBRESULT->closeCursor();
            }
            foreach ($info as $key => $value) {
                if ($key != "rows" && $key != "version" && $key != "engine") {
                    $info[$key] = round($value / $unitMultiple, 2);
                }
                if ($key == "version") {
                    $tab = explode('-', $value);
                    $info["version"] = $tab[0];
                    $info["engine"] = $tab[1];
                }
            }
        }
        return $info;
    }
}

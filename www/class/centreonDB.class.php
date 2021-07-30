<?php

/*
 * Copyright 2005-2021 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

// file centreon.config.php may not exist in test environment
$configFile = realpath(__DIR__ . "/../../config/centreon.config.php");
if ($configFile !== false) {
    require_once $configFile;
}

require_once __DIR__ . '/centreonDBStatement.class.php';
require_once __DIR__ . '/centreonLog.class.php';

/**
 * Class CentreonDB used to manage DB connection
 */
class CentreonDB extends \PDO
{
    public const LABEL_DB_CONFIGURATION = 'centreon';
    public const LABEL_DB_REALTIME = 'centstorage';
    private static $instance = [];
    protected $db_type = "mysql";
    protected $db_port = "3306";
    protected $retry;
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
     * @var int
     */
    private $queryNumber;

    /**
     * @var int
     */
    private $successQueryNumber;

    /**
     * Constructor
     *
     * @param string $db | centreon, centstorage
     * @param int $retry
     * @param bool $silent | when silent is set to false, it will display an HTML error msg,
     *                       otherwise it will throw an Exception
     *
     * @throws Exception
     */
    public function __construct($db = self::LABEL_DB_CONFIGURATION, $retry = 3, $silent = false)
    {
        try {
            $conf_centreon['hostCentreon'] = hostCentreon;
            $conf_centreon['hostCentstorage'] = hostCentstorage;
            $conf_centreon['user'] = user;
            $conf_centreon['password'] = password;
            $conf_centreon['db'] = db;
            $conf_centreon['dbcstg'] = dbcstg;
            $conf_centreon['port'] = port;

            $this->log = new CentreonLog();

            $this->centreon_path = _CENTREON_PATH_;
            $this->retry = $retry;

            $this->debug = 0;
            if (false === $silent) {
                $this->debug = 1;
            }

            $this->options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_STATEMENT_CLASS => [
                    CentreonDBStatement::class,
                    [$this, $this->log, $this->debug],
                ],
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::MYSQL_ATTR_LOCAL_INFILE => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];

            /*
             * Add possibility to change SGDB port
             */
            if (!empty($conf_centreon["port"])) {
                $this->db_port = $conf_centreon["port"];
            } else {
                $this->db_port = '3306';
            }

            $this->dsn = [
                'phptype' => $this->db_type,
                'username' => $conf_centreon["user"],
                'password' => $conf_centreon["password"],
                'port' => $this->db_port
            ];

            switch (strtolower($db)) {
                case self::LABEL_DB_REALTIME:
                    $this->dsn['hostspec'] = $conf_centreon["hostCentstorage"];
                    $this->dsn['database'] = $conf_centreon["dbcstg"];
                    break;
                default:
                    $this->dsn['hostspec'] = $conf_centreon["hostCentreon"];
                    $this->dsn['database'] = $conf_centreon["db"];
                    break;
            }

            /*
             * Init request statistics
             */
            $this->requestExecuted = 0;
            $this->requestSuccessful = 0;
            $this->lineRead = 0;

            parent::__construct(
                $this->dsn['phptype'] . ":" . "dbname=" . $this->dsn['database'] .
                ";host=" . $this->dsn['hostspec'] . ";port=" . $this->dsn['port'],
                $this->dsn['username'],
                $this->dsn['password'],
                $this->options
            );
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
        /* Deprecated */
    }

    /**
     *
     * @param type $stmt
     * @param type $arrayValues
     *
     * @return type
     */
    public function execute($stmt, $arrayValues)
    {
        return $stmt->execute($arrayValues);
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
     * Escapes a string for query
     *
     * @access public
     *
     * @param string $str
     * @param bool $htmlSpecialChars | htmlspecialchars() is used when true
     *
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
     * Query
     *
     * @return CentreonDBStatement
     */
    public function query($queryString, $parameters = null, ...$parametersArgs)
    {
        if (!is_null($parameters) && !is_array($parameters)) {
            $parameters = [$parameters];
        }

        /*
         * Launch request
         */
        $sth = null;
        try {
            if (is_null($parameters)) {
                $sth = parent::query($queryString);
            } else {
                $sth = $this->prepare($queryString);
                $sth->execute($parameters);
            }
        } catch (\PDOException $e) {
            // skip if we use CentreonDBStatement::execute method
            if ($this->debug && is_null($parameters)) {
                $string = str_replace("`", "", $queryString);
                $string = str_replace('*', "\*", $string);
                $this->log->insertLog(2, " QUERY : " . $string);
            }

            throw new \PDOException($e->getMessage(), hexdec($e->getCode()));
        }

        $this->queryNumber++;
        $this->successQueryNumber++;

        return $sth;
    }

    /**
     * launch a getAll
     *
     * @access public
     *
     * @param string $query_string query
     *
     * @return  object  getAll result
     */
    public function getAll($query_string = null, $placeHolders = [])
    {
        $rows = [];
        $this->requestExecuted++;

        try {
            $result = $this->query($query_string);
            $rows = $result->fetchAll();
            $this->requestSuccessful++;
        } catch (\PDOException $e) {
            if ($this->debug) {
                $this->log->insertLog(2, $e->getMessage() . " QUERY : " . $query_string);
            }
            throw new \PDOException($e->getMessage(), hexdec($e->getCode()));
        }

        return $rows;
    }

    /**
     * Factory for singleton
     *
     * @param string $name The name of centreon datasource
     *
     * @return CentreonDB
     * @throws Exception
     */
    public static function factory($name = self::LABEL_DB_CONFIGURATION)
    {
        if (!in_array($name, [self::LABEL_DB_CONFIGURATION, self::LABEL_DB_REALTIME])) {
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
        $dbResult = $this->query("SELECT FOUND_ROWS() AS number");
        $data = $dbResult->fetch();
        if (isset($data["number"])) {
            $number = $data["number"];
        }
        return $number;
    }

    /**
     * checks if there is malicious injection
     */
    public static function checkInjection($sString)
    {
        return 0;
    }

    /**
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
        $unitMultiple = 1024 * 1024;

        $info = [
            'version' => null,
            'engine' => null,
            'dbsize' => 0,
            'rows' => 0,
            'datafree' => 0,
            'indexsize' => 0
        ];
        /*
         * Get Version
         */
        if ($res = $this->query("SELECT VERSION() AS mysql_version")) {
            $row = $res->fetch();
            $version = $row['mysql_version'];
            $info['version'] = $row['mysql_version'];
            if ($dbResult = $this->query("SHOW TABLE STATUS FROM `" . $this->dsn['database'] . "`")) {
                while ($data = $dbResult->fetch()) {
                    $info['dbsize'] += $data['Data_length'] + $data['Index_length'];
                    $info['indexsize'] += $data['Index_length'];
                    $info['rows'] += $data['Rows'];
                    $info['datafree'] += $data['Data_free'];
                }
                $dbResult->closeCursor();
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

    /**
     * As 'ALTER TABLE IF NOT EXIST' queries are supported only by mariaDB (no more by mysql),
     * This method check if a column was already added in a previous upgrade script.
     *
     * @param string $table - the table on which we'll search the column
     * @param string $column - the column name to be checked
     *
     * @return int
     */
    public function isColumnExist(string $table = null, string $column = null): int
    {
        if (!$table || !$column) {
            return -1;
        }

        $table = filter_var($table, FILTER_SANITIZE_STRING);
        $column = filter_var($column, FILTER_SANITIZE_STRING);

        $query = "SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = :dbName
            AND TABLE_NAME = :tableName
            AND COLUMN_NAME = :columnName";

        $stmt = $this->prepare($query);

        try {
            $stmt->bindValue(':dbName', $this->dsn['database'], \PDO::PARAM_STR);
            $stmt->bindValue(':tableName', $table, \PDO::PARAM_STR);
            $stmt->bindValue(':columnName', $column, \PDO::PARAM_STR);
            $stmt->execute();
            $stmt->fetch();

            if ($stmt->rowCount()) {
                return 1; // column already exist
            }
            return 0; // column to add
        } catch (\PDOException $e) {
            if ($this->debug) {
                $this->log->insertLog(2, $e->getMessage() . " QUERY : " . $query);
            }
            return -1;
        }
    }
}

<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Internal;

/**
 * Class for manage database connection
 *
 * @see http://www.php.net/manual/en/class.pdo.php
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class Db extends \PDO
{
    /**
     * Constructor
     *
     * @see http://www.php.net/manual/en/pdo.construct.php
     * @param $dsn string The datasource name
     * @param $username string The database username
     * @param $password string The database password
     * @param $driver_options array The driver options
     */
    public function __construct($dsn, $username = '', $password = '', $driver_options = array())
    {
        parent::__construct($dsn, $username, $password, $driver_options);
        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('\Centreon\Internal\Db\Statement', array($this)));
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Execute a SQL query
     *
     * @see http://www.php.net/manual/en/pdo.query.php
     * @param $query string The SQL query
     * @return \Centreon\Db\Statement|bool Return false on failure
     */
    public function query($query, $fetchType = \PDO::FETCH_BOTH, $extraArgs1 = null, $extraArgs2 = null)
    {
        // @Todo emit event before
        if ($fetchType == \PDO::FETCH_COLUMN || $fetchType == \PDO::FETCH_INTO) {
            $stmt = parent::query($query, $fetchType, $extraArgs1);
        } elseif ($fetchType == \PDO::FETCH_CLASS) {
            $stmt = parent::query($query, $fetchType, $extraArgs1, $extraArgs2);
        } else {
            $stmt = parent::query($query);
        }
        // @Todo emit event after
        return $stmt;
    }

    /**
     * Execute a SQL query and return the number of rows affected
     *
     * @see http://www.php.net/manual/en/pdo.exec.php
     * @param $query string The SQL query
     * @return int
     */
    public function exec($query)
    {
        // @Todo emit event before
        $nbRows = parent::exec($query);
        // @Todo emit event after
        return $nbRows;
    }

    /**
     * Builds a limit clause
     *
     * @param string $sql
     * @param int $count
     * @param int $offset
     * @return string
     */
    public function limit($sql, $count, $offset)
    {
        return $sql . " LIMIT {$count} OFFSET {$offset}";
    }

    /**
     * Returns last inserted id
     *
     * @param string $table
     * @param string $primaryKey
     * @return int
     */
    public function lastInsertId($table, $primaryKey)
    {
        $sql = "SELECT MAX({$primaryKey}) AS last_id FROM {$table}";
        $stmt = $this->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['last_id'];
    }

    /**
     * Parse a DSN and return a array
     *
     * @param string $dsn The dsn
     * @param string $user The username
     * @param string $pass The password
     * @return array
     */
    public static function parseDsn($dsn, $user = '', $pass = '')
    {
        $convertKeys = array(
            'host' => 'db_host',
            'port' => 'db_port',
            'dbname' => 'db_name'
        );
        $defaultPorts = array(
            'mysql' => 3306,
            'pgsql' => 5432
        );
        $values = array(
            'db_user' => $user,
            'db_password' => $pass
        );
        list($type, $uri) = explode(':', $dsn, 2);
        if (is_null($uri)) {
            throw new Exception("Bad DSN format");
        }
        $values['db_type'] = $type;
        $information = explode(';', $uri);
        foreach ($information as $info) {
            list($infoType, $infoValue) = explode('=', $info, 2);
            $values[$convertKeys[$infoType]] = $infoValue;
        }
        if (isset($values['db_host']) && false === isset($values['db_port'])) {
            $values['db_port'] = $defaultPorts[$type];
        }
        return $values;
    }
}

<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
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

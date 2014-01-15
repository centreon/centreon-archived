<?php
/*
 * Copyright 2005-2014 MERETHIS
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

namespace Centreon\Core;

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
        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('\Centreon\Core\Db\Statement', array($this)));
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Execute a SQL query
     *
     * @see http://www.php.net/manual/en/pdo.query.php
     * @param $query string The SQL query
     * @return \Centreon\Core\Db\Statement|bool Return false on failure
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
}

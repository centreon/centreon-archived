<?php
/**
 * Copyright 2005-2019 Centreon
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
 *
 */
declare(strict_types=1);

namespace Centreon\Infrastructure;

use PDO;

/**
 * This class extend the PDO class and can be used to create a database
 * connection.
 * This class is used by all database repositories.
 *
 * @package Centreon\Infrastructure
 */
class DatabaseConnection extends \PDO
{

    /**
     * @var string Name of the configuration table
     */
    private $centreonDbName;

    /**
     * @var string Name of the storage table
     */
    private $storageDbName;

    /**
     * Initialize the PDO connection
     *
     * @param string $host
     * @param string $basename
     * @param string $login
     * @param string $password
     * @param int $port
     */
    public function __construct(string $host, string $basename, string $login, string $password, int $port = 3306)
    {
        $dsn = "mysql:dbname={$basename};host={$host};port={$port}";
        $options = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );
        parent::__construct($dsn, $login, $password, $options);
    }

    /**
     * @return string
     */
    public function getCentreonDbName()
    {
        return $this->centreonDbName;
    }

    /**
     * @param string $centreonDbName
     */
    public function setCentreonDbName(string $centreonDbName): void
    {
        $this->centreonDbName = $centreonDbName;
    }

    /**
     * @return string
     */
    public function getStorageDbName()
    {
        return $this->storageDbName;
    }

    /**
     * @param string $storageDbName
     */
    public function setStorageDbName(string $storageDbName)
    {
        $this->storageDbName = $storageDbName;
    }
}

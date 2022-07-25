<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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
declare(strict_types=1);

namespace Centreon\Infrastructure;

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
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
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

    /**
     * switch connection to another database
     *
     * @param string $dbName
     */
    public function switchToDb(string $dbName): void
    {
        $this->query('use ' . $dbName);
    }
}

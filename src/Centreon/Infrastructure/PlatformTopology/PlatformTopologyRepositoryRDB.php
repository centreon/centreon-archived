<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\PlatformTopology;

use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\PlatformTopology\PlatformTopology;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;

/**
 * This class is designed to manage the repository of the platform topology requests
 *
 * @package Centreon\Infrastructure\PlatformTopology
 */
class PlatformTopologyRepositoryRDB extends AbstractRepositoryDRB implements PlatformTopologyRepositoryInterface
{
    /**
     * PlatformTopologyRepositoryRDB constructor.
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     */
    public function addPlatformToTopology(PlatformTopology $platformTopology): void
    {
        $request = $this->translateDbName('
            INSERT INTO `:db`.platform_topology (`ip_address`, `hostname`, `server_type`, `parent`)
            VALUES (:serverAddress, :serverName, :serverType, :serverParent)
        ');

        $statement = $this->db->prepare($request);
        $statement->bindValue(':serverAddress', $platformTopology->getServerAddress(), \PDO::PARAM_STR);
        $statement->bindValue(':serverName', $platformTopology->getServerName(), \PDO::PARAM_STR);
        $statement->bindValue(':serverType', $platformTopology->getServerType(), \PDO::PARAM_INT);
        $statement->bindValue(':serverParent', $platformTopology->getServerParentAddress(), \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function findPlatformInTopology(
        string $serverAddress,
        string $serverName
    ): array {
        $request = $this->translateDbName(
            'SELECT `ip_address`, `hostname`, `server_type`
            FROM `:db`.platform_topology
            WHERE `ip_address` = :serverAddress OR `hostname` = :serverName'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':serverAddress', $serverAddress, \PDO::PARAM_STR);
        $statement->bindValue(':serverName', $serverName, \PDO::PARAM_STR);
        $statement->execute();

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        // if nothing is found, convert $result to an empty array as expected
        return (is_array($result) ? $result : []);
    }
}

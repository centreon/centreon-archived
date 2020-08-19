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

use Centreon\Domain\Entity\EntityCreator;
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
     * @inheritDoc
     */
    public function addPlatformToTopology(PlatformTopology $platformTopology): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                INSERT INTO `:db`.platform_topology (`address`, `name`, `type`, `parent_id`)
                VALUES (:address, :name, :type, :parentId)
            ')
        );
        $statement->bindValue(':address', $platformTopology->getAddress(), \PDO::PARAM_STR);
        $statement->bindValue(':name', $platformTopology->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':type', strtolower($platformTopology->getType()), \PDO::PARAM_STR);
        $statement->bindValue(':parentId', $platformTopology->getParentId(), \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * @inheritDoc
     */
    public function isPlatformAlreadyRegisteredInTopology(string $address, string $name): bool
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT `address`, `name`, `type`
                FROM `:db`.platform_topology
                WHERE `address` = :address OR `name` = :name
            ')
        );
        $statement->bindValue(':address', $address, \PDO::PARAM_STR);
        $statement->bindValue(':name', $name, \PDO::PARAM_STR);
        $statement->execute();

        return (!empty($statement->fetch(\PDO::FETCH_ASSOC)));
    }

    /**
     * @inheritDoc
     */
    public function findPlatformTopologyByAddress(string $serverAddress): ?PlatformTopology
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT platform_topology.id
                FROM `:db`.platform_topology
                WHERE `address` = :address
            ')
        );
        $statement->bindValue(':address', $serverAddress, \PDO::PARAM_STR);
        $statement->execute();

        $platformTopology = null;

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var PlatformTopology $platformTopology
             */
            $platformTopology = EntityCreator::createEntityByArray(
                PlatformTopology::class,
                $result
            );
        }

        return $platformTopology;
    }

    /**
     * @inheritDoc
     */
    public function findPlatformTopologyByType(string $serverType): ?PlatformTopology
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT `address`, `name`
                FROM `:db`.platfrom_topology
                WHERE `type` = :type
            ')
        );
        $statement->bindValue(':type', $serverType, \PDO::PARAM_STR);
        $statement->execute();

        $platformTopology = null;

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var PlatformTopology $platformTopology
             */
            $platformTopology = EntityCreator::createEntityByArray(
                PlatformTopology::class,
                $result
            );
        }

        return $platformTopology;
    }
}

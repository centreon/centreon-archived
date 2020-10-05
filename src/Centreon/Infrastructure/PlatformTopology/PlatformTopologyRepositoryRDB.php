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
                INSERT INTO `:db`.platform_topology (`address`, `name`, `type`, `parent_id`, `server_id`)
                VALUES (:address, :name, :type, :parentId, :serverId)
            ')
        );
        $statement->bindValue(':address', $platformTopology->getAddress(), \PDO::PARAM_STR);
        $statement->bindValue(':name', $platformTopology->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':type', $platformTopology->getType(), \PDO::PARAM_STR);
        $statement->bindValue(':parentId', $platformTopology->getParentId(), \PDO::PARAM_INT);
        $statement->bindValue(':serverId', $platformTopology->getServerId(), \PDO::PARAM_INT);
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
                SELECT * FROM `:db`.platform_topology
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
                FROM `:db`.platform_topology
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

    /**
     * @inheritDoc
     */
    public function findMonitoringIdFromName(string $serverName, bool $localhost): ?PlatformTopology
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT `id`
                FROM `:db`.nagios_server
                WHERE `localhost` = :state AND ns_activate = \'1\' AND `name` = :name
            ')
        );
        $statement->bindValue(':name', $serverName, \PDO::PARAM_STR);
        $statement->bindValue(':state', true === $localhost ? '1' : '0', \PDO::PARAM_STR);
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
    public function getPlatformCompleteTopology(): ?array
    {
        $statement = $this->db->query('SELECT * FROM `platform_topology`');
        $platformCompleteTopology = [];
        foreach ($statement as $topology) {
            $platformTopology = EntityCreator::createEntityByArray(PlatformTopology::class, $topology);
            $platformCompleteTopology[] = $platformTopology;
        }
        if (!empty($platformCompleteTopology)) {
            return $platformCompleteTopology;
        }
        return null;
    }

    public function findPlatformAddressById(int $serverId): ?string
    {
        $statement = $this->db->prepare('SELECT `address` FROM `platform_topology` WHERE id = :serverId');
        $statement->bindValue(':serverId', $serverId, \PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            return $result['address'];
        }
        return null;
    }

    public function findPlatformOnePeerRetentionMode(int $serverId): ?string
    {
        $statement = $this->db->prepare("
            SELECT config_value
            FROM cfg_centreonbroker_info cfgbi
            INNER JOIN cfg_centreonbroker AS cfgb
                ON cfgbi.config_id = cfgb.config_id
            INNER JOIN nagios_server AS ns
                ON cfgb.ns_nagios_server = ns.id
                AND ns.id = :serverId
            WHERE cfgbi.config_group = 'output'
            AND config_key = 'one_peer_retention_mode'
        ");
        $statement->bindValue(':serverId', $serverId, \PDO::PARAM_INT);
        $statement->execute();
        while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            return $result['config_value'];
        }
        return null;
    }
}

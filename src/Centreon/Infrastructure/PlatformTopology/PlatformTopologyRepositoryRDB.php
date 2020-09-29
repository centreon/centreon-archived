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
use Centreon\Domain\PlatformTopology\PlatformTopologyException;
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
    public function findPlatformInformation(): ?PlatformTopology
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT * FROM `:db`.informations
            ')
        );
        $result = [];
        $platformTopology = null;
        if ($statement->execute()) {
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $result[$row['key']] = $row['value'];
            }

            if (!empty($result)) {
                $platformTopology = new PlatformTopology();
                $platformTopology
                    ->setIsRemote('yes' === $result['isRemote'])
                    ->setAuthorizedMaster($result['authorizedMaster'] ?? null)
                    ->setApiUsername($result['apiUsername'] ?? null)
                    ->setApiCredentials($result['apiCredentials'] ?? null)
                    ->setApiScheme($result['apiScheme'] ?? null)
                    ->setApiPort(isset($result['apiPort']) ? (int) $result['apiPort'] : null)
                    ->setApiPath($result['apiPath'] ?? null)
                    ->setApiPeerValidationActivated('yes' === $result['apiPeerValidation']);
            }
        }

        return $platformTopology;
    }

    /**
     * @inheritDoc
     */
    public function findPlatformProxy(): ?PlatformTopology
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT * FROM `:db`.options WHERE `key` LIKE "%proxy%"
            ')
        );
        $result = [];
        $platformTopology = null;
        if ($statement->execute()) {
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $result[$row['key']] = $row['value'];
            }

            if (!empty($result)) {
                $platformTopology = new PlatformTopology();
                $platformTopology
                    ->setProxyUrl($result['proxy_url'] ?? null)
                    ->setProxyPort(isset($result['proxy_port']) ? (int) $result['proxy_port'] : null)
                    ->setProxyUsername($result['proxy_user'] ?? null)
                    ->setProxyCredentials($result['proxy_password'] ?? null);
            }
        }

        return $platformTopology;
    }
}

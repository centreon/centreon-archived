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
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\PlatformTopology\Platform;
use Centreon\Domain\Repository\RepositoryException;
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
    public function addPlatformToTopology(Platform $platform): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                INSERT INTO `:db`.platform_topology (`address`, `name`, `type`, `parent_id`, `server_id`, `hostname`)
                VALUES (:address, :name, :type, :parentId, :serverId, :hostname)
            ')
        );
        $statement->bindValue(':address', $platform->getAddress(), \PDO::PARAM_STR);
        $statement->bindValue(':name', $platform->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':type', $platform->getType(), \PDO::PARAM_STR);
        $statement->bindValue(':parentId', $platform->getParentId(), \PDO::PARAM_INT);
        $statement->bindValue(':serverId', $platform->getServerId(), \PDO::PARAM_INT);
        $statement->bindValue(':hostname', $platform->getHostname(), \PDO::PARAM_STR);
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
                WHERE `address` = :address OR `name` = :name collate utf8_bin
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
    public function findPlatformByAddress(string $serverAddress): ?Platform
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT * FROM `:db`.platform_topology
                WHERE `address` = :address
            ')
        );
        $statement->bindValue(':address', $serverAddress, \PDO::PARAM_STR);
        $statement->execute();

        $platform = null;

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var Platform $platform
             */
            $platform = EntityCreator::createEntityByArray(
                Platform::class,
                $result
            );
        }

        return $platform;
    }

    /**
     * @inheritDoc
     */
    public function findPlatformByType(string $serverType): ?Platform
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT * FROM `:db`.platform_topology
                WHERE `type` = :type AND `parent_id` IS NULL
            ')
        );
        $statement->bindValue(':type', $serverType, \PDO::PARAM_STR);
        $statement->execute();

        $platform = null;

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var Platform $platform
             */
            $platform = EntityCreator::createEntityByArray(
                Platform::class,
                $result
            );
        }

        return $platform;
    }

    /**
     * @inheritDoc
     */
    public function findLocalMonitoringIdFromName(string $serverName): ?Platform
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT `id`
                FROM `:db`.nagios_server
                WHERE `localhost` = \'1\' AND ns_activate = \'1\' AND `name` = :name collate utf8_bin
            ')
        );
        $statement->bindValue(':name', $serverName, \PDO::PARAM_STR);
        $statement->execute();

        $platform = null;

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var Platform $platform
             */
            $platform = EntityCreator::createEntityByArray(
                Platform::class,
                $result
            );
        }

        return $platform;
    }

    /**
     * @inheritDoc
     */
    public function getPlatformTopology(): array
    {
        $statement = $this->db->query('SELECT * FROM `platform_topology`');

        $platformTopology = [];
        if ($statement !== false) {
            foreach ($statement as $topology) {
                /**
                 * @var Platform $platform
                 */
                $platform = EntityCreator::createEntityByArray(Platform::class, $topology);
                $platformTopology[] = $platform;
            }
        }

        return $platformTopology;
    }

    /**
     * @inheritDoc
     */
    public function findPlatform(int $serverId): ?Platform
    {
        $statement = $this->db->prepare('SELECT * FROM `platform_topology` WHERE id = :serverId');
        $statement->bindValue(':serverId', $serverId, \PDO::PARAM_INT);
        $statement->execute();

        $platform = null;
        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $platform = EntityCreator::createEntityByArray(Platform::class, $result);
        }

        return $platform;
    }

    /**
     * @inheritDoc
     */
    public function deletePlatform(int $serverId): void
    {
        try {
            /**
             * Search for children Platform
             */
            $childrenPlatforms = $this->findChildrenPlatforms($serverId);
            if(!empty($childrenPlatforms)){
                /**
                 * If children platform are found, look for a Central to link the children platform
                 */
                $topLevelPlatform = $this->findTopLevelPlatform();
                if ($topLevelPlatform === null) {
                    throw new EntityNotFoundException(_('No top level Platform found to link the children platform.'));
                }

                $statementChangeParentId = $this->db->prepare(
                    'UPDATE `platform_topology` SET parent_id = :centralId WHERE id = :platformId'
                );
                $statementChangeParentId->bindValue(':centralId', $topLevelPlatform->getId(), \PDO::PARAM_INT);

                foreach ($childrenPlatforms as $platform) {
                    $statementChangeParentId->bindValue(':platformId', $platform->getId(), \PDO::PARAM_INT);
                    $statementChangeParentId->execute();
                }
            }

            /**
             * Then safely delete the platform without removing its children
             */
            $statement = $this->db->prepare('DELETE FROM `platform_topology` WHERE id = :serverId');
            $statement->bindValue(':serverId', $serverId, \PDO::PARAM_INT);
            $statement->execute();
        } catch (EntityNotFoundException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new RepositoryException(_('An error occured while deleting the Platform.'));
        }
    }

    /**
     * @inheritDoc
     */
    private function findTopLevelPlatform(): ?Platform
    {
        $statement = $this->db->prepare(
            $this->translateDbName('
                SELECT * FROM `:db`.platform_topology
                WHERE `parent_id` IS NULL
            ')
        );
        $statement->execute();

        $platform = null;

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var Platform $platform
             */
            $platform = EntityCreator::createEntityByArray(
                Platform::class,
                $result
            );
        }

        return $platform;
    }
    /**
     * Find the children Platforms of another Platform
     *
     * @param integer $serverId
     * @return Platform[]
     */
    private function findChildrenPlatforms(int $serverId): array
    {
            $statement = $this->db->prepare('SELECT * FROM `platform_topology` WHERE parent_id = :parentId');
            $statement->bindValue(':parentId', $serverId, \PDO::PARAM_INT);
            $statement->execute();

            $childrenPlatforms = [];
            if ($result = $statement->fetchAll(\PDO::FETCH_ASSOC)) {
                foreach($result as $platform) {
                    /**
                     * @var Platform[] $childrenPlatforms
                     */
                    $childrenPlatforms[] = EntityCreator::createEntityByArray(Platform::class, $platform);
                }
            }

            return $childrenPlatforms;
    }
}

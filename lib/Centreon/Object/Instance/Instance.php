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

use Centreon\Domain\PlatformTopology\Interfaces\PlatformInterface;
use Centreon\Domain\PlatformTopology\Model\PlatformRegistered;
use Centreon\Infrastructure\PlatformTopology\Repository\Model\PlatformTopologyFactoryRDB;

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Instances (pollers)
 *
 * @author sylvestre
 */
class Centreon_Object_Instance extends Centreon_Object
{
    protected $table = "nagios_server";
    protected $primaryKey = "id";
    protected $uniqueLabelField = "name";

    public function getDefaultInstance()
    {
        $res = $this->db->query("SELECT `name` FROM `nagios_server` WHERE `is_default` = 1");
        if ($res->rowCount() == 0) {
            $res = $this->db->query("SELECT `name` FROM `nagios_server` WHERE `localhost` = '1'");
        }

        $row = $res->fetch();
        return $row['name'];
    }

    /**
     * Insert platform in nagios_server and platform_topology tables.
     *
     * @param array<string,mixed> $params
     * @return void
     */
    public function insert($params = [])
    {
        if (!array_key_exists('ns_ip_address', $params) || !array_key_exists('name', $params)) {
            throw new \InvalidArgumentException('Missing parameters');
        }
        $platformTopology = $this->findPlatformTopologyByAddress($params['ns_ip_address']);
        $serverId = null;

        $isAlreadyInTransaction = $this->db->inTransaction();
        if (!$isAlreadyInTransaction) {
            $this->db->beginTransaction();
        }
        if ($platformTopology !== null) {
            if ($platformTopology->isPending() === false) {
                throw new \Exception('Platform already created');
            }

            /**
             * Check if the parent is a registered remote.
             */
            $parentPlatform = $this->findPlatformTopology($platformTopology->getParentId());
            if ($parentPlatform !== null && $parentPlatform->getType() === PlatformRegistered::TYPE_REMOTE) {
                if ($parentPlatform->getServerId() === null) {
                    throw new \Exception("Parent remote server isn't registered");
                }
                $params['remote_id'] = $parentPlatform->getServerId();
            }

            try {
                $serverId = parent::insert($params);
                $platformTopology->setPending(false);
                $platformTopology->setServerId($serverId);
                $this->updatePlatformTopology($platformTopology);
                if (!$isAlreadyInTransaction) {
                    $this->db->commit();
                }
            } catch (\Exception $ex) {
                if (!$isAlreadyInTransaction) {
                    $this->db->rollBack();
                }
                throw new \Exception('Unable to update platform', 0, $ex);
            }
        } else {
            try {
                $serverId = parent::insert($params);
                $params['server_id'] = $serverId;
                $this->insertIntoPlatformTopology($params);
                if (!$isAlreadyInTransaction) {
                    $this->db->commit();
                }
            } catch (\Exception $ex) {
                if (!$isAlreadyInTransaction) {
                    $this->db->rollBack();
                }
                throw new \Exception('Unable to create platform', 0, $ex);
            }
        }

        return $serverId;
    }

    /**
     * Find existing platform by id.
     *
     * @param integer $id
     * @return PlatformInterface|null
     */
    private function findPlatformTopology(int $id): ?PlatformInterface
    {
        $statement = $this->db->prepare("SELECT * FROM platform_topology WHERE id=:id");
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();
        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return PlatformTopologyFactoryRDB::create($result);
        }
        return null;
    }

    /**
     * Find existing platform by address.
     *
     * @param string $address
     * @return PlatformInterface|null
     */
    private function findPlatformTopologyByAddress(string $address): ?PlatformInterface
    {
        $statement = $this->db->prepare("SELECT * FROM platform_topology WHERE address=:address");
        $statement->bindValue(':address', $address, \PDO::PARAM_STR);
        $statement->execute();
        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return PlatformTopologyFactoryRDB::create($result);
        }
        return null;
    }

    /**
     * Update a platform topology.
     *
     * @param PlatformInterface $platformTopology
     */
    private function updatePlatformTopology(PlatformInterface $platformTopology): void
    {
        $statement = $this->db->prepare(
            "UPDATE platform_topology SET pending=:isPending, server_id=:serverId WHERE address=:address"
        );
        $statement->bindValue(':isPending', $platformTopology->isPending() ? '1' : '0', \PDO::PARAM_STR);
        $statement->bindValue(':serverId', $platformTopology->getServerId(), \PDO::PARAM_INT);
        $statement->bindValue(':address', $platformTopology->getAddress(), \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * Insert the poller in platform_topology.
     *
     * @param array<string,mixed> $params
     */
    private function insertIntoPlatformTopology(array $params): void
    {
        $centralPlatformTopologyId = $this->findCentralPlatformTopologyId();
        if ($centralPlatformTopologyId === null) {
            throw new \Exception('No Central found in topology');
        }
        $statement = $this->db->prepare(
            "INSERT INTO platform_topology (address, name, type, pending, parent_id, server_id) " .
            "VALUES (:address, :name, 'poller', '0', :parentId, :serverId)"
        );
        $statement->bindValue(':address', $params['ns_ip_address'], \PDO::PARAM_STR);
        $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
        $statement->bindValue(':parentId', $centralPlatformTopologyId, \PDO::PARAM_INT);
        $statement->bindValue(':serverId', $params['server_id'], \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Find the Central Id in platform_topology.
     *
     * @return integer|null
     */
    private function findCentralPlatformTopologyId(): ?int
    {
        $result = $this->db->query("SELECT id from platform_topology WHERE type ='central'");
        if ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            return (int) $row['id'];
        }
        return null;
    }
}

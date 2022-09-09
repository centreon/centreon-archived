<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\RealTime\Repository\Hostgroup;

use Core\Domain\RealTime\Model\Hostgroup;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\RealTime\Repository\ReadHostgroupRepositoryInterface;

class DbReadHostgroupRepository extends AbstractRepositoryDRB implements ReadHostgroupRepositoryInterface
{
    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function findAllByHostId(int $hostId): array
    {
        return $this->findAll($hostId, null);
    }

    /**
     * @inheritDoc
     */
    public function findAllByHostIdAndAccessGroupIds(int $hostId, array $accessGroupIds): array
    {
        $hostgroups = [];

        if (empty($accessGroupIds)) {
            return $hostgroups;
        }

        $aclRequest = ' INNER JOIN `:dbstg`.`centreon_acl` AS acl
            ON acl.host_id = hhg.host_id
            AND acl.service_id IS NULL
            AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';

        return $this->findAll($hostId, $aclRequest);
    }

    /**
     * @param int $hostId
     * @param string|null $aclRequest
     * @return Hostgroup[]
     */
    private function findAll(int $hostId, ?string $aclRequest): array
    {
        $request = "SELECT DISTINCT
                hg.hostgroup_id,
                hg.name AS `hostgroup_name`
            FROM `:dbstg`.`hosts_hostgroups` AS hhg
            INNER JOIN `:dbstg`.`hostgroups` AS hg ON hg.hostgroup_id = hhg.hostgroup_id";

        if ($aclRequest !== null) {
            $request .= $aclRequest;
        }

        $request .= ' WHERE hhg.host_id = :hostId ORDER BY hg.name ASC';

        $statement = $this->db->prepare($this->translateDbName($request));
        $statement->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $statement->execute();

        $hostgroups = [];

        while (($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostgroups[] = DbHostgroupFactory::createFromRecord($row);
        }

        return $hostgroups;
    }
}

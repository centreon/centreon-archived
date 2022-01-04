<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\RealTime\Repository\Servicegroup;

use Core\Domain\RealTime\Model\Servicegroup;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\RealTime\Repository\ReadServicegroupRepositoryInterface;

class DbReadServicegroupRepository extends AbstractRepositoryDRB implements ReadServicegroupRepositoryInterface
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
    public function findAllByHostIdAndServiceId(int $hostId, int $serviceId): array
    {
        return $this->findServicegroups($hostId, $serviceId);
    }

    /**
     * @inheritDoc
     */
    public function findAllByHostIdAndServiceIdAndAccessGroupIds(
        int $hostId,
        int $serviceId,
        array $accessGroupIds
    ): array {
        $servicegroups = [];

        if (empty($accessGroupIds)) {
            return $servicegroups;
        }

        $aclRequest = ' INNER JOIN `:dbstg`.`centreon_acl` AS acl
            ON acl.host_id = ssg.host_id
            AND acl.service_id = ssg.service_id
            AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';

        return $this->findServicegroups($hostId, $serviceId, $aclRequest);
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     * @param string|null $aclRequest
     * @return Servicegroup[]
     */
    private function findServicegroups(int $hostId, int $serviceId, ?string $aclRequest = null): array
    {
        $request = "SELECT DISTINCT sg.servicegroup_id, sg.name AS `servicegroup_name`
            FROM `:dbstg`.`services_servicegroups` AS ssg
            INNER JOIN `:dbstg`.`servicegroups` AS sg ON sg.servicegroup_id = ssg.servicegroup_id";

        if ($aclRequest !== null) {
            $request .= $aclRequest;
        }

        $request .= ' WHERE ssg.host_id = :hostId AND ssg.service_id = :serviceId ORDER BY sg.name ASC';

        $statement = $this->db->prepare($this->translateDbName($request));
        $statement->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
        $statement->execute();

        $servicegroups = [];

        while (($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $servicegroups[] = DbServicegroupFactory::createFromRecord($row);
        }

        return $servicegroups;
    }
}

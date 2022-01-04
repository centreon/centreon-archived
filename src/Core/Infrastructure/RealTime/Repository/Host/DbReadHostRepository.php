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

namespace Core\Infrastructure\RealTime\Repository\Host;

use Core\Domain\RealTime\Model\Host;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;

class DbReadHostRepository extends AbstractRepositoryDRB implements ReadHostRepositoryInterface
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
    public function findHostById(int $hostId): ?Host
    {
        return $this->findHost($hostId);
    }

    /**
     * @inheritDoc
     */
    public function findHostByIdAndAccessGroupIds(int $hostId, array $accessGroupIds): ?Host
    {
        if (empty($accessGroupIds)) {
            return null;
        }

        $accessGroupRequest = ' INNER JOIN `:dbstg`.`centreon_acl` AS host_acl
            ON host_acl.host_id = h.host_id
            AND host_acl.service_id IS NULL
            AND host_acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';

        return $this->findHost($hostId, $accessGroupRequest);
    }

    /**
     * Find host request according to accessgroups or not.
     *
     * @param int $hostId
     * @param string|null $accessGroupRequest
     * @return Host|null
     */
    private function findHost(int $hostId, ?string $accessGroupRequest = null): ?Host
    {
        $request = "SELECT
                h.host_id,
                h.name,
                h.address,
                h.output,
                h.alias,
                h.timezone,
                h.flapping,
                h.scheduled_downtime_depth AS `in_downtime`,
                h.acknowledged,
                i.name AS `monitoring_server_name`,
                h.state AS `status_code`,
                h.perfData AS `performance_data`,
                h.output,
                h.command_line,
                h.notification_number,
                h.last_state_change AS `last_status_change`,
                h.last_notification,
                h.latency,
                h.execution_time,
                h.percent_state_change AS `status_change_percentage`,
                h.next_check,
                h.last_check,
                h.passive_checks,
                h.active_checks,
                h.last_time_up,
                host_cvl.value AS `severity_level`,
                h.icon_image_alt AS `icon_name`,
                h.icon_image AS `icon_url`,
                h.check_attempt,
                h.max_check_attempts,
                h.state_type
            FROM `:dbstg`.`hosts` AS h
            INNER JOIN `:dbstg`.`instances` AS i ON i.instance_id = h.instance_id
            LEFT JOIN `:dbstg`.`customvariables` AS host_cvl ON host_cvl.host_id = h.host_id
                AND host_cvl.service_id = 0
                AND host_cvl.name = 'CRITICALITY_LEVEL'" .
            ($accessGroupRequest !== null ? $accessGroupRequest : '') .
            "WHERE  h.host_id = :host_id AND h.enabled = '1' AND h.name NOT LIKE '\_Module_BAM%'";

        $statement = $this->db->prepare($this->translateDbName($request));

        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);

        $statement->execute();

        if (($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            return DbHostFactory::createFromRecord($row);
        }

        return null;
    }
}

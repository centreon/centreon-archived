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

namespace Core\Infrastructure\RealTime\Repository\MetaService;

use Core\Domain\RealTime\Model\MetaService;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\RealTime\Repository\ReadMetaServiceRepositoryInterface;

class DbReadMetaServiceRepository extends AbstractRepositoryDRB implements ReadMetaServiceRepositoryInterface
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
    public function findMetaServiceById(int $metaId): ?MetaService
    {
        return $this->findMetaService($metaId);
    }

    /**
     * @inheritDoc
     */
    public function findMetaServiceByIdAndAccessGroupIds(int $metaId, array $accessGroupIds): ?MetaService
    {
        if (empty($accessGroupIds)) {
            return null;
        }

        $accessGroupRequest = ' INNER JOIN `:dbstg`.`centreon_acl` AS service_acl
            ON service_acl.service_id = s.service_id AND service_acl.host_id = s.host_id
            AND service_acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';

        return $this->findMetaService($metaId, $accessGroupRequest);
    }

    /**
     * @param int $metaId
     * @param string|null $accessGroupRequest
     * @return MetaService|null
     */
    private function findMetaService(int $metaId, ?string $accessGroupRequest = null): ?MetaService
    {
        $request = "SELECT
            SUBSTRING(s.description, 6) AS `id`,
            s.host_id,
            s.service_id,
            s.display_name AS `name`,
            s.state AS `status_code`,
            s.state_type,
            s.output,
            s.flapping,
            s.scheduled_downtime_depth AS `in_downtime`,
            s.acknowledged,
            s.perfData AS `performance_data`,
            s.output,
            s.command_line,
            s.notification_number,
            s.notify,
            s.last_state_change AS `last_status_change`,
            s.last_notification,
            s.latency,
            s.execution_time,
            s.percent_state_change AS `status_change_percentage`,
            s.next_check,
            s.last_check,
            s.passive_checks,
            s.active_checks,
            s.last_time_ok,
            s.check_attempt,
            s.max_check_attempts,
            i.name AS `monitoring_server_name`,
            CASE
                WHEN EXISTS(
                    SELECT i.host_id, i.service_id
                    FROM `:dbstg`.metrics AS m, `:dbstg`.index_data AS i
                    WHERE i.host_id = s.host_id AND i.service_id = s.service_id
                        AND i.id = m.index_id AND m.hidden = \"0\") THEN 1
                ELSE 0
            END AS `has_graph_data`
        FROM `:dbstg`.`services` AS s " . ($accessGroupRequest !== null ? $accessGroupRequest : '') .
        " INNER JOIN `:dbstg`.`hosts` sh ON sh.host_id = s.host_id" .
        " INNER JOIN `:dbstg`.`instances` AS i ON i.instance_id = sh.instance_id" .
        " WHERE s.description = :meta_id AND s.enabled = '1'";

        $statement = $this->db->prepare($this->translateDbName($request));
        $statement->bindValue(':meta_id', 'meta_' . $metaId, \PDO::PARAM_STR);
        $statement->execute();

        if ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /** @var array<string,int|string|null> $row */
            return DbMetaServiceFactory::createFromRecord($row);
        }

        return null;
    }
}

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

namespace Centreon\Infrastructure\Monitoring\Resource\Provider;

use Centreon\Infrastructure\Monitoring\Resource\Provider\Provider;
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;

final class MetaServiceProvider extends Provider
{
    /**
     * @inheritDoc
     */
    public function shouldBeSearched(ResourceFilter $filter): bool
    {
        if (
            $this->hasOnlyHostSearch()
            || $this->hasOnlyServiceSearch()
            || ($filter->getTypes() && !$filter->hasType(ResourceFilter::TYPE_META))
            || ($filter->getStatuses() && !ResourceFilter::map(
                $filter->getStatuses(),
                ResourceFilter::MAP_STATUS_SERVICE
            ))
            || $filter->getHostgroupIds()
            || $filter->getServicegroupIds()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function prepareSubQueryWithoutAcl(ResourceFilter $filter, StatementCollector $collector): string
    {
        return $this->prepareSubQuery($filter, $collector, null);
    }

    /**
     * @inheritDoc
     */
    public function prepareSubQueryWithAcl(
        ResourceFilter $filter,
        StatementCollector $collector,
        array $accessGroupIds
    ): string {
        $aclSubQuery = ' EXISTS (
            SELECT 1 FROM `:dbstg`.`centreon_acl` AS service_acl
            WHERE service_acl.host_id = s.host_id
                AND service_acl.service_id = s.service_id
                AND service_acl.group_id IN (' . implode(',', $accessGroupIds) . ')
            LIMIT 1) ';

        return $this->prepareSubQuery($filter, $collector, $aclSubQuery);
    }

    /**
     * Prepare SQL query
     *
     * @param ResourceFilter $filter
     * @param StatementCollector $collector
     * @param string|null $aclSubQuery
     * @return string
     */
    private function prepareSubQuery(
        ResourceFilter $filter,
        StatementCollector $collector,
        ?string $aclSubQuery
    ): string {
        $sql = "SELECT
            SUBSTRING(s.description, 6) AS `id`,
            'metaservice' AS `type`,
            s.display_name AS `name`,
            NULL AS `alias`,
            NULL AS `fqdn`,
            sh.host_id AS `host_id`,
            s.service_id AS `service_id`,
            NULL AS `icon_name`,
            NULL AS `icon_url`,
            NULL AS `action_url`,
            NULL AS `notes_url`,
            NULL AS `notes_label`,
            NULL AS `monitoring_server_name`,
            NULL AS `monitoring_server_id`,
            s.command_line AS `command_line`,
            NULL AS `timezone`,
            NULL AS `parent_id`,
            NULL AS `parent_name`,
            NULL AS `parent_alias`,
            NULL AS `parent_fqdn`,
            NULL AS `parent_type`,
            NULL AS `parent_icon_name`,
            NULL AS `parent_icon_url`,
            NULL AS `parent_status_code`,
            NULL AS `parent_status_name`,
            NULL AS `parent_status_severity_code`,
            s.state AS `status_code`,
            CASE
                WHEN s.state = 0 THEN 'OK'
                WHEN s.state = 1 THEN 'WARNING'
                WHEN s.state = 2 THEN 'CRITICAL'
                WHEN s.state = 3 THEN 'UNKNOWN'
                WHEN s.state = 4 THEN 'PENDING'
            END AS `status_name`,
            CASE
                WHEN s.state = 0 THEN " . ResourceStatus::SEVERITY_OK . "
                WHEN s.state = 1 THEN " . ResourceStatus::SEVERITY_MEDIUM . "
                WHEN s.state = 2 THEN " . ResourceStatus::SEVERITY_HIGH . "
                WHEN s.state = 3 THEN " . ResourceStatus::SEVERITY_LOW . "
                WHEN s.state = 4 THEN " . ResourceStatus::SEVERITY_PENDING . "
            END AS `status_severity_code`,
            s.flapping AS `flapping`,
            s.percent_state_change AS `percent_state_change`,
            s.scheduled_downtime_depth AS `in_downtime`,
            s.acknowledged AS `acknowledged`,
            1 AS `active_checks`,
            1 AS `passive_checks`,
            NULL AS `severity_level`,
            s.last_state_change AS `last_status_change`,
            s.last_notification AS `last_notification`,
            s.notification_number AS `notification_number`,
            CONCAT(s.check_attempt, '/', s.max_check_attempts, ' (', CASE
                WHEN s.state_type = 1 THEN 'H'
                WHEN s.state_type = 0 THEN 'S'
            END, ')') AS `tries`,
            s.last_check AS `last_check`,
            s.next_check AS `next_check`,
            s.output AS `information`,
            s.perfdata AS `performance_data`,
            s.execution_time AS `execution_time`,
            s.latency AS `latency`,
            s.notify AS `notification_enabled`
            FROM `:dbstg`.`services` AS s
            INNER JOIN `:dbstg`.`hosts` sh
            ON sh.host_id = s.host_id
            AND sh.name LIKE '\_Module\_Meta%'
            AND sh.enabled = 1";

        // show active services only
        $sql .= ' WHERE s.enabled = 1 ';


        // set ACL limitations
        if ($aclSubQuery !== null) {
            $sql .= ' AND ' . $aclSubQuery;
        }

        // apply the state filter to SQL query
        if ($filter->getStates() && !$filter->hasState(ResourceServiceInterface::STATE_ALL)) {
            $sqlState = [];
            $sqlStateCatalog = [
                ResourceServiceInterface::STATE_UNHANDLED_PROBLEMS => "(s.state_type = '1'"
                    . " AND s.acknowledged = 0"
                    . " AND s.scheduled_downtime_depth = 0"
                    . " AND sh.acknowledged = 0"
                    . " AND sh.scheduled_downtime_depth = 0"
                    . " AND s.state != 0"
                    . " AND s.state != 4)",
                ResourceServiceInterface::STATE_RESOURCES_PROBLEMS => '(s.state != 0 AND s.state != 4)',
                ResourceServiceInterface::STATE_IN_DOWNTIME => '(s.scheduled_downtime_depth = 1'
                    . ' OR sh.scheduled_downtime_depth = 1)',
                ResourceServiceInterface::STATE_ACKNOWLEDGED => '(s.acknowledged = 1 OR sh.acknowledged = 1)',
            ];

            foreach ($filter->getStates() as $state) {
                $sqlState[] = $sqlStateCatalog[$state];
            }

            $sql .= ' AND (' . implode(' OR ', $sqlState) . ')';
        }

        // apply the status filter to SQL query
        $statuses = ResourceFilter::map($filter->getStatuses(), ResourceFilter::MAP_STATUS_SERVICE);
        if ($statuses) {
            $statusList = [];

            foreach ($statuses as $index => $status) {
                $key = ":serviceStatuses_{$index}";

                $statusList[] = $key;
                $collector->addValue($key, $status, \PDO::PARAM_INT);
            }

            $sql .= ' AND s.state IN (' . implode(', ', $statusList) . ')';
        }

        if (!empty($filter->getMetaServiceIds())) {
            $metaServiceIds = [];

            foreach ($filter->getMetaServiceIds() as $index => $metaServiceId) {
                $key = ":metaServiceId_{$index}";

                $metaServiceIds[] = $key;
                $collector->addValue($key, 'meta_' . $metaServiceId, \PDO::PARAM_STR);
            }

            $sql .= ' AND s.description IN (' . implode(', ', $metaServiceIds) . ')';
        }

        return $sql;
    }

    /**
     * @inheritDoc
     */
    public function excludeResourcesWithoutMetrics(array $resources): array
    {
        $filteredResources = [];
        $collector = new StatementCollector();
        $where = [];
        $metaServiceResources = [];

        foreach ($resources as $key => $resource) {
            if ($resource->getType() === Resource::TYPE_META) {
                $where[] = "(s.description = :service_description_{$key})";
                $collector->addValue(":service_description_{$key}", 'meta_' . $resource->getId(), \PDO::PARAM_STR);
                $metaServiceResources[] = $resource;
            } else {
                $filteredResources[] = $resource;
            }
        }

        if (empty($metaServiceResources)) {
            return $filteredResources;
        }

        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT i.host_id, i.service_id, s.description
                FROM `:dbstg`.metrics AS m, `:dbstg`.index_data AS i, `:dbstg`.services AS s
                WHERE (' . implode(' OR ', $where) . ')
                AND i.id = m.index_id
                AND i.service_id = s.service_id
                AND m.hidden = "0"
                GROUP BY host_id, service_id'
            )
        );
        $collector->bind($statement);
        $statement->execute();

        while ($row = $statement->fetch()) {
            foreach ($metaServiceResources as $metaServiceResource) {
                if ($metaServiceResource->getId() === (int)substr($row['description'], 5)) {
                    $filteredResources[] = $metaServiceResource;
                }
            }
        }

        return $filteredResources;
    }
}

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

namespace Centreon\Infrastructure\Monitoring\MonitoringResource\Repository\Provider;

use Centreon\Infrastructure\Monitoring\MonitoringResource\Repository\Provider\Provider;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\MonitoringResource\Interfaces\MonitoringResourceServiceInterface;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;

final class ServiceProvider extends Provider
{
    public const TYPE = 'service';

    public const AVAILABLE_STATUSES = [
        ResourceFilter::STATUS_OK => 0,
        ResourceFilter::STATUS_WARNING => 1,
        ResourceFilter::STATUS_CRITICAL => 2,
        ResourceFilter::STATUS_UNKNOWN => 3,
        ResourceFilter::STATUS_PENDING => 4,
    ];

    /**
     * @inheritDoc
     */
    public function getAvailableStatuses(): array
    {
        return self::AVAILABLE_STATUSES;
    }

    /**
     * @inheritDoc
     */
    public function shouldBeSearched(ResourceFilter $filter): bool
    {
        if ($filter->getTypes() && !$filter->hasType(self::TYPE)) {
            return false;
        }

        if (
            $filter->getStatuses() &&
            !ResourceFilter::map(
                $filter->getStatuses(),
                $this->getAvailableStatuses()
            )
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
        $aclSubQuery = ' INNER JOIN `:dbstg`.`centreon_acl` AS service_acl ON service_acl.host_id = s.host_id
            AND service_acl.service_id = s.service_id
            AND service_acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';

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
        $sql = "SELECT DISTINCT
            s.service_id AS `id`,
            'service' AS `type`,
            s.description AS `name`,
            NULL AS `alias`,
            NULL AS `fqdn`,
            sh.host_id AS `host_id`,
            s.service_id AS `service_id`,
            s.icon_image_alt AS `icon_name`,
            s.icon_image AS `icon_url`,
            s.action_url AS `action_url`,
            s.notes_url AS `notes_url`,
            s.notes AS `notes_label`,
            i.name AS `monitoring_server_name`,
            i.instance_id AS `monitoring_server_id`,
            s.command_line AS `command_line`,
            NULL AS `timezone`,
            sh.host_id AS `parent_id`,
            sh.name AS `parent_name`,
            sh.alias AS `parent_alias`,
            sh.address AS `parent_fqdn`,
            'host' AS `parent_type`,
            sh.icon_image_alt AS `parent_icon_name`,
            sh.icon_image AS `parent_icon_url`,
            sh.state AS `parent_status_code`,
            CASE
                WHEN sh.state = 0 THEN 'UP'
                WHEN sh.state = 1 THEN 'DOWN'
                WHEN sh.state = 2 THEN 'UNREACHABLE'
                WHEN sh.state = 4 THEN 'PENDING'
            END AS `parent_status_name`,
            CASE
                WHEN sh.state = 0 THEN " . ResourceStatus::SEVERITY_OK . "
                WHEN sh.state = 1 THEN " . ResourceStatus::SEVERITY_HIGH . "
                WHEN sh.state = 2 THEN " . ResourceStatus::SEVERITY_LOW . "
                WHEN sh.state = 4 THEN " . ResourceStatus::SEVERITY_PENDING . "
            END AS `parent_status_severity_code`,
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
            s.active_checks AS `active_checks`,
            s.passive_checks AS `passive_checks`,
            service_cvl.value AS `severity_level`,
            s.last_state_change AS `last_status_change`,
            s.last_notification AS `last_notification`,
            s.notification_number AS `notification_number`,
            CONCAT(s.check_attempt, '/', s.max_check_attempts, ' (', CASE
                WHEN s.state_type = 1 THEN 'H'
                WHEN s.state_type = 0 THEN 'S'
            END, ')') AS `tries`,
            s.last_check AS `last_check`,
            CASE
                WHEN s.active_checks = 0 THEN NULL
                WHEN s.active_checks = 1 THEN s.next_check
            END AS `next_check`,
            s.output AS `information`,
            s.perfdata AS `performance_data`,
            s.execution_time AS `execution_time`,
            s.latency AS `latency`,
            s.notify AS `notification_enabled`,
            CASE
                WHEN EXISTS(
                    SELECT i.host_id, i.service_id
                    FROM centreon_storage.metrics AS m, centreon_storage.index_data AS i
                    WHERE i.host_id = s.host_id AND i.service_id = s.service_id
                        AND i.id = m.index_id AND m.hidden = \"0\") THEN 1
                ELSE 0
            END AS `has_graph_data`
            FROM `:dbstg`.`services` AS s
            INNER JOIN `:dbstg`.`hosts` sh
                ON sh.host_id = s.host_id
                AND sh.name NOT LIKE '_Module_%'
                AND sh.enabled = 1";

        // get monitoring server information
        $sql .= " INNER JOIN `:dbstg`.`instances` AS i ON i.instance_id = sh.instance_id";

        // get Severity level, name, icon
        $sql .= ' LEFT JOIN `:dbstg`.`customvariables` AS service_cvl ON service_cvl.host_id = s.host_id
            AND service_cvl.service_id = s.service_id
            AND service_cvl.name = "CRITICALITY_LEVEL"';

        // set ACL limitations
        if ($aclSubQuery !== null) {
            $sql .= $aclSubQuery;
        }

        // apply the service group filter to SQL query
        if ($filter->getServicegroupIds()) {
            $groupList = [];

            foreach ($filter->getServicegroupIds() as $index => $groupId) {
                $key = ":serviceServicegroupId_{$index}";

                $groupList[] = $key;
                $collector->addValue($key, $groupId, \PDO::PARAM_INT);
            }

            $sql .= ' INNER JOIN `:dbstg`.`services_servicegroups` AS ssg
                  ON ssg.host_id = s.host_id
                  AND ssg.service_id = s.service_id
                  AND ssg.servicegroup_id IN (' . implode(', ', $groupList) . ') ';
        }

        $hasWhereCondition = false;

        $this->sqlRequestTranslator->setConcordanceArray($this->serviceConcordances);
        try {
            $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        } catch (RequestParametersTranslatorException $ex) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }

        if ($searchRequest !== null) {
            $hasWhereCondition = true;
            $sql .= $searchRequest;
        }

        // show active services only
        $sql .= ($hasWhereCondition ? ' AND ' : ' WHERE ')
            . 's.enabled = 1';

        // apply the state filter to SQL query
        if ($filter->getStates() && !$filter->hasState(MonitoringResourceServiceInterface::STATE_ALL)) {
            $sqlState = [];
            $sqlStateCatalog = [
                MonitoringResourceServiceInterface::STATE_UNHANDLED_PROBLEMS => "(s.state_type = '1'"
                    . " AND s.acknowledged = 0"
                    . " AND s.scheduled_downtime_depth = 0"
                    . " AND sh.acknowledged = 0"
                    . " AND sh.scheduled_downtime_depth = 0"
                    . " AND s.state != 0"
                    . " AND s.state != 4)",
                MonitoringResourceServiceInterface::STATE_RESOURCES_PROBLEMS => '(s.state != 0 AND s.state != 4)',
                MonitoringResourceServiceInterface::STATE_IN_DOWNTIME => '(s.scheduled_downtime_depth = 1'
                    . ' OR sh.scheduled_downtime_depth = 1)',
                MonitoringResourceServiceInterface::STATE_ACKNOWLEDGED => '(s.acknowledged = 1 OR sh.acknowledged = 1)',
            ];

            foreach ($filter->getStates() as $state) {
                $sqlState[] = $sqlStateCatalog[$state];
            }

            $sql .= ' AND (' . implode(' OR ', $sqlState) . ')';
        }

        // apply the status filter to SQL query
        $statuses = ResourceFilter::map($filter->getStatuses(), $this->getAvailableStatuses());
        if ($statuses) {
            $statusList = [];

            foreach ($statuses as $index => $status) {
                $key = ":serviceStatuses_{$index}";

                $statusList[] = $key;
                $collector->addValue($key, $status, \PDO::PARAM_INT);
            }

            $sql .= ' AND s.state IN (' . implode(', ', $statusList) . ')';
        }

        if (!empty($filter->getHostIds())) {
            $hostIds = [];

            foreach ($filter->getHostIds() as $index => $hostId) {
                $key = ":hostId_{$index}";

                $hostIds[] = $key;
                $collector->addValue($key, $hostId, \PDO::PARAM_INT);
            }

            $sql .= ' AND sh.host_id IN (' . implode(', ', $hostIds) . ')';
        }

        if (!empty($filter->getServiceIds())) {
            $serviceIds = [];

            foreach ($filter->getServiceIds() as $index => $serviceId) {
                $key = ":serviceId_{$index}";

                $serviceIds[] = $key;
                $collector->addValue($key, $serviceId, \PDO::PARAM_INT);
            }

            $sql .= ' AND s.service_id IN (' . implode(', ', $serviceIds) . ')';
        }

        // apply the monitoring server filter to SQL query
        if (!empty($filter->getMonitoringServerIds())) {
            $monitoringServerIds = [];

            foreach ($filter->getMonitoringServerIds() as $index => $monitoringServerId) {
                $key = ":monitoringServerId_{$index}";

                $monitoringServerIds[] = $key;
                $collector->addValue($key, $monitoringServerId, \PDO::PARAM_INT);
            }

            $sql .= ' AND i.instance_id IN (' . implode(', ', $monitoringServerIds) . ')';
        }

        return $sql;
    }
}

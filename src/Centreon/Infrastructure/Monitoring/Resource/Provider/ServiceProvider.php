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
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;

final class ServiceProvider extends Provider
{
    /**
     * @inheritDoc
     */
    public function shouldBeSearched(ResourceFilter $filter): bool
    {
        if ($filter->getTypes() && !$filter->hasType(ResourceFilter::TYPE_SERVICE)) {
            return false;
        }

        if (
            $filter->getStatuses() &&
            !ResourceFilter::map(
                $filter->getStatuses(),
                ResourceFilter::MAP_STATUS_SERVICE
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
            s.notify AS `notification_enabled`
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
        if ($filter->getServicegroupNames()) {
            $groupList = [];

            foreach ($filter->getServicegroupNames() as $index => $groupName) {
                $key = ":serviceServicegroupName_{$index}";

                $groupList[] = $key;
                $collector->addValue($key, $groupName, \PDO::PARAM_STR);
            }

            $sql .= ' INNER JOIN `:dbstg`.`services_servicegroups` AS ssg
                  ON ssg.host_id = s.host_id AND ssg.service_id = s.service_id
                  INNER JOIN `:dbstg`.`servicegroups` AS sg
                  ON ssg.servicegroup_id = sg.servicegroup_id AND sg.name IN (' . implode(', ', $groupList) . ') ';
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
        if (!empty($filter->getMonitoringServerNames())) {
            $monitoringServerNames = [];

            foreach ($filter->getMonitoringServerNames() as $index => $monitoringServerName) {
                $key = ":monitoringServerName_{$index}";

                $monitoringServerNames[] = $key;
                $collector->addValue($key, $monitoringServerName, \PDO::PARAM_INT);
            }

            $sql .= ' AND i.name IN (' . implode(', ', $monitoringServerNames) . ')';
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
        $serviceResources = [];

        foreach ($resources as $key => $resource) {
            if ($resource->getType() === Resource::TYPE_SERVICE) {
                $where[] = "(i.host_id = :host_id_{$key} AND i.service_id = :service_id_{$key})";
                $collector->addValue(":service_id_{$key}", $resource->getId(), \PDO::PARAM_INT);
                $collector->addValue(":host_id_{$key}", $resource->getParent()->getId(), \PDO::PARAM_INT);
                $serviceResources[] = $resource;
            } else {
                $filteredResources[] = $resource;
            }
        }

        if (empty($serviceResources)) {
            return $filteredResources;
        }

        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT i.host_id, i.service_id
                FROM `:dbstg`.metrics AS m, `:dbstg`.index_data AS i
                WHERE (' . implode(' OR ', $where) . ')
                AND i.id = m.index_id
                AND m.hidden = "0"
                GROUP BY host_id, service_id'
            )
        );
        $collector->bind($statement);
        $statement->execute();

        while ($row = $statement->fetch()) {
            foreach ($serviceResources as $serviceResource) {
                if (
                    $serviceResource->getParent()->getId() === (int)$row['host_id']
                    && $serviceResource->getId() === (int)$row['service_id']
                ) {
                    $filteredResources[] = $serviceResource;
                }
            }
        }

        return $filteredResources;
    }
}

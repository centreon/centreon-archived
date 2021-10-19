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

final class HostProvider extends Provider
{
    /**
     * @inheritDoc
     */
    public function shouldBeSearched(ResourceFilter $filter): bool
    {
        if (
            $this->hasOnlyServiceSearch()
            || ($filter->getTypes() && !$filter->hasType(ResourceFilter::TYPE_HOST))
            || ($filter->getStatuses() && !ResourceFilter::map(
                $filter->getStatuses(),
                ResourceFilter::MAP_STATUS_HOST
            ))
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
        $aclSubQuery = ' EXISTS (SELECT 1 FROM `:dbstg`.`centreon_acl` AS host_acl WHERE host_acl.host_id = h.host_id
            AND host_acl.service_id IS NULL
            AND host_acl.group_id IN (' . implode(',', $accessGroupIds) . ') LIMIT 1) ';

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
            h.host_id AS `id`,
            'host' AS `type`,
            h.name AS `name`,
            h.alias AS `alias`,
            h.address AS `fqdn`,
            h.host_id AS `host_id`,
            NULL AS `service_id`,
            h.icon_image_alt AS `icon_name`,
            h.icon_image AS `icon_url`,
            h.action_url AS `action_url`,
            h.notes_url AS `notes_url`,
            h.notes AS `notes_label`,
            i.name AS `monitoring_server_name`,
            i.instance_id AS `monitoring_server_id`,
            h.command_line AS `command_line`,
            h.timezone AS `timezone`,
            NULL AS `parent_id`,
            NULL AS `parent_name`,
            NULL AS `parent_alias`,
            NULL AS `parent_fqdn`,
            NULL AS `parent_type`,
            NULL AS `parent_icon_name`,
            NULL AS `parent_icon_url`,
            NULL AS `parent_status_code`,
            NULL AS `parent_status_name`,
            NULL as `parent_status_severity_code`,
            h.state AS `status_code`,
            CASE
                WHEN h.state = 0 THEN 'UP'
                WHEN h.state = 1 THEN 'DOWN'
                WHEN h.state = 2 THEN 'UNREACHABLE'
                WHEN h.state = 4 THEN 'PENDING'
            END AS `status_name`,
            CASE
                WHEN h.state = 0 THEN " . ResourceStatus::SEVERITY_OK . "
                WHEN h.state = 1 THEN " . ResourceStatus::SEVERITY_HIGH . "
                WHEN h.state = 2 THEN " . ResourceStatus::SEVERITY_LOW . "
                WHEN h.state = 4 THEN " . ResourceStatus::SEVERITY_PENDING . "
            END AS `status_severity_code`,
            h.flapping AS `flapping`,
            h.percent_state_change AS `percent_state_change`,
            h.scheduled_downtime_depth AS `in_downtime`,
            h.acknowledged AS `acknowledged`,
            h.active_checks AS `active_checks`,
            h.passive_checks AS `passive_checks`,
            host_cvl.value AS `severity_level`,
            h.last_state_change AS `last_status_change`,
            h.last_notification AS `last_notification`,
            h.notification_number AS `notification_number`,
            CONCAT(h.check_attempt, '/', h.max_check_attempts, ' (', CASE
                WHEN h.state_type = 1 THEN 'H'
                WHEN h.state_type = 0 THEN 'S'
            END, ')') AS `tries`,
            h.last_check AS `last_check`,
            h.next_check AS `next_check`,
            h.output AS `information`,
            h.perfdata AS `performance_data`,
            h.execution_time AS `execution_time`,
            h.latency AS `latency`,
            h.notify AS `notification_enabled`
            FROM `:dbstg`.`hosts` AS h";

        // get monitoring server information
        $sql .= " INNER JOIN `:dbstg`.`instances` AS i ON i.instance_id = h.instance_id";

        // get Severity level, name, icon
        $sql .= ' LEFT JOIN `:dbstg`.`customvariables` AS host_cvl ON host_cvl.host_id = h.host_id
            AND host_cvl.service_id = 0
            AND host_cvl.name = "CRITICALITY_LEVEL"';

        $hasWhereCondition = false;

        $this->sqlRequestTranslator->setConcordanceArray($this->hostConcordances);
        try {
            $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        } catch (RequestParametersTranslatorException $ex) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }

        if ($searchRequest !== null) {
            $hasWhereCondition = true;
            $sql .= $searchRequest;
        }

        // set ACL limitations
        if ($aclSubQuery !== null) {
            $sql .= ($hasWhereCondition ? ' AND ' : ' WHERE ') . $aclSubQuery;
            $hasWhereCondition = true;
        }

        // show active hosts and aren't related to some module
        $sql .= ($hasWhereCondition ? ' AND ' : ' WHERE ')
            . 'h.enabled = 1 AND h.name NOT LIKE "\_Module\_%"';

        // apply the state filter to SQL query
        if ($filter->getStates() && !$filter->hasState(ResourceServiceInterface::STATE_ALL)) {
            $sqlState = [];
            $sqlStateCatalog = [
                ResourceServiceInterface::STATE_UNHANDLED_PROBLEMS => "(h.state_type = '1'"
                    . " AND h.acknowledged = 0"
                    . " AND h.scheduled_downtime_depth = 0"
                    . " AND h.state != 0"
                    . " AND h.state != 4)",
                ResourceServiceInterface::STATE_RESOURCES_PROBLEMS => '(h.state != 0 AND h.state != 4)',
                ResourceServiceInterface::STATE_IN_DOWNTIME => 'h.scheduled_downtime_depth = 1',
                ResourceServiceInterface::STATE_ACKNOWLEDGED => 'h.acknowledged = 1',
            ];

            foreach ($filter->getStates() as $state) {
                $sqlState[] = $sqlStateCatalog[$state];
            }

            if ($sqlState) {
                $sql .= ' AND (' . implode(' OR ', $sqlState) . ')';
            }
        }

        // apply the status filter to SQL query
        $statuses = ResourceFilter::map($filter->getStatuses(), ResourceFilter::MAP_STATUS_HOST);
        if ($statuses) {
            $statusList = [];

            foreach ($statuses as $index => $status) {
                $key = ":hostStatuses_{$index}";

                $statusList[] = $key;
                $collector->addValue($key, $status, \PDO::PARAM_INT);
            }

            $sql .= ' AND h.state IN (' . implode(', ', $statusList) . ')';
        }

        if (!empty($filter->getHostIds())) {
            $hostIds = [];

            foreach ($filter->getHostIds() as $index => $hostId) {
                $key = ":hostId_{$index}";

                $hostIds[] = $key;
                $collector->addValue($key, $hostId, \PDO::PARAM_INT);
            }

            $sql .= ' AND h.host_id IN (' . implode(', ', $hostIds) . ')';
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

    /**
     * @inheritDoc
     */
    public function excludeResourcesWithoutMetrics(array $resources): array
    {
        $filteredResources = [];

        foreach ($resources as $resource) {
            if ($resource->getType() !== Resource::TYPE_HOST) {
                $filteredResources[] = $resource;
            }
        }

        return $filteredResources;
    }
}

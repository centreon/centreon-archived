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

namespace Centreon\Infrastructure\Monitoring;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Monitoring\Resources;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\ResourceSeverity;
use Centreon\Domain\Monitoring\Model\ResourceDetailsHost;
use Centreon\Domain\Monitoring\Model\ResourceDetailsService;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;
use PDO;

/**
 * Database repository for the real time monitoring of services and host.
 *
 * @package Centreon\Infrastructure\Monitoring
 */
final class ResourceRepositoryRDB extends AbstractRepositoryDRB implements ResourceRepositoryInterface
{
    /**
     * @var AccessGroup[] List of access group used to filter the requests
     */
    private $accessGroups = [];

    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * @var array Association of resource search parameters
     */
    private $resourceConcordances = [
        'id' => 'resource.id',
        'name' => 'resource.name',
        'type' => 'resource.type',
        'status_code' => 'resource.status_code',
        'status' => 'resource.status_name',
        'status_severity_code' => 'resource.status_severity_code',
        'action_url' => 'resource.action_url',
        'parent_name' => 'resource.parent_name',
        'parent_status' => 'resource.parent_status_name',
        'severity_level' => 'resource.severity_level',
        'in_downtime' => 'resource.in_downtime',
        'acknowledged' => 'resource.acknowledged',
        'impacted_resources_count' => 'resource.impacted_resources_count',
        'last_status_change' => 'resource.last_status_change',
        'tries' => 'resource.tries',
        'last_check' => 'resource.last_check',
        'h.group' => 'hg.name',
        'h.group.id' => 'hhg.hostgroup_id',
    ];

    /**
     * @var array Association of host search parameters
     */
    private $hostConcordances = [
        'h.name' => 'h.name',
        'h.alias' => 'h.alias',
        'h.address' => 'h.address',
        'information' => 'h.output',
    ];

    /**
     * @var array Association of service search parameters
     */
    private $serviceConcordances = [
        'h.name' => 'sh.name',
        'h.alias' => 'sh.alias',
        'h.address' => 'sh.address',
        's.description' => 's.description',
        's.group' => 'sg.name',
        's.group.id' => 'ssg.servicegroup_id',
        'information' => 's.output',
    ];

    /**
     * @param DatabaseConnection $pdo
     */
    public function __construct(DatabaseConnection $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Initialized by the dependency injector.
     *
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function setSqlRequestTranslator(SqlRequestParametersTranslator $sqlRequestTranslator): void
    {
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT)
            ->setConcordanceErrorMode(RequestParameters::CONCORDANCE_ERRMODE_SILENT);
    }

    /**
     * {@inheritDoc}
     */
    public function filterByAccessGroups(?array $accessGroups): ResourceRepositoryInterface
    {
        $this->accessGroups = $accessGroups;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function findMissingInformationAboutHost(ResourceDetailsHost $host): void
    {
        $collector = new StatementCollector();
        $collector->addValue(':host_id', $host->getId(), PDO::PARAM_INT);

        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT i.name AS `poller_name`, h.state AS `status_code`, '
                . "CASE WHEN h.state = 0 THEN 'UP' "
                . "WHEN h.state = 1 THEN 'DOWN' "
                . "WHEN h.state = 2 THEN 'UNREACHABLE' "
                . "WHEN h.state = 3 THEN 'PENDING' "
                . "END AS `status_name`, "
                . "CASE WHEN h.state = 0 THEN ". ResourceStatus::SEVERITY_OK . ' '
                . "WHEN h.state = 1 THEN " . ResourceStatus::SEVERITY_HIGH . ' '
                . "WHEN h.state = 2 THEN " . ResourceStatus::SEVERITY_LOW . ' '
                . "WHEN h.state = 4 THEN " . ResourceStatus::SEVERITY_PENDING . ' '
                . " END AS `status_severity_code`, "
                . "CONCAT(h.check_attempt, '/', h.max_check_attempts, ' (', "
                . "CASE WHEN h.state_type = 1 THEN 'Hard' "
                . "WHEN h.state_type = 1 THEN 'Soft' "
                . "END, ')') AS `tries` "
                . 'FROM `:dbstg`.`hosts` AS `h` '
                . 'LEFT JOIN `:dbstg`.`instances` AS `i` ON i.instance_id = h.instance_id '

                . 'WHERE h.host_id = :host_id '
                . 'GROUP BY h.host_id'
            )
        );
        $collector->bind($statement);

        $statement->execute();

        while ($data = $statement->fetch()) {
            $host->setPollerName($data['poller_name']);
            $host->setTries($data['tries']);

            // parse ResourceStatus object
            $host->setStatus(EntityCreator::createEntityByArray(
                ResourceStatus::class,
                $data,
                'status_'
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findMissingInformationAboutService(ResourceDetailsService $service): void
    {
        $collector = new StatementCollector();
        $collector->addValue(':host_id', $service->getHost()->getId(), PDO::PARAM_INT);
        $collector->addValue(':service_id', $service->getId(), PDO::PARAM_INT);

        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT s.state AS `status_code`, '
                . "CASE WHEN s.state = 0 THEN 'OK' "
                . "WHEN s.state = 1 THEN 'WARNING' "
                . "WHEN s.state = 2 THEN 'CRITICAL' "
                . "WHEN s.state = 3 THEN 'UNKNOWN' "
                . "WHEN s.state = 4 THEN 'PENDING' "
                . "END AS `status_name`, "
                . 'h.host_id AS `parent_id`, '
                . 'h.name AS `parent_name`, '
                . 'h.state AS `parent_status_code`, '
                . "CASE WHEN h.state = 0 THEN 'UP' "
                . "WHEN h.state = 1 THEN 'DOWN' "
                . "WHEN h.state = 2 THEN 'UNREACHABLE' "
                . "WHEN h.state = 3 THEN 'PENDING' "
                . "END AS `parent_status_name`, "
                . "CASE WHEN h.state = 0 THEN ". ResourceStatus::SEVERITY_OK . ' '
                . "WHEN h.state = 1 THEN " . ResourceStatus::SEVERITY_HIGH . ' '
                . "WHEN h.state = 2 THEN " . ResourceStatus::SEVERITY_LOW . ' '
                . "WHEN h.state = 4 THEN " . ResourceStatus::SEVERITY_PENDING . ' '
                . " END AS `parent_status_severity_code`, "
                . "CASE WHEN s.state = 0 THEN " . ResourceStatus::SEVERITY_OK . ' '
                . "WHEN s.state = 1 THEN " . ResourceStatus::SEVERITY_MEDIUM . ' '
                . "WHEN s.state = 2 THEN " . ResourceStatus::SEVERITY_HIGH . ' '
                . "WHEN s.state = 3 THEN " . ResourceStatus::SEVERITY_LOW . ' '
                . "WHEN s.state = 4 THEN " . ResourceStatus::SEVERITY_PENDING . ' '
                . "END AS `status_severity_code`,"
                . "CONCAT(s.check_attempt, '/', s.max_check_attempts, ' (', "
                . "CASE WHEN s.state_type = 1 THEN 'Hard' "
                . "WHEN s.state_type = 1 THEN 'Soft' "
                . "END, ')') AS `tries` "
                . 'FROM `:dbstg`.`services` AS `s` '
                . 'LEFT JOIN `:dbstg`.`hosts` AS `h` ON h.host_id = s.host_id '
                . 'WHERE s.service_id = :service_id AND s.host_id = :host_id '
                . 'GROUP BY s.service_id'
            )
        );
        $collector->bind($statement);

        $statement->execute();

        while ($data = $statement->fetch()) {
            $service->setTries($data['tries']);

            // parse ResourceStatus object
            $service->setStatus(EntityCreator::createEntityByArray(
                ResourceStatus::class,
                $data,
                'status_'
            ));

            // parse Resource object
            $service->setParent(EntityCreator::createEntityByArray(
                Resource::class,
                $data,
                'parent_'
            ));

            // parse ResourceStatus object
            $service->getParent()->setStatus(EntityCreator::createEntityByArray(
                ResourceStatus::class,
                $data,
                'parent_status_'
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getListOfResourcesWithGraphData(array $resources): array
    {
        $collector = new StatementCollector();
        $collector->addValue(":hidden", 0);
        $where = [];

        foreach ($resources as $key => $resources) {
            if (!$resources->getParent()) {
                continue;
            }

            $where[] = "(i.host_id = :host_id_{$key} AND i.service_id = :service_id_{$key})";
            $collector->addValue(":service_id_{$key}", $resources->getId(), PDO::PARAM_INT);
            $collector->addValue(":host_id_{$key}", $resources->getParent()->getId(), PDO::PARAM_INT);
        }

        if (!$where) {
            return [];
        }

        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT host_id, service_id FROM `:dbstg`.metrics AS m, `:dbstg`.index_data AS i '
                . 'WHERE (' . implode(' OR ', $where) . ') AND i.id = m.index_id AND m.hidden = :hidden '
                . 'GROUP BY m.metric_id '
            )
        );
        $collector->bind($statement);

        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * {@inheritDoc}
     */
    public function findResources(ResourceFilter $filter): array
    {
        $resources = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $resources;
        }

        $collector = new StatementCollector();
        $request = 'SELECT SQL_CALC_FOUND_ROWS '
            . 'resource.id, resource.type, resource.name, resource.action_url, '
            . 'resource.status_code, resource.status_name, resource.status_severity_code, ' // status
            . 'resource.icon_name, resource.icon_url, ' // icon
            . 'resource.parent_id, resource.parent_name, ' // parent
            . 'resource.parent_icon_name, resource.parent_icon_url, ' // parent icon
            // parent status
            . 'resource.parent_status_code, resource.parent_status_name, resource.parent_status_severity_code, '
            . 'resource.severity_level, resource.severity_name, ' // severity
            . 'resource.in_downtime, resource.acknowledged, '
            . 'resource.impacted_resources_count, resource.last_status_change, '
            . 'resource.tries, resource.last_check, resource.information '
            . 'FROM (';

        $subRequests = [];

        if ($this->hasServiceFilter($filter)) {
            $subRequests[] = '(' . $this->prepareQueryForServiceResources($collector, $filter) . ') ';
        }

        // do not get hosts if a service filter is given
        if ($this->hasHostFilter($filter)) {
            $subRequests[] = '(' . $this->prepareQueryForHostResources($collector, $filter) . ')';
        }

        if (!$subRequests) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal(0);

            return [];
        }

        $request .= implode('UNION ALL ', $subRequests);
        unset($subRequests);

        $request .= ') AS `resource`'
            // Join the host groups
            . ' LEFT JOIN `:dbstg`.`hosts_hostgroups` AS hhg ON hhg.host_id = resource.host_id'
            . ' LEFT JOIN `:dbstg`.`hostgroups` AS hg ON hg.hostgroup_id = hhg.hostgroup_id';

        $request = $this->translateDbName($request);

        // Search
        $this->sqlRequestTranslator->setConcordanceArray($this->resourceConcordances);
        try {
            $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        } catch (RequestParametersTranslatorException $ex) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }
        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $collector->addValue($key, current($data), key($data));
        }
        $request .= $searchRequest ? $searchRequest : '';

        // apply the service group filter to SQL query
        if ($filter->getHostgroupIds()) {
            $groupList = [];

            foreach ($filter->getHostgroupIds() as $index => $groupId) {
                $key = ":resourceHostgroupId_{$index}";

                $groupList[] = $key;
                $collector->addValue($key, $groupId, PDO::PARAM_INT);
            }

            $request .= ($searchRequest ? ' AND ' : ' WHERE ')
                . 'hg.hostgroup_id IN (' . implode(', ', $groupList) . ')';
        }

        // Group
        $request .= ' GROUP BY resource.id';

        // Sort
        $request .= $this->sqlRequestTranslator->translateSortParameterToSql()
            ?: ' ORDER BY resource.status_name DESC, resource.name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);
        $collector->bind($statement);

        $statement->execute();

        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int)$this->db->query('SELECT FOUND_ROWS()')->fetchColumn()
        );

        while ($result = $statement->fetch()) {
            $resources[] = $this->parseResource($result);
        }

        return $resources;
    }

    /**
     * Check if a service filter is given in request parameters
     *
     * @return bool
     */
    private function hasServiceSearch(): bool
    {
        $search = $this->sqlRequestTranslator->getRequestParameters()->getSearch();

        if (empty($search)) {
            return false;
        }

        $operator = array_keys($search)[0];

        if ($operator === RequestParameters::AGGREGATE_OPERATOR_OR) {
            return !$this->extractSpecificSearchCriteria('/^h\./');
        }

        return $this->extractSpecificSearchCriteria('/^s\./');
    }

    /**
     * Extract request parameters
     *
     * @param string $key
     * @return bool
     */
    private function extractSpecificSearchCriteria(string $key)
    {
        $requestParameters = $this->sqlRequestTranslator->getRequestParameters();
        $search = $requestParameters->getSearch();

        $serviceConcordances = array_reduce(
            array_keys($this->serviceConcordances),
            function ($acc, $concordanceKey) use ($key) {
                if (preg_match($key, $concordanceKey)) {
                    $acc[] = $concordanceKey;
                }
                return $acc;
            },
            []
        );

        foreach ($serviceConcordances as $serviceConcordance) {
            if ($requestParameters->hasSearchParameter($serviceConcordance, $search)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the filters are compatible to extract services
     *
     * @param ResourceFilter $filter
     * @return bool
     */
    private function hasServiceFilter(ResourceFilter $filter): bool
    {
        if (
            ($filter->getTypes() && !$filter->hasType(ResourceFilter::TYPE_SERVICE)) ||
            ($filter->getStatuses() && !ResourceFilter::map(
                $filter->getStatuses(),
                ResourceFilter::MAP_STATUS_SERVICE
            ))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if the filters are compatible to extract hosts
     *
     * @param ResourceFilter $filter
     * @return bool
     */
    private function hasHostFilter(ResourceFilter $filter): bool
    {
        if (
            $this->hasServiceSearch() ||
            ($filter->getTypes() && !$filter->hasType(ResourceFilter::TYPE_HOST)) ||
            ($filter->getStatuses() && !ResourceFilter::map(
                $filter->getStatuses(),
                ResourceFilter::MAP_STATUS_HOST
            )) ||
            $filter->getServicegroupIds()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Prepare SQL query for services
     *
     * @param StatementCollector $collector
     * @param ResourceFilter $filter
     * @return string
     */
    protected function prepareQueryForServiceResources(StatementCollector $collector, ResourceFilter $filter): string
    {
        $sql = "SELECT
            s.service_id AS `id`,
            'service' AS `type`,
            s.service_id AS `origin_id`,
            sh.host_id AS `host_id`,
            s.description AS `name`,
            s.action_url AS `action_url`,
            s.icon_image_alt AS `icon_name`,
            s.icon_image AS `icon_url`,
            sh.host_id AS `parent_id`,
            sh.name AS `parent_name`,
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
            s.scheduled_downtime_depth AS `in_downtime`,
            s.acknowledged AS `acknowledged`,
            service_cvl.value AS `severity_level`,
            sc.sc_name AS `severity_name`,
            0 AS `impacted_resources_count`,
            s.last_state_change AS `last_status_change`,
            CONCAT(s.check_attempt, '/', s.max_check_attempts, ' (', CASE
                WHEN s.state_type = 1 THEN 'H'
                WHEN s.state_type = 0 THEN 'S'
            END, ')') AS `tries`,
            s.last_check AS `last_check`,
            s.output AS `information`
            FROM `:dbstg`.`services` AS s
            INNER JOIN `:dbstg`.`hosts` sh
                ON sh.host_id = s.host_id
                AND sh.name NOT LIKE :serviceModule
                AND sh.enabled = 1";
        $collector->addValue(':serviceModule', '_Module_%');

        // set ACL limitations
        if (!$this->isAdmin()) {
            $sql .= " INNER JOIN `:dbstg`.`centreon_acl` AS service_acl ON service_acl.host_id = s.host_id
                AND service_acl.service_id = s.service_id
                AND service_acl.group_id IN (" . $this->accessGroupIdToString($this->accessGroups) . ")";
        }

        // Join the service groups
        $sql .= " LEFT JOIN `:dbstg`.`services_servicegroups` AS ssg"
            . " ON ssg.host_id = s.host_id AND ssg.service_id = s.service_id"
            . ' LEFT JOIN `:dbstg`.`servicegroups` AS sg ON sg.servicegroup_id = ssg.servicegroup_id';

        // get Severity level, name, icon
        $sql .= ' LEFT JOIN `:dbstg`.`customvariables` AS service_cvl ON service_cvl.host_id = s.host_id
            AND service_cvl.service_id = s.service_id
            AND service_cvl.name = :serviceCustomVariablesName
        LEFT JOIN `:db`.`service_categories_relation` AS scr ON scr.service_service_id = s.service_id
        LEFT JOIN `:db`.`service_categories` AS sc ON sc.sc_id = scr.sc_id
            AND sc.level IS NOT NULL
            AND sc.icon_id IS NOT NULL
        LEFT JOIN `:db`.`view_img` AS service_vi ON service_vi.img_id = sc.icon_id';

        $collector->addValue(':serviceCustomVariablesName', 'CRITICALITY_LEVEL');

        $this->sqlRequestTranslator->setConcordanceArray($this->serviceConcordances);
        try {
            $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        } catch (RequestParametersTranslatorException $ex) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }

        $sql .= $searchRequest;
        $sql .= !is_null($searchRequest) ? ' AND' : ' WHERE';

        // show active services only
        $sql .= ' s.enabled = 1';

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
                $collector->addValue($key, $status, PDO::PARAM_INT);
            }

            $sql .= ' AND s.state IN (' . implode(', ', $statusList) . ')';
        }

        // apply the service group filter to SQL query
        if ($filter->getServicegroupIds()) {
            $groupList = [];

            foreach ($filter->getServicegroupIds() as $index => $groupId) {
                $key = ":serviceServicegroupId_{$index}";

                $groupList[] = $key;
                $collector->addValue($key, $groupId, PDO::PARAM_INT);
            }

            $sql .= ' AND sg.servicegroup_id IN (' . implode(', ', $groupList) . ')';
        }

        // group by the service ID to preventing the duplication
        $sql .= ' GROUP BY s.service_id';

        return $sql;
    }

    /**
     * Prepare SQL query for hosts
     *
     * @param StatementCollector $collector
     * @param ResourceFilter $filter
     * @return string
     */
    protected function prepareQueryForHostResources(StatementCollector $collector, ResourceFilter $filter): string
    {
        $sql = "SELECT
            h.host_id AS `id`,
            'host' AS `type`,
            h.host_id AS `origin_id`,
            h.host_id AS `host_id`,
            h.name AS `name`,
            h.action_url AS `action_url`,
            h.icon_image_alt AS `icon_name`,
            h.icon_image AS `icon_url`,
            NULL AS `parent_id`,
            NULL AS `parent_name`,
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
            h.scheduled_downtime_depth AS `in_downtime`,
            h.acknowledged AS `acknowledged`,
            host_cvl.value AS `severity_level`,
            hc.hc_comment AS `severity_name`,
            (SELECT COUNT(DISTINCT host_s.service_id)
                FROM `:dbstg`.`services` AS host_s
                WHERE host_s.host_id = h.host_id AND host_s.enabled = 1
            ) AS `impacted_resources_count`,
            h.last_state_change AS `last_status_change`,
            CONCAT(h.check_attempt, '/', h.max_check_attempts, ' (', CASE
                WHEN h.state_type = 1 THEN 'H'
                WHEN h.state_type = 0 THEN 'S'
            END, ')') AS `tries`,
            h.last_check AS `last_check`,
            h.output AS `information`
            FROM `:dbstg`.`hosts` AS h";

        // set ACL limitations
        if (!$this->isAdmin()) {
            $sql .= " INNER JOIN `:dbstg`.`centreon_acl` AS host_acl ON host_acl.host_id = h.host_id
                  AND host_acl.service_id IS NULL
                  AND host_acl.group_id IN (" . $this->accessGroupIdToString($this->accessGroups) . ")";
        }

        // get Severity level, name, icon
        $sql .= ' LEFT JOIN `:dbstg`.`customvariables` AS host_cvl ON host_cvl.host_id = h.host_id
            AND host_cvl.service_id = 0
            AND host_cvl.name = :hostCustomVariablesName
        LEFT JOIN `:db`.`hostcategories_relation` AS hcr ON hcr.host_host_id = h.host_id
        LEFT JOIN `:db`.`hostcategories` AS hc ON hc.hc_id = hcr.hostcategories_hc_id
            AND hc.level IS NOT NULL
            AND hc.icon_id IS NOT NULL
        LEFT JOIN `:db`.`view_img` AS host_vi ON host_vi.img_id = hc.icon_id';

        $collector->addValue(':hostCustomVariablesName', 'CRITICALITY_LEVEL');

        $this->sqlRequestTranslator->setConcordanceArray($this->hostConcordances);
        try {
            $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        } catch (RequestParametersTranslatorException $ex) {
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }

        $sql .= $searchRequest;
        $sql .= !is_null($searchRequest) ? ' AND' : ' WHERE';

        // show active hosts and aren't related to some module
        $sql .= ' h.enabled = 1 AND h.name NOT LIKE :hostModule';

        $collector->addValue(':hostModule', '_Module_%');

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
                $collector->addValue($key, $status, PDO::PARAM_INT);
            }

            $sql .= ' AND h.state IN (' . implode(', ', $statusList) . ')';
        }

        // prevent duplication
        $sql .= ' GROUP BY h.host_id';

        return $sql;
    }

    /**
     * Parse array data from DB into Resource model
     *
     * @param array $data
     * @return \Centreon\Domain\Monitoring\Resources
     * @throws \Exception
     */
    protected function parseResource(array $data): Resource
    {
        $resource = EntityCreator::createEntityByArray(
            Resource::class,
            $data
        );

        // parse ResourceStatus object
        $resource->setStatus(EntityCreator::createEntityByArray(
            ResourceStatus::class,
            $data,
            'status_'
        ));

        // parse Icon object
        $icon = EntityCreator::createEntityByArray(
            Icon::class,
            $data,
            'icon_'
        );

        if ($icon->getUrl()) {
            $resource->setIcon($icon);
        }

        // parse severity Icon object
        $severity = EntityCreator::createEntityByArray(
            ResourceSeverity::class,
            $data,
            'severity_'
        );

        if ($severity->getLevel() !== null) {
            $resource->setSeverity($severity);
        }

        // parse parent Resource object
        $parent = EntityCreator::createEntityByArray(
            Resource::class,
            $data,
            'parent_'
        );

        if ($parent->getId()) {
            $parentIcon = EntityCreator::createEntityByArray(
                Icon::class,
                $data,
                'parent_icon_'
            );

            if ($parentIcon->getUrl()) {
                $parent->setIcon($parentIcon);
            }

            $parentStatus = EntityCreator::createEntityByArray(
                ResourceStatus::class,
                $data,
                'parent_status_'
            );
            $parent->setStatus($parentStatus);

            $resource->setParent($parent);
        }

        return $resource;
    }

    /**
     * Check if the contact is admin
     *
     * @return bool
     */
    private function isAdmin(): bool
    {
        return ($this->contact !== null)
            ? $this->contact->isAdmin()
            : false;
    }

    /**
     * {@inheritDoc}
     */
    public function setContact(ContactInterface $contact): ResourceRepositoryInterface
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return bool Return FALSE if the contact is an admin or has at least one access group.
     */
    private function hasNotEnoughRightsToContinue(): bool
    {
        return ($this->contact !== null)
            ? !($this->contact->isAdmin() || count($this->accessGroups) > 0)
            : count($this->accessGroups) == 0;
    }
}

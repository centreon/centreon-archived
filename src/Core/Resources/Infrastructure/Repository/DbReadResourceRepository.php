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

namespace Core\Resources\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Core\Resources\Infrastructure\Repository\ResourceACLProviders\ResourceACLProviderInterface;
use Core\Tag\RealTime\Domain\Model\Tag;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Infrastructure\DatabaseConnection;
use Core\Domain\RealTime\ResourceTypeInterface;
use Core\Severity\RealTime\Domain\Model\Severity;
use Centreon\Domain\Repository\RepositoryException;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Core\Resources\Application\Repository\ReadResourceRepositoryInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;

class DbReadResourceRepository extends AbstractRepositoryDRB implements ReadResourceRepositoryInterface
{
    use LoggerTrait;

    /**
     * @var ResourceEntity[]
     */
    private array $resources = [];

    private const RESOURCE_TYPE_SERVICE = 0,
                  RESOURCE_TYPE_HOST = 1,
                  RESOURCE_TYPE_METASERVICE = 2;

    /**
     * @var ResourceTypeInterface[]
     */
    private array $resourceTypes = [];

    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @var array<string, string>
     */
    private $resourceConcordances = [
        'id' => 'resources.id',
        'name' => 'resources.name',
        'alias' => 'resources.alias',
        'fqdn' => 'resources.address',
        'type' => 'resources.type',
        'h.name' => 'parent_resource.name',
        'h.alias' => 'parent_resource.alias',
        'h.address' => 'parent_resource.address',
        's.description' => 'resources.type IN (0,2) AND resources.name',
        'status_code' => 'resources.status',
        'status_severity_code' => 'resources.status_ordered',
        'action_url' => 'resources.action_url',
        'parent_name' => 'resources.parent_name',
        'parent_alias' => 'parent_resource.alias',
        'parent_status' => 'parent_status',
        'severity_level' => 'severity_level',
        'in_downtime' => 'resources.in_downtime',
        'acknowledged' => 'resources.acknowledged',
        'last_status_change' => 'resources.last_status_change',
        'tries' => 'resources.check_attempts',
        'last_check' => 'resources.last_check',
        'monitoring_server_name' => 'monitoring_server_name',
        'information' => 'resources.output',
    ];

    /**
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     * @param \Traversable<ResourceTypeInterface> $resourceTypes
     * @param \Traversable<ResourceACLProviderInterface> $resourceACLProviders
     */
    public function __construct(
        DatabaseConnection $db,
        SqlRequestParametersTranslator $sqlRequestTranslator,
        \Traversable $resourceTypes,
        private \Traversable $resourceACLProviders
    ) {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT)
            ->setConcordanceErrorMode(RequestParameters::CONCORDANCE_ERRMODE_SILENT);

        if ($resourceTypes instanceof \Countable && count($resourceTypes) === 0) {
            throw new \InvalidArgumentException(
                _('You must add at least one resource provider')
            );
        }

        $this->resourceTypes = iterator_to_array($resourceTypes);
    }

    private function generateFindResourcesRequest(
        ResourceFilter $filter,
        StatementCollector $collector,
        string $accessGroupRequest = ''
    ): string {
        $this->sqlRequestTranslator->setConcordanceArray($this->resourceConcordances);

        $request = "SELECT SQL_CALC_FOUND_ROWS DISTINCT
            resources.resource_id,
            resources.name,
            resources.alias,
            resources.address,
            resources.id,
            resources.internal_id,
            resources.parent_id,
            resources.parent_name,
            parent_resource.status AS `parent_status`,
            parent_resource.alias AS `parent_alias`,
            parent_resource.status_ordered AS `parent_status_ordered`,
            parent_resource.address AS `parent_fqdn`,
            severities.id AS `severity_id`,
            severities.level AS `severity_level`,
            severities.name AS `severity_name`,
            severities.type AS `severity_type`,
            severities.icon_id AS `severity_icon_id`,
            resources.type,
            resources.status,
            resources.status_ordered,
            resources.status_confirmed,
            resources.in_downtime,
            resources.acknowledged,
            resources.passive_checks_enabled,
            resources.active_checks_enabled,
            resources.notifications_enabled,
            resources.last_check,
            resources.last_status_change,
            resources.check_attempts,
            resources.max_check_attempts,
            resources.notes,
            resources.notes_url,
            resources.action_url,
            resources.output,
            resources.poller_id,
            resources.has_graph,
            instances.name AS `monitoring_server_name`,
            resources.enabled,
            resources.icon_id,
            resources.severity_id
        FROM `:dbstg`.`resources`
        LEFT JOIN `:dbstg`.`resources` parent_resource
            ON parent_resource.id = resources.parent_id
        LEFT JOIN `:dbstg`.`severities`
            ON `severities`.severity_id = `resources`.severity_id
        LEFT JOIN `:dbstg`.`resources_tags` AS rtags
            ON `rtags`.resource_id = `resources`.resource_id
        INNER JOIN `:dbstg`.`instances`
            ON `instances`.instance_id = `resources`.poller_id";

        /**
         * Handle search values
         */
        $searchSubRequest = null;

        try {
            $searchSubRequest .= $this->sqlRequestTranslator->translateSearchParameterToSql();
        } catch (RequestParametersTranslatorException $ex) {
            $this->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            throw new RepositoryException($ex->getMessage(), 0, $ex);
        }

        $request .= ! empty($searchSubRequest) ? $searchSubRequest . ' AND ' : ' WHERE ';

        $request .= " resources.name NOT LIKE '\_Module\_%'
            AND resources.parent_name NOT LIKE '\_Module\_BAM%'
            AND resources.enabled = 1 AND resources.type != 3";

        $request .= $accessGroupRequest;

        /**
         * Resource Type filter
         * 'service', 'metaservice', 'host'
         */
        $request .= $this->addResourceTypeSubRequest($filter);

        /**
         * State filter
         * 'unhandled_problems', 'resource_problems', 'acknowledged', 'in_downtime'
         */
        $request .= $this->addResourceStateSubRequest($filter);

        /**
         * Status filter
         * 'OK', 'WARNING', 'CRITICAL', 'UNKNOWN', 'UP', 'UNREACHABLE', 'DOWN', 'PENDING'
         */
        $request .= $this->addResourceStatusSubRequest($filter);

        /**
         * Status type filter
         * 'HARD', 'SOFT'
         */
        $request .= $this->addStatusTypeSubRequest($filter);

        /**
         * Monitoring Server filter
         */
        $request .= $this->addMonitoringServerSubRequest($filter, $collector);

        /**
         * Severity filter (levels and/or names)
         */
        $request .= $this->addSeveritySubRequest($filter, $collector);

        /**
         * Resource tag filter by name
         * - servicegroups
         * - hostgroups
         * - servicecategories
         * - hostcategories
         */
        $request .= $this->addResourceTagsSubRequest($filter, $collector);

        /**
         * Handle sort parameters
         */
        $request .= $this->sqlRequestTranslator->translateSortParameterToSql()
            ?: ' ORDER BY resources.status_ordered DESC, resources.name ASC';

        /**
         * Handle pagination
         */
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        return $request;
    }

    /**
     * @inheritDoc
     */
    public function findResources(ResourceFilter $filter): array
    {
        $this->resources = [];
        $collector = new StatementCollector();
        $request = $this->generateFindResourcesRequest($filter, $collector);

        $this->fetchResources($request, $collector);

        return $this->resources;
    }

    public function findResourcesByAccessGroupIds(ResourceFilter $filter, array $accessGroupIds): array
    {
        $this->resources = [];
        $collector = new StatementCollector();
        $accessGroupRequest = $this->addResourceAclSubRequest($accessGroupIds);
        $request = $this->generateFindResourcesRequest($filter, $collector, $accessGroupRequest);
        $this->fetchResources($request, $collector);

        return $this->resources;
    }

    /**
     * @param int[] $accessGroupIds
     */
    private function addResourceAclSubRequest(array $accessGroupIds): string
    {
        $orConditions = array_map(
            fn(ResourceACLProviderInterface $provider) => '(' . $provider->getACLSubRequest() . ')',
            iterator_to_array($this->resourceACLProviders)
        );

        if (empty($orConditions)) {
            throw new \InvalidArgumentException(_('You must provide at least one ACL provider'));
        }

        $pattern = ' AND EXISTS (SELECT 1 FROM `:dbstg`.centreon_acl acl WHERE (%s) AND acl.group_id IN (%s) LIMIT 1)';

        return sprintf($pattern, join(' OR ', $orConditions), join(', ', $accessGroupIds));
    }

    private function fetchResources(string $request, StatementCollector $collector): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName($request)
        );

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            /** @var int */
            $data_type = key($data);
            $collector->addValue($key, current($data), $data_type);
        }

        $collector->bind($statement);
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');

        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        while ($resourceRecord = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->resources[] = DbResourceFactory::createFromRecord($resourceRecord, $this->resourceTypes);
        }

        $iconIds = $this->getIconIdsFromResources();
        $icons = $this->getIconsDataForResources($iconIds);
        $this->completeResourcesWithIcons($icons);
    }

    /**
     * @param array<int, array<string, string>> $icons
     * @return void
     */
    private function completeResourcesWithIcons(array $icons): void
    {
        foreach ($this->resources as $resource) {
            if ($resource->getIcon() !== null) {
                $resourceIconId = $resource->getIcon()->getId();
                $resource->getIcon()
                    ->setName($icons[$resourceIconId]['name'])
                    ->setUrl($icons[$resourceIconId]['url']);
            }

            if ($resource->getSeverity() !== null) {
                $resourceSeverityIconId = $resource->getSeverity()->getIcon()->getId();
                $resource->getSeverity()->getIcon()
                    ->setName($icons[$resourceSeverityIconId]['name'])
                    ->setUrl($icons[$resourceSeverityIconId]['url']);
            }
        }
    }

    /**
     * @return array<int, int|null>
     */
    private function getIconIdsFromResources(): array
    {
        $resourceIconIds = $this->getResourceIconIdsFromResources();
        $severityIconIds = $this->getSeverityIconIdsFromResources();

        return array_unique(array_merge($resourceIconIds, $severityIconIds));
    }

    /**
     * @return array<int, int|null>
     */
    private function getResourceIconIdsFromResources(): array
    {
        $resourcesWithIcons = array_filter(
            $this->resources,
            fn (ResourceEntity $resource) => $resource->getIcon() !== null
        );

        return array_map(
            fn (ResourceEntity $resource) => $resource->getIcon()?->getId(),
            $resourcesWithIcons
        );
    }

    /**
     * @return array<int, int|null>
     */
    private function getSeverityIconIdsFromResources(): array
    {
        $resourcesWithSeverities = array_filter(
            $this->resources,
            fn (ResourceEntity $resource) => $resource->getSeverity() !== null
        );

        return array_map(
            fn (ResourceEntity $resource) => $resource->getSeverity()?->getIcon()?->getId(),
            $resourcesWithSeverities
        );
    }

    /**
     * @param ResourceFilter $filter
     * @return int[]
     */
    private function getSeverityLevelsFromFilter(ResourceFilter $filter): array
    {
        $levels = [];
        if (! empty($filter->getHostSeverityLevels())) {
            foreach ($filter->getHostSeverityLevels() as $level) {
                $levels[] = $level;
            }
        }

        if (! empty($filter->getServiceSeverityLevels())) {
            foreach ($filter->getServiceSeverityLevels() as $level) {
                $levels[] = $level;
            }
        }
        return array_unique($levels);
    }

    /**
     * @param ResourceFilter $filter
     * @return int[]
     */
    private function getSeverityTypesFromFilter(ResourceFilter $filter): array
    {
        $types = [];
        if (
            ! empty($filter->getHostSeverityLevels())
            || ! empty($filter->getHostSeverityNames())
        ) {
            $types[] = Severity::HOST_SEVERITY_TYPE_ID;
        }

        if (
            ! empty($filter->getServiceSeverityLevels())
            || ! empty($filter->getServiceSeverityNames())
        ) {
            $types[] = Severity::SERVICE_SEVERITY_TYPE_ID;
        }

        return $types;
    }

    /**
     * @param ResourceFilter $filter
     * @return string[]
     */
    private function getSeverityNamesFromFilter(ResourceFilter $filter): array
    {
        $names = [];
        if (! empty($filter->getHostSeverityNames())) {
            foreach ($filter->getHostSeverityNames() as $hostSeverityName) {
                $names[] = $hostSeverityName;
            }
        }

        if (! empty($filter->getServiceSeverityNames())) {
            foreach ($filter->getServiceSeverityNames() as $serviceSeverityName) {
                $names[] = $serviceSeverityName;
            }
        }

        return array_unique($names);
    }

    /**
     * @param ResourceFilter $filter
     * @param StatementCollector $collector
     * @return string
     */
    private function addSeveritySubRequest(ResourceFilter $filter, StatementCollector $collector): string
    {
        $subRequest = '';
        $filteredNames = [];
        $filteredTypes = [];
        $filteredLevels = [];

        $names = $this->getSeverityNamesFromFilter($filter);
        $levels = $this->getSeverityLevelsFromFilter($filter);
        $types = $this->getSeverityTypesFromFilter($filter);

        foreach ($names as $index => $name) {
            $key = ":severityName_{$index}";
            $filteredNames[] = $key;
            $collector->addValue($key, $name, \PDO::PARAM_STR);
        }

        foreach ($levels as $index => $level) {
            $key = ":severityLevel_{$index}";
            $filteredLevels[] = $key;
            $collector->addValue($key, $level, \PDO::PARAM_INT);
        }

        foreach ($types as $index => $type) {
            $key = ":severityType_{$index}";
            $filteredTypes[] = $key;
            $collector->addValue($key, $type, \PDO::PARAM_INT);
        }

        if (
            ! empty($filteredNames)
            || ! empty($filteredLevels)
        ) {
            $subRequest = ' AND EXISTS (
                SELECT 1 FROM `:dbstg`.severities
                WHERE severities.severity_id = resources.severity_id
                    AND severities.type IN (' . implode(', ', $filteredTypes) . ')';

            $subRequest .= ! empty($filteredNames)
                ? ' AND severities.name IN (' . implode(', ', $filteredNames) . ')'
                : '';

            $subRequest .= ! empty($filteredLevels)
                ? ' AND severities.level IN (' . implode(', ', $filteredLevels) . ')'
                : '';

            $subRequest .= ' LIMIT 1)';
        }

        return $subRequest;
    }

    /**
     * Only return resources that has performance data available in order to display graphs
     *
     * @param ResourceEntity[] $resources
     * @return ResourceEntity[]
     */
    public function extractResourcesWithGraphData(array $resources): array
    {
        return array_filter(
            $resources,
            fn (ResourceEntity $resource) => $resource->hasGraph(),
        );
    }

    /**
     * This adds the sub request filter on resource types
     *
     * @param ResourceFilter $filter
     * @return string
     */
    private function addResourceTypeSubRequest(ResourceFilter $filter): string
    {
        /**
         * @var int[] $resourceTypes
         */
        $resourceTypes = [];
        $subRequest = '';
        foreach ($filter->getTypes() as $filterType) {
            foreach ($this->resourceTypes as $resourceType) {
                if ($resourceType->isValidForTypeName($filterType)) {
                    $resourceTypes[] = $resourceType->getId();
                    break;
                }
            }
        }

        if (! empty($resourceTypes)) {
            $subRequest = ' AND resources.type IN (' . implode(', ', $resourceTypes) . ')';
        }

        return $subRequest;
    }

    /**
     * This adds the sub request filter on resource state
     *
     * @param ResourceFilter $filter
     * @return string
     */
    private function addResourceStateSubRequest(ResourceFilter $filter): string
    {
        $subRequest = '';
        if (
            !empty($filter->getStates())
            && !$filter->hasState(ResourceFilter::STATE_ALL)
        ) {
            $sqlState = [];
            $sqlStateCatalog = [
                ResourceFilter::STATE_RESOURCES_PROBLEMS => 'resources.status != 0 AND resources.status != 4',
                ResourceFilter::STATE_UNHANDLED_PROBLEMS => 'resources.status != 0 AND resources.status != 4'
                    . ' AND resources.acknowledged = 0 AND resources.in_downtime = 0'
                    . ' AND resources.status_confirmed = 1',
                ResourceFilter::STATE_ACKNOWLEDGED => 'resources.acknowledged = 1',
                ResourceFilter::STATE_IN_DOWNTIME => 'resources.in_downtime = 1',
            ];

            foreach ($filter->getStates() as $state) {
                $sqlState[] = $sqlStateCatalog[$state];
            }

            $subRequest .= ' AND (' . implode(' OR ', $sqlState) . ')';
        }

        return $subRequest;
    }

    /**
     * This adds the sub request filter on resource status
     *
     * @param ResourceFilter $filter
     * @return string
     */
    private function addResourceStatusSubRequest(ResourceFilter $filter): string
    {
        $subRequest = '';
        $sqlStatuses = [];
        if (! empty($filter->getStatuses())) {
            foreach ($filter->getStatuses() as $status) {
                if (array_key_exists($status, ResourceFilter::MAP_STATUS_SERVICE)) {
                    $sqlStatuses[] = '(resources.type = ' . self::RESOURCE_TYPE_SERVICE
                        . ' OR resources.type = ' . self::RESOURCE_TYPE_METASERVICE
                        . ') AND resources.status = ' . ResourceFilter::MAP_STATUS_SERVICE[$status];
                } elseif (array_key_exists($status, ResourceFilter::MAP_STATUS_HOST)) {
                    $sqlStatuses[] = 'resources.type = ' . self::RESOURCE_TYPE_HOST
                        . ' AND resources.status = ' . ResourceFilter::MAP_STATUS_HOST[$status];
                }
            }

            $subRequest = ' AND (' . implode(' OR ', $sqlStatuses) . ')';
        }

        return $subRequest;
    }

    /**
     * This adds the sub request filter on resource status type
     *
     * @param ResourceFilter $filter
     * @return string
     */
    private function addStatusTypeSubRequest(ResourceFilter $filter): string
    {
        $subRequest = '';
        $sqlStatusTypes = [];

        if (!empty($filter->getStatusTypes())) {
            foreach ($filter->getStatusTypes() as $statusType) {
                if (array_key_exists($statusType, ResourceFilter::MAP_STATUS_TYPES)) {
                    $sqlStatusTypes[] = 'resources.status_confirmed = ' . ResourceFilter::MAP_STATUS_TYPES[$statusType];
                }
            }

            $subRequest = ' AND (' . implode(' OR ', $sqlStatusTypes) . ')';
        }

        return $subRequest;
    }

    /**
     * This adds the subrequest filter for tags (servicegroups, hostgroups)
     *
     * @param ResourceFilter $filter
     * @param StatementCollector $collector
     * @return string
     */
    public function addResourceTagsSubRequest(ResourceFilter $filter, StatementCollector $collector): string
    {
        $subRequest = '';
        $searchedTagNames = [];
        $searchedTagTypes = [];
        $searchedTags = [];

        if (! empty($filter->getHostgroupNames())) {
            $searchedTagTypes[] = Tag::HOST_GROUP_TYPE_ID;
            foreach ($filter->getHostgroupNames() as $hostgroupName) {
                $searchedTagNames[] = $hostgroupName;
            }
        }

        if (! empty($filter->getServicegroupNames())) {
            $searchedTagTypes[] = Tag::SERVICE_GROUP_TYPE_ID;
            foreach ($filter->getServicegroupNames() as $servicegroupName) {
                $searchedTagNames[] = $servicegroupName;
            }
        }

        if (! empty($filter->getServiceCategoryNames())) {
            $searchedTagTypes[] = Tag::SERVICE_CATEGORY_TYPE_ID;
            foreach ($filter->getServiceCategoryNames() as $serviceCategoryName) {
                $searchedTagNames[] = $serviceCategoryName;
            }
        }

        if (! empty($filter->getHostCategoryNames())) {
            $searchedTagTypes[] = Tag::HOST_CATEGORY_TYPE_ID;
            foreach ($filter->getHostCategoryNames() as $hostCategoryName) {
                $searchedTagNames[] = $hostCategoryName;
            }
        }

        if (! empty($searchedTagNames)) {
            foreach ($searchedTagNames as $index => $name) {
                $key = ":tagName_{$index}";
                $searchedTags[] = $key;
                $collector->addValue($key, $name, \PDO::PARAM_STR);
            }

            $subRequest = ' AND
                EXISTS (
                    SELECT 1 FROM `:dbstg`.resources_tags AS rtags
                    WHERE (rtags.resource_id = resources.resource_id OR rtags.resource_id = parent_resource.resource_id)
                    AND EXISTS (
                        SELECT 1 FROM `:dbstg`.tags
                        WHERE tags.tag_id = rtags.tag_id AND tags.name IN (' . implode(', ', $searchedTags) . ')
                        AND tags.type IN (' . implode(', ', $searchedTagTypes) . ')
                        LIMIT 1
                    )
                    LIMIT 1
                )';
        }

        return $subRequest;
    }

    /**
     * This adds the subrequest filter for Monitoring Server
     *
     * @param ResourceFilter $filter
     * @param StatementCollector $collector
     * @return string
     */
    private function addMonitoringServerSubRequest(ResourceFilter $filter, StatementCollector $collector): string
    {
        $subRequest = '';
        if (! empty($filter->getMonitoringServerNames())) {
            $monitoringServerNames = [];

            foreach ($filter->getMonitoringServerNames() as $index => $monitoringServerName) {
                $key = ":monitoringServerName_{$index}";

                $monitoringServerNames[] = $key;
                $collector->addValue($key, $monitoringServerName, \PDO::PARAM_STR);
            }

            $subRequest .= ' AND instances.name IN (' . implode(', ', $monitoringServerNames) . ')';
        }

        return $subRequest;
    }

    /**
     * Get icons for resources
     *
     * @param array<int, int|null> $iconIds
     * @return array<int, array<string, string>>
     */
    private function getIconsDataForResources(array $iconIds): array
    {
        $icons = [];
        if (! empty($iconIds)) {
            $request = 'SELECT
                img_id AS `icon_id`,
                img_name AS `icon_name`,
                img_path AS `icon_path`,
                imgd.dir_name AS `icon_directory`
            FROM `:db`.view_img img
            LEFT JOIN `:db`.view_img_dir_relation imgdr
                ON imgdr.img_img_id = img.img_id
            INNER JOIN `:db`.view_img_dir imgd
                ON imgd.dir_id = imgdr.dir_dir_parent_id
            WHERE img.img_id IN (' . str_repeat('?, ', count($iconIds) - 1) . '?)';

            $statement = $this->db->prepare($this->translateDbName($request));
            $statement->execute(array_values($iconIds));

            while ($record = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $icons[(int) $record['icon_id']] = [
                    'name' => $record['icon_name'],
                    'url' => $record['icon_directory'] . DIRECTORY_SEPARATOR . $record['icon_path']
                ];
            }
        }

        return $icons;
    }
}

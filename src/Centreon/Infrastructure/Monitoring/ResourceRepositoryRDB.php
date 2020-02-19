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
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\ResourceSeverity;
use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use PDO;

/**
 * Database repository for the real time monitoring of services and host.
 *
 * @package Centreon\Infrastructure\Monitoring
 */
final class ResourceRepositoryRDB extends AbstractRepositoryDRB implements ResourceRepositoryInterface
{
    /**
     * @var string Name of the configuration database
     */
    private $centreonDbName;

    /**
     * @var string Name of the storage database
     */
    private $storageDbName;

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
            ->setConcordanceStrictMode(
                RequestParameters::CONCORDANCE_MODE_STRICT
            );
    }

    /**
     * @inheritDoc
     */
    public function filterByAccessGroups(?array $accessGroups): self
    {
        $this->accessGroups = $accessGroups;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function findResources(): array
    {
        $resources = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $resources;
        }

        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'resourece.id',
            'name' => 'resourece.name',
            'status' => 'resource.status_id',
        ]);

        /*
            . 'resource.id, resource.type, resource.name, resource.detailsUrl, resource.icon, '
            . 'resource.parent, resource.status, resource.inDowntime, resource.acknowledged, '
            . 'resource.severity, resource.impactedResourcesCount, resource.actionUrl, '
            . 'IF(resource.lastStatusChange, FROM_UNIXTIME(resource.lastStatusChange), NULL) AS `lastStatusChange`, resource.tries, '
            . 'IF(resource.lastCheck, FROM_UNIXTIME(resource.lastCheck), NULL) AS `lastCheck`, resource.information '
         */

        $collector = new StatementCollector;
        $request = $this->translateDbName('SELECT SQL_CALC_FOUND_ROWS '
            . 'resource.id, resource.type, resource.name, resource.action_url, resource.details_url, '
            . 'resource.status_code, resource.status_name, ' // status
            . 'resource.icon_name, resource.icon_url, ' // icon
            . 'resource.parent_id, resource.parent_name, resource.parent_details_url, ' // parent
            . 'resource.parent_icon_name, resource.parent_icon_url, ' // parent icon
            . 'resource.severity_level, resource.severity_url, resource.severity_name, ' // severity
            . 'resource.in_downtime, resource.acknowledged, '
            . 'resource.impacted_resources_count, resource.last_status_change, '
            . 'resource.tries, resource.last_check, resource.information '
            . 'FROM (('
            . $this->preapreQueryForServiceResources($collector)
            .') UNION ALL ('
            . $this->preapreQueryForHostResources($collector)
            .')) AS  `resource`');

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= $searchRequest ? $searchRequest : '';

        // Group
        $request .= ' GROUP BY resource.id';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= $sortRequest ? $sortRequest : ' ORDER BY resource.status_code ASC';

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

    protected function preapreQueryForServiceResources(StatementCollector $collector): string
    {
        $collector->addValue(':serviceCustomVariablesName', 'CRITICALITY_LEVEL');

        return "SELECT
		CONCAT('S', s.service_id) AS `id`,
        'service' AS `type`,
        s.service_id AS `origin_id`,
		s.description AS `name`,
		s.action_url AS `action_url`,
		s.notes_url AS `details_url`,
		s.icon_image_alt AS `icon_name`,
		s.icon_image AS `icon_url`,
		CONCAT('H', sh.host_id) AS `parent_id`,
		sh.name AS `parent_name`,
		sh.notes_url AS `parent_details_url`,
		sh.icon_image_alt AS `parent_icon_name`,
		sh.icon_image AS `parent_icon_url`,
		s.state AS `status_code`,
		CASE
            WHEN s.state = 0 THEN 'OK'
            WHEN s.state = 1 THEN 'WARNING'
            WHEN s.state = 2 THEN 'CRITICAL'
            WHEN s.state = 3 THEN 'UNKNOWN'
            WHEN s.state = 4 THEN 'PENDING'
        END AS `status_name`,
		s.scheduled_downtime_depth AS `in_downtime`,
		s.acknowledged AS `acknowledged`,
		service_cvl.value AS `severity_level`,
		sc.sc_name AS `severity_name`,
		CONCAT(service_vid.dir_alias, IF(service_vid.dir_alias, '/', NULL), service_vi.img_path) AS `severity_url`,
		NULL AS `impacted_resources_count`,
		s.last_state_change AS `last_status_change`,
		CONCAT(s.check_attempt, '/', s.max_check_attempts, ' ', CASE
            WHEN s.state_type = 1 THEN 'H'
            WHEN s.state_type = 1 THEN 'S'
        END) AS `tries`,
		s.last_check AS `last_check`,
		s.output AS `information`
        FROM `:dbstg`.`services` AS s
        INNER JOIN `:dbstg`.`hosts` sh ON sh.host_id = s.host_id AND sh.state = 0
        LEFT JOIN `:dbstg`.`customvariables` AS service_cvl ON service_cvl.host_id = s.host_id
            AND service_cvl.service_id = s.service_id
            AND service_cvl.name = :serviceCustomVariablesName
        LEFT JOIN `:db`.`service_categories_relation` AS scr ON scr.service_service_id = s.service_id
        LEFT JOIN `:db`.`service_categories` AS sc ON sc.sc_id = scr.sc_id
            AND sc.level IS NOT NULL
            AND sc.icon_id IS NOT NULL
        LEFT JOIN `:db`.`view_img` AS service_vi ON service_vi.img_id = sc.icon_id
        LEFT JOIN `:db`.`view_img_dir_relation` AS service_vidr ON service_vidr.img_img_id = service_vi.img_id
        LEFT JOIN `:db`.`view_img_dir` AS service_vid ON service_vid.dir_id = service_vidr.dir_dir_parent_id
        GROUP BY s.service_id";
    }

    protected function preapreQueryForHostResources(StatementCollector $collector): string
    {
        $collector->addValue(':hostCustomVariablesName', 'CRITICALITY_LEVEL');

        return "SELECT
		CONCAT('H', h.host_id) AS `id`,
        'host' AS `type`,
        h.host_id AS `origin_id`,
		h.name AS `name`,
		h.action_url AS `action_url`,
		h.notes_url AS `details_url`,
		h.icon_image_alt AS `icon_name`,
		h.icon_image AS `icon_url`,
		NULL AS `parent_id`,
		NULL AS `parent_name`,
		NULL AS `parent_details_url`,
		NULL AS `parent_icon_name`,
		NULL AS `parent_icon_url`,
		h.state AS `status_code`,
		CASE
            WHEN h.state = 0 THEN 'UP'
            WHEN h.state = 1 THEN 'DOWN'
            WHEN h.state = 2 THEN 'UNREACHABLE'
            WHEN h.state = 3 THEN 'PENDING'
        END AS `status_name`,
		h.scheduled_downtime_depth AS `in_downtime`,
		h.acknowledged AS `acknowledged`,
		host_cvl.value AS `severity_level`,
		hc.hc_comment AS `severity_name`,
		CONCAT(host_vid.dir_alias, '/', host_vi.img_path) AS `severity_url`,
		NULL AS `impacted_resources_count`,
		h.last_state_change AS `last_status_change`,
		CONCAT(h.check_attempt, '/', h.max_check_attempts, ' ', CASE
            WHEN h.state_type = 1 THEN 'H'
            WHEN h.state_type = 1 THEN 'S'
        END) AS `tries`,
		h.last_check AS `last_check`,
		h.output AS `information`
        FROM `:dbstg`.`hosts` AS h
        LEFT JOIN `:dbstg`.`customvariables` AS host_cvl ON host_cvl.host_id = h.host_id
            AND host_cvl.service_id = 0
            AND host_cvl.name = :hostCustomVariablesName
        LEFT JOIN `:db`.`hostcategories_relation` AS hcr ON hcr.host_host_id = h.host_id
        LEFT JOIN `:db`.`hostcategories` AS hc ON hc.hc_id = hcr.hostcategories_hc_id
            AND hc.level IS NOT NULL
            AND hc.icon_id IS NOT NULL
        LEFT JOIN `:db`.`view_img` AS host_vi ON host_vi.img_id = hc.icon_id
        LEFT JOIN `:db`.`view_img_dir_relation` AS host_vidr ON host_vidr.img_img_id = host_vi.img_id
        LEFT JOIN `:db`.`view_img_dir` AS host_vid ON host_vid.dir_id = host_vidr.dir_dir_parent_id
        GROUP BY h.host_id
";
    }

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

        if ($severity->getLevel() || $severity->getName() || $severity->getUrl()) {
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

            $resource->setParent($parent);
        }

        return $resource;
    }

    private function isAdmin(): bool
    {
        return ($this->contact !== null)
            ? $this->contact->isAdmin()
            : false;
    }

    /**
     * {@inheritDoc}
     */
    public function setContact(ContactInterface $contact): self
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

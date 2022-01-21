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

use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Monitoring\ServiceGroup;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * Database repository for the real time monitoring of services and host.
 *
 * @package Centreon\Infrastructure\Monitoring
 */
final class MonitoringRepositoryRDB extends AbstractRepositoryDRB implements MonitoringRepositoryInterface
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

    /**
     * MonitoringRepositoryRDB constructor.
     *
     * @param DatabaseConnection $pdo
     */
    public function __construct(DatabaseConnection $pdo)
    {
        $this->db = $pdo;
        $this->centreonDbName = $this->db->getCentreonDbName();
        $this->storageDbName = $this->db->getStorageDbName();
    }

    /**
     * @inheritDoc
     */
    public function filterByAccessGroups(?array $accessGroups): MonitoringRepositoryInterface
    {
        $this->accessGroups = $accessGroups;
        return $this;
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
    public function findHosts(): array
    {
        $hosts = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $hosts;
        }

        $this->sqlRequestTranslator->setConcordanceArray([
            'host.id' => 'h.host_id',
            'host.name' => 'h.name',
            'host.alias' => 'h.alias',
            'host.address' => 'h.address',
            'host.last_state_change' => 'h.last_state_change',
            'host.state' => 'h.state',
            'poller.id' => 'h.instance_id',
            'service.display_name' => 'srv.display_name',
            'host_group.id' => 'hg.hostgroup_id',
            'host.is_acknowledged' => 'h.acknowledged',
            'host.downtime' => 'h.scheduled_downtime_depth',
            'host.criticality' => 'cv.value'
        ]);

        $accessGroupFilter = '';

        if ($this->isAdmin() === false) {
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $this->accessGroups
            );

            $accessGroupFilter = ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = h.host_id
                AND acl.service_id IS NULL
                AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT
              h.*,
              cv.value AS criticality,
              i.name AS poller_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS state
            FROM `:dbstg`.`instances` i
            INNER JOIN `:dbstg`.`hosts` h
              ON h.instance_id = i.instance_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\''
            . $accessGroupFilter
            . ' LEFT JOIN `:dbstg`.`services` srv
              ON srv.host_id = h.host_id
              AND srv.enabled = \'1\'
            LEFT JOIN `:dbstg`.`hosts_hostgroups` hg
              ON hg.host_id = h.host_id
            LEFT JOIN `:dbstg`.`customvariables` cv
            ON (cv.host_id = h.host_id AND cv.service_id IS NULL AND cv.name = \'CRITICALITY_LEVEL\')';

        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Group
        $request .= ' GROUP BY h.host_id';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY h.name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new \Exception(_('Bad SQL request'));
        }

        $result = $this->db->query('SELECT FOUND_ROWS()');

        if ($result !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal(
                (int) $result->fetchColumn()
            );
        }

        $hostIds = [];

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostIds[] = (int)$result['host_id'];
            $hosts[] = EntityCreator::createEntityByArray(
                Host::class,
                $result
            );
        }

        return $hosts;
    }

    /**
     * @inheritDoc
     */
    public function findHostsByHostsGroups(array $hostsGroupsIds): array
    {
        if ($this->hasNotEnoughRightsToContinue() || empty($hostsGroupsIds)) {
            return [];
        }

        $accessGroupFilter = '';

        if ($this->isAdmin() === false) {
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $this->accessGroups
            );

            $accessGroupFilter = ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = h.host_id
                AND acl.service_id IS NULL
                AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT
              hg.hostgroup_id, h.*, i.name AS poller_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS state
            FROM `:dbstg`.`instances` i
            INNER JOIN `:dbstg`.`hosts` h
              ON h.instance_id = i.instance_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\''
            . $accessGroupFilter
            . ' LEFT JOIN `:dbstg`.`services` srv
              ON srv.host_id = h.host_id
              AND srv.enabled = \'1\'
            LEFT JOIN `:dbstg`.`hosts_hostgroups` hg
              ON hg.host_id = h.host_id
              AND hg.hostgroup_id IN (' . str_repeat('?,', count($hostsGroupsIds) - 1)
            . '?) ORDER BY h.name ASC';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $statement->execute($hostsGroupsIds);

        $hostsByHostsGroupsId = [];

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostsByHostsGroupsId[(int) $result['hostgroup_id']][] =
                EntityCreator::createEntityByArray(
                    Host::class,
                    $result
                );
        }

        return $hostsByHostsGroupsId;
    }

    /**
     * @inheritDoc
     */
    public function findHostsByServiceGroups(array $servicesGroupsIds): array
    {
        if ($this->hasNotEnoughRightsToContinue() || empty($servicesGroupsIds)) {
            return [];
        }

        $accessGroupFilter = '';

        if ($this->isAdmin() === false) {
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $this->accessGroups
            );

            $accessGroupFilter = ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = h.host_id
                AND acl.service_id = srv.service_id
                AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT
              ssg.servicegroup_id, h.*, i.name AS poller_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS state
            FROM `:dbstg`.`instances` i
            INNER JOIN `:dbstg`.`hosts` h
              ON h.instance_id = i.instance_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\'
            INNER JOIN `:dbstg`.`services` srv
              ON srv.host_id = h.host_id
              AND srv.enabled = \'1\''
            . $accessGroupFilter
            . ' LEFT JOIN `:dbstg`.`services_servicegroups` ssg
              ON ssg.host_id = h.host_id
              AND ssg.servicegroup_id IN (' . str_repeat('?,', count($servicesGroupsIds) - 1)
            . '?) ORDER BY h.name ASC';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $statement->execute($servicesGroupsIds);

        $hostsByServicesGroupsId = [];

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostsByServicesGroupsId[(int) $result['servicegroup_id']][] =
                EntityCreator::createEntityByArray(
                    Host::class,
                    $result
                );
        }

        return $hostsByServicesGroupsId;
    }

    /**
     * @inheritDoc
     */
    public function findHostGroups(?int $hostId): array
    {
        $hostGroups = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $hostGroups;
        }

        $hostGroupConcordanceArray = [
            'id' => 'hg.hostgroup_id',
            'name' => 'hg.name'
        ];

        // To allow to find host groups relating to host information
        $hostConcordanceArray = [
            'host.id' => 'h.host_id',
            'host.name' => 'h.name',
            'host.alias' => 'h.alias',
            'host.address' => 'h.address',
            'host.display_name' => 'h.display_name',
            'host.state' => 'h.state',
            'poller.id' => 'h.instance_id'
        ];

        $searchParameters = $this->sqlRequestTranslator->getRequestParameters()->extractSearchNames();

        $shouldJoinHost = false;
        if (count(array_intersect($searchParameters, array_keys($hostConcordanceArray))) > 0) {
            $shouldJoinHost = true;
            $hostGroupConcordanceArray = array_merge($hostGroupConcordanceArray, $hostConcordanceArray);
        }

        //if the filter is for specific host id, remove it from search parameters
        if (null !== $hostId) {
            $shouldJoinHost = true;
            unset($hostConcordanceArray['host.id']);
        }

        $this->sqlRequestTranslator->setConcordanceArray($hostGroupConcordanceArray);

        $sqlExtraParameters = [];
        $subRequest = '';
        if (!$this->isAdmin()) {
            $sqlExtraParameters = [':contact_id' => [\PDO::PARAM_INT => $this->contact->getId()]];

            // Not an admin, we must to filter on contact
            $subRequest .=
                ' INNER JOIN `:db`.acl_resources_hg_relations hgr
                    ON hgr.hg_hg_id = hg.hostgroup_id
                INNER JOIN `:db`.acl_resources res
                    ON res.acl_res_id = hgr.acl_res_id
                    AND res.acl_res_activate = \'1\'
                INNER JOIN `:db`.acl_res_group_relations rgr
                    ON rgr.acl_res_id = res.acl_res_id
                INNER JOIN `:db`.acl_groups grp
                    ON grp.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups)
                . ') AND grp.acl_group_activate = \'1\'
                    AND grp.acl_group_id = rgr.acl_group_id
                LEFT JOIN `:db`.acl_group_contacts_relations gcr
                    ON gcr.acl_group_id = grp.acl_group_id
                LEFT JOIN `:db`.acl_group_contactgroups_relations gcgr
                    ON gcgr.acl_group_id = grp.acl_group_id
                LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                    ON cgcr.contactgroup_cg_id = gcgr.cg_cg_id
                    AND cgcr.contact_contact_id = :contact_id 
                    OR gcr.contact_contact_id = :contact_id';
        }

        // This join will only be added if a search parameter corresponding to one of the host parameter
        if ($shouldJoinHost) {
            $subRequest .=
                ' INNER JOIN `:dbstg`.hosts_hostgroups hhg 
                    ON hhg.hostgroup_id = hg.hostgroup_id
                INNER JOIN `:dbstg`.hosts h
                    ON h.host_id = hhg.host_id
                    AND h.enabled = \'1\'
                    AND h.name NOT LIKE \'_Module_BAM%\'';

            if (!$this->isAdmin()) {
                $subRequest .=
                    ' INNER JOIN `:dbstg`.`centreon_acl` acl
                        ON acl.host_id = h.host_id
                        AND acl.service_id IS NULL
                        AND acl.group_id = grp.acl_group_id';
            }
        }

        $request = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT hg.* FROM `:dbstg`.`hostgroups` hg ' . $subRequest;
        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();

        //if host id is provided, filter results by it
        if (null !== $hostId) {
            $searchByHostIdQuery = !is_null($searchRequest) ? ' AND h.host_id = :hostId' : ' WHERE h.host_id = :hostId';
        } else {
            $searchByHostIdQuery = '';
        }

        $request .= !is_null($searchRequest) ? $searchRequest : '';
        $request .= $searchByHostIdQuery;

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY hg.name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        // We bind extra parameters according to access rights
        foreach ($sqlExtraParameters as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        if (null !== $hostId) {
            //bind the host id to search for it if provided
            $statement->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        }

        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');

        if ($result !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal(
                (int) $result->fetchColumn()
            );
        }

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostGroups[] = EntityCreator::createEntityByArray(
                HostGroup::class,
                $result
            );
        }

        return $hostGroups;
    }

    /**
     * @inheritDoc
     */
    public function findOneHost(int $hostId): ?Host
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return null;
        }

        $accessGroupFilter = '';

        if ($this->isAdmin() === false) {
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $this->accessGroups
            );

            $accessGroupFilter = ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = h.host_id
                AND acl.service_id IS NULL
                AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';
        }

        $request =
            'SELECT h.*,
            i.name AS poller_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS state,
            host_cvl.value AS `criticality`
            FROM `:dbstg`.`instances` i
            INNER JOIN `:dbstg`.`hosts` h
              ON h.instance_id = i.instance_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\'
            LEFT JOIN `:dbstg`.`customvariables` AS host_cvl ON host_cvl.host_id = h.host_id
              AND host_cvl.service_id = 0 AND host_cvl.name = "CRITICALITY_LEVEL"'
            . $accessGroupFilter .
            ' WHERE h.host_id = :host_id';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);

        if ($statement->execute()) {
            if (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
                $host = EntityCreator::createEntityByArray(
                    Host::class,
                    $row
                );

                //get services for host
                $servicesByHost = $this->findServicesByHosts([$hostId]);
                $host->setServices(!empty($servicesByHost[$hostId]) ? $servicesByHost[$hostId] : []);

                //get downtimes for host
                $downtimes = $this->findDowntimes($hostId, 0);
                $host->setDowntimes($downtimes);

                //get active acknowledgment for host
                if ($host->getAcknowledged()) {
                    $acknowledgements = $this->findAcknowledgements($hostId, 0);
                    if (!empty($acknowledgements)) {
                        $host->setAcknowledgement($acknowledgements[0]);
                    }
                }

                return $host;
            } else {
                return null;
            }
        } else {
            throw new \Exception(_('Bad SQL request'));
        }
    }

    /**
     * @inheritDoc
     */
    public function findServicesByIdsForAdminUser(array $serviceIds): array
    {
        $services = [];

        $collector = new StatementCollector();

        if (empty($serviceIds)) {
            return $services;
        }

        $request =
            'SELECT DISTINCT srv.*,
              h.host_id AS `host_host_id`, h.name AS `host_name`, h.alias AS `host_alias`,
              h.instance_id AS `host_poller_id`, srv.state AS `status_code`,
              CASE
                WHEN srv.state = 0 THEN "' . ResourceStatus::STATUS_NAME_OK . '"
                WHEN srv.state = 1 THEN "' . ResourceStatus::STATUS_NAME_WARNING . '"
                WHEN srv.state = 2 THEN "' . ResourceStatus::STATUS_NAME_CRITICAL . '"
                WHEN srv.state = 3 THEN "' . ResourceStatus::STATUS_NAME_UNKNOWN . '"
                WHEN srv.state = 4 THEN "' . ResourceStatus::STATUS_NAME_PENDING . '"
              END AS `status_name`,
              CASE
                WHEN srv.state = 0 THEN ' . ResourceStatus::SEVERITY_OK . '
                WHEN srv.state = 1 THEN ' . ResourceStatus::SEVERITY_MEDIUM . '
                WHEN srv.state = 2 THEN ' . ResourceStatus::SEVERITY_HIGH . '
                WHEN srv.state = 3 THEN ' . ResourceStatus::SEVERITY_LOW . '
                WHEN srv.state = 4 THEN ' . ResourceStatus::SEVERITY_PENDING . '
              END AS `status_severity_code`
            FROM `:dbstg`.services srv
            LEFT JOIN `:dbstg`.hosts h
              ON h.host_id = srv.host_id
              WHERE srv.enabled = \'1\'
              AND h.enabled = \'1\'';

        $idsListKey = [];
        foreach ($serviceIds as $index => $hostServiceIds) {
            $hostKey = ":host_id{$index}";
            $hostIdsListKey[] = $hostKey;
            $serviceKey = ":service_id{$index}";
            $serviceIdsListKey[] = $serviceKey;
            $collector->addValue($serviceKey, $hostServiceIds['service_id'], \PDO::PARAM_INT);
            $collector->addValue($hostKey, $hostServiceIds['host_id'], \PDO::PARAM_INT);
        }
        $request .= ' AND srv.service_id IN (' . implode(',', $serviceIdsListKey) . ')';
        $request .= ' AND srv.host_id IN (' . implode(',', $hostIdsListKey) . ')';

        $request .= ' GROUP BY srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $collector->bind($statement);
        $statement->execute();

        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $service = EntityCreator::createEntityByArray(
                Service::class,
                $row
            );
            $service->setHost(
                EntityCreator::createEntityByArray(Host::class, $row, 'host_')
            );
            $services[] = $service;
        }
        return $services;
    }

    /**
     * @inheritDoc
     */
    public function findHostsByIdsForAdminUser(array $hostIds): array
    {
        $hosts = [];

        if (empty($hostIds)) {
            return $hosts;
        }

        $collector = new StatementCollector();

        $request =
            'SELECT DISTINCT h.*,
            i.name AS poller_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS state
            FROM `:dbstg`.`instances` i
            INNER JOIN `:dbstg`.`hosts` h
              ON h.instance_id = i.instance_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\'';

        $idsListKey = [];
        foreach ($hostIds as $index => $id) {
            $key = ":id{$index}";
            $idsListKey[] = $key;
            $collector->addValue($key, $id, \PDO::PARAM_INT);
        }
        $request .= ' WHERE h.host_id IN (' . implode(',', $idsListKey) . ')';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $collector->bind($statement);
        $statement->execute();

        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hosts[] = EntityCreator::createEntityByArray(
                Host::class,
                $row
            );
        }

        return $hosts;
    }

    /**
     * @inheritDoc
     */
    public function findHostsByIdsForNonAdminUser(array $hostIds): array
    {
        $hosts = [];

        if (empty($hostIds)) {
            return $hosts;
        }

        $collector = new StatementCollector();

        $accessGroupFilter =
            ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = h.host_id
                AND acl.service_id IS NULL
            INNER JOIN `:db`.`acl_groups` acg
                ON acg.acl_group_id = acl.group_id
                AND acg.acl_group_activate = \'1\'
                AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT DISTINCT h.*,
            i.name AS poller_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS state
            FROM `:dbstg`.`instances` i
            INNER JOIN `:dbstg`.`hosts` h
              ON h.instance_id = i.instance_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\''
            . $accessGroupFilter;

        $idsListKey = [];
        foreach ($hostIds as $index => $id) {
            $key = ":id{$index}";
            $idsListKey[] = $key;
            $collector->addValue($key, $id, \PDO::PARAM_INT);
        }

        $request .= ' WHERE h.host_id IN (' . implode(',', $idsListKey) . ')';
        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $collector->bind($statement);
        $statement->execute();

        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hosts[] = EntityCreator::createEntityByArray(
                Host::class,
                $row
            );
        }

        return $hosts;
    }

    /**
     * @inheritDoc
     */
    public function findOneService(int $hostId, int $serviceId): ?Service
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return null;
        }

        $accessGroupFilter = '';

        if ($this->isAdmin() === false) {
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $this->accessGroups
            );

            $accessGroupFilter = ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = h.host_id
                AND acl.service_id = srv.service_id
                AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';
        }

        $request =
            'SELECT DISTINCT srv.*, h.host_id AS `host_host_id`,
              srv.state AS `status_code`,
              CASE
                WHEN srv.state = 0 THEN "' . ResourceStatus::STATUS_NAME_OK . '"
                WHEN srv.state = 1 THEN "' . ResourceStatus::STATUS_NAME_WARNING . '"
                WHEN srv.state = 2 THEN "' . ResourceStatus::STATUS_NAME_CRITICAL . '"
                WHEN srv.state = 3 THEN "' . ResourceStatus::STATUS_NAME_UNKNOWN . '"
                WHEN srv.state = 4 THEN "' . ResourceStatus::STATUS_NAME_PENDING . '"
              END AS `status_name`,
              CASE
                WHEN srv.state = 0 THEN ' . ResourceStatus::SEVERITY_OK . '
                WHEN srv.state = 1 THEN ' . ResourceStatus::SEVERITY_MEDIUM . '
                WHEN srv.state = 2 THEN ' . ResourceStatus::SEVERITY_HIGH . '
                WHEN srv.state = 3 THEN ' . ResourceStatus::SEVERITY_LOW . '
                WHEN srv.state = 4 THEN ' . ResourceStatus::SEVERITY_PENDING . '
              END AS `status_severity_code`,
              service_cvl.value AS `criticality`
            FROM `:dbstg`.services srv
            LEFT JOIN `:dbstg`.hosts h
              ON h.host_id = srv.host_id
            LEFT JOIN `:dbstg`.`customvariables` AS service_cvl ON service_cvl.host_id = srv.host_id
              AND service_cvl.service_id = srv.service_id AND service_cvl.name = "CRITICALITY_LEVEL"'
            . $accessGroupFilter
            . ' WHERE srv.enabled = \'1\'
              AND h.enabled = \'1\'
              AND srv.service_id = :service_id
              AND srv.host_id = :host_id
            GROUP BY srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $statement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);

        $statement->execute();

        if (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $service = EntityCreator::createEntityByArray(
                Service::class,
                $row
            );

            $service->setStatus(EntityCreator::createEntityByArray(
                ResourceStatus::class,
                $row,
                'status_'
            ));

            $service->setHost(
                EntityCreator::createEntityByArray(Host::class, $row, 'host_')
            );
        } else {
            return null;
        }

        //get downtimes for service
        $downtimes = $this->findDowntimes($hostId, $serviceId);
        $service->setDowntimes($downtimes);

        //get active acknowledgment for service
        if ($service->isAcknowledged()) {
            $acknowledgements = $this->findAcknowledgements($hostId, $serviceId);

            if (!empty($acknowledgements)) {
                $service->setAcknowledgement($acknowledgements[0]);
            }
        }

        return $service;
    }

    /**
     * @inheritDoc
     */
    public function findOneServiceByDescription(string $serviceDescription): ?Service
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return null;
        }

        $accessGroupFilter = '';

        if ($this->isAdmin() === false) {
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $this->accessGroups
            );

            $accessGroupFilter = ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = h.host_id
                AND acl.service_id = srv.service_id
                AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';
        }

        $request =
            'SELECT DISTINCT srv.*, h.host_id AS `host_host_id`,
              srv.state AS `status_code`,
              CASE
                WHEN srv.state = 0 THEN "' . ResourceStatus::STATUS_NAME_OK . '"
                WHEN srv.state = 1 THEN "' . ResourceStatus::STATUS_NAME_WARNING . '"
                WHEN srv.state = 2 THEN "' . ResourceStatus::STATUS_NAME_CRITICAL . '"
                WHEN srv.state = 3 THEN "' . ResourceStatus::STATUS_NAME_UNKNOWN . '"
                WHEN srv.state = 4 THEN "' . ResourceStatus::STATUS_NAME_PENDING . '"
              END AS `status_name`,
              CASE
                WHEN srv.state = 0 THEN ' . ResourceStatus::SEVERITY_OK . '
                WHEN srv.state = 1 THEN ' . ResourceStatus::SEVERITY_MEDIUM . '
                WHEN srv.state = 2 THEN ' . ResourceStatus::SEVERITY_HIGH . '
                WHEN srv.state = 3 THEN ' . ResourceStatus::SEVERITY_LOW . '
                WHEN srv.state = 4 THEN ' . ResourceStatus::SEVERITY_PENDING . '
              END AS `status_severity_code`
            FROM `:dbstg`.services srv
            LEFT JOIN `:dbstg`.hosts h
              ON h.host_id = srv.host_id'
            . $accessGroupFilter
            . ' WHERE srv.enabled = \'1\'
              AND h.enabled = \'1\'
              AND srv.description = :service_description
            GROUP BY srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $statement->bindValue(':service_description', $serviceDescription, \PDO::PARAM_STR);

        $statement->execute();

        if (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $service = EntityCreator::createEntityByArray(
                Service::class,
                $row
            );

            $service->setStatus(EntityCreator::createEntityByArray(
                ResourceStatus::class,
                $row,
                'status_'
            ));

            $service->setHost(
                EntityCreator::createEntityByArray(Host::class, $row, 'host_')
            );
        } else {
            return null;
        }

        //get downtimes for service
        $downtimes = $this->findDowntimes($service->getHost()->getId(), $service->getId());
        $service->setDowntimes($downtimes);

        //get active acknowledgment for service
        if ($service->isAcknowledged()) {
            $acknowledgements = $this->findAcknowledgements($service->getHost()->getId(), $service->getId());

            if (!empty($acknowledgements)) {
                $service->setAcknowledgement($acknowledgements[0]);
            }
        }

        return $service;
    }

    /**
     * @inheritDoc
     */
    public function findServicesByIdsForNonAdminUser(array $serviceIds): array
    {
        $services = [];

        $collector = new StatementCollector();

        if (empty($serviceIds)) {
            return $services;
        }

        $accessGroupFilter =
            ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = h.host_id
                AND acl.service_id = srv.service_id
            INNER JOIN `:db`.`acl_groups` acg
                ON acg.acl_group_id = acl.group_id
                AND acg.acl_group_activate = \'1\'
                AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT DISTINCT srv.*,
              h.host_id AS `host_host_id`, h.name AS `host_name`, h.alias AS `host_alias`,
              h.instance_id AS `host_poller_id`, srv.state AS `status_code`,
              CASE
                WHEN srv.state = 0 THEN "' . ResourceStatus::STATUS_NAME_OK . '"
                WHEN srv.state = 1 THEN "' . ResourceStatus::STATUS_NAME_WARNING . '"
                WHEN srv.state = 2 THEN "' . ResourceStatus::STATUS_NAME_CRITICAL . '"
                WHEN srv.state = 3 THEN "' . ResourceStatus::STATUS_NAME_UNKNOWN . '"
                WHEN srv.state = 4 THEN "' . ResourceStatus::STATUS_NAME_PENDING . '"
              END AS `status_name`,
              CASE
                WHEN srv.state = 0 THEN ' . ResourceStatus::SEVERITY_OK . '
                WHEN srv.state = 1 THEN ' . ResourceStatus::SEVERITY_MEDIUM . '
                WHEN srv.state = 2 THEN ' . ResourceStatus::SEVERITY_HIGH . '
                WHEN srv.state = 3 THEN ' . ResourceStatus::SEVERITY_LOW . '
                WHEN srv.state = 4 THEN ' . ResourceStatus::SEVERITY_PENDING . '
              END AS `status_severity_code`
            FROM `:dbstg`.services srv
            LEFT JOIN `:dbstg`.hosts h
              ON h.host_id = srv.host_id'
            . $accessGroupFilter
            . ' WHERE srv.enabled = \'1\'
              AND h.enabled = \'1\'';

        $idsListKey = [];
        foreach ($serviceIds as $index => $hostServiceIds) {
            $hostKey = ":host_id{$index}";
            $hostIdsListKey[] = $hostKey;
            $serviceKey = ":service_id{$index}";
            $serviceIdsListKey[] = $serviceKey;
            $collector->addValue($serviceKey, $hostServiceIds['service_id'], \PDO::PARAM_INT);
            $collector->addValue($hostKey, $hostServiceIds['host_id'], \PDO::PARAM_INT);
        }
        $request .= ' AND srv.service_id IN (' . implode(',', $serviceIdsListKey) . ')';
        $request .= ' AND srv.host_id IN (' . implode(',', $hostIdsListKey) . ')';

        $request .= ' GROUP BY srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $collector->bind($statement);
        $statement->execute();

        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $service = EntityCreator::createEntityByArray(
                Service::class,
                $row
            );
            $service->setHost(
                EntityCreator::createEntityByArray(Host::class, $row, 'host_')
            );
            $services[] = $service;
        }

        return $services;
    }

    /**
     * @inheritDoc
     */
    public function findServices(): array
    {
        $services = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $services;
        }

        $this->sqlRequestTranslator->setConcordanceArray([
            'host.id' => 'h.host_id',
            'host.name' => 'h.name',
            'host.alias' => 'h.alias',
            'host.address' => 'h.address',
            'host.last_state_change' => 'h.last_state_change',
            'host.state' => 'h.state',
            'host_group.id' => 'hhg.hostgroup_id',
            'poller.id' => 'i.instance_id',
            'service.description' => 'srv.description',
            'service.display_name' => 'srv.display_name',
            'service.is_acknowledged' => 'srv.acknowledged',
            'service.last_state_change' => 'srv.last_state_change',
            'service.output' => 'srv.output',
            'service.state' => 'srv.state',
            'service_group.id' => 'ssg.servicegroup_id',
            'service.downtime' => 'srv.scheduled_downtime_depth',
            'service.criticality' => 'cv.value'
        ]);

        $accessGroupFilter = '';

        if ($this->isAdmin() === false) {
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $this->accessGroups
            );

            $accessGroupFilter = ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = srv.host_id
                AND acl.service_id = srv.service_id
                AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT
              srv.*,
              h.host_id, h.alias AS host_alias, h.name AS host_name,
              cv.value as criticality,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS host_display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS host_state,
              srv.state AS `status_code`,
              CASE
                WHEN srv.state = 0 THEN "' . ResourceStatus::STATUS_NAME_OK . '"
                WHEN srv.state = 1 THEN "' . ResourceStatus::STATUS_NAME_WARNING . '"
                WHEN srv.state = 2 THEN "' . ResourceStatus::STATUS_NAME_CRITICAL . '"
                WHEN srv.state = 3 THEN "' . ResourceStatus::STATUS_NAME_UNKNOWN . '"
                WHEN srv.state = 4 THEN "' . ResourceStatus::STATUS_NAME_PENDING . '"
              END AS `status_name`,
              CASE
                WHEN srv.state = 0 THEN ' . ResourceStatus::SEVERITY_OK . '
                WHEN srv.state = 1 THEN ' . ResourceStatus::SEVERITY_MEDIUM . '
                WHEN srv.state = 2 THEN ' . ResourceStatus::SEVERITY_HIGH . '
                WHEN srv.state = 3 THEN ' . ResourceStatus::SEVERITY_LOW . '
                WHEN srv.state = 4 THEN ' . ResourceStatus::SEVERITY_PENDING . '
              END AS `status_severity_code`
            FROM `:dbstg`.services srv'
            . $accessGroupFilter
            . ' INNER JOIN `:dbstg`.hosts h
              ON h.host_id = srv.host_id
              AND h.name NOT LIKE \'_Module_BAM%\'
              AND h.enabled = \'1\'
              AND srv.enabled = \'1\'
            INNER JOIN `:dbstg`.instances i
              ON i.instance_id = h.instance_id
            LEFT JOIN :dbstg.hosts_hostgroups hhg
              ON hhg.host_id = h.host_id
            LEFT JOIN :dbstg.hostgroups hg
              ON hg.hostgroup_id = hhg.hostgroup_id
            LEFT JOIN :dbstg.services_servicegroups ssg
              ON ssg.service_id = srv.service_id
              AND ssg.host_id = h.host_id
            LEFT JOIN :dbstg.customvariables cv
              ON (cv.service_id = srv.service_id
              AND cv.host_id = srv.host_id AND cv.name = \'CRITICALITY_LEVEL\')';

        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Group by
        $request .= ' GROUP BY srv.host_id, srv.service_id';

        // Sort by
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY h.name ASC, srv.display_name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);
        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new \Exception(_('Bad SQL request'));
        }

        $result = $this->db->query('SELECT FOUND_ROWS()');

        if ($result !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal(
                (int) $result->fetchColumn()
            );
        }

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $service = EntityCreator::createEntityByArray(
                Service::class,
                $result
            );

            $service->setStatus(EntityCreator::createEntityByArray(
                ResourceStatus::class,
                $result,
                'status_'
            ));

            $host = (new Host())
                ->setId((int) $result['host_id'])
                ->setName($result['host_name'])
                ->setAlias($result['host_alias'])
                ->setState((int) $result['host_state'])
                ->setDisplayName($result['host_display_name']);

            $service->setHost($host);
            $services[] = $service;
        }

        return $services;
    }

    /**
     * @inheritDoc
     */
    public function findServicesByHostWithRequestParameters(int $hostId): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'service.id' => 'srv.service_id',
            'service.description' => 'srv.description',
            'service.display_name' => 'srv.display_name',
            'service_group.id' => 'ssg.servicegroup_id',
            'service.is_acknowledged' => 'srv.acknowledged',
            'service.state' => 'srv.state'
        ]);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();

        // Pagination
        $paginationRequest = $this->sqlRequestTranslator->translatePaginationToSql();

        return $this->findServicesByHost($hostId, $searchRequest, $sortRequest, $paginationRequest);
    }

    /**
     * @inheritDoc
     */
    public function findServicesByHostWithoutRequestParameters(int $hostId): array
    {
        return $this->findServicesByHost($hostId, null, null, null);
    }

    /**
     * Retrieve all real time services according to ACL of contact and host id
     *
     * @param int $hostId Host ID for which we want to find services
     * @param string|null $searchRequest search request
     * @param string|null $sortRequest sort request
     * @param string|null $paginationRequest pagination request
     * @return Service[]
     * @throws \Exception
     */
    private function findServicesByHost(
        int $hostId,
        ?string $searchRequest = null,
        ?string $sortRequest = null,
        ?string $paginationRequest = null
    ): array {
        $services = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $services;
        }

        $accessGroupFilter = '';

        if ($this->isAdmin() === false) {
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $this->accessGroups
            );

            $accessGroupFilter = ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = srv.host_id
                AND acl.service_id = srv.service_id
                AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT srv.*,
              srv.state AS `status_code`,
              CASE
                WHEN srv.state = 0 THEN "' . ResourceStatus::STATUS_NAME_OK . '"
                WHEN srv.state = 1 THEN "' . ResourceStatus::STATUS_NAME_WARNING . '"
                WHEN srv.state = 2 THEN "' . ResourceStatus::STATUS_NAME_CRITICAL . '"
                WHEN srv.state = 3 THEN "' . ResourceStatus::STATUS_NAME_UNKNOWN . '"
                WHEN srv.state = 4 THEN "' . ResourceStatus::STATUS_NAME_PENDING . '"
              END AS `status_name`,
              CASE
                WHEN srv.state = 0 THEN ' . ResourceStatus::SEVERITY_OK . '
                WHEN srv.state = 1 THEN ' . ResourceStatus::SEVERITY_MEDIUM . '
                WHEN srv.state = 2 THEN ' . ResourceStatus::SEVERITY_HIGH . '
                WHEN srv.state = 3 THEN ' . ResourceStatus::SEVERITY_LOW . '
                WHEN srv.state = 4 THEN ' . ResourceStatus::SEVERITY_PENDING . '
              END AS `status_severity_code`
            FROM `:dbstg`.services srv'
            . $accessGroupFilter
            . ' INNER JOIN `:dbstg`.hosts h
              ON h.host_id = srv.host_id
              AND h.host_id = :host_id
              AND h.name NOT LIKE \'_Module_BAM%\'
              AND h.enabled = \'1\'
              AND srv.enabled = \'1\'
            INNER JOIN `:dbstg`.instances i
              ON i.instance_id = h.instance_id
            LEFT JOIN `:dbstg`.services_servicegroups ssg
              ON ssg.service_id = srv.service_id
              AND ssg.host_id = h.host_id';

        $request = $this->translateDbName($request);

        // Search
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Group
        $request .= ' GROUP BY srv.service_id';

        // Sort
        $request .= !is_null($sortRequest) ? $sortRequest : '';

        // Pagination
        $request .= !is_null($paginationRequest) ? $paginationRequest : '';

        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new \Exception(_('Bad SQL request'));
        }

        $result = $this->db->query('SELECT FOUND_ROWS()');

        if ($result !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal(
                (int) $result->fetchColumn()
            );
        }

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $service = EntityCreator::createEntityByArray(
                Service::class,
                $result
            );

            $service->setStatus(EntityCreator::createEntityByArray(
                ResourceStatus::class,
                $result,
                'status_'
            ));

            $services[] = $service;
        }

        return $services;
    }

    /**
     * @inheritDoc
     */
    public function findServiceGroups(): array
    {
        $serviceGroups = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $serviceGroups;
        }

        $serviceGroupConcordanceArray = [
            'id' => 'sg.servicegroup_id',
            'name' => 'sg.name'
        ];

        $this->sqlRequestTranslator->setConcordanceArray($serviceGroupConcordanceArray);

        $sqlExtraParameters = [];
        $subRequest = '';
        if (!$this->isAdmin()) {
            $sqlExtraParameters = [':contact_id' => [\PDO::PARAM_INT => $this->contact->getId()]];

            // Not an admin, we must to filter on contact
            $subRequest .=
                ' INNER JOIN `:db`.acl_resources_sg_relations sgr
                    ON sgr.sg_id = sg.servicegroup_id
                INNER JOIN `:db`.acl_resources res
                    ON res.acl_res_id = sgr.acl_res_id
                    AND res.acl_res_activate = \'1\'
                INNER JOIN `:db`.acl_res_group_relations rgr
                    ON rgr.acl_res_id = res.acl_res_id
                INNER JOIN `:db`.acl_groups grp
                    ON grp.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups)
                . ') AND grp.acl_group_activate = \'1\'
                    AND grp.acl_group_id = rgr.acl_group_id
                LEFT JOIN `:db`.acl_group_contacts_relations gcr
                    ON gcr.acl_group_id = grp.acl_group_id
                LEFT JOIN `:db`.acl_group_contactgroups_relations gcgr
                    ON gcgr.acl_group_id = grp.acl_group_id
                LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                    ON cgcr.contactgroup_cg_id = gcgr.cg_cg_id
                    AND cgcr.contact_contact_id = :contact_id
                    OR gcr.contact_contact_id = :contact_id';
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.*
            FROM `:dbstg`.`servicegroups` sg ' . $subRequest;
        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY sg.name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        // We bind extra parameters according to access rights
        foreach ($sqlExtraParameters as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');

        if ($result !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal(
                (int) $result->fetchColumn()
            );
        }

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $serviceGroups[] = EntityCreator::createEntityByArray(
                ServiceGroup::class,
                $result
            );
        }

        return $serviceGroups;
    }

    /**
     * @inheritDoc
     */
    public function findSelectedServicesByHost(int $hostId, array $serviceIds): array
    {
        $services = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $services;
        }

        if (empty($serviceIds)) {
            return $services;
        }
        $serviceIds = array_map(
            function ($serviceId) {
                return (int)$serviceId;
            },
            $serviceIds
        );

        $accessGroupFilter = '';

        if ($this->isAdmin() === false) {
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $this->accessGroups
            );

            $accessGroupFilter = ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = h.host_id
                AND acl.service_id = srv.service_id
                AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';
        }

        $request =
            'SELECT DISTINCT 
              srv.service_id, srv.display_name, srv.description, srv.host_id, srv.state
            FROM :dbstg.services srv
            INNER JOIN :dbstg.hosts h
              ON h.host_id = srv.host_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\''
            . $accessGroupFilter
            . ' WHERE srv.host_id = ?
              AND srv.enabled = 1
              AND srv.service_id IN (' . str_repeat('?,', count($serviceIds) - 1) . '?)
            GROUP by srv.host_id, srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);

        if (false === $statement->execute(array_merge([$hostId], $serviceIds))) {
            throw new \Exception(_('Bad SQL request'));
        }

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $services[] = EntityCreator::createEntityByArray(
                Service::class,
                $result
            );
        }

        return $services;
    }

    /**
     * @inheritDoc
     */
    public function findServicesByHosts(array $hostIds): array
    {
        $services = [];

        if ($this->hasNotEnoughRightsToContinue() || empty($hostIds)) {
            return $services;
        }

        $accessGroupFilter = '';

        if ($this->isAdmin() === false) {
            $accessGroupIds = array_map(
                function ($accessGroup) {
                    return $accessGroup->getId();
                },
                $this->accessGroups
            );

            $accessGroupFilter = ' INNER JOIN `:dbstg`.`centreon_acl` acl
                ON acl.host_id = h.host_id
                AND acl.service_id = srv.service_id
                AND acl.group_id IN (' . implode(',', $accessGroupIds) . ') ';
        }

        $request =
            'SELECT DISTINCT
                srv.service_id,
                srv.display_name,
                srv.description,
                srv.host_id,
                srv.state
            FROM :dbstg.services srv
            INNER JOIN :dbstg.hosts h
              ON h.host_id = srv.host_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\''
            . $accessGroupFilter
            . ' WHERE srv.host_id IN (' . str_repeat('?,', count($hostIds) - 1) . '?)
              AND srv.enabled = 1
            GROUP by srv.host_id, srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $statement->execute($hostIds);

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $services[(int)$result['host_id']][] = EntityCreator::createEntityByArray(
                Service::class,
                $result
            );
        }

        return $services;
    }

    /**
     * @inheritDoc
     */
    public function findServicesByServiceGroups(array $serviceGroupIds): array
    {
        $servicesByServiceGroupId = [];

        if ($this->hasNotEnoughRightsToContinue() || empty($serviceGroupIds)) {
            return $servicesByServiceGroupId;
        }

        $subRequest = (!$this->isAdmin())
            ? ' INNER JOIN `:db`.`acl_resources_sg_relations` arsr
                  ON arsr.sg_id = ssg.servicegroup_id
                INNER JOIN `:db`.`acl_resources` aclr
                    ON aclr.acl_res_id = arsr.acl_res_id
                INNER JOIN `:db`.`acl_res_group_relations` argr
                    ON argr.acl_res_id = aclr.acl_res_id
                INNER JOIN `:db`.`acl_groups` acg
                    ON acg.acl_group_id = argr.acl_group_id
                    AND acg.acl_group_activate = \'1\' '
            : ' ';

        $request =
            'SELECT DISTINCT
		        ssg.servicegroup_id,
                srv.service_id, 
                srv.display_name, 
                srv.description, 
                srv.host_id,
                srv.state
            FROM `:dbstg`.`services` srv
            INNER JOIN :dbstg.`hosts` h
              ON h.host_id = srv.host_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\'
            INNER JOIN `:dbstg`.`services_servicegroups` ssg
              ON ssg.service_id = srv.service_id
              AND ssg.host_id = srv.host_id'
            . $subRequest
            . 'WHERE ssg.servicegroup_id IN (' . str_repeat('?,', count($serviceGroupIds) - 1) . '?)
                AND srv.enabled = 1
            GROUP by ssg.servicegroup_id, srv.host_id, srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $statement->execute($serviceGroupIds);

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $service = EntityCreator::createEntityByArray(
                Service::class,
                $result
            );
            $host = EntityCreator::createEntityByArray(
                Host::class,
                $result
            );
            $service->setHost($host);
            $servicesByServiceGroupId[(int)$result['servicegroup_id']][] = $service;
        }

        return $servicesByServiceGroupId;
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     * @return ServiceGroup[]
     */
    public function findServiceGroupsByHostAndService(int $hostId, int $serviceId): array
    {
        $serviceGroups = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $serviceGroups;
        }

        $sqlExtraParameters = [];
        $subRequest = '';
        if (!$this->isAdmin()) {
            $sqlExtraParameters = [':contact_id' => [\PDO::PARAM_INT => $this->contact->getId()]];

            // Not an admin, we must to filter on contact
            $subRequest .=
                ' INNER JOIN `:db`.acl_resources_sg_relations sgr
                    ON sgr.sg_id = sg.servicegroup_id
                INNER JOIN `:db`.acl_resources res
                    ON (res.acl_res_id = sgr.acl_res_id OR res.all_servicegroups = \'1\')
                    AND res.acl_res_activate = \'1\'
                INNER JOIN `:db`.acl_res_group_relations rgr
                    ON rgr.acl_res_id = res.acl_res_id
                INNER JOIN `:db`.acl_groups grp
                    ON grp.acl_group_id IN ('
                . $this->accessGroupIdToString($this->accessGroups)
                . ') AND grp.acl_group_activate = \'1\'
                    AND grp.acl_group_id = rgr.acl_group_id
                LEFT JOIN `:db`.acl_group_contacts_relations gcr
                    ON gcr.acl_group_id = grp.acl_group_id
                LEFT JOIN `:db`.acl_group_contactgroups_relations gcgr
                    ON gcgr.acl_group_id = grp.acl_group_id
                LEFT JOIN `:db`.contactgroup_contact_relation cgcr
                    ON cgcr.contactgroup_cg_id = gcgr.cg_cg_id
                    AND cgcr.contact_contact_id = :contact_id 
                    OR gcr.contact_contact_id = :contact_id';
        }

        $subRequest .=
            ' INNER JOIN `:dbstg`.services_servicegroups ssg 
                    ON ssg.servicegroup_id = sg.servicegroup_id
                INNER JOIN `:dbstg`.hosts h
                    ON h.host_id = ssg.host_id';
        $subRequest .=
            ' LEFT JOIN `:dbstg`.`services` srv
                      ON srv.service_id = ssg.service_id
                      AND srv.host_id = h.host_id
                      AND srv.enabled = \'1\'';

        if (!$this->isAdmin()) {
            $subRequest .=
                ' INNER JOIN `:dbstg`.`centreon_acl` acl
                        ON acl.host_id = h.host_id
                        AND acl.service_id = srv.service_id
                        AND acl.group_id = grp.acl_group_id';
        }

        //define where clause to filter by host and service
        $subRequest .= ' WHERE srv.service_id = :serviceId AND srv.host_id = :hostId';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.* 
            FROM `:dbstg`.`servicegroups` sg ' . $subRequest;
        $request = $this->translateDbName($request);

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY sg.name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);

        // We bind extra parameters according to access rights
        foreach ($sqlExtraParameters as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        //bind where clause without search parameters
        $statement->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
        $statement->bindValue(':hostId', $hostId, \PDO::PARAM_INT);

        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');

        if ($result !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal(
                (int) $result->fetchColumn()
            );
        }

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $serviceGroups[] = EntityCreator::createEntityByArray(
                ServiceGroup::class,
                $result
            );
        }

        return $serviceGroups;
    }

    /**
     * @inheritDoc
     */
    public function findDowntimes(int $hostId, int $serviceId): array
    {
        $downtimes = [];

        if (empty($hostId)) {
            return $downtimes;
        }

        $sql = 'SELECT
            d.downtime_id,
            d.entry_time,
            d.host_id,
            d.service_id,
            d.author,
            d.cancelled,
            `cmts`.data AS `comment_data`,
            d.deletion_time,
            d.duration,
            d.end_time,
            d.fixed,
            d.instance_id,
            d.internal_id,
            d.start_time,
            d.actual_start_time,
            d.actual_end_time,
            d.started,
            d.triggered_by,
            d.type,
            c.contact_id AS `author_id`
        FROM `:dbstg`.`downtimes`  AS `d`
        LEFT JOIN `:db`.contact AS `c` ON c.contact_alias = d.author
        LEFT JOIN `:dbstg`.`comments` AS `cmts`
            ON `cmts`.host_id = d.host_id AND `cmts`.service_id = d.service_id
            AND `cmts`.deletion_time IS NULL
        WHERE d.host_id = :hostId AND d.service_id = :serviceId
            AND d.deletion_time IS NULL AND ((NOW() BETWEEN FROM_UNIXTIME(d.actual_start_time)
            AND FROM_UNIXTIME(d.actual_end_time)) OR ((NOW() > FROM_UNIXTIME(d.actual_start_time)
            AND d.actual_end_time IS NULL)))
        ORDER BY d.entry_time DESC';

        $request = $this->translateDbName($sql);
        $statement = $this->db->prepare($request);
        $statement->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
        $statement->execute();

        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $downtimes[] = EntityCreator::createEntityByArray(
                Downtime::class,
                $result
            );
        }

        return $downtimes;
    }

    /**
     * @inheritDoc
     */
    public function findAcknowledgements(int $hostId, int $serviceId): array
    {
        $acks = [];

        if (empty($hostId)) {
            return $acks;
        }

        $sql = 'SELECT
            a.acknowledgement_id,
            a.entry_time,
            a.host_id,
            a.service_id,
            a.author,
            `cmts`.data AS `comment_data`,
            a.deletion_time,
            a.instance_id,
            a.notify_contacts,
            a.persistent_comment,
            a.state,
            a.sticky,
            a.type,
            c.contact_id AS `author_id`
            FROM `:dbstg`.`acknowledgements` AS `a`
            LEFT JOIN `:db`.contact AS `c` ON c.contact_alias = a.author
            LEFT JOIN `:dbstg`.`comments` AS `cmts`
                ON `cmts`.host_id = a.host_id AND `cmts`.service_id = a.service_id
                AND `cmts`.deletion_time IS NULL
            WHERE a.host_id = :hostId AND a.service_id = :serviceId AND a.deletion_time IS NULL
            ORDER BY a.entry_time DESC';

        $request = $this->translateDbName($sql);
        $statement = $this->db->prepare($request);
        $statement->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
        $statement->execute();

        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $acks[] = EntityCreator::createEntityByArray(
                Acknowledgement::class,
                $result
            );
        }

        return $acks;
    }

    private function isAdmin(): bool
    {
        return ($this->contact !== null)
            ? $this->contact->isAdmin()
            : false;
    }

    /**
     * @inheritDoc
     */
    public function setContact(ContactInterface $contact): MonitoringRepositoryInterface
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

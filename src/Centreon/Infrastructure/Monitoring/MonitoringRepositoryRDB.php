<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Monitoring\ServiceGroup;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
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
            'host.state' => 'h.state',
            'poller.id' => 'h.instance_id',
            'service.display_name' => 'srv.display_name',
            'host_group.id' => 'hg.hostgroup_id']);

        $accessGroupFilter = $this->isAdmin()
            ? ''
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id IS NULL
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT 
              h.*,
              i.name AS instance_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS state
            FROM `:dbstg`.`instances` i
            INNER JOIN `:dbstg`.`hosts` h
              ON h.instance_id = i.instance_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\''
            . $accessGroupFilter
            .' LEFT JOIN `:dbstg`.`services` srv
              ON srv.host_id = h.host_id
              AND srv.enabled = \'1\'
            LEFT JOIN `:dbstg`.`hosts_hostgroups` hg
              ON hg.host_id = h.host_id';

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
            throw new \Exception('Bad SQL request');
        }

        $result = $this->db->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

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

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id IS NULL
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT 
              hg.hostgroup_id, h.*, i.name AS instance_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS state
            FROM `:dbstg`.`instances` i
            INNER JOIN `:dbstg`.`hosts` h
              ON h.instance_id = i.instance_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\''
            . $accessGroupFilter
            .' LEFT JOIN `:dbstg`.`services` srv
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

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT 
              ssg.servicegroup_id, h.*, i.name AS instance_name,
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
    public function findHostGroups(): array
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

        $request ='SELECT SQL_CALC_FOUND_ROWS DISTINCT hg.* FROM `:dbstg`.`hostgroups` hg ' . $subRequest;
        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

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
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

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

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id IS NULL
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT h.*,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS state
            FROM `:dbstg`.`hosts` h'
            . $accessGroupFilter
            . ' INNER JOIN `:dbstg`.`services` srv
              ON srv.host_id = h.host_id
              AND srv.enabled = \'1\'
            WHERE h.host_id = :host_id
              AND h.name NOT LIKE \'_Module_BAM%\'
              AND h.enabled = \'1\'';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);

        if ($statement->execute()) {
            if (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
                $host = EntityCreator::createEntityByArray(
                    Host::class,
                    $row
                );

                $servicesByHost = $this->findServicesByHosts([$hostId]);

                $host->setServices(
                    $servicesByHost[$hostId]
                );

                return $host;
            } else {
                return null;
            }
        } else {
            throw new \Exception('Bad SQL request');
        }
    }

    /**
     * @inheritDoc
     */
    public function findOneService(int $hostId, int $serviceId): ?Service
    {
        if ($this->hasNotEnoughRightsToContinue()) {
            return null;
        }

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT DISTINCT srv.*
            FROM `:dbstg`.services srv
            LEFT JOIN `:dbstg`.hosts h 
              ON h.host_id = srv.host_id'
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

        if ($statement->execute()) {
            if (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
                return EntityCreator::createEntityByArray(
                    Service::class,
                    $row
                );
            } else {
                return null;
            }
        } else {
            throw new \Exception('Bad SQL request');
        }
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
            'host.state' => 'h.state',
            'host_group.id' => 'hhg.hostgroup_id',
            'poller.id' => 'i.instance_id',
            'service.description' => 'srv.description',
            'service.display_name' => 'srv.display_name',
            'service.is_acknowledged' => 'srv.acknowledged',
            'service.output' => 'srv.output',
            'service.state' => 'srv.state',
            'service_group.id' => 'ssg.servicegroup_id',
        ]);

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = srv.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT 
              srv.*,
              h.host_id, h.alias AS host_alias, h.name AS host_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'Meta\', h.display_name) AS host_display_name,
              IF (h.display_name LIKE \'_Module_Meta%\', \'0\', h.state) AS host_state
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
              AND ssg.host_id = h.host_id';

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
            throw new \Exception('Bad SQL request');
        }

        $result = $this->db->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $service = EntityCreator::createEntityByArray(
                Service::class,
                $result
            );

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
    public function findServicesByHost(int $hostId): array
    {
        $services = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $services;
        }

        $this->sqlRequestTranslator->setConcordanceArray([
            'service.id' => 'srv.service_id',
            'service.description' => 'srv.description',
            'service.display_name' => 'srv.display_name',
            'service_group.id' => 'ssg.servicegroup_id',
            'service.is_acknowledged' => 'srv.acknowledged',
            'service.state' => 'srv.state'
        ]);

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = srv.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT srv.*
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
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Group
        $request .= ' GROUP BY srv.service_id';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest) ? $sortRequest : '';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new \Exception('Bad SQL request');
        }

        $result = $this->db->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

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
    public function findServiceGroups(): array
    {
        $serviceGroups = [];

        if ($this->hasNotEnoughRightsToContinue()) {
            return $serviceGroups;
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
            'poller.id' => 'h.instance_id'];

        $serviceConcordanceArray = [
            'service.display_name' => 'srv.display_name',
            'service_group.id' => 'sg.servicegroup_id',
            'service_group.name' => 'sg.name'
        ];

        $searchParameters = $this->sqlRequestTranslator->getRequestParameters()->extractSearchNames();

        $shouldJoinHost = false;
        if (count(array_intersect($searchParameters, array_keys($hostConcordanceArray))) > 0) {
            $shouldJoinHost = true;
            $hostGroupConcordanceArray = array_merge($hostGroupConcordanceArray, $hostConcordanceArray);
        }

        $shouldJoinService = false;
        if (count(array_intersect($searchParameters, array_keys($serviceConcordanceArray))) > 0) {
            $shouldJoinHost = true;
            $hostGroupConcordanceArray = array_merge($hostGroupConcordanceArray, $serviceConcordanceArray);
        }
        $this->sqlRequestTranslator->setConcordanceArray($hostGroupConcordanceArray);

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

        // This join will only be added if a search parameter corresponding to one of the host parameter
        if ($shouldJoinHost || $shouldJoinService) {
            $subRequest .=
                ' INNER JOIN `:dbstg`.services_servicegroups ssg 
                    ON ssg.servicegroup_id = sg.servicegroup_id
                    AND ssg.service_id = srv.service_id
                INNER JOIN `:dbstg`.hosts h
                    ON h.host_id = ssg.host_id';

            if ($shouldJoinService) {
                $subRequest .=
                    'LEFT JOIN `:dbstg`.`services` srv
                      ON srv.service_id = ssg.service_id
                      AND srv.host_id = h.host_id
                      AND srv.enabled = \'1\'';
            }

            if (!$this->isAdmin()) {
                $subRequest .=
                    ' INNER JOIN `:dbstg`.`centreon_acl` acl
                        ON acl.host_id = h.host_id
                        AND acl.service_id = srv.service_id
                        AND acl.group_id = grp.acl_group_id';
            }
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
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

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

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';


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
            throw new \Exception('Bad SQL request');
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

        $accessGroupFilter = $this->isAdmin()
            ? ' '
            : ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') ';

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
     * @param array $serviceGroups
     * @return array
     * @throws \Exception
     */
    public function findServicesByServiceGroups(array $serviceGroups): array
    {
        $servicesByServiceGroupId = [];

        if ($this->hasNotEnoughRightsToContinue() || empty($serviceGroups)) {
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
            . 'WHERE ssg.servicegroup_id IN (' . str_repeat('?,', count($serviceGroups) - 1) . '?)
                AND srv.enabled = 1
            GROUP by ssg.servicegroup_id, srv.host_id, srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->db->prepare($request);
        $statement->execute($serviceGroups);

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

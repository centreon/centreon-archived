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
     * @var bool Indicates whether the contact is an admin or not
     */
    private $isAdmin = false;

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

        if (!$this->hasEnoughRightsToContinue()) {
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

        $accessGroupFilter = !empty($this->accessGroups)
            ? ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id IS NULL
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

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
    public function findHostGroups(): array
    {
        $hostGroups = [];

        if (!$this->hasEnoughRightsToContinue()) {
            return $hostGroups;
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

        $accessGroupFilter = !empty($this->accessGroups)
            ? ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id IS NULL
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

        // First request to count the number of result for pagination
        $firstRequest =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT
              hg.hostgroup_id
            FROM `:dbstg`.`hostgroups` hg
            INNER JOIN `:dbstg`.`hosts_hostgroups` hhg
              ON hhg.hostgroup_id = hg.hostgroup_id
            INNER JOIN `:dbstg`.`hosts` h
              ON h.host_id = hhg.host_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\'
            INNER JOIN `:dbstg`.`instances` i
              ON i.instance_id = h.instance_id'
            . $accessGroupFilter
            . ' LEFT JOIN `:dbstg`.`services` srv
              ON srv.host_id = h.host_id
              AND srv.enabled = \'1\'';

        $firstRequest = $this->translateDbName($firstRequest);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $firstRequest .= !is_null($searchRequest) ? $searchRequest : '';

        // Group
        $firstRequest .= ' GROUP BY hg.hostgroup_id, hg.name, h.host_id, h.name';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $firstRequest .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY hg.name ASC';

        // Pagination
        $firstRequest .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($firstRequest);

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

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT
              hg.hostgroup_id, hg.name AS hostgroup_name,
              h.host_id, h.name as host_name, h.display_name AS host_display_name,
              h.state AS host_state, h.alias AS host_alias
            FROM `:dbstg`.`hostgroups` hg
            INNER JOIN `:dbstg`.`hosts_hostgroups` hhg
              ON hhg.hostgroup_id = hg.hostgroup_id
            INNER JOIN `:dbstg`.`hosts` h
              ON h.host_id = hhg.host_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\'
            INNER JOIN `:dbstg`.`instances` i
              ON i.instance_id = h.instance_id'
            . $accessGroupFilter
            . ' LEFT JOIN `:dbstg`.`services` srv
              ON srv.host_id = h.host_id
              AND srv.enabled = \'1\'';

        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Group
        $request .= ' GROUP BY hg.hostgroup_id, hg.name, h.host_id, h.name';

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
        if (false === $statement->execute()) {
            throw new \Exception('Bad SQL request');
        }

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostGroupId = (int) $result['hostgroup_id'];
            $host = (new Host())
                ->setId((int) $result['host_id'])
                ->setName($result['host_name'])
                ->setAlias($result['host_alias'])
                ->setState((int) $result['host_state']);

            if (!array_key_exists($hostGroupId, $hostGroups)) {
                $hostGroups[$hostGroupId] = (new HostGroup())
                    ->setId($hostGroupId)
                    ->setName($result['hostgroup_name']);
            }

            if (!$hostGroups[$hostGroupId]->isHostExists($host->getId())) {
                $hostGroups[$hostGroupId]->addHost($host);
            }
        }

        return array_values($hostGroups);
    }

    /**
     * @inheritDoc
     */
    public function findOneHost(int $hostId): ?Host
    {
        if (!$this->hasEnoughRightsToContinue()) {
            return null;
        }

        $accessGroupFilter = !empty($this->accessGroups)
            ? ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id IS NULL
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

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

                $servicesByHost = $this->findServicesOnMultipleHosts([$hostId]);

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
        if (!$this->hasEnoughRightsToContinue()) {
            return null;
        }

        $accessGroupFilter = !empty($this->accessGroups)
            ? ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

        $request =
            'SELECT DISTINCT srv.*
            FROM `:dbstg`.services srv
            LEFT JOIN `:dbstg`.hosts h 
              ON h.host_id = srv.host_id'
            . $accessGroupFilter
            . ' WHERE srv.enabled = 1
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

        if (!$this->hasEnoughRightsToContinue()) {
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

        $accessGroupFilter = !empty($this->accessGroups)
            ? ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = srv.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

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

        if (!$this->hasEnoughRightsToContinue()) {
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

        $accessGroupFilter = !empty($this->accessGroups)
            ? ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = srv.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT srv.*
            FROM `:dbstg`.services srv'
            . $accessGroupFilter
            . ' INNER JOIN `:dbstg`.hosts h
              ON h.host_id = srv.host_id
              AND h.host_id = :host_id
              AND h.name NOT LIKE \'_Module_BAM%\'
              AND h.enabled = \'1\'
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

        if (!$this->hasEnoughRightsToContinue()) {
            return $serviceGroups;
        }

        $this->sqlRequestTranslator->setConcordanceArray([
            'host.id' => 'h.host_id',
            'host.name' => 'h.name',
            'host.alias' => 'h.alias',
            'host.address' => 'h.address',
            'host.state' => 'h.state',
            'poller.id' => 'h.instance_id',
            'service.display_name' => 'srv.display_name',
            'service_group.id' => 'sg.servicegroup_id',
            'service_group.name' => 'sg.name']);

        $accessGroupFilter = !empty($this->accessGroups)
            ? ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id = ssg.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

        // First request to count the number of result for pagination
        $firstRequest =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT
              sg.servicegroup_id
            FROM `:dbstg`.`servicegroups` sg
            INNER JOIN `:dbstg`.`services_servicegroups` ssg
              ON ssg.servicegroup_id = sg.servicegroup_id
            INNER JOIN `:dbstg`.`hosts` h
              ON h.host_id = ssg.host_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\'
            INNER JOIN `:dbstg`.`instances` i
              ON i.instance_id = h.instance_id'
            . $accessGroupFilter
            . ' LEFT JOIN `:dbstg`.`services` srv
              ON srv.service_id = ssg.service_id
              AND srv.host_id = h.host_id
              AND srv.enabled = \'1\'';

        $firstRequest = $request = $this->translateDbName($firstRequest);

        // Where
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $firstRequest .= !is_null($searchRequest) ? $searchRequest : '';

        // Group
        $firstRequest .= ' GROUP BY sg.servicegroup_id, sg.name';

        // Order
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $firstRequest .= !is_null($sortRequest) ? $sortRequest : '';

        // Pagination
        $firstRequest .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($firstRequest);

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

        $request =
            'SELECT DISTINCT
              sg.servicegroup_id, sg.name AS servicegroup_name,
              h.host_id, h.name AS host_name, h.alias AS host_alias,
              h.display_name AS host_display_name, h.state AS host_state,
              GROUP_CONCAT(DISTINCT srv.service_id ORDER BY srv.display_name ASC) AS serviceIds
            FROM `:dbstg`.`servicegroups` sg
            INNER JOIN `:dbstg`.`services_servicegroups` ssg
              ON ssg.servicegroup_id = sg.servicegroup_id
            LEFT JOIN `centreon_storage`.`services` srv
              ON srv.service_id = ssg.service_id
              AND srv.enabled = \'1\'
            INNER JOIN `:dbstg`.`hosts` h
              ON h.host_id = srv.host_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\'
            INNER JOIN `:dbstg`.`instances` i
              ON i.instance_id = h.instance_id'
            . $accessGroupFilter;

        $request = $this->translateDbName($request);

        // Where
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Group
        $request .= ' GROUP BY sg.servicegroup_id, sg.name, h.host_id, h.name';

        // Order
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
        if (false === $statement->execute()) {
            throw new \Exception('Bad SQL request');
        }

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $serviceGroupId = (int) $result['servicegroup_id'];
            $host = (new Host())
                ->setId((int) $result['host_id'])
                ->setName($result['host_name'])
                ->setAlias($result['host_alias'])
                ->setDisplayName($result['host_display_name'])
                ->setState((int) $result['host_state'])
                ->setServices(
                    $this->findSelectedServicesByHost(
                        (int) $result['host_id'],
                        explode(',', $result['serviceIds'])
                    )
                );

            if (!array_key_exists($serviceGroupId, $serviceGroups)) {
                $serviceGroups[$serviceGroupId] = (new ServiceGroup())
                    ->setId($serviceGroupId)
                    ->setName($result['servicegroup_name']);
            }

            if (!$serviceGroups[$serviceGroupId]->isHostExists($host->getId())) {
                $serviceGroups[$serviceGroupId]->addHost($host);
            }
        }

        return array_values($serviceGroups);
    }

    /**
     * @inheritDoc
     */
    public function findSelectedServicesByHost(int $hostId, array $serviceIds): array
    {
        $services = [];

        if (!$this->hasEnoughRightsToContinue()) {
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

        $accessGroupFilter = !empty($this->accessGroups)
            ? ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') '
            : ' ';


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
    public function findServicesOnMultipleHosts(array $hostIds): array
    {
        $services = [];

        if (!$this->hasEnoughRightsToContinue()) {
            return $services;
        }

        if (empty($hostIds)) {
            return $services;
        }

        $accessGroupFilter = !empty($this->accessGroups)
            ? ' INNER JOIN `:dbstg`.`centreon_acl` acl
                  ON acl.host_id = h.host_id
                  AND acl.service_id = srv.service_id
                INNER JOIN `:db`.`acl_groups` acg
                  ON acg.acl_group_id = acl.group_id
                  AND acg.acl_group_activate = \'1\'
                  AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups) . ') '
            : ' ';

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

        if (false === $statement->execute($hostIds)) {
            throw new \Exception('Bad SQL request');
        }

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
    public function setAdmin(bool $isAdmin): MonitoringRepositoryInterface
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    /**
     * @return bool Return TRUE if the contact is an admin or has at least one access group.
     */
    private function hasEnoughRightsToContinue(): bool
    {
        return $this->isAdmin || count($this->accessGroups) > 0;
    }
}

<?php
declare(strict_types=1);

namespace Centreon\Infrastructure\Monitoring;

use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Monitoring\ServiceGroup;
use Centreon\Domain\Pagination\RequestParameters;
use Centreon\Infrastructure\Repository\SqlRequestParametersTranslator;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;
use \Exception;

class MonitoringRepositoryRDB implements MonitoringRepositoryInterface
{
    /**
     * @var DatabaseConnection
     */
    private $pdo;

    /**
     * @var string Name of the configuration database
     */
    private $centreonDbName;

    /**
     * @var string Name of the storage database
     */
    private $storageDbName;

    /**
     * @var AccessGroup[]
     */
    private $accessGroups = [];

    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * MonitoringRepositoryRDB constructor.
     * @param DatabaseConnection $pdo
     */
    public function __construct(DatabaseConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param int $unixTimestamp
     * @return \DateTime
     * @throws Exception
     */
    private function createDatetime(int $unixTimestamp): \DateTime
    {
        return (new \DateTime())->setTimestamp($unixTimestamp);
    }

    /**
     * @param AccessGroup[]|null $accessGroups
     * @return MonitoringRepositoryInterface
     */
    public function filterByAccessGroups(?array $accessGroups): MonitoringRepositoryInterface
    {
        $this->accessGroups = $accessGroups;
        return $this;
    }

    /**
     * @param mixed $centreonDbName
     */
    public function setCentreonDbName($centreonDbName): void
    {
        $this->centreonDbName = $centreonDbName;
    }

    /**
     * @param mixed $storageDbName
     */
    public function setStorageDbName($storageDbName): void
    {
        $this->storageDbName = $storageDbName;
    }

    /**
     * @return SqlRequestParametersTranslator
     */
    public function getSqlRequestTranslator(): SqlRequestParametersTranslator
    {
        return $this->sqlRequestTranslator;
    }

    /**
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
     * Retrieve all real time hosts.
     *
     * @return Host[]
     * @throws Exception
     */
    public function findHosts(): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'host.id' => 'h.host_id',
            'host.name' => 'h.name',
            'host.alias' => 'h.alias',
            'host.address' => 'h.address',
            'host.state' => 'h.state',
            'poller.id' => 'h.instance_id',
            'service.display_name' => 'srv.display_name',
            'host_group.id' => 'hg.hostgroup_id']);

        $accessGroupFilter = !is_null($this->accessGroups)
            ? ' AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT 
              h.*,
              i.name AS instance_name
            FROM `:dbstg`.`instances` i
            INNER JOIN `:dbstg`.`hosts` h
              ON h.instance_id = i.instance_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_%\'
            INNER JOIN `:dbstg`.`centreon_acl` acl
              ON acl.host_id = h.host_id
            INNER JOIN `:db`.`acl_groups` acg
              ON acg.acl_group_id = acl.group_id
              AND acg.acl_group_activate = \'1\''
            . $accessGroupFilter
            .'LEFT JOIN `:dbstg`.`services` srv
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

        $statement = $this->pdo->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new Exception('Bad SQL request');
        }

        $result = $this->pdo->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

        $hosts = [];
        $hostIds = [];

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostIds[] = (int)$result['host_id'];
            $hosts[] = EntityCreator::createEntityByArray(
                Host::class,
                $result
            );
        }

        if (count($hostIds) > 0) {
            $servicesByHost = $this->findServicesOnMultipleHosts($hostIds);

            foreach ($servicesByHost as $hostId => $services) {
                foreach ($hosts as $host) {
                    if ($host->getId() === $hostId) {
                        $host->setServices($services);
                        break;
                    }
                }
            }
        }

        return $hosts;
    }

    /**
     * @inheritDoc
     */
    public function findHostGroups(): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'host.id' => 'h.host_id',
            'host.name' => 'h.name',
            'host.alias' => 'h.alias',
            'host.address' => 'h.address',
            'host.state' => 'h.state',
            'poller.id' => 'h.instance_id',
            'service.display_name' => 'srv.display_name',
            'host_group.id' => 'hg.hostgroup_id']);

        $accessGroupFilter = !is_null($this->accessGroups)
            ? ' AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
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
              ON i.instance_id = h.instance_id
            INNER JOIN `:dbstg`.`centreon_acl` acl
              ON acl.host_id = h.host_id
            INNER JOIN `:db`.`acl_groups` acg
              ON acg.acl_group_id = acl.group_id
              AND acg.acl_group_activate = \'1\''
            . $accessGroupFilter
            . 'LEFT JOIN `:dbstg`.`services` srv
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

        $statement = $this->pdo->prepare($firstRequest);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new Exception('Bad SQL request');
        }

        $result = $this->pdo->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT
              hg.hostgroup_id, hg.name AS hostgroup_name,
              h.host_id, h.name AS host_name, h.state AS host_state, h.alias AS host_alias
            FROM `:dbstg`.`hostgroups` hg
            INNER JOIN `:dbstg`.`hosts_hostgroups` hhg
              ON hhg.hostgroup_id = hg.hostgroup_id
            INNER JOIN `:dbstg`.`hosts` h
              ON h.host_id = hhg.host_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\'
            INNER JOIN `:dbstg`.`instances` i
              ON i.instance_id = h.instance_id
            INNER JOIN `:dbstg`.`centreon_acl` acl
              ON acl.host_id = h.host_id
            INNER JOIN `:db`.`acl_groups` acg
              ON acg.acl_group_id = acl.group_id
              AND acg.acl_group_activate = \'1\''
            . $accessGroupFilter
            . 'LEFT JOIN `:dbstg`.`services` srv
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

        $statement = $this->pdo->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new Exception('Bad SQL request');
        }

        $hostGroups = [];
        $hostIds = [];

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $hostGroupId = (int) $result['hostgroup_id'];
            $host = (new Host())
                ->setId((int) $result['host_id'])
                ->setName($result['host_name'])
                ->setAlias($result['host_alias'])
                ->setState((int) $result['host_state']);

            if (!in_array($host->getId(), $hostIds)) {
                $hostIds[] = $host->getId();
            }

            if (!array_key_exists($hostGroupId, $hostGroups)) {
                $hostGroups[$hostGroupId] = (new HostGroup())
                    ->setId($hostGroupId)
                    ->setName($result['hostgroup_name']);
            }

            if (!$hostGroups[$hostGroupId]->isHostExists($host->getId())) {
                $hostGroups[$hostGroupId]->addHost($host);
            }
        }

        if (!empty($hostIds)) {
            $servicesByHost = $this->findServicesOnMultipleHosts($hostIds);
            foreach ($hostGroups as $hostGroupId => $hostGroup) {
                foreach ($hostGroup->getHosts() as $host) {
                    if (array_key_exists($host->getId(), $servicesByHost)) {
                        $host->setServices($servicesByHost[$host->getId()]);
                    }
                }
            }
        }

        return array_values($hostGroups);
    }

    /**
     * Find a host.
     *
     * @param int $hostId Host id
     * @return Host|null
     * @throws Exception
     */
    public function findOneHost(int $hostId): ?Host
    {
        $accessGroupFilter = !is_null($this->accessGroups)
            ? ' AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

        $request =
            'SELECT h.*
            FROM `:dbstg`.`hosts` h
            INNER JOIN `:dbstg`.`centreon_acl` acl
              ON acl.host_id = h.host_id
            INNER JOIN `:db`.`acl_groups` acg
              ON acg.acl_group_id = acl.group_id
              AND acg.acl_group_activate = \'1\''
            . $accessGroupFilter
            . 'INNER JOIN `:dbstg`.`services` srv
              ON srv.host_id = h.host_id
              AND srv.enabled = \'1\'
            WHERE h.host_id = :host_id
              AND h.name NOT LIKE \'_Module_BAM%\'
              AND h.enabled = \'1\'';

        $request = $this->translateDbName($request);

        $statement = $this->pdo->prepare($request);
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
            throw new Exception('Bad SQL request');
        }
    }

    /**
     * Find a service according to its id.
     *
     * @param int $hostId Host id of the service
     * @param int $serviceId Service Id
     * @return Service|null
     * @throws Exception
     */
    public function findOneService(int $hostId, int $serviceId): ?Service
    {
        $accessGroupFilter = !is_null($this->accessGroups)
            ? ' AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

        $request =
            'SELECT DISTINCT srv.*,
                h.host_id, h.name, h.alias
            FROM `:dbstg`.services srv
            LEFT JOIN `:dbstg`.hosts h 
              ON h.host_id = srv.host_id
            INNER JOIN `:dbstg`.centreon_acl acl
              ON acl.service_id = srv.service_id
            INNER JOIN `:db`.acl_groups acg
              ON acg.acl_group_id = acl.group_id'
            . $accessGroupFilter
            . 'WHERE acg.acl_group_activate = \'1\'
              AND srv.enabled = 1
              AND srv.service_id = :service_id
              AND srv.host_id = :host_id
            GROUP BY srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->pdo->prepare($request);
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
            throw new Exception('Bad SQL request');
        }
    }

    /**
     * Retrieve all real time services according to ACL of contact
     *
     * @return Service[]
     * @throws Exception
     */
    public function findServices(): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'poller.id' => 'i.instance_id',
            'host_group.id' => 'hhg.hostgroup_id',
            'host.id' => 'h.host_id',
            'host.name' => 'h.name',
            'host.alias' => 'h.alias',
            'host.address' => 'h.address',
            'service_group.id' => 'ssg.servicegroup_id',
            'service.id' => 'srv.service_id',
            'service.description' => 'srv.description',
            'service.display_name' => 'srv.display_name',
            'service.is_acknowledged' => 'srv.acknowledged',
            'service.output' => 'srv.output',
            'service.state' => 'srv.state',
            'status.id' => 'srv.state'
        ]);

        $accessGroupFilter = !is_null($this->accessGroups)
            ? ' AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT 
              srv.*,
              h.host_id, h.name, h.alias
            FROM `:dbstg`.services srv
            INNER JOIN `:dbstg`.centreon_acl acl
              ON acl.service_id = srv.service_id
              AND srv.enabled = 1 
            INNER JOIN `:db`.acl_groups acg
              ON acg.acl_group_id = acl.group_id
              AND acg.acl_group_activate = \'1\''
            . $accessGroupFilter
            . 'INNER JOIN `:dbstg`.hosts h 
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

        $statement = $this->pdo->prepare($request);
        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new Exception('Bad SQL request');
        }

        $result = $this->pdo->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

        $services = [];

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

            $services[] = $service;
        }

        return $services;
    }

    /**
     * Retrieve all real time services according to ACL of contact and host id
     *
     * @param int $hostId
     * @return Service[]
     * @throws Exception
     */
    public function findServicesByHost(int $hostId): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'service.id' => 'srv.service_id',
            'service.description' => 'srv.description',
            'service.display_name' => 'srv.display_name',
            'service_group.id' => 'ssg.servicegroup_id',
            'service.is_acknowledged' => 'srv.acknowledged',
            'service.state' => 'srv.state'
        ]);

        $accessGroupFilter = !is_null($this->accessGroups)
            ? ' AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
            : ' ';

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT 
              srv.*,h.*
            FROM `:dbstg`.services srv
            INNER JOIN `:dbstg`.centreon_acl acl
              ON acl.service_id = srv.service_id
              AND srv.enabled = 1 
            INNER JOIN `:db`.acl_groups acg
              ON acg.acl_group_id = acl.group_id
              AND acg.acl_group_activate = \'1\''
            . $accessGroupFilter
            . 'INNER JOIN `:dbstg`.hosts h 
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

        $statement = $this->pdo->prepare($request);
        $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new Exception('Bad SQL request');
        }

        $result = $this->pdo->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

        $services = [];

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

        $accessGroupFilter = !is_null($this->accessGroups)
            ? ' AND acg.acl_group_id IN (' . $this->accessGroupIdToString($this->accessGroups). ') '
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
              ON i.instance_id = h.instance_id
            INNER JOIN `:dbstg`.`centreon_acl` acl
              ON acl.host_id = h.host_id
            INNER JOIN `:db`.`acl_groups` acg
              ON acg.acl_group_id = acl.group_id
              AND acg.acl_group_activate = \'1\''
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

        $statement = $this->pdo->prepare($firstRequest);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        if (false === $statement->execute()) {
            throw new Exception('Bad SQL request');
        }

        $result = $this->pdo->query('SELECT FOUND_ROWS()');
        $this->sqlRequestTranslator->getRequestParameters()->setTotal(
            (int) $result->fetchColumn()
        );

        $request =
            'SELECT DISTINCT
              sg.servicegroup_id, sg.name as servicegroup_name,
              h.host_id, h.name as host_name, h.state as host_state, h.alias as host_alias,
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
              ON i.instance_id = h.instance_id
            INNER JOIN `:dbstg`.`centreon_acl` acl
              ON acl.host_id = h.host_id
            INNER JOIN `:db`.`acl_groups` acg
              ON acg.acl_group_id = acl.group_id
              AND acg.acl_group_activate = \'1\''
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

        $statement = $this->pdo->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new Exception('Bad SQL request');
        }

        $serviceGroups = [];

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $serviceGroupId = (int) $result['servicegroup_id'];
            $host = (new Host())
                ->setId((int) $result['host_id'])
                ->setName($result['host_name'])
                ->setAlias($result['host_alias'])
                ->setState((int) $result['host_state'])
                ->setServices(
                    $this->findCertainServicesByHost(
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
     * Formats the access group ids in string. (values are separated by coma)
     *
     * @param AccessGroup[] $accessGroups
     * @return string
     */
    private function accessGroupIdToString(array $accessGroups): string
    {
        $ids = [];
        foreach ($accessGroups as $accessGroup) {
            $ids[] = $accessGroup->getId();
        }
        return implode(',', $ids);
    }

    /**
     * Find services according to the host id and service ids given
     *
     * @param int $hostId Host id
     * @param int[] $serviceIds Service Ids
     * @return Service[]
     * @throws Exception
     */
    private function findCertainServicesByHost(int $hostId, array $serviceIds): array
    {
        $services = [];
        if (empty($serviceIds)) {
            return $services;
        }
        $serviceIds = array_map(
            function ($serviceId) {
                return (int) $serviceId;
            },
            $serviceIds
        );

        $request =
            'SELECT DISTINCT 
              srv.service_id, srv.display_name, srv.description, srv.host_id, srv.state
            FROM :dbstg.services srv
            INNER JOIN :dbstg.centreon_acl acl
              ON acl.service_id = srv.service_id
            INNER JOIN :db.acl_groups acg
              ON acg.acl_group_id = acl.group_id
            INNER JOIN :dbstg.hosts h
              ON h.host_id = srv.host_id
              AND h.enabled = \'1\'
              AND h.name NOT LIKE \'_Module_BAM%\'
            WHERE srv.host_id = ?
              AND acg.acl_group_activate = \'1\'
              AND srv.enabled = 1
              AND srv.service_id IN (' . str_repeat('?,', count($serviceIds) - 1) . '?)
            GROUP by srv.host_id, srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->pdo->prepare($request);

        if (false === $statement->execute(array_merge([$hostId], $serviceIds))) {
            throw new Exception('Bad SQL request');
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
     * @param array $hostIds
     * @return array Return a list of services indexed by host
     * [host_id => Service[], ...]
     * @throws Exception
     */
    private function findServicesOnMultipleHosts(array $hostIds): array
    {
        $services = [];
        if (empty($hostIds)) {
            return $services;
        }

        $request =
            'SELECT DISTINCT 
                srv.service_id, 
                srv.display_name, 
                srv.description, 
                srv.host_id,
                srv.state
                FROM :dbstg.services srv
                INNER JOIN :dbstg.centreon_acl acl
                  ON acl.service_id = srv.service_id
                INNER JOIN :db.acl_groups acg
                  ON acg.acl_group_id = acl.group_id
                INNER JOIN :dbstg.hosts h
                  ON h.host_id = srv.host_id
                  AND h.enabled = \'1\'
                  AND h.name NOT LIKE \'_Module_BAM%\'
                WHERE srv.host_id IN (' . str_repeat('?,', count($hostIds) - 1) . '?)
                  AND acg.acl_group_activate = \'1\'
                  AND srv.enabled = 1
                GROUP by srv.host_id, srv.service_id';

        $request = $this->translateDbName($request);

        $statement = $this->pdo->prepare($request);

        if (false === $statement->execute($hostIds)) {
            throw new Exception('Bad SQL request');
        }

        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $services[(int) $result['host_id']][] = EntityCreator::createEntityByArray(
                Service::class,
                $result
            );
        }

        return $services;
    }

    /**
     * Replace all instances of :dbstg and :db by the real db names
     *
     * @param string $request Request to translate
     * @return string Request translated
     */
    private function translateDbName(string $request): string
    {
        return str_replace(
            array(':dbstg', ':db'),
            array($this->storageDbName, $this->centreonDbName),
            $request
        );
    }
}

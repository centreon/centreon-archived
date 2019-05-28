<?php


namespace Centreon\Infrastructure\Repository;


use Centreon\Domain\Entity\AccessGroup;
use Centreon\Domain\Entity\Host;
use Centreon\Domain\Entity\Service;
use Centreon\Domain\Pagination;
use Centreon\Domain\Repository\Interfaces\MonitoringRepositoryInterface;
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
     * @var AccessGroup
     */
    private $accessGroups;

    /**
     * MonitoringRepositoryRDB constructor.
     * @param DatabaseConnection $pdo
     */
    public function __construct(DatabaseConnection $pdo)
    {
        $this->pdo = $pdo;
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
     * Retrieve all real time hosts.
     *
     * @param Pagination $pagination
     * @return Host[]
     * @throws \Exception
     */
    public function findHosts(Pagination $pagination): array
    {
        $originalSearchParameters = $pagination->getSearch();

        if (count($originalSearchParameters) === 0
            || isset($originalSearchParameters[Pagination::AGREGATE_OPERATOR_AND])
        ) {
            /**
             * We define internal filters that must be hidden from the user
             */
            $newSearchParameters = [];
            $newSearchParameters[Pagination::AGREGATE_OPERATOR_AND][] = ['acl_group.activate' => '1'];
            $newSearchParameters[Pagination::AGREGATE_OPERATOR_AND][] = ['host.enable' => 1];
            $newSearchParameters[Pagination::AGREGATE_OPERATOR_AND][] =
                ['host.name' => [Pagination::OPERATOR_NOT_LIKE => '_Module_%']];

            if (!is_null($this->accessGroups)) {
                $newSearchParameters[Pagination::AGREGATE_OPERATOR_AND][] =
                    ['acl_group.id' => [Pagination::OPERATOR_IN => $this->accessGroups]];
            }

            $pagination->setSearch(
                array_merge_recursive($originalSearchParameters, $newSearchParameters)
            );
        }

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT 
            h.state, h.acknowledged, h.passive_checks, h.active_checks,
            h.notify, h.last_state_change, h.last_hard_state_change, h.output,
            h.last_check, h.address, h.name, h.alias, h.action_url, h.notes_url,
            h.notes, h.icon_image, h.icon_image_alt, h.max_check_attempts,
            h.state_type, h.check_attempt, h.scheduled_downtime_depth, h.host_id,
            h.flapping, i.name as instance_name
            FROM `:dbstg`.`instances` i
            INNER JOIN `:dbstg`.`hosts` h
              ON h.instance_id = i.instance_id
            INNER JOIN `:dbstg`.`centreon_acl` acl
              ON acl.host_id = h.host_id
            INNER JOIN `:db`.`acl_groups` acg
              ON acg.acl_group_id = acl.group_id';

        $request = str_replace(
            array(':dbstg', ':db'),
            array($this->storageDbName, $this->centreonDbName),
            $request
        );

        list ($query, $bindValues) = $pagination->createQuery(
            [
                'acl_group.activate' => 'acg.acl_group_activate',
                'acl_group.id' => 'acg.acl_group_id',
                'host.enable' => 'h.enabled',
                'host.id' => 'h.host_id',
                'host.name' => 'h.name',
                'host.alias' => 'h.alias',
                'host.address' => 'h.address',
                'status.id' => 'h.state'
            ]
        );

        $request .= $query;

        $statement = $this->pdo->prepare($request);

        foreach ($bindValues as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new Exception('Bad SQL request');
        }

        $result = $this->pdo->query('SELECT FOUND_ROWS()');
        $pagination->setTotal(
            (int) $result->fetchColumn()
        );

        $pagination->setSearch($originalSearchParameters);

        $hosts = [];
        $hostIds = [];

        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $hostId = (int)$result['host_id'];
            $hostIds[] = $hostId;
            $host = (new Host())
                ->setId($hostId)
                ->setAlias($result['alias'])
                ->setName($result['name'])
                ->setActive($result['active_checks'] === '1')
                ->setAddress($result['address'])
                ->setStatus((int)$result['state']);

            $hosts[] = $host;
        }

        /**
         * We retrieve the services and put them in their associated hosts
         */
        $request2 =
            'SELECT DISTINCT 
                srv.service_id, srv.display_name, srv.description, srv.host_id
            FROM :dbstg.services srv
            INNER JOIN :dbstg.centreon_acl acl
              ON acl.service_id = srv.service_id
            INNER JOIN :db.acl_groups acg
              ON acg.acl_group_id = acl.group_id
            WHERE srv.host_id IN (' . str_repeat('?,', count($hostIds) - 1) . '?)
              AND acg.acl_group_activate = \'1\'
              AND srv.enabled = 1';

        $request2 = str_replace(
            array(':dbstg', ':db'),
            array($this->storageDbName, $this->centreonDbName),
            $request2
        );

        $statement2 = $this->pdo->prepare($request2);

        if (false === $statement2->execute($hostIds)) {
            throw new Exception('Bad SQL request');
        }

        while ($result = $statement2->fetch(\PDO::FETCH_ASSOC)) {
            $service = (new Service())
                ->setId((int)$result['service_id'])
                ->setDisplayName($result['display_name'])
                ->setDescription($result['description']);

            $hostId = (int)$result['host_id'];
            foreach ($hosts as $host) {
                if ($host->getId() === $hostId) {
                    $host->addService($service);
                    break;
                }
            }
        }

        return $hosts;
    }

    public function findOneHost(int $hostId): ?Host
    {
        // TODO: Implement findOneHost() method.
    }

    /**
     * Find a service according to its id.
     *
     * @param int $serviceId Service Id
     * @return Service|null
     * @throws Exception
     */
    public function findOneService(int $serviceId): ?Service
    {
        $request =
            'SELECT srv.*
            FROM `:dbstg`.services srv
            INNER JOIN `:dbstg`.centreon_acl acl
              ON acl.service_id = srv.service_id
            INNER JOIN `:db`.acl_groups acg
              ON acg.acl_group_id = acl.group_id
            WHERE acg.acl_group_activate = \'1\'
              AND srv.enabled = 1
              AND srv.service_id = :service_id';

        if (!empty($this->accessGroups)) {
            $request .= ' AND acg.acl_group_id IN ('
                . str_repeat('?,', count($this->accessGroups) - 1) . '?)';
        }

        $request = str_replace(
            array(':dbstg', ':db'),
            array($this->storageDbName, $this->centreonDbName),
            $request
        );

        $statement = $this->pdo->prepare($request);
        $statement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
        if ($statement->execute($this->accessGroups)) {
            $service = null;
            if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $service = (new Service())
                    ->setId((int)$result['service_id'])
                    ->setDescription($result['description'])
                    ->setDisplayName($result['display_name']);
            }
            return $service;
        } else {
            throw new Exception('Bad SQL request');
        }
    }

    /**
     * Retrieve all real time services according to ACL of contact
     *
     * @param Pagination $pagination
     * @return Service[]
     * @throws \Exception
     */
    public function findServices(Pagination $pagination): array
    {
        $originalSearchParameters = $pagination->getSearch();

        if (count($originalSearchParameters) === 0
            || isset($originalSearchParameters[Pagination::AGREGATE_OPERATOR_AND])
        ) {
            /**
             * We define internal filters that must be hidden from the user
             */
            $newSearchParameters = [];
            $newSearchParameters[Pagination::AGREGATE_OPERATOR_AND][] = ['acl_group.activate' => '1'];
            $newSearchParameters[Pagination::AGREGATE_OPERATOR_AND][] = ['host.enable' => 1];
            $newSearchParameters[Pagination::AGREGATE_OPERATOR_AND][] = ['service.enable' => 1];
            $newSearchParameters[Pagination::AGREGATE_OPERATOR_AND][] =
                ['host.name' => [Pagination::OPERATOR_NOT_LIKE => '_Module_BAM%']];

            if (!is_null($this->accessGroups)) {
                $newSearchParameters[Pagination::AGREGATE_OPERATOR_AND][] =
                    ['acl_group.id' => [Pagination::OPERATOR_IN => $this->accessGroups]];
            }

            $pagination->setSearch(
                array_merge_recursive($originalSearchParameters, $newSearchParameters)
            );
        }

        $isHostgroupDefined = $pagination->isParameterDefined('hostgroup.id');
        $isServicegroupDefined = $pagination->isParameterDefined('servicegroup.id');

        $request =
            'SELECT SQL_CALC_FOUND_ROWS DISTINCT 
              srv.*, 
              h.name as host_name, 
              h.alias as host_alias
            FROM `:dbstg`.services srv
            INNER JOIN `:dbstg`.centreon_acl acl
              ON acl.service_id = srv.service_id
            INNER JOIN `:db`.acl_groups acg
              ON acg.acl_group_id = acl.group_id
            INNER JOIN `:dbstg`.hosts h 
              ON h.host_id = srv.host_id
            INNER JOIN `:dbstg`.instances i
              ON i.instance_id = h.instance_id
            ';

        if ($isHostgroupDefined) {
            $request .=
                "INNER JOIN :dbstg.hosts_hostgroups hhg
                  ON hhg.host_id = h.host_id
                INNER JOIN :dbstg.hostgroups hg
                  ON hg.hostgroup_id = hhg.hostgroup_id
                ";
        }

        if ($isServicegroupDefined) {
            $request .=
                'INNER JOIN :dbstg.services_servicegroups ssg
                  ON ssg.service_id = srv.service_id
                INNER JOIN :dbstg.servicegroups sg
                  ON sg.servicegroup_id = ssg.servicegroup_id
                ';
        }

        $request = str_replace(
            array(':dbstg', ':db'),
            array($this->storageDbName, $this->centreonDbName),
            $request
        );

        list ($query, $bindValues) = $pagination->createQuery(
            [
                'poller.id' => 'i.instance_id',
                'hostgroup.id' => 'hhg.hostgroup_id',
                'acl_group.activate' => 'acg.acl_group_activate',
                'acl_group.id' => 'acg.acl_group_id',
                'host.enable' => 'h.enabled',
                'host.id' => 'h.host_id',
                'host.name' => 'h.name',
                'host.alias' => 'h.alias',
                'host.address' => 'h.address',
                'service.id' => 'srv.service_id',
                'service.enable' => 'srv.enabled',
                'service.description' => 'srv.description',
                'service.name' => 'srv.display_name',
                'servicegroup.id' => 'ssg.servicegroup_id',
                'status.id' => 'srv.state'
            ]
        );

        $request .= $query;

        $statement = $this->pdo->prepare($request);
        foreach ($bindValues as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        if (false === $statement->execute()) {
            throw new Exception('Bad SQL request');
        }
        // To hide all internal filters
        $pagination->setSearch($originalSearchParameters);

        $result = $this->pdo->query('SELECT FOUND_ROWS()');
        $pagination->setTotal(
            (int) $result->fetchColumn()
        );

        $hostIds = [];
        /**
         * @var $services Service[]
         */
        $services = [];

        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $uniqueHostId = (int) $result['host_id'];
            if (!in_array($uniqueHostId, $hostIds)) {
                $hostIds[] = $uniqueHostId;
            }
            $serviceId = (int)$result['service_id'];
            $host = (new Host())
                ->setId((int)$result['host_id'])
                ->setName($result['host_name'])
                ->setAlias($result['host_alias']);

            $isServiceAlreadyInserted = false;
            foreach ($services as $service) {
                if ($service->getId() === $serviceId) {
                    $service->addHost($host);
                    $isServiceAlreadyInserted = true;
                    break;
                }
            }

            if (!$isServiceAlreadyInserted) {
                $services[] = (new Service())
                    ->setId((int)$result['service_id'])
                    ->setDisplayName($result['display_name'])
                    ->setDescription($result['description'])
                    ->setActiveCheck($result['active_checks'] === '1')
                    ->setState((int)$result['state'])
                    ->setCheckAttempt((int)$result['check_attempt'])
                    ->setMaxCheckAttempt((int)$result['max_check_attempts'])
                    ->setOutput(utf8_encode($result['output']))
                    ->setAcknowledged((bool)$result['acknowledged'])
                    ->setAcknowledgementType((int)$result['acknowledgement_type'])
                    ->addHost($host);
            }
        }

        return $services;
    }
}

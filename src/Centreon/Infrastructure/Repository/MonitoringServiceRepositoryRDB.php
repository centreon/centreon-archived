<?php

namespace Centreon\Infrastructure\Repository;

use Centreon\Domain\Entity\AccessGroup;
use Centreon\Domain\Entity\Host;
use Centreon\Domain\Entity\Service;
use Centreon\Domain\Pagination;
use Centreon\Domain\Repository\Interfaces\MonitoringServiceRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;

class MonitoringServiceRepositoryRDB implements MonitoringServiceRepositoryInterface
{
    /**
     * @var DatabaseConnection
     */
    private $pdo;
    /**
     * @var Pagination
     */
    private $pagination;

    /**
     * MonitoringServiceRepositoryRDB constructor.
     * @param DatabaseConnection $pdo
     * @param Pagination $pagination
     */
    public function __construct(DatabaseConnection $pdo, Pagination $pagination)
    {
        $this->pdo = $pdo;
        $this->pagination = $pagination;
    }

    /**
     * Retrieve all real time services according to ACL of contact
     *
     * @param AccessGroup[]|null $accessGroups
     * @return Host[]
     * @throws \Exception
     */
    public function getServices(?array $accessGroups): array
    {
        $originalSearchParameters = $this->pagination->getSearch();

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

            if (!is_null($accessGroups)) {
                $newSearchParameters[Pagination::AGREGATE_OPERATOR_AND][] =
                    ['acl_group.id' => [Pagination::OPERATOR_IN => $accessGroups]];
            }

            $this->pagination->setSearch(
                array_merge_recursive($originalSearchParameters, $newSearchParameters)
            );
        }

        $isHostgroupDefined = $this->pagination->isParameterDefined('hostgroup.id');
        $isServicegroupDefined = $this->pagination->isParameterDefined('servicegroup.id');

        $request =
            "SELECT SQL_CALC_FOUND_ROWS DISTINCT 
                srv.*
            FROM centreon_storage.services srv
            INNER JOIN centreon_storage.centreon_acl acl
              ON acl.service_id = srv.service_id
            INNER JOIN centreon.acl_groups acg
              ON acg.acl_group_id = acl.group_id
            INNER JOIN centreon_storage.hosts h 
              ON h.host_id = srv.host_id
            INNER JOIN centreon_storage.instances i
              ON i.instance_id = h.instance_id
            ";

        if ($isHostgroupDefined) {
            $request .=
                "INNER JOIN centreon_storage.hosts_hostgroups hhg
                  ON hhg.host_id = h.host_id
                INNER JOIN centreon_storage.hostgroups hg
                  ON hg.hostgroup_id = hhg.hostgroup_id
                ";
        }

        if ($isServicegroupDefined) {
            $request .=
                'INNER JOIN centreon_storage.services_servicegroups ssg
                  ON ssg.service_id = srv.service_id
                INNER JOIN centreon_storage.servicegroups sg
                  ON sg.servicegroup_id = ssg.servicegroup_id
                ';
        }

        list ($query, $bindValues) = $this->pagination->createQuery(
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
            throw new \Exception('Bad querie');
        }
        // To hide all internal filters
        $this->pagination->setSearch($originalSearchParameters);

        $result = $this->pdo->query('SELECT FOUND_ROWS()');
        $this->pagination->setTotal(
            (int) $result->fetchColumn()
        );

        $hostIds = [];

        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $hostId = (int) $result['host_id'];

            /*
             * We retrieve all services
             */
            $service = (new Service())
                ->setId((int)$result['service_id'])
                ->setDisplayName($result['display_name'])
                ->setDescription($result['description'])
                ->setActiveCheck($result['active_checks'] === '1')
                ->setState((int)$result['state'])
                ->setCheckAttempt((int)$result['check_attempt'])
                ->setMaxCheckAttempt((int)$result['max_check_attempts'])
                ->setOutput(utf8_encode($result['output']));

            /*
             * And sorting them by host
             */
            $hostIds[$hostId][] = $service;
        }

        $hosts = [];
        if (count($hostIds) > 0) {
            $prepare = $this->pdo->prepare(
                'SELECT * FROM centreon_storage.hosts
                 WHERE host_id IN ('
                . str_repeat('?,', count($hostIds) - 1) . '?'
                . ')'
            );

            /*
             * And retrieve the associated hosts
             */
            if ($prepare->execute(array_keys($hostIds))) {
                while ($result = $prepare->fetch(\PDO::FETCH_ASSOC)) {
                    $hostId = (int) $result['host_id'];
                    $hosts[] = (new Host())
                        ->setId($hostId)
                        ->setName($result['name'])
                        ->setAlias($result['alias'])
                        ->setStatus($result['state'])
                        ->setActive($result['enabled'] === '1')
                        ->setServices($hostIds[$hostId]);
                }
            }
        }
        return $hosts;
    }
}

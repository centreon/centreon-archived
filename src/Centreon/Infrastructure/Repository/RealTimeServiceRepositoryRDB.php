<?php

namespace Centreon\Infrastructure\Repository;

use Centreon\Domain\Entity\AccessGroup;
use Centreon\Domain\Entity\Host;
use Centreon\Domain\Entity\Service;
use Centreon\Domain\Repository\Interfaces\RealTimeServiceRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;

class RealTimeServiceRepositoryRDB implements RealTimeServiceRepositoryInterface
{
    /**
     * @var DatabaseConnection
     */
    private $pdo;

    public function __construct(DatabaseConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Retrieve all real time services according to ACL of contact
     *
     * @param AccessGroup[]|null $accessGroups
     * @return Host[]
     */
    public function getServices(?array $accessGroups): array
    {
        $hosts = [];

        if (is_null($accessGroups)) {
            $prepare = $this->pdo->prepare(
                "SELECT DISTINCT 
                    srv.* 
                FROM centreon_storage.services srv
                INNER JOIN centreon_storage.centreon_acl acl
                  ON acl.service_id = srv.service_id
                INNER JOIN centreon.acl_groups acg
                  ON acg.acl_group_id = acl.group_id
                INNER JOIN centreon_storage.hosts h 
                  ON h.host_id = srv.host_id
                WHERE acl_group_activate = '1'
                AND h.enabled = 1
                AND srv.enabled = 1"
            );
            $prepare->execute();
        } else {
            /*
             * Retrieve all services through access group and contact
             */
            $prepare = $this->pdo->prepare(
                "SELECT DISTINCT srv.* FROM centreon_storage.services srv
                INNER JOIN centreon_storage.centreon_acl acl
                  ON acl.service_id = srv.service_id
                INNER JOIN centreon.acl_groups acg
                  ON acg.acl_group_id = acl.group_id
                INNER JOIN centreon_storage.hosts h 
                  ON h.host_id = srv.host_id
                WHERE acl_group_activate = '1'
                AND h.enabled = 1
                AND srv.enabled = 1
                AND ( 
                  acl_group_id IN (" . str_repeat('?,', count($accessGroups) - 1) . '?' . ")
                )"
            );

            $accessGroupIds = [];

            foreach ($accessGroups as $oneAccesGroup) {
                if ($oneAccesGroup instanceof AccessGroup) {
                    $accessGroupIds[] = $oneAccesGroup->getId();
                }
            }
            $prepare->execute($accessGroupIds);
        }

        $hostIds = [];

        while ($result = $prepare->fetch(\PDO::FETCH_ASSOC)) {
            $hostId = (int) $result['host_id'];

            /*
             * We retrieve all services
             */
            $service = (new Service())
                ->setId((int)$result['service_id'])
                ->setName($result['display_name'])
                ->setDescription($result['description'])
                ->setIsActive($result['enabled'] === '1')
                ->setState((int)$result['state']);

            /*
             * And sorting them by host
             */
            $hostIds[$hostId][] = $service;
        }

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

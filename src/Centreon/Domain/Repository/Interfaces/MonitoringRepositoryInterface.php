<?php


namespace Centreon\Domain\Repository\Interfaces;


use Centreon\Domain\Entity\AccessGroup;
use Centreon\Domain\Entity\Host;
use Centreon\Domain\Entity\Service;
use Centreon\Domain\Pagination;

interface MonitoringRepositoryInterface
{
    /**
     * @param AccessGroup[]|null $accessGroups
     * @return MonitoringRepositoryInterface
     */
    public function filterByAccessGroups(?array $accessGroups): MonitoringRepositoryInterface;

    /**
     * Retrieve all real time hosts.
     *
     * @param AccessGroup[]|null $accessGroupEntity
     * @return Host[]
     * @throws \Exception
     */
    public function findHosts(Pagination $pagination): array;

    /**
     * @param int $hostId
     * @return Host|null
     */
    public function findOneHost(int $hostId): ?Host;

    /**
     * @param int $serviceId
     * @return Service|null
     */
    public function findOneService(int $serviceId): ?Service;

    /**
     * Retrieve all real time services according to ACL of contact
     *
     * @param AccessGroup[]|null $accessGroups
     * @return Service[]
     * @throws \Exception
     */
    public function findServices(Pagination $pagination): array;
}
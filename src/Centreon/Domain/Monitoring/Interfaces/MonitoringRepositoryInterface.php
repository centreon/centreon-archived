<?php

namespace Centreon\Domain\Monitoring\Interfaces;

use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Pagination\Pagination;

interface MonitoringRepositoryInterface
{
    /**
     * Sets the access groups that will be used to filter services and the host.
     *
     * @param AccessGroup[]|null $accessGroups
     * @return self
     */
    public function filterByAccessGroups(?array $accessGroups): self;

    /**
     * Find all real time hosts according to access group.
     *
     * @param Pagination $pagination
     * @return Host[]
     */
    public function findHosts(Pagination $pagination): array;

    /**
     * Find one host based on its id and according to access groups.
     *
     * @param int $hostId Id of the host to be found
     * @return Host|null
     */
    public function findOneHost(int $hostId): ?Host;

    /**
     * Find one service based on its id and according to access groups.
     *
     * @param int $serviceId Id of the service to be found
     * @return Service|null
     */
    public function findOneService(int $serviceId): ?Service;

    /**
     * Find all real time services according to access group.
     *
     * @param Pagination $pagination
     * @return Service[]
     */
    public function findServices(Pagination $pagination): array;
}

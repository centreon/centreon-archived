<?php

namespace Centreon\Domain\Monitoring\Interfaces;

use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Exception;

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
     * @return Host[]
     */
    public function findHosts(): array;

    /**
     * Find all services grouped by host groups
     *
     * @return HostGroup[]
     * @throws Exception
     */
    public function findHostGroups(): array;

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
     * @param int $hostId
     * @return Service|null
     */
    public function findOneService(int $hostId, int $serviceId): ?Service;

    /**
     * Find all services grouped by service groups
     *
     * @return \Servicegroup[]
     * @throws Exception
     */
    public function findServiceGroups(): array;

    /**
     * Find all real time services according to access group.
     *
     * @return Service[]
     */
    public function findServices(): array;

    /**
     * Find all real time services according to access group and host id.
     *
     * @param int $hostId
     * @return Service[]
     */
    public function findServicesByHost(int $hostId): array;
}

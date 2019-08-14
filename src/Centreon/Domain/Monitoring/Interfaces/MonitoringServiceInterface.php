<?php

namespace Centreon\Domain\Monitoring\Interfaces;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;

interface MonitoringServiceInterface
{
    /**
     * @param ContactInterface $contact Contact to use as a ACL filter
     * @return self
     */
    public function filterByContact(ContactInterface $contact): MonitoringServiceInterface;

    /**
     * @return Host[]
     * @throws \Exception
     */
    public function findHosts(): array;

    /**
     * @return \HostGroup[]
     * @throws \Exception
     */
    public function findHostGroups(): array;

    /**
     * @param int $hostId Id of the host to be found
     * @return Host|null
     * @throws \Exception
     */
    public function findOneHost(int $hostId): ?Host;

    /**
     * @param int $hostId
     * @param int $serviceId Id of the service to be found
     * @return Service|null
     * @throws \Exception
     */
    public function findOneService(int $hostId, int $serviceId): ?Service;

    /**
     * @return \Servicegroup[]
     * @throws \Exception
     */
    public function findServiceGroups(): array;

    /**
     * @return Service[]
     * @throws \Exception
     */
    public function findServices(): array;

    /**
     * @param int $hostId
     * @throws \Exception
     * @return Service[]
     */
    public function findServicesByHost(int $hostId): array;

    /**
     * Indicates whether a host exists
     *
     * @param int $hostId Host id to find
     * @return bool
     * @throws \Exception
     */
    public function isHostExists(int $hostId): bool;
}

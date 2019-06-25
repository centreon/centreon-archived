<?php

namespace Centreon\Domain\Monitoring\Interfaces;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Pagination\Pagination;

interface MonitoringServiceInterface
{
    /**
     * @param int $hostId Id of the host to be found
     * @return Host|null
     */
    public function findOneHost(int $hostId): ?Host;

    /**
     * @param int $serviceId Id of the service to be found
     * @return Service|null
     */
    public function findOneService(int $serviceId): ?Service;

    /**
     * @param Pagination $pagination
     * @return Host[]|null
     */
    public function findHosts(Pagination $pagination): array;

    /**
     * @param Pagination $pagination
     * @return Service[]|null
     */
    public function findServices(Pagination $pagination): array;

    /**
     * @param ContactInterface $contact Contact to use as a ACL filter
     * @return self
     */
    public function filterByContact(ContactInterface $contact): MonitoringServiceInterface;
}

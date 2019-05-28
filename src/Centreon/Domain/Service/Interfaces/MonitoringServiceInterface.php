<?php


namespace Centreon\Domain\Service\Interfaces;

use Centreon\Domain\Entity\AccessGroup;
use Centreon\Domain\Entity\Contact;
use Centreon\Domain\Entity\Host;
use Centreon\Domain\Entity\Service;
use Centreon\Domain\Pagination;

interface MonitoringServiceInterface
{
    /**
     * @param int $contactId
     * @return Host
     */
    public function findOneHost(int $contactId): ?Host;

    /**
     * @param Contact $contact
     * @param int $serviceId
     * @return Service
     */
    public function findOneService(int $serviceId): ?Service;

    /**
     * @param Pagination $pagination
     * @return Host[]|null
     */
    public function findHosts(Pagination $pagination): array;

    /**
     * @param Pagination $pagination
     * @return AccessGroup[]|null
     */
    public function findServices(Pagination $pagination): array;

    /**
     * @param Contact $contact
     * @return mixed
     */
    public function filterByContact(Contact $contact): MonitoringServiceInterface;
}
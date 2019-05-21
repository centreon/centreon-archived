<?php


namespace Centreon\Domain\Service\Interfaces;

use Centreon\Domain\Entity\AccessGroup;
use Centreon\Domain\Entity\Contact;

interface MonitoringServiceInterface
{
    /**
     * @param Contact $contact
     * @return AccessGroup[]|null
     */
    public function findServicesFromContact(Contact $contact): array;
}
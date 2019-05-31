<?php

namespace Centreon\Domain\Repository\Interfaces;

use Centreon\Domain\Entity\AccessGroup;
use Centreon\Domain\Entity\Contact;

interface AccessGroupRepositoryInterface
{
    /**
     * Find all access groups from a contact
     *
     * @param Contact $contact
     * @return AccessGroup[]
     */
    public function findByContact(Contact $contact): array;
}

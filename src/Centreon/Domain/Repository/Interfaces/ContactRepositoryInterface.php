<?php

namespace Centreon\Domain\Repository\Interfaces;

use Centreon\Domain\Entity\Contact;

interface ContactRepositoryInterface
{
    /**
     * @param string $name Username
     * @return Contact|null
     */
    public function findByName(string $name): ?Contact;

    /**
     * @param int $contactId Contact id
     * @return Contact|null
     */
    public function findById(int $contactId): ?Contact;
}

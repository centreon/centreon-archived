<?php

namespace Centreon\Domain\Contact\Interfaces;

use Centreon\Domain\Contact\Contact;

interface ContactRepositoryInterface
{
    /**
     * Find a contact by name
     *
     * @param string $name Username
     * @return Contact|null
     */
    public function findByName(string $name): ?Contact;

    /**
     * Find a contact by id
     *
     * @param int $contactId Contact id
     * @return Contact|null
     */
    public function findById(int $contactId): ?Contact;

    /**
     * Find a contact based on their session id
     *
     * @param string $sessionId Session id
     * @return Contact|null
     */
    public function findBySession(string $sessionId): ?Contact;
}

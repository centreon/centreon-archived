<?php
/*
 * Centreon
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more informations : contact@centreon.com
 *
 */

namespace Centreon\Domain\Repository\Interfaces;

interface ContactRepositoryInterface
{
    /**
     * Search a contact id by its api token
     *
     * @param string $token Token to search
     * @return Return The contact id if found otherwise NULL
     */
    public function searchContactIdByToken(string $token): ?int;
}

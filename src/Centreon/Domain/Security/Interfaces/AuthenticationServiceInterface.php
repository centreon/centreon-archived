<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\Security\Interfaces;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Security\AuthenticationService;

interface AuthenticationServiceInterface
{
    /**
     * Find a contact according to the credentials.
     *
     * @param string $username Username
     * @param string $password Password
     * @return Contact|null
     * @throws \Exception
     */
    public function findContactByCredentials(string $username, string $password): ?Contact;

    /**
     * Generate a new token.
     * There is no limit to the number of tokens per contact.
     *
     * @param string $username Username for which we want to generate a token
     * @return string Returns the new generated token
     * @throws \Exception
     */
    public function generateToken(string $username): string;

    /**
     * Get the generated token.
     *
     * @return string Returns the generated token
     */
    public function getGeneratedToken():string;

    /**
     * Delete an existing authentication token
     *
     * @param string $authToken
     * @return bool
     * @throws \Exception
     */
    public function logout(string $authToken): bool;

    /**
     * Delete all expired tokens
     *.
     * @return int Returns the number of expired tokens deleted
     */
    public function deleteExpiredTokens(): int;
}

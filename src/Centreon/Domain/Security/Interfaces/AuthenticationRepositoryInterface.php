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

use Centreon\Domain\Security\AuthenticationToken;
use Centreon\Domain\Security\Session;

interface AuthenticationRepositoryInterface
{
    /**
     * Indicates whether the credentials are correct
     *
     * @param string $username Username
     * @param string $password Password
     * @return bool Return TRUE if credentials are good
     */
    public function isGoodCredentials(string $username, string $password): bool;

    /**
     * Register a new authentication token.
     *
     * @param int $contactId Contact id
     * @param string $token New authentication token
     * @return int Return ID of the new authentication token
     */
    public function addToken(int $contactId, string $token);

    /**
     * Delete all expired tokens registered.
     *
     * @return int Returns the number of tokens deleted
     */
    public function deleteExpiredTokens():int;

    /**
     * Find a session.
     *
     * @param string $sessionId Session ID
     * @return Session|null
     */
    public function findSession(string $sessionId): ?Session;

    /**
     * Find a token based on a contact ID.
     *
     * @param int $contactId Contact ID
     * @return AuthenticationToken[] Return a list of founds tokens
     * @throws \Exception
     */
    public function findTokensByContact(int $contactId): array;

    /**
     * Find a token.
     *
     * @param string $token Token to find
     * @return AuthenticationToken|null
     * @throws \Exception
     */
    public function findToken(string $token): ?AuthenticationToken;

    /**
     * Delete all tokens for a contact.
     *
     * @param int $contactId Contact ID
     * @return int Returns the number of tokens deleted
     * @throws \Exception
     */
    public function deleteTokensByContact(int $contactId): int;

    /**
     * Delete all expired sessions.
     *
     * @throws \Exception
     */
    public function deleteExpiredSession(): void;

    /**
     * Refresh the generation date of the authentication token.
     *
     * @param string $token Token id for which we want to refresh
     */
    public function refreshToken(string $token): void;

    /**
     * Delete a token from a contact.
     *
     * @param int $contactId Contact id to which the token belongs
     * @param string $token Token to delete
     * @return bool
     */
    public function deleteTokenFromContact(int $contactId, string $token): bool;
}

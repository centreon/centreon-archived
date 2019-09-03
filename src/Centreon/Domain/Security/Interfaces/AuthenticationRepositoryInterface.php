<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\Security\Interfaces;

use Centreon\Domain\Entity\AuthenticationToken;
use Centreon\Domain\Entity\Session;

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
     * @throws \Exception
     */
    public function deleteExpiredSession(): void;

    /**
     * Refresh the generation date of the authentication token.
     *
     * @param string $token Token id for which we want to refresh
     */
    public function refreshToken(string $token): void;
}

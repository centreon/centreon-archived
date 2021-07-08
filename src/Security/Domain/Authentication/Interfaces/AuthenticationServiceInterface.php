<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Security\Domain\Authentication\Interfaces;

use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Centreon\Domain\Authentication\Exception\AuthenticationException;

/**
 * @package Security\Domain\Authentication\Interfaces
 */
interface AuthenticationServiceInterface
{
    /**
     * Check authentication token
     *
     * @param string $token
     * @return boolean
     * @throws ProviderException
     * @throws AuthenticationException
     */
    public function isValidToken(string $token): bool;

    /**
     * Delete a session.
     *
     * @param string $sessionToken
     * @throws AuthenticationException
     */
    public function deleteSession(string $sessionToken): void;

    /**
     * Delete all expired API tokens
     * @throws AuthenticationException
     */
    public function deleteExpiredSecurityTokens(): void;

    /**
     * @param AuthenticationTokens $authenticationToken
     * @throws AuthenticationException
     */
    public function updateAuthenticationTokens(AuthenticationTokens $authenticationToken): void;

    /**
     * @param string $token
     * @return AuthenticationTokens|null
     * @throws AuthenticationException
     */
    public function findAuthenticationTokensByToken(string $token): ?AuthenticationTokens;

    /**
     * Check if the session is valid (use the refresh token if necessary).
     *
     * @param string $sessionToken Session token
     * @param ProviderInterface $provider Provider that will be used to refresh the token if necessary
     * @return bool Returns true if the session is valid (after use of the refresh token by the provider if necessary)
     * @throws AuthenticationException
     */
    public function hasValidSession(string $sessionToken, ProviderInterface $provider): bool;
}

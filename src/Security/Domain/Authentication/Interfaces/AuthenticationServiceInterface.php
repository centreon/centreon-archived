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

use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;

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
     * @throws ProviderServiceException
     * @throws AuthenticationServiceException
     */
    public function checkToken(string $token): bool;

    /**
     * Create the authentication tokens.
     *
     * @param string $sessionToken
     * @param string $providerConfigurationName
     * @param ContactInterface $contact
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
     * @throws AuthenticationServiceException
     */
    public function createAuthenticationTokens(
        string $sessionToken,
        string $providerConfigurationName,
        ContactInterface $contact,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void;

    /**
     * Delete a session.
     *
     * @param string $sessionToken
     * @throws AuthenticationServiceException
     */
    public function deleteSession(string $sessionToken): void;

    /**
     * Create the authentication tokens for API.
     *
     * @param string $token
     * @param ContactInterface $contact
     * @param ProviderConfiguration $providerConfiguration
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
     * @throws AuthenticationServiceException
     */
    public function createAPIAuthenticationTokens(
        string $token,
        ProviderConfiguration $providerConfiguration,
        ContactInterface $contact,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void;

    /**
     * Delete all expired API tokens
     * @throws AuthenticationServiceException
     */
    public function deleteExpiredSecurityTokens(): void;

    /**
     * @param AuthenticationTokens $authenticationToken
     * @throws AuthenticationServiceException
     */
    public function updateAuthenticationTokens(AuthenticationTokens $authenticationToken): void;

    /**
     * @param string $token
     * @return AuthenticationTokens|null
     * @throws AuthenticationServiceException
     */
    public function findAuthenticationTokensByToken(string $token): ?AuthenticationTokens;

    /**
     * Check if the session is valid (use the refresh token if necessary).
     *
     * @param string $sessionToken Session token
     * @param ProviderInterface $provider Provider that will be used to refresh the token if necessary
     * @return bool Returns true if the session is valid (after use of the refresh token by the provider if necessary)
     * @throws AuthenticationServiceException
     */
    public function hasValidSession(string $sessionToken, ProviderInterface $provider): bool;
}

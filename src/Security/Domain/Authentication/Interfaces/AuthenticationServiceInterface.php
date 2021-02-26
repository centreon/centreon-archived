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

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Model\ProviderToken;

/**
 * @package Security\Domain\Authentication\Interfaces
 */
interface AuthenticationServiceInterface
{
    /**
     * Create the authentication tokens.
     *
     * @param string $sessionToken
     * @param string $providerConfigurationName
     * @param ContactInterface $contact
     * @param ProviderToken|null $providerToken
     * @param ProviderToken|null $providerRefreshToken
     */
    public function createAuthenticationTokens(
        string $sessionToken,
        string $providerConfigurationName,
        ContactInterface $contact,
        ?ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void;

    /**
     * @param string $sessionToken
     */
    public function deleteSession(string $sessionToken): void;

    /**
     * Find a provider by configuration id.
     *
     * @param int $providerConfigurationId
     * @return ProviderInterface|null
     * @throws \Exception
     */
    public function findProviderByConfigurationId(int $providerConfigurationId): ?ProviderInterface;

    /**
     * Find a provider by the configuration name.
     *
     * @param string $providerConfigurationName
     * @return ProviderInterface|null
     */
    public function findProviderByConfigurationName(string $providerConfigurationName): ?ProviderInterface;

    /**
     * @param string $providerConfigurationName
     * @return ProviderConfiguration|null
     */
    public function findProviderConfigurationByConfigurationName(string $providerConfigurationName): ?ProviderConfiguration;

    /**
     * @param string $sessionToken
     * @return ProviderInterface|null
     * @throws \Exception
     */
    public function findProviderBySession(string $sessionToken): ?ProviderInterface;

    /**
     * @param AuthenticationTokens $authenticationToken
     */
    public function updateAuthenticationToken(AuthenticationTokens $authenticationToken): void;

    /**
     * @param string $sessionToken
     * @return AuthenticationTokens|null
     */
    public function findAuthenticationTokenBySessionToken(string $sessionToken): ?AuthenticationTokens;

    /**
     * Check if the session is valid (use the refresh token if necessary).
     *
     * @param string $sessionToken Session token
     * @param ProviderInterface $provider Provider that will be used to refresh the token if necessary
     * @return bool Returns true if the session is valid (after use of the refresh token by the provider if necessary)
     * @throws \Exception *
     */
    public function hasValidSession(string $sessionToken, ProviderInterface $provider): bool;
}

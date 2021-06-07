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
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Model\ProviderToken;

/**
 * @package Security\Authentication\Interfaces
 */
interface AuthenticationRepositoryInterface
{
    /**
     * @param string $sessionToken Session token
     * @param int $providerConfigurationId Provider configuration id
     * @param int $contactId Contact id
     * @param ProviderToken $providerToken Provider token
     * @param ProviderToken $providerRefreshToken Provider refresh token
     */
    public function addAuthenticationTokens(
        string $sessionToken,
        int $providerConfigurationId,
        int $contactId,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void;

    /**
     * @param string $token Session token
     * @param int $providerConfigurationId Provider configuration id
     * @param int $contactId Contact id
     * @param ProviderToken $providerToken Provider token
     * @param ProviderToken $providerRefreshToken Provider refresh token
     */
    public function addApiAuthenticationTokens(
        string $token,
        int $providerConfigurationId,
        int $contactId,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void;

    /**
     * Clear all information about the session token.
     *
     * @param string $sessionToken
     */
    public function deleteSession(string $sessionToken): void;

    /**
     * Delete all expired sessions.
     */
    public function deleteExpiredSession(): void;

    /**
     * Find providers configurations
     *
     * @return ProviderConfiguration[]
     */
    public function findProvidersConfigurations(): array;

    /**
     * Find the authentication token using the session token.
     *
     * @param string $token Session token
     * @return AuthenticationTokens|null
     */
    public function findAuthenticationTokensByToken(string $token): ?AuthenticationTokens;

    /**
     * Find the provider's configuration.
     *
     * @param int $id Id of the provider configuration
     * @return ProviderConfiguration|null
     * @throws \Exception
     */
    public function findProviderConfiguration(int $id): ?ProviderConfiguration;

    /**
     * Updates the provider authentication tokens.
     *
     * @param AuthenticationTokens $authenticationTokens Provider tokens
     */
    public function updateAuthenticationTokens(AuthenticationTokens $authenticationTokens): void;

    /**
     * Updates the provider token.
     *
     * @param ProviderToken $providerToken
     * @return void
     */
    public function updateProviderToken(ProviderToken $providerToken): void;

    /**
     * Find the provider configuration by name
     *
     * @param string $providerConfigurationName
     * @return ProviderConfiguration|null
     */
    public function findProviderConfigurationByConfigurationName(
        string $providerConfigurationName
    ): ?ProviderConfiguration;
}

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

use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\Authentication\Domain\Model\ProviderToken;

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
     * Find the authentication token using the session token.
     *
     * @param string $token Session token
     * @return AuthenticationTokens|null
     */
    public function findAuthenticationTokensByToken(string $token): ?AuthenticationTokens;

    /**
     * Updates the provider authentication tokens.
     *
     * @param AuthenticationTokens $authenticationTokens Provider tokens
     */
    public function updateAuthenticationTokens(AuthenticationTokens $authenticationTokens): void;

    /**
     * Updates the provider token.
     *
     * @param NewProviderToken $providerToken
     * @return void
     */
    public function updateProviderToken(NewProviderToken $providerToken): void;

    /**
     * Delete a security token.
     *
     * @param string $token
     * @return void
     */
    public function deleteSecurityToken(string $token): void;
}

<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Security\Authentication\Application\Repository;

use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\Authentication\Domain\Model\ProviderToken;

interface WriteTokenRepositoryInterface
{
    /**
     * Delete all expired tokens registered.
     */
    public function deleteExpiredSecurityTokens(): void;

    /**
     * @param string $token
     * @param int $providerConfigurationId
     * @param int $contactId
     * @param NewProviderToken $providerToken
     * @param NewProviderToken|null $providerRefreshToken
     */
    public function createAuthenticationTokens(
        string $token,
        int $providerConfigurationId,
        int $contactId,
        NewProviderToken $providerToken,
        ?NewProviderToken $providerRefreshToken
    ): void;

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
     */
    public function updateProviderToken(ProviderToken $providerToken): void;

    /**
     * Delete a security token.
     *
     * @param string $token
     */
    public function deleteSecurityToken(string $token): void;
}

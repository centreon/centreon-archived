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

namespace Core\Security\Authentication\Application\Provider;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;

interface ProviderAuthenticationInterface
{
    /**
     * @return \Centreon
     */
    public function getLegacySession(): \Centreon;

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration;

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration): void;

    /**
     * @return bool
     */
    public function isUpdateACLSupported(): bool;

    /**
     * Indicates if the provider has a mechanism to refresh the token.
     *
     * @return bool
     */
    public function canRefreshToken(): bool;

    /**
     * Refresh the provider token.
     *
     * @param AuthenticationTokens $authenticationTokens
     * @return AuthenticationTokens|null Return the new AuthenticationTokens object if success otherwise null
     */
    public function refreshToken(AuthenticationTokens $authenticationTokens): ?AuthenticationTokens;

    /**
     *
     * @return ContactInterface|null
     */
    public function getAuthenticatedUser(): ?ContactInterface;

    /**
     * @param LoginRequest $request
     */
    public function authenticateOrFail(LoginRequest $request): void;

    /**
     * @return ContactInterface
     */
    public function findUserOrFail(): ContactInterface;

    /**
     * Return the contact username
     *
     * @return string
     */
    public function getUsername(): string;

    /**
     * If isAutoImportEnabled method returns true, the user will be imported to the database
     */
    public function importUser(): void;

    /**
     * @return void
     */
    public function updateUser(): void;

    /**
     * @param string|null $token
     * @return NewProviderToken
     */
    public function getProviderToken(?string $token = null): NewProviderToken;

    /**
     * @return NewProviderToken|null
     */
    public function getProviderRefreshToken(): ?NewProviderToken;

    /**
     * @return bool
     */
    public function isAutoImportEnabled(): bool;

    /**
     * Get User information gathered from IdP
     *
     * @return array<string,mixed>
     */
    public function getUserInformation(): array;

    /**
     * Get information store in id_token JWT Payload
     *
     * @return array<string,mixed>
     */
    public function getIdTokenPayload(): array;
}

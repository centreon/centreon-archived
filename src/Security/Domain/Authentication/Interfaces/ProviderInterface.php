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
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Model\ProviderToken;

/**
 * @package Security\Authentication\Interfaces
 */
interface ProviderInterface
{
    /**
     * @param array<string, mixed> $data
     * @throw \Exception
     */
    public function authenticate(array $data): void;

    /**
     * Indicates whether we can create the authenticated user or not.
     *
     * @return bool
     */
    public function canCreateUser(): bool;

    /**
     * Indicates whether or not the provider has a mechanism to refresh the token.
     *
     * @return bool
     */
    public function canRefreshToken(): bool;

    /**
     * Export the provider's configuration (ex: client_id, client_secret, grant_type, ...).
     *
     * @return array<string, mixed>
     */
    public function exportConfiguration(): array;

    /**
     * Get the provider's authentication uri (ex: https://www.okta.com/.../auth).
     *
     * @return string
     */
    public function getAuthenticationUri(): string;

    /**
     * Return the provider's name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Return the provider refresh token.
     *
     * @return ProviderToken|null
     */
    public function getProviderRefreshToken(): ?ProviderToken;

    /**
     * Return the provider token.
     *
     * @return ProviderToken|null
     */
    public function getProviderToken(): ?ProviderToken;

    /**
     * Retrieve the contact.
     *
     * @return ContactInterface|null
     */
    public function getUser(): ?ContactInterface;

    /**
     * Import the provider's configuration to initialize it (ex: client_id, client_secret, grant_type, ...).
     *
     * @param array<string, mixed> $configuration
     */
    public function importConfiguration(array $configuration): void;

    /**
     * Indicates whether this provider is the one selected for authentication.
     *
     * @return bool
     */
    public function isForced(): bool;

    /**
     * Indicates whether the authentication process is complete and the user is properly authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Refresh the provider token.
     *
     * @param AuthenticationTokens $authenticationTokens
     * @return AuthenticationTokens|null Return the new AuthenticationTokens object if success otherwise null
     */
    public function refreshToken(AuthenticationTokens $authenticationTokens): ?AuthenticationTokens;
}
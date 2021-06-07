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
use Security\Domain\Authentication\Model\ProviderConfiguration;

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
     * Get legacy Centreon session
     *
     * @return \Centreon|null
     */
    public function getLegacySession(): ?\Centreon;

    /**
     * Set legacy Centreon session
     *
     * @param \Centreon $legacySession
     */
    public function setLegacySession(?\Centreon $legacySession): void;

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
     * Return the provider's name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Return the provider token
     *
     * @param string $token
     * @return ProviderToken
     */
    public function getProviderToken(string $token): ProviderToken;

    /**
     * Return the provider refresh token.
     *
     * @param string $token
     * @return ProviderToken|null
     */
    public function getProviderRefreshToken(string $token): ?ProviderToken;

    /**
     * Retrieve the contact.
     *
     * @return ContactInterface|null
     */
    public function getUser(): ?ContactInterface;

    /**
     * Get the provider's configuration (ex: client_id, client_secret, grant_type, ...).
     *
     * @return ProviderConfiguration
     */
    public function getConfiguration(): ProviderConfiguration;

    /**
     * Set the provider's configuration to initialize it (ex: client_id, client_secret, grant_type, ...).
     *
     * @param ProviderConfiguration $configuration
     */
    public function setConfiguration(ProviderConfiguration $configuration): void;

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

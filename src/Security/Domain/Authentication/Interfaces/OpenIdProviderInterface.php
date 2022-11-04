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

namespace Security\Domain\Authentication\Interfaces;

use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Security\Domain\Authentication\Model\ProviderToken;

interface OpenIdProviderInterface extends ProviderInterface
{
    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration;

    /**
     * @return NewProviderToken
     */
    public function getProviderToken(): NewProviderToken;

    /**
     * @return NewProviderToken|null
     */
    public function getProviderRefreshToken(): ?NewProviderToken;

    /**
     * Create user with informations from identity provider
     * @throws \Throwable
     */
    public function createUser(): void;

     /**
     * Authenticate the user using OpenId Provider.
     *
     * @param string|null $authorizationCode
     */
    public function authenticateOrFail(?string $authorizationCode, string $clientIp): void;

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

    /**
     * @return array<string>
     */
    public function getRolesMappingFromProvider(): array;
}

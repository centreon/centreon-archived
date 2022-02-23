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

namespace Core\Domain\Security\ProviderConfiguration\OpenId\Model;

class OpenIdConfiguration
{
    /**
     * @param boolean $isActive
     * @param boolean $isForced
     * @param array<string> $trustedClientAddresses
     * @param array<string> $blacklistClientAddresses
     * @param string|null $baseUrl
     * @param string|null $authorizationEndpoint
     * @param string|null $tokenEndpoint
     * @param string|null $introspectionTokenEndpoint
     * @param string|null $userInformationsEndpoint
     * @param string|null $endSessionEndpoint
     * @param array<string> $connectionScope
     * @param string|null $loginClaim
     * @param string|null $clientId
     * @param string|null $clientSecret
     * @param string|null $authenticationType
     * @param boolean $verifyPeer
     */
    public function __construct(
        private bool $isActive,
        private bool $isForced,
        private array $trustedClientAddresses,
        private array $blacklistClientAddresses,
        private ?string $baseUrl,
        private ?string $authorizationEndpoint,
        private ?string $tokenEndpoint,
        private ?string $introspectionTokenEndpoint,
        private ?string $userInformationsEndpoint,
        private ?string $endSessionEndpoint,
        private array $connectionScope,
        private ?string $loginClaim,
        private ?string $clientId,
        private ?string $clientSecret,
        private ?string $authenticationType,
        private bool $verifyPeer
    ) {
        // @todo: Add validation rules.
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isForced(): bool
    {
        return $this->isForced;
    }

    /**
     * @return array<string>
     */
    public function getTrustedClientAddresses(): array
    {
        return $this->trustedClientAddresses;
    }

    /**
     * @return array<string>
     */
    public function getBlacklistClientAddresses(): array
    {
        return $this->blacklistClientAddresses;
    }

    /**
     * @return string|null
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * @return string|null
     */
    public function getAuthorizationEndpoint(): ?string
    {
        return $this->authorizationEndpoint;
    }

    /**
     * @return string|null
     */
    public function getTokenEndpoint(): ?string
    {
        return $this->tokenEndpoint;
    }

    /**
     * @return string|null
     */
    public function getIntrospectionTokenEndpoint(): ?string
    {
        return $this->introspectionTokenEndpoint;
    }

    /**
     * @return string|null
     */
    public function getUserInformationsEndpoint(): ?string
    {
        return $this->userInformationsEndpoint;
    }

    /**
     * @return string|null
     */
    public function getEndSessionEndpoint(): ?string
    {
        return $this->endSessionEndpoint;
    }

    /**
     * @return array<string>
     */
    public function getConnectionScope(): array
    {
        return $this->connectionScope;
    }

    /**
     * @return string|null
     */
    public function getLoginClaim(): ?string
    {
        return $this->loginClaim;
    }

    /**
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * @return string|null
     */
    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    /**
     * @return string|null
     */
    public function getAuthenticationType(): ?string
    {
        return $this->authenticationType;
    }

    /**
     * @return boolean
     */
    public function isVerifyPeer(): bool
    {
        return $this->verifyPeer;
    }
}

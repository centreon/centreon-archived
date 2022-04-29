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

use Centreon\Domain\Common\Assertion\AssertionException;
use Security\Domain\Authentication\Interfaces\ProviderConfigurationInterface;

class OpenIdConfiguration implements ProviderConfigurationInterface
{
    public const DEFAULT_LOGIN_GLAIM = 'preferred_username';
    public const AUTHENTICATION_POST = 'client_secret_post';
    public const AUTHENTICATION_BASIC = 'client_secret_basic';
    public const TYPE = 'openid';
    public const NAME = 'openid';

    /**
     * @var int|null
     */
    private ?int $id;

    /**
     * @param boolean $isActive
     * @param boolean $isForced
     * @param string[] $trustedClientAddresses
     * @param string[] $blacklistClientAddresses
     * @param string|null $baseUrl
     * @param string|null $authorizationEndpoint
     * @param string|null $tokenEndpoint
     * @param string|null $introspectionTokenEndpoint
     * @param string|null $userInformationEndpoint
     * @param string|null $endSessionEndpoint
     * @param string[] $connectionScopes
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
        private ?string $userInformationEndpoint,
        private ?string $endSessionEndpoint,
        private array $connectionScopes,
        private ?string $loginClaim,
        private ?string $clientId,
        private ?string $clientSecret,
        private ?string $authenticationType,
        private bool $verifyPeer
    ) {
        foreach ($trustedClientAddresses as $trustedClientAddress) {
            if (
                filter_var($trustedClientAddress, FILTER_VALIDATE_IP) === false
                && filter_var($trustedClientAddress, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
            ) {
                throw AssertionException::ipOrDomain(
                    $trustedClientAddress,
                    'OpenIdConfiguration::trustedClientAddresses'
                );
            }
        }
        foreach ($blacklistClientAddresses as $blacklistClientAddress) {
            if (
                filter_var($blacklistClientAddress, FILTER_VALIDATE_IP) === false
                && filter_var($blacklistClientAddress, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
            ) {
                throw AssertionException::ipOrDomain(
                    $blacklistClientAddress,
                    'OpenIdConfiguration::blacklistClientAddresses'
                );
            }
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
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
     * @return string[]
     */
    public function getTrustedClientAddresses(): array
    {
        return $this->trustedClientAddresses;
    }

    /**
     * @return string[]
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
    public function getUserInformationEndpoint(): ?string
    {
        return $this->userInformationEndpoint;
    }

    /**
     * @return string|null
     */
    public function getEndSessionEndpoint(): ?string
    {
        return $this->endSessionEndpoint;
    }

    /**
     * @return string[]
     */
    public function getConnectionScopes(): array
    {
        return $this->connectionScopes;
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
    public function verifyPeer(): bool
    {
        return $this->verifyPeer;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE;
    }
}

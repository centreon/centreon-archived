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

namespace Core\Security\Domain\ProviderConfiguration\OpenId\Model;

use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;
use Centreon\Domain\Common\Assertion\AssertionException;
use Security\Domain\Authentication\Interfaces\ProviderConfigurationInterface;

abstract class AbstractConfiguration implements ProviderConfigurationInterface
{
    public const DEFAULT_LOGIN_GLAIM = 'preferred_username';
    public const AUTHENTICATION_POST = 'client_secret_post';
    public const AUTHENTICATION_BASIC = 'client_secret_basic';
    public const TYPE = 'openid';
    public const NAME = 'openid';
    public const DEFAULT_CLAIM_NAME = "groups";

    protected ?string $claimName = self::DEFAULT_CLAIM_NAME;

    /**
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * @var bool
     */
    protected bool $isForced = false;

    /**
     * @var string[]
     */
    protected array $trustedClientAddresses = [];

    /**
     * @var string[]
     */
    protected array $blacklistClientAddresses = [];

    /**
     * @var string|null
     */
    protected ?string $endSessionEndpoint = null;

    /**
     * @var string[]
     */
    protected array $connectionScopes = [];

    /**
     * @var string|null
     */
    protected ?string $loginClaim = null;

    /**
     * @var string|null
     */
    protected ?string $authenticationType = null;

    /**
     * @var bool
     */
    protected bool $verifyPeer = false;

    /**
     * @var array<AuthorizationRule>
     */
    protected array $authorizationRules = [];

    /**
     * @var boolean
     */
    protected bool $isAutoImportEnabled;

    /**
     * @var bool
     */
    protected bool $isActive = false;

    /**
     * @var string|null
     */
    protected ?string $clientId = null;

    /**
     * @var string|null
     */
    protected ?string $clientSecret = null;

    /**
     * @var string|null
     */
    protected ?string $baseUrl = null;

    /**
     * @var string|null
     */
    protected ?string $authorizationEndpoint = null;

    /**
     * @var string|null
     */
    protected ?string $tokenEndpoint = null;

    /**
     * @var string|null
     */
    protected ?string $introspectionTokenEndpoint = null;

    /**
     * @var string|null
     */
    protected ?string $userInformationEndpoint = null;

    /**
     * @var ContactTemplate|null
     */
    protected ?ContactTemplate $contactTemplate = null;

    /**
     * @var string|null
     */
    protected ?string $emailBindAttribute = null;

    /**
     * @var string|null
     */
    protected ?string $userAliasBindAttribute = null;

    /**
     * @var string|null
     */
    protected ?string $userNameBindAttribute = null;

    /**
     * @var ContactGroup|null
     */
    protected ?ContactGroup $contactGroup = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return bool
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
    public function getAuthenticationType(): ?string
    {
        return $this->authenticationType;
    }

    /**
     * @return bool
     */
    public function verifyPeer(): bool
    {
        return $this->verifyPeer;
    }

    /**
     * @return AuthorizationRule[]
     */
    public function getAuthorizationRules(): array
    {
        return $this->authorizationRules;
    }

    /**
     * @return bool
     */
    public function isAutoImportEnabled(): bool
    {
        return $this->isAutoImportEnabled;
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
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * @return ContactTemplate|null
     */
    public function getContactTemplate(): ?ContactTemplate
    {
        return $this->contactTemplate;
    }

    /**
     * @return string|null
     */
    public function getEmailBindAttribute(): ?string
    {
        return $this->emailBindAttribute;
    }

    /**
     * @return string|null
     */
    public function getUserAliasBindAttribute(): ?string
    {
        return $this->userAliasBindAttribute;
    }

    /**
     * @return string|null
     */
    public function getUserNameBindAttribute(): ?string
    {
        return $this->userNameBindAttribute;
    }

    /**
     * @return ContactGroup|null
     */
    public function getContactGroup(): ?ContactGroup
    {
        return $this->contactGroup;
    }

        /**
     * @param int|null $id
     * @return static
     */
    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param bool $isForced
     * @return static
     */
    public function setForced(bool $isForced): static
    {
        $this->isForced = $isForced;

        return $this;
    }

    /**
     * @param string[] $trustedClientAddresses
     * @return static
     * @throws AssertionException
     */
    public function setTrustedClientAddresses(array $trustedClientAddresses): static
    {
        $this->trustedClientAddresses = [];
        foreach ($trustedClientAddresses as $trustedClientAddress) {
            $this->addTrustedClientAddress($trustedClientAddress);
        }

        return $this;
    }

    /**
     * @param string $trustedClientAddress
     * @return static
     * @throws AssertionException
     */
    public function addTrustedClientAddress(string $trustedClientAddress): static
    {
        $this->validateClientAddressOrFail($trustedClientAddress, 'trustedClientAddresses');
        $this->trustedClientAddresses[] = $trustedClientAddress;

        return $this;
    }

    /**
     * @param string[] $blacklistClientAddresses
     * @return static
     * @throws AssertionException
     */
    public function setBlacklistClientAddresses(array $blacklistClientAddresses): static
    {
        $this->blacklistClientAddresses = [];
        foreach ($blacklistClientAddresses as $blacklistClientAddress) {
            $this->addBlacklistClientAddress($blacklistClientAddress);
        }

        return $this;
    }

    /**
     * @param string $blacklistClientAddress
     * @return static
     * @throws AssertionException
     */
    public function addBlacklistClientAddress(string $blacklistClientAddress): static
    {
        $this->validateClientAddressOrFail($blacklistClientAddress, 'blacklistClientAddresses');
        $this->blacklistClientAddresses[] = $blacklistClientAddress;

        return $this;
    }

    /**
     * @param string $clientAddress
     * @param string $fieldName
     * @throws AssertionException
     */
    private function validateClientAddressOrFail(string $clientAddress, string $fieldName): void
    {
        if (
            filter_var($clientAddress, FILTER_VALIDATE_IP) === false
            && filter_var($clientAddress, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
        ) {
            throw AssertionException::ipOrDomain(
                $clientAddress,
                'OpenIdConfiguration::' . $fieldName
            );
        }
    }

    /**
     * @param string|null $baseUrl
     * @return static
     */
    public function setBaseUrl(?string $baseUrl): static
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @param string|null $authorizationEndpoint
     * @return static
     */
    public function setAuthorizationEndpoint(?string $authorizationEndpoint): static
    {
        $this->authorizationEndpoint = $authorizationEndpoint;

        return $this;
    }

    /**
     * @param string|null $tokenEndpoint
     * @return static
     */
    public function setTokenEndpoint(?string $tokenEndpoint): static
    {
        $this->tokenEndpoint = $tokenEndpoint;

        return $this;
    }

    /**
     * @param string|null $introspectionTokenEndpoint
     * @return static
     */
    public function setIntrospectionTokenEndpoint(?string $introspectionTokenEndpoint): static
    {
        $this->introspectionTokenEndpoint = $introspectionTokenEndpoint;

        return $this;
    }

    /**
     * @param string|null $userInformationEndpoint
     * @return static
     */
    public function setUserInformationEndpoint(?string $userInformationEndpoint): static
    {
        $this->userInformationEndpoint = $userInformationEndpoint;

        return $this;
    }

    /**
     * @param string|null $endSessionEndpoint
     * @return static
     */
    public function setEndSessionEndpoint(?string $endSessionEndpoint): static
    {
        $this->endSessionEndpoint = $endSessionEndpoint;

        return $this;
    }

    /**
     * @param string[] $connectionScopes
     * @return static
     */
    public function setConnectionScopes(array $connectionScopes): static
    {
        $this->connectionScopes = [];
        foreach ($connectionScopes as $connectionScope) {
            $this->addConnectionScope($connectionScope);
        }

        return $this;
    }

    /**
     * @param string $connectionScope
     * @return static
     */
    public function addConnectionScope(string $connectionScope): static
    {
        $this->connectionScopes[] = $connectionScope;

        return $this;
    }

    /**
     * @param string|null $loginClaim
     * @return static
     */
    public function setLoginClaim(?string $loginClaim): static
    {
        $this->loginClaim = $loginClaim;

        return $this;
    }

    /**
     * @param string|null $clientId
     * @return static
     */
    public function setClientId(?string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @param string|null $clientSecret
     * @return static
     */
    public function setClientSecret(?string $clientSecret): static
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * @param string|null $authenticationType
     * @return static
     */
    public function setAuthenticationType(?string $authenticationType): static
    {
        $this->authenticationType = $authenticationType;

        return $this;
    }

    /**
     * @param bool $verifyPeer
     * @return static
     */
    public function setVerifyPeer(bool $verifyPeer): static
    {
        $this->verifyPeer = $verifyPeer;

        return $this;
    }

    /**
     * @param boolean $isAutoImportEnabled
     * @return static
     */
    public function setAutoImportEnabled(bool $isAutoImportEnabled): static
    {
        $this->isAutoImportEnabled = $isAutoImportEnabled;

        return $this;
    }

    /**
     * @param ContactTemplate|null $contactTemplate
     * @return static
     */
    public function setContactTemplate(?ContactTemplate $contactTemplate): static
    {
        $this->contactTemplate = $contactTemplate;

        return $this;
    }

    /**
     * @param string|null $emailBindAttribute
     * @return static
     */
    public function setEmailBindAttribute(?string $emailBindAttribute): static
    {
        $this->emailBindAttribute = $emailBindAttribute;

        return $this;
    }

    /**
     * @param string|null $userAliasBindAttribute
     * @return static
     */
    public function setUserAliasBindAttribute(?string $userAliasBindAttribute): static
    {
        $this->userAliasBindAttribute = $userAliasBindAttribute;

        return $this;
    }

    /**
     * @param string|null $userNameBindAttribute
     * @return static
     */
    public function setUserNameBindAttribute(?string $userNameBindAttribute): static
    {
        $this->userNameBindAttribute = $userNameBindAttribute;

        return $this;
    }

    /**
     * @param AuthorizationRule[] $authorizationRules
     * @return static
     * @throws \TypeError
     */
    public function setAuthorizationRules(array $authorizationRules): static
    {
        $this->authorizationRules = [];
        foreach ($authorizationRules as $authorizationRule) {
            $this->addAuthorizationRule($authorizationRule);
        }

        return $this;
    }

    /**
     * @param AuthorizationRule $authorizationRule
     * @return static
     */
    public function addAuthorizationRule(AuthorizationRule $authorizationRule): static
    {
        $this->authorizationRules[] = $authorizationRule;

        return $this;
    }

    /**
     * @param ContactGroup|null $contactGroup
     * @return static
     */
    public function setContactGroup(?ContactGroup $contactGroup): static
    {
        $this->contactGroup = $contactGroup;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getClaimName(): ?string
    {
        return $this->claimName;
    }

    /**
     * @param string|null $claimName
     * @return static
     */
    public function setClaimName(?string $claimName): static
    {
        $this->claimName = $claimName;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }
}

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
use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Common\Assertion\AssertionException;
use Security\Domain\Authentication\Interfaces\ProviderConfigurationInterface;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\AuthorizationRule;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;

class Configuration implements ProviderConfigurationInterface
{
    public const DEFAULT_LOGIN_GLAIM = 'preferred_username';
    public const AUTHENTICATION_POST = 'client_secret_post';
    public const AUTHENTICATION_BASIC = 'client_secret_basic';
    public const TYPE = 'openid';
    public const NAME = 'openid';

    /**
     * @var int|null
     */
    private ?int $id = null;

    /**
     * @var bool
     */
    private bool $isForced = false;

    /**
     * @var string[]
     */
    private array $trustedClientAddresses = [];

    /**
     * @var string[]
     */
    private array $blacklistClientAddresses = [];

    /**
     * @var string|null
     */
    private ?string $endSessionEndpoint = null;

    /**
     * @var string[]
     */
    private array $connectionScopes = [];

    /**
     * @var string|null
     */
    private ?string $loginClaim = null;

    /**
     * @var string|null
     */
    private ?string $authenticationType = null;

    /**
     * @var bool
     */
    private bool $verifyPeer = false;

    /**
     * @var array<AuthorizationRule>
     */
    private array $authorizationRules = [];

    /**
     * @param boolean $isActive
     * @param boolean $isAutoImportEnabled
     * @param string|null $clientId
     * @param string|null $clientSecret
     * @param string|null $baseUrl
     * @param string|null $authorizationEndpoint
     * @param string|null $tokenEndpoint
     * @param string|null $introspectionTokenEndpoint
     * @param string|null $userInformationEndpoint
     * @param ContactTemplate|null $contactTemplate
     * @param string|null $emailBindAttribute
     * @param string|null $userAliasBindAttribute
     * @param string|null $userNameBindAttribute
     * @param ContactGroup|null $contactGroup
     * @param string|null $claimName
     * @throws OpenIdConfigurationException
     */
    public function __construct(
        private bool $isActive,
        private bool $isAutoImportEnabled,
        private ?string $clientId = null,
        private ?string $clientSecret = null,
        private ?string $baseUrl = null,
        private ?string $authorizationEndpoint = null,
        private ?string $tokenEndpoint = null,
        private ?string $introspectionTokenEndpoint = null,
        private ?string $userInformationEndpoint = null,
        private ?ContactTemplate $contactTemplate = null,
        private ?string $emailBindAttribute = null,
        private ?string $userAliasBindAttribute = null,
        private ?string $userNameBindAttribute = null,
        private ?ContactGroup $contactGroup = null,
        private ?string $claimName = null
    ) {
        if ($isActive === true) {
            Assertion::notEmpty($clientId, "Configuration::clientId");
            Assertion::notEmpty($clientSecret, "Configuration:clientSecret");
            Assertion::notEmpty($baseUrl, "Configuration::baseUrl");
            Assertion::notEmpty($authorizationEndpoint, "Configuration:authorizationEndpoint");
            Assertion::notEmpty($tokenEndpoint, "Configuration::tokenEndpoint");
            Assertion::notNull($contactGroup, "Configuration::contactGroup");
            Assertion::notEmpty($claimName, "Configuration::claimName");
            if (empty($introspectionTokenEndpoint) && empty($userInformationEndpoint)) {
                throw OpenIdConfigurationException::missingInformationEndpoint();
            }
            if ($isAutoImportEnabled === true) {
                $missingMandatoryParameters = [];
                if ($contactTemplate === null) {
                    $missingMandatoryParameters[] = 'contact_template';
                }
                if (empty($emailBindAttribute)) {
                    $missingMandatoryParameters[] = 'email_bind_attribute';
                }
                if (empty($userAliasBindAttribute)) {
                    $missingMandatoryParameters[] = 'alias_bind_attribute';
                }
                if (empty($userNameBindAttribute)) {
                    $missingMandatoryParameters[] = 'fullname_bind_attribute';
                }
                if (! empty($missingMandatoryParameters)) {
                    throw OpenIdConfigurationException::missingAutoImportMandatoryParameters(
                        $missingMandatoryParameters
                    );
                }
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
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return bool
     */
    public function isForced(): bool
    {
        return $this->isForced;
    }

    /**
     * @param bool $isForced
     * @return self
     */
    public function setForced(bool $isForced): self
    {
        $this->isForced = $isForced;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTrustedClientAddresses(): array
    {
        return $this->trustedClientAddresses;
    }

    /**
     * @param string[] $trustedClientAddresses
     * @return self
     * @throws AssertionException
     */
    public function setTrustedClientAddresses(array $trustedClientAddresses): self
    {
        $this->trustedClientAddresses = [];
        foreach ($trustedClientAddresses as $trustedClientAddress) {
            $this->addTrustedClientAddress($trustedClientAddress);
        }

        return $this;
    }

    /**
     * @param string $trustedClientAddress
     * @return self
     * @throws AssertionException
     */
    public function addTrustedClientAddress(string $trustedClientAddress): self
    {
        $this->validateClientAddressOrFail($trustedClientAddress, 'trustedClientAddresses');
        $this->trustedClientAddresses[] = $trustedClientAddress;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getBlacklistClientAddresses(): array
    {
        return $this->blacklistClientAddresses;
    }

    /**
     * @param string[] $blacklistClientAddresses
     * @return self
     * @throws AssertionException
     */
    public function setBlacklistClientAddresses(array $blacklistClientAddresses): self
    {
        $this->blacklistClientAddresses = [];
        foreach ($blacklistClientAddresses as $blacklistClientAddress) {
            $this->addBlacklistClientAddress($blacklistClientAddress);
        }

        return $this;
    }

    /**
     * @param string $blacklistClientAddress
     * @return self
     * @throws AssertionException
     */
    public function addBlacklistClientAddress(string $blacklistClientAddress): self
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
     * @param string|null $endSessionEndpoint
     * @return self
     */
    public function setEndSessionEndpoint(?string $endSessionEndpoint): self
    {
        $this->endSessionEndpoint = $endSessionEndpoint;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getConnectionScopes(): array
    {
        return $this->connectionScopes;
    }

    /**
     * @param string[] $connectionScopes
     * @return self
     */
    public function setConnectionScopes(array $connectionScopes): self
    {
        $this->connectionScopes = [];
        foreach ($connectionScopes as $connectionScope) {
            $this->addConnectionScope($connectionScope);
        }

        return $this;
    }

    /**
     * @param string $connectionScope
     * @return self
     */
    public function addConnectionScope(string $connectionScope): self
    {
        $this->connectionScopes[] = $connectionScope;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLoginClaim(): ?string
    {
        return $this->loginClaim;
    }

    /**
     * @param string|null $loginClaim
     * @return self
     */
    public function setLoginClaim(?string $loginClaim): self
    {
        $this->loginClaim = $loginClaim;

        return $this;
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
     * @param string|null $authenticationType
     * @return self
     */
    public function setAuthenticationType(?string $authenticationType): self
    {
        $this->authenticationType = $authenticationType;

        return $this;
    }

    /**
     * @return bool
     */
    public function verifyPeer(): bool
    {
        return $this->verifyPeer;
    }

    /**
     * @param bool $verifyPeer
     * @return self
     */
    public function setVerifyPeer(bool $verifyPeer): self
    {
        $this->verifyPeer = $verifyPeer;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * @return bool
     */
    public function isAutoImportEnabled(): bool
    {
        return $this->isAutoImportEnabled;
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
     * @return string|null
     */
    public function getClaimName(): ?string
    {
        return $this->claimName;
    }

    /**
     * @return ContactGroup|null
     */
    public function getContactGroup(): ?ContactGroup
    {
        return $this->contactGroup;
    }

    /**
     * @return AuthorizationRule[]
     */
    public function getAuthorizationRules(): array
    {
        return $this->authorizationRules;
    }

    /**
     * @param AuthorizationRule[] $authorizationRules
     * @return self
     * @throws \TypeError
     */
    public function setAuthorizationRules(array $authorizationRules): self
    {
        $this->authorizationRules = [];
        foreach ($authorizationRules as $authorizationRule) {
            $this->addAuthorizationRule($authorizationRule);
        }

        return $this;
    }

    /**
     * @param AuthorizationRule $authorizationRule
     * @return self
     */
    public function addAuthorizationRule(AuthorizationRule $authorizationRule)
    {
        $this->authorizationRules[] = $authorizationRule;

        return $this;
    }
}

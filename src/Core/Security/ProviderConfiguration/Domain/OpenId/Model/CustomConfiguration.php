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

namespace Core\Security\ProviderConfiguration\Domain\OpenId\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\ProviderConfiguration\Domain\CustomConfigurationInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\OpenIdConfigurationException;
use TypeError;

final class CustomConfiguration implements CustomConfigurationInterface, OpenIdCustomConfigurationInterface
{
    public const DEFAULT_LOGIN_CLAIM = 'preferred_username';
    public const AUTHENTICATION_POST = 'client_secret_post';
    public const AUTHENTICATION_BASIC = 'client_secret_basic';
    public const TYPE = 'openid';
    public const NAME = 'openid';
    public const DEFAULT_CLAIM_NAME = "groups";

    private ?string $claimName = self::DEFAULT_CLAIM_NAME;

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
     * @var boolean
     */
    private bool $isAutoImportEnabled = false;

    /**
     * @var string|null
     */
    private ?string $clientId = null;

    /**
     * @var string|null
     */
    private ?string $clientSecret = null;

    /**
     * @var string|null
     */
    private ?string $baseUrl = null;

    /**
     * @var string|null
     */
    private ?string $authorizationEndpoint = null;

    /**
     * @var string|null
     */
    private ?string $tokenEndpoint = null;

    /**
     * @var string|null
     */
    private ?string $introspectionTokenEndpoint = null;

    /**
     * @var string|null
     */
    private ?string $userInformationEndpoint = null;

    /**
     * @var ContactTemplate|null
     */
    private ?ContactTemplate $contactTemplate = null;

    /**
     * @var string|null
     */
    private ?string $emailBindAttribute = null;

    /**
     * @var string|null
     */
    private ?string $userNameBindAttribute = null;

    /**
     * @var ContactGroup|null
     */
    private ?ContactGroup $contactGroup = null;

    /**
     * @param array $json
     * @throws OpenIdConfigurationException
     */
    public function __construct(array $json)
    {
        $this->create($json);
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
                'OpenIdCustomConfiguration::' . $fieldName
            );
        }
    }

    /**
     * @param string|null $baseUrl
     * @return self
     */
    public function setBaseUrl(?string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @param string|null $authorizationEndpoint
     * @return self
     */
    public function setAuthorizationEndpoint(?string $authorizationEndpoint): self
    {
        $this->authorizationEndpoint = $authorizationEndpoint;

        return $this;
    }

    /**
     * @param string|null $tokenEndpoint
     * @return self
     */
    public function setTokenEndpoint(?string $tokenEndpoint): self
    {
        $this->tokenEndpoint = $tokenEndpoint;

        return $this;
    }

    /**
     * @param string|null $introspectionTokenEndpoint
     * @return self
     */
    public function setIntrospectionTokenEndpoint(?string $introspectionTokenEndpoint): self
    {
        $this->introspectionTokenEndpoint = $introspectionTokenEndpoint;

        return $this;
    }

    /**
     * @param string|null $userInformationEndpoint
     * @return self
     */
    public function setUserInformationEndpoint(?string $userInformationEndpoint): self
    {
        $this->userInformationEndpoint = $userInformationEndpoint;

        return $this;
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
     * @param string|null $loginClaim
     * @return self
     */
    public function setLoginClaim(?string $loginClaim): self
    {
        $this->loginClaim = $loginClaim;

        return $this;
    }

    /**
     * @param string|null $clientId
     * @return self
     */
    public function setClientId(?string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @param string|null $clientSecret
     * @return self
     */
    public function setClientSecret(?string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
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
     * @param bool $verifyPeer
     * @return self
     */
    public function setVerifyPeer(bool $verifyPeer): self
    {
        $this->verifyPeer = $verifyPeer;

        return $this;
    }

    /**
     * @param boolean $isAutoImportEnabled
     * @return self
     */
    public function setAutoImportEnabled(bool $isAutoImportEnabled): self
    {
        $this->isAutoImportEnabled = $isAutoImportEnabled;

        return $this;
    }

    /**
     * @param ContactTemplate|null $contactTemplate
     * @return self
     */
    public function setContactTemplate(?ContactTemplate $contactTemplate): self
    {
        $this->contactTemplate = $contactTemplate;

        return $this;
    }

    /**
     * @param string|null $emailBindAttribute
     * @return self
     */
    public function setEmailBindAttribute(?string $emailBindAttribute): self
    {
        $this->emailBindAttribute = $emailBindAttribute;

        return $this;
    }

    /**
     * @param string|null $userNameBindAttribute
     * @return self
     */
    public function setUserNameBindAttribute(?string $userNameBindAttribute): self
    {
        $this->userNameBindAttribute = $userNameBindAttribute;

        return $this;
    }

    /**
     * @param AuthorizationRule[] $authorizationRules
     * @return self
     * @throws TypeError
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
    public function addAuthorizationRule(AuthorizationRule $authorizationRule): self
    {
        $this->authorizationRules[] = $authorizationRule;

        return $this;
    }

    /**
     * @param ContactGroup|null $contactGroup
     * @return self
     */
    public function setContactGroup(?ContactGroup $contactGroup): self
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
     * @return self
     */
    public function setClaimName(?string $claimName): self
    {
        $this->claimName = $claimName;

        return $this;
    }

    /**
     * @param array $json
     * @return void
     * @throws OpenIdConfigurationException
     */
    public function create(array $json): void
    {
        if (isset($json['is_active']) && $json['is_active']) {
            $this->validateMandatoryFields($json);
        }

        $this->setClientId($json['client_id']);
        $this->setAutoImportEnabled($json['auto_import']);
        $this->setClientSecret($json['client_secret']);
        $this->setBaseUrl($json['base_url']);
        $this->setAuthorizationEndpoint($json['authorization_endpoint']);
        $this->setTokenEndpoint($json['token_endpoint']);
        $this->setIntrospectionTokenEndpoint($json['introspection_token_endpoint']);
        $this->setUserInformationEndpoint($json['userinfo_endpoint']);
        $this->setContactTemplate($json['contact_template']);
        $this->setEmailBindAttribute($json['email_bind_attribute']);
        $this->setUserNameBindAttribute($json['fullname_bind_attribute']);
        $this->setTrustedClientAddresses($json['trusted_client_addresses']);
        $this->setBlacklistClientAddresses($json['blacklist_client_addresses']);
        $this->setEndSessionEndpoint($json['endsession_endpoint']);
        $this->setConnectionScopes($json['connection_scopes']);
        $this->setLoginClaim($json['login_claim']);
        $this->setAuthenticationType($json['authentication_type']);
        $this->setVerifyPeer($json['verify_peer']);
        $this->setContactGroup($json['contact_group']);
        $this->setClaimName($json['claim_name']);
        if (array_key_exists('authorization_rules', $json)) {
            $this->setAuthorizationRules($json['authorization_rules']);
        }
    }

    /**
     * @throws OpenIdConfigurationException
     */
    private function validateMandatoryFields(array $json): void
    {
        $mandatoryFields = [
            'client_id',
            'client_secret',
            'base_url',
            'authorization_endpoint',
            'token_endpoint',
            'contact_group'
        ];

        $emptyParameters = [];
        foreach ($mandatoryFields as $key) {
            if (empty($json[$key])) {
                $emptyParameters[] = $key;
            }
        }

        if (!empty($emptyParameters)) {
            throw OpenIdConfigurationException::missingMandatoryParameters($emptyParameters);
        }

        if (empty($json['introspection_token_endpoint']) && empty($json['userinfo_endpoint'])) {
            throw OpenIdConfigurationException::missingInformationEndpoint();
        }

        if ($json['auto_import'] === true) {
            self::validateParametersForAutoImport(
                $json['contact_template'],
                $json['email_bind_attribute'],
                $json['fullname_bind_attribute']
            );
        }
    }

    /**
     * Validate mandatory parameters for auto import
     *
     * @param ContactTemplate|null $contactTemplate
     * @param string|null $emailBindAttribute
     * @param string|null $userNameBindAttribute
     * @throws OpenIdConfigurationException
     */
    private static function validateParametersForAutoImport(
        ?ContactTemplate $contactTemplate,
        ?string          $emailBindAttribute,
        ?string          $userNameBindAttribute
    ): void
    {
        $missingMandatoryParameters = [];
        if ($contactTemplate === null) {
            $missingMandatoryParameters[] = 'contact_template';
        }
        if (empty($emailBindAttribute)) {
            $missingMandatoryParameters[] = 'email_bind_attribute';
        }
        if (empty($userNameBindAttribute)) {
            $missingMandatoryParameters[] = 'fullname_bind_attribute';
        }
        if (!empty($missingMandatoryParameters)) {
            throw OpenIdConfigurationException::missingAutoImportMandatoryParameters(
                $missingMandatoryParameters
            );
        }
    }
}

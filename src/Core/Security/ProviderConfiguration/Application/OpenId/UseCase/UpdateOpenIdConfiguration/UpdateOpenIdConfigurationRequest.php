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

namespace Core\Security\ProviderConfiguration\Application\OpenId\UseCase\UpdateOpenIdConfiguration;

class UpdateOpenIdConfigurationRequest
{
    /**
     * @var int|null
     */
    public ?int $contactGroupId = null;

    /**
     * @var boolean
     */
    public bool $isActive = false;

    /**
     * @var boolean
     */
    public bool $isForced = false;

    /**
     * @var string[]
     */
    public array $trustedClientAddresses = [];

    /**
     * @var string[]
     */
    public array $blacklistClientAddresses = [];

    /**
     * @var string|null
     */
    public ?string $baseUrl = null;

    /**
     * @var string|null
     */
    public ?string $authorizationEndpoint = null;

    /**
     * @var string|null
     */
    public ?string $tokenEndpoint = null;

    /**
     * @var string|null
     */
    public ?string $introspectionTokenEndpoint = null;

    /**
     * @var string|null
     */
    public ?string $userInformationEndpoint = null;

    /**
     * @var string|null
     */
    public ?string $endSessionEndpoint = null;

    /**
     * @var string[]
     */
    public array $connectionScopes = [];

    /**
     * @var string|null
     */
    public ?string $loginClaim = null;

    /**
     * @var string|null
     */
    public ?string $clientId = null;

    /**
     * @var string|null
     */
    public ?string $clientSecret = null;

    /**
     * @var string|null
     */
    public ?string $authenticationType = null;

    /**
     * @var boolean
     */
    public bool $verifyPeer = false;

    /**
     * @var boolean
     */
    public bool $isAutoImportEnabled = false;

    /**
     * @var array{id: int, name: string}|null
     */
    public ?array $contactTemplate = null;

    /**
     * @var string|null
     */
    public ?string $emailBindAttribute = null;

    /**
     * @var string|null
     */
    public ?string $userNameBindAttribute = null;

    /**
     * @var string|null
     */
    public ?string $claimName = null;

    /**
     * @var array<array{claim_value: string, access_group_id: int}>
     */
    public array $authorizationRules = [];

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'is_forced' => $this->isForced,
            'is_active' => $this->isActive,
            'contact_group_id' => $this->contactGroupId,
            'contact_template' => $this->contactTemplate,
            'authorization_rules' => $this->authorizationRules,
            'auto_import' => $this->isAutoImportEnabled,
            'client_id' => $this->clientId,
            'authentication_type' => $this->authenticationType,
            'authorization_endpoint' => $this->authorizationEndpoint,
            'base_url' => $this->baseUrl,
            'blacklist_client_addresses' => $this->blacklistClientAddresses,
            'claim_name' => $this->claimName,
            'client_secret' => $this->clientSecret,
            'connection_scopes' => $this->connectionScopes,
            'email_bind_attribute' => $this->emailBindAttribute,
            'endsession_endpoint' => $this->endSessionEndpoint,
            'introspection_token_endpoint' => $this->introspectionTokenEndpoint,
            'login_claim' => $this->loginClaim,
            'token_endpoint' => $this->tokenEndpoint,
            'trusted_client_addresses' => $this->trustedClientAddresses,
            'userinfo_endpoint' => $this->userInformationEndpoint,
            'fullname_bind_attribute' => $this->userNameBindAttribute,
            'verify_peer' => $this->verifyPeer
        ];
    }
}

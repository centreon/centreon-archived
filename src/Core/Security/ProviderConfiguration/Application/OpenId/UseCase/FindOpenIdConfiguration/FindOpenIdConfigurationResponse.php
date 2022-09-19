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

namespace Core\Security\ProviderConfiguration\Application\OpenId\UseCase\FindOpenIdConfiguration;

use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\AuthenticationConditions;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\AuthorizationRule;

class FindOpenIdConfigurationResponse
{
    /**
     * @var boolean
     */
    public bool $isActive = false;

    /**
     * @var boolean
     */
    public bool $isForced = false;

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
     * @var array<string,int|string>|null
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
     * @var array{id: int, name: string}|null
     */
    public ?array $contactGroup = null;

    /**
     * @var array<array{claim_value: string, access_group:array{id: int, name: string}}>
     */
    public array $authorizationRules = [];

    /**
     * @var array<string,bool|string|string[]>
     */
    public array $authenticationConditions = [];

    /**
     * @param ContactTemplate $contactTemplate
     * @return array<string,int|string>
     */
    public static function contactTemplateToArray(ContactTemplate $contactTemplate): array
    {
        return [
            "id" => $contactTemplate->getId(),
            "name" => $contactTemplate->getName(),
        ];
    }

    /**
     * @param ContactGroup $contactGroup
     * @return array{id: int, name: string}
     */
    public static function contactGroupToArray(ContactGroup $contactGroup): array
    {
        return [
            "id" => $contactGroup->getId(),
            "name" => $contactGroup->getName()
        ];
    }

    /**
     * @param AuthorizationRule[] $authorizationRules
     * @return array<array{claim_value: string, access_group:array{id: int, name: string}}>
     */
    public static function authorizationRulesToArray(array $authorizationRules): array
    {
        return array_map(function (AuthorizationRule $authorizationRule) {
            return [
                'claim_value' => $authorizationRule->getClaimValue(),
                'access_group' => [
                    "id" => $authorizationRule->getAccessGroup()->getId(),
                    "name" => $authorizationRule->getAccessGroup()->getName()
                ]
            ];
        }, $authorizationRules);
    }

    /**
     * @param AuthenticationConditions $authenticationConditions
     * @return array<string,bool|string|string[]>
     */
    public static function authenticationConditionsToArray(AuthenticationConditions $authenticationConditions): array
    {
        return [
            "is_enabled" => $authenticationConditions->isEnabled(),
            "attribute_path" => $authenticationConditions->getAttributePath(),
            "endpoint" => $authenticationConditions->getEndpoint()->toArray(),
            "authorized_values" => $authenticationConditions->getAuthorizedValues(),
            "trusted_client_addresses" => $authenticationConditions->getTrustedClientAddresses(),
            "blacklist_client_addresses" => $authenticationConditions->getBlacklistClientAddresses()
        ];
    }
}

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

namespace Core\Security\Application\ProviderConfiguration\OpenId\Builder;

use Core\Security\Application\ProviderConfiguration\OpenId\UseCase\{
    UpdateOpenIdConfiguration\UpdateOpenIdConfigurationRequest
};
use Core\Contact\Domain\Model\ContactGroup;
use Centreon\Domain\Common\Assertion\Assertion;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\{
    Configuration,
    AuthorizationRule
};
use Assert\AssertionFailedException;

class ConfigurationBuilder
{
    /**
     * Create OpenId Configuration from Request DTO
     *
     * @param UpdateOpenIdConfigurationRequest $request
     * @param ContactTemplate|null $contactTemplate
     * @param ContactGroup|null $contactGroup
     * @param AuthorizationRule[] $authorizationRules
     * @return Configuration
     * @throws OpenIdConfigurationException|AssertionFailedException
     */
    public static function createConfigurationFromRequest(
        UpdateOpenIdConfigurationRequest $request,
        ?ContactTemplate $contactTemplate,
        ?ContactGroup $contactGroup,
        array $authorizationRules
    ): Configuration {
        if ($request->isActive === true) {
            Assertion::notEmpty($request->clientId, "Configuration::clientId");
            Assertion::notEmpty($request->clientSecret, "Configuration::clientSecret");
            Assertion::notEmpty($request->baseUrl, "Configuration::baseUrl");
            Assertion::notEmpty($request->authorizationEndpoint, "Configuration::authorizationEndpoint");
            Assertion::notEmpty($request->tokenEndpoint, "Configuration::tokenEndpoint");
            Assertion::notNull($contactGroup, "Configuration::contactGroup");
            if (empty($request->introspectionTokenEndpoint) && empty($request->userInformationEndpoint)) {
                throw OpenIdConfigurationException::missingInformationEndpoint();
            }
            if ($request->isAutoImportEnabled === true) {
                self::validateParametersForAutoImport(
                    $contactTemplate,
                    $request->emailBindAttribute,
                    $request->userAliasBindAttribute,
                    $request->userNameBindAttribute
                );
            }
        }

        return (new Configuration())
            ->setClientId($request->clientId)
            ->setForced($request->isForced)
            ->setActive($request->isActive)
            ->setTrustedClientAddresses($request->trustedClientAddresses)
            ->setBlacklistClientAddresses($request->blacklistClientAddresses)
            ->setEndSessionEndpoint($request->endSessionEndpoint)
            ->setConnectionScopes($request->connectionScopes)
            ->setLoginClaim($request->loginClaim)
            ->setAuthenticationType($request->authenticationType)
            ->setVerifyPeer($request->verifyPeer)
            ->setAuthorizationRules($authorizationRules)
            ->setAutoImportEnabled($request->isAutoImportEnabled)
            ->setClientSecret($request->clientSecret)
            ->setBaseUrl($request->baseUrl)
            ->setAuthorizationEndpoint($request->authorizationEndpoint)
            ->setTokenEndpoint($request->tokenEndpoint)
            ->setIntrospectionTokenEndpoint($request->introspectionTokenEndpoint)
            ->setUserInformationEndpoint($request->userInformationEndpoint)
            ->setContactTemplate($contactTemplate)
            ->setEmailBindAttribute($request->emailBindAttribute)
            ->setUserAliasBindAttribute($request->userAliasBindAttribute)
            ->setUserNameBindAttribute($request->userNameBindAttribute)
            ->setContactGroup($contactGroup)
            ->setClaimName($request->claimName);
    }

    /**
     * Create OpenId Configuration from data storage record
     *
     * @param array<string, mixed> $record
     * @param array<string, mixed> $customConfiguration
     * @return Configuration
     */
    public static function createConfigurationFromRecord(array $record, array $customConfiguration): Configuration
    {
        if ($record['is_active'] === true) {
            Assertion::notEmpty($customConfiguration['client_id'], "Configuration::clientId");
            Assertion::notEmpty($customConfiguration['client_secret'], "Configuration::clientSecret");
            Assertion::notEmpty($customConfiguration['base_url'], "Configuration::baseUrl");
            Assertion::notEmpty($customConfiguration['authorization_endpoint'], "Configuration::authorizationEndpoint");
            Assertion::notEmpty($customConfiguration['token_endpoint'], "Configuration::tokenEndpoint");
            Assertion::notNull($customConfiguration['contact_group'], "Configuration::contactGroup");
            if (
                empty($customConfiguration['introspection_token_endpoint'])
                && empty($customConfiguration['userinfo_endpoint'])
            ) {
                throw OpenIdConfigurationException::missingInformationEndpoint();
            }
            if ($customConfiguration['auto_import'] === '1') {
                self::validateParametersForAutoImport(
                    $customConfiguration['contact_template'],
                    $customConfiguration['email_bind_attribute'],
                    $customConfiguration['alias_bind_attribute'],
                    $customConfiguration['fullname_bind_attribute']
                );
            }
        }

        return (new Configuration())
            ->setId((int) $record['id'])
            ->setForced($record['is_forced'] === '1')
            ->setActive($record['is_active'] === true)
            ->setClientId($customConfiguration['client_id'])
            ->setAutoImportEnabled($customConfiguration['auto_import'] === '1')
            ->setClientSecret($customConfiguration['client_secret'])
            ->setBaseUrl($customConfiguration['base_url'])
            ->setAuthorizationEndpoint($customConfiguration['authorization_endpoint'])
            ->setTokenEndpoint($customConfiguration['token_endpoint'])
            ->setIntrospectionTokenEndpoint($customConfiguration['introspection_token_endpoint'])
            ->setUserInformationEndpoint($customConfiguration['userinfo_endpoint'])
            ->setContactTemplate($customConfiguration['contact_template'])
            ->setEmailBindAttribute($customConfiguration['email_bind_attribute'])
            ->setUserAliasBindAttribute($customConfiguration['alias_bind_attribute'])
            ->setUserNameBindAttribute($customConfiguration['fullname_bind_attribute'])
            ->setTrustedClientAddresses($customConfiguration['trusted_client_addresses'])
            ->setBlacklistClientAddresses($customConfiguration['blacklist_client_addresses'])
            ->setEndSessionEndpoint($customConfiguration['endsession_endpoint'])
            ->setConnectionScopes($customConfiguration['connection_scopes'])
            ->setLoginClaim($customConfiguration['login_claim'])
            ->setAuthenticationType($customConfiguration['authentication_type'])
            ->setVerifyPeer($customConfiguration['verify_peer'])
            ->setContactGroup($customConfiguration['contact_group'])
            ->setClaimName($customConfiguration['claim_name'])
            ->setAuthorizationRules($customConfiguration['authorization_rules']);
    }

    /**
     * Validate mandatory parameters for auto import
     *
     * @param ContactTemplate|null $contactTemplate
     * @param string|null $emailBindAttribute
     * @param string|null $userAliasBindAttribute
     * @param string|null $userNameBindAttribute
     * @throws OpenIdConfigurationException
     */
    private static function validateParametersForAutoImport(
        ?ContactTemplate $contactTemplate,
        ?string $emailBindAttribute,
        ?string $userAliasBindAttribute,
        ?string $userNameBindAttribute
    ): void {
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
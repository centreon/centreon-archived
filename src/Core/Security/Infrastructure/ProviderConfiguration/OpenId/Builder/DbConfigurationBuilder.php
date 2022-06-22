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

namespace Core\Security\Infrastructure\ProviderConfiguration\OpenId\Builder;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\Configuration;

class DbConfigurationBuilder
{
    /**
     * Create OpenId Configuration from data storage record
     *
     * @param array<string, mixed> $record
     * @param array<string, mixed> $customConfiguration
     * @return Configuration
     * @throws OpenIdConfigurationException|AssertionException
     */
    public static function create(array $record, array $customConfiguration): Configuration
    {
        /**
         * If the configuration is active, check that all mandatory parameters are correctly set to be able to use this
         * provider configuration
         */
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
            if ($customConfiguration['auto_import'] === true) {
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
            ->setActive($record['is_active'] === '1')
            ->setClientId($customConfiguration['client_id'])
            ->setAutoImportEnabled($customConfiguration['auto_import'])
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

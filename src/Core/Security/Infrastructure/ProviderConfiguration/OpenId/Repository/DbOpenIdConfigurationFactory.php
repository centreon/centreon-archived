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

namespace Core\Security\Infrastructure\ProviderConfiguration\OpenId\Repository;

use Core\Security\Domain\ProviderConfiguration\OpenId\Model\ActiveConfiguration;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\AbstractConfiguration;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\NonActiveConfiguration;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;

class DbOpenIdConfigurationFactory
{
    /**
     * @param array<string,mixed> $record
     * @param array<string,mixed> $customConfiguration
     * @return AbstractConfiguration
     * @throws OpenIdConfigurationException
     */
    public static function createFromRecord(array $record, array $customConfiguration): AbstractConfiguration
    {
        if ($record['is_active'] === '1') {
            $configuration = self::createActiveConfiguration($record, $customConfiguration);
        } else {
            $configuration = self::createNonActiveConfiguration($record, $customConfiguration);
        }

        return $configuration;
    }

    /**
     * Create Active Configuration
     *
     * @param array<string,mixed> $record
     * @param array<string,mixed> $customConfiguration
     * @return ActiveConfiguration
     * @throws OpenIdConfigurationException
     */
    private static function createActiveConfiguration(array $record, array $customConfiguration): ActiveConfiguration
    {
        $configuration = new ActiveConfiguration(
            $customConfiguration['auto_import'] === '1',
            $customConfiguration['client_id'],
            $customConfiguration['client_secret'],
            $customConfiguration['base_url'],
            $customConfiguration['authorization_endpoint'],
            $customConfiguration['token_endpoint'],
            $customConfiguration['introspection_token_endpoint'],
            $customConfiguration['userinfo_endpoint'],
            $customConfiguration['contact_group'],
            $customConfiguration['contact_template'],
            $customConfiguration['email_bind_attribute'],
            $customConfiguration['alias_bind_attribute'],
            $customConfiguration['fullname_bind_attribute']
        );

        $configuration
            ->setId((int) $record['id'])
            ->setForced($record['is_forced'] === '1')
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

        return $configuration;
    }

    /**
     * Create Non Active Configuration
     *
     * @param array<string,mixed> $record
     * @param array<string,mixed> $customConfiguration
     * @return NonActiveConfiguration
     */
    private static function createNonActiveConfiguration(
        array $record,
        array $customConfiguration
    ): NonActiveConfiguration {
        return (new NonActiveConfiguration())
            ->setId((int) $record['id'])
            ->setAutoImportEnabled($customConfiguration['auto_import'] === '1')
            ->setClientId($customConfiguration['client_id'])
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
            ->setForced($record['is_forced'] === '1')
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
}

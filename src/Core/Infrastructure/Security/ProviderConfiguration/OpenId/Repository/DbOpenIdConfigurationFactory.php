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

namespace Core\Infrastructure\Security\ProviderConfiguration\OpenId\Repository;

use Core\Contact\Domain\Model\ContactTemplate;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;

class DbOpenIdConfigurationFactory
{
    /**
     * @param array<string,mixed> $record
     * @param array<string,mixed> $customConfiguration
     * @return OpenIdConfiguration
     */
    public static function createFromRecord(array $record, array $customConfiguration): OpenIdConfiguration
    {
        $configuration = new OpenIdConfiguration(
            $customConfiguration['contact_template'] !== null
                ? self::createContactTemplate($customConfiguration['contact_template'])
                : null,
            $customConfiguration['auto_import'] === '1',
            $customConfiguration['email_bind_attribute'],
            $customConfiguration['alias_bind_attribute'],
            $customConfiguration['fullname_bind_attribute']
        );

        $configuration
            ->setId((int) $record['id'])
            ->setActive($record['is_active'] === '1')
            ->setForced($record['is_forced'] === '1')
            ->setTrustedClientAddresses($customConfiguration['trusted_client_addresses'])
            ->setBlacklistClientAddresses($customConfiguration['blacklist_client_addresses'])
            ->setBaseUrl($customConfiguration['base_url'])
            ->setAuthorizationEndpoint($customConfiguration['authorization_endpoint'])
            ->setTokenEndpoint($customConfiguration['token_endpoint'])
            ->setIntrospectionTokenEndpoint($customConfiguration['introspection_token_endpoint'])
            ->setUserInformationEndpoint($customConfiguration['userinfo_endpoint'])
            ->setEndSessionEndpoint($customConfiguration['endsession_endpoint'])
            ->setConnectionScopes($customConfiguration['connection_scopes'])
            ->setLoginClaim($customConfiguration['login_claim'])
            ->setClientId($customConfiguration['client_id'])
            ->setClientSecret($customConfiguration['client_secret'])
            ->setAuthenticationType($customConfiguration['authentication_type'])
            ->setVerifyPeer($customConfiguration['verify_peer']);

        return $configuration;
    }

    /**
     * create a Contact Template
     *
     * @param array<string,string> $contactTemplate
     * @return ContactTemplate
     */
    public static function createContactTemplate(array $contactTemplate): ContactTemplate
    {
        return new ContactTemplate((int) $contactTemplate['id'], $contactTemplate['name']);
    }
}

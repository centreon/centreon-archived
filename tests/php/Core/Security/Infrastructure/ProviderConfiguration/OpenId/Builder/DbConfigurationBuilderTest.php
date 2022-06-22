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

namespace Tests\Core\Security\Infrastructure\ProviderConfiguration\OpenId\Builder;

use Assert\InvalidArgumentException;
use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\Infrastructure\ProviderConfiguration\OpenId\Builder\DbConfigurationBuilder;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\Configuration;

beforeEach(function () {
    $this->customConfiguration = [
        'client_id' => 'clientid',
        'client_secret' => 'clientsecret',
        'base_url' => 'http://127.0.0.1/openid',
        'auto_import' => true,
        'authorization_endpoint' => '/authorize',
        'token_endpoint' => '/token',
        'introspection_token_endpoint' => '/introspect',
        'userinfo_endpoint' => '/userinfo',
        'contact_template' => new ContactTemplate(1, 'contact_template'),
        'email_bind_attribute' => 'email',
        'alias_bind_attribute' => 'alias',
        'fullname_bind_attribute' => 'name',
        'trusted_client_addresses' => [],
        'blacklist_client_addresses' => [],
        'endsession_endpoint' => '/logout',
        'connection_scopes' => [],
        'login_claim' => 'preferred_username',
        'authentication_type' => 'client_secret_post',
        'verify_peer' => false,
        'contact_group' => new ContactGroup(1, 'contact_group'),
        'claim_name' => 'groups',
        'authorization_rules' => [],
    ];
});

it('should throw an exception when a mandatory parameters is empty and configuration is active', function () {
    $this->customConfiguration['base_url'] = null;
    DbConfigurationBuilder::create(['id' => 2, 'is_active' => true, 'is_forced' => true], $this->customConfiguration);
})->expectException(InvalidArgumentException::class);

it(
    'should throw an exception when both userinformation and introspection '
    . 'endpoints are empty and a configuration is active',
    function () {
        $this->customConfiguration['userinfo_endpoint'] = null;
        $this->customConfiguration['introspection_token_endpoint'] = null;
        DbConfigurationBuilder::create(
            ['id' => 2, 'is_active' => true, 'is_forced' => true],
            $this->customConfiguration
        );
    }
)->throws(
    OpenIdConfigurationException::class,
    OpenIdConfigurationException::missingInformationEndpoint()->getMessage()
);

it(
    'should throw an exception when the configuration is active, autoimport enable but with missing parameters',
    function () {
        $this->customConfiguration['contact_template'] = null;
        $this->customConfiguration['email_bind_attribute'] =  null;
        $this->customConfiguration['alias_bind_attribute'] =  null;
        $this->customConfiguration['fullname_bind_attribute'] = null;
        DbConfigurationBuilder::create(
            ['id' => 2, 'is_active' => true, 'is_forced' => true],
            $this->customConfiguration
        );
    }
)->throws(
    OpenIdConfigurationException::class,
    OpenIdConfigurationException::missingAutoImportMandatoryParameters(
        ['contact_template', 'email_bind_attribute', 'alias_bind_attribute', 'fullname_bind_attribute']
    )->getMessage()
);

it('should return a Configuration when all mandatory parameters are present', function () {
    $configuration = DbConfigurationBuilder::create(
        ['id' => 2, 'is_active' => true, 'is_forced' => true],
        $this->customConfiguration
    );
    expect($configuration)->toBeInstanceOf(Configuration::class);
});

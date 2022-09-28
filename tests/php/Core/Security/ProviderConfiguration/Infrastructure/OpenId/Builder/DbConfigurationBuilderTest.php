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

namespace Tests\Core\Security\ProviderConfiguration\Infrastructure\OpenId\Builder;

use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\ACLConditions;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\AuthenticationConditions;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\CustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\Endpoint;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\GroupsMapping;

beforeEach(function () {
    $this->customConfiguration = [
        'is_active' => true,
        'client_id' => 'clientid',
        'client_secret' => 'clientsecret',
        'base_url' => 'http://127.0.0.1/openid',
        'auto_import' => true,
        'authorization_endpoint' => '/authorize',
        'token_endpoint' => '/token',
        'introspection_token_endpoint' => '/introspect',
        'userinfo_endpoint' => '/userinfo',
        'contact_template_id' => 1,
        'email_bind_attribute' => 'email',
        'fullname_bind_attribute' => 'name',
        'trusted_client_addresses' => [],
        'blacklist_client_addresses' => [],
        'endsession_endpoint' => '/logout',
        'connection_scopes' => [],
        'login_claim' => 'preferred_username',
        'authentication_type' => 'client_secret_post',
        'verify_peer' => false,
        'claim_name' => 'groups',
        'roles_mapping' => new ACLConditions(
            false,
            false,
            '',
            new Endpoint(Endpoint::INTROSPECTION, ''),
            []
        ),
        "groups_mapping" => new GroupsMapping(false, "", new Endpoint(), [])
    ];
});

it('should throw an exception when a mandatory parameter is empty and configuration is active', function () {
    $this->customConfiguration['base_url'] = null;
    $configuration = new Configuration(
        2,
        strtolower(Provider::OPENID),
        Provider::OPENID,
        json_encode($this->customConfiguration),
        true,
        false
    );
    $configuration->setCustomConfiguration(new CustomConfiguration($this->customConfiguration));
})->throws(OpenIdConfigurationException::class, "Missing mandatory parameters: base_url");

it(
    'should throw an exception when both userinformation and introspection '
    . 'endpoints are empty and a configuration is active',
    function () {
        $this->customConfiguration['userinfo_endpoint'] = null;
        $this->customConfiguration['introspection_token_endpoint'] = null;
        $configuration = new Configuration(
            2,
            strtolower(Provider::OPENID),
            Provider::OPENID,
            json_encode($this->customConfiguration),
            true,
            false
        );
        $configuration->setCustomConfiguration(new CustomConfiguration($this->customConfiguration));
    }
)->throws(
    OpenIdConfigurationException::class,
    OpenIdConfigurationException::missingInformationEndpoint()->getMessage()
);

it(
    'should throw an exception when the configuration is active, autoimport enabled but with missing parameters',
    function () {
        $this->customConfiguration['contact_template'] = null;
        $this->customConfiguration['email_bind_attribute'] = null;
        $this->customConfiguration['fullname_bind_attribute'] = null;
        $configuration = new Configuration(
            2,
            strtolower(Provider::OPENID),
            Provider::OPENID,
            json_encode($this->customConfiguration),
            true,
            false
        );
        $configuration->setCustomConfiguration(new CustomConfiguration($this->customConfiguration));
    }
)->throws(
    OpenIdConfigurationException::class,
    OpenIdConfigurationException::missingAutoImportMandatoryParameters(
        ['contact_template', 'email_bind_attribute', 'fullname_bind_attribute']
    )->getMessage()
);

it('should return a Provider when all mandatory parameters are present', function () {

    // Note: contact_template and contact_group are overridden
    $this->customConfiguration['contact_template'] = new ContactTemplate(1, 'contact_template');
    $this->customConfiguration['contact_group'] = new ContactGroup(1, 'contact_group');
    $this->customConfiguration['authentication_conditions'] = new AuthenticationConditions(
        true,
        "info.groups",
        new Endpoint(),
        ["groupA", "groupB"]
    );

    $configuration = new Configuration(
        2,
        strtolower(Provider::OPENID),
        Provider::OPENID,
        json_encode($this->customConfiguration),
        true,
        false
    );
    $configuration->setCustomConfiguration(new CustomConfiguration($this->customConfiguration));
    expect($configuration->getCustomConfiguration())->toBeInstanceOf(CustomConfiguration::class);
});

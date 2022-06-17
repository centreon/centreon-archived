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

namespace Tests\Core\Security\Application\ProviderConfiguration\OpenId\Builder;

use Assert\InvalidArgumentException;
use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\Application\ProviderConfiguration\OpenId\Builder\ConfigurationBuilder;
use Core\Security\Application\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration\{
    UpdateOpenIdConfigurationRequest
};
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\Configuration;

it('should throw an exception when a mandatory parameters is empty and configuration is active', function () {
    $request = new UpdateOpenIdConfigurationRequest();
    $request->clientId = null;
    $request->clientSecret = null;
    $request->baseUrl = null;
    $request->authorizationEndpoint = null;
    $request->tokenEndpoint = null;
    $request->isActive = true;
    $contactTemplate = new ContactTemplate(1, 'contact_template');
    $contactGroup = new ContactGroup(1, 'contact_group');
    ConfigurationBuilder::create($request, $contactTemplate, $contactGroup, []);
})->expectException(InvalidArgumentException::class);

it(
    'should throw an exception when both userinformation and introspection '
    . 'endpoints are empty and a configuration is active',
    function () {
        $request = new UpdateOpenIdConfigurationRequest();
        $request->clientId = "clientId";
        $request->clientSecret = "clientSecret";
        $request->baseUrl = "http://127.0.0.1/openid";
        $request->authorizationEndpoint = "/authorize";
        $request->tokenEndpoint = "/token";
        $request->isActive = true;
        $request->introspectionTokenEndpoint = null;
        $request->userInformationEndpoint = null;
        $contactTemplate = new ContactTemplate(1, 'contact_template');
        $contactGroup = new ContactGroup(1, 'contact_group');
        ConfigurationBuilder::create($request, $contactTemplate, $contactGroup, []);
    }
)->throws(
    OpenIdConfigurationException::class,
    OpenIdConfigurationException::missingInformationEndpoint()->getMessage()
);

it(
    'should throw an exception when the configuration is active, autoimport enable but with missing parameters',
    function () {
        $request = new UpdateOpenIdConfigurationRequest();
        $request->clientId = "clientId";
        $request->clientSecret = "clientSecret";
        $request->baseUrl = "http://127.0.0.1/openid";
        $request->authorizationEndpoint = "/authorize";
        $request->tokenEndpoint = "/token";
        $request->isActive = true;
        $request->introspectionTokenEndpoint = '/introspect';
        $request->isAutoImportEnabled = true;
        $contactTemplate = null;
        $contactGroup = new ContactGroup(1, 'contact_group');
        ConfigurationBuilder::create($request, $contactTemplate, $contactGroup, []);
    }
)->throws(
    OpenIdConfigurationException::class,
    OpenIdConfigurationException::missingAutoImportMandatoryParameters(
        ['contact_template', 'email_bind_attribute', 'alias_bind_attribute', 'fullname_bind_attribute']
    )->getMessage()
);

it('should return a Configuration when all mandatory parameters are present', function () {
    $request = new UpdateOpenIdConfigurationRequest();
    $request->clientId = "clientId";
    $request->clientSecret = "clientSecret";
    $request->baseUrl = "http://127.0.0.1/openid";
    $request->authorizationEndpoint = "/authorize";
    $request->tokenEndpoint = "/token";
    $request->isActive = true;
    $request->introspectionTokenEndpoint = '/introspect';
    $request->isAutoImportEnabled = true;
    $request->userAliasBindAttribute = 'alias';
    $request->userNameBindAttribute = 'name';
    $request->emailBindAttribute = 'email';
    $contactTemplate = new ContactTemplate(1, 'contact_template');
    $contactGroup = new ContactGroup(1, 'contact_group');
    $configuration = ConfigurationBuilder::create($request, $contactTemplate, $contactGroup, []);
    expect($configuration)->toBeInstanceOf(Configuration::class);
});

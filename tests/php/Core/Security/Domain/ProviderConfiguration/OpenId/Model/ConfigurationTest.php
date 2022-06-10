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

namespace Tests\Core\Security\Domain\ProviderConfiguration\OpenId\Model;

use Assert\InvalidArgumentException;
use Core\Contact\Domain\Model\ContactGroup;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\Configuration;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;

it('should throw an Exception when a configuration client id is empty and configuration is active', function () {
    new Configuration(true, true, '');
})->throws(InvalidArgumentException::class, AssertionException::notEmpty('Configuration::clientId')->getMessage());

it('should throw an Exception when a configuration client secret is empty and configuration is active', function () {
    new Configuration(true, true, 'MyCl1ientId', '');
})->throws(InvalidArgumentException::class, AssertionException::notEmpty('Configuration::clientSecret')->getMessage());

it('should throw an Exception when a configuration base url is empty and configuration is active', function () {
    new Configuration(true, true, 'MyCl1ientId', 'MyCl1ientSuperSecr3tKey', '');
})->throws(InvalidArgumentException::class, AssertionException::notEmpty('Configuration::baseUrl')->getMessage());

it(
    'should throw an Exception when a configuration authorization endpoint is empty and configuration is active',
    function () {
        new Configuration(
            true,
            true,
            'MyCl1ientId',
            'MyCl1ientSuperSecr3tKey',
            'http://127.0.0.1/auth/openid-connect',
            ''
        );
    }
)->throws(
    InvalidArgumentException::class,
    AssertionException::notEmpty('Configuration::authorizationEndpoint')->getMessage()
);

it('should throw an Exception when a configuration token endpoint is empty and configuration is active', function () {
    new Configuration(
        true,
        true,
        'MyCl1ientId',
        'MyCl1ientSuperSecr3tKey',
        'http://127.0.0.1/auth/openid-connect',
        '/authorization'
    );
})->throws(InvalidArgumentException::class, AssertionException::notEmpty('Configuration::tokenEndpoint')->getMessage());

it(
    'should throw an Exception when both introspection and userinfo endpoints are empty and configuration is active',
    function () {
        new Configuration(
            true,
            false,
            'MyCl1ientId',
            'MyCl1ientSuperSecr3tKey',
            'http://127.0.0.1/auth/openid-connect',
            '/authorization',
            '/token',
            null,
            null,
            null,
            null,
            null,
            null,
            new ContactGroup(1, 'contact_group'),
            'groups'
        );
    }
)->throws(
    OpenIdConfigurationException::class,
    OpenIdConfigurationException::missingInformationEndpoint()->getMessage()
);

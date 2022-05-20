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

namespace Tests\Core\Application\Security\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration;

use Core\Application\Common\UseCase\NoContentResponse;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Security\ProviderConfiguration\OpenId\Repository\WriteOpenIdConfigurationRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration\{
    UpdateOpenIdConfiguration,
    UpdateOpenIdConfigurationPresenterInterface,
    UpdateOpenIdConfigurationRequest
};
use Core\Contact\Application\Repository\ReadContactTemplateRepositoryInterface;
use Core\Domain\Security\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfigurationFactory;

beforeEach(function () {
    $this->repository = $this->createMock(WriteOpenIdConfigurationRepositoryInterface::class);
    $this->presenter = $this->createMock(UpdateOpenIdConfigurationPresenterInterface::class);
    $this->contactTemplateRepository = $this->createMock(ReadContactTemplateRepositoryInterface::class);
});

it('should present a NoContentResponse when the use case is executed correctly', function () {
    $request = new UpdateOpenIdConfigurationRequest();
    $request->isActive = true;
    $request->isForced = true;
    $request->trustedClientAddresses = [];
    $request->blacklistClientAddresses = [];
    $request->baseUrl = 'http://127.0.0.1/auth/openid-connect';
    $request->authorizationEndpoint = '/authorization';
    $request->tokenEndpoint = '/token';
    $request->introspectionTokenEndpoint = '/introspect';
    $request->userInformationEndpoint = '/userinfo';
    $request->endSessionEndpoint = '/logout';
    $request->connectionScopes = [];
    $request->loginClaim = 'preferred_username';
    $request->clientId = 'MyCl1ientId';
    $request->clientSecret = 'MyCl1ientSuperSecr3tKey';
    $request->authenticationType = 'client_secret_post';
    $request->verifyPeer = false;
    $request->isAutoImportEnabled = false;

    $openIdConfiguration = OpenIdConfigurationFactory::createFromRequest($request);

    $this->repository
        ->expects($this->once())
        ->method('updateConfiguration')
        ->with($openIdConfiguration);

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new NoContentResponse());

    $useCase = new UpdateOpenIdConfiguration($this->repository, $this->contactTemplateRepository);
    $useCase($this->presenter, $request);
});

it('should present an ErrorResponse when an error occured during the use case execution', function () {
    $request = new UpdateOpenIdConfigurationRequest();
    $request->isActive = true;
    $request->isForced = true;
    $request->trustedClientAddresses = ["abcd_.@"];
    $request->blacklistClientAddresses = [];
    $request->baseUrl = 'http://127.0.0.1/auth/openid-connect';
    $request->authorizationEndpoint = '/authorization';
    $request->tokenEndpoint = '/token';
    $request->introspectionTokenEndpoint = '/introspect';
    $request->userInformationEndpoint = '/userinfo';
    $request->endSessionEndpoint = '/logout';
    $request->connectionScopes = [];
    $request->loginClaim = 'preferred_username';
    $request->clientId = 'MyCl1ientId';
    $request->clientSecret = 'MyCl1ientSuperSecr3tKey';
    $request->authenticationType = 'client_secret_post';
    $request->verifyPeer = false;
    $request->isAutoImportEnabled = false;

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new ErrorResponse(
            '[OpenIdConfiguration::trustedClientAddresses] The value "abcd_.@" '
            . 'was expected to be a valid ip address or domain name'
        ));

    $useCase = new UpdateOpenIdConfiguration($this->repository, $this->contactTemplateRepository);

    $useCase($this->presenter, $request);
});

it('should present an Error Response when auto import is enable and mandatory parameters are missing', function () {
    $request = new UpdateOpenIdConfigurationRequest();
    $request->isActive = true;
    $request->isForced = true;
    $request->trustedClientAddresses = [];
    $request->blacklistClientAddresses = [];
    $request->baseUrl = 'http://127.0.0.1/auth/openid-connect';
    $request->authorizationEndpoint = '/authorization';
    $request->tokenEndpoint = '/token';
    $request->introspectionTokenEndpoint = '/introspect';
    $request->userInformationEndpoint = '/userinfo';
    $request->endSessionEndpoint = '/logout';
    $request->connectionScopes = [];
    $request->loginClaim = 'preferred_username';
    $request->clientId = 'MyCl1ientId';
    $request->clientSecret = 'MyCl1ientSuperSecr3tKey';
    $request->authenticationType = 'client_secret_post';
    $request->verifyPeer = false;
    $request->isAutoImportEnabled = true;

    $missingParameters = [
        'contact_template',
        'email_bind_attribute',
        'alias_bind_attribute',
        'fullname_bind_attribute',
    ];

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new ErrorResponse(
            OpenIdConfigurationException::missingAutoImportMandatoryParameters($missingParameters)->getMessage()
        ));

    $useCase = new UpdateOpenIdConfiguration($this->repository, $this->contactTemplateRepository);

    $useCase($this->presenter, $request);
});

it('should present an Error Response when auto import is enable and the contact template doesn\'t exist', function () {
    $request = new UpdateOpenIdConfigurationRequest();
    $request->isActive = true;
    $request->isForced = true;
    $request->trustedClientAddresses = [];
    $request->blacklistClientAddresses = [];
    $request->baseUrl = 'http://127.0.0.1/auth/openid-connect';
    $request->authorizationEndpoint = '/authorization';
    $request->tokenEndpoint = '/token';
    $request->introspectionTokenEndpoint = '/introspect';
    $request->userInformationEndpoint = '/userinfo';
    $request->endSessionEndpoint = '/logout';
    $request->connectionScopes = [];
    $request->loginClaim = 'preferred_username';
    $request->clientId = 'MyCl1ientId';
    $request->clientSecret = 'MyCl1ientSuperSecr3tKey';
    $request->authenticationType = 'client_secret_post';
    $request->verifyPeer = false;
    $request->isAutoImportEnabled = true;
    $request->contactTemplate = ['id' => 1, "name" => 'contact_template'];
    $request->emailBindAttribute = 'email';
    $request->userAliasBindAttribute = 'alias';
    $request->userNameBindAttribute = 'name';

    $this->contactTemplateRepository
        ->expects($this->once())
        ->method('find')
        ->with($request->contactTemplate['id'])
        ->willReturn(null);

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new ErrorResponse(
            OpenIdConfigurationException::contactTemplateNotFound($request->contactTemplate['name'])->getMessage()
        ));

    $useCase = new UpdateOpenIdConfiguration($this->repository, $this->contactTemplateRepository);

    $useCase($this->presenter, $request);
});

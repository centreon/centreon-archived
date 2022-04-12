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
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfigurationFactory;

beforeEach(function () {
    $this->repository = $this->createMock(WriteOpenIdConfigurationRepositoryInterface::class);
    $this->presenter = $this->createMock(UpdateOpenIdConfigurationPresenterInterface::class);
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

        $openIdConfiguration = OpenIdConfigurationFactory::createFromRequest($request);

        $this->repository
            ->expects($this->once())
            ->method('updateConfiguration')
            ->with($openIdConfiguration);

        $this->presenter
            ->expects($this->once())
            ->method('setResponseStatus')
            ->with(new NoContentResponse());

        $useCase = new UpdateOpenIdConfiguration($this->repository);
        $useCase($this->presenter, $request);
})->skip('Reimplement those tests while handling update openid configuration extension');

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

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new ErrorResponse(
            '[OpenIdConfiguration::trustedClientAddresses] The value "abcd_.@" '
            . 'was expected to be a valid ip address or domain name'
        ));

    $useCase = new UpdateOpenIdConfiguration($this->repository);

    $useCase($this->presenter, $request);
})->skip('should present a NoContentResponse when the use case is executed correctly');

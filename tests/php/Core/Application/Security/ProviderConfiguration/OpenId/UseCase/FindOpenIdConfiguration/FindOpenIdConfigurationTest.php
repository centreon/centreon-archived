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

namespace Tests\Core\Application\Security\ProviderConfiguration\OpenId\UseCase\FindOpenIdConfiguration;

use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\Security\ProviderConfiguration\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\OpenId\UseCase\FindOpenIdConfiguration\{
    FindOpenIdConfiguration,
    FindOpenIdConfigurationResponse
};
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;

beforeEach(function () {
    $this->repository = $this->createMock(ReadOpenIdConfigurationRepositoryInterface::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
});

it('should present a provider configuration', function () {
        $configuration = new OpenIdConfiguration(
            false,
            new ContactTemplate(1, 'contact_template'),
            null,
            null,
            null,
        );

        $configuration
            ->setActive(true)
            ->setForced(true)
            ->setTrustedClientAddresses([])
            ->setBlacklistClientAddresses([])
            ->setBaseUrl('http://127.0.0.1/auth/openid-connect')
            ->setAuthorizationEndpoint('/authorization')
            ->setTokenEndpoint('/token')
            ->setIntrospectionTokenEndpoint('/introspect')
            ->setUserInformationEndpoint('/userinfo')
            ->setEndSessionEndpoint('/logout')
            ->setConnectionScopes([])
            ->setLoginClaim('preferred_username')
            ->setClientId('MyCl1ientId')
            ->setClientSecret('MyCl1ientSuperSecr3tKey')
            ->setAuthenticationType('client_secret_post')
            ->setVerifyPeer(false);

        $useCase = new FindOpenIdConfiguration($this->repository);
        $presenter = new FindOpenIdConfigurationPresenterStub($this->presenterFormatter);

        $this->repository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willReturn($configuration);

        $useCase($presenter);

        expect($presenter->response)->toBeInstanceOf(FindOpenIdConfigurationResponse::class);
        expect($presenter->response->isActive)->toBeTrue();
        expect($presenter->response->isForced)->toBeTrue();
        expect($presenter->response->verifyPeer)->toBeFalse();
        expect($presenter->response->trustedClientAddresses)->toBeEmpty();
        expect($presenter->response->trustedClientAddresses)->toBeArray();
        expect($presenter->response->blacklistClientAddresses)->toBeEmpty();
        expect($presenter->response->blacklistClientAddresses)->toBeArray();
        expect($presenter->response->baseUrl)->toBe('http://127.0.0.1/auth/openid-connect');
        expect($presenter->response->authorizationEndpoint)->toBe('/authorization');
        expect($presenter->response->tokenEndpoint)->toBe('/token');
        expect($presenter->response->introspectionTokenEndpoint)->toBe('/introspect');
        expect($presenter->response->userInformationEndpoint)->toBe('/userinfo');
        expect($presenter->response->endSessionEndpoint)->toBe('/logout');
        expect($presenter->response->connectionScopes)->toBeEmpty();
        expect($presenter->response->connectionScopes)->toBeArray();
        expect($presenter->response->loginClaim)->toBe('preferred_username');
        expect($presenter->response->clientId)->toBe('MyCl1ientId');
        expect($presenter->response->clientSecret)->toBe('MyCl1ientSuperSecr3tKey');
        expect($presenter->response->authenticationType)->toBe('client_secret_post');
        expect($presenter->response->contactTemplate)->toBe(['id' => 1, 'name' => 'contact_template']);
        expect($presenter->response->isAutoImportEnabled)->toBeFalse();
        expect($presenter->response->emailBindAttribute)->toBeNull();
        expect($presenter->response->userAliasBindAttribute)->toBeNull();
        expect($presenter->response->userNameBindAttribute)->toBeNull();
});

it('should present a NotFoundReponse when no configuration is found', function () {
    $useCase = new FindOpenIdConfiguration($this->repository);
    $presenter = new FindOpenIdConfigurationPresenterStub($this->presenterFormatter);

    $this->repository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willReturn(null);

    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe('OpenIdConfiguration not found');
});

it('should present an ErrorResponse when an error occured during the process', function () {
    $useCase = new FindOpenIdConfiguration($this->repository);
    $presenter = new FindOpenIdConfigurationPresenterStub($this->presenterFormatter);

    $this->repository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willThrowException(new \Exception('An error occured'));

    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe('An error occured');
});

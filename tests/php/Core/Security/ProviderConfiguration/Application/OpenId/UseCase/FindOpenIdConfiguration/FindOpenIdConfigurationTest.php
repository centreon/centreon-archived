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

namespace Tests\Core\Security\ProviderConfiguration\Application\OpenId\UseCase\FindOpenIdConfiguration;

use Core\Application\Common\UseCase\ErrorResponse;
use Core\Contact\Domain\Model\ContactGroup;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\ProviderConfiguration\Application\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\Repository\ReadConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\ACLConditions;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\AuthenticationConditions;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\CustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\Endpoint;
use Core\Security\ProviderConfiguration\Application\OpenId\UseCase\FindOpenIdConfiguration\{
    FindOpenIdConfiguration,
    FindOpenIdConfigurationResponse
};
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\Configuration;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\GroupsMapping;
use Security\Domain\Authentication\Exceptions\ProviderException;

beforeEach(function () {
    $this->repository = $this->createMock(ReadOpenIdConfigurationRepositoryInterface::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    $this->readConfiguration = $this->createMock(ReadConfigurationRepositoryInterface::class);
    $this->providerFactory = $this->createMock(ProviderAuthenticationFactoryInterface::class);
    $this->provider = $this->createMock(ProviderAuthenticationInterface::class);
});

it('should present a provider configuration', function () {
    $configuration = new Configuration(1, 'openid', 'openid', '{}', true, true);
    $customConfiguration = new CustomConfiguration([
        'is_active' => true,
        'client_id' => 'MyCl1ientId',
        'client_secret' => 'MyCl1ientSuperSecr3tKey',
        'base_url' => 'http://127.0.0.1/auth/openid-connect',
        'auto_import' => false,
        'authorization_endpoint' => '/authorization',
        'token_endpoint' => '/token',
        'introspection_token_endpoint' => '/introspect',
        'userinfo_endpoint' => '/userinfo',
        'contact_template' => new ContactTemplate(1, 'contact_template'),
        'email_bind_attribute' => null,
        'fullname_bind_attribute' => null,
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
        'authentication_conditions' => new AuthenticationConditions(false, '', new Endpoint(), []),
        "groups_mapping" => new GroupsMapping(false, "", new Endpoint(), [])
    ]);
    $configuration->setCustomConfiguration($customConfiguration);

    $this->provider
        ->method('getConfiguration')
        ->willReturn($configuration);

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->with(Provider::OPENID)
        ->willReturn($this->provider);

    $useCase = new FindOpenIdConfiguration($this->providerFactory);
    $presenter = new FindOpenIdConfigurationPresenterStub($this->presenterFormatter);

    $useCase($presenter);

    expect($presenter->response)->toBeInstanceOf(FindOpenIdConfigurationResponse::class);
    expect($presenter->response->isActive)->toBeTrue();
    expect($presenter->response->isForced)->toBeTrue();
    expect($presenter->response->verifyPeer)->toBeFalse();
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
    expect($presenter->response->userNameBindAttribute)->toBeNull();
    expect($presenter->response->authenticationConditions)->toBeArray();
    expect($presenter->response->groupsMapping)->toBeArray();
});

it('should present an ErrorResponse when an error occured during the process', function () {
    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->with(Provider::OPENID)
        ->willThrowException(ProviderException::providerConfigurationNotFound(Provider::OPENID));

    $useCase = new FindOpenIdConfiguration($this->providerFactory);
    $presenter = new FindOpenIdConfigurationPresenterStub($this->presenterFormatter);

    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe('Provider configuration (openid) not found');
});

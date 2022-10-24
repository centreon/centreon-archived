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

namespace Tests\Core\Security\ProviderConfiguration\Application\OpenId\UseCase\UpdateOpenIdConfiguration;

use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Contact\Application\Repository\ReadContactGroupRepositoryInterface;
use Core\Contact\Application\Repository\ReadContactTemplateRepositoryInterface;
use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\ProviderConfiguration\Application\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\OpenId\Repository\WriteOpenIdConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\OpenId\UseCase\UpdateOpenIdConfiguration\{UpdateOpenIdConfiguration,
    UpdateOpenIdConfigurationPresenterInterface,
    UpdateOpenIdConfigurationRequest
};
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\OpenIdConfigurationException;

beforeEach(function () {
    $this->repository = $this->createMock(WriteOpenIdConfigurationRepositoryInterface::class);
    $this->contactGroupRepository = $this->createMock(ReadContactGroupRepositoryInterface::class);
    $this->accessGroupRepository = $this->createMock(ReadAccessGroupRepositoryInterface::class);
    $this->presenter = $this->createMock(UpdateOpenIdConfigurationPresenterInterface::class);
    $this->readOpenIdRepository = $this->createMock(ReadOpenIdConfigurationRepositoryInterface::class);
    $this->contactTemplateRepository = $this->createMock(ReadContactTemplateRepositoryInterface::class);
    $this->dataStorageEngine = $this->createMock(DataStorageEngineInterface::class);
    $this->providerFactory = $this->createMock(ProviderAuthenticationFactoryInterface::class);
    $this->contactGroup = new ContactGroup(1, 'contact_group');
    $this->contactTemplate = new ContactTemplate(1, 'contact_template');
});

it('should present a NoContentResponse when the use case is executed correctly', function () {
    $request = new UpdateOpenIdConfigurationRequest();
    $request->isActive = true;
    $request->isForced = true;
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
    $request->contactTemplate = ['id' => 1]; /** @phpstan-ignore-line */
    $request->rolesMapping = [
        'is_enabled' => false,
        'apply_only_first_role' => false,
        'attribute_path' => '',
        'endpoint' => [
            'type' => 'introspection_endpoint',
            'custom_endpoint' => ''
        ],
        'relations' => []
    ];

    $this->contactTemplateRepository
        ->expects($this->once())
        ->method('find')
        ->with(1)
        ->willReturn($this->contactTemplate);

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new NoContentResponse());

    $useCase = new UpdateOpenIdConfiguration(
        $this->repository,
        $this->contactTemplateRepository,
        $this->contactGroupRepository,
        $this->accessGroupRepository,
        $this->dataStorageEngine,
        $this->providerFactory
    );
    $useCase($this->presenter, $request);
});

it('should present an ErrorResponse when an error occured during the use case execution', function () {
    $request = new UpdateOpenIdConfigurationRequest();
    $request->isActive = true;
    $request->isForced = true;
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
    $request->contactTemplate = ['id' => 1]; /** @phpstan-ignore-line */
    $request->rolesMapping = [
        'is_enabled' => false,
        'apply_only_first_role' => false,
        'attribute_path' => '',
        'endpoint' => [
            'type' => 'introspection_endpoint',
            'custom_endpoint' => ''
        ],
        'relations' => []
    ];
    $request->authenticationConditions = [
        "is_enabled" => true,
        "attribute_path" => "info.groups",
        "endpoint" => ["type" => "introspection_endpoint", "custom_endpoint" => null],
        "authorized_values" => ["groupsA"],
        "trusted_client_addresses" => ['abcd_.@'],
        "blacklist_client_addresses" => []
    ];

    $this->contactTemplateRepository
        ->expects($this->once())
        ->method('find')
        ->with(1)
        ->willReturn($this->contactTemplate);

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new ErrorResponse(
            AssertionException::ipOrDomain('abcd_.@', 'AuthenticationConditions::trustedClientAddresses')->getMessage()
        ));

    $useCase = new UpdateOpenIdConfiguration(
        $this->repository,
        $this->contactTemplateRepository,
        $this->contactGroupRepository,
        $this->accessGroupRepository,
        $this->dataStorageEngine,
        $this->providerFactory
    );

    $useCase($this->presenter, $request);
});

it('should present an Error Response when auto import is enable and mandatory parameters are missing', function () {
    $request = new UpdateOpenIdConfigurationRequest();
    $request->isActive = true;
    $request->isForced = true;
    $request->baseUrl = 'http://127.0.0.1/auth/openid-connect2';
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
    $request->rolesMapping = [
        'is_enabled' => false,
        'apply_only_first_role' => false,
        'attribute_path' => '',
        'endpoint' => [
            'type' => 'introspection_endpoint',
            'custom_endpoint' => ''
        ],
        'relations' => []
    ];

    $missingParameters = [
        'contact_template',
        'email_bind_attribute',
        'fullname_bind_attribute',
    ];

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new ErrorResponse(
            OpenIdConfigurationException::missingAutoImportMandatoryParameters($missingParameters)->getMessage()
        ));

    $useCase = new UpdateOpenIdConfiguration(
        $this->repository,
        $this->contactTemplateRepository,
        $this->contactGroupRepository,
        $this->accessGroupRepository,
        $this->dataStorageEngine,
        $this->providerFactory
    );

    $useCase($this->presenter, $request);
});

it('should present an Error Response when auto import is enable and the contact template doesn\'t exist', function () {
    $request = new UpdateOpenIdConfigurationRequest();
    $request->isActive = true;
    $request->isForced = true;
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

    $useCase = new UpdateOpenIdConfiguration(
        $this->repository,
        $this->contactTemplateRepository,
        $this->contactGroupRepository,
        $this->accessGroupRepository,
        $this->dataStorageEngine,
        $this->providerFactory
    );

    $useCase($this->presenter, $request);
});

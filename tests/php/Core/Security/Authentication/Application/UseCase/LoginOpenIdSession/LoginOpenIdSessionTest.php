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

namespace Tests\Core\Security\Authentication\Application\UseCase\LoginOpenIdSession;

use CentreonDB;
use Pimple\Container;
use Centreon\Domain\Contact\Contact;
use Core\Contact\Domain\Model\ContactGroup;
use Symfony\Component\HttpFoundation\Request;
use Core\Contact\Domain\Model\ContactTemplate;
use Symfony\Component\HttpFoundation\RequestStack;
use Core\Infrastructure\Common\Presenter\JsonPresenter;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Menu\Interfaces\MenuServiceInterface;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Core\Security\Authentication\Application\UseCase\Login\Login;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\Endpoint;
use Security\Domain\Authentication\Interfaces\OpenIdProviderInterface;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\Configuration;
use Core\Contact\Application\Repository\WriteContactGroupRepositoryInterface;
use Core\Security\Authentication\Infrastructure\Provider\AclUpdaterInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\AuthorizationRule;
use Core\Security\Authentication\Infrastructure\Api\Login\OpenId\LoginPresenter;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\CustomConfiguration;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Core\Security\Authentication\Infrastructure\Repository\WriteSessionRepository;
use Core\Security\Authentication\Application\Repository\ReadTokenRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\AuthenticationConditions;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\Repository\WriteTokenRepositoryInterface;
use Core\Security\AccessGroup\Application\Repository\WriteAccessGroupRepositoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionTokenRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;

beforeEach(function () {
    $this->repository = $this->createMock(ReadOpenIdConfigurationRepositoryInterface::class);
    $this->provider = $this->createMock(ProviderAuthenticationInterface::class);
    $this->legacyProvider = $this->createMock(OpenIdProviderInterface::class);
    $this->legacyProviderService = $this->createMock(ProviderServiceInterface::class);
    $this->session = $this->createMock(SessionInterface::class);
    $this->session
        ->expects($this->any())
        ->method('getId')
        ->willReturn('session_abcd');
    $this->request = $this->createMock(Request::class);
    $this->request
        ->expects($this->any())
        ->method('getSession')
        ->willReturn($this->session);
    $this->requestStack = $this->createMock(RequestStack::class);
    $this->requestStack
        ->expects($this->any())
        ->method('getCurrentRequest')
        ->willReturn($this->request);
    $this->centreonDB = $this->createMock(CentreonDB::class);
    $this->dependencyInjector = new Container(['configuration_db' => $this->centreonDB]);
    $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
    $this->authenticationRepository = $this->createMock(AuthenticationRepositoryInterface::class);
    $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
    $this->dataStorageEngine = $this->createMock(DataStorageEngineInterface::class);
    $this->formatter = $this->createMock(JsonPresenter::class);
    $this->presenter = new LoginPresenter($this->formatter);
    $this->contact = $this->createMock(ContactInterface::class);
    $this->authenticationTokens = $this->createMock(AuthenticationTokens::class);
    $this->contactGroupRepository = $this->createMock(WriteContactGroupRepositoryInterface::class);
    $this->accessGroupRepository = $this->createMock(WriteAccessGroupRepositoryInterface::class);
    $this->providerFactory = $this->createMock(ProviderAuthenticationFactoryInterface::class);
    $this->readTokenRepository = $this->createMock(ReadTokenRepositoryInterface::class);
    $this->writeTokenRepository = $this->createMock(WriteTokenRepositoryInterface::class);
    $this->writeSessionRepository = $this->createMock(WriteSessionRepository::class);
    $this->writeSessionTokenRepository = $this->createMock(WriteSessionTokenRepositoryInterface::class);
    $this->aclUpdater = $this->createMock(AclUpdaterInterface::class);
    $this->menuService = $this->createMock(MenuServiceInterface::class);
    $this->defaultRedirectUri = '/monitoring/resources';

    $configuration = new Configuration(
        1,
        'openid',
        'openid',
        '{}',
        true,
        false
    );
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
        'contact_template' => new ContactTemplate(19, 'contact_template'),
        'email_bind_attribute' => '',
        'fullname_bind_attribute' => '',
        'trusted_client_addresses' => [],
        'blacklist_client_addresses' => [],
        'endsession_endpoint' => '',
        'connection_scopes' => [],
        'login_claim' => 'preferred_username',
        'authentication_type' => 'client_secret_post',
        'verify_peer' => false,
        'contact_group' => new ContactGroup(3, 'contact_group'),
        'claim_name' => 'groups',
        'authorization_rules' => [],
        'authentication_conditions' => new AuthenticationConditions(false, '', new Endpoint(), [])
    ]);
    $configuration->setCustomConfiguration($customConfiguration);
    $this->validOpenIdConfiguration = $configuration;
});

it('expects to return an error message in presenter when no provider configuration is found', function () {
    $request = LoginRequest::createForOpenId('127.0.0.1', 'abcde-fghij-klmno');
    $request->providerName = 'unknown provider';

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->with('unknown provider')
        ->will($this->throwException(ProviderException::providerConfigurationNotFound('unknown provider')));

    $useCase = new Login(
        $this->providerFactory,
        $this->session,
        $this->dataStorageEngine,
        $this->writeSessionRepository,
        $this->readTokenRepository,
        $this->writeTokenRepository,
        $this->writeSessionTokenRepository,
        $this->aclUpdater,
        $this->menuService,
        $this->defaultRedirectUri
    );

    $useCase($request, $this->presenter);
    expect($this->presenter->getPresentedData())->toBeObject();
})->throws(ProviderException::class, 'Provider configuration (unknown provider) not found');

it('expects to execute authenticateOrFail method from OpenIdProvider', function () {
    $request = LoginRequest::createForOpenId('127.0.0.1', 'abcde-fghij-klmno');

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->willReturn($this->provider);

    $this->provider
        ->expects($this->once())
        ->method('authenticateOrFail');

    $useCase = new Login(
        $this->providerFactory,
        $this->session,
        $this->dataStorageEngine,
        $this->writeSessionRepository,
        $this->readTokenRepository,
        $this->writeTokenRepository,
        $this->writeSessionTokenRepository,
        $this->aclUpdater,
        $this->menuService,
        $this->defaultRedirectUri
    );
    $useCase($request, $this->presenter);
});

it(
    'expects to return an error message in presenter when the provider can\'t find the user and can\'t create it',
    function () {
        $request = LoginRequest::createForOpenId('127.0.0.1', 'abcde-fghij-klmno');

        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail');

        $this->provider
            ->expects($this->never())
            ->method('isAutoImportEnabled');

        $this->provider
            ->expects($this->never())
            ->method('importUser');

        $this->provider
            ->expects($this->once())
            ->method('findUserOrFail')
            ->will($this->throwException(new NotFoundException('User could not be created')));

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->provider);

        $useCase = new Login(
            $this->providerFactory,
            $this->session,
            $this->dataStorageEngine,
            $this->writeSessionRepository,
            $this->readTokenRepository,
            $this->writeTokenRepository,
            $this->writeSessionTokenRepository,
            $this->aclUpdater,
            $this->menuService,
            $this->defaultRedirectUri
        );
        $useCase($request, $this->presenter);
    }
)->throws(NotFoundException::class, 'User could not be created');

it(
    'expects to return an error message in presenter when the provider ' .
    'wasn\'t be able to return a user after creating it',
    function () {
        $request = LoginRequest::createForOpenId('127.0.0.1', 'abcde-fghij-klmno');

        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail');

        $this->provider
            ->expects($this->once())
            ->method('findUserOrFail');

        $this->provider
            ->expects($this->once())
            ->method('isAutoImportEnabled')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('importUser')
            ->will($this->throwException(new NotFoundException('User not found')));

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->provider);

        $useCase = new Login(
            $this->providerFactory,
            $this->session,
            $this->dataStorageEngine,
            $this->writeSessionRepository,
            $this->readTokenRepository,
            $this->writeTokenRepository,
            $this->writeSessionTokenRepository,
            $this->aclUpdater,
            $this->menuService,
            $this->defaultRedirectUri
        );

        $useCase($request, $this->presenter);
    }
)->throws(NotFoundException::class, 'User not found');

it('should update access groups for the authenticated user', function () {
    $request = LoginRequest::createForOpenId('127.0.0.1', 'abcde-fghij-klmno');

    $accessGroup1 = new AccessGroup(1, "access_group_1", "access_group_1");
    $accessGroup2 = new AccessGroup(2, "access_group_2", "access_group_2");
    $authorizationRules = [
        new AuthorizationRule("group1", $accessGroup1),
        new AuthorizationRule("group2", $accessGroup2)
    ];
    $this->validOpenIdConfiguration->getCustomConfiguration()->setAuthorizationRules($authorizationRules);

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->willReturn($this->provider);

    $this->provider
        ->expects($this->any())
        ->method('getConfiguration')
        ->willReturn($this->validOpenIdConfiguration);

    $contact = (new Contact())->setId(1);
    $this->provider
        ->expects($this->once())
        ->method('findUserOrFail')
        ->willReturn($contact);

    $this->aclUpdater
        ->expects($this->once())
        ->method('updateForProviderAndUser')
        ->with($this->provider, $contact);

    $useCase = new Login(
        $this->providerFactory,
        $this->session,
        $this->dataStorageEngine,
        $this->writeSessionRepository,
        $this->readTokenRepository,
        $this->writeTokenRepository,
        $this->writeSessionTokenRepository,
        $this->aclUpdater,
        $this->menuService,
        $this->defaultRedirectUri
    );

    $useCase($request, $this->presenter);
});

//it('should not duplicate ACL insertion when access group is in multiple authorization rules', function () {
//
//    $request = LoginRequest::createForOpenId(Provider::OPENID, '127.0.0.1', 'abcde-fghij-klmno');
//
//    $accessGroup1 = new AccessGroup(1, "access_group_1", "access_group_1");
//    $authorizationRules = [
//        new AuthorizationRule("group1", $accessGroup1),
//        new AuthorizationRule("group2", $accessGroup1)
//    ];
//    $customConfiguration = $this->validOpenIdConfiguration->getCustomConfiguration();
//    $customConfiguration->setAuthorizationRules($authorizationRules);
//
//    $this->provider
//        ->expects($this->any())
//        ->method('getConfiguration')
//        ->willReturn($this->validOpenIdConfiguration);
//
//    $this->providerFactory
//        ->expects($this->once())
//        ->method('create')
//        ->willReturn($this->provider);
//
//    $useCase = new Login(
//        $this->providerFactory,
//        $this->session,
//        $this->dataStorageEngine,
//        $this->writeSessionRepository,
//        $this->readTokenRepository,
//        $this->writeTokenRepository,
//        $this->writeSessionTokenRepository,
//        $this->aclUpdater
//    );
//
//    $useCase($request, $this->presenter);
//
//    dd($this->validOpenIdConfiguration);
//});

//it('should update contact group for the authenticated user', function () {
//    $request = LoginRequest::createForOpenId(Provider::OPENID, '127.0.0.1', 'abcde-fghij-klmno');
//
//    $this->providerFactory
//        ->expects($this->once())
//        ->method('create')
//        ->willReturn($this->provider);
//
//    $this->provider
//        ->expects($this->any())
//        ->method('getConfiguration')
//        ->willReturn($this->validOpenIdConfiguration);
//
//    $contact = (new Contact())->setId(1);
//    $this->provider
//        ->expects($this->once())
//        ->method('getUser')
//        ->willReturn($contact);
//
//    $this->contactGroupRepository
//        ->expects($this->once())
//        ->method('deleteContactGroupsForUser')
//        ->with($contact);
//
//    $this->contactGroupRepository
//        ->expects($this->once())
//        ->method('insertContactGroupForUser')
//        ->with($contact, $this->contactGroup);
//
//    $useCase = new Login(
//        $this->providerFactory,
//        $this->session,
//        $this->dataStorageEngine,
//        $this->writeSessionRepository,
//        $this->readTokenRepository,
//        $this->writeTokenRepository,
//        $this->writeSessionTokenRepository,
//        $this->aclUpdater
//    );
//
//    $useCase($request, $this->presenter);
//});

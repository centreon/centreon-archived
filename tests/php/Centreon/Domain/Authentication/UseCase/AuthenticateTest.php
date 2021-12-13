<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Tests\Centreon\Domain\Authentication\UseCase;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Menu\Model\Page;
use Centreon\Domain\Authentication\Model\Credentials;
use Security\Domain\Authentication\Model\ProviderToken;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Centreon\Domain\Authentication\UseCase\Authenticate;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Centreon\Domain\Authentication\UseCase\AuthenticateRequest;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Centreon\Domain\Authentication\UseCase\AuthenticateResponse;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Centreon\Domain\Menu\Interfaces\MenuServiceInterface;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @package Tests\Centreon\Domain\Authentication\UseCase
 */
class AuthenticateTest extends TestCase
{
    /**
     * @var AuthenticationServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationService;

    /**
     * @var ProviderServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerService;

    /**
     * @var ContactServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contactService;

    /**
     * @var RequestStack|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestStack;

    /**
     * @var Request|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $session;

    /**
     * @var ProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $provider;

    /**
     * @var ContactInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contact;

    /**
     * @var AuthenticationTokens|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationTokens;

    /**
     * @var AuthenticateResponse|\PHPUnit\Framework\MockObject\MockObject
     */
    private $response;

    /**
     * @var MenuServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $menuService;

    /**
     * @var AuthenticationRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationRepository;

    /**
     * @var SessionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionRepository;

    /**
     * @var DataStorageEngineInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataStorageEngine;

    protected function setUp(): void
    {
        $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $this->providerService = $this->createMock(ProviderServiceInterface::class);
        $this->contactService = $this->createMock(ContactServiceInterface::class);
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->contact = $this->createMock(ContactInterface::class);
        $this->authenticationTokens = $this->createMock(AuthenticationTokens::class);
        $this->response = $this->createMock(AuthenticateResponse::class);
        $this->menuService = $this->createMock(MenuServiceInterface::class);
        $this->authenticationRepository = $this->createMock(AuthenticationRepositoryInterface::class);
        $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
        $this->dataStorageEngine = $this->createMock(DataStorageEngineInterface::class);
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
    }

    /**
     * test execute when provider configuration is not found
     */
    public function testExecuteProviderConfigurationNotFound(): void
    {
        $authenticate = new Authenticate(
            '/monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $credentials = new Credentials('admin', 'centreon');
        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            'provider_configuration_1',
            '/',
            null,
            '127.0.0.1'
        );

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Provider configuration (provider_configuration_1) not found');

        $authenticate->execute($authenticateRequest, $this->response);
    }

    /**
     * test execute when login / password are wrong
     */
    public function testExecuteNotAuthenticated(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(false);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $authenticate = new Authenticate(
            '/monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $credentials = new Credentials('admin', 'centreon');
        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            'provider_configuration_1',
            '/',
            null,
            '127.0.0.1'
        );

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Authentication failed');

        $authenticate->execute($authenticateRequest, $this->response);
    }

    /**
     * test execute when user is not authorizer to log in web app
     */
    public function testExecuteUserNotAuthorizeToLogInWeb(): void
    {
        $this->contact
            ->expects($this->once())
            ->method('isAllowedToReachWeb')
            ->willReturn(false);

        $this->contactService
            ->expects($this->once())
            ->method('findByName')
            ->willReturn($this->contact);

        $authenticate = new Authenticate(
            '/monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $credentials = new Credentials('admin', 'centreon');
        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            'provider_configuration_1',
            '/',
            null,
            '127.0.0.1'
        );

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User is not allowed to reach web application');

        $authenticate->execute($authenticateRequest, $this->response);
    }

    /**
     * test execute when user is not found by provider
     */
    public function testExecuteUserNotFoundByProvider(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $authenticate = new Authenticate(
            '/monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $credentials = new Credentials('admin', 'centreon');
        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            'provider_configuration_1',
            '/',
            null,
            '127.0.0.1'
        );

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User cannot be retrieved from the provider');

        $authenticate->execute($authenticateRequest, $this->response);
    }

    /**
     * test execute when user is created
     */
    public function testExecuteCreateUser(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->contact);

        $this->contactService
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->provider
            ->expects($this->once())
            ->method('canCreateUser')
            ->willReturn(true);

        $this->contactService
            ->expects($this->once())
            ->method('addUser')
            ->with($this->contact);

        $this->authenticationService
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->willReturn($this->authenticationTokens);

        $authenticate = new Authenticate(
            '/monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $credentials = new Credentials('admin', 'centreon');
        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            'provider_configuration_1',
            '/',
            null,
            '127.0.0.1'
        );

        $authenticate->execute($authenticateRequest, $this->response);
    }

    /**
     * test execute when user is not found and cannot be created
     */
    public function testExecuteCannotCreateUser(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->contact);

        $this->contactService
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->provider
            ->expects($this->once())
            ->method('canCreateUser')
            ->willReturn(false);

        $authenticate = new Authenticate(
            '/monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $credentials = new Credentials('admin', 'centreon');
        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            'provider_configuration_1',
            '/',
            null,
            '127.0.0.1'
        );

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User not found and cannot be created');

        $authenticate->execute($authenticateRequest, $this->response);
    }

    /**
     * test execute when user is updated
     */
    public function testExecuteUpdateUser(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->contact);

        $this->contactService
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->contactService
            ->expects($this->once())
            ->method('updateUser')
            ->with($this->contact);

        $this->authenticationService
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->willReturn($this->authenticationTokens);

        $authenticate = new Authenticate(
            '/monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $credentials = new Credentials('admin', 'centreon');
        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            'provider_configuration_1',
            '/',
            null,
            '127.0.0.1'
        );

        $authenticate->execute($authenticateRequest, $this->response);
    }

    /**
     * test execute when authentication tokens are created
     */
    public function testExecuteCreateAuthenticationTokens(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->contact);

        $this->contactService
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->contactService
            ->expects($this->once())
            ->method('updateUser')
            ->with($this->contact);

        $this->authenticationService
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->willReturn(null);

        $providerToken = $this->createMock(ProviderToken::class);
        $providerRefreshToken = $this->createMock(ProviderToken::class);

        $this->provider
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($providerToken);

        $this->provider
            ->expects($this->once())
            ->method('getProviderRefreshToken')
            ->willReturn($providerRefreshToken);

        $providerConfiguration = new ProviderConfiguration(1, 'local', 'local', true, true, '/centreon');
        $this->providerService
            ->expects($this->once())
            ->method('findProviderConfigurationByConfigurationName')
            ->willReturn($providerConfiguration);

        $authenticate = new Authenticate(
            '/monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $credentials = new Credentials('admin', 'centreon');
        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            'provider_configuration_1',
            '/',
            null,
            '127.0.0.1'
        );

        $authenticate->execute($authenticateRequest, $this->response);
    }

    /**
     * test execute response with default page
     */
    public function testExecuteDefaultPage(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->contact);

        $this->contactService
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->authenticationService
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->willReturn($this->authenticationTokens);

        $this->contact
            ->expects($this->once())
            ->method('getDefaultPage')
            ->willReturn(null);

        $this->response
            ->expects($this->once())
            ->method('getRedirectionUri')
            ->willReturn('//monitoring/resources');

        $authenticate = new Authenticate(
            '/monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $credentials = new Credentials('admin', 'centreon');
        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            'provider_configuration_1',
            '/',
            null,
            '127.0.0.1'
        );

        $authenticate->execute($authenticateRequest, $this->response);
        $this->assertEquals('//monitoring/resources', $this->response->getRedirectionUri());
    }

    /**
     * test execute response with custom default page (defined by user)
     */
    public function testExecuteCustomDefaultPage(): void
    {
        $page = new Page(1, '/my_custom_page', 60101, false);

        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->contact);

        $this->contactService
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->authenticationService
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->willReturn($this->authenticationTokens);

        $this->contact
            ->expects($this->any())
            ->method('getDefaultPage')
            ->willReturn($page);

        $this->response
            ->expects($this->once())
            ->method('getRedirectionUri')
            ->willReturn('//my_custom_page');

        $authenticate = new Authenticate(
            '/monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $credentials = new Credentials('admin', 'centreon');
        $authenticateRequest = new AuthenticateRequest(
            $credentials,
            'provider_configuration_1',
            '/',
            null,
            '127.0.0.1'
        );

        $authenticate->execute($authenticateRequest, $this->response);
        $this->assertEquals('//my_custom_page', $this->response->getRedirectionUri());
    }
}

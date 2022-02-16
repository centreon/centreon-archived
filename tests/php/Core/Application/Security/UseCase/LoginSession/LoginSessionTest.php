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

namespace Tests\Core\Application\Security\UseCase\LoginSession;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Menu\Model\Page;
use Security\Domain\Authentication\Model\ProviderToken;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Core\Application\Security\UseCase\LoginSession\LoginSession;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Core\Infrastructure\Security\Api\LoginSession\LoginSessionPresenter;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\Security\UseCase\LoginSession\LoginSessionRequest;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Centreon\Domain\Authentication\Exception\AuthenticationException as LegacyAuthenticationException;
use Core\Domain\Security\Authentication\AuthenticationException;
use Core\Domain\Security\Authentication\PasswordExpiredException;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Application\Security\UseCase\LoginSession\PasswordExpiredResponse;
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

class LoginSessionTest extends TestCase
{
    /**
     * @var PresenterFormatterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenterFormatter;

    /**
     * @var LoginSessionPresenter
     */
    private $loginSessionPresenter;

    /**
     * @var AuthenticationServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationService;

    /**
     * @var ProviderServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerService;

    /**
     * @var ContactServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $contactService;

    /**
     * @var RequestStack&\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestStack;

    /**
     * @var Request&\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var SessionInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $session;

    /**
     * @var ProviderInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $provider;

    /**
     * @var ContactInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $contact;

    /**
     * @var AuthenticationTokens&\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationTokens;

    /**
     * @var MenuServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $menuService;

    /**
     * @var AuthenticationRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationRepository;

    /**
     * @var SessionRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionRepository;

    /**
     * @var DataStorageEngineInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataStorageEngine;

    protected function setUp(): void
    {
        $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
        $this->loginSessionPresenter = new LoginSessionPresenter($this->presenterFormatter);
        $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $this->providerService = $this->createMock(ProviderServiceInterface::class);
        $this->contactService = $this->createMock(ContactServiceInterface::class);
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->contact = $this->createMock(ContactInterface::class);
        $this->authenticationTokens = $this->createMock(AuthenticationTokens::class);
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
        $authenticate = new LoginSession(
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

        $authenticateRequest = $this->createLoginSessionRequest();

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn(null);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Provider configuration (provider_configuration_1) not found');

        $authenticate($this->loginSessionPresenter, $authenticateRequest);
    }

    /**
     * test execute when login / password are wrong
     */
    public function testExecuteNotAuthenticated(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail')
            ->willThrowException(AuthenticationException::notAuthenticated());

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $authenticate = new LoginSession(
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

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($this->loginSessionPresenter, $authenticateRequest);

        $this->assertInstanceOf(UnauthorizedResponse::class, $this->loginSessionPresenter->getResponseStatus());
    }

    /**
     * test execute when password is expired
     */
    public function testExecutePasswordExpired(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail')
            ->willThrowException(PasswordExpiredException::passwordIsExpired());

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $authenticate = new LoginSession(
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

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($this->loginSessionPresenter, $authenticateRequest);

        $this->assertInstanceOf(PasswordExpiredResponse::class, $this->loginSessionPresenter->getResponseStatus());
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

        $authenticate = new LoginSession(
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

        $authenticateRequest = $this->createLoginSessionRequest();

        $this->expectException(LegacyAuthenticationException::class);
        $this->expectExceptionMessage('User is not allowed to reach web application');

        $authenticate($this->loginSessionPresenter, $authenticateRequest);
    }

    /**
     * test execute when user is not found by provider
     */
    public function testExecuteUserNotFoundByProvider(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail');

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $authenticate = new LoginSession(
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

        $authenticateRequest = $this->createLoginSessionRequest();

        $this->expectException(LegacyAuthenticationException::class);
        $this->expectExceptionMessage('User cannot be retrieved from the provider');

        $authenticate($this->loginSessionPresenter, $authenticateRequest);
    }

    /**
     * test execute when user is created
     */
    public function testExecuteCreateUser(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail');

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

        $authenticate = new LoginSession(
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

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($this->loginSessionPresenter, $authenticateRequest);
    }

    /**
     * test execute when user is not found and cannot be created
     */
    public function testExecuteCannotCreateUser(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail');

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

        $authenticate = new LoginSession(
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

        $authenticateRequest = $this->createLoginSessionRequest();

        $this->expectException(LegacyAuthenticationException::class);
        $this->expectExceptionMessage('User not found and cannot be created');

        $authenticate($this->loginSessionPresenter, $authenticateRequest);
    }

    /**
     * test execute when user is updated
     */
    public function testExecuteUpdateUser(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail');

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

        $authenticate = new LoginSession(
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

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($this->loginSessionPresenter, $authenticateRequest);
    }

    /**
     * test execute when authentication tokens are created
     */
    public function testExecuteCreateAuthenticationTokens(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail');

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

        $authenticate = new LoginSession(
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

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($this->loginSessionPresenter, $authenticateRequest);
    }

    /**
     * test execute response with default page
     */
    public function testExecuteDefaultPage(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail');

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

        $this->presenterFormatter
            ->expects($this->once())
            ->method('present')
            ->with([
                'redirect_uri' => '//monitoring/resources'
            ]);

        $authenticate = new LoginSession(
            '//monitoring/resources',
            $this->authenticationService,
            $this->providerService,
            $this->contactService,
            $this->requestStack,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($this->loginSessionPresenter, $authenticateRequest);
    }

    /**
     * test execute response with custom default page (defined by user)
     */
    public function testExecuteCustomDefaultPage(): void
    {
        $page = new Page(1, '/my_custom_page', 60101, true);

        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail');

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

        $this->presenterFormatter
            ->expects($this->once())
            ->method('present')
            ->with([
                'redirect_uri' => '/my_custom_page'
            ]);

        $authenticate = new LoginSession(
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

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($this->loginSessionPresenter, $authenticateRequest);
    }

    /**
     * Create LoginSessionRequest
     *
     * @return LoginSessionRequest
     */
    private function createLoginSessionRequest(): LoginSessionRequest
    {
        $request = new LoginSessionRequest();
        $request->providerConfigurationName = 'provider_configuration_1';
        $request->login = 'admin';
        $request->password = 'centreon';
        $request->baseUri = '/';
        $request->refererQueryParameters = null;
        $request->clientIp = '127.0.0.1';

        return $request;
    }
}

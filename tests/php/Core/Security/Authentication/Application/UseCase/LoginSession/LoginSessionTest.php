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

namespace Tests\Core\Security\Authentication\Application\UseCase\LoginSession;

use Centreon\Domain\Authentication\Exception\AuthenticationException as LegacyAuthenticationException;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Centreon\Domain\Menu\Interfaces\MenuServiceInterface;
use Centreon\Domain\Menu\Model\Page;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Core\Application\Common\UseCase\PresenterInterface;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\Authentication\Application\Provider\LocalProviderInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\Repository\ReadTokenRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionTokenRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteTokenRepositoryInterface;
use Core\Security\Authentication\Application\UseCase\Login\Login;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Application\UseCase\Login\LoginResponse;
use Core\Security\Authentication\Application\UseCase\LoginSession\PasswordExpiredResponse;
use Core\Security\Authentication\Domain\Exception\AuthenticationException;
use Core\Security\Authentication\Domain\Exception\PasswordExpiredException;
use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\Authentication\Infrastructure\Api\Login\Local\LoginPresenter;
use Core\Security\Authentication\Infrastructure\Provider\AclUpdaterInterface;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use PHPUnit\Framework\TestCase;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginSessionTest extends TestCase
{
    /**
     * @var PresenterFormatterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenterFormatter;

    /**
     * @var LoginPresenter
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
     * @var ProviderAuthenticationInterface&\PHPUnit\Framework\MockObject\MockObject
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

    /**
     * @var ProviderAuthenticationFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private ProviderAuthenticationFactoryInterface $providerFactory;

    private ReadTokenRepositoryInterface $readTokenRepository;

    private WriteTokenRepositoryInterface $writeTokenRepository;

    private WriteSessionTokenRepositoryInterface $writeSessionTokenRepository;

    private WriteSessionRepositoryInterface $writeSessionRepository;

    private AclUpdaterInterface $aclUpdater;

    protected function setUp(): void
    {
        $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
        $this->loginSessionPresenter = $this->createMock(PresenterInterface::class);
        $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $this->providerService = $this->createMock(ProviderServiceInterface::class);
        $this->contactService = $this->createMock(ContactServiceInterface::class);
        $this->provider = $this->createMock(ProviderAuthenticationInterface::class);
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

        $this->providerFactory = $this->createMock(ProviderAuthenticationFactoryInterface::class);
        $this->readTokenRepository = $this->createMock(ReadTokenRepositoryInterface::class);
        $this->writeTokenRepository = $this->createMock(WriteTokenRepositoryInterface::class);
        $this->writeSessionTokenRepository = $this->createMock(WriteSessionTokenRepositoryInterface::class);
        $this->writeSessionRepository = $this->createMock(WriteSessionRepositoryInterface::class);
        $this->aclUpdater = $this->createMock(AclUpdaterInterface::class);
    }

    /**
     * test execute when provider configuration is not found
     */
    public function testExecuteProviderConfigurationNotFound(): void
    {
        $authenticate = $this->createLoginUseCase();

        $authenticateRequest = $this->createLoginSessionRequest();

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willThrowException(ProviderException::providerConfigurationNotFound(Provider::LOCAL));


        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage(
            ProviderException::providerConfigurationNotFound(Provider::LOCAL)->getMessage()
        );

        $authenticate($authenticateRequest, $this->loginSessionPresenter);
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

        $this->expectException(AuthenticationException::class);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->provider);

        $authenticate = $this->createLoginUseCase();

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($authenticateRequest, $this->loginSessionPresenter);

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

        $this->expectException(PasswordExpiredException::class);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->provider);

        $authenticate = $this->createLoginUseCase();

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($authenticateRequest, $this->loginSessionPresenter);

        $this->assertInstanceOf(PasswordExpiredResponse::class, $this->loginSessionPresenter->getResponseStatus());
    }

    /**
     * test execute when user is not authorizer to log in web app
     */
    public function testExecuteUserNotAuthorizeToLogInWeb(): void
    {
        $authenticate = $this->createLoginUseCase();

        $authenticateRequest = $this->createLoginSessionRequest();

        $this->expectException(LegacyAuthenticationException::class);
        $this->expectExceptionMessage('User is not allowed to reach web application');

        $authenticate($authenticateRequest, $this->loginSessionPresenter);
    }

    /**
     * test execute when user is not found by provider
     */
    public function testExecuteUserNotFoundByProvider(): void
    {
        $this->provider
            ->expects($this->once())
            ->method('authenticateOrFail');

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('findUserOrFail')
            ->willThrowException(LegacyAuthenticationException::userNotFound());

        $authenticate = $this->createLoginUseCase();
        $authenticateRequest = $this->createLoginSessionRequest();

        $this->expectException(LegacyAuthenticationException::class);
        $this->expectExceptionMessage('User cannot be retrieved from the provider');

        $authenticate($authenticateRequest, $this->loginSessionPresenter);
    }

    /**
     * test execute when user is created
     */
    public function testExecuteCreateUser(): void
    {
        $this->contact
            ->expects($this->once())
            ->method('isAllowedToReachWeb')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('findUserOrFail')
            ->willReturn($this->contact);

        $this->provider
            ->expects($this->once())
            ->method('isAutoImportEnabled')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('importUserToDatabase');

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->provider);

        $authenticate = $this->createLoginUseCase();

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($authenticateRequest, $this->loginSessionPresenter);
    }

    /**
     * test execute when user is not found and cannot be created
     */
    public function testExecuteCannotCreateUser(): void
    {
        $this->contact
            ->method('isAllowedToReachWeb')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('findUserOrFail')
            ->willReturn($this->contact);

        $this->provider
            ->expects($this->once())
            ->method('isAutoImportEnabled')
            ->willReturn(false);

        $this->provider
            ->expects($this->never())
            ->method('importUserToDatabase');

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->provider);

        $authenticate = $this->createLoginUseCase();
        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($authenticateRequest, $this->loginSessionPresenter);
    }

    /**
     * test execute when authentication tokens are created
     */
    public function testExecuteCreateAuthenticationTokens(): void
    {
        $this->contact
            ->method('isAllowedToReachWeb')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('findUserOrFail')
            ->willReturn($this->contact);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->provider);

        $this->writeSessionRepository
            ->expects($this->once())
            ->method('start')
            ->willReturn(true);

        $this->readTokenRepository
            ->expects($this->once())
            ->method('hasAuthenticationTokensByToken')
            ->willReturn(false);

        $providerToken = $this->createMock(NewProviderToken::class);
        $providerRefreshToken = $this->createMock(NewProviderToken::class);

        $this->provider
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($providerToken);

        $this->provider
            ->expects($this->once())
            ->method('getProviderRefreshToken')
            ->willReturn($providerRefreshToken);

        $this->writeTokenRepository
            ->expects($this->once())
            ->method('createAuthenticationTokens');

        $authenticate = $this->createLoginUseCase();
        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($authenticateRequest, $this->loginSessionPresenter);
    }

    /**
     * test execute response with default page
     */
    public function testExecuteDefaultPage(): void
    {
        $this->contact
            ->method('isAllowedToReachWeb')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('findUserOrFail')
            ->willReturn($this->contact);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->provider);

        $this->writeSessionRepository
            ->expects($this->once())
            ->method('start')
            ->willReturn(true);

        $this->readTokenRepository
            ->expects($this->once())
            ->method('hasAuthenticationTokensByToken')
            ->willReturn(true);

        $this->loginSessionPresenter
            ->expects($this->once())
            ->method('present')
            ->with(new LoginResponse('/monitoring/resources'));

        $authenticate = $this->createLoginUseCase();
        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($authenticateRequest, $this->loginSessionPresenter);
    }

    /**
     * test execute response with custom default page (defined by user)
     */
    public function testExecuteCustomDefaultPage(): void
    {
        $page = new Page(1, '/my_custom_page', 60101, true);

        $this->contact
            ->expects($this->any())
            ->method('getDefaultPage')
            ->willReturn($page);

        $this->contact
            ->method('isAllowedToReachWeb')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('findUserOrFail')
            ->willReturn($this->contact);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->provider);

        $this->writeSessionRepository
            ->expects($this->once())
            ->method('start')
            ->willReturn(true);

        $this->readTokenRepository
            ->expects($this->once())
            ->method('hasAuthenticationTokensByToken')
            ->willReturn(true);

        $this->loginSessionPresenter
            ->expects($this->once())
            ->method('present')
            ->with(new LoginResponse('/my_custom_page'));

        $authenticate = $this->createLoginUseCase();

        $authenticateRequest = $this->createLoginSessionRequest();

        $authenticate($authenticateRequest, $this->loginSessionPresenter);
    }

    /**
     * Create LoginSessionRequest
     *
     * @param string $username
     * @param string $password
     * @return LoginRequest
     */
    private function createLoginSessionRequest(string $username = 'admin', string $password = 'centreon'): LoginRequest
    {
        return LoginRequest::createForLocal(
            Provider::LOCAL,
            $username,
            $password,
            '127.0.0.1'
        );
    }

    /**
     * @return Login
     *
     *  private ProviderFactoryInterface             $providerFactory,
     * private SessionInterface                     $session,
     * private DataStorageEngineInterface           $dataStorageEngine,
     * private WriteSessionRepositoryInterface      $sessionRepository,
     * private ReadTokenRepositoryInterface         $readTokenRepository,
     * private WriteTokenRepositoryInterface        $writeTokenRepository,
     * private WriteSessionTokenRepositoryInterface $writeSessionTokenRepository,
     * private AclUpdaterInterface                  $aclUpdater)
     */
    private function createLoginUseCase(): Login
    {
        return new Login(
            $this->providerFactory,
            $this->session,
            $this->dataStorageEngine,
            $this->writeSessionRepository,
            $this->readTokenRepository,
            $this->writeTokenRepository,
            $this->writeSessionTokenRepository,
            $this->aclUpdater
        );
    }
}

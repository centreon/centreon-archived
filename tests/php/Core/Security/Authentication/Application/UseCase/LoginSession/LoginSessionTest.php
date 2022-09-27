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

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Menu\Model\Page;
use Symfony\Component\HttpFoundation\Request;
use Core\Application\Common\UseCase\ErrorResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Core\Application\Common\UseCase\PresenterInterface;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Menu\Interfaces\MenuServiceInterface;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Core\Security\Authentication\Application\UseCase\Login\Login;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Application\UseCase\Login\LoginResponse;
use Core\Security\Authentication\Domain\Exception\AuthenticationException;
use Core\Security\Authentication\Domain\Exception\PasswordExpiredException;
use Core\Security\Authentication\Infrastructure\Provider\AclUpdaterInterface;
use Core\Security\Authentication\Application\UseCase\Login\PasswordExpiredResponse;
use Core\Security\Authentication\Application\Repository\ReadTokenRepositoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\Repository\WriteTokenRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionRepositoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionTokenRepositoryInterface;
use Centreon\Domain\Authentication\Exception\AuthenticationException as LegacyAuthenticationException;

beforeEach(function () {
    $this->provider = $this->createMock(ProviderAuthenticationInterface::class);
    $this->contact = $this->createMock(ContactInterface::class);
    $this->menuService = $this->createMock(MenuServiceInterface::class);
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
    $this->defaultRedirectUri = '/monitoring/resources';
    $this->useCase = new Login(
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

    $this->authenticationRequest = LoginRequest::createForLocal("admin", "password", '127.0.0.1');
});

it('should present an error Response when the Provider configuration is not found', function () {
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
    $presenter = new LoginPresenterStub();
    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->with(Provider::LOCAL)
        ->willThrowException(ProviderException::providerConfigurationNotFound(Provider::LOCAL));

    $useCase($this->authenticationRequest, $presenter);
    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
});


it('should present an UnauthorizedResponse when the authentication fail', function () {
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
    $presenter = new LoginPresenterStub();
    $this->provider
        ->expects($this->once())
        ->method('authenticateOrFail')
        ->willThrowException(AuthenticationException::notAuthenticated());

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->with(Provider::LOCAL)
        ->willReturn($this->provider);

    $useCase($this->authenticationRequest, $presenter);
    expect($presenter->getResponseStatus())->toBeInstanceOf(UnauthorizedResponse::class);
});

it('should present a PasswordExpiredResponse when the user password is expired', function () {
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
    $presenter = new LoginPresenterStub();
    $this->provider
        ->expects($this->once())
        ->method('authenticateOrFail')
        ->willThrowException(PasswordExpiredException::passwordIsExpired());

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->with(Provider::LOCAL)
        ->willReturn($this->provider);

    $useCase($this->authenticationRequest, $presenter);
    expect($presenter->getResponseStatus())->toBeInstanceOf(PasswordExpiredResponse::class);
});

it('should present an UnauthorizedResponse when User is not authorize to login', function () {
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
    $presenter = new LoginPresenterStub();
    $useCase($this->authenticationRequest, $presenter);
    expect($presenter->getResponseStatus())->toBeInstanceOf(UnauthorizedResponse::class);
});


it("should present an UnauthorizedResponse when User doesn't exist", function () {
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
    $presenter = new LoginPresenterStub();
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

    $useCase($this->authenticationRequest, $presenter);
    expect($presenter->getResponseStatus())->toBeInstanceOf(UnauthorizedResponse::class);
});

it('should create an user when auto import is enabled', function () {
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
    $presenter = new LoginPresenterStub();
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
        ->method('importUser');

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->with(Provider::LOCAL)
        ->willReturn($this->provider);

    $useCase($this->authenticationRequest, $presenter);
});

it('should create authentication tokens when user is correctly authenticated', function () {
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
    $presenter = new LoginPresenterStub();

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

    $useCase($this->authenticationRequest, $presenter);
});

it('should present the default page when user is correctly authenticated', function () {
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
    $presenter = new LoginPresenterStub();

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

    $useCase($this->authenticationRequest, $presenter);
    expect($presenter->getResponseStatus())->toBeInstanceOf(LoginResponse::class);
    expect($presenter->response->getMessage())->toBe('/monitoring/resources');
});

it('should present the custom redirection page when user is authenticated', function () {
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
    $presenter = new LoginPresenterStub();
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

    $useCase($this->authenticationRequest, $presenter);
    expect($presenter->getResponseStatus())->toBeInstanceOf(LoginResponse::class);
    expect($presenter->response->getMessage())->toBe($page->getRedirectionUri());
});

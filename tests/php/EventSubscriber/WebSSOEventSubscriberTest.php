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

namespace Tests\EventSubscriber;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteTokenRepositoryInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Domain\Exception\SSOAuthenticationException;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Core\Security\ProviderConfiguration\Domain\WebSSO\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\WebSSO\Model\CustomConfiguration;
use EventSubscriber\WebSSOEventSubscriber;
use InvalidArgumentException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

beforeEach(function () {
    $this->contactRepository = $this->createMock(ContactRepositoryInterface::class);
    $this->session = $this->createMock(SessionInterface::class);
    $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
    $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
    $this->dataStorageEngine = $this->createMock(DataStorageEngineInterface::class);
    $this->optionService = $this->createMock(OptionServiceInterface::class);
    $this->event = $this->createMock(RequestEvent::class);
    $this->request = $this->createMock(Request::class);
    $this->writeTokenRepository = $this->createMock(WriteTokenRepositoryInterface::class);
    $this->writeSessionRepository = $this->createMock(WriteSessionRepositoryInterface::class);
    $this->providerFactory = $this->createMock(ProviderAuthenticationFactoryInterface::class);
    $this->provider = $this->createMock(ProviderAuthenticationInterface::class);
    $this->contact = $this->createMock(ContactInterface::class);

    $this->subscriber = new WebSSOEventSubscriber(
        $this->authenticationService,
        $this->sessionRepository,
        $this->dataStorageEngine,
        $this->optionService,
        $this->writeTokenRepository,
        $this->writeSessionRepository,
        $this->providerFactory
    );
});

it('should do nothing if user is already connected', function () {

    $this->request->cookies = new InputBag();
    $this->request->cookies->set('PHPSESSID', '1234');
    $this->event->method('getRequest')->willReturn($this->request);

    $this->provider
        ->expects($this->never())
        ->method('authenticateOrFail');

    $this->provider
        ->expects($this->never())
        ->method('findUserOrFail');

    $this->writeSessionRepository
        ->expects($this->never())
        ->method('start');

    $this->subscriber->loginWebSSOUser($this->event);
});

it('should do nothing if Web SSO is not active', function () {

    $this->request->cookies = new InputBag();
    $this->request->cookies->set('PHPSESSID', null);
    $this->request
        ->method('getSession')
        ->willReturn($this->session);
    $this->session
        ->method('getId')
        ->willReturn(uniqid());

    $this->event->method('getRequest')->willReturn($this->request);
    $parameters = [
        'trusted_client_addresses' => [],
        'blacklist_client_addresses' => [],
        'login_header_attribute' => null,
        'pattern_matching_login' => null,
        'pattern_replace_login' => null
    ];
    $configuration = new Configuration(
        3,
        Provider::WEB_SSO,
        Provider::WEB_SSO,
        json_encode($parameters),
        false,
        false
    );
    $configuration->setCustomConfiguration(new CustomConfiguration());

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->willReturn($this->provider);

    $this->provider
        ->expects($this->once())
        ->method('getConfiguration')
        ->willReturn($configuration);

    $this->provider
        ->expects($this->never())
        ->method('authenticateOrFail');

    $this->provider
        ->expects($this->never())
        ->method('findUserOrFail');

    $this->event
        ->expects($this->never())
        ->method('getRequest');

    $this->contactRepository
        ->expects($this->never())
        ->method('findByName');

    $this->subscriber->loginWebSSOUser($this->event);
});

it("should throw an exception if the user's IP is blacklisted", function () {
    $this->request->cookies = new InputBag();
    $this->request->cookies->set('PHPSESSID', null);
    $this->request
        ->expects($this->exactly(1))
        ->method('getClientIp')
        ->willReturn('127.0.0.1');
    $this->request
        ->method('getSession')
        ->willReturn($this->session);
    $this->session
        ->method('getId')
        ->willReturn('');

    $this->event->method('getRequest')->willReturn($this->request);

    $parameters = [
        'trusted_client_addresses' => [],
        'blacklist_client_addresses' => ['127.0.0.1'],
        'login_header_attribute' => null,
        'pattern_matching_login' => null,
        'pattern_replace_login' => null
    ];
    $configuration = new Configuration(
        3,
        Provider::WEB_SSO,
        Provider::WEB_SSO,
        json_encode($parameters),
        true,
        false
    );
    $configuration->setCustomConfiguration(new CustomConfiguration([], ['127.0.0.1']));

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->willReturn($this->provider);

    $this->provider
        ->expects($this->exactly(1))
        ->method('getConfiguration')
        ->willReturn($configuration);

    $this->provider
        ->expects($this->once())
        ->method('authenticateOrFail')
        ->with(LoginRequest::createForSSO('127.0.0.1'))
        ->willThrowException(SSOAuthenticationException::blackListedClient());

    $this->provider
        ->expects($this->never())
        ->method('findUserOrFail')
        ->willReturn($this->contact);

    $this->writeSessionRepository
        ->expects($this->never())
        ->method('start');

    $this->subscriber->loginWebSSOUser($this->event);
})->throws(SSOAuthenticationException::class, 'Your IP is blacklisted');

it("should throw an exception if the user's IP is not whitelisted", function () {

    $this->request->cookies = new InputBag();
    $this->request->cookies->set('PHPSESSID', null);
    $this->request
        ->expects($this->exactly(1))
        ->method('getClientIp')
        ->willReturn('127.0.0.1');
    $this->request
        ->method('getSession')
        ->willReturn($this->session);
    $this->session
        ->method('getId')
        ->willReturn('');

    $this->event->method('getRequest')->willReturn($this->request);

    $parameters = [
        'trusted_client_addresses' => ['127.0.0.2'],
        'blacklist_client_addresses' => [],
        'login_header_attribute' => null,
        'pattern_matching_login' => null,
        'pattern_replace_login' => null
    ];
    $configuration = new Configuration(
        3,
        Provider::WEB_SSO,
        Provider::WEB_SSO,
        json_encode($parameters),
        true,
        false
    );
    $configuration->setCustomConfiguration(new CustomConfiguration(['127.0.0.2']));

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->willReturn($this->provider);

    $this->provider
        ->expects($this->exactly(1))
        ->method('getConfiguration')
        ->willReturn($configuration);

    $this->provider
        ->expects($this->once())
        ->method('authenticateOrFail')
        ->with(LoginRequest::createForSSO('127.0.0.1'))
        ->willThrowException(SSOAuthenticationException::blackListedClient());

    $this->provider
        ->expects($this->never())
        ->method('findUserOrFail')
        ->willReturn($this->contact);

    $this->writeSessionRepository
        ->expects($this->never())
        ->method('start');

    $this->subscriber->loginWebSSOUser($this->event);
})->throws(\Exception::class);

it('should throw an exception when login attribute environment variable is not set', function () {

    $this->request->cookies = new InputBag();
    $this->request->cookies->set('PHPSESSID', null);

    $this->request
        ->method('getSession')
        ->willReturn($this->session);

    $this->session
        ->method('getId')
        ->willReturn('');

    unset($_SERVER['HTTP_AUTH_CLIENT']);
    $parameters = [
        'trusted_client_addresses' => [],
        'blacklist_client_addresses' => [],
        'login_header_attribute' => 'HTTP_AUTH_CLIENT',
        'pattern_matching_login' => null,
        'pattern_replace_login' => null
    ];
    $configuration = new Configuration(
        3,
        Provider::WEB_SSO,
        Provider::WEB_SSO,
        json_encode($parameters),
        true,
        false
    );
    $configuration->setCustomConfiguration(new CustomConfiguration([], [], 'HTTP_AUTH_CLIENT'));

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->willReturn($this->provider);

    $this->provider
        ->expects($this->exactly(1))
        ->method('getConfiguration')
        ->willReturn($configuration);

    $this->event
        ->expects($this->once())
        ->method('getRequest')
        ->willReturn($this->request);

    $this->request
        ->expects($this->once())
        ->method('getClientIp')
        ->willReturn('127.0.0.1');

    $this->provider
        ->expects($this->once())
        ->method('authenticateOrFail')
        ->with(LoginRequest::createForSSO('127.0.0.1'))
        ->willThrowException(new InvalidArgumentException('Missing Login Attribute'));

    $this->provider
        ->expects($this->never())
        ->method('findUserOrFail')
        ->willReturn($this->contact);

    $this->writeSessionRepository
        ->expects($this->never())
        ->method('start');

    $this->subscriber->loginWebSSOUser($this->event);
})->throws(\InvalidArgumentException::class, 'Missing Login Attribute');

it('should throw an exception when login matching regexp returns an invalid result', function () {
    $this->request->cookies = new InputBag();
    $this->request->cookies->set('PHPSESSID', null);

    $this->request
        ->method('getSession')
        ->willReturn($this->session);
    $this->session
        ->method('getId')
        ->willReturn('');

    unset($_SERVER['HTTP_AUTH_CLIENT']);
    $parameters = [
        'trusted_client_addresses' => [],
        'blacklist_client_addresses' => [],
        'login_header_attribute' => 'HTTP_AUTH_CLIENT',
        'pattern_matching_login' => null,
        'pattern_replace_login' => null
    ];
    $configuration = new Configuration(
        3,
        Provider::WEB_SSO,
        Provider::WEB_SSO,
        json_encode($parameters),
        true,
        false
    );
    $configuration->setCustomConfiguration(new CustomConfiguration([], [], 'HTTP_AUTH_CLIENT'));

    $this->providerFactory
        ->expects($this->once())
        ->method('create')
        ->willReturn($this->provider);

    $this->provider
        ->expects($this->exactly(1))
        ->method('getConfiguration')
        ->willReturn($configuration);

    $this->event
        ->expects($this->once())
        ->method('getRequest')
        ->willReturn($this->request);

    $this->request
        ->expects($this->once())
        ->method('getClientIp')
        ->willReturn('127.0.0.1');

    $this->provider
        ->expects($this->once())
        ->method('authenticateOrFail')
        ->with(LoginRequest::createForSSO('127.0.0.1'))
        ->willThrowException(SSOAuthenticationException::unableToRetrieveUsernameFromLoginClaim());

    $this->provider
        ->expects($this->never())
        ->method('findUserOrFail')
        ->willReturn($this->contact);

    $this->writeSessionRepository
        ->expects($this->never())
        ->method('start');

    $this->subscriber->loginWebSSOUser($this->event);
})->throws(\Exception::class);

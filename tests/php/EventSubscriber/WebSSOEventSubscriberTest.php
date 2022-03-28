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

use Pimple\Container;
use Centreon\Domain\Contact\Contact;
use EventSubscriber\WebSSOEventSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Core\Domain\Security\ProviderConfiguration\WebSSO\Model\WebSSOConfiguration;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\WebSSO\Repository\ReadWebSSOConfigurationRepositoryInterface;

beforeEach(function () {
    $this->security = $this->createMock(Security::class);
    $this->dependencyInjector = $this->createMock(Container::class);
    $this->webSSOReadRepository = $this->createMock(ReadWebSSOConfigurationRepositoryInterface::class);
    $this->contactRepository = $this->createMock(ContactRepositoryInterface::class);
    $this->session = $this->createMock(SessionInterface::class);
    $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
    $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
    $this->dataStorageEngine = $this->createMock(DataStorageEngineInterface::class);
    $this->optionService = $this->createMock(OptionServiceInterface::class);
    $this->authenticationRepository = $this->createMock(AuthenticationRepositoryInterface::class);
    $this->security = $this->createMock(Security::class);
    $this->event = $this->createMock(RequestEvent::class);
    $this->request = $this->createMock(Request::class);
    $this->subscriber = new WebSSOEventSubscriber(
        120,
        $this->dependencyInjector,
        $this->webSSOReadRepository,
        $this->contactRepository,
        $this->session,
        $this->authenticationService,
        $this->sessionRepository,
        $this->dataStorageEngine,
        $this->optionService,
        $this->authenticationRepository,
        $this->security
    );
});

it('should throw an exception if no web-sso configuration are found', function () {
    $this->webSSOReadRepository
    ->expects($this->once())
    ->method('findConfiguration')
    ->willReturn(null);

    $this->subscriber->loginWebSSOUser($this->event);
})->throws(NotFoundException::class);

it('should do nothing if user is already connected', function () {
    $contact = new Contact();
    $this->security
        ->expects($this->once())
        ->method('getUser')
        ->willReturn($contact);

    $this->webSSOReadRepository
        ->expects($this->never())
        ->method('findConfiguration');

    $this->contactRepository
        ->expects($this->never())
        ->method('findByName');

    $this->subscriber->loginWebSSOUser($this->event);
});

it('should do nothing if Web SSO is not active', function () {
    $webSSOConfiguration = new WebSSOConfiguration(false, false, [], [], '', '', '');

    $this->security
        ->expects($this->once())
        ->method('getUser')
        ->willReturn(null);

    $this->webSSOReadRepository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willReturn($webSSOConfiguration);

    $this->event
        ->expects($this->once())
        ->method('getRequest');

    $this->contactRepository
        ->expects($this->never())
        ->method('findByName');

    $this->subscriber->loginWebSSOUser($this->event);
});

it('should throw an exception if the user IP is blacklisted', function () {
    $blacklistedIp = '127.0.0.1';
    $webSSOConfiguration = new WebSSOConfiguration(true, false, [], [$blacklistedIp], '', '', '');

    $this->webSSOReadRepository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willReturn($webSSOConfiguration);

    $this->event
        ->expects($this->once())
        ->method('getRequest')
        ->willReturn($this->request);

    $this->request
        ->expects($this->once())
        ->method('getClientIp')
        ->willReturn($blacklistedIp);

    $this->subscriber->loginWebSSOUser($this->event);
})->throws(\Exception::class);

it('should throw an exception if the user IP is not whitelisted', function () {
    $webSSOConfiguration = new WebSSOConfiguration(true, false, ['127.0.0.2'], [], '', '', '');

    $this->webSSOReadRepository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willReturn($webSSOConfiguration);

    $this->event
        ->expects($this->once())
        ->method('getRequest')
        ->willReturn($this->request);

    $this->request
        ->expects($this->once())
        ->method('getClientIp')
        ->willReturn('127.0.0.1');

    $this->subscriber->loginWebSSOUser($this->event);
})->throws(\Exception::class);

it('should throw an exception when login attribute environment variable is not set', function () {
    $webSSOConfiguration = new WebSSOConfiguration(true, false, [], [], 'HTTP_AUTH_CLIENT', '', '');

    $this->webSSOReadRepository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willReturn($webSSOConfiguration);

    $this->event
        ->expects($this->once())
        ->method('getRequest')
        ->willReturn($this->request);

    $this->request
        ->expects($this->once())
        ->method('getClientIp')
        ->willReturn('127.0.0.1');

    $this->subscriber->loginWebSSOUser($this->event);
})->throws(\InvalidArgumentException::class);

it('should throw an exception when login matching regexp return an invalid result', function () {
    $webSSOConfiguration = new WebSSOConfiguration(true, false, [], [], 'HTTP_AUTH_CLIENT', '@.*', '');
    $_SERVER['HTTP_AUTH_USER'] = 'oidc';
    $this->webSSOReadRepository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willReturn($webSSOConfiguration);

    $this->event
        ->expects($this->once())
        ->method('getRequest')
        ->willReturn($this->request);

    $this->request
        ->expects($this->once())
        ->method('getClientIp')
        ->willReturn('127.0.0.1');

    $this->subscriber->loginWebSSOUser($this->event);
})->throws(\Exception::class);

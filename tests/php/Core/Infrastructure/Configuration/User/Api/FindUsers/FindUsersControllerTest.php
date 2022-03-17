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

namespace Tests\Core\Infrastructure\Configuration\User\Api\FindUsers;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Centreon\Domain\Contact\Contact;
use Core\Application\Configuration\User\UseCase\FindUsers\FindUsers;
use Core\Infrastructure\Configuration\User\Api\FindUsers\FindUsersController;
use Core\Application\Configuration\User\UseCase\FindUsers\FindUsersPresenterInterface;

class FindUsersControllerTest extends TestCase
{
    /**
     * @var FindUsersPresenterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenter;

    /**
     * @var FindUsers&\PHPUnit\Framework\MockObject\MockObject
     */
    private $useCase;

    /**
     * @var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $container;

    public function setUp(): void
    {
        $this->presenter = $this->createMock(FindUsersPresenterInterface::class);
        $this->useCase = $this->createMock(FindUsers::class);

        $timezone = new \DateTimeZone('Europe/Paris');
        $adminContact = (new Contact())
        ->setId(1)
        ->setName('admin')
        ->setAdmin(true)
        ->setTimezone($timezone);

        /**
         * @var AuthorizationCheckerInterface&\PHPUnit\Framework\MockObject\MockObject
         */
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);

        /**
         * @var TokenInterface&\PHPUnit\Framework\MockObject\MockObject
         */
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($adminContact);

        /**
         * @var TokenStorageInterface&\PHPUnit\Framework\MockObject\MockObject
         */
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->expects($this->any())
            ->method('has')
            ->willReturn(true);
        $this->container->expects($this->any())
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('security.authorization_checker')],
                [$this->equalTo('parameter_bag')]
            )
            ->willReturnOnConsecutiveCalls(
                $authorizationChecker,
                new class () {
                    public function get(): string
                    {
                        return __DIR__ . '/../../../../../';
                    }
                }
            );
    }

    /**
     * Test that the controller calls properly the usecase
     */
    public function testFindControllerExecute(): void
    {
        $controller = new FindUsersController();
        $controller->setContainer($this->container);

        $this->useCase
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->equalTo($this->presenter)
            );

        $controller($this->useCase, $this->presenter);
    }
}

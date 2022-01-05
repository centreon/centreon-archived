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

namespace Tests\Infrastructure\Security\Api\LogoutSession;

use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\InputBag;
use Core\Application\Security\UseCase\LogoutSession\LogoutSession;
use Core\Infrastructure\Security\Api\LogoutSession\LogoutSessionController;

class LogoutSessionControllerTest extends TestCase
{
    /**
     * @var Request&\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var LogoutSession&\PHPUnit\Framework\MockObject\MockObject
     */
    private $useCase;

    /**
     * @var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $container;

    /**
     * @var InputBag&\PHPUnit\Framework\MockObject\MockObject
     */
    private $inputBag;

    public function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->useCase = $this->createMock(LogoutSession::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->inputBag = $this->createMock(InputBag::class);
    }

    /**
     * test Logout.
     */
    public function testLogout(): void
    {
        $logoutSessionController = new LogoutSessionController();
        $logoutSessionController->setContainer($this->container);

        $this->request->cookies = $this->inputBag;

        $this->inputBag
            ->expects($this->once())
            ->method('get')
            ->willReturn('token');

        $view = $logoutSessionController($this->useCase, $this->request);

        $this->assertEquals(
            View::create([
                "message" => 'Successful logout'
            ]),
            $view
        );
    }

    /**
     * test Logout with bad token.
     */
    public function testLogoutFailed(): void
    {
        $logoutSessionController = new LogoutSessionController();
        $logoutSessionController->setContainer($this->container);

        $this->request->cookies = $this->inputBag;

        $this->inputBag
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $view = $logoutSessionController($this->useCase, $this->request);

        $this->assertEquals(
            View::create(
                [
                    "code" => Response::HTTP_UNAUTHORIZED,
                    "message" => 'Invalid credentials'
                ],
                Response::HTTP_UNAUTHORIZED
            ),
            $view
        );
    }
}

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

namespace Tests\Core\Security\Authentication\Infrastructure\Api\LogoutSession;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Core\Security\Authentication\Application\UseCase\LogoutSession\LogoutSession;
use Core\Security\Authentication\Infrastructure\Api\LogoutSession\LogoutSessionController;
use Core\Security\Authentication\Infrastructure\Api\LogoutSession\LogoutSessionPresenter;
use Core\Infrastructure\Common\Presenter\JsonFormatter;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Application\Common\UseCase\ErrorResponse;

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
     * @var LogoutSessionPresenter
     */
    private $logoutSessionPresenter;

    public function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->useCase = $this->createMock(LogoutSession::class);
        $this->logoutSessionPresenter = new LogoutSessionPresenter(new JsonFormatter());
    }

    /**
     * test Logout.
     */
    public function testLogout(): void
    {
        $logoutSessionController = new LogoutSessionController();

        $this->request->cookies = new InputBag(['PHPSESSID' => 'token']);

        $this->logoutSessionPresenter->setResponseStatus(new NoContentResponse());

        $this->useCase->expects($this->once())
            ->method('__invoke')
            ->with('token', $this->logoutSessionPresenter);

        $response = $logoutSessionController($this->useCase, $this->request, $this->logoutSessionPresenter);

        $this->assertEquals(
            new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT),
            $response
        );
    }

    /**
     * test Logout with bad token.
     */
    public function testLogoutFailed(): void
    {
        $logoutSessionController = new LogoutSessionController();

        $this->request->cookies = new InputBag([]);

        $this->logoutSessionPresenter->setResponseStatus(new ErrorResponse('No session token provided'));

        $this->useCase->expects($this->once())
            ->method('__invoke')
            ->with(null, $this->logoutSessionPresenter);

        $response = $logoutSessionController($this->useCase, $this->request, $this->logoutSessionPresenter);

        $this->assertEquals(
            new JsonResponse(
                [
                    'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'No session token provided',
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            ),
            $response
        );
    }
}

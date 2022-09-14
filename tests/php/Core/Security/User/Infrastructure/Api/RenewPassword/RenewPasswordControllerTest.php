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

namespace Tests\Core\Security\User\Infrastructure\Api\RenewPassword;

use Core\Security\User\Application\UseCase\RenewPassword\RenewPassword;
use Core\Security\User\Application\UseCase\RenewPassword\RenewPasswordPresenterInterface;
use Core\Security\User\Infrastructure\Api\RenewPassword\RenewPasswordController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RenewPasswordControllerTest extends TestCase
{
    /**
     * @var RenewPassword&\PHPUnit\Framework\MockObject\MockObject
     */
    private $useCase;

    /**
     * @var Request&\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var RenewPasswordPresenterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenter;

    public function setUp(): void
    {
        $this->useCase = $this->createMock(RenewPassword::class);
        $this->request = $this->createMock(Request::class);
        $this->presenter = $this->createMock(RenewPasswordPresenterInterface::class);
    }

    /**
     * Test that an exception is thrown is the received payload is invalid.
     */
    public function testExceptionIsThrownWithInvalidPayload(): void
    {
        $controller = new RenewPasswordController();

        $invalidPayload = json_encode([
            'old_password' => 'titi'
        ]);
        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($invalidPayload);

        $this->expectException(\InvalidArgumentException::class);
        $controller($this->useCase, $this->request, $this->presenter, 'admin');
    }
}

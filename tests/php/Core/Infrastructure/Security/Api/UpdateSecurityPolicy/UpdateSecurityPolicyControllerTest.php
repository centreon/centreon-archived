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

namespace Tests\Core\Infrastructure\Api\UpdateSecurityPolicy;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicy;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Core\Infrastructure\Security\Api\UpdateSecurityPolicy\UpdateSecurityPolicyController;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyPresenterInterface;

class UpdateSecurityPolicyControllerTest extends TestCase
{
    /**
     * @var Request&\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var UpdateSecurityPolicyPresenterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenter;

    /**
     * @var UpdateSecurityPolicy&\PHPUnit\Framework\MockObject\MockObject
     */
    private $useCase;

    /**
     * @var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $container;

    public function setUp(): void
    {
        $this->presenter = $this->createMock(UpdateSecurityPolicyPresenterInterface::class);
        $this->useCase = $this->createMock(UpdateSecurityPolicy::class);

        $timezone = new \DateTimeZone('Europe/Paris');
        $adminContact = (new Contact())
        ->setId(1)
        ->setName('admin')
        ->setAdmin(true)
        ->setTimezone($timezone);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($adminContact);
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
                [$this->equalTo('security.token_storage')],
                [$this->equalTo('parameter_bag')]
            )
            ->willReturnOnConsecutiveCalls(
                $authorizationChecker,
                $tokenStorage,
                new class () {
                    public function get(): string
                    {
                        return __DIR__ . '/../../../../../';
                    }
                }
            );

        $this->request = $this->createMock(Request::class);
    }

    /**
     * Test that a correct exception is thrown when body is invalid.
     */
    public function testCreateUpdateSecurityPolicyRequestWithInvalidBody(): void
    {
        $controller = new UpdateSecurityPolicyController();
        $controller->setContainer($this->container);

        $invalidPayload = json_encode([
            'has_uppercase' => true
        ]);
        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($invalidPayload);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Bad Parameters');
        $controller($this->useCase, $this->request, $this->presenter);
    }
}

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

namespace Tests\Core\Infrastructure\Security\ProviderConfiguration\Local\Api\UpdateConfiguration;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Centreon\Domain\Contact\Contact;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\UpdateConfiguration\UpdateConfiguration;
use Core\Infrastructure\Security\ProviderConfiguration\Local\Api\UpdateConfiguration\UpdateConfigurationController;
use Core\Infrastructure\Security\ProviderConfiguration\Local\Api\Exception\ConfigurationException;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\UpdateConfiguration\{
    UpdateConfigurationPresenterInterface
};

class UpdateConfigurationControllerTest extends TestCase
{
    /**
     * @var Request&\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var UpdateConfigurationPresenterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenter;

    /**
     * @var UpdateConfiguration&\PHPUnit\Framework\MockObject\MockObject
     */
    private $useCase;

    /**
     * @var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $container;

    public function setUp(): void
    {
        $this->presenter = $this->createMock(UpdateConfigurationPresenterInterface::class);
        $this->useCase = $this->createMock(UpdateConfiguration::class);

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

        $this->request = $this->createMock(Request::class);
    }

    /**
     * Test that a correct exception is thrown when body is invalid.
     */
    public function testCreateUpdateConfigurationRequestWithInvalidBody(): void
    {
        $controller = new UpdateConfigurationController();
        $controller->setContainer($this->container);

        $invalidPayload = json_encode([
            'password_security_policy' => [
                'has_uppercase' => true,
            ],
        ]);
        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($invalidPayload);

        $this->expectException(ConfigurationException::class);
        $controller($this->useCase, $this->request, $this->presenter);
    }

    /**
     * Test that use is called when body is valid.
     */
    public function testCreateUpdateConfigurationRequestWithValidBody(): void
    {
        $controller = new UpdateConfigurationController();
        $controller->setContainer($this->container);

        $validPayload = json_encode([
            'password_security_policy' => [
                "password_min_length" => 12,
                "has_uppercase" => true,
                "has_lowercase" => true,
                "has_number" => true,
                "has_special_character" => true,
                "attempts" => 6,
                "blocking_duration" => 900,
                "password_expiration" => 7776000,
                "can_reuse_passwords" => true,
                "delay_before_new_password" => 3600,
            ],
        ]);

        $this->request
            ->expects($this->exactly(2))
            ->method('getContent')
            ->willReturn($validPayload);

        $this->useCase
            ->expects($this->once())
            ->method('__invoke');

        $controller($this->useCase, $this->request, $this->presenter);
    }
}

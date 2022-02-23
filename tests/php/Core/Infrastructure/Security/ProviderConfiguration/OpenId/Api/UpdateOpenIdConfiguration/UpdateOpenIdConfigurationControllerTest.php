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

namespace Tests\Core\Infrastructure\Security\ProviderConfiguration\OpenId\Api\UpdateOpenIdConfiguration;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Core\Infrastructure\Security\ProviderConfiguration\OpenId\Api\UpdateOpenIdConfiguration\{
    UpdateOpenIdConfigurationController
};
use Core\Application\Security\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration\{
    UpdateOpenIdConfiguration,
    UpdateOpenIdConfigurationPresenterInterface
};

class UpdateOpenIdConfigurationControllerTest extends TestCase
{

    /**
     * @var Request&\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var UpdateOpenIdConfigurationPresenterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenter;

    /**
     * @var UpdateOpenIdConfiguration&\PHPUnit\Framework\MockObject\MockObject
     */
    private $useCase;

    /**
     * @var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $container;

    public function setUp(): void
    {
        $this->presenter = $this->createMock(UpdateOpenIdConfigurationPresenterInterface::class);
        $this->useCase = $this->createMock(UpdateOpenIdConfiguration::class);

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
    public function testCreateUpdateOpenIdConfigurationRequestWithInvalidBody(): void
    {
        $controller = new UpdateOpenIdConfigurationController();
        $controller->setContainer($this->container);

        $invalidPayload = json_encode([]);
        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($invalidPayload);

        $this->expectException(\InvalidArgumentException::class);
        $controller($this->useCase, $this->request, $this->presenter);
    }

    /**
     * Test that the controller correctly send the request to the useCase with valid body.
     */
    public function testCreateUpdateOpenIdConfigurationRequestWithValidBody(): void
    {
        $controller = new UpdateOpenIdConfigurationController();
        $controller->setContainer($this->container);

        $validPayload = json_encode([
            'is_active' => true,
            'is_forced' => true,
            'trusted_client_addresses' => [],
            'blacklist_client_addresses' => [],
            'base_url' => 'http://127.0.0.1/auth/openid-connect',
            'authorization_endpoint' => '/authorization',
            'token_endpoint' => '/token',
            'introspection_token_endpoint' => '/introspect',
            'userinfo_endpoint' => '/userinfo',
            'endsession_endpoint' => '/logout',
            'connection_scope' => [],
            'login_claim' => 'preferred_username',
            'client_id' => 'MyCl1ientId',
            'client_secret' => 'MyCl1ientSuperSecr3tKey',
            'authentication_type' => 'client_secret_post',
            'verify_peer' => false
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

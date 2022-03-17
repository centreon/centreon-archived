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

namespace Tests\Centreon\Application\Controller;

use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Authentication\UseCase\Logout;
use Centreon\Domain\Authentication\UseCase\AuthenticateApi;
use Centreon\Application\Controller\AuthenticationController;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiResponse;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Centreon\Domain\Authentication\UseCase\FindProvidersConfigurations;
use Centreon\Domain\Authentication\UseCase\FindProvidersConfigurationsResponse;
use Security\Infrastructure\Authentication\API\Model_2110\ApiAuthenticationFactory;
use Security\Infrastructure\Authentication\API\Model_2110\ProvidersConfigurationsFactory;

/**
 * @package Tests\Centreon\Application\Controller
 */
class AuthenticationControllerTest extends TestCase
{
    /**
     * @var Contact
     */
    protected $adminContact;

    /**
     * @var AuthenticateApi|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $authenticateApi;

    /**
     * @var Logout|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $logout;

    /**
     * @var FindProvidersConfigurations|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $findProvidersConfigurations;

    /**
     * @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $container;

    /**
     * @var Request|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    protected function setUp(): void
    {
        $timezone = new \DateTimeZone('Europe/Paris');

        $this->adminContact = (new Contact())
            ->setId(1)
            ->setAlias('admin')
            ->setName('admin')
            ->setEmail('root@localhost')
            ->setAdmin(true)
            ->setTimezone($timezone);

        $this->authenticateApi = $this->createMock(AuthenticateApi::class);
        $this->logout = $this->createMock(Logout::class);
        $this->findProvidersConfigurations = $this->createMock(FindProvidersConfigurations::class);

        $this->container = $this->createMock(ContainerInterface::class);

        $this->request = $this->createMock(Request::class);
    }

    /**
     * test login
     */
    public function testLogin(): void
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode([
                'security' => [
                    'credentials' => [
                        'login' => 'admin',
                        'password' => 'centreon',
                    ],
                ],
            ]));

        $response = new AuthenticateApiResponse();
        $response->setApiAuthentication(
            $this->adminContact,
            'token'
        );

        $view = $authenticationController->login($this->request, $this->authenticateApi, $response);
        $this->assertEquals(
            View::create(ApiAuthenticationFactory::createFromResponse($response)),
            $view
        );
    }

    /**
     * test login with bad credentials
     */
    public function testLoginFailed(): void
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode([
                'security' => [
                    'credentials' => [
                        'login' => 'toto',
                        'password' => 'centreon',
                    ],
                ],
            ]));

        $response = new AuthenticateApiResponse();
        $response->setApiAuthentication(
            $this->adminContact,
            'token'
        );

        $this->authenticateApi
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(AuthenticationException::invalidCredentials());

        $view = $authenticationController->login($this->request, $this->authenticateApi, $response);

        $this->assertEquals(
            View::create(
                [
                    "code" => Response::HTTP_UNAUTHORIZED,
                    "message" => 'Invalid credentials',
                ],
                Response::HTTP_UNAUTHORIZED
            ),
            $view
        );
    }

    /**
     * test logout
     */
    public function testLogout(): void
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $this->request->headers = new class () {
            public function get(): string
            {
                return 'token';
            }
        };

        $view = $authenticationController->logout($this->request, $this->logout);

        $this->assertEquals(
            View::create([
                "message" => 'Successful logout'
            ]),
            $view
        );
    }

    /**
     * test logout with bad token
     */
    public function testLogoutFailed(): void
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $this->request->headers = new class () {
            public function get(): void
            {
                return;
            }
        };

        $view = $authenticationController->logout($this->request, $this->logout);

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

    /**
     * test findProvidersConfigurations
     */
    public function testFindProvidersConfigurations(): void
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $localProvider = new ProviderConfiguration(1, 'local', 'local', true, true, '/');

        $response = new FindProvidersConfigurationsResponse();
        $response->setProvidersConfigurations([$localProvider]);

        $view = $authenticationController->findProvidersConfigurations($this->findProvidersConfigurations, $response);

        $this->assertEquals(
            View::create(ProvidersConfigurationsFactory::createFromResponse($response)),
            $view
        );
    }
}

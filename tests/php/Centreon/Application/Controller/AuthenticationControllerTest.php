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

use Centreon\Domain\Contact\Contact;
use Centreon\Application\Controller\AuthenticationController;
use Centreon\Domain\Authentication\UseCase\AuthenticateApi;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiResponse;
use Centreon\Domain\Authentication\UseCase\Logout;
use Centreon\Domain\Authentication\UseCase\Redirect;
use Centreon\Domain\Authentication\UseCase\RedirectResponse;
use Centreon\Domain\Authentication\UseCase\FindProvidersConfigurations;
use Centreon\Domain\Authentication\UseCase\FindProvidersConfigurationsResponse;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Centreon\Domain\Authentication\UseCase\Authenticate;
use Centreon\Domain\Authentication\UseCase\AuthenticateResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

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
     * @var Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $redirect;

    /**
     * @var FindProvidersConfigurations|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $findProvidersConfigurations;

    /**
     * @var Authenticate|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $authenticate;

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
        $this->redirect = $this->createMock(Redirect::class);
        $this->findProvidersConfigurations = $this->createMock(FindProvidersConfigurations::class);
        $this->authenticate = $this->createMock(Authenticate::class);

        $this->container = $this->createMock(ContainerInterface::class);

        $this->request = $this->createMock(Request::class);
    }

    /**
     * test login
     */
    public function testLogin()
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

        $this->authenticateApi
            ->expects($this->once())
            ->method('execute')
            ->willReturn($response);

        $view = $authenticationController->login($this->request, $this->authenticateApi);

        $this->assertEquals(
            View::create($response->getApiAuthentication()),
            $view
        );
    }

    /**
     * test login with bad credentials
     */
    public function testLoginFailed()
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

        $this->authenticateApi
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException(new \Exception('wrong credentials')));

        $view = $authenticationController->login($this->request, $this->authenticateApi);

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
     * test logout
     */
    public function testLogout()
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $this->request->headers = new class () {
            public function get()
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
    public function testLogoutFailed()
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $this->request->headers = new class () {
            public function get()
            {
                return null;
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
     * test redirection from api
     */
    public function testRedirectionFromApi()
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $response = new RedirectResponse();
        $response->setRedirectionUri('/monitoring/resources');

        $this->redirect
            ->expects($this->once())
            ->method('execute')
            ->willReturn($response);

        $this->request->headers = new class () {
            public function get()
            {
                return 'application/json';
            }
        };

        $view = $authenticationController->redirection($this->request, $this->redirect);

        $this->assertEquals(
            View::create([
                'authentication_uri' => '/monitoring/resources'
            ]),
            $view
        );
    }

    /**
     * test redirection from web
     */
    public function testRedirectionFromWeb()
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $response = new RedirectResponse();
        $response->setRedirectionUri('/monitoring/resources');

        $this->redirect
            ->expects($this->once())
            ->method('execute')
            ->willReturn($response);

        $this->request->headers = new class () {
            public function get()
            {
                return 'text/html';
            }
        };

        $view = $authenticationController->redirection($this->request, $this->redirect);

        $expectedView = View::createRedirect('/monitoring/resources');
        $expectedView->setHeader('Content-Type', 'text/html');

        $this->assertEquals(
            $expectedView,
            $view
        );
    }

    /**
     * test findProvidersConfigurations
     */
    public function testFindProvidersConfigurations()
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $localProvider = (new ProviderConfiguration())
            ->setId(1)
            ->setType('local')
            ->setName('local')
            ->setCentreonBaseUri('/')
            ->setActive(true)
            ->setForced(true);

        $response = new FindProvidersConfigurationsResponse();
        $response->setProvidersConfigurations([$localProvider]);

        $this->findProvidersConfigurations
            ->expects($this->once())
            ->method('execute')
            ->willReturn($response);

        $view = $authenticationController->findProvidersConfigurations($this->findProvidersConfigurations);

        $this->assertEquals(
            View::create([
                [
                    'id' => 1,
                    'type' => 'local',
                    'name' => 'local',
                    'centreonBaseUri' => '/',
                    'isActive' => true,
                    'isForced' => true,
                    'authenticationUri' => '//authentication/providers/local',
                ],
            ]),
            $view
        );
    }

    /**
     * test authentication with get method
     */
    public function testAuthenticationWithGetMethod()
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $this->request
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('GET');

        $this->request->query = new class () {
            public function getIterator()
            {
                return [
                    'parameter1' => 'value1',
                ];
            }
        };

        $this->request->headers = new class () {
            public function get()
            {
                return 'application/json';
            }
        };

        $response = new AuthenticateResponse();
        $response->setRedirectionUri('/monitoring/resources');

        $this->authenticate
            ->expects($this->once())
            ->method('execute')
            ->willReturn($response);

        $view = $authenticationController->authentication($this->request, $this->authenticate, 'local');

        $this->assertEquals(
            View::create([
                'redirect_uri' => '/monitoring/resources'
            ]),
            $view
        );
    }

    /**
     * test authentication with post method
     */
    public function testAuthenticationWithPostMethod()
    {
        $authenticationController = new AuthenticationController();
        $authenticationController->setContainer($this->container);

        $this->request
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->request->request = new class () {
            public function getIterator()
            {
                return [
                    'parameter1' => 'value1',
                ];
            }
        };

        $this->request->headers = new class () {
            public function get()
            {
                return 'text/html';
            }
        };

        $response = new AuthenticateResponse();
        $response->setRedirectionUri('/monitoring/resources');

        $this->authenticate
            ->expects($this->once())
            ->method('execute')
            ->willReturn($response);

        $view = $authenticationController->authentication($this->request, $this->authenticate, 'local');

        $this->assertEquals(
            View::createRedirect(
                '/authentication/login',
                Response::HTTP_OK,
                ['content-type' => 'text/html']
            ),
            $view
        );
    }
}

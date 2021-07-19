<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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
use Centreon\Application\Controller\CheckController;
use Centreon\Application\Request\CheckRequest;
use Centreon\Domain\Check\Check;
use Centreon\Domain\Check\CheckException;
use Centreon\Domain\Monitoring\Resources;
use Centreon\Domain\Check\Interfaces\CheckServiceInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializerInterface;
use FOS\RestBundle\View\View;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class CheckControllerTest extends TestCase
{
    protected $adminContact;

    protected $goodJsonCheck;
    protected $badJsonCheck;
    protected $checkRequest;
    protected $check;
    protected $hostResource;
    protected $serviceResource;

    protected $checkService;

    protected $container;

    protected $request;
    protected $serializer;

    protected function setUp()
    {
        $timezone = new \DateTimeZone('Europe/Paris');

        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true)
            ->setTimezone($timezone);

        $goodJsonCheck = [
            'resources' => [
                [
                    'type' => 'host',
                    'id' => 1,
                    'parent' => null,
                ],
                [
                    'type' => 'service',
                    'id' => 1,
                    'parent' => [
                        'id' => 1,
                    ],
                ],
            ],
        ];
        $this->hostResource = (new Resources())
            ->setType($goodJsonCheck['resources'][0]['type'])
            ->setId($goodJsonCheck['resources'][0]['id']);
        $this->serviceResource = (new Resources())
            ->setType($goodJsonCheck['resources'][1]['type'])
            ->setId($goodJsonCheck['resources'][1]['id'])
            ->setParent($this->hostResource);

        $this->goodJsonCheck = json_encode($goodJsonCheck);

        $this->check = (new Check())
            ->setCheckTime(new \DateTime());
        $this->checkRequest = (new CheckRequest())
            ->setResources([$this->hostResource, $this->serviceResource])
            ->setCheck($this->check);

        $this->badJsonCheck = json_encode([
            'unknown_property' => 'unknown',
        ]);

        $this->checkService = $this->createMock(CheckServiceInterface::class);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($this->adminContact);
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
                    public function get()
                    {
                        return __DIR__ . '/../../../../../';
                    }
                }
            );

        $this->request = $this->createMock(Request::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * test checkResources with bad json format
     */
    public function testCheckResourcesBadJsonFormat()
    {
        $checkController = new CheckController($this->checkService);
        $checkController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn('[}');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Error when decoding sent data');
        $checkController->checkResources($this->request, $this->serializer);
    }

    /**
     * test checkResources with bad json properties
     */
    public function testCheckResourcesBadJsonProperties()
    {
        $checkController = new CheckController($this->checkService);
        $checkController->setContainer($this->container);

        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->badJsonCheck);
        $this->expectException(CheckException::class);
        $this->expectExceptionMessage('[resources] The property resources is required');
        $checkController->checkResources($this->request, $this->serializer);
    }

    /**
     * test checkResources which succeed
     */
    public function testCheckResourcesSuccess()
    {
        $this->checkService->expects($this->any())
            ->method('filterByContact')
            ->willReturn($this->checkService);
        $this->checkService->expects($this->any())
            ->method('checkResource')
            ->willReturn(null);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->willReturn($this->checkRequest);

        $checkController = new CheckController($this->checkService);
        $checkController->setContainer($this->container);

        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->goodJsonCheck);
        $view = $checkController->checkResources($this->request, $this->serializer);

        $this->assertEquals($view, View::create());
    }
}

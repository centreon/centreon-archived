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

use Centreon\Application\Controller\PlatformTopologyController;
use Centreon\Domain\PlatformTopology\PlatformTopology;
use Centreon\Domain\PlatformTopology\PlatformTopologyException;
use Centreon\Domain\PlatformTopology\PlatformTopologyConflictException;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class PlatformTopologyControllerTest extends TestCase
{
    protected $goodJsonPlatformTopology;
    protected $badJsonPlatformTopology;
    protected $platformTopology;

    protected $platformTopologyService;

    protected $container;

    protected $request;

    protected function setUp()
    {
        $goodJsonPlatformTopology = [
            'name' => 'poller1',
            'address' => '1.1.1.2',
            'type' => 'poller',
            'parent_address' => '1.1.1.1'
        ];

        $this->goodJsonPlatformTopology = json_encode($goodJsonPlatformTopology);

        $this->platformTopology = (new PlatformTopology())
            ->setName($goodJsonPlatformTopology['name'])
            ->setAddress($goodJsonPlatformTopology['address'])
            ->setType($goodJsonPlatformTopology['type'])
            ->setParentAddress($goodJsonPlatformTopology['parent_address']);

        $this->badJsonPlatformTopology = json_encode([
            'unknown_property' => 'unknown',
        ]);

        $this->platformTopologyService = $this->createMock(PlatformTopologyServiceInterface::class);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);
        $token = $this->createMock(TokenInterface::class);
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
                    public function get()
                    {
                        return __DIR__ . '/../../../../../';
                    }
                }
            );

        $this->request = $this->createMock(Request::class);
    }

    /**
     * test addPlatformToTopology with bad json format
     */
    public function testAddPlatformToTopologyBadJsonFormat()
    {
        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn('[}');
        $this->expectException(PlatformTopologyException::class);
        $this->expectExceptionMessage('Error when decoding sent data');
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $platformTopologyController->addPlatformToTopology($this->request);
    }

    /**
     * test addPlatformToTopology with conflict
     */
    public function testAddPlatformToTopologyConflict()
    {
        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->goodJsonPlatformTopology);

        $this->platformTopologyService->expects($this->any())
            ->method('addPlatformToTopology')
            ->will($this->throwException(new PlatformTopologyConflictException('conflict')));

        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $this->expectException(PlatformTopologyException::class);
        $this->expectExceptionMessage('conflict');
        $this->expectExceptionCode(Response::HTTP_CONFLICT);
        $platformTopologyController->addPlatformToTopology($this->request);
    }

    /**
     * test addPlatformToTopology which succeed
     */
    public function testAddPlatformToTopologySuccess()
    {
        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->goodJsonPlatformTopology);

        $this->platformTopologyService->expects($this->any())
            ->method('addPlatformToTopology')
            ->willReturn(null);

        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $view = $platformTopologyController->addPlatformToTopology($this->request);
        $this->assertEquals(
            $view,
            View::create(null, Response::HTTP_CREATED)
        );
    }
}

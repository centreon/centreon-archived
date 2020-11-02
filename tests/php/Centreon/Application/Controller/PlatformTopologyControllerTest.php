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

use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use FOS\RestBundle\Context\Context;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\PlatformTopology\PlatformTopology;
use Centreon\Domain\MonitoringServer\MonitoringServerService;
use Centreon\Domain\PlatformTopology\PlatformTopologyService;
use Centreon\Application\Controller\PlatformTopologyController;
use Centreon\Domain\PlatformTopology\PlatformTopologyException;
use Centreon\Application\PlatformTopology\PlatformTopologyHeliosFormat;
use Centreon\Domain\Broker\Interfaces\BrokerServiceInterface;
use Centreon\Domain\PlatformTopology\PlatformTopologyConflictException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\Exception\EntityNotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PlatformTopologyControllerTest extends TestCase
{
    protected $goodJsonPlatformTopology;
    protected $badJsonPlatformTopology;

    /**
     * @var PlatformTopology|null $platformTopology
     */
    protected $platformTopology;

    /**
     * @var PlatformTopology
     */
    protected $centralPlatform;

    /**
     * @var PlatformTopology
     */
    protected $pollerPlatform;

    /**
     * @var PlatformTopologyHeliosFormat
     */
    protected $centralHeliosFormat;

    /**
     * @var PlatformTopologyHeliosFormat
     */
    protected $pollerHeliosFormat;

    /**
     * @var PlatformTopologyService&MockObject $platformTopologyService
     */
    protected $platformTopologyService;

    /**
     * @var BrokerService&MockObject $platformTopologyService
     */
    protected $brokerService;

    protected $container;

    protected $request;

    protected function setUp(): void
    {
        $goodJsonPlatformTopology = [
            'name' => 'poller1',
            'hostname' => 'localhost.localdomain',
            'address' => '1.1.1.2',
            'type' => 'poller',
            'parent_address' => '1.1.1.1'
        ];

        $this->goodJsonPlatformTopology = json_encode($goodJsonPlatformTopology);

        $this->platformTopology = (new PlatformTopology())
            ->setName($goodJsonPlatformTopology['name'])
            ->setRelation('normal')
            ->setHostname($goodJsonPlatformTopology['hostname'])
            ->setAddress($goodJsonPlatformTopology['address'])
            ->setType($goodJsonPlatformTopology['type'])
            ->setParentAddress($goodJsonPlatformTopology['parent_address']);

        $this->centralPlatform = (new PlatformTopology())
            ->setId(1)
            ->setName('Central')
            ->setHostname('localhost.localdomain')
            ->setType(PlatformTopology::TYPE_CENTRAL)
            ->setAddress('192.168.1.1')
            ->setServerId(1)
            ->setRelation(PlatformTopology::NORMAL_RELATION);

        $this->pollerPlatform = (new PlatformTopology())
            ->setId(2)
            ->setName('Poller')
            ->setHostname('poller.poller1')
            ->setType(PlatformTopology::TYPE_POLLER)
            ->setAddress('192.168.1.2')
            ->setParentAddress('192.168.1.1')
            ->setParentId(1)
            ->setServerId(2)
            ->setRelation(PlatformTopology::NORMAL_RELATION);

        $this->centralHeliosFormat = new PlatformTopologyHeliosFormat($this->centralPlatform);
        $this->pollerHeliosFormat = new PlatformTopologyHeliosFormat($this->pollerPlatform);

        $this->badJsonPlatformTopology = json_encode([
            'unknown_property' => 'unknown',
        ]);

        $this->platformTopologyService = $this->createMock(PlatformTopologyServiceInterface::class);
        $this->brokerService = $this->createMock(BrokerServiceInterface::class);

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
                    public function get(): string
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
    public function testAddPlatformToTopologyBadJsonFormat(): void
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
     * @throws PlatformTopologyException
     */
    public function testAddPlatformToTopologyConflict(): void
    {
        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->goodJsonPlatformTopology);

        $this->platformTopologyService->expects($this->any())
            ->method('addPlatformToTopology')
            ->will($this->throwException(new PlatformTopologyConflictException('conflict')));

        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $view = $platformTopologyController->addPlatformToTopology($this->request);
        $this->assertEquals(
            $view,
            View::create(['message' => 'conflict'], Response::HTTP_CONFLICT)
        );
    }

    /**
     * test addPlatformToTopology with bad request
     * @throws PlatformTopologyException
     */
    public function testAddPlatformToTopologyBadRequest(): void
    {
        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->goodJsonPlatformTopology);

        $this->platformTopologyService->expects($this->any())
            ->method('addPlatformToTopology')
            ->will($this->throwException(new PlatformTopologyException('bad request')));

        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $view = $platformTopologyController->addPlatformToTopology($this->request);

        $this->assertEquals(
            $view,
            View::create(['message' => 'bad request'], Response::HTTP_BAD_REQUEST)
        );
    }

    /**
     * test addPlatformToTopology which succeed
     * @throws PlatformTopologyException
     */
    public function testAddPlatformToTopologySuccess(): void
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

    public function testGetPlatformTopologyHelios(): void
    {
        $completeTopology = [$this->centralPlatform, $this->pollerPlatform];
        $nodes[$this->centralHeliosFormat->getId()] = $this->centralHeliosFormat;
        $nodes[$this->pollerHeliosFormat->getId()] = $this->pollerHeliosFormat;

        $this->platformTopologyService->expects($this->any())
            ->method('getPlatformCompleteTopology')
            ->willReturn($completeTopology);

        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $view = $platformTopologyController->getPlatformTopologyHelios();

        $context = (new Context())->setGroups(PlatformTopologyController::SERIALIZER_GROUP_HELIOS);

        $this->assertEquals(
            $view,
            View::create(
                [
                    'graph' => [
                        'label' => 'centreon-topology',
                        'metadata' => [
                            'version' => '1.0.0'
                        ],
                        'nodes' => $nodes,
                        'edges' => [
                            [
                                "source" => "2",
                                "relation" => "normal",
                                "target" => "1"
                            ]
                        ]
                    ],
                ],
                Response::HTTP_OK
            )->setContext($context)
        );
    }

    public function testGetPlatformTopologyHeliosWithEmptyPlatform(): void
    {
        $this->platformTopologyService->expects($this->any())
            ->method('getPlatformCompleteTopology')
            ->will($this->throwException(new EntityNotFoundException('Platform Topology not found')));

        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $view = $platformTopologyController->getPlatformTopologyHelios();
        $this->assertEquals($view,View::create(['message' => 'Platform Topology not found'],Response::HTTP_NOT_FOUND));
    }

    public function testGetPlatformTopologyHeliosBadRequest(): void
    {
        $badPollerPlatform = (new PlatformTopology())
            ->setId(3)
            ->setName('Poller')
            ->setHostname('poller.poller1')
            ->setType(PlatformTopology::TYPE_POLLER)
            ->setAddress('192.168.1.2')
            ->setParentAddress('192.168.1.1')
            ->setParentId(1)
            ->setServerId(null)
            ->setRelation(PlatformTopology::NORMAL_RELATION);

        $this->platformTopologyService->expects($this->any())
            ->method('getPlatformCompleteTopology')
            ->will($this->throwException(new PlatformTopologyException(
                sprintf(
                    _("the '%s': '%s'@'%s' isn't fully registered, please finish installation using wizard"),
                    $badPollerPlatform->getType(),
                    $badPollerPlatform->getName(),
                    $badPollerPlatform->getAddress()
                )
            )));
            $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
            $platformTopologyController->setContainer($this->container);

            $view = $platformTopologyController->getPlatformTopologyHelios();
            $this->assertEquals($view, View::create(
                [
                    'message' => "the 'poller': 'Poller'@'192.168.1.2' isn't fully registered," .
                    " please finish installation using wizard"
                ],
                Response::HTTP_BAD_REQUEST
            ));
    }
}

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

namespace Tests\Centreon\Application\Controller\Monitoring;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Application\Controller\Monitoring\TimelineController;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Timeline\Interfaces\TimelineServiceInterface;
use Centreon\Domain\Monitoring\Timeline\TimelineEvent;
use Centreon\Domain\Monitoring\ResourceStatus;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class TimelineControllerTest extends TestCase
{
    protected $adminContact;

    protected $host;
    protected $service;

    protected $timelineEvent;

    protected $monitoringService;
    protected $timelineService;

    protected $container;

    protected $requestParameters;

    protected function setUp(): void
    {
        $timezone = new \DateTimeZone('Europe/Paris');

        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true)
            ->setTimezone($timezone);

        $this->host = (new Host())
            ->setId(1);

        $this->service = (new Service())
            ->setId(1);
        $this->service->setHost($this->host);

        $resourceStatus = (new ResourceStatus())
            ->setCode(0)
            ->setName('UP')
            ->setSeverityCode(4);
        $this->timelineEvent = (new TimelineEvent())
            ->setId(1)
            ->setType('event')
            ->setDate((new \Datetime())->setTimestamp(1581980400)->setTimezone($timezone))
            ->setContent('output')
            ->setStatus($resourceStatus)
            ->setTries(1);

        $this->monitoringService = $this->createMock(MonitoringServiceInterface::class);
        $this->timelineService = $this->createMock(TimelineServiceInterface::class);

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
                [$this->equalTo('security.token_storage')]
            )
            ->willReturnOnConsecutiveCalls(
                $authorizationChecker,
                $tokenStorage
            );

        $this->requestParameters = $this->createMock(RequestParametersInterface::class);
    }

    /**
     * test getHostTimeline
     */
    public function testGetHostTimeline()
    {
        $this->monitoringService->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);

        $this->timelineService->expects($this->once())
            ->method('findTimelineEventsByHost')
            ->willReturn([$this->timelineEvent]);

        $timelineController = new TimelineController($this->monitoringService, $this->timelineService);
        $timelineController->setContainer($this->container);

        $view = $timelineController->getHostTimeline(1, $this->requestParameters);

        $context = (new Context())
            ->setGroups(TimelineController::SERIALIZER_GROUPS_MAIN)
            ->enableMaxDepth();

        $this->assertEquals(
            $view,
            View::create([
                'result' => [$this->timelineEvent],
                'meta' => []
            ])->setContext($context)
        );
    }

    /**
     * test getServiceTimeline
     */
    public function testGetServiceTimeline()
    {
        $this->monitoringService->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);

        $this->monitoringService->expects($this->once())
            ->method('findOneService')
            ->willReturn($this->service);

        $this->timelineService->expects($this->once())
            ->method('findTimelineEventsByService')
            ->willReturn([$this->timelineEvent]);

        $timelineController = new TimelineController($this->monitoringService, $this->timelineService);
        $timelineController->setContainer($this->container);

        $view = $timelineController->getServiceTimeline(1, 1, $this->requestParameters);

        $context = (new Context())
            ->setGroups(TimelineController::SERIALIZER_GROUPS_MAIN)
            ->enableMaxDepth();

        $this->assertEquals(
            $view,
            View::create([
                'result' => [$this->timelineEvent],
                'meta' => []
            ])->setContext($context)
        );
    }
}

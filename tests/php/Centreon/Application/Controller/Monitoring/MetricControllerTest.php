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

namespace Tests\Centreon\Application\Controller\Monitoring\Metric;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Application\Controller\Monitoring\MetricController;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Monitoring\Metric\Interfaces\MetricServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use FOS\RestBundle\View\View;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class MetricControllerTest extends TestCase
{
    protected $adminContact;

    protected $host;
    protected $service;

    protected $metrics;
    protected $start;
    protected $end;

    protected $monitoringService;
    protected $metricService;

    protected $container;

    protected function setUp()
    {
        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $this->host = (new Host())
            ->setId(1);

        $this->service = (new Service())
            ->setId(1);
        $this->service->setHost($this->host);

        $this->metrics = [
            'global' => [],
            'metrics' => [],
            'times' => [],
        ];
        $this->status = [
            'critical' => [],
            'wraning' => [],
            'ok' => [],
            'unknown' => [],
        ];

        $this->start = new \DateTime('2020-02-18T00:00:00');
        $this->end = new \DateTime('2020-02-18T12:00:00');

        $this->metricService = $this->createMock(metricServiceInterface::class);
        $this->monitoringService = $this->createMock(MonitoringServiceInterface::class);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn('admin');
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->exactly(2))
            ->method('getToken')
            ->willReturn($token);

        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->expects($this->any())
            ->method('has')
            ->willReturn(true);
        $this->container->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('security.authorization_checker')],
                [$this->equalTo('security.token_storage')],
                [$this->equalTo('security.token_storage')]
            )
            ->willReturnOnConsecutiveCalls($authorizationChecker, $tokenStorage, $tokenStorage);
    }

    /**
     * test getServiceMetrics with not found host
     */
    public function testGetServiceMetricsNotFoundHost()
    {
        $this->monitoringService->expects($this->once())
            ->method('findOneHost')
            ->willReturn(null);

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Host 1 not found');
        $metricController->getServiceMetrics(1, 1, $this->start, $this->end);
    }

    /**
     * test getServiceMetrics with not found service
     */
    public function testGetServiceMetricsNotFoundService()
    {
        $this->monitoringService->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $this->monitoringService->expects($this->once())
            ->method('findOneService')
            ->willReturn(null);

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Service 1 not found');
        $metricController->getServiceMetrics(1, 1, $this->start, $this->end);
    }

    /**
     * test getServiceMetrics which succeed
     */
    public function testGetServiceMetricsSucceed()
    {
        $this->monitoringService->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $this->monitoringService->expects($this->once())
            ->method('findOneService')
            ->willReturn($this->service);
        $this->metricService->expects($this->once())
            ->method('filterByContact')
            ->willReturn($this->metricService);
        $this->metricService->expects($this->once())
            ->method('findMetricsByService')
            ->willReturn($this->metrics);

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $metrics = $metricController->getServiceMetrics(1, 1, $this->start, $this->end);
        $this->assertEquals(
            $metrics,
            View::create($this->metrics, null, [])
        );
    }

    /**
     * test getServiceStatus with not found host
     */
    public function testGetServiceStatusNotFoundHost()
    {
        $this->monitoringService->expects($this->once())
            ->method('findOneHost')
            ->willReturn(null);

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Host 1 not found');
        $metricController->getServiceStatus(1, 1, $this->start, $this->end);
    }

    /**
     * test getServiceStatus with not found service
     */
    public function testGetServiceStatusNotFoundService()
    {
        $this->monitoringService->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $this->monitoringService->expects($this->once())
            ->method('findOneService')
            ->willReturn(null);

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Service 1 not found');
        $metricController->getServiceStatus(1, 1, $this->start, $this->end);
    }

    /**
     * test getServiceStatus which succeed
     */
    public function testGetServiceStatusSucceed()
    {
        $this->monitoringService->expects($this->once())
            ->method('findOneHost')
            ->willReturn($this->host);
        $this->monitoringService->expects($this->once())
            ->method('findOneService')
            ->willReturn($this->service);
        $this->metricService->expects($this->once())
            ->method('filterByContact')
            ->willReturn($this->metricService);
        $this->metricService->expects($this->once())
            ->method('findStatusByService')
            ->willReturn($this->status);

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $status = $metricController->getServiceStatus(1, 1, $this->start, $this->end);
        $this->assertEquals(
            $status,
            View::create($this->status, null, [])
        );
    }
}

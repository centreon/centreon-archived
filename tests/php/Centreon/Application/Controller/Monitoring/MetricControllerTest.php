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
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

        $this->metrics = [
            'global' => [
                'start' => '1581980400',
                'end' => '1582023600',
            ],
            'metrics' => [],
            'times' => [],
        ];

        $this->normalizedPerformanceMetrics = [
            'global' => [
                'start' => (new \Datetime())->setTimestamp(1581980400)->setTimezone($timezone),
                'end' => (new \Datetime())->setTimestamp(1582023600)->setTimezone($timezone),
            ],
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

        $this->metricService = $this->createMock(MetricServiceInterface::class);
        $this->monitoringService = $this->createMock(MonitoringServiceInterface::class);

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
                [$this->equalTo('security.token_storage')],
                [$this->equalTo('security.token_storage')]
            )
            ->willReturnOnConsecutiveCalls($authorizationChecker, $tokenStorage, $tokenStorage, $tokenStorage);

        $this->requestParameters = $this->createMock(RequestParametersInterface::class);
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

    /**
     * test getServicePerformanceMetrics with not found host
     */
    public function testGetServicePerformanceMetricsNotFoundHost()
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
     * test getServicePerformanceMetrics with not found service
     */
    public function testGetServicePerformanceMetricsNotFoundService()
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
        $metricController->getServicePerformanceMetrics($this->requestParameters, 1, 1);
    }

    /**
     * test getServicePerformanceMetrics with wrong start date format
     */
    public function testGetServicePerformanceMetricsWrongStartDate()
    {
        $this->requestParameters->expects($this->exactly(2))
            ->method('getExtraParameter')
            ->willReturnOnConsecutiveCalls('wrong format', '2020-02-18T12:00:00');

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid date given for parameter "start".');
        $metricController->getServicePerformanceMetrics($this->requestParameters, 1, 1);
    }

    /**
     * test getServicePerformanceMetrics with wrong end date format
     */
    public function testGetServicePerformanceMetricsWrongEndDate()
    {
        $this->requestParameters->expects($this->exactly(2))
            ->method('getExtraParameter')
            ->willReturnOnConsecutiveCalls('2020-02-18T00:00:00', 'wrong format');

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid date given for parameter "end".');
        $metricController->getServicePerformanceMetrics($this->requestParameters, 1, 1);
    }

     /**
     * test getServicePerformanceMetrics with start date greater than end date
     */
    public function testGetServicePerformanceMetricsWrongDateRange()
    {
        $this->requestParameters->expects($this->exactly(2))
            ->method('getExtraParameter')
            ->willReturnOnConsecutiveCalls('2020-02-18T12:00:00', '2020-02-18T00:00:00');

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $this->expectException(\RangeException::class);
        $this->expectExceptionMessage('End date must be greater than start date.');
        $metricController->getServicePerformanceMetrics($this->requestParameters, 1, 1);
    }

    /**
     * test getServicePerformanceMetrics which succeed
     */
    public function testGetServicePerformanceMetricsSucceed()
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

        $metrics = $metricController->getServicePerformanceMetrics($this->requestParameters, 1, 1);
        $this->assertEquals(
            $metrics,
            View::create($this->normalizedPerformanceMetrics, null, [])
        );
    }

    /**
     * test getServiceStatusMetrics with not found host
     */
    public function testGetServiceStatusMetricsNotFoundHost()
    {
        $this->monitoringService->expects($this->once())
            ->method('findOneHost')
            ->willReturn(null);

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Host 1 not found');
        $metricController->getServiceStatusMetrics($this->requestParameters, 1, 1);
    }

    /**
     * test getServiceStatusMetrics with not found service
     */
    public function testGetServiceStatusMetricsNotFoundService()
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
        $metricController->getServiceStatusMetrics($this->requestParameters, 1, 1);
    }

    /**
     * test getServiceStatusMetrics which succeed
     */
    public function testGetServiceStatusMetricsSucceed()
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

        $status = $metricController->getServiceStatusMetrics($this->requestParameters, 1, 1);
        $this->assertEquals(
            $status,
            View::create($this->status, null, [])
        );
    }
}

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
use Centreon\Domain\Monitoring\Metric\Interfaces\MetricServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class MetricControllerTest extends TestCase
{
    protected $adminContact;
    protected $aclContact;

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

        $this->start = new \DateTime('2020-02-18T00:00:00');
        $this->end = new \DateTime('2020-02-18T12:00:00');

        $this->metricService = $this->createMock(metricServiceInterface::class);
        $this->monitoringService = $this->createMock(MonitoringServiceInterface::class);

        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->expects($this->once())
            ->method('has')
            ->with($this->equalTo('security.authorization_checker'))
            ->willReturn(true);
    }

    /**
     * test getServiceMetrics
     */
    public function testGetServiceMetrics()
    {
        $this->metricService->expects($this->once())
            ->method('findMetricsByService')
            ->willReturn($this->metrics);

        $metricController = new MetricController($this->metricService, $this->monitoringService);
        $metricController->setContainer($this->container);

        $metrics = $metricController->getServiceMetrics(1, 1, $this->start, $this->end);
        $this->assertEquals($metrics, $this->metrics);
    }
}

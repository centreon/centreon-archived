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

namespace Tests\Centreon\Domain\Monitoring\Metric;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\Metric\Interfaces\MetricRepositoryInterface;
use Centreon\Domain\Monitoring\Metric\MetricService;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use PHPUnit\Framework\TestCase;

class MetricServiceTest extends TestCase
{
    protected $adminContact;
    protected $aclContact;

    protected $host;
    protected $service;

    protected $metrics;
    protected $start;
    protected $end;

    protected $monitoringRepository;
    protected $metricRepository;
    protected $accessGroupRepository;

    protected function setUp(): void
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

        $this->metricRepository = $this->createMock(metricRepositoryInterface::class);
        $this->accessGroupRepository = $this->createMock(ReadAccessGroupRepositoryInterface::class);
        $this->monitoringRepository = $this->createMock(MonitoringRepositoryInterface::class);
    }

    /**
     * test findMetricsByService with admin user
     */
    public function testFindMetricsByService()
    {
        $this->metricRepository->expects($this->once())
            ->method('findMetricsByService')
            ->willReturn($this->metrics);

        $metricService = new MetricService(
            $this->monitoringRepository,
            $this->metricRepository,
            $this->accessGroupRepository
        );
        $metricService->filterByContact($this->adminContact);

        $metrics = $metricService->findMetricsByService($this->service, $this->start, $this->end);
        $this->assertEquals($metrics, $this->metrics);
    }

    /**
     * test findStatusByService with admin user
     */
    public function testFindStatusByService()
    {
        $this->metricRepository->expects($this->once())
            ->method('findStatusByService')
            ->willReturn($this->status);

        $metricService = new MetricService(
            $this->monitoringRepository,
            $this->metricRepository,
            $this->accessGroupRepository
        );
        $metricService->filterByContact($this->adminContact);

        $status = $metricService->findStatusByService($this->service, $this->start, $this->end);
        $this->assertEquals($status, $this->status);
    }
}

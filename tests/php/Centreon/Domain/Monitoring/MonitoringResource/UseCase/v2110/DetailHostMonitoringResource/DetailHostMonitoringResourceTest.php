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
declare(strict_types=1);

namespace Tests\Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\Monitoring\MonitoringResource\MonitoringResourceService;
use Tests\Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResourceTest;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource as UseCase;

/**
 * @package Tests\Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource
 */
class DetailHostMonitoringResourceTest extends TestCase
{
    /**
     * @var MonitoringResourceService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $monitoringResourceService;

    /**
     * @var MonitoringRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $monitoringRepository;

    /**
     * @var MonitoringResource
     */
    private $monitoringResource;

    protected function setUp(): void
    {
        $this->monitoringResourceService = $this->createMock(MonitoringResourceService::class);
        $this->monitoringRepository = $this->createMock(MonitoringRepositoryInterface::class);
        $this->monitoringResource = MonitoringResourceTest::createHostMonitoringResourceEntity();
    }

    /**
     * Test as admin user
     */
    public function testExecuteAsAdmin(): void
    {
        $this->monitoringResourceService
            ->expects($this->once())
            ->method('findAllWithoutAcl')
            ->willReturn([$this->monitoringResource]);

        $contact = new Contact();
        $contact->setAdmin(true);
        $detailHostMonitoringResource = new UseCase\DetailHostMonitoringResource(
            $this->monitoringResourceService,
            $contact,
            $this->monitoringRepository
        );
        $response = $detailHostMonitoringResource->execute(new ResourceFilter());
        $detailHostMonitoringResourceResponse = $response->getHostMonitoringResourceDetail();

        $this->assertIsArray($detailHostMonitoringResourceResponse);
    }

    /**
     * Test as non admin user
     */
    public function testExecuteAsNonAdmin(): void
    {
        $this->monitoringResourceService
            ->expects($this->once())
            ->method('findAllWithAcl')
            ->willReturn([$this->monitoringResource]);

        $contact = new Contact();
        $contact->setAdmin(false);
        $detailHostMonitoringResource = new UseCase\DetailHostMonitoringResource(
            $this->monitoringResourceService,
            $contact,
            $this->monitoringRepository
        );
        $response = $detailHostMonitoringResource->execute(new ResourceFilter());
        $detailHostMonitoringResourceResponse = $response->getHostMonitoringResourceDetail();

        $this->assertIsArray($detailHostMonitoringResourceResponse);
    }
}

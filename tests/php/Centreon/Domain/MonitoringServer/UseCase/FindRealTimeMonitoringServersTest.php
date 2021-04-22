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

namespace Tests\Centreon\Domain\MonitoringServer\UseCase;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\MonitoringServer\RealTimeMonitoringServerService;
use Centreon\Domain\MonitoringServer\UseCase\RealTimeMonitoringServer\FindRealTimeMonitoringServers;
use Tests\Centreon\Domain\MonitoringServer\Model\RealTimeMonitoringServerTest;

/**
 * @package Tests\Centreon\Domain\MonitoringServer\UseCase
 */
class FindRealTimeMonitoringServersTest extends TestCase
{
    /**
     * @var RealTimeMonitoringServerService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $realTimeMonitoringServerService;
    /**
     * @var \Centreon\Domain\MonitoringServer\Model\RealTimeMonitoringServer
     */
    private $realTimeMonitoringServer;

    protected function setUp(): void
    {
        $this->realTimeMonitoringServerService = $this->createMock(RealTimeMonitoringServerService::class);
        $this->realTimeMonitoringServer = RealTimeMonitoringServerTest::createEntity();
    }

    /**
     * Test as admin user
     */
    public function testExecuteAsAdmin(): void
    {
        $this->realTimeMonitoringServerService
            ->expects($this->once())
            ->method('findAllWithoutAcl')
            ->willReturn([$this->realTimeMonitoringServer]);

        $contact = new Contact();
        $contact->setAdmin(true);
        $findRealTimeMonitoringServers = new FindRealTimeMonitoringServers(
            $this->realTimeMonitoringServerService,
            $contact
        );
        $response = $findRealTimeMonitoringServers->execute();
        $this->assertCount(1, $response->getRealTimeMonitoringServers());
    }

    /**
    * Test as non admin user
    */
    public function testExecuteAsNonAdmin(): void
    {
        $this->realTimeMonitoringServerService
            ->expects($this->once())
            ->method('findAllWithAcl')
            ->willReturn([$this->realTimeMonitoringServer]);

        $contact = new Contact();
        $contact->setAdmin(false);
        $findRealTimeMonitoringServers = new FindRealTimeMonitoringServers(
            $this->realTimeMonitoringServerService,
            $contact
        );
        $response = $findRealTimeMonitoringServers->execute();
        $this->assertCount(1, $response->getRealTimeMonitoringServers());
    }
}

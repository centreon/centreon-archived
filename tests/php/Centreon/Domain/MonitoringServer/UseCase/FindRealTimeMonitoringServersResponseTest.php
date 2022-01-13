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
declare(strict_types=1);

namespace Tests\Centreon\Domain\MonitoringServer\UseCase;

use Centreon\Domain\MonitoringServer\UseCase\RealTimeMonitoringServer\FindRealTimeMonitoringServersResponse;
use Tests\Centreon\Domain\MonitoringServer\Model\RealTimeMonitoringServerTest;
use PHPUnit\Framework\TestCase;

/**
 * @package Tests\Centreon\Domain\MonitoringServer\UseCase
 */
class FindRealTimeMonitoringServersResponseTest extends TestCase
{
    /**
     * We test the transformation of an empty response into an array.
     */
    public function testEmptyResponse(): void
    {
        $response = new FindRealTimeMonitoringServersResponse();
        $realTimeMonitoringServers = $response->getRealTimeMonitoringServers();
        $this->assertCount(0, $realTimeMonitoringServers);
    }

    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $realTimeMonitoringServer = RealTimeMonitoringServerTest::createEntity();
        $response = new FindRealTimeMonitoringServersResponse();
        $response->setRealTimeMonitoringServers([$realTimeMonitoringServer]);
        $realTimeMonitoringServers = $response->getRealTimeMonitoringServers();
        $this->assertCount(1, $realTimeMonitoringServers);
        $this->assertEquals($realTimeMonitoringServer->getId(), $realTimeMonitoringServers[0]['id']);
        $this->assertEquals($realTimeMonitoringServer->getName(), $realTimeMonitoringServers[0]['name']);
        $this->assertEquals($realTimeMonitoringServer->getDescription(), $realTimeMonitoringServers[0]['description']);
        $this->assertEquals($realTimeMonitoringServer->getVersion(), $realTimeMonitoringServers[0]['version']);
        $this->assertEquals($realTimeMonitoringServer->isRunning(), $realTimeMonitoringServers[0]['is_running']);
        $this->assertEquals($realTimeMonitoringServer->getLastAlive(), $realTimeMonitoringServers[0]['last_alive']);
        $this->assertEquals($realTimeMonitoringServer->getAddress(), $realTimeMonitoringServers[0]['address']);
    }
}

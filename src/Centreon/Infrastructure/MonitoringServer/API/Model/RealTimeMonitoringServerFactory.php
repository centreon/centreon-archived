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

namespace Centreon\Infrastructure\MonitoringServer\API\Model;

use Centreon\Infrastructure\MonitoringServer\API\Model\RealTimeMonitoringServer;
use Centreon\Domain\MonitoringServer\UseCase\RealTimeMonitoringServer\FindRealTimeMonitoringServersResponse;

/**
 * This class is designed to create the hostCategoryV21 entity
 *
 * @package Centreon\Infrastructure\HostConfiguration\API\Model\HostCategory
 */
class RealTimeMonitoringServerFactory
{
    /**
     * @param FindRealTimeMonitoringServersResponse $response
     * @return RealTimeMonitoringServer[]
     */
    public static function createFromResponse(FindRealTimeMonitoringServersResponse $response): array
    {
        $realTimeMonitoringServers = [];
        foreach ($response->getRealTimeMonitoringServers() as $realTimeMonitoringServer) {
            $newRealTimeMonitoringServer = new RealTimeMonitoringServer();
            $newRealTimeMonitoringServer->id = $realTimeMonitoringServer['id'];
            $newRealTimeMonitoringServer->name = $realTimeMonitoringServer['name'];
            $newRealTimeMonitoringServer->address = $realTimeMonitoringServer['address'];
            $newRealTimeMonitoringServer->description = $realTimeMonitoringServer['description'];
            $newRealTimeMonitoringServer->version = $realTimeMonitoringServer['version'];
            $newRealTimeMonitoringServer->isRunning = $realTimeMonitoringServer['is_running'];
            $newRealTimeMonitoringServer->lastAlive = $realTimeMonitoringServer['last_alive'];

            $realTimeMonitoringServers[] = $newRealTimeMonitoringServer;
        }
        return $realTimeMonitoringServers;
    }
}

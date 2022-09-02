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

namespace Centreon\Domain\MonitoringServer\UseCase\RealTimeMonitoringServer;

use Centreon\Domain\MonitoringServer\Model\RealTimeMonitoringServer;

/**
 * This class is a DTO for the FindRealTimeMonitoringServers use case.
 *
 * @package Centreon\Domain\MonitoringServer\UseCase\RealTimeMonitoringServer
 */
class FindRealTimeMonitoringServersResponse
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private $realTimeMonitoringServers = [];

    /**
     * @param RealTimeMonitoringServer[] $realTimeMonitoringServers
     */
    public function setRealTimeMonitoringServers(array $realTimeMonitoringServers): void
    {
        foreach ($realTimeMonitoringServers as $realTimeMonitoringServer) {
            $this->realTimeMonitoringServers[] = [
                'id' => $realTimeMonitoringServer->getId(),
                'name' => $realTimeMonitoringServer->getName(),
                'address' => $realTimeMonitoringServer->getAddress(),
                'description' => $realTimeMonitoringServer->getDescription(),
                'last_alive' => $realTimeMonitoringServer->getLastAlive(),
                'is_running' => $realTimeMonitoringServer->isRunning(),
                'version' => $realTimeMonitoringServer->getVersion(),
            ];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRealTimeMonitoringServers(): array
    {
        return $this->realTimeMonitoringServers;
    }
}

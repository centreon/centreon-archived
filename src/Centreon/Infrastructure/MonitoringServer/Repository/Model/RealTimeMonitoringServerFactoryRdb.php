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

namespace Centreon\Infrastructure\MonitoringServer\Repository\Model;

use Centreon\Domain\MonitoringServer\Model\RealTimeMonitoringServer;

/**
 * This class is designed to provide a way to create the HostCategory entity from the database.
 *
 * @package Centreon\Infrastructure\HostConfiguration\Repository\Model
 */
class RealTimeMonitoringServerFactoryRdb
{
    /**
     * Create a RealTimeMonitoringServer entity from database data.
     *
     * @param array<string, mixed> $data
     * @return RealTimeMonitoringServer
     * @throws \Assert\AssertionFailedException
     */
    public static function create(array $data): RealTimeMonitoringServer
    {
        return (new RealTimeMonitoringServer((int) $data['instance_id'], $data['name']))
            ->setRunning((bool) $data['running'])
            ->setLastAlive((int) $data['last_alive'])
            ->setVersion($data['version'])
            ->setDescription($data['description'])
            ->setAddress($data['address']);
    }
}

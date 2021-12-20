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

namespace Core\Infrastructure\RealTime\Repository\Host;

use Core\Domain\RealTime\Model\Host;
use Core\Infrastructure\RealTime\Repository\Icon\DbIconFactory;
use Core\Infrastructure\RealTime\Repository\Host\DbHostStatusFactory;
use Core\Infrastructure\Common\Repository\DbFactoryUtilitiesTrait;

class DbHostFactory
{
    use DbFactoryUtilitiesTrait;

    /**
     * @param array<string, mixed> $data
     * @return Host
     */
    public static function createFromRecord(array $data): Host
    {
        $host = (new Host(
            (int) $data['host_id'],
            $data['name'],
            $data['address'],
            $data['monitoring_server_name'],
            DbHostStatusFactory::createFromRecord($data)
        ));

        $host->setTimezone($data['timezone'])
            ->setPerformanceData($data['performance_data'])
            ->setOutput($data['output'])
            ->setCommandLine($data['command_line'])
            ->setIsFlapping((int) $data['flapping'] === 1)
            ->setIsAcknowledged((int) $data['acknowledged'] === 1)
            ->setIsInDowntime((int) $data['in_downtime'] === 1)
            ->setPassiveChecks((int) $data['passive_checks'] === 1)
            ->setActiveChecks((int) $data['active_checks'] === 1)
            ->setLatency(self::getFloatOrNull($data['latency']))
            ->setExecutionTime(self::getFloatOrNull($data['execution_time']))
            ->setStatusChangePercentage(self::getFloatOrNull($data['status_change_percentage']))
            ->setSeverityLevel(self::getIntOrNull($data['severity_level']))
            ->setNotificationNumber(self::getIntOrNull($data['notification_number']))
            ->setLastStatusChange(self::createDateTimeFromTimestamp((int) $data['last_status_change']))
            ->setLastNotification(self::createDateTimeFromTimestamp((int) $data['last_notification']))
            ->setLastCheck(self::createDateTimeFromTimestamp((int) $data['last_check']))
            ->setLastTimeUp(self::createDateTimeFromTimestamp((int) $data['last_time_up']))
            ->setMaxCheckAttempts(self::getIntOrNull($data['max_check_attempts']))
            ->setCheckAttemps(self::getIntOrNull($data['check_attempt']));

        $nextCheck = self::createDateTimeFromTimestamp(
            (int) $data['active_checks'] === 1 ? (int) $data['next_check'] : null
        );

        $host->setNextCheck($nextCheck);
        $host->setIcon(DbIconFactory::createFromRecord($data));

        return $host;
    }
}

<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\RealTime\Repository\MetaService;

use Core\Domain\RealTime\Model\MetaService;
use Core\Infrastructure\Common\Repository\DbFactoryUtilitiesTrait;
use Core\Infrastructure\RealTime\Repository\Service\DbServiceStatusFactory;

class DbMetaServiceFactory
{
    use DbFactoryUtilitiesTrait;

    /**
     * @param array<string, mixed> $data
     * @return MetaService
     */
    public static function createFromRecord(array $data): MetaService
    {
        $metaService = new MetaService(
            (int) $data['id'],
            (int) $data['host_id'],
            (int) $data['service_id'],
            $data['name'],
            $data['monitoring_server_name'],
            DbServiceStatusFactory::createFromRecord($data)
        );

        $metaService->setPerformanceData($data['performance_data'])
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
            ->setNotificationEnabled((int) $data['notify'] === 1)
            ->setNotificationNumber(self::getIntOrNull($data['notification_number']))
            ->setLastStatusChange(self::createDateTimeFromTimestamp((int) $data['last_status_change']))
            ->setLastNotification(self::createDateTimeFromTimestamp((int) $data['last_notification']))
            ->setLastCheck(self::createDateTimeFromTimestamp((int) $data['last_check']))
            ->setLastTimeOk(self::createDateTimeFromTimestamp((int) $data['last_time_ok']))
            ->setMaxCheckAttempts(self::getIntOrNull($data['max_check_attempts']))
            ->setCheckAttempts(self::getIntOrNull($data['check_attempt']))
            ->setHasGraphData((int) $data['has_graph_data'] === 1);

        $nextCheck = self::createDateTimeFromTimestamp(
            (int) $data['active_checks'] === 1 ? (int) $data['next_check'] : null
        );

        $metaService->setNextCheck($nextCheck);

        return $metaService;
    }
}

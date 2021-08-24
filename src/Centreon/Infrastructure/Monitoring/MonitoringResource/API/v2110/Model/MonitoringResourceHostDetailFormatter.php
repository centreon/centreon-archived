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

namespace Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model;

use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource as DetailHost;
use stdClass;

/**
 * This class is designed to create the MonitoringResourceV2110 entity
 *
 * @package Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model
 */
class MonitoringResourceHostDetailFormatter
{
    /**
     * @param DetailHost\DetailHostMonitoringResourceResponse $response
     * @param array<string, mixed> $responseLinks
     * @return \stdClass
     */
    public static function createFromResponse(
        DetailHost\DetailHostMonitoringResourceResponse $response,
        array $responseLinks = []
    ): \stdClass {
        $hostMonitoringResourceDetail = $response->getHostMonitoringResourceDetail();
        $newMonitoringResource = self::createEmptyClass();

        $newMonitoringResource->uuid = $hostMonitoringResourceDetail['uuid'];
        $newMonitoringResource->short_type = $hostMonitoringResourceDetail['short_type'];
        $newMonitoringResource->id = $hostMonitoringResourceDetail['id'];
        $newMonitoringResource->name = $hostMonitoringResourceDetail['name'];
        $newMonitoringResource->type = $hostMonitoringResourceDetail['type'];
        $newMonitoringResource->alias = $hostMonitoringResourceDetail['alias'];
        $newMonitoringResource->fqdn = $hostMonitoringResourceDetail['fqdn'];
        $newMonitoringResource->acknowledged = $hostMonitoringResourceDetail['acknowledged'];
        $newMonitoringResource->active_checks = $hostMonitoringResourceDetail['active_checks'];
        $newMonitoringResource->flapping = $hostMonitoringResourceDetail['flapping'];
        $newMonitoringResource->icon = $hostMonitoringResourceDetail['icon'];
        $newMonitoringResource->in_downtime = $hostMonitoringResourceDetail['in_downtime'];
        $newMonitoringResource->information = $hostMonitoringResourceDetail['information'];
        $newMonitoringResource->last_check = $hostMonitoringResourceDetail['last_check'];
        $newMonitoringResource->last_status_change = $hostMonitoringResourceDetail['last_status_change'];
        $newMonitoringResource->monitoring_server_name = $hostMonitoringResourceDetail['monitoring_server_name'];
        $newMonitoringResource->notification_enabled = $hostMonitoringResourceDetail['notification_enabled'];
        $newMonitoringResource->parent = $hostMonitoringResourceDetail['parent'];
        $newMonitoringResource->passive_checks = $hostMonitoringResourceDetail['passive_checks'];
        $newMonitoringResource->performance_data = $hostMonitoringResourceDetail['performance_data'];
        $newMonitoringResource->severity_level = $hostMonitoringResourceDetail['severity_level'];
        $newMonitoringResource->status = $hostMonitoringResourceDetail['status'];
        $newMonitoringResource->tries = $hostMonitoringResourceDetail['tries'];
        $newMonitoringResource->duration = $hostMonitoringResourceDetail['duration'];
        $newMonitoringResource->links = array_merge(
            $hostMonitoringResourceDetail['links'],
            $responseLinks
        );
        $newMonitoringResource->groups = $hostMonitoringResourceDetail['groups'];
        $newMonitoringResource->command_line = $hostMonitoringResourceDetail['command_line'];
        $newMonitoringResource->timezone = $hostMonitoringResourceDetail['timezone'];
        $newMonitoringResource->downtimes = $hostMonitoringResourceDetail['downtimes'];
        $newMonitoringResource->acknowledgement = $hostMonitoringResourceDetail['acknowledgement'];
        $newMonitoringResource->execution_time = $hostMonitoringResourceDetail['execution_time'];
        $newMonitoringResource->last_notification = $hostMonitoringResourceDetail['last_notification'];
        $newMonitoringResource->latency = $hostMonitoringResourceDetail['latency'];
        $newMonitoringResource->next_check = $hostMonitoringResourceDetail['next_check'];
        $newMonitoringResource->notification_number = $hostMonitoringResourceDetail['notification_number'];
        $newMonitoringResource->percent_state_change = $hostMonitoringResourceDetail['percent_state_change'];

        return $newMonitoringResource;
    }

    /**
     * @return \stdClass
     */
    private static function createEmptyClass(): \stdClass
    {
        return new class extends \stdClass
        {
            /**
             * @var string
             */
            public $uuid;

            /**
             * @var int
             */
            public $id;

            /**
             * @var string
             */
            public $name;

            /**
             * @var string
             */
            public $type;

            /**
             * @var string
             */
            public $short_type;

            /**
             * @var string|null
             */
            public $alias;

            /**
             * @var string|null
             */
            public $fqdn;

            /**
             * @var bool
             */
            public $acknowledged;

            /**
             * @var bool
             */
            public $active_checks;

            /**
             * @var string|null
             */
            public $duration;

            /**
             * @var bool|null
             */
            public $flapping;

            /**
             * @var array<string, string>|null
             */
            public $icon;

            /**
             * @var bool
             */
            public $in_downtime;

            /**
             * @var string|null
             */
            public $information;

            /**
             * @var string|null
             */
            public $last_check;

            /**
             * @var \DateTime|null
             */

            /**
             * @var \DateTime|null
             */
            public $last_status_change;

            /**
             * @var string|null
             */
            public $monitoring_server_name;

            /**
             * @var bool
             */
            public $notification_enabled;

            /**
             * @var array<string,mixed>
             */
            public $parent;

            /**
             * @var bool|null
             */
            public $passive_checks;

            /**
             * @var string|null
             */
            public $performance_data;

            /**
             * @var int|null
             */
            public $severity_level;

            /**
             * @var array<string, mixed>
             */
            public $status;

            /**
             * @var string|null
             */
            public $tries;

            /**
             * @var array<string, array<string, mixed>>
             */
            public $links;

            /**
             * @var array<int, array<string, string>>
             */
            public $groups;

            /**
             * @var string|null
             */
            public $commandLine;

            /**
             * @var string|null
             */
            public $timezone;

            /**
             * @var array<int, array<string, mixed>>
             */
            public $downtimes;

            /**
             * @var array<string, mixed>>
             */
            public $acknowledgement;

            /**
             * @var double|null
             */
            public $execution_time;

            /**
             * @var \DateTime|null
             */
            public $last_notification;

            /**
             * @var float|null
             */
            public $latency;

            /**
             * @var \DateTime|null
             */
            public $next_check;

            /**
             * @var int|null
             */
            public $notification_number;

            /**
             * @var double|null
             */
            public $percent_state_change;
        };
    }
}

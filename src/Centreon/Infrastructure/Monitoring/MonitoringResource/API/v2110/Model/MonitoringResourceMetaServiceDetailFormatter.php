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

use stdClass;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailMetaServiceMonitoringResource\DetailMetaServiceMonitoringResourceResponse;

/**
 * This class is designed to create the MonitoringResourceV2110 entity
 *
 * @package Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model
 */
class MonitoringResourceMetaServiceDetailFormatter
{
    /**
     * @param DetailMetaServiceMonitoringResourceResponse $response
     * @param array<string, mixed> $responseLinks
     * @return \stdClass
     */
    public static function createFromResponse(
        DetailMetaServiceMonitoringResourceResponse $response,
        array $responseLinks = []
    ): \stdClass {
        $metaServiceMonitoringResourceDetail = $response->getMetaServiceMonitoringResourceDetail();
        $newMonitoringResource = self::createEmptyClass();

        $newMonitoringResource->uuid = $metaServiceMonitoringResourceDetail['uuid'];
        $newMonitoringResource->short_type = $metaServiceMonitoringResourceDetail['short_type'];
        $newMonitoringResource->id = $metaServiceMonitoringResourceDetail['id'];
        $newMonitoringResource->name = $metaServiceMonitoringResourceDetail['name'];
        $newMonitoringResource->type = $metaServiceMonitoringResourceDetail['type'];
        $newMonitoringResource->alias = $metaServiceMonitoringResourceDetail['alias'];
        $newMonitoringResource->fqdn = $metaServiceMonitoringResourceDetail['fqdn'];
        $newMonitoringResource->acknowledged = $metaServiceMonitoringResourceDetail['acknowledged'];
        $newMonitoringResource->active_checks = $metaServiceMonitoringResourceDetail['active_checks'];
        $newMonitoringResource->flapping = $metaServiceMonitoringResourceDetail['flapping'];
        $newMonitoringResource->icon = $metaServiceMonitoringResourceDetail['icon'];
        $newMonitoringResource->in_downtime = $metaServiceMonitoringResourceDetail['in_downtime'];
        $newMonitoringResource->information = $metaServiceMonitoringResourceDetail['information'];
        $newMonitoringResource->last_check = $metaServiceMonitoringResourceDetail['last_check'];
        $newMonitoringResource->last_status_change = $metaServiceMonitoringResourceDetail['last_status_change'];
        $newMonitoringResource->monitoring_server_name = $metaServiceMonitoringResourceDetail['monitoring_server_name'];
        $newMonitoringResource->notification_enabled = $metaServiceMonitoringResourceDetail['notification_enabled'];
        $newMonitoringResource->parent = $metaServiceMonitoringResourceDetail['parent'];
        $newMonitoringResource->passive_checks = $metaServiceMonitoringResourceDetail['passive_checks'];
        $newMonitoringResource->performance_data = $metaServiceMonitoringResourceDetail['performance_data'];
        $newMonitoringResource->severity_level = $metaServiceMonitoringResourceDetail['severity_level'];
        $newMonitoringResource->status = $metaServiceMonitoringResourceDetail['status'];
        $newMonitoringResource->tries = $metaServiceMonitoringResourceDetail['tries'];
        $newMonitoringResource->duration = $metaServiceMonitoringResourceDetail['duration'];
        $newMonitoringResource->links = array_merge(
            $metaServiceMonitoringResourceDetail['links'],
            $responseLinks
        );
        $newMonitoringResource->groups = $metaServiceMonitoringResourceDetail['groups'];
        $newMonitoringResource->command_line = $metaServiceMonitoringResourceDetail['command_line'];
        $newMonitoringResource->timezone = $metaServiceMonitoringResourceDetail['timezone'];
        $newMonitoringResource->downtimes = $metaServiceMonitoringResourceDetail['downtimes'];
        $newMonitoringResource->acknowledgement = $metaServiceMonitoringResourceDetail['acknowledgement'];
        $newMonitoringResource->execution_time = $metaServiceMonitoringResourceDetail['execution_time'];
        $newMonitoringResource->last_notification = $metaServiceMonitoringResourceDetail['last_notification'];
        $newMonitoringResource->latency = $metaServiceMonitoringResourceDetail['latency'];
        $newMonitoringResource->next_check = $metaServiceMonitoringResourceDetail['next_check'];
        $newMonitoringResource->notification_number = $metaServiceMonitoringResourceDetail['notification_number'];
        $newMonitoringResource->percent_state_change = $metaServiceMonitoringResourceDetail['percent_state_change'];
        $newMonitoringResource->calculation_type = $metaServiceMonitoringResourceDetail['calculation_type'];

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
            public $command_line;

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

            /**
             * @var string
             */
            public $calculation_type;
        };
    }
}

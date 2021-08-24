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

use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailServiceMonitoringResource as DetailService;
use stdClass;

/**
 * This class is designed to create the MonitoringResourceV2110 entity
 *
 * @package Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model
 */
class MonitoringResourceServiceDetailFormatter
{
    /**
     * @param DetailService\DetailServiceMonitoringResourceResponse $response
     * @param array<string, mixed> $responseLinks
     * @return \stdClass
     */
    public static function createFromResponse(
        DetailService\DetailServiceMonitoringResourceResponse $response,
        array $responseLinks = []
    ): \stdClass {
        $parentLinks = [];
        if (isset($responseLinks['parent'])) {
            $parentLinks = $responseLinks['parent'];
            unset($responseLinks['parent']);
        }
        $serviceMonitoringResourceDetail = $response->getServiceMonitoringResourceDetail();
        $newMonitoringResource = self::createEmptyClass();

        if (!empty($serviceMonitoringResourceDetail['parent']) && !empty($parentLinks)) {
            $serviceMonitoringResourceDetail['parent']['links'] = $parentLinks;
        }

        $newMonitoringResource->uuid = $serviceMonitoringResourceDetail['uuid'];
        $newMonitoringResource->short_type = $serviceMonitoringResourceDetail['short_type'];
        $newMonitoringResource->id = $serviceMonitoringResourceDetail['id'];
        $newMonitoringResource->name = $serviceMonitoringResourceDetail['name'];
        $newMonitoringResource->type = $serviceMonitoringResourceDetail['type'];
        $newMonitoringResource->alias = $serviceMonitoringResourceDetail['alias'];
        $newMonitoringResource->fqdn = $serviceMonitoringResourceDetail['fqdn'];
        $newMonitoringResource->acknowledged = $serviceMonitoringResourceDetail['acknowledged'];
        $newMonitoringResource->active_checks = $serviceMonitoringResourceDetail['active_checks'];
        $newMonitoringResource->flapping = $serviceMonitoringResourceDetail['flapping'];
        $newMonitoringResource->icon = $serviceMonitoringResourceDetail['icon'];
        $newMonitoringResource->in_downtime = $serviceMonitoringResourceDetail['in_downtime'];
        $newMonitoringResource->information = $serviceMonitoringResourceDetail['information'];
        $newMonitoringResource->last_check = $serviceMonitoringResourceDetail['last_check'];
        $newMonitoringResource->last_status_change = $serviceMonitoringResourceDetail['last_status_change'];
        $newMonitoringResource->monitoring_server_name = $serviceMonitoringResourceDetail['monitoring_server_name'];
        $newMonitoringResource->notification_enabled = $serviceMonitoringResourceDetail['notification_enabled'];
        $newMonitoringResource->parent = $serviceMonitoringResourceDetail['parent'];
        $newMonitoringResource->passive_checks = $serviceMonitoringResourceDetail['passive_checks'];
        $newMonitoringResource->performance_data = $serviceMonitoringResourceDetail['performance_data'];
        $newMonitoringResource->severity_level = $serviceMonitoringResourceDetail['severity_level'];
        $newMonitoringResource->status = $serviceMonitoringResourceDetail['status'];
        $newMonitoringResource->tries = $serviceMonitoringResourceDetail['tries'];
        $newMonitoringResource->duration = $serviceMonitoringResourceDetail['duration'];
        $newMonitoringResource->links = array_merge(
            $serviceMonitoringResourceDetail['links'],
            $responseLinks
        );
        $newMonitoringResource->groups = $serviceMonitoringResourceDetail['groups'];
        $newMonitoringResource->command_line = $serviceMonitoringResourceDetail['command_line'];
        $newMonitoringResource->timezone = $serviceMonitoringResourceDetail['timezone'];
        $newMonitoringResource->downtimes = $serviceMonitoringResourceDetail['downtimes'];
        $newMonitoringResource->acknowledgement = $serviceMonitoringResourceDetail['acknowledgement'];
        $newMonitoringResource->execution_time = $serviceMonitoringResourceDetail['execution_time'];
        $newMonitoringResource->last_notification = $serviceMonitoringResourceDetail['last_notification'];
        $newMonitoringResource->latency = $serviceMonitoringResourceDetail['latency'];
        $newMonitoringResource->next_check = $serviceMonitoringResourceDetail['next_check'];
        $newMonitoringResource->notification_number = $serviceMonitoringResourceDetail['notification_number'];
        $newMonitoringResource->percent_state_change = $serviceMonitoringResourceDetail['percent_state_change'];

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
        };
    }
}

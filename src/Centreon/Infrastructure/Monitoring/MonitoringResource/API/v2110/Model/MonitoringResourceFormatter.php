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

use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\FindMonitoringResources\FindMonitoringResourcesResponse;

/**
 * This class is designed to create the MonitoringResourceV2110 entity
 *
 * @package Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Model
 */
class MonitoringResourceFormatter
{
    /**
     * @param FindMonitoringResourcesResponse $response
     * @param array<int, array<string, array<string, array<string, string>|string>>> $responseLinks
     * @return \stdClass[]
     */
    public static function createFromResponse(
        FindMonitoringResourcesResponse $response,
        array $responseLinks = []
    ): array {
        $monitoringResources = [];
        foreach ($response->getMonitoringResources() as $index => $monitoringResource) {
            $parentLinks = [];
            if (isset($responseLinks[$index]['parent'])) {
                $parentLinks = $responseLinks[$index]['parent'];
                unset($responseLinks[$index]['parent']);
            }
            $newMonitoringResource = self::createEmptyClass();

            if (!empty($monitoringResource['parent']) && !empty($parentLinks)) {
                $monitoringResource['parent']['links'] = $parentLinks;
            }

            $newMonitoringResource->uuid = $monitoringResource['uuid'];
            $newMonitoringResource->short_type = $monitoringResource['short_type'];
            $newMonitoringResource->id = $monitoringResource['id'];
            $newMonitoringResource->name = $monitoringResource['name'];
            $newMonitoringResource->type = $monitoringResource['type'];
            $newMonitoringResource->alias = $monitoringResource['alias'];
            $newMonitoringResource->fqdn = $monitoringResource['fqdn'];
            $newMonitoringResource->acknowledged = $monitoringResource['acknowledged'];
            $newMonitoringResource->active_checks = $monitoringResource['active_checks'];
            $newMonitoringResource->flapping = $monitoringResource['flapping'];
            $newMonitoringResource->icon = $monitoringResource['icon'];
            $newMonitoringResource->in_downtime = $monitoringResource['in_downtime'];
            $newMonitoringResource->information = $monitoringResource['information'];
            $newMonitoringResource->last_check = $monitoringResource['last_check'];
            $newMonitoringResource->last_status_change = $monitoringResource['last_status_change'];
            $newMonitoringResource->monitoring_server_name = $monitoringResource['monitoring_server_name'];
            $newMonitoringResource->notification_enabled = $monitoringResource['notification_enabled'];
            $newMonitoringResource->parent = $monitoringResource['parent'];
            $newMonitoringResource->passive_checks = $monitoringResource['passive_checks'];
            $newMonitoringResource->performance_data = $monitoringResource['performance_data'];
            $newMonitoringResource->severity_level = $monitoringResource['severity_level'];
            $newMonitoringResource->status = $monitoringResource['status'];
            $newMonitoringResource->tries = $monitoringResource['tries'];
            $newMonitoringResource->duration = $monitoringResource['duration'];
            $newMonitoringResource->links = array_merge(
                $monitoringResource['links'],
                isset($responseLinks[$index]) ? $responseLinks[$index] : []
            );

            $monitoringResources[] = $newMonitoringResource;
        }
        return $monitoringResources;
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
        };
    }
}

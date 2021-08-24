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

namespace Centreon\Infrastructure\Monitoring\MonitoringResource\Repository\Model;

use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Monitoring\Notes;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;

/**
 * This class is designed to provide a way to create the Monitoring Resource entity from the database.
 *
 * @package Centreon\Infrastructure\Monitoring\MonitoringResource\Repository\Model
 */
class MonitoringResourceFactoryRdb
{
    /**
     * Create a Monitoring Resource entity from database data.
     *
     * @param array<string, mixed> $data
     * @return MonitoringResource
     * @throws \Assert\AssertionFailedException
     */
    public static function create(array $data): MonitoringResource
    {
        $monitoringResource = new MonitoringResource(
            (int) $data['id'],
            $data['name'],
            $data['type']
        );

        $monitoringResource->setAlias($data['alias']);
        $monitoringResource->setFqdn($data['fqdn']);
        $monitoringResource->setHostId(self::getIntOrNull($data['host_id']));
        $monitoringResource->setServiceId(self::getIntOrNull($data['service_id']));
        $monitoringResource->setCommandLine($data['command_line']);
        $monitoringResource->setTimezone($data['timezone']);
        $monitoringResource->setMonitoringServerName($data['monitoring_server_name']);
        $monitoringResource->setFlapping((int) $data['flapping'] === 1);
        $monitoringResource->setPercentStateChange((float) $data['percent_state_change']);
        $monitoringResource->setSeverityLevel(self::getIntOrNull($data['severity_level']));
        $monitoringResource->setInDowntime((int) $data['in_downtime'] === 1);
        $monitoringResource->setAcknowledged((int) $data['acknowledged'] === 1);
        $monitoringResource->setActiveChecks((int) $data['active_checks'] === 1);
        $monitoringResource->setPassiveChecks((int) $data['passive_checks'] === 1);

        $lastStatusChange = (new \DateTime())
            ->setTimestamp((int) $data['last_status_change']);

        $monitoringResource->setLastStatusChange($lastStatusChange);

        $lastNotification = $data['last_notification'] !== null
            ? $lastNotification = (new \DateTime())->setTimestamp((int) $data['last_notification'])
            : null;

        $monitoringResource->setLastNotification($lastNotification);

        $monitoringResource->setNotificationNumber(self::getIntOrNull($data['notification_number']));
        $monitoringResource->setTries($data['tries']);

        $lastCheck = (new \DateTime())
            ->setTimestamp((int) $data['last_check']);

        $monitoringResource->setLastCheck($lastCheck);

        $nextCheck = (new \DateTime())
            ->setTimestamp((int) $data['next_check']);

        $monitoringResource->setNextCheck($nextCheck);

        $monitoringResource->setInformation($data['information']);
        $monitoringResource->setPerformanceData($data['performance_data']);
        $monitoringResource->setExecutionTime((float) $data['execution_time']);
        $monitoringResource->setLatency((float) $data['latency']);
        $monitoringResource->setNotificationEnabled((int) $data['notification_enabled'] === 1);


        $status = (new ResourceStatus())
            ->setCode((int) $data['status_code'])
            ->setName($data['status_name'])
            ->setSeverityCode((int) $data['status_severity_code']);

        $monitoringResource->setStatus($status);

        $icon = null;
        if (!empty($data['icon_url'])) {
            $icon = (new Icon())
                ->setName($data['icon_name'])
                ->setUrl($data['icon_url']);
        }

        $monitoringResource->setIcon($icon);

        $parent = null;
        if (
            !empty($data['parent_id']) &&
            !empty($data['parent_name']) &&
            !empty($data['parent_type'])
        ) {
            $parent = new MonitoringResource(
                (int) $data['parent_id'],
                $data['parent_name'],
                $data['parent_type']
            );

            $parent->setAlias($data['parent_alias']);
            $parent->setFqdn($data['parent_fqdn']);

            $parentIcon = null;
            if (!empty($data['parent_icon_url'])) {
                $parentIcon = (new Icon())
                    ->setName($data['parent_icon_name'])
                    ->setUrl($data['parent_icon_url']);
            }

            $parent->setIcon($parentIcon);

            $parentStatus = (new ResourceStatus())
                ->setCode((int) $data['parent_status_code'])
                ->setName($data['parent_status_name'])
                ->setSeverityCode((int) $data['parent_status_severity_code']);

            $parent->setStatus($parentStatus);
            $parent->setHasGraphData(false);
        }

        $monitoringResource->setParent($parent);

        $externalLinks = $monitoringResource->getLinks()->getExternals();

        if ($data['action_url']) {
            $externalLinks->setActionUrl(self::replaceMacrosInUrl($monitoringResource, $data['action_url']));
        }

        if ($data['notes_url'] !== null) {
            $notes = (new Notes(self::replaceMacrosInUrl($monitoringResource, $data['notes_url'])))
                ->setLabel($data['notes_label']);
            $externalLinks->setNotes($notes);
        }

        $monitoringResource->setHasGraphData((int) $data['has_graph_data'] === 1);

        return $monitoringResource;
    }

    /**
     * @param int|string|null $property
     * @return int|null
     */
    private static function getIntOrNull($property): ?int
    {
        return ($property !== null) ? (int) $property : null;
    }

    /**
     * @param MonitoringResource $monitoringResource
     * @param string $url
     * @return string
     */
    private static function replaceMacrosInUrl(MonitoringResource $monitoringResource, string $url): string
    {
        if ($monitoringResource->getType() === MonitoringResource::TYPE_HOST) {
            return self::replaceMacrosInUrlForHostResource($monitoringResource, $url);
        } elseif ($monitoringResource->getType() === MonitoringResource::TYPE_SERVICE) {
            return self::replaceMacrosInUrlForServiceResource($monitoringResource, $url);
        }
        return $url;
    }

    /**
     * Replaces macros in the URL for host resource type
     *
     * @param MonitoringResource $resource
     * @param string $url
     * @return string
     */
    private static function replaceMacrosInUrlForHostResource(MonitoringResource $resource, string $url): string
    {
        $url = str_replace('$HOSTADDRESS$', $resource->getFqdn(), $url);
        $url = str_replace('$HOSTNAME$', $resource->getName(), $url);
        $url = str_replace('$HOSTSTATE$', $resource->getStatus()->getName(), $url);
        $url = str_replace('$HOSTSTATEID$', (string) $resource->getStatus()->getCode(), $url);
        $url = str_replace('$HOSTALIAS$', $resource->getAlias(), $url);

        return $url;
    }

    /**
     * Replaces macros in the URL for service resource type
     *
     * @param MonitoringResource $resource
     * @param string $url
     * @return string
     */
    private static function replaceMacrosInUrlForServiceResource(MonitoringResource $resource, string $url): string
    {
        $url = str_replace('$HOSTADDRESS$', $resource->getParent()->getFqdn(), $url);
        $url = str_replace('$HOSTNAME$', $resource->getParent()->getName(), $url);
        $url = str_replace('$HOSTSTATE$', $resource->getParent()->getStatus()->getName(), $url);
        $url = str_replace('$HOSTSTATEID$', (string) $resource->getParent()->getStatus()->getCode(), $url);
        $url = str_replace('$HOSTALIAS$', $resource->getParent()->getAlias(), $url);
        $url = str_replace('$SERVICEDESC$', $resource->getName(), $url);
        $url = str_replace('$SERVICESTATE$', $resource->getStatus()->getName(), $url);
        $url = str_replace('$SERVICESTATEID$', (string) $resource->getStatus()->getCode(), $url);

        return $url;
    }
}

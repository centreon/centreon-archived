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

namespace Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\FindMonitoringResources;

use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\Monitoring\ResourceLinks;
use Centreon\Domain\Monitoring\ResourceStatus;

/**
 * This class is a DTO for the FindMonitoringResources use case.
 *
 * @package Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\FindMonitoringResources
 */
class FindMonitoringResourcesResponse
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private $monitoringResources = [];

    /**
     * @param MonitoringResource[] $monitoringResources
     */
    public function setMonitoringResources(array $monitoringResources): void
    {
        foreach ($monitoringResources as $monitoringResource) {
            $formattedMonitoringResource = $this->monitoringResourceToArray($monitoringResource);
            $formattedMonitoringResource['has_graph_data'] = $monitoringResource->hasGraphData();
            $formattedMonitoringResource['parent'] = $this->monitoringResourceToArray($monitoringResource->getParent());
            $this->monitoringResources[] = $formattedMonitoringResource;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMonitoringResources(): array
    {
        return $this->monitoringResources;
    }

    /**
     * Converts a MonitoringResource entity into an array
     *
     * @param MonitoringResource|null $monitoringResource
     * @return array<string, mixed>|null
     */
    private function monitoringResourceToArray(?MonitoringResource $monitoringResource): ?array
    {
        if ($monitoringResource !== null) {
            return [
                'uuid' => $monitoringResource->getUuid(),
                'short_type' => $monitoringResource->getShortType(),
                'id' => $monitoringResource->getId(),
                'name' => $monitoringResource->getName(),
                'type' => $monitoringResource->getType(),
                'alias' => $monitoringResource->getAlias(),
                'fqdn' => $monitoringResource->getFqdn(),
                'service_id' => $monitoringResource->getServiceId(),
                'host_id' => $monitoringResource->getHostId(),
                'acknowledged' => $monitoringResource->getAcknowledged(),
                'active_checks' => $monitoringResource->getActiveChecks(),
                'flapping' => $monitoringResource->getFlapping(),
                'icon' => $this->iconToArray($monitoringResource->getIcon()),
                'in_downtime' => $monitoringResource->getInDowntime(),
                'information' => $monitoringResource->getInformation(),
                'last_check' => $monitoringResource->getLastCheckAsString(),
                'last_status_change' => $monitoringResource->getLastStatusChange(),
                'monitoring_server_name' => $monitoringResource->getMonitoringServerName(),
                'notification_enabled' => $monitoringResource->isNotificationEnabled(),
                'passive_checks' => $monitoringResource->getPassiveChecks(),
                'performance_data' => $monitoringResource->getPerformanceData(),
                'severity_level' => $monitoringResource->getSeverityLevel(),
                'status' => $this->statusToArray($monitoringResource->getStatus()),
                'tries' => $monitoringResource->getTries(),
                'duration' => $monitoringResource->getDuration(),
                'links' => $this->linksToArray($monitoringResource->getLinks())
            ];
        }
        return null;
    }

    /**
     * Converts ResourceLinks entity into an array
     *
     * @param ResourceLinks|null $links
     * @return array<string, mixed>
     */
    private function linksToArray(?ResourceLinks $links): array
    {
        $formattedLinks = [
            'uris' => [],
            'endpoints' => [],
            'externals' => [],
        ];

        if ($links->getExternals() !== null) {
            $formattedExternals = [
                'action_url' => $links->getExternals()->getActionUrl(),
                'notes' => null,
            ];

            if ($links->getExternals()->getNotes() !== null) {
                $formattedExternals['notes'] = [
                    'label' => $links->getExternals()->getNotes()->getLabel(),
                    'url' => $links->getExternals()->getNotes()->getUrl()
                ];
            }
            $formattedLinks['externals'] = $formattedExternals;
        }

        return $formattedLinks;
    }

    /**
     * Converts a ResourceStatus entity into an array
     *
     * @param ResourceStatus|null $status
     * @return array<string, mixed>|null
     */
    private function statusToArray(?ResourceStatus $status): ?array
    {
        if ($status !== null) {
            return [
                'code' => $status->getCode(),
                'name' => $status->getName(),
                'severity_code' => $status->getSeverityCode()
            ];
        }
        return null;
    }

    /**
     * Converts an Icon entity into an array
     *
     * @param Icon|null $icon
     * @return array<string, mixed>|null
     */
    private function iconToArray(?Icon $icon): ?array
    {
        if ($icon !== null) {
            return [
                'name' => $icon->getName(),
                'url' => $icon->getUrl(),
            ];
        }
        return null;
    }
}

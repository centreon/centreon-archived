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

namespace Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailServiceMonitoringResource;

use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\ResourceGroup;
use Centreon\Domain\Monitoring\ResourceLinks;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;

/**
 * This class is a DTO for the detailServiceMonitoringResource use case.
 *
 * @package Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailServiceMonitoringResource
 */
class DetailServiceMonitoringResourceResponse
{
    /**
     * @var array<string, mixed>
     */
    private $monitoringResource = [];

    /**
     * @param MonitoringResource $monitoringResource
     */
    public function setServiceMonitoringResourceDetail(MonitoringResource $monitoringResource): void
    {
        $formattedMonitoringResource = $this->monitoringResourceToArray($monitoringResource);
        $formattedMonitoringResource['parent'] = $this->monitoringResourceToArray($monitoringResource->getParent());
        $this->monitoringResource = $formattedMonitoringResource;
    }

    /**
     * @return array<string, mixed> $monitoringResource
     */
    public function getServiceMonitoringResourceDetail(): array
    {
        return $this->monitoringResource;
    }

    /**
     * Converts a MonitoringResource entity and sub-entities into an array
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
                'last_check' => $monitoringResource->getLastCheck(),
                'last_status_change' => $monitoringResource->getLastStatusChange(),
                'monitoring_server_name' => $monitoringResource->getMonitoringServerName(),
                'notification_enabled' => $monitoringResource->isNotificationEnabled(),
                'passive_checks' => $monitoringResource->getPassiveChecks(),
                'performance_data' => $monitoringResource->getPerformanceData(),
                'severity_level' => $monitoringResource->getSeverityLevel(),
                'status' => $this->statusToArray($monitoringResource->getStatus()),
                'tries' => $monitoringResource->getTries(),
                'duration' => $monitoringResource->getDuration(),
                'links' => $this->linksToArray($monitoringResource->getLinks()),
                'groups' => $this->groupsToArray($monitoringResource->getGroups()),
                'acknowledgement' => $this->acknowledgementToArray($monitoringResource->getAcknowledgement()),
                'downtimes' => $this->downtimeToArray($monitoringResource->getDowntimes()),
                'percent_state_change' => $monitoringResource->getPercentStateChange(),
                'notification_number' => $monitoringResource->getNotificationNumber(),
                'next_check' => $monitoringResource->getNextCheck(),
                'latency' => $monitoringResource->getLatency(),
                'last_notification' => $monitoringResource->getLastNotification(),
                'execution_time' => $monitoringResource->getExecutionTime(),
                'timezone' => $monitoringResource->getTimezone(),
                'command_line' => $monitoringResource->getCommandLine(),
            ];
        }
        return null;
    }

    /**
     * Convert ResourceLinks entity into an array
     *
     * @param ResourceLinks|null $links
     * @return array<string, array<string, array<string, string|null>|string|null>>
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
     * Convert Icon entity into an array
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

    /**
     * Converts an array of ResourceGroup entity into an array
     *
     * @param ResourceGroup[] $groups
     * @return array<int, array<string, mixed>>
     */
    private function groupsToArray(array $groups): array
    {
        $arrGroups = [];
        foreach ($groups as $value) {
            $arrGroups[] = [
                'id' => $value->getId(),
                'name' => $value->getName()
            ];
        }
        return $arrGroups;
    }

    /**
     * Converts an Acknowledgement entity into an array
     *
     * @param Acknowledgement|null $acknowledgement
     * @return array<string, mixed>|null
     */
    private function acknowledgementToArray(?Acknowledgement $acknowledgement): ?array
    {
        if ($acknowledgement !==  null) {
            return [
                'author' => $acknowledgement->getAuthorName(),
                'acknowledgement_id' => $acknowledgement->getId(),
                'comment_data' => $acknowledgement->getComment(),
                'instance_id' => $acknowledgement->getPollerId(),
                'notify_contacts' => $acknowledgement->isNotifyContacts(),
                'persistent_comment' => $acknowledgement->isPersistentComment(),
                'sticky' => $acknowledgement->isSticky()
            ];
        }
        return null;
    }

    /**
     * Converts an array of Downtimes entities into an array
     *
     * @param Downtime[] $downtimes
     * @return array<int, array<string, mixed>>
     */
    private function downtimeToArray(array $downtimes): array
    {
        $downtimesToArray = [];
        foreach ($downtimes as $downtime) {
            $downtimesToArray[] = [
                'author' => $downtime->getAuthorName(),
                'downtime_id' => $downtime->getId(),
                'cancelled' => $downtime->isCancelled(),
                'comment_data' => $downtime->getComment(),
                'fixed' => $downtime->isFixed(),
                'instance_id' => $downtime->getPollerId(),
                'started' => $downtime->isStarted(),
            ];
        }
        return $downtimesToArray;
    }
}

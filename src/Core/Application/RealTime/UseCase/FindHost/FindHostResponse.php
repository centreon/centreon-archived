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

namespace Core\Application\RealTime\UseCase\FindHost;

use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Domain\RealTime\Model\Icon;
use Core\Domain\RealTime\Model\Status;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Hostgroup;

class FindHostResponse
{
    /**
     * @var array<string, string|null>|null
     */
    public $icon;

    /**
     * @var array<int, array<string, mixed>>
     */
    public $hostgroups;

    /**
     * @var array<string, mixed>
     */
    public $status;

    /**
     * @var array<int, array<string, mixed>>
     */
    public $downtimes;

    /**
     * @var array<string, mixed>
     */
    public $acknowledgement;

    /**
     * @param int $id
     * @param string $name
     * @param string $address
     * @param string $monitoringServerName
     * @param string|null $timezone
     * @param string|null $alias
     * @param boolean|null $isFlapping
     * @param boolean|null $isAcknowledged
     * @param boolean|null $isInDowntime
     * @param string|null $output
     * @param string|null $performanceData
     * @param string|null $commandLine
     * @param int|null $notificationNumber
     * @param \DateTime|null $lastStatusChange
     * @param \DateTime|null $lastNotification
     * @param float|null $latency
     * @param float|null $executionTime
     * @param float|null $statusChangePercentage
     * @param \DateTime|null $nextCheck
     * @param \DateTime|null $lastCheck
     * @param boolean|null $hasPassiveChecks
     * @param boolean|null $hasActiveChecks
     * @param \DateTime|null $lastTimeUp
     * @param int|null $severityLevel
     * @param Status $status
     * @param Icon|null $icon
     * @param Hostgroup[] $hostgroups
     * @param Downtime[] $downtimes
     * @param Acknowledgement|null $acknowledgement
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $address,
        public string $monitoringServerName,
        public ?string $timezone,
        public ?string $alias,
        public ?bool $isFlapping,
        public ?bool $isAcknowledged,
        public ?bool $isInDowntime,
        public ?string $output,
        public ?string $performanceData,
        public ?string $commandLine,
        public ?int $notificationNumber,
        public ?\DateTime $lastStatusChange,
        public ?\DateTime $lastNotification,
        public ?float $latency,
        public ?float $executionTime,
        public ?float $statusChangePercentage,
        public ?\DateTime $nextCheck,
        public ?\DateTime $lastCheck,
        public ?bool $hasPassiveChecks,
        public ?bool $hasActiveChecks,
        public ?\DateTime $lastTimeUp,
        public ?int $severityLevel,
        public ?int $checkAttempts,
        public ?int $maxCheckAttempts,
        Status $status,
        ?Icon $icon,
        array $hostgroups,
        array $downtimes,
        ?Acknowledgement $acknowledgement
    ) {
        $this->icon = self::iconToArray($icon);
        $this->status = self::statusToArray($status);
        $this->hostgroups = self::hostgroupsToArray($hostgroups);
        $this->downtimes = self::downtimeToArray($downtimes);
        $this->acknowledgement = self::acknowledgementToArray($acknowledgement);
    }

    /**
     * Converts Status model into an array for DTO
     *
     * @param Status $status
     * @return array<string, mixed>
     */
    private static function statusToArray(Status $status): array
    {
        return [
            'name' => $status->getName(),
            'code' => $status->getCode(),
            'severity_code' => $status->getOrder(),
            'type' => $status->getType()
        ];
    }

    /**
     * Converts an Icon model into an array
     *
     * @param Icon|null $icon
     * @return array<string, string|null>
     */
    private static function iconToArray(?Icon $icon): array
    {
        if ($icon !== null) {
            return [
                'name' => $icon->getName(),
                'url' => $icon->getUrl()
            ];
        }
        return [];
    }

    /**
    * Converts an array of Hostgroups model into an array
    *
    * @param Hostgroup[] $hostgroups
    * @return array<int, array<string, mixed>>
    */
    private static function hostgroupsToArray(array $hostgroups): array
    {
        $arrHostgroups = [];
        foreach ($hostgroups as $hostgroup) {
            $arrHostgroups[] = [
                'id' => $hostgroup->getId(),
                'name' => $hostgroup->getName()
            ];
        }
        return $arrHostgroups;
    }

    /**
    * Converts an Acknowledgement entity into an array
    *
    * @param Acknowledgement|null $acknowledgement
    * @return array<string, mixed>
    */
    private static function acknowledgementToArray(?Acknowledgement $acknowledgement): array
    {
        if (is_null($acknowledgement)) {
            return [];
        }

        return [
            'id' => $acknowledgement->getId(),
            'poller_id' => $acknowledgement->getInstanceId(),
            'host_id' => $acknowledgement->getHostId(),
            'service_id' => $acknowledgement->getServiceId(),
            'author_id' => $acknowledgement->getAuthorId(),
            'author_name' => $acknowledgement->getAuthorName(),
            'comment' => $acknowledgement->getComment(),
            'deletion_time' => $acknowledgement->getDeletionTime(),
            'entry_time' => $acknowledgement->getEntryTime(),
            'is_notify_contacts' => $acknowledgement->isNotifyContacts(),
            'is_persistent_comment' => $acknowledgement->isPersistentComment(),
            'is_sticky' => $acknowledgement->isSticky(),
            'state' => $acknowledgement->getState(),
            'type' => $acknowledgement->getType(),
            'with_services' => $acknowledgement->isWithServices()
        ];
    }

    /**
    * Converts an array of Downtimes entities into an array
    *
    * @param Downtime[] $downtimes
    * @return array<int, array<string, mixed>>
    */
    private static function downtimeToArray(array $downtimes): array
    {
        $arrayDowntimes = [];
        foreach ($downtimes as $downtime) {
            $arrayDowntimes[] = [
                'start_time' => $downtime->getStartTime(),
                'end_time' => $downtime->getEndTime(),
                'actual_start_time' => $downtime->getActualStartTime(),
                'id' => $downtime->getId(),
                'entry_time' => $downtime->getEntryTime(),
                'author_id' => $downtime->getAuthorId(),
                'author_name' => $downtime->getAuthorName(),
                'host_id' => $downtime->getHostId(),
                'service_id' => $downtime->getServiceId(),
                'is_cancelled' => $downtime->isCancelled(),
                'comment' => $downtime->getComment(),
                'deletion_time' => $downtime->getDeletionTime(),
                'duration' => $downtime->getDuration(),
                'internal_id' => $downtime->getInternalId(),
                'is_fixed' => $downtime->isFixed(),
                'poller_id' => $downtime->getInstanceId(),
                'is_started' => $downtime->isStarted()
            ];
        }
        return $arrayDowntimes;
    }
}

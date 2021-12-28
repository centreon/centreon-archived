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
     * @var string|null
     */
    public $timezone;

    /**
     * @var string|null
     */
    public $alias;

    /**
     * @var bool|null
     */
    public $isFlapping;

    /**
     * @var bool|null
     */
    public $isAcknowledged;

    /**
     * @var bool|null
     */
    public $isInDowntime;

    /**
     * @var string|null
     */
    public $output;

    /**
     * @var string|null
     */
    public $performanceData;

    /**
     * @var string|null
     */
    public $commandLine;

    /**
     * @var int|null
     */
    public $notificationNumber;

    /**
     * @var \DateTime|null
     */
    public $lastStatusChange;

    /**
     * @var \DateTime|null
     */
    public $lastNotification;

    /**
     * @var float|null
     */
    public $latency;

    /**
     * @var float|null
     */
    public $executionTime;

    /**
     * @var float|null
     */
    public $statusChangePercentage;

    /**
     * @var \DateTime|null
     */
    public $nextCheck;

    /**
     * @var \DateTime|null
     */
    public $lastCheck;

    /**
     * @var bool|null
     */
    public $hasActiveChecks;

    /**
     * @var bool|null
     */
    public $hasPassiveChecks;

    /**
     * @var \DateTime|null
     */
    public $lastTimeUp;

    /**
     * @var int|null
     */
    public $severityLevel;

    /**
     * @var int|null
     */
    public $checkAttempts;

    /**
     * @var int|null
     */
    public $maxCheckAttempts;

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
        return array_map(
            fn($hostgroup) => [
                'id' => $hostgroup->getId(),
                'name' => $hostgroup->getName()
            ],
            $hostgroups
        );
        /*
        return array_reduce(
            $hostgroups,
            fn (Hostgroup $hostgroup) => [
                'id' => $hostgroup->getId(),
                'name' => $hostgroup->getName()
            ],
            $hostgroups
        ); */
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
        return array_map(
            fn (Downtime $downtime) => [
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
                    'internal_id' => $downtime->getEngineDowntimeId(),
                    'is_fixed' => $downtime->isFixed(),
                    'poller_id' => $downtime->getInstanceId(),
                    'is_started' => $downtime->isStarted()
            ],
            $downtimes
        );
    }
}

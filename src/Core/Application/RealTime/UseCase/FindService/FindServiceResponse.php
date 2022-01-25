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

namespace Core\Application\RealTime\UseCase\FindService;

use Core\Domain\RealTime\Model\Host;
use Core\Domain\RealTime\Model\Icon;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Servicegroup;
use Core\Domain\RealTime\Model\ServiceStatus;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Application\RealTime\Common\RealTimeResponseTrait;

class FindServiceResponse
{
    use RealTimeResponseTrait;

    /**
     * @var bool
     */
    public $isFlapping;

    /**
     * @var bool
     */
    public $isAcknowledged;

    /**
     * @var bool
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
     * @var bool
     */
    public $hasActiveChecks;

    /**
     * @var bool
     */
    public $hasPassiveChecks;

    /**
     * @var \DateTime|null
     */
    public $lastTimeOk;

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
     * @var array<string, string|null>
     */
    public $icon;

    /**
     * @var array<array<string, mixed>>
     */
    public $servicegroups;

    /**
     * @var array<string, mixed>
     */
    public $status;

    /**
     * @var array<array<string, mixed>>
     */
    public $downtimes;

    /**
     * @var array<string, mixed>
     */
    public $acknowledgement;

    /**
     * @var array<string, mixed>
     */
    public $host;

    /**
     * @param int $id
     * @param int $hostId
     * @param string $name
     * @param ServiceStatus $status
     * @param Icon|null $icon
     * @param Servicegroup[] $servicegroups
     * @param Downtime[] $downtimes
     * @param Acknowledgement|null $acknowledgement
     * @param Host $host
     */
    public function __construct(
        public int $id,
        public int $hostId,
        public string $name,
        ServiceStatus $status,
        ?Icon $icon,
        array $servicegroups,
        array $downtimes,
        ?Acknowledgement $acknowledgement,
        Host $host
    ) {
        $this->servicegroups = self::servicegroupsToArray($servicegroups);
        $this->status = self::statusToArray($status);
        $this->icon = self::iconToArray($icon);
        $this->downtimes = self::downtimesToArray($downtimes);
        $this->acknowledgement = self::acknowledgementToArray($acknowledgement);
        $this->host = self::hostToArray($host);
    }

    /**
     * Converts an array of Servicegroups model into an array
     *
     * @param Servicegroup[] $servicegroups
     * @return array<int, array<string, mixed>>
     */
    private static function servicegroupsToArray(array $servicegroups): array
    {
        return array_map(
            fn (Servicegroup $servicegroup) => [
                'id' => $servicegroup->getId(),
                'name' => $servicegroup->getName()
            ],
            $servicegroups
        );
    }

    /**
     * Converts a Host entity into an array
     *
     * @param Host $host
     * @return array<string, mixed>
     */
    private static function hostToArray(Host $host): array
    {
        return [
            'type' => 'host',
            'id' => $host->getId(),
            'name' => $host->getName(),
            'status' => self::statusToArray($host->getStatus()),
            'monitoring_server_name' => $host->getMonitoringServerName()
        ];
    }
}

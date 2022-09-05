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

namespace Core\Application\RealTime\UseCase\FindHost;

use Core\Domain\RealTime\Model\Icon;
use Core\Tag\RealTime\Domain\Model\Tag;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Hostgroup;
use Core\Domain\RealTime\Model\HostStatus;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Severity\RealTime\Domain\Model\Severity;
use Core\Application\RealTime\Common\RealTimeResponseTrait;
use Core\Domain\RealTime\Model\ResourceTypes\HostResourceType;

class FindHostResponse
{
    use RealTimeResponseTrait;

    /**
     * @var string|null
     */
    public $timezone;

    /**
     * @var string|null
     */
    public $alias;

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
    public $lastTimeUp;

    /**
     * @var int|null
     */
    public $checkAttempts;

    /**
     * @var int|null
     */
    public $maxCheckAttempts;

    /**
     * @var array<string, int|string|null>
     */
    public $icon;

    /**
     * @var array<array<string, mixed>>
     */
    public $groups;

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
     * @var array<array<string, mixed>>
     */
    public array $categories = [];

    /**
     * @var array<string, mixed>|null
     */
    public ?array $severity = null;

    /**
     * @var string
     */
    public string $type = HostResourceType::TYPE_NAME;

    /**
     * @param int $hostId
     * @param string $name
     * @param string $address
     * @param string $monitoringServerName
     * @param HostStatus $status
     * @param Icon|null $icon
     * @param Hostgroup[] $hostgroups
     * @param Downtime[] $downtimes
     * @param Acknowledgement|null $acknowledgement,
     * @param Tag[] $categories
     * @param Severity|null $severity
     */
    public function __construct(
        public int $hostId,
        public string $name,
        public string $address,
        public string $monitoringServerName,
        HostStatus $status,
        ?Icon $icon,
        array $hostgroups,
        array $downtimes,
        ?Acknowledgement $acknowledgement,
        array $categories,
        ?Severity $severity
    ) {
        $this->icon = $this->iconToArray($icon);
        $this->status = $this->statusToArray($status);
        $this->groups = $this->hostgroupsToArray($hostgroups);
        $this->downtimes = $this->downtimesToArray($downtimes);
        $this->acknowledgement = $this->acknowledgementToArray($acknowledgement);
        $this->categories = $this->tagsToArray($categories);
        $this->severity = is_null($severity) ? $severity : $this->severityToArray($severity);
    }

    /**
     * Converts an array of Hostgroups model into an array
     *
     * @param Hostgroup[] $hostgroups
     * @return array<int, array<string, mixed>>
     */
    private function hostgroupsToArray(array $hostgroups): array
    {
        return array_map(
            fn (Hostgroup $hostgroup) => [
                'id' => $hostgroup->getId(),
                'name' => $hostgroup->getName()
            ],
            $hostgroups
        );
    }
}

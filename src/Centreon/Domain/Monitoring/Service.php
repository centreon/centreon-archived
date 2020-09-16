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

namespace Centreon\Domain\Monitoring;

use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Service\EntityDescriptorMetadataInterface;
use CentreonDuration;

/**
 * Class representing a record of a service in the repository.
 *
 * @package Centreon\Domain\Monitoring
 */
class Service implements EntityDescriptorMetadataInterface
{
    // Groups for serilizing
    public const SERIALIZER_GROUP_MIN = 'service_min';
    public const SERIALIZER_GROUP_MAIN = 'service_main';
    public const SERIALIZER_GROUP_FULL = 'service_full';
    public const SERIALIZER_GROUP_WITH_HOST = 'service_with_host';

    /**
     * @var int|null Unique index
     */
    protected $id;

    /**
     * @var int
     */
    protected $checkAttempt;

    /**
     * @var string|null
     */
    protected $checkCommand;

    /**
     * @var float|null
     */
    protected $checkInterval;

    /**
     * @var string|null
     */
    protected $checkPeriod;

    /**
     * @var int|null
     */
    protected $checkType;

    /**
     * @var string|null
     */
    protected $commandLine;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var float|null
     */
    protected $executionTime;

    /**
     * @var Host|null
     */
    protected $host;

    /**
     * @var string|null
     */
    protected $iconImage;

    /**
     * @var string|null
     */
    protected $iconImageAlt;

    /**
     * @var bool
     */
    protected $isAcknowledged;

    /**
     * @var bool
     */
    protected $isActiveCheck;

    /**
     * @var bool
     */
    protected $isChecked;

    /**
     * @var int|null
     */
    protected $scheduledDowntimeDepth;

    /**
     * @var \DateTime|null
     */
    protected $lastCheck;

    /**
     * @var \DateTime|null
     */
    protected $lastHardStateChange;

    /**
     * @var \DateTime|null
     */
    protected $lastNotification;

    /**
     * @var \DateTime|null
     */
    protected $lastTimeCritical;

    /**
     * @var \DateTime|null
     */
    protected $lastTimeOk;

    /**
     * @var \DateTime|null
     */
    protected $lastTimeUnknown;

    /**
     * @var \DateTime|null
     */
    protected $lastTimeWarning;

    /**
     * @var \DateTime|null
     */
    protected $lastUpdate;

    /**
     * @var \DateTime|null
     */
    protected $lastStateChange;

    /**
     * @var float|null
     */
    protected $latency;

    /**
     * @var int
     */
    protected $maxCheckAttempts;

    /**
     * @var \DateTime
     */
    protected $nextCheck;

    /**
     * @var string
     */
    protected $output;

    /**
     * @var string
     */
    protected $performanceData;

    /**
     * @var int ['0' => 'OK', '1' => 'WARNING', '2' => 'CRITICAL', '3' => 'UNKNOWN', '4' => 'PENDING']
     */
    protected $state;

    /**
     * @var int ('1' => 'HARD', '0' => 'SOFT')
     */
    protected $stateType;

    /**
     * @var int
     */
    protected $criticality;

    /**
     * @var Downtime[]
     */
    protected $downtimes = [];

    /**
     * @var Acknowledgement|null
     */
    protected $acknowledgement;

    /**
     * @var bool|null
     */
    protected $flapping;

    /**
     * @var \Centreon\Domain\Monitoring\ResourceStatus|null
     */
    private $status;

    /**
     * {@inheritdoc}
     */
    public static function loadEntityDescriptorMetadata(): array
    {
        return [
            'service_id' => 'setId',
            'acknowledged' => 'setAcknowledged',
            'active_checks' => 'setActiveCheck',
            'checked' => 'setChecked',
            'perfdata' => 'setPerformanceData',
        ];
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Service
     */
    public function setId(int $id): Service
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCheckCommand(): ?string
    {
        return $this->checkCommand;
    }

    /**
     * @param string|null $checkCommand
     * @return Service
     */
    public function setCheckCommand(?string $checkCommand): Service
    {
        $this->checkCommand = $checkCommand;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getCheckInterval(): ?float
    {
        return $this->checkInterval;
    }

    /**
     * @param float|null $checkInterval
     * @return Service
     */
    public function setCheckInterval(?float $checkInterval): Service
    {
        $this->checkInterval = $checkInterval;
        return $this;
    }

    /**
     * @return string
     */
    public function getCheckPeriod(): ?string
    {
        return $this->checkPeriod;
    }

    /**
     * @param string|null $checkPeriod
     * @return Service
     */
    public function setCheckPeriod(?string $checkPeriod): Service
    {
        $this->checkPeriod = $checkPeriod;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCheckType(): ?int
    {
        return $this->checkType;
    }

    /**
     * @param int|null $checkType
     * @return Service
     */
    public function setCheckType(?int $checkType): Service
    {
        $this->checkType = $checkType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommandLine(): ?string
    {
        return $this->commandLine;
    }

    /**
     * @param string|null $commandLine
     * @return Service
     */
    public function setCommandLine(?string $commandLine): Service
    {
        $this->commandLine = $commandLine;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Service
     */
    public function setDescription(string $description): Service
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     * @return Service
     */
    public function setDisplayName(string $displayName): Service
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
    }

    /**
     * @param float|null $executionTime
     * @return Service
     */
    public function setExecutionTime(?float $executionTime): Service
    {
        $this->executionTime = $executionTime;
        return $this;
    }

    /**
     * @return Host|null
     */
    public function getHost(): ?Host
    {
        return $this->host;
    }

    /**
     * @param Host $host|null
     * @return Service
     */
    public function setHost(?Host $host): Service
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIconImage(): ?string
    {
        return $this->iconImage;
    }

    /**
     * @param string|null $iconImage
     * @return Service
     */
    public function setIconImage(?string $iconImage): Service
    {
        $this->iconImage = $iconImage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIconImageAlt(): ?string
    {
        return $this->iconImageAlt;
    }

    /**
     * @param string|null $iconImageAlt
     */
    public function setIconImageAlt(?string $iconImageAlt): void
    {
        $this->iconImageAlt = $iconImageAlt;
    }

    /**
     * @return bool
     */
    public function isAcknowledged(): bool
    {
        return $this->isAcknowledged;
    }

    /**
     * @param bool $isAcknowledged
     * @return Service
     */
    public function setAcknowledged(bool $isAcknowledged): Service
    {
        $this->isAcknowledged = $isAcknowledged;
        return $this;
    }

    /**
     * virtual property used by resource details endpoint
     * @return bool
     */
    public function getActiveCheck(): bool
    {
        return $this->isActiveCheck;
    }

    /**
     * @return bool
     */
    public function isActiveCheck(): bool
    {
        return $this->isActiveCheck;
    }

    /**
     * @param bool $isActiveCheck
     * @return Service
     */
    public function setActiveCheck(bool $isActiveCheck): Service
    {
        $this->isActiveCheck = $isActiveCheck;
        return $this;
    }

    /**
     * @return int
     */
    public function getCheckAttempt(): int
    {
        return $this->checkAttempt;
    }

    /**
     * @param int $checkAttempt
     * @return Service
     */
    public function setCheckAttempt(int $checkAttempt): Service
    {
        $this->checkAttempt = $checkAttempt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isChecked(): bool
    {
        return $this->isChecked;
    }

    /**
     * @param bool $isChecked
     * @return Service
     */
    public function setChecked(bool $isChecked): Service
    {
        $this->isChecked = $isChecked;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getScheduledDowntimeDepth(): ?int
    {
        return $this->scheduledDowntimeDepth;
    }

    /**
     * @param int $scheduledDowntimeDepth
     * @return Service
     */
    public function setScheduledDowntimeDepth(int $scheduledDowntimeDepth): Service
    {
        $this->scheduledDowntimeDepth = $scheduledDowntimeDepth;
        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     * @return Service
     */
    public function setState(int $state): Service
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * @param string $output
     * @return Service
     */
    public function setOutput(string $output): Service
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return string
     */
    public function getPerformanceData(): string
    {
        return $this->performanceData;
    }

    /**
     * @param string $performanceData
     * @return Service
     */
    public function setPerformanceData(string $performanceData): Service
    {
        $this->performanceData = $performanceData;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastCheck(): ?\DateTime
    {
        return $this->lastCheck;
    }

    /**
     * @param \DateTime|null $lastCheck
     * @return Service
     */
    public function setLastCheck(?\DateTime $lastCheck): Service
    {
        $this->lastCheck = $lastCheck;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getNextCheck(): ?\DateTime
    {
        return $this->nextCheck;
    }

    /**
     * @param \DateTime|null $nextCheck
     * @return Service|null
     */
    public function setNextCheck(?\DateTime $nextCheck): Service
    {
        $this->nextCheck = $nextCheck;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastUpdate(): ?\DateTime
    {
        return $this->lastUpdate;
    }

    /**
     * @param \DateTime|null $lastUpdate
     * @return Service
     */
    public function setLastUpdate(?\DateTime $lastUpdate): Service
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastStateChange(): ?\DateTime
    {
        return $this->lastStateChange;
    }

    /**
     * @param \DateTime|null $lastStateChange
     * @return Service
     */
    public function setLastStateChange(?\DateTime $lastStateChange): Service
    {
        $this->lastStateChange = $lastStateChange;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLatency(): ?float
    {
        return $this->latency;
    }

    /**
     * @param float|null $latency
     * @return Service
     */
    public function setLatency(?float $latency): Service
    {
        $this->latency = $latency;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastHardStateChange(): ?\DateTime
    {
        return $this->lastHardStateChange;
    }

    /**
     * @param \DateTime|null $lastHardStateChange
     * @return Service
     */
    public function setLastHardStateChange(?\DateTime $lastHardStateChange): Service
    {
        $this->lastHardStateChange = $lastHardStateChange;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastNotification(): ?\DateTime
    {
        return $this->lastNotification;
    }

    /**
     * @param \DateTime|null $lastNotification
     * @return Service
     */
    public function setLastNotification(?\DateTime $lastNotification): Service
    {
        $this->lastNotification = $lastNotification;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTimeCritical(): ?\DateTime
    {
        return $this->lastTimeCritical;
    }

    /**
     * @param \DateTime|null $lastTimeCritical
     * @return Service
     */
    public function setLastTimeCritical(?\DateTime $lastTimeCritical): Service
    {
        $this->lastTimeCritical = $lastTimeCritical;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTimeOk(): ?\DateTime
    {
        return $this->lastTimeOk;
    }

    /**
     * @param \DateTime|null $lastTimeOk
     * @return Service
     */
    public function setLastTimeOk(?\DateTime $lastTimeOk): Service
    {
        $this->lastTimeOk = $lastTimeOk;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTimeUnknown(): ?\DateTime
    {
        return $this->lastTimeUnknown;
    }

    /**
     * @param \DateTime|null $lastTimeUnknown
     * @return Service
     */
    public function setLastTimeUnknown(?\DateTime $lastTimeUnknown): Service
    {
        $this->lastTimeUnknown = $lastTimeUnknown;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTimeWarning(): ?\DateTime
    {
        return $this->lastTimeWarning;
    }

    /**
     * @param \DateTime|null $lastTimeWarning
     * @return Service
     */
    public function setLastTimeWarning(?\DateTime $lastTimeWarning): Service
    {
        $this->lastTimeWarning = $lastTimeWarning;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxCheckAttempts(): int
    {
        return $this->maxCheckAttempts;
    }

    /**
     * @param int $maxCheckAttempts
     * @return Service
     */
    public function setMaxCheckAttempts(int $maxCheckAttempts): Service
    {
        $this->maxCheckAttempts = $maxCheckAttempts;
        return $this;
    }

    /**
     * @return int
     */
    public function getStateType(): int
    {
        return $this->stateType;
    }

    /**
     * @param int $stateType
     * @return Service
     */
    public function setStateType(int $stateType): Service
    {
        $this->stateType = $stateType;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCriticality(): ?int
    {
        return $this->criticality;
    }

    /**
     * @param int|null $criticality
     * @return Service
     */
    public function setCriticality(?int $criticality): Service
    {
        $this->criticality = $criticality;
        return $this;
    }

    /**
     * @return Downtime[]
     */
    public function getDowntimes(): array
    {
        return $this->downtimes;
    }

    /**
     * @param Downtime[] $downtimes
     * @return Service
     */
    public function setDowntimes(array $downtimes): self
    {
        $this->downtimes = $downtimes;
        return $this;
    }

    /**
     * @return Acknowledgement|null
     */
    public function getAcknowledgement(): ?Acknowledgement
    {
        return $this->acknowledgement;
    }

    /**
     * @param Acknowledgement|null $acknowledgement
     * @return Service
     */
    public function setAcknowledgement(?Acknowledgement $acknowledgement): self
    {
        $this->acknowledgement = $acknowledgement;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getFlapping(): ?bool
    {
        return $this->flapping;
    }

    /**
     * @param bool|null $flapping
     * @return Service
     */
    public function setFlapping(?bool $flapping): self
    {
        $this->flapping = $flapping;
        return $this;
    }

    /**
     * @return \Centreon\Domain\Monitoring\ResourceStatus|null
     */
    public function getStatus(): ?ResourceStatus
    {
        return $this->status;
    }

    /**
     * @param \Centreon\Domain\Monitoring\ResourceStatus|null $status
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setStatus(?ResourceStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDuration(): ?string
    {
        $duration = null;

        if ($this->getLastStateChange()) {
            $duration = CentreonDuration::toString(time() - $this->getLastStateChange()->getTimestamp());
        }

        return $duration;
    }
}

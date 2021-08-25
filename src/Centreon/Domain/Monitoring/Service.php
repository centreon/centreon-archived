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
     * @return self
     */
    public function setId(int $id): self
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
     * @return self
     */
    public function setCheckCommand(?string $checkCommand): self
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
     * @return self
     */
    public function setCheckInterval(?float $checkInterval): self
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
     * @return self
     */
    public function setCheckPeriod(?string $checkPeriod): self
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
     * @return self
     */
    public function setCheckType(?int $checkType): self
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
     * @return self
     */
    public function setCommandLine(?string $commandLine): self
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
     * @return self
     */
    public function setDescription(string $description): self
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
     * @return self
     */
    public function setDisplayName(string $displayName): self
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
     * @return self
     */
    public function setExecutionTime(?float $executionTime): self
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
     * @return self
     */
    public function setHost(?Host $host): self
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
     * @return self
     */
    public function setIconImage(?string $iconImage): self
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
     * @return self
     */
    public function setAcknowledged(bool $isAcknowledged): self
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
     * @return self
     */
    public function setActiveCheck(bool $isActiveCheck): self
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
     * @return self
     */
    public function setCheckAttempt(int $checkAttempt): self
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
     * @return self
     */
    public function setChecked(bool $isChecked): self
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
     * @return self
     */
    public function setScheduledDowntimeDepth(int $scheduledDowntimeDepth): self
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
     * @return self
     */
    public function setState(int $state): self
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
     * @return self
     */
    public function setOutput(string $output): self
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
     * @return self
     */
    public function setPerformanceData(string $performanceData): self
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
     * @return self
     */
    public function setLastCheck(?\DateTime $lastCheck): self
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
     * @return self
     */
    public function setNextCheck(?\DateTime $nextCheck): self
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
     * @return self
     */
    public function setLastUpdate(?\DateTime $lastUpdate): self
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
     * @return self
     */
    public function setLastStateChange(?\DateTime $lastStateChange): self
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
     * @return self
     */
    public function setLatency(?float $latency): self
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
     * @return self
     */
    public function setLastHardStateChange(?\DateTime $lastHardStateChange): self
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
     * @return self
     */
    public function setLastNotification(?\DateTime $lastNotification): self
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
     * @return self
     */
    public function setLastTimeCritical(?\DateTime $lastTimeCritical): self
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
     * @return self
     */
    public function setLastTimeOk(?\DateTime $lastTimeOk): self
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
     * @return self
     */
    public function setLastTimeUnknown(?\DateTime $lastTimeUnknown): self
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
     * @return self
     */
    public function setLastTimeWarning(?\DateTime $lastTimeWarning): self
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
     * @return self
     */
    public function setMaxCheckAttempts(int $maxCheckAttempts): self
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
     * @return self
     */
    public function setStateType(int $stateType): self
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
     * @return self
     */
    public function setCriticality(?int $criticality): self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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

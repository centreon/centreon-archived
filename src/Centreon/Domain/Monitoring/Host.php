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
use Centreon\Domain\Service\EntityDescriptorMetadataInterface;

/**
 * Class representing a record of a host in the repository.
 *
 * @package Centreon\Domain\Monitoring
 */
class Host implements EntityDescriptorMetadataInterface
{
    // Groups for serilizing
    final public const SERIALIZER_GROUP_MIN = 'host_min';
    final public const SERIALIZER_GROUP_MAIN = 'host_main';
    final public const SERIALIZER_GROUP_FULL = 'host_full';
    final public const SERIALIZER_GROUP_WITH_SERVICES = 'host_with_services';

    // Status options
    final public const STATUS_UP          = 0;
    final public const STATUS_DOWN        = 1;
    final public const STATUS_UNREACHABLE = 2;

    /**
     * @var int|null Id of host
     */
    protected $id;

    /**
     * @var int Poller id
     */
    protected $pollerId;

    /**
     * @var string Name of host
     */
    protected $name;

    /**
     * @var bool|null
     */
    protected $acknowledged;

    /**
     * @var bool|null
     */
    protected $activeChecks;

    /**
     * @var string|null Ip address or domain name
     */
    protected $addressIp;

    /**
     * @var string|null Alias of host
     */
    protected $alias;

    /**
     * @var int|null
     */
    protected $checkAttempt;

    /**
     * @var string|null
     */
    protected $checkCommand;

    /**
     * @var double|null
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
     * @var bool|null
     */
    protected $checked;

    /**
     * @var string|null
     */
    protected $displayName;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var double|null
     */
    protected $executionTime;

    /**
     * @var string|null
     */
    protected $iconImage;

    /**
     * @var string|null
     */
    protected $iconImageAlt;

    /**
     * @var \DateTime|null
     */
    protected $lastCheck;

    /**
     * @var int|null
     */
    protected $lastHardState;

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
    protected $lastStateChange;

    /**
     * @var \DateTime|null
     */
    protected $lastTimeDown;

    /**
     * @var \DateTime|null
     */
    protected $lastTimeUnreachable;

    /**
     * @var \DateTime|null
     */
    protected $lastTimeUp;

    /**
     * @var \DateTime|null
     */
    protected $lastUpdate;

    /**
     * @var double|null
     */
    protected $latency;

    /**
     * @var int|null
     */
    protected $maxCheckAttempts;

    /**
     * @var \DateTime|null
     */
    protected $nextCheck;

    /**
     * @var int|null
     */
    protected $nextHostNotification;

    /**
     * @var double|null
     */
    protected $notificationInterval;

    /**
     * @var int|null
     */
    protected $notificationNumber;

    /**
     * @var string|null
     */
    protected $notificationPeriod;

    /**
     * @var bool|null
     */
    protected $notify;

    /**
     * @var bool|null
     */
    protected $notifyOnDown;

    /**
     * @var bool|null
     */
    protected $notifyOnDowntime;

    /**
     * @var bool|null
     */
    protected $notifyOnFlapping;

    /**
     * @var bool|null
     */
    protected $notifyOnRecovery;

    /**
     * @var bool|null
     */
    protected $notifyOnUnreachable;

    /**
     * @var string|null
     */
    protected $output;

    /**
     * @var bool|null
     */
    protected $passiveChecks;

    /**
     * @var Service[]
     */
    protected $services = [];

    /**
     * @var int|null ['0' => 'UP', '1' => 'DOWN', '2' => 'UNREACHABLE', '4' => 'PENDING']
     */
    protected $state;

    /**
     * @var int|null
     */
    protected $stateType;

    /**
     * @var string|null
     */
    protected $timezone;

    /**
     * @var int|null
     */
    protected $scheduledDowntimeDepth;

    /**
     * @var int|null
     */
    protected $criticality;

    /**
     * @var bool|null
     */
    protected $flapping;

    /**
     * @var double|null
     */
    protected $percentStateChange;

    /**
     * @var Downtime[]
     */
    protected $downtimes = [];

    /**
     * @var Acknowledgement|null
     */
    protected $acknowledgement;

    /**
     * @var string|null
     */
    protected $pollerName;

    /**
     * {@inheritdoc}
     */
    public static function loadEntityDescriptorMetadata(): array
    {
        return [
            'host_id' => 'setId',
            'instance_id' => 'setPollerId',
            'address' => 'setAddressIp',
            'acknowledged' => 'setAcknowledged',
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Host
    {
        $this->id = $id;
        return $this;
    }

    public function getPollerId(): int
    {
        return $this->pollerId;
    }

    public function setPollerId(int $pollerId): Host
    {
        $this->pollerId = $pollerId;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Host
    {
        $this->name = $name;
        return $this;
    }

    public function getAcknowledged(): ?bool
    {
        return $this->acknowledged;
    }

    public function setAcknowledged(?bool $acknowledged): Host
    {
        $this->acknowledged = $acknowledged;
        return $this;
    }

    public function getActiveChecks(): ?bool
    {
        return $this->activeChecks;
    }

    public function setActiveChecks(?bool $activeChecks): Host
    {
        $this->activeChecks = $activeChecks;
        return $this;
    }

    public function getAddressIp(): ?string
    {
        return $this->addressIp;
    }

    public function setAddressIp(?string $addressIp): Host
    {
        $this->addressIp = $addressIp;
        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): Host
    {
        $this->alias = $alias;
        return $this;
    }

    public function getCheckAttempt(): ?int
    {
        return $this->checkAttempt;
    }

    public function setCheckAttempt(?int $checkAttempt): Host
    {
        $this->checkAttempt = $checkAttempt;
        return $this;
    }

    public function getCheckCommand(): ?string
    {
        return $this->checkCommand;
    }

    public function setCheckCommand(?string $checkCommand): Host
    {
        $this->checkCommand = $checkCommand;
        return $this;
    }

    public function getCheckInterval(): ?float
    {
        return $this->checkInterval;
    }

    public function setCheckInterval(?float $checkInterval): Host
    {
        $this->checkInterval = $checkInterval;
        return $this;
    }

    public function getCheckPeriod(): ?string
    {
        return $this->checkPeriod;
    }

    public function setCheckPeriod(?string $checkPeriod): Host
    {
        $this->checkPeriod = $checkPeriod;
        return $this;
    }

    public function getCheckType(): ?int
    {
        return $this->checkType;
    }

    public function setCheckType(?int $checkType): Host
    {
        $this->checkType = $checkType;
        return $this;
    }

    public function getChecked(): ?bool
    {
        return $this->checked;
    }

    public function setChecked(?bool $checked): Host
    {
        $this->checked = $checked;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): Host
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): Host
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
    }

    public function setExecutionTime(?float $executionTime): Host
    {
        $this->executionTime = $executionTime;
        return $this;
    }

    public function getIconImage(): ?string
    {
        return $this->iconImage;
    }

    public function setIconImage(?string $iconImage): Host
    {
        $this->iconImage = $iconImage;
        return $this;
    }

    public function getIconImageAlt(): ?string
    {
        return $this->iconImageAlt;
    }

    public function setIconImageAlt(?string $iconImageAlt): Host
    {
        $this->iconImageAlt = $iconImageAlt;
        return $this;
    }

    public function getLastCheck(): ?\DateTime
    {
        return $this->lastCheck;
    }

    public function setLastCheck(?\DateTime $lastCheck): Host
    {
        $this->lastCheck = $lastCheck;
        return $this;
    }

    public function getLastHardState(): ?int
    {
        return $this->lastHardState;
    }

    public function setLastHardState(?int $lastHardState): Host
    {
        $this->lastHardState = $lastHardState;
        return $this;
    }

    public function getLastHardStateChange(): ?\DateTime
    {
        return $this->lastHardStateChange;
    }

    public function setLastHardStateChange(?\DateTime $lastHardStateChange): Host
    {
        $this->lastHardStateChange = $lastHardStateChange;
        return $this;
    }

    public function getLastNotification(): ?\DateTime
    {
        return $this->lastNotification;
    }

    public function setLastNotification(?\DateTime $lastNotification): Host
    {
        $this->lastNotification = $lastNotification;
        return $this;
    }

    public function getLastStateChange(): ?\DateTime
    {
        return $this->lastStateChange;
    }

    public function setLastStateChange(?\DateTime $lastStateChange): Host
    {
        $this->lastStateChange = $lastStateChange;
        return $this;
    }

    public function getLastTimeDown(): ?\DateTime
    {
        return $this->lastTimeDown;
    }

    public function setLastTimeDown(?\DateTime $lastTimeDown): Host
    {
        $this->lastTimeDown = $lastTimeDown;
        return $this;
    }

    public function getLastTimeUnreachable(): ?\DateTime
    {
        return $this->lastTimeUnreachable;
    }

    public function setLastTimeUnreachable(?\DateTime $lastTimeUnreachable): Host
    {
        $this->lastTimeUnreachable = $lastTimeUnreachable;
        return $this;
    }

    public function getLastTimeUp(): ?\DateTime
    {
        return $this->lastTimeUp;
    }

    public function setLastTimeUp(?\DateTime $lastTimeUp): Host
    {
        $this->lastTimeUp = $lastTimeUp;
        return $this;
    }

    public function getLastUpdate(): ?\DateTime
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(?\DateTime $lastUpdate): Host
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }

    public function getLatency(): ?float
    {
        return $this->latency;
    }

    public function setLatency(?float $latency): Host
    {
        $this->latency = $latency;
        return $this;
    }

    public function getMaxCheckAttempts(): ?int
    {
        return $this->maxCheckAttempts;
    }

    public function setMaxCheckAttempts(?int $maxCheckAttempts): Host
    {
        $this->maxCheckAttempts = $maxCheckAttempts;
        return $this;
    }

    public function getNextCheck(): ?\DateTime
    {
        return $this->nextCheck;
    }

    public function setNextCheck(?\DateTime $nextCheck): Host
    {
        $this->nextCheck = $nextCheck;
        return $this;
    }

    public function getNextHostNotification(): ?int
    {
        return $this->nextHostNotification;
    }

    public function setNextHostNotification(?int $nextHostNotification): Host
    {
        $this->nextHostNotification = $nextHostNotification;
        return $this;
    }

    public function getNotificationInterval(): ?float
    {
        return $this->notificationInterval;
    }

    public function setNotificationInterval(?float $notificationInterval): Host
    {
        $this->notificationInterval = $notificationInterval;
        return $this;
    }

    public function getNotificationNumber(): ?int
    {
        return $this->notificationNumber;
    }

    public function setNotificationNumber(?int $notificationNumber): Host
    {
        $this->notificationNumber = $notificationNumber;
        return $this;
    }

    public function getNotificationPeriod(): ?string
    {
        return $this->notificationPeriod;
    }

    public function setNotificationPeriod(?string $notificationPeriod): Host
    {
        $this->notificationPeriod = $notificationPeriod;
        return $this;
    }

    public function getNotify(): ?bool
    {
        return $this->notify;
    }

    public function setNotify(?bool $notify): Host
    {
        $this->notify = $notify;
        return $this;
    }

    public function getNotifyOnDown(): ?bool
    {
        return $this->notifyOnDown;
    }

    public function setNotifyOnDown(?bool $notifyOnDown): Host
    {
        $this->notifyOnDown = $notifyOnDown;
        return $this;
    }

    public function getNotifyOnDowntime(): ?bool
    {
        return $this->notifyOnDowntime;
    }

    public function setNotifyOnDowntime(?bool $notifyOnDowntime): Host
    {
        $this->notifyOnDowntime = $notifyOnDowntime;
        return $this;
    }

    public function getNotifyOnFlapping(): ?bool
    {
        return $this->notifyOnFlapping;
    }

    public function setNotifyOnFlapping(?bool $notifyOnFlapping): Host
    {
        $this->notifyOnFlapping = $notifyOnFlapping;
        return $this;
    }

    public function getNotifyOnRecovery(): ?bool
    {
        return $this->notifyOnRecovery;
    }

    public function setNotifyOnRecovery(?bool $notifyOnRecovery): Host
    {
        $this->notifyOnRecovery = $notifyOnRecovery;
        return $this;
    }

    public function getNotifyOnUnreachable(): ?bool
    {
        return $this->notifyOnUnreachable;
    }

    public function setNotifyOnUnreachable(?bool $notifyOnUnreachable): Host
    {
        $this->notifyOnUnreachable = $notifyOnUnreachable;
        return $this;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): Host
    {
        $this->output = $output;
        return $this;
    }

    public function getPassiveChecks(): ?bool
    {
        return $this->passiveChecks;
    }

    public function setPassiveChecks(?bool $passiveChecks): Host
    {
        $this->passiveChecks = $passiveChecks;
        return $this;
    }

    /**
     * @return Service[]
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * @param Service[] $services
     */
    public function setServices(array $services): Host
    {
        $this->services = $services;
        return $this;
    }

    public function addService(Service $service)
    {
        $this->services[] = $service;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): Host
    {
        $this->state = $state;
        return $this;
    }

    public function getStateType(): ?int
    {
        return $this->stateType;
    }

    public function setStateType(?int $stateType): Host
    {
        $this->stateType = $stateType;
        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function getSanitizedTimezone(): ?string
    {
        return (null !== $this->timezone) ?
            preg_replace('/^:/', '', $this->timezone) :
            $this->timezone;
    }

    public function setTimezone(?string $timezone): Host
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getScheduledDowntimeDepth(): ?int
    {
        return $this->scheduledDowntimeDepth;
    }

    public function setScheduledDowntimeDepth(?int $scheduledDowntimeDepth): Host
    {
        $this->scheduledDowntimeDepth = $scheduledDowntimeDepth;
        return $this;
    }

    public function getCriticality(): ?int
    {
        return $this->criticality;
    }

    public function setCriticality(?int $criticality): Host
    {
        $this->criticality = $criticality;
        return $this;
    }

    public function getFlapping(): ?bool
    {
        return $this->flapping;
    }

    public function setFlapping(?bool $flapping): Host
    {
        $this->flapping = $flapping;
        return $this;
    }

    public function getPercentStateChange(): ?float
    {
        return $this->percentStateChange;
    }

    public function setPercentStateChange(?float $percentStateChange): Host
    {
        $this->percentStateChange = $percentStateChange;
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
     */
    public function setDowntimes(array $downtimes): self
    {
        $this->downtimes = $downtimes;
        return $this;
    }

    public function getAcknowledgement(): ?Acknowledgement
    {
        return $this->acknowledgement;
    }

    public function setAcknowledgement(?Acknowledgement $acknowledgement): self
    {
        $this->acknowledgement = $acknowledgement;
        return $this;
    }

    public function getPollerName(): ?string
    {
        return $this->pollerName;
    }

    public function setPollerName(?string $pollerName): self
    {
        $this->pollerName = $pollerName;

        return $this;
    }
}

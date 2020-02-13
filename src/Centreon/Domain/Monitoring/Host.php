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

use Centreon\Domain\Service\EntityDescriptorMetadataInterface;

/**
 * Class representing a record of a host in the repository.
 *
 * @package Centreon\Domain\Monitoring
 */
class Host implements EntityDescriptorMetadataInterface
{
    // Groups for serilizing
    public const SERIALIZER_GROUP_MIN = 'host_min';
    public const SERIALIZER_GROUP_MAIN = 'host_main';
    public const SERIALIZER_GROUP_FULL = 'host_full';
    public const SERIALIZER_GROUP_WITH_SERVICES = 'host_with_services';

    // Status options
    public const STATUS_UP          = 0;
    public const STATUS_DOWN        = 1;
    public const STATUS_UNREACHABLE = 2;

    /**
     * @var int Id of host
     */
    private $id;

    /**
     * @var int Poller id
     */
    private $pollerId;

    /**
     * @var string Name of host
     */
    private $name;

    /**
     * @var bool|null
     */
    private $acknowledged;

    /**
     * @var bool|null
     */
    private $activeChecks;

    /**
     * @var string|null Ip address or domain name
     */
    private $addressIp;

    /**
     * @var string|null Alias of host
     */
    private $alias;

    /**
     * @var int|null
     */
    private $checkAttempt;

    /**
     * @var string|null
     */
    private $checkCommand;

    /**
     * @var double|null
     */
    private $checkInterval;

    /**
     * @var string|null
     */
    private $checkPeriod;

    /**
     * @var int|null
     */
    private $checkType;

    /**
     * @var bool|null
     */
    private $checked;

    /**
     * @var string|null
     */
    private $displayName;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var double|null
     */
    private $executionTime;

    /**
     * @var string|null
     */
    private $iconImage;

    /**
     * @var string|null
     */
    private $iconImageAlt;

    /**
     * @var \DateTime|null
     */
    private $lastCheck;

    /**
     * @var int|null
     */
    private $lastHardState;

    /**
     * @var \DateTime|null
     */
    private $lastHardStateChange;

    /**
     * @var \DateTime|null
     */
    private $lastNotification;

    /**
     * @var \DateTime|null
     */
    private $lastStateChange;

    /**
     * @var \DateTime|null
     */
    private $lastTimeDown;

    /**
     * @var \DateTime|null
     */
    private $lastTimeUnreachable;

    /**
     * @var \DateTime|null
     */
    private $lastTimeUp;

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;

    /**
     * @var double|null
     */
    private $latency;

    /**
     * @var int|null
     */
    private $maxCheckAttempts;

    /**
     * @var \DateTime|null
     */
    private $nextCheck;

    /**
     * @var int|null
     */
    private $nextHostNotification;

    /**
     * @var double|null
     */
    private $notificationInterval;

    /**
     * @var int|null
     */
    private $notificationNumber;

    /**
     * @var string|null
     */
    private $notificationPeriod;

    /**
     * @var bool|null
     */
    private $notify;

    /**
     * @var bool|null
     */
    private $notifyOnDown;

    /**
     * @var bool|null
     */
    private $notifyOnDowntime;

    /**
     * @var bool|null
     */
    private $notifyOnFlapping;

    /**
     * @var bool|null
     */
    private $notifyOnRecovery;

    /**
     * @var bool|null
     */
    private $notifyOnUnreachable;

    /**
     * @var string|null
     */
    private $output;

    /**
     * @var bool|null
     */
    private $passiveChecks;

    /**
     * @var Service[]
     */
    private $services = [];

    /**
     * @var int|null ['0' => 'UP', '1' => 'DOWN', '2' => 'UNREACHABLE', '4' => 'PENDING']
     */
    private $state;

    /**
     * @var int|null
     */
    private $stateType;

    /**
     * @var string|null
     */
    private $timezone;

    /**
     * @var int|null
     */
    private $scheduledDowntimeDepth;

    /**
     * @var int|null
     */
    private $criticality;

    /**
     * {@inheritdoc}
     */
    public static function loadEntityDescriptorMetadata(): array
    {
        return [
            'host_id' => 'setId',
            'instance_id' => 'setPollerId',
            'address' => 'setAddressIp',
        ];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Host
     */
    public function setId(int $id): Host
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getPollerId(): int
    {
        return $this->pollerId;
    }

    /**
     * @param int $pollerId
     * @return Host
     */
    public function setPollerId(int $pollerId): Host
    {
        $this->pollerId = $pollerId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Host
     */
    public function setName(string $name): Host
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getAcknowledged(): ?bool
    {
        return $this->acknowledged;
    }

    /**
     * @param bool|null $acknowledged
     * @return Host
     */
    public function setAcknowledged(?bool $acknowledged): Host
    {
        $this->acknowledged = $acknowledged;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getActiveChecks(): ?bool
    {
        return $this->activeChecks;
    }

    /**
     * @param bool|null $activeChecks
     * @return Host
     */
    public function setActiveChecks(?bool $activeChecks): Host
    {
        $this->activeChecks = $activeChecks;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddressIp(): ?string
    {
        return $this->addressIp;
    }

    /**
     * @param string|null $addressIp
     * @return Host
     */
    public function setAddressIp(?string $addressIp): Host
    {
        $this->addressIp = $addressIp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param string|null $alias
     * @return Host
     */
    public function setAlias(?string $alias): Host
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCheckAttempt(): ?int
    {
        return $this->checkAttempt;
    }

    /**
     * @param int|null $checkAttempt
     * @return Host
     */
    public function setCheckAttempt(?int $checkAttempt): Host
    {
        $this->checkAttempt = $checkAttempt;
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
     * @return Host
     */
    public function setCheckCommand(?string $checkCommand): Host
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
     * @return Host
     */
    public function setCheckInterval(?float $checkInterval): Host
    {
        $this->checkInterval = $checkInterval;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCheckPeriod(): ?string
    {
        return $this->checkPeriod;
    }

    /**
     * @param string|null $checkPeriod
     * @return Host
     */
    public function setCheckPeriod(?string $checkPeriod): Host
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
     * @return Host
     */
    public function setCheckType(?int $checkType): Host
    {
        $this->checkType = $checkType;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getChecked(): ?bool
    {
        return $this->checked;
    }

    /**
     * @param bool|null $checked
     * @return Host
     */
    public function setChecked(?bool $checked): Host
    {
        $this->checked = $checked;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     * @return Host
     */
    public function setDisplayName(?string $displayName): Host
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return Host
     */
    public function setEnabled(bool $enabled): Host
    {
        $this->enabled = $enabled;
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
     * @return Host
     */
    public function setExecutionTime(?float $executionTime): Host
    {
        $this->executionTime = $executionTime;
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
     * @return Host
     */
    public function setIconImage(?string $iconImage): Host
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
     * @return Host
     */
    public function setIconImageAlt(?string $iconImageAlt): Host
    {
        $this->iconImageAlt = $iconImageAlt;
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
     * @return Host
     */
    public function setLastCheck(?\DateTime $lastCheck): Host
    {
        $this->lastCheck = $lastCheck;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getLastHardState(): ?int
    {
        return $this->lastHardState;
    }

    /**
     * @param int|null $lastHardState
     * @return Host
     */
    public function setLastHardState(?int $lastHardState): Host
    {
        $this->lastHardState = $lastHardState;
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
     * @return Host
     */
    public function setLastHardStateChange(?\DateTime $lastHardStateChange): Host
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
     * @return Host
     */
    public function setLastNotification(?\DateTime $lastNotification): Host
    {
        $this->lastNotification = $lastNotification;
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
     * @return Host
     */
    public function setLastStateChange(?\DateTime $lastStateChange): Host
    {
        $this->lastStateChange = $lastStateChange;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTimeDown(): ?\DateTime
    {
        return $this->lastTimeDown;
    }

    /**
     * @param \DateTime|null $lastTimeDown
     * @return Host
     */
    public function setLastTimeDown(?\DateTime $lastTimeDown): Host
    {
        $this->lastTimeDown = $lastTimeDown;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTimeUnreachable(): ?\DateTime
    {
        return $this->lastTimeUnreachable;
    }

    /**
     * @param \DateTime|null $lastTimeUnreachable
     * @return Host
     */
    public function setLastTimeUnreachable(?\DateTime $lastTimeUnreachable): Host
    {
        $this->lastTimeUnreachable = $lastTimeUnreachable;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTimeUp(): ?\DateTime
    {
        return $this->lastTimeUp;
    }

    /**
     * @param \DateTime|null $lastTimeUp
     * @return Host
     */
    public function setLastTimeUp(?\DateTime $lastTimeUp): Host
    {
        $this->lastTimeUp = $lastTimeUp;
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
     * @return Host
     */
    public function setLastUpdate(?\DateTime $lastUpdate): Host
    {
        $this->lastUpdate = $lastUpdate;
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
     * @return Host
     */
    public function setLatency(?float $latency): Host
    {
        $this->latency = $latency;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxCheckAttempts(): ?int
    {
        return $this->maxCheckAttempts;
    }

    /**
     * @param int|null $maxCheckAttempts
     * @return Host
     */
    public function setMaxCheckAttempts(?int $maxCheckAttempts): Host
    {
        $this->maxCheckAttempts = $maxCheckAttempts;
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
     * @return Host
     */
    public function setNextCheck(?\DateTime $nextCheck): Host
    {
        $this->nextCheck = $nextCheck;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNextHostNotification(): ?int
    {
        return $this->nextHostNotification;
    }

    /**
     * @param int|null $nextHostNotification
     * @return Host
     */
    public function setNextHostNotification(?int $nextHostNotification): Host
    {
        $this->nextHostNotification = $nextHostNotification;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getNotificationInterval(): ?float
    {
        return $this->notificationInterval;
    }

    /**
     * @param float|null $notificationInterval
     * @return Host
     */
    public function setNotificationInterval(?float $notificationInterval): Host
    {
        $this->notificationInterval = $notificationInterval;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNotificationNumber(): ?int
    {
        return $this->notificationNumber;
    }

    /**
     * @param int|null $notificationNumber
     * @return Host
     */
    public function setNotificationNumber(?int $notificationNumber): Host
    {
        $this->notificationNumber = $notificationNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotificationPeriod(): ?string
    {
        return $this->notificationPeriod;
    }

    /**
     * @param string|null $notificationPeriod
     * @return Host
     */
    public function setNotificationPeriod(?string $notificationPeriod): Host
    {
        $this->notificationPeriod = $notificationPeriod;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getNotify(): ?bool
    {
        return $this->notify;
    }

    /**
     * @param bool|null $notify
     * @return Host
     */
    public function setNotify(?bool $notify): Host
    {
        $this->notify = $notify;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getNotifyOnDown(): ?bool
    {
        return $this->notifyOnDown;
    }

    /**
     * @param bool|null $notifyOnDown
     * @return Host
     */
    public function setNotifyOnDown(?bool $notifyOnDown): Host
    {
        $this->notifyOnDown = $notifyOnDown;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getNotifyOnDowntime(): ?bool
    {
        return $this->notifyOnDowntime;
    }

    /**
     * @param bool|null $notifyOnDowntime
     * @return Host
     */
    public function setNotifyOnDowntime(?bool $notifyOnDowntime): Host
    {
        $this->notifyOnDowntime = $notifyOnDowntime;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getNotifyOnFlapping(): ?bool
    {
        return $this->notifyOnFlapping;
    }

    /**
     * @param bool|null $notifyOnFlapping
     * @return Host
     */
    public function setNotifyOnFlapping(?bool $notifyOnFlapping): Host
    {
        $this->notifyOnFlapping = $notifyOnFlapping;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getNotifyOnRecovery(): ?bool
    {
        return $this->notifyOnRecovery;
    }

    /**
     * @param bool|null $notifyOnRecovery
     * @return Host
     */
    public function setNotifyOnRecovery(?bool $notifyOnRecovery): Host
    {
        $this->notifyOnRecovery = $notifyOnRecovery;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getNotifyOnUnreachable(): ?bool
    {
        return $this->notifyOnUnreachable;
    }

    /**
     * @param bool|null $notifyOnUnreachable
     * @return Host
     */
    public function setNotifyOnUnreachable(?bool $notifyOnUnreachable): Host
    {
        $this->notifyOnUnreachable = $notifyOnUnreachable;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * @param string|null $output
     * @return Host
     */
    public function setOutput(?string $output): Host
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getPassiveChecks(): ?bool
    {
        return $this->passiveChecks;
    }

    /**
     * @param bool|null $passiveChecks
     * @return Host
     */
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
     * @return Host
     */
    public function setServices(array $services): Host
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @param Service $service
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * @return int|null
     */
    public function getState(): ?int
    {
        return $this->state;
    }

    /**
     * @param int|null $state
     * @return Host
     */
    public function setState(?int $state): Host
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getStateType(): ?int
    {
        return $this->stateType;
    }

    /**
     * @param int|null $stateType
     * @return Host
     */
    public function setStateType(?int $stateType): Host
    {
        $this->stateType = $stateType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * @param string|null $timezone
     * @return Host
     */
    public function setTimezone(?string $timezone): Host
    {
        $this->timezone = $timezone;
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
     * @param int|null $scheduledDowntimeDepth
     * @return Host
     */
    public function setScheduledDowntimeDepth(?int $scheduledDowntimeDepth): Host
    {
        $this->scheduledDowntimeDepth = $scheduledDowntimeDepth;
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
     * @return Host
     */
    public function setCriticality(?int $criticality): Host
    {
        $this->criticality = $criticality;
        return $this;
    }
}

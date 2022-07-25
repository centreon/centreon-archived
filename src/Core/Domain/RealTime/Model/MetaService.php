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

namespace Core\Domain\RealTime\Model;

use Core\Domain\RealTime\Model\ServiceStatus;
use Centreon\Domain\Common\Assertion\Assertion;

class MetaService
{
    final public const MAX_NAME_LENGTH = 255;

    private bool $isInDowntime = false;

    private bool $isAcknowledged = false;

    private bool $isNotificationEnabled = false;

    private ?int $notificationNumber = null;

    private ?string $commandLine = null;

    private ?string $performanceData = null;

    private ?string $output = null;

    private ?\DateTime $lastStatusChange = null;

    private ?\DateTime $lastNotification = null;

    private ?float $latency = null;

    private ?float $executionTime = null;

    private ?float $statusChangePercentage = null;

    private ?\DateTime $nextCheck = null;

    private ?\DateTime $lastCheck = null;

    private bool $activeChecks = true;

    private bool $passiveChecks = false;

    private ?\DateTime $lastTimeOk = null;

    private ?int $maxCheckAttempts = null;

    private ?int $checkAttempts = null;

    private bool $isFlapping = false;

    private bool $hasGraphData = false;

    /**
     * @param int $id
     * @param int $hostId
     * @param int $serviceId
     * @param string $name
     * @param ServiceStatus $status
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(
        private readonly int $id,
        private readonly int $hostId,
        private readonly int $serviceId,
        private readonly string $name,
        private readonly string $monitoringServerName,
        private readonly ServiceStatus $status
    ) {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'MetaService::name');
        Assertion::notEmpty($name, 'MetaService::name');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): ServiceStatus
    {
        return $this->status;
    }

    public function isFlapping(): bool
    {
        return $this->isFlapping;
    }

    public function setIsFlapping(bool $isFlapping): self
    {
        $this->isFlapping = $isFlapping;
        return $this;
    }

    public function isAcknowledged(): bool
    {
        return $this->isAcknowledged;
    }

    public function setIsAcknowledged(bool $isAcknowledged): self
    {
        $this->isAcknowledged = $isAcknowledged;
        return $this;
    }

    public function setIsInDowntime(bool $isInDowntime): self
    {
        $this->isInDowntime = $isInDowntime;
        return $this;
    }

    public function isInDowntime(): bool
    {
        return $this->isInDowntime;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): self
    {
        $this->output = $output;
        return $this;
    }

    public function setPerformanceData(?string $performanceData): self
    {
        $this->performanceData = $performanceData;
        return $this;
    }

    public function getPerformanceData(): ?string
    {
        return $this->performanceData;
    }

    public function getCommandLine(): ?string
    {
        return $this->commandLine;
    }

    public function setCommandLine(?string $commandLine): self
    {
        $this->commandLine = $commandLine;
        return $this;
    }

    public function isNotificationEnabled(): bool
    {
        return $this->isNotificationEnabled;
    }

    public function setNotificationEnabled(bool $isNotificationEnabled): self
    {
        $this->isNotificationEnabled = $isNotificationEnabled;

        return $this;
    }

    public function getNotificationNumber(): ?int
    {
        return $this->notificationNumber;
    }

    public function setNotificationNumber(?int $notificationNumber): self
    {
        $this->notificationNumber = $notificationNumber;
        return $this;
    }

    public function getLastStatusChange(): ?\DateTime
    {
        return $this->lastStatusChange;
    }

    public function setLastStatusChange(?\DateTime $lastStatusChange): self
    {
        $this->lastStatusChange = $lastStatusChange;
        return $this;
    }

    public function getLastNotification(): ?\DateTime
    {
        return $this->lastNotification;
    }

    public function setLastNotification(?\DateTime $lastNotification): self
    {
        $this->lastNotification = $lastNotification;
        return $this;
    }

    public function getLatency(): ?float
    {
        return $this->latency;
    }

    public function setLatency(?float $latency): self
    {
        $this->latency = $latency;
        return $this;
    }

    public function setExecutionTime(?float $executionTime): self
    {
        $this->executionTime = $executionTime;
        return $this;
    }

    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
    }

    public function setStatusChangePercentage(?float $statusChangePercentage): self
    {
        $this->statusChangePercentage = $statusChangePercentage;
        return $this;
    }

    public function getStatusChangePercentage(): ?float
    {
        return $this->statusChangePercentage;
    }

    public function getNextCheck(): ?\DateTime
    {
        return $this->nextCheck;
    }

    public function setNextCheck(?\DateTime $nextCheck): self
    {
        $this->nextCheck = $nextCheck;
        return $this;
    }

    public function getLastCheck(): ?\DateTime
    {
        return $this->lastCheck;
    }

    public function setLastCheck(?\DateTime $lastCheck): self
    {
        $this->lastCheck = $lastCheck;
        return $this;
    }

    public function setActiveChecks(bool $activeChecks): self
    {
        $this->activeChecks = $activeChecks;
        return $this;
    }

    public function hasActiveChecks(): bool
    {
        return $this->activeChecks;
    }

    public function setPassiveChecks(bool $passiveChecks): self
    {
        $this->passiveChecks = $passiveChecks;
        return $this;
    }

    public function hasPassiveChecks(): bool
    {
        return $this->passiveChecks;
    }

    public function setLastTimeOk(?\DateTime $lastTimeOk): self
    {
        $this->lastTimeOk = $lastTimeOk;
        return $this;
    }

    public function getLastTimeOk(): ?\DateTime
    {
        return $this->lastTimeOk;
    }

    public function getMaxCheckAttempts(): ?int
    {
        return $this->maxCheckAttempts;
    }

    public function setMaxCheckAttempts(?int $maxCheckAttempts): self
    {
        $this->maxCheckAttempts = $maxCheckAttempts;
        return $this;
    }

    public function setCheckAttempts(?int $checkAttempts): self
    {
        $this->checkAttempts = $checkAttempts;
        return $this;
    }

    public function getCheckAttempts(): ?int
    {
        return $this->checkAttempts;
    }

    public function getHostId(): int
    {
        return $this->hostId;
    }

    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    public function getMonitoringServerName(): string
    {
        return $this->monitoringServerName;
    }

    public function hasGraphData(): bool
    {
        return $this->hasGraphData;
    }

    public function setHasGraphData(bool $hasGraphData): self
    {
        $this->hasGraphData = $hasGraphData;
        return $this;
    }
}

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

namespace Core\Domain\RealTime\Model;

use Core\Domain\RealTime\Model\Host;
use Core\Domain\RealTime\Model\Icon;
use Core\Domain\RealTime\Model\Servicegroup;
use Core\Domain\RealTime\Model\ServiceStatus;
use Centreon\Domain\Common\Assertion\Assertion;

class Service
{
    public const MAX_NAME_LENGTH = 255;

    /**
     * @var Servicegroup[]
     */
    private $servicegroups = [];

    /**
     * @var boolean
     */
    private $isInDowntime = false;

    /**
     * @var boolean
     */
    private $isAcknowledged = false;
    /**
     * @var int|null
     */
    private $notificationNumber;

    /**
     * @var string|null
     */
    private $commandLine;

    /**
     * @var string|null
     */
    private $performanceData;

    /**
     * @var string|null
     */
    private $output;

    /**
     * @var \DateTime|null
     */
    private $lastStatusChange;

    /**
     * @var \DateTime|null
     */
    private $lastNotification;

    /**
     * @var float|null
     */
    private $latency;

    /**
     * @var float|null
     */
    private $executionTime;

    /**
     * @var float|null
     */
    private $statusChangePercentage;

    /**
     * @var \DateTime|null
     */
    private $nextCheck;

    /**
     * @var \DateTime|null
     */
    private $lastCheck;

    /**
     * @var bool
     */
    private $activeChecks = true;

    /**
     * @var bool
     */
    private $passiveChecks = false;

    /**
     * @var \DateTime|null
     */
    private $lastTimeOk;

    /**
     * @var int|null
     */
    private $severityLevel;

    /**
     * @var Icon|null
     */
    private $icon;

    /**
     * @var int|null
     */
    private $maxCheckAttempts;

    /**
     * @var int|null
     */
    private $checkAttempts;

    /**
     * @var boolean
     */
    private $isFlapping = false;

    /**
     * @param int $id
     * @param int $hostId
     * @param string $name
     * @param ServiceStatus $status
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(
        private int $id,
        private int $hostId,
        private string $name,
        private ServiceStatus $status
    ) {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'Service::name');
        Assertion::notEmpty($name, 'Service::name');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Servicegroup[]
     */
    public function getServicegroups(): array
    {
        return $this->servicegroups;
    }

    /**
     * @param Servicegroup $servicegroup
     * @return self
     */
    public function addServicegroup(Servicegroup $servicegroup): self
    {
        $this->servicegroups[] = $servicegroup;
        return $this;
    }

    /**
     * @return ServiceStatus
     */
    public function getStatus(): ServiceStatus
    {
        return $this->status;
    }

    /**
     * @return boolean
     */
    public function isFlapping(): bool
    {
        return $this->isFlapping;
    }

    /**
     * @param boolean $isFlapping
     * @return self
     */
    public function setIsFlapping(bool $isFlapping): self
    {
        $this->isFlapping = $isFlapping;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAcknowledged(): bool
    {
        return $this->isAcknowledged;
    }

    /**
     * @param boolean $isAcknowledged
     * @return self
     */
    public function setIsAcknowledged(bool $isAcknowledged): self
    {
        $this->isAcknowledged = $isAcknowledged;
        return $this;
    }

    /**
     * @param boolean $isInDowntime
     * @return self
     */
    public function setIsInDowntime(bool $isInDowntime): self
    {
        $this->isInDowntime = $isInDowntime;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isInDowntime(): bool
    {
        return $this->isInDowntime;
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
     * @return self
     */
    public function setOutput(?string $output): self
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @param string|null $performanceData
     * @return self
     */
    public function setPerformanceData(?string $performanceData): self
    {
        $this->performanceData = $performanceData;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPerformanceData(): ?string
    {
        return $this->performanceData;
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
     * @return int|null
     */
    public function getNotificationNumber(): ?int
    {
        return $this->notificationNumber;
    }

    /**
     * @param int|null $notificationNumber
     * @return self
     */
    public function setNotificationNumber(?int $notificationNumber): self
    {
        $this->notificationNumber = $notificationNumber;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastStatusChange(): ?\DateTime
    {
        return $this->lastStatusChange;
    }

    /**
     * @param \DateTime|null $lastStatusChange
     * @return self
     */
    public function setLastStatusChange(?\DateTime $lastStatusChange): self
    {
        $this->lastStatusChange = $lastStatusChange;
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
     * @param float|null $executionTime
     * @return self
     */
    public function setExecutionTime(?float $executionTime): self
    {
        $this->executionTime = $executionTime;
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
     * @param float|null $statusChangePercentage
     * @return self
     */
    public function setStatusChangePercentage(?float $statusChangePercentage): self
    {
        $this->statusChangePercentage = $statusChangePercentage;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getStatusChangePercentage(): ?float
    {
        return $this->statusChangePercentage;
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
     * @param boolean $activeChecks
     * @return self
     */
    public function setActiveChecks(bool $activeChecks): self
    {
        $this->activeChecks = $activeChecks;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasActiveChecks(): bool
    {
        return $this->activeChecks;
    }

    /**
     * @param boolean $passiveChecks
     * @return self
     */
    public function setPassiveChecks(bool $passiveChecks): self
    {
        $this->passiveChecks = $passiveChecks;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasPassiveChecks(): bool
    {
        return $this->passiveChecks;
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
    public function getLastTimeOk(): ?\DateTime
    {
        return $this->lastTimeOk;
    }

    /**
     * @param int|null $severityLevel
     * @return self
     */
    public function setSeverityLevel(?int $severityLevel): self
    {
        $this->severityLevel = $severityLevel;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSeverityLevel(): ?int
    {
        return $this->severityLevel;
    }

    /**
     *
     * @param ?Icon $icon
     * @return self
     */
    public function setIcon(?Icon $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return Icon|null
     */
    public function getIcon(): ?Icon
    {
        return $this->icon;
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
     * @return self
     */
    public function setMaxCheckAttempts(?int $maxCheckAttempts): self
    {
        $this->maxCheckAttempts = $maxCheckAttempts;
        return $this;
    }

    /**
     * @param int|null $checkAttempts
     * @return self
     */
    public function setCheckAttempts(?int $checkAttempts): self
    {
        $this->checkAttempts = $checkAttempts;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCheckAttempts(): ?int
    {
        return $this->checkAttempts;
    }

    public function getHostId(): int
    {
        return $this->hostId;
    }
}

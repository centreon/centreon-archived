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

namespace Centreon\Domain\Monitoring;

use CentreonDuration;
use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\ResourceGroup;
use Centreon\Domain\Monitoring\ResourceLinks;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Acknowledgement\Acknowledgement;

/**
 * Class representing a record of a resource in the repository.
 *
 * @package Centreon\Domain\Monitoring
 */
class Resource
{
    // Groups for serialization
    final public const SERIALIZER_GROUP_MAIN = 'resource_main';
    final public const SERIALIZER_GROUP_PARENT = 'resource_parent';

    // Groups for validation
    final public const VALIDATION_GROUP_ACK_HOST = ['ack_host'];
    final public const VALIDATION_GROUP_ACK_SERVICE = ['ack_service'];
    final public const VALIDATION_GROUP_ACK_META = ['ack_meta'];
    final public const VALIDATION_GROUP_DISACK_HOST = ['disack_host'];
    final public const VALIDATION_GROUP_DISACK_SERVICE = ['disack_service'];
    final public const VALIDATION_GROUP_DOWNTIME_HOST = ['downtime_host'];
    final public const VALIDATION_GROUP_DOWNTIME_META = ['downtime_meta'];
    final public const VALIDATION_GROUP_DOWNTIME_SERVICE = ['downtime_service'];

    // Types
    final public const TYPE_SERVICE = 'service';
    final public const TYPE_HOST = 'host';
    final public const TYPE_META = 'metaservice';

    private ?int $id = null;

    private ?string $type = null;

    private ?string $name = null;

    private ?string $alias = null;

    private ?string $fqdn = null;

    private ?int $hostId = null;

    private ?int $serviceId = null;

    private ?\Centreon\Domain\Monitoring\Icon $icon = null;

    /**
     * @var string|null
     */
    protected $commandLine;

    private ?string $monitoringServerName = null;

    private ?string $timezone = null;

    private ?\Centreon\Domain\Monitoring\Resource $parent = null;

    private ?\Centreon\Domain\Monitoring\ResourceStatus $status = null;

    private ?bool $flapping = null;

    private ?float $percentStateChange = null;

    /**
     * @var int|null
     */
    protected $criticality;

    private bool $inDowntime = false;

    private bool $acknowledged = false;

    private bool $activeChecks = true;

    private bool $passiveChecks = false;

    private \Centreon\Domain\Monitoring\ResourceLinks $links;

    private ?int $severityLevel = null;

    /**
     * @var string|null
     */
    private $chartUrl;

    private ?\DateTime $lastStatusChange = null;

    private ?\DateTime $lastTimeWithNoIssue = null;

    private ?\DateTime $lastNotification = null;

    private ?int $notificationNumber = null;

    private ?int $stateType = null;

    private ?string $tries = null;

    private ?\DateTime $lastCheck = null;

    private ?\DateTime $nextCheck = null;

    private ?string $information = null;

    private ?string $performanceData = null;

    private ?float $executionTime = null;

    private ?float $latency = null;

    /**
     * @var Downtime[]
     */
    private array $downtimes = [];

    private ?\Centreon\Domain\Acknowledgement\Acknowledgement $acknowledgement = null;

    /**
     * Groups to which belongs the resource
     *
     * @var ResourceGroup[]
     */
    private array $groups = [];

    /**
     * Calculation type of the Resource
     */
    private ?string $calculationType = null;

    /**
     * Indicates if notifications are enabled for the Resource
     */
    private bool $notificationEnabled = false;

    private bool $hasGraph = false;

    /**
     * Resource constructor.
     */
    public function __construct()
    {
        $this->links = new ResourceLinks();
    }

    public function getShortType(): ?string
    {
        return $this->type ? $this->type[0] : null;
    }

    public function getDuration(): ?string
    {
        $result = null;

        if ($this->getLastStatusChange() !== null) {
            $result = CentreonDuration::toString(time() - $this->getLastStatusChange()->getTimestamp());
        }

        return $result;
    }

    public function getLastCheckAsString(): ?string
    {
        $result = null;

        if ($this->getLastCheck() !== null) {
            $result = CentreonDuration::toString(time() - $this->getLastCheck()->getTimestamp());
        }

        return $result;
    }

    public function getUuid(): string
    {
        $uuid = '';

        if ($this->getShortType() !== null && $this->getId() !== null) {
            $uuid = $this->getShortType() . $this->getId();
        }

        if ($this->getParent() !== null) {
            $uuid = $this->getParent()->getUuid() . '-' . $uuid;
        }

        return $uuid;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    public function getFqdn(): ?string
    {
        return $this->fqdn;
    }

    public function getHostId(): ?int
    {
        return $this->hostId;
    }

    public function setHostId(?int $hostId): self
    {
        $this->hostId = $hostId;

        return $this;
    }

    public function getServiceId(): ?int
    {
        return $this->serviceId;
    }

    public function setServiceId(?int $serviceId): self
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    public function setFqdn(?string $fqdn): self
    {
        $this->fqdn = $fqdn;

        return $this;
    }

    public function getIcon(): ?Icon
    {
        return $this->icon;
    }

    public function setIcon(?Icon $icon): self
    {
        $this->icon = $icon;

        return $this;
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

    public function getMonitoringServerName(): string
    {
        return $this->monitoringServerName;
    }

   public function setMonitoringServerName(string $monitoringServerName): self
    {
        $this->monitoringServerName = $monitoringServerName;
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

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getParent(): ?Resource
    {
        return $this->parent;
    }

    public function setParent(?Resource $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getStatus(): ?ResourceStatus
    {
        return $this->status;
    }

    public function setStatus(?ResourceStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFlapping(): ?bool
    {
        return $this->flapping;
    }

    public function setFlapping(?bool $flapping): self
    {
        $this->flapping = $flapping;
        return $this;
    }

    public function getPercentStateChange(): ?float
    {
        return $this->percentStateChange;
    }

    public function setPercentStateChange(?float $percentStateChange): self
    {
        $this->percentStateChange = $percentStateChange;
        return $this;
    }

    public function getCriticality(): ?int
    {
        return $this->criticality;
    }

    public function setCriticality(?int $criticality): self
    {
        $this->criticality = $criticality;
        return $this;
    }

    public function getInDowntime(): bool
    {
        return $this->inDowntime;
    }

    public function setInDowntime(bool $inDowntime): self
    {
        $this->inDowntime = $inDowntime;
        return $this;
    }

    public function getAcknowledged(): bool
    {
        return $this->acknowledged;
    }

    public function setAcknowledged(bool $acknowledged): self
    {
        $this->acknowledged = $acknowledged;
        return $this;
    }

    public function getActiveChecks(): bool
    {
        return $this->activeChecks;
    }

    public function setActiveChecks(bool $activeChecks): self
    {
        $this->activeChecks = $activeChecks;
        return $this;
    }

    public function getPassiveChecks(): bool
    {
        return $this->passiveChecks;
    }

    public function setPassiveChecks(bool $passiveChecks): self
    {
        $this->passiveChecks = $passiveChecks;
        return $this;
    }

    public function getLinks(): ResourceLinks
    {
        return $this->links;
    }

    public function setLinks(ResourceLinks $links): self
    {
        $this->links = $links;
        return $this;
    }

    public function getSeverityLevel(): ?int
    {
        return $this->severityLevel;
    }

    public function setSeverityLevel(?int $severityLevel): self
    {
        $this->severityLevel = $severityLevel;

        return $this;
    }

    public function getChartUrl(): ?string
    {
        return $this->chartUrl;
    }

    public function setChartUrl(?string $chartUrl): self
    {
        $this->chartUrl = $chartUrl ?: null;

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

   public function getLastTimeWithNoIssue(): ?\DateTime
    {
        return $this->lastTimeWithNoIssue;
    }

    public function setLastTimeWithNoIssue(?\DateTime $lastTimeWithNoIssue): self
    {
        $this->lastTimeWithNoIssue = $lastTimeWithNoIssue;
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

    public function getNotificationNumber(): ?int
    {
        return $this->notificationNumber;
    }

    public function setNotificationNumber(?int $notificationNumber): self
    {
        $this->notificationNumber = $notificationNumber;
        return $this;
    }

    public function getStateType(): int
    {
        return $this->stateType;
    }

    public function setStateType(int $stateType): self
    {
        $this->stateType = $stateType;
        return $this;
    }

    public function getTries(): ?string
    {
        return $this->tries;
    }

    public function setTries(?string $tries): self
    {
        $this->tries = $tries;

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

    public function getNextCheck(): ?\DateTime
    {
        return $this->nextCheck;
    }

    public function setNextCheck(?\DateTime $nextCheck): self
    {
        $this->nextCheck = $nextCheck;

        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): self
    {
        $this->information = $information !== null ? trim($information) : null;

        return $this;
    }

    public function getPerformanceData(): string
    {
        return $this->performanceData;
    }

    public function setPerformanceData(string $performanceData): self
    {
        $this->performanceData = $performanceData;
        return $this;
    }

    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
    }

    public function setExecutionTime(?float $executionTime): self
    {
        $this->executionTime = $executionTime;
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


    /**
     * Get groups to which belongs the resource.
     *
     * @return ResourceGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Set groups to which belongs the resource
     *
     * @param ResourceGroup[] $groups
     * @throws \InvalidArgumentException
     */
    public function setGroups(array $groups): self
    {
        foreach ($groups as $group) {
            if (!($group instanceof ResourceGroup)) {
                throw new \InvalidArgumentException(_('One of the elements provided is not a ResourceGroup type'));
            }
        }
        $this->groups = $groups;

        return $this;
    }

    public function setCalculationType(?string $calculationType): self
    {
        $this->calculationType = $calculationType;
        return $this;
    }

    public function getCalculationType(): ?string
    {
        return $this->calculationType;
    }

    /*
     * @return boolean
     */
    public function isNotificationEnabled(): bool
    {
        return $this->notificationEnabled;
    }

    public function setNotificationEnabled(bool $notificationEnabled): self
    {
        $this->notificationEnabled = $notificationEnabled;

        return $this;
    }

    public function setHasGraph(bool $hasGraph): self
    {
        $this->hasGraph = $hasGraph;
        return $this;
    }

    public function hasGraph(): bool
    {
        return $this->hasGraph;
    }
}

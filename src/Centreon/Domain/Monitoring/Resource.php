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
    public const SERIALIZER_GROUP_MAIN = 'resource_main';
    public const SERIALIZER_GROUP_PARENT = 'resource_parent';

    // Groups for validation
    public const VALIDATION_GROUP_ACK_HOST = ['ack_host'];
    public const VALIDATION_GROUP_ACK_SERVICE = ['ack_service'];
    public const VALIDATION_GROUP_ACK_META = ['ack_meta'];
    public const VALIDATION_GROUP_DISACK_HOST = ['disack_host'];
    public const VALIDATION_GROUP_DISACK_SERVICE = ['disack_service'];
    public const VALIDATION_GROUP_DOWNTIME_HOST = ['downtime_host'];
    public const VALIDATION_GROUP_DOWNTIME_META = ['downtime_meta'];
    public const VALIDATION_GROUP_DOWNTIME_SERVICE = ['downtime_service'];

    // Types
    public const TYPE_SERVICE = 'service';
    public const TYPE_HOST = 'host';
    public const TYPE_META = 'metaservice';

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $alias;

    /**
     * @var string|null
     */
    private $fqdn;

    /**
     * @var int|null
     */
    private $hostId;

    /**
     * @var int|null
     */
    private $serviceId;

    /**
     * @var \Centreon\Domain\Monitoring\Icon|null
     */
    private $icon;

    /**
     * @var string|null
     */
    protected $commandLine;

    /**
     * @var string
     */
    private $monitoringServerName;

    /**
     * @var string|null
     */
    private $timezone;

    /**
     * @var \Centreon\Domain\Monitoring\Resource|null
     */
    private $parent;

    /**
     * @var \Centreon\Domain\Monitoring\ResourceStatus|null
     */
    private $status;

    /**
     * @var bool|null
     */
    private $flapping;

    /**
     * @var double|null
     */
    private $percentStateChange;

    /**
     * @var int|null
     */
    protected $criticality;

    /**
     * @var bool
     */
    private $inDowntime = false;

    /**
     * @var bool
     */
    private $acknowledged = false;

    /**
     * @var bool|null
     */
    private $activeChecks;

    /**
     * @var bool|null
     */
    private $passiveChecks;

    /**
     * @var ResourceLinks
     */
    private $links;

    /**
     * @var int|null
     */
    private $severityLevel;

    /**
     * @var string|null
     */
    private $chartUrl;

    /**
     * @var \DateTime|null
     */
    private $lastStatusChange;

    /**
     * @var \DateTime|null
     */
    private $lastTimeWithNoIssue;

    /**
     * @var \DateTime|null
     */
    private $lastNotification;

    /**
     * @var int|null
     */
    private $notificationNumber;

    /**
     * @var int
     */
    private $stateType;

    /**
     * @var string|null
     */
    private $tries;

    /**
     * @var \DateTime|null
     */
    private $lastCheck;

    /**
     * @var \DateTime|null
     */
    private $nextCheck;

    /**
     * @var string|null
     */
    private $information;

    /**
     * @var string
     */
    private $performanceData;

    /**
     * @var double|null
     */
    private $executionTime;

    /**
     * @var double|null
     */
    private $latency;

    /**
     * @var Downtime[]
     */
    private $downtimes = [];

    /**
     * @var Acknowledgement|null
     */
    private $acknowledgement;

    /**
     * Groups to which belongs the resource
     *
     * @var ResourceGroup[]
     */
    private $groups = [];

    /**
     * Calculation type of the Resource
     *
     * @var string|null
     */
    private $calculationType;

    /**
     * Indicates if notifications are enabled for the Resource
     *
     * @var bool
     */
    private $notificationEnabled = false;

    /**
     * Resource constructor.
     */
    public function __construct()
    {
        $this->links = new ResourceLinks();
    }

    /**
     * @return string|null
     */
    public function getShortType(): ?string
    {
        return $this->type ? $this->type[0] : null;
    }

    /**
     * @return string|null
     */
    public function getDuration(): ?string
    {
        $result = null;

        if ($this->getLastStatusChange() !== null) {
            $result = CentreonDuration::toString(time() - $this->getLastStatusChange()->getTimestamp());
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getLastCheckAsString(): ?string
    {
        $result = null;

        if ($this->getLastCheck() !== null) {
            $result = CentreonDuration::toString(time() - $this->getLastCheck()->getTimestamp());
        }

        return $result;
    }

    /**
     * @return string
     */
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

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

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
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFqdn(): ?string
    {
        return $this->fqdn;
    }

    /**
     * @return int|null
     */
    public function getHostId(): ?int
    {
        return $this->hostId;
    }

    /**
     * @param int|null $hostId
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setHostId(?int $hostId): self
    {
        $this->hostId = $hostId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getServiceId(): ?int
    {
        return $this->serviceId;
    }

    /**
     * @param int|null $serviceId
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setServiceId(?int $serviceId): self
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * @param string|null $fqdn
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setFqdn(?string $fqdn): self
    {
        $this->fqdn = $fqdn;

        return $this;
    }

    /**
     * @return \Centreon\Domain\Monitoring\Icon|null
     */
    public function getIcon(): ?Icon
    {
        return $this->icon;
    }

    /**
     * @param \Centreon\Domain\Monitoring\Icon|null $icon
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setIcon(?Icon $icon): self
    {
        $this->icon = $icon;

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
    public function getMonitoringServerName(): string
    {
        return $this->monitoringServerName;
    }

   /**
     * @param string $monitoringServerName
     * @return self
     */
    public function setMonitoringServerName(string $monitoringServerName): self
    {
        $this->monitoringServerName = $monitoringServerName;
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
     * @return null|string
     */
    public function getSanitizedTimezone(): ?string
    {
        return (null !== $this->timezone) ?
            preg_replace('/^:/', '', $this->timezone) :
            $this->timezone;
    }

    /**
     * @param string|null $timezone
     * @return self
     */
    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return \Centreon\Domain\Monitoring\Resource|null
     */
    public function getParent(): ?Resource
    {
        return $this->parent;
    }

    /**
     * @param \Centreon\Domain\Monitoring\Resource|null $parent
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setParent(?Resource $parent): self
    {
        $this->parent = $parent;

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
     * @return float|null
     */
    public function getPercentStateChange(): ?float
    {
        return $this->percentStateChange;
    }

    /**
     * @param float|null $percentStateChange
     * @return self
     */
    public function setPercentStateChange(?float $percentStateChange): self
    {
        $this->percentStateChange = $percentStateChange;
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
     * @return bool
     */
    public function getInDowntime(): bool
    {
        return $this->inDowntime;
    }

    /**
     * @param bool $inDowntime
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setInDowntime(bool $inDowntime): self
    {
        $this->inDowntime = $inDowntime;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAcknowledged(): bool
    {
        return $this->acknowledged;
    }

    /**
     * @param bool $acknowledged
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setAcknowledged(bool $acknowledged): self
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
     * @return self
     */
    public function setActiveChecks(?bool $activeChecks): self
    {
        $this->activeChecks = $activeChecks;
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
     * @return self
     */
    public function setPassiveChecks(?bool $passiveChecks): self
    {
        $this->passiveChecks = $passiveChecks;
        return $this;
    }

    /**
     * @return ResourceLinks
     */
    public function getLinks(): ResourceLinks
    {
        return $this->links;
    }

    /**
     * @param ResourceLinks $links
     * @return self
     */
    public function setLinks(ResourceLinks $links): self
    {
        $this->links = $links;
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
     * @param int|null $severityLevel
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setSeverityLevel(?int $severityLevel): self
    {
        $this->severityLevel = $severityLevel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getChartUrl(): ?string
    {
        return $this->chartUrl;
    }

    /**
     * @param string|null $chartUrl
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setChartUrl(?string $chartUrl): self
    {
        $this->chartUrl = $chartUrl ?: null;

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
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setLastStatusChange(?\DateTime $lastStatusChange): self
    {
        $this->lastStatusChange = $lastStatusChange;

        return $this;
    }

   /**
     * @return \DateTime|null
     */
    public function getLastTimeWithNoIssue(): ?\DateTime
    {
        return $this->lastTimeWithNoIssue;
    }

    /**
     * @param \DateTime|null $lastTimeWithNoIssue
     * @return self
     */
    public function setLastTimeWithNoIssue(?\DateTime $lastTimeWithNoIssue): self
    {
        $this->lastTimeWithNoIssue = $lastTimeWithNoIssue;
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
     * @return integer
     */
    public function getStateType(): int
    {
        return $this->stateType;
    }

    /**
     * @param integer $stateType
     * @return self
     */
    public function setStateType(int $stateType): self
    {
        $this->stateType = $stateType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTries(): ?string
    {
        return $this->tries;
    }

    /**
     * @param string|null $tries
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setTries(?string $tries): self
    {
        $this->tries = $tries;

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
     * @return \Centreon\Domain\Monitoring\Resource
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
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setNextCheck(?\DateTime $nextCheck): self
    {
        $this->nextCheck = $nextCheck;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInformation(): ?string
    {
        return $this->information;
    }

    /**
     * @param string|null $information
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setInformation(?string $information): self
    {
        $this->information = $information !== null ? trim($information) : null;

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
     * @return self
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

    /**
     * @param string|null $calculationType
     * @return self
     */
    public function setCalculationType(?string $calculationType): self
    {
        $this->calculationType = $calculationType;
        return $this;
    }

    /**
     * @return string|null
     */
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

    /**
     * @param boolean $notificationEnabled
     * @return self
     */
    public function setNotificationEnabled(bool $notificationEnabled): self
    {
        $this->notificationEnabled = $notificationEnabled;

        return $this;
    }
}

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

namespace Centreon\Domain\Monitoring\MonitoringResource\Model;

use CentreonDuration;
use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\ResourceGroup;
use Centreon\Domain\Monitoring\ResourceLinks;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Acknowledgement\Acknowledgement;

/**
 * This class is designed to represent a Monitoring Resource.
 *
 * @package Centreon\Domain\Monitoring\MonitoringResource\Model
 */
class MonitoringResource
{
    public const MAX_NAME_LENGTH = 255,
                 MIN_NAME_LENGTH = 1;

    /**
     * Available resource types
     */
    public const TYPE_SERVICE = 'service',
                 TYPE_HOST = 'host',
                 TYPE_META = 'metaservice';
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

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
    private $commandLine;

    /**
     * @var string|null
     */
    private $monitoringServerName;

    /**
     * @var string|null
     */
    private $timezone;

    /**
     * @var \Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource|null
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
     * @var \Centreon\Domain\Monitoring\ResourceLinks
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
    private $lastNotification;

    /**
     * @var int|null
     */
    private $notificationNumber;

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
     * @var string|null
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
     * @var \Centreon\Domain\Downtime\Downtime[]
     */
    private $downtimes = [];

    /**
     * @var \Centreon\Domain\Acknowledgement\Acknowledgement|null
     */
    private $acknowledgement;

    /**
     * Groups to which belongs the resource
     *
     * @var \Centreon\Domain\Monitoring\ResourceGroup[]
     */
    private $groups = [];

    /**
     * @var string|null
     */
    private $calculationType;

    /*
     * @var bool
     */
    private $notificationEnabled = false;

    /**
     * Tells is resource has available graph data
     *
     * @var bool
     */
    private $hasGraphData;

    public function __construct(int $id, string $name, string $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->links = new ResourceLinks();
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

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return self
     * @throws \Assert\AssertionFailedException
     */
    public function setName(string $name): self
    {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'MonitoringResource::name');
        Assertion::minLength($name, self::MIN_NAME_LENGTH, 'MonitoringResource::name');

        $this->name = $name;
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
     * @param string $type
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setType(string $type): self
    {
        if (
            $type !== self::TYPE_HOST &&
            $type !== self::TYPE_SERVICE &&
            $type !== self::TYPE_META
        ) {
            throw new \InvalidArgumentException(
                sprintf(_('Invalid resource type %s'), $type)
            );
        }

        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
        if ($this->getLastStatusChange()) {
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
        if ($this->getLastCheck()) {
            $result = CentreonDuration::toString(time() - $this->getLastCheck()->getTimestamp());
        }

        return $result;
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function setServiceId(?int $serviceId): self
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    /**
     * @param string|null $fqdn
     * @return self
     */
    public function setFqdn(?string $fqdn): self
    {
        $this->fqdn = $fqdn;
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
     * @param Icon|null $icon
     * @return self
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
     * @return string|null
     */
    public function getMonitoringServerName(): ?string
    {
        return $this->monitoringServerName;
    }

   /**
     * @param string|null $monitoringServerName
     * @return self
     */
    public function setMonitoringServerName(?string $monitoringServerName): self
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
     * @return \Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource|null
     */
    public function getParent(): ?MonitoringResource
    {
        return $this->parent;
    }

    /**
     * @param \Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource|null $parent
     * @return self
     */
    public function setParent(?MonitoringResource $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return ResourceStatus|null
     */
    public function getStatus(): ?ResourceStatus
    {
        return $this->status;
    }

    /**
     * @param ResourceStatus|null $status
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return string|null
     */
    public function getTries(): ?string
    {
        return $this->tries;
    }

    /**
     * @param string|null $tries
     * @return self
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
     * @return string|null
     */
    public function getInformation(): ?string
    {
        return $this->information;
    }

    /**
     * @param string|null $information
     * @return self
     */
    public function setInformation(?string $information): self
    {
        $this->information = trim($information);
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
     * @param string|null $performanceData
     * @return self
     */
    public function setPerformanceData(?string $performanceData): self
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
        foreach ($downtimes as $downtime) {
            if (!($downtime instanceof Downtime)) {
                throw new \InvalidArgumentException(_('One of the elements provided is not a Downtime instance'));
            }
        }
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
     * Get groups to which belongs the monitoring resource.
     *
     * @return ResourceGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Set groups to which belongs the monitoring resource
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
     * @return bool
     */
    public function isNotificationEnabled(): bool
    {
        return $this->notificationEnabled;
    }

    /**
     * @param bool $notificationEnabled
     * @return self
     */
    public function setNotificationEnabled(bool $notificationEnabled): self
    {
        $this->notificationEnabled = $notificationEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasGraphData(): bool
    {
        return $this->hasGraphData;
    }

    /**
     * @param bool $hasGraphData
     * @return bool
     */
    public function setHasGraphData(bool $hasGraphData): self
    {
        $this->hasGraphData = $hasGraphData;
        return $this;
    }
}

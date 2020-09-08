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

use Centreon\Domain\Monitoring\Icon;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\ResourceSeverity;
use Centreon\Domain\Monitoring\ResourceLinks;
use DateTime;
use CentreonDuration;

/**
 * Class representing a record of a resource in the repository.
 *
 * @package Centreon\Domain\Monitoring
 */
class Resource
{
    // Groups for serializing
    public const SERIALIZER_GROUP_MAIN = 'resource_main';
    public const SERIALIZER_GROUP_PARENT = 'resource_parent';

    // Groups for validation
    public const VALIDATION_GROUP_ACK_HOST = ['ack_host'];
    public const VALIDATION_GROUP_ACK_SERVICE = ['ack_service'];
    public const VALIDATION_GROUP_DISACK_HOST = ['disack_host'];
    public const VALIDATION_GROUP_DISACK_SERVICE = ['disack_service'];
    public const VALIDATION_GROUP_DOWNTIME_HOST = ['downtime_host'];
    public const VALIDATION_GROUP_DOWNTIME_SERVICE = ['downtime_service'];

    // Types
    public const TYPE_SERVICE = 'service';
    public const TYPE_HOST = 'host';

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
     * @var \Centreon\Domain\Monitoring\Icon|null
     */
    private $icon;

    /**
     * @var string|null
     */
    protected $commandLine;

    /**
     * @var string|null
     */
    private $pollerName;

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
     * @var ResourceLinks
     */
    private $links;

    /**
     * @var string|null
     */
    private $configurationUri;

    /**
     * @var string|null
     */
    private $logsUri;

    /**
     * @var string|null
     */
    private $reportingUri;

    /**
     * @var string|null
     */
    private $downtimeEndpoint;

    /**
     * @var string|null
     */
    private $acknowledgementEndpoint;

    /**
     * @var string|null
     */
    private $detailsEndpoint;

    /**
     * @var string|null
     */
    private $timelineEndpoint;

    /**
     * @var string|null
     */
    private $statusGraphEndpoint;

    /**
     * @var string|null
     */
    private $performanceGraphEndpoint;

    /**
     * @var \Centreon\Domain\Monitoring\ResourceSeverity|null
     */
    private $severity;

    /**
     * @var int
     */
    private $impactedResourcesCount = 0;

    /**
     * @var string|null
     */
    private $actionUrl;

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
     * Resource constructor.
     */
    public function __construct()
    {
        $this->links = new ResourceLinks();
    }

    /**
     * Get prepared list of groups for the context
     *
     * @return array
     */
    public static function contextGroupsForListing(): array
    {
        return [
            static::SERIALIZER_GROUP_MAIN,
            static::SERIALIZER_GROUP_PARENT,
            Icon::SERIALIZER_GROUP_MAIN,
            ResourceStatus::SERIALIZER_GROUP_MAIN,
            ResourceSeverity::SERIALIZER_GROUP_MAIN,
        ];
    }

    /**
     * @return string|null
     */
    public function getShortType(): ?string
    {
        return $this->type ? $this->type{0} : null;
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
     * @return string|null
     */
    public function getPollerName(): ?string
    {
        return $this->pollerName;
    }

    /**
     * @param string|null $pollerName
     * @return self
     */
    public function setPollerName(?string $pollerName): self
    {
        $this->pollerName = $pollerName;

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
     * @return string|null
     */
    public function getConfigurationUri(): ?string
    {
        return $this->configurationUri;
    }

    /**
     * @param string|null $configurationUri
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setConfigurationUri(string $configurationUri): self
    {
        $this->configurationUri = $configurationUri;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogsUri(): ?string
    {
        return $this->logsUri;
    }

    /**
     * @param string|null $logsUri
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setLogsUri(string $logsUri): self
    {
        $this->logsUri = $logsUri;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReportingUri(): ?string
    {
        return $this->reportingUri;
    }

    /**
     * @param string|null $reportingUri
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setReportingUri(string $reportingUri): self
    {
        $this->reportingUri = $reportingUri;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDowntimeEndpoint(): ?string
    {
        return $this->downtimeEndpoint;
    }

    /**
     * @param string $downtimeEndpoint
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setDowntimeEndpoint(string $downtimeEndpoint): self
    {
        $this->downtimeEndpoint = $downtimeEndpoint;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAcknowledgementEndpoint(): ?string
    {
        return $this->acknowledgementEndpoint;
    }

    /**
     * @param string $acknowledgementEndpoint
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setAcknowledgementEndpoint(string $acknowledgementEndpoint): self
    {
        $this->acknowledgementEndpoint = $acknowledgementEndpoint;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDetailsEndpoint(): ?string
    {
        return $this->detailsEndpoint;
    }

    /**
     * @param string|null $detailsEndpoint
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setDetailsEndpoint(?string $detailsEndpoint): self
    {
        $this->detailsEndpoint = $detailsEndpoint ?: null;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimelineEndpoint(): ?string
    {
        return $this->timelineEndpoint;
    }

    /**
     * @param string|null $timelineEndpoint
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setTimelineEndpoint(?string $timelineEndpoint): self
    {
        $this->timelineEndpoint = $timelineEndpoint ?: null;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatusGraphEndpoint(): ?string
    {
        return $this->statusGraphEndpoint;
    }

    /**
     * @param string $statusGraphEndpoint
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setStatusGraphEndpoint(string $statusGraphEndpoint): self
    {
        $this->statusGraphEndpoint = $statusGraphEndpoint;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPerformanceGraphEndpoint(): ?string
    {
        return $this->performanceGraphEndpoint;
    }

    /**
     * @param string $performanceGraphEndpoint
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setPerformanceGraphEndpoint(string $performanceGraphEndpoint): self
    {
        $this->performanceGraphEndpoint = $performanceGraphEndpoint;

        return $this;
    }

    /**
     * @return \Centreon\Domain\Monitoring\ResourceSeverity|null
     */
    public function getSeverity(): ?ResourceSeverity
    {
        return $this->severity;
    }

    /**
     * @param \Centreon\Domain\Monitoring\ResourceSeverity|null $severity
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setSeverity(?ResourceSeverity $severity): self
    {
        $this->severity = $severity;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getImpactedResourcesCount(): ?int
    {
        return $this->impactedResourcesCount;
    }

    /**
     * @param int|null $impactedResourcesCount
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setImpactedResourcesCount(?int $impactedResourcesCount): self
    {
        $this->impactedResourcesCount = $impactedResourcesCount ?: 0;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    /**
     * @param string|null $actionUrl
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setActionUrl(?string $actionUrl): self
    {
        $this->actionUrl = $actionUrl ?: null;

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
    public function getLastStatusChange(): ?DateTime
    {
        return $this->lastStatusChange;
    }

    /**
     * @param \DateTime|null $lastStatusChange
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setLastStatusChange(?DateTime $lastStatusChange): self
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
    public function getLastCheck(): ?DateTime
    {
        return $this->lastCheck;
    }

    /**
     * @param \DateTime|null $lastCheck
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setLastCheck(?DateTime $lastCheck): self
    {
        $this->lastCheck = $lastCheck;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getNextCheck(): ?DateTime
    {
        return $this->nextCheck;
    }

    /**
     * @param \DateTime|null $nextCheck
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setNextCheck(?DateTime $nextCheck): self
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
        $this->information = trim($information);

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
}

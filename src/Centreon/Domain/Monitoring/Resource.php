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

    // Types
    public const TYPE_SERVICE = 'service';
    public const TYPE_HOST = 'host';

    /**
     * @var string|null
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
    private $detailsUrl;

    /**
     * @var \Centreon\Domain\Monitoring\Icon|null
     */
    private $icon;

    /**
     * @var \Centreon\Domain\Monitoring\Resource|null
     */
    private $parent;

    /**
     * @var \Centreon\Domain\Monitoring\ResourceStatus|null
     */
    private $status;

    /**
     * @var bool
     */
    private $inDowntime = false;

    /**
     * @var bool
     */
    private $acknowledged = false;

    /**
     * @var string|null
     */
    private $downtimeEndpoint;

    /**
     * @var string|null
     */
    private $acknowledgementEndpoint;

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
     * @var string|null
     */
    private $tries;

    /**
     * @var \DateTime|null
     */
    private $lastCheck;

    /**
     * @var string|null
     */
    private $information;

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

        if ($this->getLastCheck()) {
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
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setId(?string $id): self
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
    public function getDetailsUrl(): ?string
    {
        return $this->detailsUrl;
    }

    /**
     * @param string|null $detailsUrl
     * @return \Centreon\Domain\Monitoring\Resource
     */
    public function setDetailsUrl(?string $detailsUrl): self
    {
        $this->detailsUrl = $detailsUrl ?: null;

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
}

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
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\ResourceStatus;
use DateTime;

/**
 * Resource model
 *
 * @package Centreon\Domain\Monitoring
 */
class Resource
{
    // Groups for serilizing
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
     * @var bool|null
     */
    private $inDowntime;

    /**
     * @var bool|null
     */
    private $acknowledged;

    /**
     * @var \Centreon\Domain\Monitoring\Icon|null
     */
    private $severity;

    /**
     * @var int|null
     */
    private $impactedResourcesCount;

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
        ];
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
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

    public function getDetailsUrl(): ?string
    {
        return $this->detailsUrl;
    }

    public function setDetailsUrl(?string $detailsUrl): self
    {
        $this->detailsUrl = $detailsUrl ?: null;

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

    public function getInDowntime(): ?bool
    {
        return $this->inDowntime;
    }

    public function setInDowntime(?bool $inDowntime): self
    {
        $this->inDowntime = $inDowntime;

        return $this;
    }

    public function getAcknowledged(): ?bool
    {
        return $this->acknowledged;
    }

    public function setAcknowledged(?bool $acknowledged): self
    {
        $this->acknowledged = $acknowledged;

        return $this;
    }

    public function getSeverity(): ?Icon
    {
        return $this->severity;
    }

    public function setSeverity(?Icon $severity): self
    {
        $this->severity = $severity;

        return $this;
    }

    public function getImpactedResourcesCount(): ?int
    {
        return $this->impactedResourcesCount;
    }

    public function setImpactedResourcesCount(?int $impactedResourcesCount): self
    {
        $this->impactedResourcesCount = $impactedResourcesCount ?: 0;

        return $this;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function setActionUrl(?string $actionUrl): self
    {
        $this->actionUrl = $actionUrl ?: null;

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

    public function getLastStatusChange(): ?DateTime
    {
        return $this->lastStatusChange;
    }

    public function setLastStatusChange(?DateTime $lastStatusChange): self
    {
        $this->lastStatusChange = $lastStatusChange;

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

    public function getLastCheck(): ?DateTime
    {
        return $this->lastCheck;
    }

    public function setLastCheck(?DateTime $lastCheck): self
    {
        $this->lastCheck = $lastCheck;

        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): self
    {
        $this->information = trim($information);

        return $this;
    }
}

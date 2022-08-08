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

/**
 * Resource Links Endpoints model for resource repository
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceLinksEndpoints
{
    /**
     * @var string|null
     */
    private $details;

    /**
     * @var string|null
     */
    private $timeline;

    /**
     * @var string|null
     */
    private $statusGraph;

    /**
     * @var string|null
     */
    private $performanceGraph;

    /**
     * @var string|null
     */
    private $acknowledgement;

    /**
     * @var string|null
     */
    private $downtime;

    /**
     * @var string|null
     */
    private $metrics;

    /**
     * @var string|null
     */
    private $notificationPolicy;

    /**
     * @return string|null
     */
    public function getDetails(): ?string
    {
        return $this->details;
    }

    /**
     * @param string|null $details
     * @return self
     */
    public function setDetails(?string $details): self
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimeline(): ?string
    {
        return $this->timeline;
    }

    /**
     * @param string|null $timeline
     * @return self
     */
    public function setTimeline(?string $timeline): self
    {
        $this->timeline = $timeline;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatusGraph(): ?string
    {
        return $this->statusGraph;
    }

    /**
     * @param string|null $statusGraph
     * @return self
     */
    public function setStatusGraph(?string $statusGraph): self
    {
        $this->statusGraph = $statusGraph;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPerformanceGraph(): ?string
    {
        return $this->performanceGraph;
    }

    /**
     * @param string|null $performanceGraph
     * @return self
     */
    public function setPerformanceGraph(?string $performanceGraph): self
    {
        $this->performanceGraph = $performanceGraph;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAcknowledgement(): ?string
    {
        return $this->acknowledgement;
    }

    /**
     * @param string|null $acknowledgement
     * @return self
     */
    public function setAcknowledgement(?string $acknowledgement): self
    {
        $this->acknowledgement = $acknowledgement;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDowntime(): ?string
    {
        return $this->downtime;
    }

    /**
     * @param string|null $downtime
     * @return self
     */
    public function setDowntime(?string $downtime): self
    {
        $this->downtime = $downtime;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMetrics(): ?string
    {
        return $this->metrics;
    }

    /**
     * @param string|null $metrics
     * @return self
     */
    public function setMetrics(?string $metrics): self
    {
        $this->metrics = $metrics;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotificationPolicy(): ?string
    {
        return $this->notificationPolicy;
    }

    /**
     * @param string|null $notificationPolicy
     * @return self
     */
    public function setNotificationPolicy(?string $notificationPolicy): self
    {
        $this->notificationPolicy = $notificationPolicy;

        return $this;
    }
}

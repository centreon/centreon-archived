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

namespace Centreon\Domain\Monitoring\Model;

use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\ResourceStatus;
use CentreonDuration;

/**
 * The model enrich the Host model
 */
trait ResourceDetailsTrait
{
    /**
     * @var Resource|null
     */
    protected $parent;

    /**
     * @var ResourceStatus|null
     */
    protected $status;

    /**
     * @var string|null
     */
    protected $tries;

    /**
     * @var string|null
     */
    private $timelineEndpoint;

    /**
     * @var string|null
     */
    private $performanceGraphEndpoint;

    /**
     * @var string|null
     */
    private $statusGraphEndpoint;

    /**
     * @return selfStatus|null
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
     * @return string|null
     */
    public function getDuration(): ?string
    {
        $result = null;

        if ($this->getLastStateChange()) {
            $result = CentreonDuration::toString(time() - $this->getLastStateChange()->getTimestamp());
        }

        return $result;
    }

    /**
     * @return self|null
     */
    public function getParent(): ?Resource
    {
        return $this->parent;
    }

    /**
     * @param ResourceStatus|null $parent
     * @return self
     */
    public function setParent(?Resource $parent): self
    {
        $this->parent = $parent;

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
     * Get the tries property with translation
     *
     * @return string|null
     */
    public function getTriesTranslatable(): ?string
    {
        $search = $replace = ['Hard', 'Soft'];
        array_walk($replace, function (&$value) {
            $value = _($value);
        });

        return str_replace($search, $replace, $this->tries);
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
}

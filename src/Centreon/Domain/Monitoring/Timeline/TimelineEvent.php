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

namespace Centreon\Domain\Monitoring\Timeline;

use Centreon\Domain\Monitoring\Timeline\TimelineContact;
use Centreon\Domain\Monitoring\ResourceStatus;
use DateTime;

/**
 * Class TimelineEvent
 * @package Centreon\Domain\Monitoring\Timeline
 */
class TimelineEvent
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var DateTime|null
     */
    private $date;

    /**
     * @var DateTime|null
     */
    private $startDate;

    /**
     * @var DateTime|null
     */
    private $endDate;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var TimelineContact|null
     */
    private $contact;

    /**
     * @var ResourceStatus|null
     */
    private $status;

    /**
     * @var int|null
     */
    private $tries;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return TimelineEvent
     */
    public function setId(?int $id): TimelineEvent
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
     * @return TimelineEvent
     */
    public function setType(?string $type): TimelineEvent
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime|null $date
     * @return TimelineEvent
     */
    public function setDate(?DateTime $date): TimelineEvent
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime|null $startDate
     * @return TimelineEvent
     */
    public function setStartDate(?DateTime $startDate): TimelineEvent
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime|null $endDate
     * @return TimelineEvent
     */
    public function setEndDate(?DateTime $endDate): TimelineEvent
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     * @return TimelineEvent
     */
    public function setContent(?string $content): TimelineEvent
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return TimelineContact|null
     */
    public function getContact(): ?TimelineContact
    {
        return $this->contact;
    }

    /**
     * @param TimelineContact|null $contact
     * @return TimelineEvent
     */
    public function setContact(?TimelineContact $contact): TimelineEvent
    {
        $this->contact = $contact;
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
     * @return TimelineEvent
     */
    public function setStatus(?ResourceStatus $status): TimelineEvent
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTries(): ?int
    {
        return $this->tries;
    }

    /**
     * @param int|null $tries
     * @return TimelineEvent
     */
    public function setTries(?int $tries): TimelineEvent
    {
        $this->tries = $tries;
        return $this;
    }
}

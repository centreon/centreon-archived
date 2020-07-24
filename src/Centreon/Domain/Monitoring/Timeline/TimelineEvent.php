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

use DateTime;

/**
 * Class TimelineEvent
 * @package Centreon\Domain\Monitoring\Timeline
 */
class TimelineEvent
{
    // Groups for serilizing
    public const SERIALIZER_GROUP_LIST = 'timeline_list';
    public const SERIALIZER_GROUP_FULL = 'timeline_full';

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
    private $output;

    /**
     * @var DateTime|null
     */
    private $timestamp;

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
     * @return string|null
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * @param string|null $output
     * @return TimelineEvent
     */
    public function setOutput(?string $output): TimelineEvent
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getTimestamp(): ?DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param DateTime|null $timestamp
     * @return TimelineEvent
     */
    public function setTimestamp(?DateTime $timestamp): TimelineEvent
    {
        $this->timestamp = $timestamp;
        return $this;
    }
}

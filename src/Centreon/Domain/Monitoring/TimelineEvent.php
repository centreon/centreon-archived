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

use Centreon\Domain\Monitoring\Interfaces\EventObjectInterface;

/**
 * Class TimelineEvent
 * @package Centreon\Domain\Monitoring
 */
class TimelineEvent
{
    // Groups for serilizing
    public const SERIALIZER_GROUP_LIST = 'timeline_list';
    public const SERIALIZER_GROUP_FULL = 'timeline_full';

    /**
     * The subobject of the event
     * @var EventObjectInterface
     */
    protected $object;

    public function __construct(EventObjectInterface $object)
    {
        $this->setObject($object);
    }

    /**
     * @return string $id
     */
    public function getId(): string
    {
        return $this->getObject()->getEventType() . $this->getObject()->getEventId();
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): ?\DateTime
    {
        return $this->getObject()->getTimestamp();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->getObject()->getEventType();
    }

    /**
     * @return EventObjectInterface
     */
    public function getObject(): EventObjectInterface
    {
        return $this->object;
    }

    /**
     * @param EventObjectInterface $object
     * @return void
     */
    public function setObject(EventObjectInterface $object): void
    {
        $this->object = $object;
    }
}

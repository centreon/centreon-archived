<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Domain\Configuration\Notification\Model;

use Core\Domain\Configuration\Notification\Exception\NotificationException;
use Core\Domain\Configuration\Notification\Model\NotificationInterface;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;

class HostNotification implements NotificationInterface
{
    public const EVENT_HOST_RECOVERY = 'RECOVERY',
                 EVENT_HOST_SCHEDULED_DOWNTIME = 'SCHEDULED_DOWNTIME',
                 EVENT_HOST_FLAPPING = 'FLAPPING',
                 EVENT_HOST_NONE = 'NONE',
                 EVENT_HOST_DOWN = 'DOWN',
                 EVENT_HOST_UNREACHABLE = 'UNREACHABLE';

    public const HOST_EVENTS = [
        self::EVENT_HOST_DOWN,
        self::EVENT_HOST_FLAPPING,
        self::EVENT_HOST_NONE,
        self::EVENT_HOST_RECOVERY,
        self::EVENT_HOST_SCHEDULED_DOWNTIME,
        self::EVENT_HOST_UNREACHABLE,
    ];

    /**
     * @var string[]
     */
    private $events = [];

    /**
     * @param TimePeriod $timePeriod
     */
    public function __construct(
        private TimePeriod $timePeriod
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @inheritDoc
     */
    public function getTimePeriod(): TimePeriod
    {
        return $this->timePeriod;
    }

    /**
     * @param string $event
     * @return self
     * @throws NotificationException
     */
    public function addEvent(string $event): self
    {
        if (in_array($event, self::HOST_EVENTS) === false) {
            throw NotificationException::badEvent($event);
        }
        $this->events[] = $event;
        return $this;
    }
}

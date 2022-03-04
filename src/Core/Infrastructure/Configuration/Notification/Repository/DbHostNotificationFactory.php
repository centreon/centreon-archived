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

namespace Core\Infrastructure\Configuration\Notification\Repository;

use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;
use Core\Domain\Configuration\Notification\Model\HostNotification;

class DbHostNotificationFactory
{
    /**
     * @param array<string, mixed> $data
     * @return HostNotification
     */
    public static function createFromRecord(array $data): HostNotification
    {
        $timePeriod = new TimePeriod(
            (int) $data['timeperiod_tp_id'],
            $data['tp_name'],
            $data['tp_alias']
        );

        $notification = new HostNotification($timePeriod);

        $events = ($data['contact_host_notification_options'] !== null)
            ? explode(',', $data['contact_host_notification_options'])
            : [];

        foreach ($events as $event) {
            $normalizedEvent = self::normalizeHostEvent($event);
            if ($normalizedEvent === null) {
                continue;
            }
            $notification->addEvent($normalizedEvent);
        }

        return $notification;
    }

    /**
     * Convert single char referencing Host event into a string
     *
     * @param string $event
     * @return string|null
     */
    private static function normalizeHostEvent(string $event): ?string
    {
        return match ($event) {
            'd' => HostNotification::EVENT_HOST_DOWN,
            'u' => HostNotification::EVENT_HOST_UNREACHABLE,
            'r' => HostNotification::EVENT_HOST_RECOVERY,
            'f' => HostNotification::EVENT_HOST_FLAPPING,
            's' => HostNotification::EVENT_HOST_SCHEDULED_DOWNTIME,
            default => null
        };
    }
}

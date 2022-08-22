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

namespace Core\Infrastructure\Configuration\NotificationPolicy\Repository;

use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;
use Core\Domain\Configuration\Notification\Model\ServiceNotification;

class DbContactServiceNotificationFactory
{
    /**
     * @param array<string,int|string|null> $notification
     * @return ServiceNotification
     */
    public static function createFromRecord(array $notification): ServiceNotification
    {
        $timePeriod = new TimePeriod(
            (int) $notification['service_timeperiod_id'],
            (string) $notification['service_timeperiod_name'],
            (string) $notification['service_timeperiod_alias']
        );

        $serviceNotification = new ServiceNotification($timePeriod);

        /** @var string|null */
        $notificationOptions = $notification['contact_service_notification_options'];

        $events = $notificationOptions !== null ? explode(',', $notificationOptions) : [];

        foreach ($events as $event) {
            $normalizedEvent = self::normalizeServiceEvent($event);
            if ($normalizedEvent === null) {
                continue;
            }
            $serviceNotification->addEvent($normalizedEvent);
        }

        return $serviceNotification;
    }

    /**
     * Convert single char referencing Host event into a string
     *
     * @param string $event
     * @return string|null
     */
    private static function normalizeServiceEvent(string $event): ?string
    {
        return match ($event) {
            'o' => ServiceNotification::EVENT_SERVICE_RECOVERY,
            's' => ServiceNotification::EVENT_SERVICE_SCHEDULED_DOWNTIME,
            'f' => ServiceNotification::EVENT_SERVICE_FLAPPING,
            'w' => ServiceNotification::EVENT_SERVICE_WARNING,
            'u' => ServiceNotification::EVENT_SERVICE_UNKNOWN,
            'c' => ServiceNotification::EVENT_SERVICE_CRITICAL,
            default => null,
        };
    }
}

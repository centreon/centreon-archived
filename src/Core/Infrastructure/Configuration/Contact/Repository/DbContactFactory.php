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

namespace Core\Infrastructure\Configuration\Contact\Repository;

use Core\Domain\Configuration\Contact\Model\Contact;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;

class DbContactFactory
{
    /**
     * @param array<string, mixed> $data
     * @return Contact
     */
    public static function createFromRecord(array $data): Contact
    {
        $contact = new Contact(
            (int) $data['id'],
            $data['name'],
            $data['alias'],
            $data['mail']
        );

        $notifiedOnHostEvents = array_map(
            fn (string $event) => self::normalizeHostEvent($event),
            explode(',', $data['notified_on_host_events'])
        );

        $contact->setNotifiedOnHostEvents(
            (count($notifiedOnHostEvents) === 1 && $notifiedOnHostEvents[0] === Contact::EVENT_NONE)
            ? []
            : $notifiedOnHostEvents
        );

        $notifiedOnServiceEvents = array_map(
            fn (string $event) => self::normalizeServiceEvent($event),
            explode(',', $data['notified_on_service_events'])
        );

        $contact->setNotifiedOnServiceEvents(
            (count($notifiedOnServiceEvents) === 1 && $notifiedOnServiceEvents[0] === Contact::EVENT_NONE)
            ? []
            : $notifiedOnServiceEvents
        );

        $contact->setHostNotificationTimePeriod(new TimePeriod(
            (int) $data['notification_period_id_for_host'],
            $data['notification_period_name_for_host'],
            $data['notification_period_alias_for_host']
        ));

        $contact->setServiceNotificationTimePeriod(new TimePeriod(
            (int) $data['notification_period_id_for_service'],
            $data['notification_period_name_for_service'],
            $data['notification_period_alias_for_service']
        ));

        return $contact;
    }

    /**
     * Convert single char referencing Host event into a string
     *
     * @param string $event
     * @return string
     */
    private static function normalizeHostEvent(string $event): string
    {
        return match ($event) {
            'd' => Contact::EVENT_HOST_DOWN,
            'u' => Contact::EVENT_HOST_UNREACHABLE,
            'r' => Contact::EVENT_RECOVERY,
            'f' => Contact::EVENT_FLAPPING,
            's' => Contact::EVENT_SCHEDULED_DOWNTIME,
            default => Contact::EVENT_NONE
        };
    }

    /**
     * Convert single char referencing Service event into a string
     *
     * @param string $event
     * @return string
     */
    private static function normalizeServiceEvent(string $event): string
    {
        return match ($event) {
            'c' => Contact::EVENT_SERVICE_CRITICAL,
            'u' => Contact::EVENT_SERVICE_UNKNOWN,
            'w' => Contact::EVENT_SERVICE_WARNING,
            'r' => Contact::EVENT_RECOVERY,
            'f' => Contact::EVENT_FLAPPING,
            's' => Contact::EVENT_SCHEDULED_DOWNTIME,
            default => Contact::EVENT_NONE
        };

    }
}

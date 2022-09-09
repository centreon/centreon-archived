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

namespace Core\Application\Configuration\NotificationPolicy\UseCase;

use Core\Domain\Configuration\Notification\Model\NotifiedContact;
use Core\Domain\Configuration\Notification\Model\NotifiedContactGroup;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;

class FindNotificationPolicyResponse
{
    /**
     * @var array<array<string, mixed>>
     */
    public $notifiedContacts;

    /**
     * @var array<array<string, mixed>>
     */
    public $notifiedContactGroups;

    /**
     * @var array<array<string, mixed>>
     */
    public $usersNotificationSettings;

    /**
     * @param NotifiedContact[] $notifiedContacts
     * @param NotifiedContactGroup[] $notifiedContactGroups
     * @param bool $isNotificationEnabled
     */
    public function __construct(
        array $notifiedContacts,
        array $notifiedContactGroups,
        public bool $isNotificationEnabled,
    ) {
        $this->notifiedContacts = $this->contactsToArray($notifiedContacts);
        $this->notifiedContactGroups = $this->contactGroupsToArray($notifiedContactGroups);
    }

    /**
     * @param NotifiedContact[] $notifiedContacts
     * @return array<array<string, mixed>>
     */
    private function contactsToArray(array $notifiedContacts): array
    {
        return array_map(
            fn (NotifiedContact $notifiedContact) => [
                'id' => $notifiedContact->getId(),
                'name' => $notifiedContact->getName(),
                'alias' => $notifiedContact->getAlias(),
                'email' => $notifiedContact->getEmail(),
                'notifications' => [
                    'host' => [
                        'events' => $notifiedContact->getHostNotification()->getEvents(),
                        'time_period' => self::timePeriodToArray(
                            $notifiedContact->getHostNotification()->getTimePeriod(),
                        ),
                    ],
                    'service' => [
                        'events' => $notifiedContact->getServiceNotification()->getEvents(),
                        'time_period' => self::timePeriodToArray(
                            $notifiedContact->getServiceNotification()->getTimePeriod(),
                        ),
                    ]
                ],
            ],
            $notifiedContacts
        );
    }

    /**
     * @param NotifiedContactGroup[] $notifiedContactGroups
     * @return array<array<string, mixed>>
     */
    private function contactGroupsToArray(array $notifiedContactGroups): array
    {
        return array_map(
            fn (NotifiedContactGroup $notifiedContactGroup) => [
                'id' => $notifiedContactGroup->getId(),
                'name' => $notifiedContactGroup->getName(),
                'alias' => $notifiedContactGroup->getAlias(),
            ],
            $notifiedContactGroups
        );
    }

    /**
     * @param TimePeriod|null $timePeriod
     * @return array<string, mixed>
     */
    private function timePeriodToArray(?TimePeriod $timePeriod): array
    {
        if ($timePeriod === null) {
            return [];
        }

        return [
            'id' => $timePeriod->getId(),
            'name' => $timePeriod->getName(),
            'alias' => $timePeriod->getAlias(),
        ];
    }
}

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

namespace Core\Application\Configuration\NotificationPolicy\UseCase\FindHostNotificationPolicy;

use Core\Domain\Configuration\Contact\Model\Contact;
use Core\Domain\Configuration\ContactGroup\Model\ContactGroup;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;

class FindHostNotificationPolicyResponse
{
    /**
     * @var array<array<string, mixed>>
     */
    public $contacts;

    /**
     * @var array<array<string, mixed>>
     */
    public $contactGroups;

    /**
     * @param Contact[] $contacts
     * @param ContactGroup[] $contactGroups
     */
    public function __construct(
        array $contacts,
        array $contactGroups,
    ) {
        $this->contacts = $this->contactsToArray($contacts);
        $this->contactGroups = $this->contactGroupsToArray($contactGroups);
    }

    /**
     * @param Contact[] $contacts
     * @return array<array<string, mixed>>
     */
    private function contactsToArray(array $contacts): array
    {
        return array_map(
            fn (Contact $contact) => [
                'id' => $contact->getId(),
                'name' => $contact->getName(),
                'alias' => $contact->getAlias(),
                'notified_on_host_events' => $contact->getNotifiedOnHostEvents(),
                'notified_on_service_events' => $contact->getNotifiedOnServiceEvents(),
                'host_notification_time_period' => $this->timePeriodToArray($contact->getHostNotificationTimePeriod()),
                'service_notification_time_period' => $this->timePeriodToArray($contact->getServiceNotificationTimePeriod()),
            ],
            $contacts
        );
    }

    /**
     * @param ContactGroup[] $contactGroups
     * @return array<array<string, mixed>>
     */
    private function contactGroupsToArray(array $contactGroups): array
    {
        return array_map(
            fn (ContactGroup $contactGroup) => [
                'id' => $contactGroup->getId(),
                'name' => $contactGroup->getName(),
                'alias' => $contactGroup->getAlias(),
            ],
            $contactGroups
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

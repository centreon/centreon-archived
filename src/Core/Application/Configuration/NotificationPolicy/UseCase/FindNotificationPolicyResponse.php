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

use Core\Domain\Configuration\Notification\Model\NotificationInterface;
use Core\Domain\Configuration\User\Model\User;
use Core\Domain\Configuration\UserGroup\Model\UserGroup;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;

class FindNotificationPolicyResponse
{
    /**
     * @var array<array<string, mixed>>
     */
    public $users;

    /**
     * @var array<array<string, mixed>>
     */
    public $userGroups;

    /**
     * @var array<array<string, mixed>>
     */
    public $usersNotificationSettings;

    /**
     * @param User[] $users
     * @param UserGroup[] $userGroups
     * @param NotificationInterface[] $usersNotificationSettings
     */
    public function __construct(
        array $users,
        array $userGroups,
        array $usersNotificationSettings,
    ) {
        $this->users = $this->usersToArray($users);
        $this->userGroups = $this->userGroupsToArray($userGroups);
        $this->usersNotificationSettings = $this->usersNotificationSettingsToArray($usersNotificationSettings);
    }

    /**
     * @param User[] $users
     * @return array<array<string, mixed>>
     */
    private function usersToArray(array $users): array
    {
        return array_map(
            fn (User $user) => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'alias' => $user->getAlias(),
                'email' => $user->getEmail(),
            ],
            $users
        );
    }

    /**
     * @param UserGroup[] $userGroups
     * @return array<array<string, mixed>>
     */
    private function userGroupsToArray(array $userGroups): array
    {
        return array_map(
            fn (UserGroup $userGroup) => [
                'id' => $userGroup->getId(),
                'name' => $userGroup->getName(),
                'alias' => $userGroup->getAlias(),
            ],
            $userGroups
        );
    }

    /**
     * @param NotificationInterface[] $usersNotificationSettings
     * @return array<array<string, mixed>>
     */
    private function usersNotificationSettingsToArray(array $usersNotificationSettings): array
    {
        return array_map(
            fn (NotificationInterface $userNotificationSetting) => [
                'is_notified_on' => $userNotificationSetting->getEvents(),
                'time_period' => self::timePeriodToArray($userNotificationSetting->getTimePeriod())
            ],
            $usersNotificationSettings
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

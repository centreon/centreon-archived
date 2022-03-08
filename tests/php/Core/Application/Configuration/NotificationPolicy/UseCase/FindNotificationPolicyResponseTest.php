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

namespace Tests\Core\Application\Configuration\NotificationPolicy\UseCase;

use Core\Application\Configuration\NotificationPolicy\UseCase\FindNotificationPolicyResponse;
use Core\Domain\Configuration\User\Model\User;
use Core\Domain\Configuration\Notification\Model\HostNotification;
use Core\Domain\Configuration\Notification\Model\ServiceNotification;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;
use Core\Domain\Configuration\UserGroup\Model\UserGroup;

beforeEach(function () {
    $this->user = new User(2, 'user2', 'user 2', 'user2@localhost', false);
    $this->userGroup = new UserGroup(3, 'cg3', 'cg 3');

    $this->userHostNotificationSettings = new HostNotification(new Timeperiod(1, '24x7', '24/24 7/7'));
    $this->userHostNotificationSettings->addEvent(HostNotification::EVENT_HOST_DOWN);

    $this->userServiceNotificationSettings = new ServiceNotification(new Timeperiod(1, '24x7', '24/24 7/7'));
    $this->userServiceNotificationSettings->addEvent(ServiceNotification::EVENT_SERVICE_CRITICAL);
});

it('converts given host notification models to array', function () {
    $response = new FindNotificationPolicyResponse(
        [$this->user],
        [$this->userGroup],
        [$this->userHostNotificationSettings],
        true,
    );

    expect($response->users)->toBe([
        [
            'id' => $this->user->getId(),
            'name' => $this->user->getName(),
            'alias' => $this->user->getAlias(),
            'email' => $this->user->getEmail(),
        ],
    ]);

    expect($response->userGroups)->toBe([
        [
            'id' => $this->userGroup->getId(),
            'name' => $this->userGroup->getName(),
            'alias' => $this->userGroup->getAlias(),
        ],
    ]);

    expect($response->usersNotificationSettings)->toBe([
        [
            'is_notified_on' => $this->userHostNotificationSettings->getEvents(),
            'time_period' => [
                'id' => $this->userHostNotificationSettings->getTimePeriod()->getId(),
                'name' => $this->userHostNotificationSettings->getTimePeriod()->getName(),
                'alias' => $this->userHostNotificationSettings->getTimePeriod()->getAlias(),
            ],
        ],
    ]);

    expect($response->isNotificationEnabled)->toBe(true);
});

it('converts given service notification models to array', function () {
    $response = new FindNotificationPolicyResponse(
        [$this->user],
        [$this->userGroup],
        [$this->userServiceNotificationSettings],
        true,
    );

    expect($response->users)->toBe([
        [
            'id' => $this->user->getId(),
            'name' => $this->user->getName(),
            'alias' => $this->user->getAlias(),
            'email' => $this->user->getEmail(),
        ],
    ]);

    expect($response->userGroups)->toBe([
        [
            'id' => $this->userGroup->getId(),
            'name' => $this->userGroup->getName(),
            'alias' => $this->userGroup->getAlias(),
        ],
    ]);

    expect($response->usersNotificationSettings)->toBe([
        [
            'is_notified_on' => $this->userServiceNotificationSettings->getEvents(),
            'time_period' => [
                'id' => $this->userServiceNotificationSettings->getTimePeriod()->getId(),
                'name' => $this->userServiceNotificationSettings->getTimePeriod()->getName(),
                'alias' => $this->userServiceNotificationSettings->getTimePeriod()->getAlias(),
            ],
        ],
    ]);

    expect($response->isNotificationEnabled)->toBe(true);
});

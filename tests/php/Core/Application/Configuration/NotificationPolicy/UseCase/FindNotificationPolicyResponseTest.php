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
use Core\Domain\Configuration\Notification\Model\NotifiedContact;
use Core\Domain\Configuration\Notification\Model\NotifiedContactGroup;
use Core\Domain\Configuration\Notification\Model\HostNotification;
use Core\Domain\Configuration\Notification\Model\ServiceNotification;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;

beforeEach(function () {
    $hostNotification = new HostNotification(new Timeperiod(1, '24x7', '24/24 7/7'));
    $hostNotification->addEvent(HostNotification::EVENT_HOST_DOWN);

    $serviceNotification = new ServiceNotification(new Timeperiod(1, '24x7', '24/24 7/7'));
    $serviceNotification->addEvent(ServiceNotification::EVENT_SERVICE_CRITICAL);

    $this->contact = new NotifiedContact(
        2,
        'user2',
        'user 2',
        'user2@localhost',
        $hostNotification,
        $serviceNotification,
    );

    $this->contactGroup = new NotifiedContactGroup(3, 'cg3', 'cg 3');
});

it('converts given host notification models to array', function () {
    $response = new FindNotificationPolicyResponse(
        [$this->contact],
        [$this->contactGroup],
        true,
    );

    expect($response->notifiedContacts)->toBe([
        [
            'id' => $this->contact->getId(),
            'name' => $this->contact->getName(),
            'alias' => $this->contact->getAlias(),
            'email' => $this->contact->getEmail(),
            'notifications' => [
                'host' => [
                    'events' => ['DOWN'],
                    'time_period' => [
                        'id' => 1,
                        'name' => '24x7',
                        'alias' => '24/24 7/7',
                    ],
                ],
                'service' => [
                    'events' => ['CRITICAL'],
                    'time_period' => [
                        'id' => 1,
                        'name' => '24x7',
                        'alias' => '24/24 7/7',
                    ],
                ],
            ],
        ],
    ]);

    expect($response->notifiedContactGroups)->toBe([
        [
            'id' => $this->contactGroup->getId(),
            'name' => $this->contactGroup->getName(),
            'alias' => $this->contactGroup->getAlias(),
        ],
    ]);

    expect($response->isNotificationEnabled)->toBe(true);
});

it('converts given service notification models to array', function () {
    $response = new FindNotificationPolicyResponse(
        [$this->contact],
        [$this->contactGroup],
        true,
    );

    expect($response->notifiedContacts)->toBe([
        [
            'id' => $this->contact->getId(),
            'name' => $this->contact->getName(),
            'alias' => $this->contact->getAlias(),
            'email' => $this->contact->getEmail(),
            'notifications' => [
                'host' => [
                    'events' => ['DOWN'],
                    'time_period' => [
                        'id' => 1,
                        'name' => '24x7',
                        'alias' => '24/24 7/7',
                    ],
                ],
                'service' => [
                    'events' => ['CRITICAL'],
                    'time_period' => [
                        'id' => 1,
                        'name' => '24x7',
                        'alias' => '24/24 7/7',
                    ],
                ],
            ],
        ],
    ]);

    expect($response->notifiedContactGroups)->toBe([
        [
            'id' => $this->contactGroup->getId(),
            'name' => $this->contactGroup->getName(),
            'alias' => $this->contactGroup->getAlias(),
        ],
    ]);

    expect($response->isNotificationEnabled)->toBe(true);
});

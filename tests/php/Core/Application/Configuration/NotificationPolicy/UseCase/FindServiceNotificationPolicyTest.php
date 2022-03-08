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

use Core\Application\Configuration\NotificationPolicy\UseCase\FindServiceNotificationPolicy;
use Core\Application\Configuration\NotificationPolicy\UseCase\FindNotificationPolicyPresenterInterface;
use Core\Application\Configuration\NotificationPolicy\UseCase\FindNotificationPolicyResponse;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Core\Application\Configuration\User\Repository\ReadUserRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Core\Application\Configuration\UserGroup\Repository\ReadUserGroupRepositoryInterface;
use Core\Application\Configuration\Notification\Repository\ReadNotificationRepositoryInterface;
use Core\Application\Configuration\NotificationPolicy\Repository\LegacyNotificationPolicyRepositoryInterface;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\RealTime\Repository\ReadServiceRepositoryInterface as ReadRealTimeServiceRepositoryInterface;
use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\ServiceConfiguration\Service;
use Core\Domain\RealTime\Model\Service as RealTimeService;
use Core\Domain\RealTime\Model\ServiceStatus;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Domain\Configuration\User\Model\User;
use Core\Domain\Configuration\Notification\Model\ServiceNotification;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;
use Core\Domain\Configuration\UserGroup\Model\UserGroup;

beforeEach(function () {
    $this->legacyRepository = $this->createMock(LegacyNotificationPolicyRepositoryInterface::class);
    $this->notificationRepository = $this->createMock(ReadNotificationRepositoryInterface::class);
    $this->userRepository = $this->createMock(ReadUserRepositoryInterface::class);
    $this->userGroupRepository = $this->createMock(ReadUserGroupRepositoryInterface::class);
    $this->hostRepository = $this->createMock(HostConfigurationRepositoryInterface::class);
    $this->serviceRepository = $this->createMock(ServiceConfigurationRepositoryInterface::class);
    $this->engineService = $this->createMock(EngineConfigurationServiceInterface::class);
    $this->accessGroupRepository = $this->createMock(AccessGroupRepositoryInterface::class);
    $this->contact = $this->createMock(ContactInterface::class);
    $this->readRealTimeServiceRepository = $this->createMock(ReadRealTimeServiceRepositoryInterface::class);

    $this->host = new Host();

    $this->service = new Service();
    $this->realTimeService = new RealTimeService(
        1,
        1,
        'Ping',
        new ServiceStatus(ServiceStatus::STATUS_NAME_CRITICAL, ServiceStatus::STATUS_CODE_CRITICAL, 1),
    );


    $this->findNotificationPolicyPresenter = $this->createMock(FindNotificationPolicyPresenterInterface::class);
});

it('does not find service notification policy when host is not found by admin user', function () {
    $useCase = new FindServiceNotificationPolicy(
        $this->legacyRepository,
        $this->notificationRepository,
        $this->userRepository,
        $this->userGroupRepository,
        $this->hostRepository,
        $this->serviceRepository,
        $this->engineService,
        $this->accessGroupRepository,
        $this->contact,
        $this->readRealTimeServiceRepository,
    );

    $this->contact
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $this->hostRepository
        ->expects($this->once())
        ->method('findHost')
        ->willReturn(null);

    $this->findNotificationPolicyPresenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new NotFoundResponse('Host'));

    $useCase(1, 1, $this->findNotificationPolicyPresenter);
});

it('does not find service notification policy when service is not found by admin user', function () {
    $useCase = new FindServiceNotificationPolicy(
        $this->legacyRepository,
        $this->notificationRepository,
        $this->userRepository,
        $this->userGroupRepository,
        $this->hostRepository,
        $this->serviceRepository,
        $this->engineService,
        $this->accessGroupRepository,
        $this->contact,
        $this->readRealTimeServiceRepository,
    );

    $this->contact
        ->expects($this->exactly(2))
        ->method('isAdmin')
        ->willReturn(true);

    $this->hostRepository
        ->expects($this->once())
        ->method('findHost')
        ->willReturn($this->host);

    $this->serviceRepository
        ->expects($this->once())
        ->method('findService')
        ->willReturn(null);

    $this->findNotificationPolicyPresenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new NotFoundResponse('Service'));

    $useCase(1, 1, $this->findNotificationPolicyPresenter);
});

it('returns empty response when service notification is disabled', function () {
    $useCase = new FindServiceNotificationPolicy(
        $this->legacyRepository,
        $this->notificationRepository,
        $this->userRepository,
        $this->userGroupRepository,
        $this->hostRepository,
        $this->serviceRepository,
        $this->engineService,
        $this->accessGroupRepository,
        $this->contact,
        $this->readRealTimeServiceRepository,
    );

    $this->contact
        ->expects($this->exactly(2))
        ->method('isAdmin')
        ->willReturn(true);

    $this->hostRepository
        ->expects($this->once())
        ->method('findHost')
        ->willReturn($this->host);

    $this->serviceRepository
        ->expects($this->once())
        ->method('findService')
        ->willReturn($this->service);

    $this->service->setNotificationsEnabledOption(Service::NOTIFICATIONS_OPTION_DISABLED);

    $this->legacyRepository
        ->expects($this->once())
        ->method('findServiceNotifiedUserIdsAndUserGroupIds')
        ->with(1)
        ->willReturn([
            'contact' => [],
            'cg' => [],
        ]);

    $this->realTimeService->setNotificationEnabled(false);
    $this->readRealTimeServiceRepository
        ->expects($this->once())
        ->method('findServiceById')
        ->willReturn($this->realTimeService);

    $engineConfiguration = new EngineConfiguration();
    $engineConfiguration->setNotificationsEnabledOption(EngineConfiguration::NOTIFICATIONS_OPTION_DISABLED);
    $this->engineService
        ->expects($this->once())
        ->method('findEngineConfigurationByHost')
        ->willReturn($engineConfiguration);

    $this->findNotificationPolicyPresenter
        ->expects($this->once())
        ->method('present')
        ->with(new FindNotificationPolicyResponse([], [], [], false));

    $useCase(1, 1, $this->findNotificationPolicyPresenter);
});

it('returns users, user groups and settings when host notification is enabled', function () {
    $useCase = new FindServiceNotificationPolicy(
        $this->legacyRepository,
        $this->notificationRepository,
        $this->userRepository,
        $this->userGroupRepository,
        $this->hostRepository,
        $this->serviceRepository,
        $this->engineService,
        $this->accessGroupRepository,
        $this->contact,
        $this->readRealTimeServiceRepository,
    );

    $this->contact
        ->expects($this->exactly(2))
        ->method('isAdmin')
        ->willReturn(true);

    $this->hostRepository
        ->expects($this->once())
        ->method('findHost')
        ->willReturn($this->host);

    $this->serviceRepository
        ->expects($this->once())
        ->method('findService')
        ->willReturn($this->service);

    $this->service->setNotificationsEnabledOption(Host::NOTIFICATIONS_OPTION_ENABLED);

    $this->legacyRepository
        ->expects($this->once())
        ->method('findServiceNotifiedUserIdsAndUserGroupIds')
        ->with(1)
        ->willReturn([
            'contact' => [2],
            'cg' => [3],
        ]);

    $user = new User(2, 'user2', 'user 2', 'user2@localhost', false);
    $serviceNotification = new ServiceNotification(new Timeperiod(1, '24x7', '24/24 7/7'));
    $userGroup = new UserGroup(3, 'cg3', 'cg 3');

    $this->userRepository
        ->expects($this->once())
        ->method('findUsersByIds')
        ->with([2])
        ->willReturn([$user]);

    $this->notificationRepository
        ->expects($this->once())
        ->method('findServiceNotificationSettingsByUserIds')
        ->with([2])
        ->willReturn([$serviceNotification]);

    $this->userGroupRepository
        ->expects($this->once())
        ->method('findByIds')
        ->with([3])
        ->willReturn([$userGroup]);

    $this->realTimeService->setNotificationEnabled(true);
    $this->readRealTimeServiceRepository
        ->expects($this->once())
        ->method('findServiceById')
        ->willReturn($this->realTimeService);

    $engineConfiguration = new EngineConfiguration();
    $engineConfiguration->setNotificationsEnabledOption(EngineConfiguration::NOTIFICATIONS_OPTION_ENABLED);
    $this->engineService
        ->expects($this->once())
        ->method('findEngineConfigurationByHost')
        ->willReturn($engineConfiguration);

    $this->findNotificationPolicyPresenter
        ->expects($this->once())
        ->method('present')
        ->with(new FindNotificationPolicyResponse([$user], [$userGroup], [$serviceNotification], true));

    $useCase(1, 1, $this->findNotificationPolicyPresenter);
});

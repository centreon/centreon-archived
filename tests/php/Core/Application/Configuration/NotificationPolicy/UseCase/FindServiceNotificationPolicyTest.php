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
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Core\Application\Configuration\User\Repository\ReadUserRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Core\Application\Configuration\UserGroup\Repository\ReadUserGroupRepositoryInterface;
use Core\Application\Configuration\Notification\Repository\ReadNotificationRepositoryInterface;
use Core\Application\Configuration\Notification\Repository\ReadServiceNotificationRepositoryInterface;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface as ReadRealTimeHostRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServiceRepositoryInterface as ReadRealTimeServiceRepositoryInterface;
use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\ServiceConfiguration\Service;
use Core\Domain\RealTime\Model\Service as RealTimeService;
use Core\Domain\RealTime\Model\ServiceStatus;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Domain\Configuration\Notification\Model\NotifiedContact;
use Core\Domain\Configuration\Notification\Model\NotifiedContactGroup;
use Core\Domain\Configuration\Notification\Model\HostNotification;
use Core\Domain\Configuration\Notification\Model\ServiceNotification;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;

beforeEach(function () {
    $this->readServiceNotificationRepository = $this->createMock(ReadServiceNotificationRepositoryInterface::class);
    $this->hostRepository = $this->createMock(HostConfigurationRepositoryInterface::class);
    $this->serviceRepository = $this->createMock(ServiceConfigurationRepositoryInterface::class);
    $this->engineService = $this->createMock(EngineConfigurationServiceInterface::class);
    $this->accessGroupRepository = $this->createMock(ReadAccessGroupRepositoryInterface::class);
    $this->contact = $this->createMock(ContactInterface::class);
    $this->readRealTimeHostRepository = $this->createMock(ReadRealTimeHostRepositoryInterface::class);
    $this->readRealTimeServiceRepository = $this->createMock(ReadRealTimeServiceRepositoryInterface::class);

    $this->host = new Host();

    $this->service = new Service();
    $this->realTimeService = new RealTimeService(
        1,
        1,
        'Ping',
        new ServiceStatus(ServiceStatus::STATUS_NAME_CRITICAL, ServiceStatus::STATUS_CODE_CRITICAL, 1),
    );

    $hostNotification = new HostNotification(new Timeperiod(1, '24x7', '24/24 7/7'));
    $hostNotification->addEvent(HostNotification::EVENT_HOST_DOWN);

    $serviceNotification = new ServiceNotification(new Timeperiod(1, '24x7', '24/24 7/7'));
    $serviceNotification->addEvent(ServiceNotification::EVENT_SERVICE_CRITICAL);

    $this->notifiedContact = new NotifiedContact(
        1,
        'contact1',
        'contact1',
        'contact1@localhost',
        $hostNotification,
        $serviceNotification,
    );

    $this->notifiedContactGroup = new NotifiedContactGroup(3, 'cg3', 'cg 3');

    $this->findNotificationPolicyPresenter = $this->createMock(FindNotificationPolicyPresenterInterface::class);

    $this->useCase = new FindServiceNotificationPolicy(
        $this->readServiceNotificationRepository,
        $this->hostRepository,
        $this->serviceRepository,
        $this->engineService,
        $this->accessGroupRepository,
        $this->contact,
        $this->readRealTimeHostRepository,
        $this->readRealTimeServiceRepository,
    );
});

it('does not find service notification policy when host is not found by admin user', function () {
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

    ($this->useCase)(1, 1, $this->findNotificationPolicyPresenter);
});

it('does not find service notification policy when acl user does not have access to host', function () {
    $this->contact
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(false);

    $this->readRealTimeHostRepository
        ->expects($this->once())
        ->method('isAllowedToFindHostByAccessGroupIds')
        ->willReturn(false);

    $this->findNotificationPolicyPresenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new NotFoundResponse('Host'));

    ($this->useCase)(1, 1, $this->findNotificationPolicyPresenter);
});


it('does not find service notification policy when acl user does not have access to service', function () {
    $this->contact
        ->expects($this->exactly(2))
        ->method('isAdmin')
        ->willReturn(false);

    $this->readRealTimeHostRepository
        ->expects($this->once())
        ->method('isAllowedToFindHostByAccessGroupIds')
        ->willReturn(true);

    $this->hostRepository
        ->expects($this->once())
        ->method('findHost')
        ->willReturn($this->host);

    $this->readRealTimeServiceRepository
        ->expects($this->once())
        ->method('isAllowedToFindServiceByAccessGroupIds')
        ->willReturn(false);

    $this->findNotificationPolicyPresenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new NotFoundResponse('Service'));

    ($this->useCase)(1, 1, $this->findNotificationPolicyPresenter);
});

it('does not find service notification policy when service is not found by admin user', function () {
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

    ($this->useCase)(1, 1, $this->findNotificationPolicyPresenter);
});

it('returns users, user groups and notification status', function () {
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

    $this->readServiceNotificationRepository
        ->expects($this->once())
        ->method('findNotifiedContactsById')
        ->with(1)
        ->willReturn([$this->notifiedContact]);

    $this->readServiceNotificationRepository
        ->expects($this->once())
        ->method('findNotifiedContactGroupsById')
        ->with(1)
        ->willReturn([$this->notifiedContactGroup]);

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
        ->with(new FindNotificationPolicyResponse([$this->notifiedContact], [$this->notifiedContactGroup], false));

    ($this->useCase)(1, 1, $this->findNotificationPolicyPresenter);
});

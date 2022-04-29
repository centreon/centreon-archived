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

use Core\Application\Configuration\NotificationPolicy\UseCase\FindMetaServiceNotificationPolicy;
use Core\Application\Configuration\NotificationPolicy\UseCase\FindNotificationPolicyPresenterInterface;
use Core\Application\Configuration\NotificationPolicy\UseCase\FindNotificationPolicyResponse;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationReadRepositoryInterface;
use Core\Application\Configuration\Notification\Repository\ReadMetaServiceNotificationRepositoryInterface;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\RealTime\Repository\ReadMetaServiceRepositoryInterface as
    ReadRealTimeMetaServiceRepositoryInterface;
use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration;
use Core\Domain\RealTime\Model\MetaService as RealtimeMetaService;
use Core\Domain\RealTime\Model\ServiceStatus;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Domain\Configuration\Notification\Model\NotifiedContact;
use Core\Domain\Configuration\Notification\Model\NotifiedContactGroup;
use Core\Domain\Configuration\Notification\Model\HostNotification;
use Core\Domain\Configuration\Notification\Model\ServiceNotification;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;

beforeEach(function () {
    $this->readMetaServiceNotificationRepository = $this->createMock(
        ReadMetaServiceNotificationRepositoryInterface::class
    );
    $this->readMetaServiceRepository = $this->createMock(MetaServiceConfigurationReadRepositoryInterface::class);
    $this->engineService = $this->createMock(EngineConfigurationServiceInterface::class);
    $this->contact = $this->createMock(ContactInterface::class);
    $this->readRealTimeMetaServiceRepository = $this->createMock(ReadRealTimeMetaServiceRepositoryInterface::class);

    $this->metaService = new MetaServiceConfiguration(
        'meta1',
        'average',
        MetaServiceConfiguration::META_SELECT_MODE_LIST,
    );
    $this->realTimeMetaService = new RealTimeMetaService(
        1,
        1,
        1,
        'meta1',
        'central,',
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

    $this->useCase = new FindMetaServiceNotificationPolicy(
        $this->readMetaServiceNotificationRepository,
        $this->readMetaServiceRepository,
        $this->engineService,
        $this->contact,
        $this->readRealTimeMetaServiceRepository,
    );
});

it('does not find meta service notification policy when meta service is not found by admin user', function () {
    $this->contact
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $this->readMetaServiceRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn(null);

    $this->findNotificationPolicyPresenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new NotFoundResponse('Meta service'));

    ($this->useCase)(1, $this->findNotificationPolicyPresenter);
});

it('does not find meta service notification policy when meta service is not found by acl user', function () {
    $this->contact
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(false);

    $this->readMetaServiceRepository
        ->expects($this->once())
        ->method('findByIdAndContact')
        ->willReturn(null);

    $this->findNotificationPolicyPresenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new NotFoundResponse('Meta service'));

    ($this->useCase)(1, $this->findNotificationPolicyPresenter);
});

it('returns users, user groups and notification status', function () {
    $this->contact
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $this->readMetaServiceRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn($this->metaService);

    $this->readMetaServiceNotificationRepository
        ->expects($this->once())
        ->method('findNotifiedContactsById')
        ->with(1)
        ->willReturn([$this->notifiedContact]);

    $this->readMetaServiceNotificationRepository
        ->expects($this->once())
        ->method('findNotifiedContactGroupsById')
        ->with(1)
        ->willReturn([$this->notifiedContactGroup]);

    $this->realTimeMetaService->setNotificationEnabled(false);
    $this->readRealTimeMetaServiceRepository
        ->expects($this->once())
        ->method('findMetaServiceById')
        ->willReturn($this->realTimeMetaService);

    $engineConfiguration = new EngineConfiguration();
    $engineConfiguration->setNotificationsEnabledOption(EngineConfiguration::NOTIFICATIONS_OPTION_DISABLED);
    $this->engineService
        ->expects($this->once())
        ->method('findCentralEngineConfiguration')
        ->willReturn($engineConfiguration);

    $this->findNotificationPolicyPresenter
        ->expects($this->once())
        ->method('present')
        ->with(new FindNotificationPolicyResponse([$this->notifiedContact], [$this->notifiedContactGroup], false));

    ($this->useCase)(1, $this->findNotificationPolicyPresenter);
});

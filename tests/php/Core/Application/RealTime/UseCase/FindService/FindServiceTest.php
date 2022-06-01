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

namespace Tests\Core\Application\RealTime\UseCase\FindService;

use Core\Domain\RealTime\Model\Tag;
use Centreon\Domain\Contact\Contact;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Servicegroup;
use Tests\Core\Domain\RealTime\Model\HostTest;
use Core\Domain\RealTime\Model\Acknowledgement;
use Tests\Core\Domain\RealTime\Model\ServiceTest;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\RealTime\UseCase\FindService\FindService;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaCreator;
use Core\Application\RealTime\Repository\ReadTagRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Infrastructure\RealTime\Api\FindService\FindServicePresenter;
use Core\Application\RealTime\Repository\ReadServiceRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Security\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServicegroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Tests\Core\Application\RealTime\UseCase\FindService\FindServicePresenterStub;

beforeEach(function () {
    $this->repository = $this->createMock(ReadServiceRepositoryInterface::class);
    $this->servicegroupRepository = $this->createMock(ReadServicegroupRepositoryInterface::class);
    $this->accessGroupRepository = $this->createMock(ReadAccessGroupRepositoryInterface::class);
    $this->downtimeRepository = $this->createMock(ReadDowntimeRepositoryInterface::class);
    $this->acknowledgementRepository = $this->createMock(ReadAcknowledgementRepositoryInterface::class);
    $this->hypermediaCreator = $this->createMock(HypermediaCreator::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    $this->monitoringService = $this->createMock(MonitoringServiceInterface::class);
    $this->hostRepository = $this->createMock(ReadHostRepositoryInterface::class);
    $this->tagRepository = $this->createMock(ReadTagRepositoryInterface::class);

    $this->downtime = (new Downtime(1, 1, 10))
        ->setCancelled(false);

    $this->acknowledgement = new Acknowledgement(1, 1, 10, new \DateTime('1991-09-10'));

    $this->adminContact = (new Contact())
        ->setId(1)
        ->setName('admin')
        ->setAdmin(true);

    $this->nonAdminContact = (new Contact())
        ->setId(2)
        ->setName('user')
        ->setAdmin(false);

    $this->host = HostTest::createHostModel();
    $this->service = ServiceTest::createServiceModel();
    $this->servicegroup = new Servicegroup(1, 'ALL');
    $this->category = new Tag(1, 'service-category-name', Tag::SERVICE_CATEGORY_TYPE_ID);
});

it('FindService host not found as admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
        $this->adminContact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository,
    );

    $this->hostRepository
        ->expects($this->once())
        ->method('findHostById')
        ->willReturn(null);

    $findServicePresenter = new FindServicePresenter($this->hypermediaCreator, $this->presenterFormatter);

    $findService(1, 20, $findServicePresenter);

    $this->assertEquals($findServicePresenter->getResponseStatus(), new NotFoundResponse('Host'));
});

it('FindService host not found as non admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
        $this->nonAdminContact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository,
    );

    $this->accessGroupRepository
        ->expects($this->once())
        ->method('findByContact')
        ->willReturn([]);

    $this->hostRepository
        ->expects($this->once())
        ->method('findHostByIdAndAccessGroupIds')
        ->willReturn(null);

    $findServicePresenter = new FindServicePresenter($this->hypermediaCreator, $this->presenterFormatter);

    $findService(1, 20, $findServicePresenter);

    $this->assertEquals($findServicePresenter->getResponseStatus(), new NotFoundResponse('Host'));
});

it('FindService service not found as admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
        $this->adminContact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository,
    );

    $this->hostRepository
        ->expects($this->once())
        ->method('findHostById')
        ->willReturn(HostTest::createHostModel());

    $this->repository
        ->expects($this->once())
        ->method('findServiceById')
        ->willReturn(null);

    $findServicePresenter = new FindServicePresenter($this->hypermediaCreator, $this->presenterFormatter);

    $findService(1, 20, $findServicePresenter);

    $this->assertEquals($findServicePresenter->getResponseStatus(), new NotFoundResponse('Service'));
});

it('FindService service not found as non admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
        $this->nonAdminContact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository,
    );

    $this->accessGroupRepository
        ->expects($this->once())
        ->method('findByContact')
        ->willReturn([]);

    $this->hostRepository
        ->expects($this->once())
        ->method('findHostByIdAndAccessGroupIds')
        ->willReturn(HostTest::createHostModel());

    $this->repository
        ->expects($this->once())
        ->method('findServiceByIdAndAccessGroupIds')
        ->willReturn(null);

    $findServicePresenter = new FindServicePresenter($this->hypermediaCreator, $this->presenterFormatter);

    $findService(1, 20, $findServicePresenter);

    $this->assertEquals($findServicePresenter->getResponseStatus(), new NotFoundResponse('Service'));
});

it('FindService service found as admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
        $this->adminContact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository,
    );

    $this->hostRepository
        ->expects($this->once())
        ->method('findHostById')
        ->willReturn($this->host);

    $this->repository
        ->expects($this->once())
        ->method('findServiceById')
        ->willReturn($this->service);

    $this->servicegroupRepository
        ->expects($this->once())
        ->method('findAllByHostIdAndServiceId')
        ->willReturn([$this->servicegroup]);

    $this->tagRepository
        ->expects($this->once())
        ->method('findAllByResourceAndTypeId')
        ->willReturn([$this->category]);

    $this->downtimeRepository
        ->expects($this->once())
        ->method('findOnGoingDowntimesByHostIdAndServiceId')
        ->willReturn([$this->downtime]);

    $this->acknowledgementRepository
        ->expects($this->once())
        ->method('findOnGoingAcknowledgementByHostIdAndServiceId')
        ->willReturn($this->acknowledgement);

    $findServicePresenter = new FindServicePresenterStub();

    $findService(1, 10, $findServicePresenter);

    $this->assertEquals($findServicePresenter->response->name, $this->service->getName());
    $this->assertEquals($findServicePresenter->response->id, $this->service->getId());
    $this->assertEquals(
        $findServicePresenter->response->host['monitoring_server_name'],
        $this->host->getMonitoringServerName()
    );
    $this->assertEquals($findServicePresenter->response->isFlapping, $this->service->isFlapping());
    $this->assertEquals($findServicePresenter->response->isAcknowledged, $this->service->isAcknowledged());
    $this->assertEquals($findServicePresenter->response->isInDowntime, $this->service->isInDowntime());
    $this->assertEquals($findServicePresenter->response->output, $this->service->getOutput());
    $this->assertEquals($findServicePresenter->response->commandLine, $this->service->getCommandLine());
    $this->assertEquals($findServicePresenter->response->performanceData, $this->service->getPerformanceData());
    $this->assertEquals($findServicePresenter->response->notificationNumber, $this->service->getNotificationNumber());
    $this->assertEquals($findServicePresenter->response->latency, $this->service->getLatency());
    $this->assertEquals($findServicePresenter->response->executionTime, $this->service->getExecutionTime());
    $this->assertEquals(
        $findServicePresenter->response->statusChangePercentage,
        $this->service->getStatusChangePercentage()
    );
    $this->assertEquals($findServicePresenter->response->hasActiveChecks, $this->service->hasActiveChecks());
    $this->assertEquals($findServicePresenter->response->hasPassiveChecks, $this->service->hasPassiveChecks());
    $this->assertEquals($findServicePresenter->response->severityLevel, $this->service->getSeverityLevel());
    $this->assertEquals($findServicePresenter->response->checkAttempts, $this->service->getCheckAttempts());
    $this->assertEquals($findServicePresenter->response->maxCheckAttempts, $this->service->getMaxCheckAttempts());
    $this->assertEquals($findServicePresenter->response->lastTimeOk, $this->service->getLastTimeOk());
    $this->assertEquals($findServicePresenter->response->lastCheck, $this->service->getLastCheck());
    $this->assertEquals($findServicePresenter->response->nextCheck, $this->service->getNextCheck());
    $this->assertEquals($findServicePresenter->response->lastNotification, $this->service->getLastNotification());
    $this->assertEquals($findServicePresenter->response->lastStatusChange, $this->service->getLastStatusChange());
    $this->assertEquals($findServicePresenter->response->status['code'], $this->service->getStatus()->getCode());
    $this->assertEquals($findServicePresenter->response->status['name'], $this->service->getStatus()->getName());
    $this->assertEquals($findServicePresenter->response->status['type'], $this->service->getStatus()->getType());
    $this->assertEquals(
        $findServicePresenter->response->status['severity_code'],
        $this->service->getStatus()->getOrder()
    );
    $this->assertEquals(
        $findServicePresenter->response->servicegroups[0]['id'],
        $this->service->getServicegroups()[0]->getId()
    );
    $this->assertEquals(
        $findServicePresenter->response->servicegroups[0]['name'],
        $this->service->getServicegroups()[0]->getName()
    );
    $this->assertEquals($findServicePresenter->response->icon['name'], $this->service->getIcon()?->getName());
    $this->assertEquals($findServicePresenter->response->icon['url'], $this->service->getIcon()?->getUrl());
    $this->assertEquals($findServicePresenter->response->downtimes[0]['id'], $this->downtime->getId());
    $this->assertEquals($findServicePresenter->response->downtimes[0]['service_id'], $this->downtime->getServiceId());
    $this->assertEquals($findServicePresenter->response->downtimes[0]['host_id'], $this->downtime->getHostId());
    $this->assertEquals($findServicePresenter->response->acknowledgement['id'], $this->acknowledgement->getId());
    $this->assertEquals(
        $findServicePresenter->response->acknowledgement['service_id'],
        $this->acknowledgement->getServiceId()
    );
    $this->assertEquals(
        $findServicePresenter->response->acknowledgement['host_id'],
        $this->acknowledgement->getHostId()
    );
});

it('FindService service found as non admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
        $this->nonAdminContact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository,
    );

    $this->hostRepository
        ->expects($this->once())
        ->method('findHostByIdAndAccessGroupIds')
        ->willReturn($this->host);

    $this->repository
        ->expects($this->once())
        ->method('findServiceByIdAndAccessGroupIds')
        ->willReturn($this->service);

    $this->servicegroupRepository
        ->expects($this->once())
        ->method('findAllByHostIdAndServiceIdAndAccessGroupIds')
        ->willReturn([$this->servicegroup]);

    $this->downtimeRepository
        ->expects($this->once())
        ->method('findOnGoingDowntimesByHostIdAndServiceId')
        ->willReturn([$this->downtime]);

    $this->acknowledgementRepository
        ->expects($this->once())
        ->method('findOnGoingAcknowledgementByHostIdAndServiceId')
        ->willReturn($this->acknowledgement);

    $findServicePresenter = new FindServicePresenterStub();

    $findService(1, 10, $findServicePresenter);

    $this->assertEquals($findServicePresenter->response->name, $this->service->getName());
    $this->assertEquals($findServicePresenter->response->id, $this->service->getId());
    $this->assertEquals(
        $findServicePresenter->response->host['monitoring_server_name'],
        $this->host->getMonitoringServerName()
    );
    $this->assertEquals($findServicePresenter->response->isFlapping, $this->service->isFlapping());
    $this->assertEquals($findServicePresenter->response->isAcknowledged, $this->service->isAcknowledged());
    $this->assertEquals($findServicePresenter->response->isInDowntime, $this->service->isInDowntime());
    $this->assertEquals($findServicePresenter->response->output, $this->service->getOutput());
    $this->assertEquals($findServicePresenter->response->commandLine, $this->service->getCommandLine());
    $this->assertEquals($findServicePresenter->response->performanceData, $this->service->getPerformanceData());
    $this->assertEquals($findServicePresenter->response->notificationNumber, $this->service->getNotificationNumber());
    $this->assertEquals($findServicePresenter->response->latency, $this->service->getLatency());
    $this->assertEquals($findServicePresenter->response->executionTime, $this->service->getExecutionTime());
    $this->assertEquals(
        $findServicePresenter->response->statusChangePercentage,
        $this->service->getStatusChangePercentage()
    );
    $this->assertEquals($findServicePresenter->response->hasActiveChecks, $this->service->hasActiveChecks());
    $this->assertEquals($findServicePresenter->response->hasPassiveChecks, $this->service->hasPassiveChecks());
    $this->assertEquals($findServicePresenter->response->severityLevel, $this->service->getSeverityLevel());
    $this->assertEquals($findServicePresenter->response->checkAttempts, $this->service->getCheckAttempts());
    $this->assertEquals($findServicePresenter->response->maxCheckAttempts, $this->service->getMaxCheckAttempts());
    $this->assertEquals($findServicePresenter->response->lastTimeOk, $this->service->getLastTimeOk());
    $this->assertEquals($findServicePresenter->response->lastCheck, $this->service->getLastCheck());
    $this->assertEquals($findServicePresenter->response->nextCheck, $this->service->getNextCheck());
    $this->assertEquals($findServicePresenter->response->lastNotification, $this->service->getLastNotification());
    $this->assertEquals($findServicePresenter->response->lastStatusChange, $this->service->getLastStatusChange());
    $this->assertEquals($findServicePresenter->response->status['code'], $this->service->getStatus()->getCode());
    $this->assertEquals($findServicePresenter->response->status['name'], $this->service->getStatus()->getName());
    $this->assertEquals($findServicePresenter->response->status['type'], $this->service->getStatus()->getType());
    $this->assertEquals(
        $findServicePresenter->response->status['severity_code'],
        $this->service->getStatus()->getOrder()
    );
    $this->assertEquals(
        $findServicePresenter->response->servicegroups[0]['id'],
        $this->service->getServicegroups()[0]->getId()
    );
    $this->assertEquals(
        $findServicePresenter->response->servicegroups[0]['name'],
        $this->service->getServicegroups()[0]->getName()
    );
    $this->assertEquals($findServicePresenter->response->icon['name'], $this->service->getIcon()?->getName());
    $this->assertEquals($findServicePresenter->response->icon['url'], $this->service->getIcon()?->getUrl());
    $this->assertEquals($findServicePresenter->response->downtimes[0]['id'], $this->downtime->getId());
    $this->assertEquals($findServicePresenter->response->downtimes[0]['service_id'], $this->downtime->getServiceId());
    $this->assertEquals($findServicePresenter->response->downtimes[0]['host_id'], $this->downtime->getHostId());
    $this->assertEquals($findServicePresenter->response->acknowledgement['id'], $this->acknowledgement->getId());
    $this->assertEquals(
        $findServicePresenter->response->acknowledgement['service_id'],
        $this->acknowledgement->getServiceId()
    );
    $this->assertEquals(
        $findServicePresenter->response->acknowledgement['host_id'],
        $this->acknowledgement->getHostId()
    );
});

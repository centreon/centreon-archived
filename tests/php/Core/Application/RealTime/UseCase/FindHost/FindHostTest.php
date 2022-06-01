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

namespace Tests\Core\Application\RealTime\UseCase\FindHost;

use Centreon\Domain\Contact\Contact;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Hostgroup;
use Tests\Core\Domain\RealTime\Model\HostTest;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\RealTime\UseCase\FindHost\FindHost;
use Core\Infrastructure\RealTime\Api\FindHost\FindHostPresenter;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaCreator;
use Core\Application\RealTime\Repository\ReadTagRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadHostgroupRepositoryInterface;
use Core\Security\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Core\Domain\RealTime\Model\Tag;

beforeEach(function () {
    $this->repository = $this->createMock(ReadHostRepositoryInterface::class);
    $this->hostgroupRepository = $this->createMock(ReadHostgroupRepositoryInterface::class);
    $this->accessGroupRepository = $this->createMock(ReadAccessGroupRepositoryInterface::class);
    $this->downtimeRepository = $this->createMock(ReadDowntimeRepositoryInterface::class);
    $this->acknowledgementRepository = $this->createMock(ReadAcknowledgementRepositoryInterface::class);
    $this->hypermediaCreator = $this->createMock(HypermediaCreator::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    $this->monitoringService = $this->createMock(MonitoringServiceInterface::class);
    $this->tagRepository = $this->createMock(ReadTagRepositoryInterface::class);

    $this->acknowledgement = new Acknowledgement(1, 1, 10, new \DateTime('1991-09-10'));
    $this->host = HostTest::createHostModel();

    $this->nonAdminContact = (new Contact())
        ->setId(2)
        ->setName('user')
        ->setAdmin(false);

    $this->adminContact = (new Contact())
        ->setId(1)
        ->setName('admin')
        ->setAdmin(true);

    $this->hostgroup = new Hostgroup(10, 'ALL');
    $this->category = new Tag(1, 'host-category-name', Tag::HOST_CATEGORY_TYPE_ID);
    $this->downtime = (new Downtime(1, 1, 10))
        ->setCancelled(false);
});

it('FindHost not found response as admin', function () {
    $findHost = new FindHost(
        $this->repository,
        $this->hostgroupRepository,
        $this->adminContact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository
    );

    $this->repository
        ->expects($this->once())
        ->method('findHostById')
        ->willReturn(null);

    $findHostPresenter = new FindHostPresenter($this->hypermediaCreator, $this->presenterFormatter);
    $findHost(1, $findHostPresenter);
    $this->assertEquals($findHostPresenter->getResponseStatus(), new NotFoundResponse('Host'));
});

it('FindHost not found response as non admin', function () {
    $findHost = new FindHost(
        $this->repository,
        $this->hostgroupRepository,
        $this->nonAdminContact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository
    );

    $this->accessGroupRepository
        ->expects($this->once())
        ->method('findByContact')
        ->willReturn([]);

    $this->repository
        ->expects($this->once())
        ->method('findHostByIdAndAccessGroupIds')
        ->willReturn(null);

    $findHostPresenter = new FindHostPresenter($this->hypermediaCreator, $this->presenterFormatter);
    $findHost(1, $findHostPresenter);
    $this->assertEquals($findHostPresenter->getResponseStatus(), new NotFoundResponse('Host'));
});

it('FindHost as admin', function () {
    $findHost = new FindHost(
        $this->repository,
        $this->hostgroupRepository,
        $this->adminContact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository
    );

    $this->repository
        ->expects($this->once())
        ->method('findHostById')
        ->willReturn($this->host);

    $this->downtimeRepository
        ->expects($this->once())
        ->method('findOnGoingDowntimesByHostId')
        ->willReturn([$this->downtime]);

    $this->acknowledgementRepository
        ->expects($this->once())
        ->method('findOnGoingAcknowledgementByHostId')
        ->willReturn($this->acknowledgement);

    $this->hostgroupRepository
        ->expects($this->once())
        ->method('findAllByHostId')
        ->willReturn([$this->hostgroup]);

    $this->tagRepository
        ->expects($this->once())
        ->method('findAllByResourceAndTypeId')
        ->willReturn([$this->category]);

    $findHostPresenter = new FindHostPresenterStub();

    $findHost(1, $findHostPresenter);

    $this->assertEquals($findHostPresenter->response->name, $this->host->getName());
    $this->assertEquals($findHostPresenter->response->id, $this->host->getId());
    $this->assertEquals($findHostPresenter->response->address, $this->host->getAddress());
    $this->assertEquals($findHostPresenter->response->monitoringServerName, $this->host->getMonitoringServerName());
    $this->assertEquals($findHostPresenter->response->timezone, $this->host->getTimezone());
    $this->assertEquals($findHostPresenter->response->alias, $this->host->getAlias());
    $this->assertEquals($findHostPresenter->response->isFlapping, $this->host->isFlapping());
    $this->assertEquals($findHostPresenter->response->isAcknowledged, $this->host->isAcknowledged());
    $this->assertEquals($findHostPresenter->response->isInDowntime, $this->host->isInDowntime());
    $this->assertEquals($findHostPresenter->response->output, $this->host->getOutput());
    $this->assertEquals($findHostPresenter->response->commandLine, $this->host->getCommandLine());
    $this->assertEquals($findHostPresenter->response->performanceData, $this->host->getPerformanceData());
    $this->assertEquals($findHostPresenter->response->notificationNumber, $this->host->getNotificationNumber());
    $this->assertEquals($findHostPresenter->response->latency, $this->host->getLatency());
    $this->assertEquals($findHostPresenter->response->executionTime, $this->host->getExecutionTime());
    $this->assertEquals($findHostPresenter->response->statusChangePercentage, $this->host->getStatusChangePercentage());
    $this->assertEquals($findHostPresenter->response->hasActiveChecks, $this->host->hasActiveChecks());
    $this->assertEquals($findHostPresenter->response->hasPassiveChecks, $this->host->hasPassiveChecks());
    $this->assertEquals($findHostPresenter->response->severityLevel, $this->host->getSeverityLevel());
    $this->assertEquals($findHostPresenter->response->checkAttempts, $this->host->getCheckAttempts());
    $this->assertEquals($findHostPresenter->response->maxCheckAttempts, $this->host->getMaxCheckAttempts());
    $this->assertEquals($findHostPresenter->response->lastTimeUp, $this->host->getLastTimeUp());
    $this->assertEquals($findHostPresenter->response->lastCheck, $this->host->getLastCheck());
    $this->assertEquals($findHostPresenter->response->nextCheck, $this->host->getNextCheck());
    $this->assertEquals($findHostPresenter->response->lastNotification, $this->host->getLastNotification());
    $this->assertEquals($findHostPresenter->response->lastStatusChange, $this->host->getLastStatusChange());
    $this->assertEquals($findHostPresenter->response->status['code'], $this->host->getStatus()->getCode());
    $this->assertEquals($findHostPresenter->response->status['name'], $this->host->getStatus()->getName());
    $this->assertEquals($findHostPresenter->response->status['type'], $this->host->getStatus()->getType());
    $this->assertEquals($findHostPresenter->response->status['severity_code'], $this->host->getStatus()->getOrder());
    $this->assertEquals($findHostPresenter->response->hostgroups[0]['id'], $this->host->getHostgroups()[0]->getId());
    $this->assertEquals(
        $findHostPresenter->response->hostgroups[0]['name'],
        $this->host->getHostgroups()[0]->getName()
    );
    $this->assertEquals($findHostPresenter->response->icon['name'], $this->host->getIcon()?->getName());
    $this->assertEquals($findHostPresenter->response->icon['url'], $this->host->getIcon()?->getUrl());
    $this->assertEquals($findHostPresenter->response->downtimes[0]['id'], $this->downtime->getId());
    $this->assertEquals($findHostPresenter->response->downtimes[0]['service_id'], $this->downtime->getServiceId());
    $this->assertEquals($findHostPresenter->response->downtimes[0]['host_id'], $this->downtime->getHostId());
    $this->assertEquals($findHostPresenter->response->acknowledgement['id'], $this->acknowledgement->getId());
    $this->assertEquals(
        $findHostPresenter->response->acknowledgement['service_id'],
        $this->acknowledgement->getServiceId()
    );
    $this->assertEquals($findHostPresenter->response->acknowledgement['host_id'], $this->acknowledgement->getHostId());
    $this->assertEquals($findHostPresenter->response->categories[0]['id'], $this->category->getId());
    $this->assertEquals($findHostPresenter->response->categories[0]['name'], $this->category->getName());
});

it('FindHost as non admin', function () {
    $findHost = new FindHost(
        $this->repository,
        $this->hostgroupRepository,
        $this->nonAdminContact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository
    );

    $this->repository
        ->expects($this->once())
        ->method('findHostByIdAndAccessGroupIds')
        ->willReturn($this->host);

    /**
     * Ajouter les downtimes + ack
     */
    $this->downtimeRepository
        ->expects($this->once())
        ->method('findOnGoingDowntimesByHostId')
        ->willReturn([$this->downtime]);

    $this->acknowledgementRepository
        ->expects($this->once())
        ->method('findOnGoingAcknowledgementByHostId')
        ->willReturn($this->acknowledgement);

    $this->hostgroupRepository
        ->expects($this->once())
        ->method('findAllByHostIdAndAccessGroupIds')
        ->willReturn([$this->hostgroup]);

    $this->tagRepository
        ->expects($this->once())
        ->method('findAllByResourceAndTypeId')
        ->willReturn([$this->category]);

    $findHostPresenter = new FindHostPresenterStub();

    $findHost(1, $findHostPresenter);

    $this->assertEquals($findHostPresenter->response->name, $this->host->getName());
    $this->assertEquals($findHostPresenter->response->id, $this->host->getId());
    $this->assertEquals($findHostPresenter->response->address, $this->host->getAddress());
    $this->assertEquals($findHostPresenter->response->monitoringServerName, $this->host->getMonitoringServerName());
    $this->assertEquals($findHostPresenter->response->timezone, $this->host->getTimezone());
    $this->assertEquals($findHostPresenter->response->alias, $this->host->getAlias());
    $this->assertEquals($findHostPresenter->response->isFlapping, $this->host->isFlapping());
    $this->assertEquals($findHostPresenter->response->isAcknowledged, $this->host->isAcknowledged());
    $this->assertEquals($findHostPresenter->response->isInDowntime, $this->host->isInDowntime());
    $this->assertEquals($findHostPresenter->response->output, $this->host->getOutput());
    $this->assertEquals($findHostPresenter->response->commandLine, $this->host->getCommandLine());
    $this->assertEquals($findHostPresenter->response->performanceData, $this->host->getPerformanceData());
    $this->assertEquals($findHostPresenter->response->notificationNumber, $this->host->getNotificationNumber());
    $this->assertEquals($findHostPresenter->response->latency, $this->host->getLatency());
    $this->assertEquals($findHostPresenter->response->executionTime, $this->host->getExecutionTime());
    $this->assertEquals($findHostPresenter->response->statusChangePercentage, $this->host->getStatusChangePercentage());
    $this->assertEquals($findHostPresenter->response->hasActiveChecks, $this->host->hasActiveChecks());
    $this->assertEquals($findHostPresenter->response->hasPassiveChecks, $this->host->hasPassiveChecks());
    $this->assertEquals($findHostPresenter->response->severityLevel, $this->host->getSeverityLevel());
    $this->assertEquals($findHostPresenter->response->checkAttempts, $this->host->getCheckAttempts());
    $this->assertEquals($findHostPresenter->response->maxCheckAttempts, $this->host->getMaxCheckAttempts());
    $this->assertEquals($findHostPresenter->response->lastTimeUp, $this->host->getLastTimeUp());
    $this->assertEquals($findHostPresenter->response->lastCheck, $this->host->getLastCheck());
    $this->assertEquals($findHostPresenter->response->nextCheck, $this->host->getNextCheck());
    $this->assertEquals($findHostPresenter->response->lastNotification, $this->host->getLastNotification());
    $this->assertEquals($findHostPresenter->response->lastStatusChange, $this->host->getLastStatusChange());
    $this->assertEquals($findHostPresenter->response->status['code'], $this->host->getStatus()->getCode());
    $this->assertEquals($findHostPresenter->response->status['name'], $this->host->getStatus()->getName());
    $this->assertEquals($findHostPresenter->response->status['type'], $this->host->getStatus()->getType());
    $this->assertEquals($findHostPresenter->response->status['severity_code'], $this->host->getStatus()->getOrder());
    $this->assertEquals($findHostPresenter->response->hostgroups[0]['id'], $this->host->getHostgroups()[0]->getId());
    $this->assertEquals(
        $findHostPresenter->response->hostgroups[0]['name'],
        $this->host->getHostgroups()[0]->getName()
    );
    $this->assertEquals($findHostPresenter->response->icon['name'], $this->host->getIcon()?->getName());
    $this->assertEquals($findHostPresenter->response->icon['url'], $this->host->getIcon()?->getUrl());
    $this->assertEquals($findHostPresenter->response->downtimes[0]['id'], $this->downtime->getId());
    $this->assertEquals($findHostPresenter->response->downtimes[0]['service_id'], $this->downtime->getServiceId());
    $this->assertEquals($findHostPresenter->response->downtimes[0]['host_id'], $this->downtime->getHostId());
    $this->assertEquals($findHostPresenter->response->acknowledgement['id'], $this->acknowledgement->getId());
    $this->assertEquals(
        $findHostPresenter->response->acknowledgement['service_id'],
        $this->acknowledgement->getServiceId()
    );
    $this->assertEquals($findHostPresenter->response->acknowledgement['host_id'], $this->acknowledgement->getHostId());
    $this->assertEquals(
        $findHostPresenter->response->acknowledgement['entry_time'],
        $this->acknowledgement->getEntryTime()
    );
    $this->assertEquals($findHostPresenter->response->categories[0]['id'], $this->category->getId());
    $this->assertEquals($findHostPresenter->response->categories[0]['name'], $this->category->getName());
});

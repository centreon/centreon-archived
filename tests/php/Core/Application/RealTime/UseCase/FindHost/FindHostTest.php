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

use Core\Domain\RealTime\Model\Icon;
use Core\Tag\RealTime\Domain\Model\Tag;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Hostgroup;
use Tests\Core\Domain\RealTime\Model\HostTest;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Severity\RealTime\Domain\Model\Severity;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\RealTime\UseCase\FindHost\FindHost;
use Core\Infrastructure\RealTime\Hypermedia\HypermediaCreator;
use Core\Infrastructure\RealTime\Api\FindHost\FindHostPresenter;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Tag\RealTime\Application\Repository\ReadTagRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadHostgroupRepositoryInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Core\Severity\RealTime\Application\Repository\ReadSeverityRepositoryInterface;

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
    $this->severityRepository = $this->createMock(ReadSeverityRepositoryInterface::class);

    $this->acknowledgement = new Acknowledgement(1, 1, 10, new \DateTime('1991-09-10'));
    $this->host = (HostTest::createHostModel())
        ->setIsInDowntime(true)
        ->setIsAcknowledged(true);

    $this->contact = $this->createMock(ContactInterface::class);

    $this->hostgroup = new Hostgroup(10, 'ALL');
    $this->category = new Tag(1, 'host-category-name', Tag::HOST_CATEGORY_TYPE_ID);
    $this->downtime = (new Downtime(1, 1, 10))
        ->setCancelled(false);

    $icon = (new Icon())->setId(1)->setName('centreon')->setUrl('ppm/centreon.png');
    $this->severity = new Severity(1, 'severityName', 10, Severity::HOST_SEVERITY_TYPE_ID, $icon);
});

it('FindHost not found response as admin', function () {
    $findHost = new FindHost(
        $this->repository,
        $this->hostgroupRepository,
        $this->contact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository,
        $this->severityRepository
    );

    $this->contact
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $this->repository
        ->expects($this->once())
        ->method('findHostById')
        ->willReturn(null);

    $presenter = new FindHostPresenter($this->hypermediaCreator, $this->presenterFormatter);
    $findHost(1, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'Host not found'
    );
});

it('should present a not found response as non admin', function () {
    $findHost = new FindHost(
        $this->repository,
        $this->hostgroupRepository,
        $this->contact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository,
        $this->severityRepository
    );

    $this->contact
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(false);

    $this->accessGroupRepository
        ->expects($this->once())
        ->method('findByContact')
        ->willReturn([]);

    $this->repository
        ->expects($this->once())
        ->method('findHostByIdAndAccessGroupIds')
        ->willReturn(null);

    $presenter = new FindHostPresenter($this->hypermediaCreator, $this->presenterFormatter);
    $findHost(1, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'Host not found'
    );
});

it('should find the host as admin', function () {
    $findHost = new FindHost(
        $this->repository,
        $this->hostgroupRepository,
        $this->contact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository,
        $this->severityRepository
    );

    $this->contact
        ->expects($this->any())
        ->method('isAdmin')
        ->willReturn(true);

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

    $this->severityRepository
        ->expects($this->once())
        ->method('findByResourceAndTypeId')
        ->willReturn($this->severity);

    $presenter = new FindHostPresenterStub();

    $findHost(1, $presenter);

    expect($presenter->response->name)->toBe($this->host->getName());
    expect($presenter->response->hostId)->toBe($this->host->getId());
    expect($presenter->response->address)->toBe($this->host->getAddress());
    expect($presenter->response->monitoringServerName)->toBe($this->host->getMonitoringServerName());
    expect($presenter->response->timezone)->toBe($this->host->getTimezone());
    expect($presenter->response->alias)->toBe($this->host->getAlias());
    expect($presenter->response->isFlapping)->toBe($this->host->isFlapping());
    expect($presenter->response->isAcknowledged)->toBe($this->host->isAcknowledged());
    expect($presenter->response->isInDowntime)->toBe($this->host->isInDowntime());
    expect($presenter->response->output)->toBe($this->host->getOutput());
    expect($presenter->response->commandLine)->toBe($this->host->getCommandLine());
    expect($presenter->response->performanceData)->toBe($this->host->getPerformanceData());
    expect($presenter->response->notificationNumber)->toBe($this->host->getNotificationNumber());
    expect($presenter->response->latency)->toBe($this->host->getLatency());
    expect($presenter->response->executionTime)->toBe($this->host->getExecutionTime());
    expect($presenter->response->statusChangePercentage)->toBe($this->host->getStatusChangePercentage());
    expect($presenter->response->hasActiveChecks)->toBe($this->host->hasActiveChecks());
    expect($presenter->response->hasPassiveChecks)->toBe($this->host->hasPassiveChecks());
    expect($presenter->response->checkAttempts)->toBe($this->host->getCheckAttempts());
    expect($presenter->response->maxCheckAttempts)->toBe($this->host->getMaxCheckAttempts());
    expect($presenter->response->lastTimeUp)->toBe($this->host->getLastTimeUp());
    expect($presenter->response->lastCheck)->toBe($this->host->getLastCheck());
    expect($presenter->response->nextCheck)->toBe($this->host->getNextCheck());
    expect($presenter->response->lastNotification)->toBe($this->host->getLastNotification());
    expect($presenter->response->lastStatusChange)->toBe($this->host->getLastStatusChange());
    expect($presenter->response->status['code'])->toBe($this->host->getStatus()->getCode());
    expect($presenter->response->status['name'])->toBe($this->host->getStatus()->getName());
    expect($presenter->response->status['type'])->toBe($this->host->getStatus()->getType());
    expect($presenter->response->status['severity_code'])->toBe($this->host->getStatus()->getOrder());
    expect($presenter->response->groups[0]['id'])->toBe($this->host->getGroups()[0]->getId());
    expect($presenter->response->groups[0]['name'])->toBe($this->host->getGroups()[0]->getName());
    expect($presenter->response->icon['name'])->toBe($this->host->getIcon()?->getName());
    expect($presenter->response->icon['url'])->toBe($this->host->getIcon()?->getUrl());
    expect($presenter->response->downtimes[0]['id'])->toBe($this->downtime->getId());
    expect($presenter->response->downtimes[0]['service_id'])->toBe($this->downtime->getServiceId());
    expect($presenter->response->downtimes[0]['host_id'])->toBe($this->downtime->getHostId());
    expect($presenter->response->acknowledgement['id'])->toBe($this->acknowledgement->getId());
    expect($presenter->response->acknowledgement['service_id'])->toBe($this->acknowledgement->getServiceId());
    expect($presenter->response->acknowledgement['host_id'])->toBe($this->acknowledgement->getHostId());
    expect($presenter->response->categories[0]['id'])->toBe($this->category->getId());
    expect($presenter->response->categories[0]['name'])->toBe($this->category->getName());

    /**
     * @var array<string, mixed> $severity
     */
    $severity = $presenter->response->severity;
    expect($severity['id'])->toBe($this->severity->getId());
    expect($severity['name'])->toBe($this->severity->getName());
    expect($severity['type'])->toBe($this->severity->getTypeAsString());
    expect($severity['level'])->toBe($this->severity->getLevel());
    expect($severity['icon']['id'])->toBe($this->severity->getIcon()->getId());
    expect($severity['icon']['name'])->toBe($this->severity->getIcon()->getName());
    expect($severity['icon']['url'])->toBe($this->severity->getIcon()->getUrl());
});

it('should find the host as non admin', function () {
    $findHost = new FindHost(
        $this->repository,
        $this->hostgroupRepository,
        $this->contact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository,
        $this->monitoringService,
        $this->tagRepository,
        $this->severityRepository
    );

    $this->contact
        ->expects($this->any())
        ->method('isAdmin')
        ->willReturn(false);

    $this->repository
        ->expects($this->once())
        ->method('findHostByIdAndAccessGroupIds')
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
        ->method('findAllByHostIdAndAccessGroupIds')
        ->willReturn([$this->hostgroup]);

    $this->tagRepository
        ->expects($this->once())
        ->method('findAllByResourceAndTypeId')
        ->willReturn([$this->category]);

    $this->severityRepository
        ->expects($this->once())
        ->method('findByResourceAndTypeId')
        ->willReturn($this->severity);

    $presenter = new FindHostPresenterStub();

    $findHost(1, $presenter);

    expect($presenter->response->name)->toBe($this->host->getName());
    expect($presenter->response->hostId)->toBe($this->host->getId());
    expect($presenter->response->address)->toBe($this->host->getAddress());
    expect($presenter->response->monitoringServerName)->toBe($this->host->getMonitoringServerName());
    expect($presenter->response->timezone)->toBe($this->host->getTimezone());
    expect($presenter->response->alias)->toBe($this->host->getAlias());
    expect($presenter->response->isFlapping)->toBe($this->host->isFlapping());
    expect($presenter->response->isAcknowledged)->toBe($this->host->isAcknowledged());
    expect($presenter->response->isInDowntime)->toBe($this->host->isInDowntime());
    expect($presenter->response->output)->toBe($this->host->getOutput());
    expect($presenter->response->commandLine)->toBe($this->host->getCommandLine());
    expect($presenter->response->performanceData)->toBe($this->host->getPerformanceData());
    expect($presenter->response->notificationNumber)->toBe($this->host->getNotificationNumber());
    expect($presenter->response->latency)->toBe($this->host->getLatency());
    expect($presenter->response->executionTime)->toBe($this->host->getExecutionTime());
    expect($presenter->response->statusChangePercentage)->toBe($this->host->getStatusChangePercentage());
    expect($presenter->response->hasActiveChecks)->toBe($this->host->hasActiveChecks());
    expect($presenter->response->hasPassiveChecks)->toBe($this->host->hasPassiveChecks());
    expect($presenter->response->checkAttempts)->toBe($this->host->getCheckAttempts());
    expect($presenter->response->maxCheckAttempts)->toBe($this->host->getMaxCheckAttempts());
    expect($presenter->response->lastTimeUp)->toBe($this->host->getLastTimeUp());
    expect($presenter->response->lastCheck)->toBe($this->host->getLastCheck());
    expect($presenter->response->nextCheck)->toBe($this->host->getNextCheck());
    expect($presenter->response->lastNotification)->toBe($this->host->getLastNotification());
    expect($presenter->response->lastStatusChange)->toBe($this->host->getLastStatusChange());
    expect($presenter->response->status['code'])->toBe($this->host->getStatus()->getCode());
    expect($presenter->response->status['name'])->toBe($this->host->getStatus()->getName());
    expect($presenter->response->status['type'])->toBe($this->host->getStatus()->getType());
    expect($presenter->response->status['severity_code'])->toBe($this->host->getStatus()->getOrder());
    expect($presenter->response->groups[0]['id'])->toBe($this->host->getGroups()[0]->getId());
    expect($presenter->response->groups[0]['name'])->toBe($this->host->getGroups()[0]->getName());
    expect($presenter->response->icon['name'])->toBe($this->host->getIcon()?->getName());
    expect($presenter->response->icon['url'])->toBe($this->host->getIcon()?->getUrl());
    expect($presenter->response->downtimes[0]['id'])->toBe($this->downtime->getId());
    expect($presenter->response->downtimes[0]['service_id'])->toBe($this->downtime->getServiceId());
    expect($presenter->response->downtimes[0]['host_id'])->toBe($this->downtime->getHostId());
    expect($presenter->response->acknowledgement['id'])->toBe($this->acknowledgement->getId());
    expect($presenter->response->acknowledgement['service_id'])->toBe($this->acknowledgement->getServiceId());
    expect($presenter->response->acknowledgement['host_id'])->toBe($this->acknowledgement->getHostId());
    expect($presenter->response->acknowledgement['entry_time'])->toBe($this->acknowledgement->getEntryTime());
    expect($presenter->response->categories[0]['id'])->toBe($this->category->getId());
    expect($presenter->response->categories[0]['name'])->toBe($this->category->getName());

    /**
     * @var array<string, mixed> $severity
     */
    $severity = $presenter->response->severity;
    expect($severity['id'])->toBe($this->severity->getId());
    expect($severity['name'])->toBe($this->severity->getName());
    expect($severity['type'])->toBe($this->severity->getTypeAsString());
    expect($severity['level'])->toBe($this->severity->getLevel());
    expect($severity['icon']['id'])->toBe($this->severity->getIcon()->getId());
    expect($severity['icon']['name'])->toBe($this->severity->getIcon()->getName());
    expect($severity['icon']['url'])->toBe($this->severity->getIcon()->getUrl());
});

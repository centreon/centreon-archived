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

use Core\Domain\RealTime\Model\Icon;
use Core\Tag\RealTime\Domain\Model\Tag;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Servicegroup;
use Tests\Core\Domain\RealTime\Model\HostTest;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Severity\RealTime\Domain\Model\Severity;
use Tests\Core\Domain\RealTime\Model\ServiceTest;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\RealTime\UseCase\FindService\FindService;
use Core\Infrastructure\RealTime\Hypermedia\HypermediaCreator;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Infrastructure\RealTime\Api\FindService\FindServicePresenter;
use Core\Application\RealTime\Repository\ReadServiceRepositoryInterface;
use Core\Tag\RealTime\Application\Repository\ReadTagRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServicegroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Tests\Core\Application\RealTime\UseCase\FindService\FindServicePresenterStub;
use Core\Severity\RealTime\Application\Repository\ReadSeverityRepositoryInterface;

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
    $this->severityRepository = $this->createMock(ReadSeverityRepositoryInterface::class);

    $this->downtime = (new Downtime(1, 1, 10))
        ->setCancelled(false);

    $this->acknowledgement = new Acknowledgement(1, 1, 10, new \DateTime('1991-09-10'));

    $this->contact = $this->createMock(ContactInterface::class);

    $this->host = (HostTest::createHostModel())
        ->setIsInDowntime(true)
        ->setIsAcknowledged(true);

    $this->service = (ServiceTest::createServiceModel())
        ->setIsInDowntime(true)
        ->setIsAcknowledged(true);

    $this->servicegroup = new Servicegroup(1, 'ALL');
    $this->category = new Tag(1, 'service-category-name', Tag::SERVICE_CATEGORY_TYPE_ID);
    $icon = (new Icon())->setId(1)->setName('centreon')->setUrl('ppm/centreon.png');
    $this->severity = new Severity(1, 'severityName', 10, Severity::SERVICE_SEVERITY_TYPE_ID, $icon);
});

it('should present a NotFoundResponse if host not found as admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
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

    $this->hostRepository
        ->expects($this->once())
        ->method('findHostById')
        ->willReturn(null);

    $presenter = new FindServicePresenter($this->hypermediaCreator, $this->presenterFormatter);

    $findService(1, 20, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'Host not found'
    );
});

it('should present a NotFoundResponse if host not found as non admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
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

    $this->accessGroupRepository
        ->expects($this->once())
        ->method('findByContact')
        ->willReturn([]);

    $this->hostRepository
        ->expects($this->once())
        ->method('findHostByIdAndAccessGroupIds')
        ->willReturn(null);

    $presenter = new FindServicePresenter($this->hypermediaCreator, $this->presenterFormatter);

    $findService(1, 20, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'Host not found'
    );
});

it('should present a NotFoundResponse if service not found as admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
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

    $this->hostRepository
        ->expects($this->once())
        ->method('findHostById')
        ->willReturn(HostTest::createHostModel());

    $this->repository
        ->expects($this->once())
        ->method('findServiceById')
        ->willReturn(null);

    $presenter = new FindServicePresenter($this->hypermediaCreator, $this->presenterFormatter);

    $findService(1, 20, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'Service not found'
    );
});

it('should present a NotFoundResponse if service not found as non admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
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

    $presenter = new FindServicePresenter($this->hypermediaCreator, $this->presenterFormatter);

    $findService(1, 20, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'Service not found'
    );
});

it('should find service as admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
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

    $this->severityRepository
        ->expects($this->once())
        ->method('findByResourceAndTypeId')
        ->willReturn($this->severity);

    $presenter = new FindServicePresenterStub();

    $findService(1, 10, $presenter);

    expect($presenter->response->name)->toBe($this->service->getName());
    expect($presenter->response->serviceId)->toBe($this->service->getId());
    expect($presenter->response->host['monitoring_server_name'])
        ->toBe($this->host->getMonitoringServerName());
    expect($presenter->response->isFlapping)->toBe($this->service->isFlapping());
    expect($presenter->response->isAcknowledged)->toBe($this->service->isAcknowledged());
    expect($presenter->response->isInDowntime)->toBe($this->service->isInDowntime());
    expect($presenter->response->output)->toBe($this->service->getOutput());
    expect($presenter->response->commandLine)->toBe($this->service->getCommandLine());
    expect($presenter->response->performanceData)->toBe($this->service->getPerformanceData());
    expect($presenter->response->notificationNumber)->toBe($this->service->getNotificationNumber());
    expect($presenter->response->latency)->toBe($this->service->getLatency());
    expect($presenter->response->executionTime)->toBe($this->service->getExecutionTime());
    expect($presenter->response->statusChangePercentage)->toBe($this->service->getStatusChangePercentage());
    expect($presenter->response->hasActiveChecks)->toBe($this->service->hasActiveChecks());
    expect($presenter->response->hasPassiveChecks)->toBe($this->service->hasPassiveChecks());
    expect($presenter->response->checkAttempts)->toBe($this->service->getCheckAttempts());
    expect($presenter->response->maxCheckAttempts)->toBe($this->service->getMaxCheckAttempts());
    expect($presenter->response->lastTimeOk)->toBe($this->service->getLastTimeOk());
    expect($presenter->response->lastCheck)->toBe($this->service->getLastCheck());
    expect($presenter->response->nextCheck)->toBe($this->service->getNextCheck());
    expect($presenter->response->lastNotification)->toBe($this->service->getLastNotification());
    expect($presenter->response->lastStatusChange)->toBe($this->service->getLastStatusChange());
    expect($presenter->response->status['code'])->toBe($this->service->getStatus()->getCode());
    expect($presenter->response->status['name'])->toBe($this->service->getStatus()->getName());
    expect($presenter->response->status['type'])->toBe($this->service->getStatus()->getType());
    expect($presenter->response->status['severity_code'])->toBe($this->service->getStatus()->getOrder());
    expect($presenter->response->groups[0]['id'])
        ->toBe($this->service->getGroups()[0]->getId());
    expect($presenter->response->groups[0]['name'])
        ->toBe($this->service->getGroups()[0]->getName());
    expect($presenter->response->icon['name'])->toBe($this->service->getIcon()?->getName());
    expect($presenter->response->icon['url'])->toBe($this->service->getIcon()?->getUrl());
    expect($presenter->response->downtimes[0]['id'])->toBe($this->downtime->getId());
    expect($presenter->response->downtimes[0]['service_id'])->toBe($this->downtime->getServiceId());
    expect($presenter->response->downtimes[0]['host_id'])->toBe($this->downtime->getHostId());
    expect($presenter->response->acknowledgement['id'])->toBe($this->acknowledgement->getId());
    expect($presenter->response->acknowledgement['service_id'])
        ->toBe($this->acknowledgement->getServiceId());
    expect($presenter->response->acknowledgement['host_id'])->toBe($this->acknowledgement->getHostId());

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

it('FindService service found as non admin', function () {
    $findService = new FindService(
        $this->repository,
        $this->hostRepository,
        $this->servicegroupRepository,
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

    $this->severityRepository
        ->expects($this->once())
        ->method('findByResourceAndTypeId')
        ->willReturn($this->severity);

    $presenter = new FindServicePresenterStub();

    $findService(1, 10, $presenter);

    expect($presenter->response->name)->toBe($this->service->getName());
    expect($presenter->response->serviceId)->toBe($this->service->getId());
    expect($presenter->response->host['monitoring_server_name'])
        ->toBe($this->host->getMonitoringServerName());
    expect($presenter->response->isFlapping)->toBe($this->service->isFlapping());
    expect($presenter->response->isAcknowledged)->toBe($this->service->isAcknowledged());
    expect($presenter->response->isInDowntime)->toBe($this->service->isInDowntime());
    expect($presenter->response->output)->toBe($this->service->getOutput());
    expect($presenter->response->commandLine)->toBe($this->service->getCommandLine());
    expect($presenter->response->performanceData)->toBe($this->service->getPerformanceData());
    expect($presenter->response->notificationNumber)->toBe($this->service->getNotificationNumber());
    expect($presenter->response->latency)->toBe($this->service->getLatency());
    expect($presenter->response->executionTime)->toBe($this->service->getExecutionTime());
    expect($presenter->response->statusChangePercentage)->toBe($this->service->getStatusChangePercentage());
    expect($presenter->response->hasActiveChecks)->toBe($this->service->hasActiveChecks());
    expect($presenter->response->hasPassiveChecks)->toBe($this->service->hasPassiveChecks());
    expect($presenter->response->checkAttempts)->toBe($this->service->getCheckAttempts());
    expect($presenter->response->maxCheckAttempts)->toBe($this->service->getMaxCheckAttempts());
    expect($presenter->response->lastTimeOk)->toBe($this->service->getLastTimeOk());
    expect($presenter->response->lastCheck)->toBe($this->service->getLastCheck());
    expect($presenter->response->nextCheck)->toBe($this->service->getNextCheck());
    expect($presenter->response->lastNotification)->toBe($this->service->getLastNotification());
    expect($presenter->response->lastStatusChange)->toBe($this->service->getLastStatusChange());
    expect($presenter->response->status['code'])->toBe($this->service->getStatus()->getCode());
    expect($presenter->response->status['name'])->toBe($this->service->getStatus()->getName());
    expect($presenter->response->status['type'])->toBe($this->service->getStatus()->getType());
    expect($presenter->response->status['severity_code'])->toBe($this->service->getStatus()->getOrder());
    expect($presenter->response->groups[0]['id'])
        ->toBe($this->service->getGroups()[0]->getId());
    expect($presenter->response->groups[0]['name'])
        ->toBe($this->service->getGroups()[0]->getName());
    expect($presenter->response->icon['name'])->toBe($this->service->getIcon()?->getName());
    expect($presenter->response->icon['url'])->toBe($this->service->getIcon()?->getUrl());
    expect($presenter->response->downtimes[0]['id'])->toBe($this->downtime->getId());
    expect($presenter->response->downtimes[0]['service_id'])->toBe($this->downtime->getServiceId());
    expect($presenter->response->downtimes[0]['host_id'])->toBe($this->downtime->getHostId());
    expect($presenter->response->acknowledgement['id'])->toBe($this->acknowledgement->getId());
    expect($presenter->response->acknowledgement['service_id'])
        ->toBe($this->acknowledgement->getServiceId());
    expect($presenter->response->acknowledgement['host_id'])->toBe($this->acknowledgement->getHostId());

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

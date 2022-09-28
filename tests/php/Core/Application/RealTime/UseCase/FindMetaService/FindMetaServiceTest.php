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

namespace Tests\Core\Application\RealTime\UseCase\FindMetaService;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Application\Common\UseCase\NotFoundResponse;
use Tests\Core\Domain\RealTime\Model\MetaServiceTest;
use Core\Infrastructure\RealTime\Hypermedia\HypermediaCreator;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\RealTime\UseCase\FindMetaService\FindMetaService;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadMetaServiceRepositoryInterface;
use Core\Infrastructure\RealTime\Api\FindMetaService\FindMetaServicePresenter;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Tests\Core\Application\RealTime\UseCase\FindMetaService\FindMetaServicePresenterStub;
use Tests\Core\Domain\Configuration\MetaServiceTest as MetaServiceConfigurationTest;
use Core\Application\Configuration\MetaService\Repository\ReadMetaServiceRepositoryInterface as
    ReadMetaServiceConfigurationRepositoryInterface;

beforeEach(function () {
    $this->repository = $this->createMock(ReadMetaServiceRepositoryInterface::class);
    $this->configurationRepository = $this->createMock(ReadMetaServiceConfigurationRepositoryInterface::class);
    $this->accessGroupRepository = $this->createMock(ReadAccessGroupRepositoryInterface::class);
    $this->downtimeRepository = $this->createMock(ReadDowntimeRepositoryInterface::class);
    $this->acknowledgementRepository = $this->createMock(ReadAcknowledgementRepositoryInterface::class);
    $this->hypermediaCreator = $this->createMock(HypermediaCreator::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    $this->contact = $this->createMock(ContactInterface::class);
});

it('should present a NotFoundResponse if meta service configuration not found as admin', function () {
    $findMetaService = new FindMetaService(
        $this->repository,
        $this->configurationRepository,
        $this->contact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository
    );

    $this->contact
        ->expects($this->any())
        ->method('isAdmin')
        ->willReturn(true);

    $this->configurationRepository
        ->expects($this->once())
        ->method('findMetaServiceById')
        ->willReturn(null);

    $presenter = new FindMetaServicePresenter($this->hypermediaCreator, $this->presenterFormatter);
    $findMetaService(1, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'MetaService configuration not found'
    );
});

it('should present a NotFoundResponse if meta service configuration not found as non-admin', function () {
    $findMetaService = new FindMetaService(
        $this->repository,
        $this->configurationRepository,
        $this->contact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository
    );

    $this->contact
        ->expects($this->any())
        ->method('isAdmin')
        ->willReturn(false);

    $this->configurationRepository
        ->expects($this->once())
        ->method('findMetaServiceByIdAndAccessGroupIds')
        ->willReturn(null);

    $presenter = new FindMetaServicePresenter($this->hypermediaCreator, $this->presenterFormatter);
    $findMetaService(1, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'MetaService configuration not found'
    );
});

it('should present a NotFoundResponse if metaservice requested is not found as admin', function () {
    $findMetaService = new FindMetaService(
        $this->repository,
        $this->configurationRepository,
        $this->contact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository
    );

    $configuration = MetaServiceConfigurationTest::createMetaServiceModel();

    $this->contact
        ->expects($this->any())
        ->method('isAdmin')
        ->willReturn(true);

    $this->configurationRepository
        ->expects($this->once())
        ->method('findMetaServiceById')
        ->willReturn($configuration);

    $this->repository
        ->expects($this->once())
        ->method('findMetaServiceById')
        ->willReturn(null);

    $presenter = new FindMetaServicePresenter($this->hypermediaCreator, $this->presenterFormatter);
    $findMetaService(1, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'MetaService not found'
    );
});

it('should present a NotFoundResponse if metaservice requested is not found as non-admin', function () {
    $findMetaService = new FindMetaService(
        $this->repository,
        $this->configurationRepository,
        $this->contact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository
    );

    $this->contact
        ->expects($this->any())
        ->method('isAdmin')
        ->willReturn(false);

    $this->configurationRepository
        ->expects($this->once())
        ->method('findMetaServiceByIdAndAccessGroupIds')
        ->willReturn(MetaServiceConfigurationTest::createMetaServiceModel());

    $this->repository
        ->expects($this->once())
        ->method('findMetaServiceByIdAndAccessGroupIds')
        ->willReturn(null);

    $presenter = new FindMetaServicePresenter($this->hypermediaCreator, $this->presenterFormatter);
    $findMetaService(1, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(NotFoundResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'MetaService not found'
    );
});

it('should find the metaservice as non-admin', function () {
    $metaServiceConfiguration = MetaServiceConfigurationTest::createMetaServiceModel();
    $metaService = (MetaServiceTest::createMetaServiceModel())
        ->setIsInDowntime(true)
        ->setIsAcknowledged(true);

    $downtimes[] = (new Downtime(1, 1, 10))
        ->setCancelled(false);

    $acknowledgement = new Acknowledgement(1, 1, 10, new \DateTime('1991-09-10'));

    $this->contact
        ->expects($this->any())
        ->method('isAdmin')
        ->willReturn(false);

    $this->downtimeRepository
        ->expects($this->once())
        ->method('findOnGoingDowntimesByHostIdAndServiceId')
        ->willReturn($downtimes);

    $this->acknowledgementRepository
        ->expects($this->once())
        ->method('findOnGoingAcknowledgementByHostIdAndServiceId')
        ->willReturn($acknowledgement);

    $findMetaService = new FindMetaService(
        $this->repository,
        $this->configurationRepository,
        $this->contact,
        $this->accessGroupRepository,
        $this->downtimeRepository,
        $this->acknowledgementRepository
    );

    $this->configurationRepository
        ->expects($this->once())
        ->method('findMetaServiceByIdAndAccessGroupIds')
        ->willReturn($metaServiceConfiguration);

    $this->repository
        ->expects($this->once())
        ->method('findMetaServiceByIdAndAccessGroupIds')
        ->willReturn($metaService);

    $presenter = new FindMetaServicePresenterStub();
    $findMetaService(1, $presenter);

    expect($presenter->response->name)->toBe($metaService->getName());
    expect($presenter->response->metaId)->toBe($metaService->getId());
    expect($presenter->response->isFlapping)->toBe($metaService->isFlapping());
    expect($presenter->response->isAcknowledged)->toBe($metaService->isAcknowledged());
    expect($presenter->response->isInDowntime)->toBe($metaService->isInDowntime());
    expect($presenter->response->output)->toBe($metaService->getOutput());
    expect($presenter->response->commandLine)->toBe($metaService->getCommandLine());
    expect($presenter->response->performanceData)->toBe($metaService->getPerformanceData());
    expect($presenter->response->notificationNumber)->toBe($metaService->getNotificationNumber());
    expect($presenter->response->latency)->toBe($metaService->getLatency());
    expect($presenter->response->executionTime)->toBe($metaService->getExecutionTime());
    expect($presenter->response->statusChangePercentage)
        ->toBe($metaService->getStatusChangePercentage());
    expect($presenter->response->hasActiveChecks)->toBe($metaService->hasActiveChecks());
    expect($presenter->response->hasPassiveChecks)->toBe($metaService->hasPassiveChecks());
    expect($presenter->response->checkAttempts)->toBe($metaService->getCheckAttempts());
    expect($presenter->response->maxCheckAttempts)->toBe($metaService->getMaxCheckAttempts());
    expect($presenter->response->lastTimeOk)->toBe($metaService->getLastTimeOk());
    expect($presenter->response->lastCheck)->toBe($metaService->getLastCheck());
    expect($presenter->response->nextCheck)->toBe($metaService->getNextCheck());
    expect($presenter->response->lastNotification)->toBe($metaService->getLastNotification());
    expect($presenter->response->lastStatusChange, $metaService->getLastStatusChange());
    expect($presenter->response->status['code'])->toBe($metaService->getStatus()->getCode());
    expect($presenter->response->status['name'])->toBe($metaService->getStatus()->getName());
    expect($presenter->response->status['type'])->toBe($metaService->getStatus()->getType());
    expect($presenter->response->status['severity_code'])->toBe($metaService->getStatus()->getOrder());
    expect($presenter->response->downtimes[0]['id'])->toBe($downtimes[0]->getId());
    expect($presenter->response->downtimes[0]['service_id'])->toBe($downtimes[0]->getServiceId());
    expect($presenter->response->downtimes[0]['host_id'])->toBe($downtimes[0]->getHostId());
    expect($presenter->response->acknowledgement['id'])->toBe($acknowledgement->getId());
    expect($presenter->response->acknowledgement['service_id'])->toBe($acknowledgement->getServiceId());
    expect($presenter->response->acknowledgement['host_id'])->toBe($acknowledgement->getHostId());
    expect($presenter->response->calculationType)->toBe($metaServiceConfiguration->getCalculationType());
});

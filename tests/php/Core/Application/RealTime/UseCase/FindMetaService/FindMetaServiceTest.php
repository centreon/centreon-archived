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

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Application\Common\UseCase\NotFoundResponse;
use Tests\Core\Domain\RealTime\Model\MetaServiceTest;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaCreator;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\RealTime\UseCase\FindMetaService\FindMetaService;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadMetaServiceRepositoryInterface;
use Core\Infrastructure\RealTime\Api\FindMetaService\FindMetaServicePresenter;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Tests\Core\Application\RealTime\UseCase\FindMetaService\FindMetaServicePresenterStub;
use Tests\Core\Domain\Configuration\MetaServiceTest as MetaServiceConfigurationTest;
use Core\Application\Configuration\MetaService\Repository\ReadMetaServiceRepositoryInterface as
    ReadMetaServiceConfigurationRepositoryInterface;

class FindMetaServiceTest extends TestCase
{
    /**
     * @var ReadMetaServiceRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    /**
     * @var ReadMetaServiceConfigurationRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $configurationRepository;

    /**
     * @var AccessGroupRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $accessGroupRepository;

    /**
     * @var ReadDowntimeRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $downtimeRepository;

    /**
     * @var ReadAcknowledgementRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $acknowledgementRepository;

    /**
     * @var HypermediaCreator&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hypermediaCreator;

    /**
     * @var PresenterFormatterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenterFormatter;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ReadMetaServiceRepositoryInterface::class);
        $this->configurationRepository = $this->createMock(ReadMetaServiceConfigurationRepositoryInterface::class);
        $this->accessGroupRepository = $this->createMock(AccessGroupRepositoryInterface::class);
        $this->downtimeRepository = $this->createMock(ReadDowntimeRepositoryInterface::class);
        $this->acknowledgementRepository = $this->createMock(ReadAcknowledgementRepositoryInterface::class);
        $this->hypermediaCreator = $this->createMock(HypermediaCreator::class);
        $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    }

    /**
     * test requested meta service configuration not found
     */
    public function testMetaServiceConfigurationNotFoundAsAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $findMetaService = new FindMetaService(
            $this->repository,
            $this->configurationRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository
        );

        $this->configurationRepository
            ->expects($this->once())
            ->method('findMetaServiceById')
            ->willReturn(null);

        $findMetaServicePresenter = new FindMetaServicePresenter($this->hypermediaCreator, $this->presenterFormatter);
        $findMetaService(1, $findMetaServicePresenter);

        $this->assertEquals($findMetaServicePresenter->getResponseStatus(), new NotFoundResponse('MetaService configuration'));
    }

    /**
     * test requested meta service configuration not found
     */
    public function testMetaServiceConfigurationNotFoundAsNonAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(2)
            ->setName('user')
            ->setAdmin(false);

        $findMetaService = new FindMetaService(
            $this->repository,
            $this->configurationRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository
        );

        $this->configurationRepository
            ->expects($this->once())
            ->method('findMetaServiceByIdAndAccessGroupIds')
            ->willReturn(null);

        $findMetaServicePresenter = new FindMetaServicePresenter($this->hypermediaCreator, $this->presenterFormatter);
        $findMetaService(1, $findMetaServicePresenter);

        $this->assertEquals($findMetaServicePresenter->getResponseStatus(), new NotFoundResponse('MetaService configuration'));
    }

    /**
     * test requested meta service not found
     */
    public function testMetaServiceNotFoundAsAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $findMetaService = new FindMetaService(
            $this->repository,
            $this->configurationRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository
        );

        /**
         * @var MetaServiceConfigurationTest
         */
        $configuration = MetaServiceConfigurationTest::createMetaServiceModel();

        $this->configurationRepository
            ->expects($this->once())
            ->method('findMetaServiceById')
            ->willReturn($configuration);

        $this->repository
            ->expects($this->once())
            ->method('findMetaServiceById')
            ->willReturn(null);

        $findMetaServicePresenter = new FindMetaServicePresenter($this->hypermediaCreator, $this->presenterFormatter);
        $findMetaService(1, $findMetaServicePresenter);

        $this->assertEquals($findMetaServicePresenter->getResponseStatus(), new NotFoundResponse('MetaService'));
    }

    /**
     * test requested meta service configuration not found
     */
    public function testMetaServiceNotFoundAsNonAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(2)
            ->setName('user')
            ->setAdmin(false);

        $findMetaService = new FindMetaService(
            $this->repository,
            $this->configurationRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository
        );

        $this->configurationRepository
            ->expects($this->once())
            ->method('findMetaServiceByIdAndAccessGroupIds')
            ->willReturn(MetaServiceConfigurationTest::createMetaServiceModel());

        $this->repository
            ->expects($this->once())
            ->method('findMetaServiceByIdAndAccessGroupIds')
            ->willReturn(null);

        $findMetaServicePresenter = new FindMetaServicePresenter($this->hypermediaCreator, $this->presenterFormatter);
        $findMetaService(1, $findMetaServicePresenter);

        $this->assertEquals($findMetaServicePresenter->getResponseStatus(), new NotFoundResponse('MetaService'));
    }

    /**
     * test requested meta service found
     */
    public function testMetaServiceFound(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(2)
            ->setName('user')
            ->setAdmin(false);

        $metaServiceConfiguration = MetaServiceConfigurationTest::createMetaServiceModel();
        $metaService = MetaServiceTest::createMetaServiceModel();

        $downtimes[] = (new Downtime(1, 1, 10))
            ->setCancelled(false);

        $acknowledgement = new Acknowledgement(1, 1, 10, new \DateTime('1991-09-10'));

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
            $contact,
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

        $findMetaServicePresenter = new FindMetaServicePresenterStub();
        $findMetaService(1, $findMetaServicePresenter);


        $this->assertEquals($findMetaServicePresenter->response->name, $metaService->getName());
        $this->assertEquals($findMetaServicePresenter->response->id, $metaService->getId());
        $this->assertEquals($findMetaServicePresenter->response->isFlapping, $metaService->isFlapping());
        $this->assertEquals($findMetaServicePresenter->response->isAcknowledged, $metaService->isAcknowledged());
        $this->assertEquals($findMetaServicePresenter->response->isInDowntime, $metaService->isInDowntime());
        $this->assertEquals($findMetaServicePresenter->response->output, $metaService->getOutput());
        $this->assertEquals($findMetaServicePresenter->response->commandLine, $metaService->getCommandLine());
        $this->assertEquals($findMetaServicePresenter->response->performanceData, $metaService->getPerformanceData());
        $this->assertEquals(
            $findMetaServicePresenter->response->notificationNumber,
            $metaService->getNotificationNumber()
        );
        $this->assertEquals($findMetaServicePresenter->response->latency, $metaService->getLatency());
        $this->assertEquals($findMetaServicePresenter->response->executionTime, $metaService->getExecutionTime());
        $this->assertEquals(
            $findMetaServicePresenter->response->statusChangePercentage,
            $metaService->getStatusChangePercentage()
        );
        $this->assertEquals($findMetaServicePresenter->response->hasActiveChecks, $metaService->hasActiveChecks());
        $this->assertEquals($findMetaServicePresenter->response->hasPassiveChecks, $metaService->hasPassiveChecks());
        $this->assertEquals($findMetaServicePresenter->response->checkAttempts, $metaService->getCheckAttempts());
        $this->assertEquals($findMetaServicePresenter->response->maxCheckAttempts, $metaService->getMaxCheckAttempts());
        $this->assertEquals($findMetaServicePresenter->response->lastTimeOk, $metaService->getLastTimeOk());
        $this->assertEquals($findMetaServicePresenter->response->lastCheck, $metaService->getLastCheck());
        $this->assertEquals($findMetaServicePresenter->response->nextCheck, $metaService->getNextCheck());
        $this->assertEquals($findMetaServicePresenter->response->lastNotification, $metaService->getLastNotification());
        $this->assertEquals($findMetaServicePresenter->response->lastStatusChange, $metaService->getLastStatusChange());
        $this->assertEquals($findMetaServicePresenter->response->status['code'], $metaService->getStatus()->getCode());
        $this->assertEquals($findMetaServicePresenter->response->status['name'], $metaService->getStatus()->getName());
        $this->assertEquals($findMetaServicePresenter->response->status['type'], $metaService->getStatus()->getType());
        $this->assertEquals(
            $findMetaServicePresenter->response->status['severity_code'],
            $metaService->getStatus()->getOrder()
        );
        $this->assertEquals($findMetaServicePresenter->response->downtimes[0]['id'], $downtimes[0]->getId());
        $this->assertEquals(
            $findMetaServicePresenter->response->downtimes[0]['service_id'],
            $downtimes[0]->getServiceId()
        );
        $this->assertEquals($findMetaServicePresenter->response->downtimes[0]['host_id'], $downtimes[0]->getHostId());
        $this->assertEquals($findMetaServicePresenter->response->acknowledgement['id'], $acknowledgement->getId());
        $this->assertEquals(
            $findMetaServicePresenter->response->acknowledgement['service_id'],
            $acknowledgement->getServiceId()
        );
        $this->assertEquals(
            $findMetaServicePresenter->response->acknowledgement['host_id'],
            $acknowledgement->getHostId()
        );
        $this->assertEquals(
            $findMetaServicePresenter->response->calculationType,
            $metaServiceConfiguration->getCalculationType()
        );
    }
}

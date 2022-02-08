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

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Core\Domain\RealTime\Model\Host;
use Core\Domain\RealTime\Model\Service;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Servicegroup;
use Tests\Core\Domain\RealTime\Model\HostTest;
use Core\Domain\RealTime\Model\Acknowledgement;
use Tests\Core\Domain\RealTime\Model\ServiceTest;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\RealTime\UseCase\FindService\FindService;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaCreator;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Infrastructure\RealTime\Api\FindService\FindServicePresenter;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServiceRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServicegroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;

class FindServiceTest extends TestCase
{
    /**
     * @var ReadServiceRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    /**
     * @var ReadServicegroupRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $servicegroupRepository;

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

    /**
     * @var MonitoringServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $monitoringService;

    /**
     * @var ReadHostRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostRepository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ReadServiceRepositoryInterface::class);
        $this->servicegroupRepository = $this->createMock(ReadServicegroupRepositoryInterface::class);
        $this->accessGroupRepository = $this->createMock(AccessGroupRepositoryInterface::class);
        $this->downtimeRepository = $this->createMock(ReadDowntimeRepositoryInterface::class);
        $this->acknowledgementRepository = $this->createMock(ReadAcknowledgementRepositoryInterface::class);
        $this->hypermediaCreator = $this->createMock(HypermediaCreator::class);
        $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
        $this->monitoringService = $this->createMock(MonitoringServiceInterface::class);
        $this->hostRepository = $this->createMock(ReadHostRepositoryInterface::class);
    }

    /**
     * test requested host service not found with admin
     */
    public function testHostNotFoundAsAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $findService = new FindService(
            $this->repository,
            $this->hostRepository,
            $this->servicegroupRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository,
            $this->monitoringService
        );

        $this->hostRepository
            ->expects($this->once())
            ->method('findHostById')
            ->willReturn(null);

        $findServicePresenter = new FindServicePresenter($this->hypermediaCreator, $this->presenterFormatter);

        $findService(1, 20, $findServicePresenter);

        $this->assertEquals($findServicePresenter->getResponseStatus(), new NotFoundResponse('Host'));
    }

    /**
     * test requested host service not found with user under ACL
     */
    public function testHostNotFoundAsNonAdminUser(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(2)
            ->setName('user')
            ->setAdmin(false);

        $findService = new FindService(
            $this->repository,
            $this->hostRepository,
            $this->servicegroupRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository,
            $this->monitoringService
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
    }

    /**
     * test requested service not found with admin
     */
    public function testServiceNotFoundAsAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $findService = new FindService(
            $this->repository,
            $this->hostRepository,
            $this->servicegroupRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository,
            $this->monitoringService
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
    }

    /**
     * test requested service not found with user under ACL
     */
    public function testServiceNotFoundAsNonAdminUser(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(2)
            ->setName('user')
            ->setAdmin(false);

        $findService = new FindService(
            $this->repository,
            $this->hostRepository,
            $this->servicegroupRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository,
            $this->monitoringService
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
    }

    /**
     * test find service with an admin user
     */
    public function testFindServiceAsAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $findService = new FindService(
            $this->repository,
            $this->hostRepository,
            $this->servicegroupRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository,
            $this->monitoringService
        );

        /**
         * @var Host
         */
        $host = HostTest::createHostModel();

        /**
         * @var Service
         */
        $service = ServiceTest::createServiceModel();
        $servicegroup = new Servicegroup(1, 'ALL');

        $this->hostRepository
            ->expects($this->once())
            ->method('findHostById')
            ->willReturn($host);

        $this->repository
            ->expects($this->once())
            ->method('findServiceById')
            ->willReturn($service);

        $this->servicegroupRepository
            ->expects($this->once())
            ->method('findAllByHostIdAndServiceId')
            ->willReturn([$servicegroup]);

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

        $findServicePresenter = new FindServicePresenterStub();

        $findService(1, 10, $findServicePresenter);

        $this->assertEquals($findServicePresenter->response->name, $service->getName());
        $this->assertEquals($findServicePresenter->response->id, $service->getId());
        $this->assertEquals(
            $findServicePresenter->response->host['monitoring_server_name'],
            $host->getMonitoringServerName()
        );
        $this->assertEquals($findServicePresenter->response->isFlapping, $service->isFlapping());
        $this->assertEquals($findServicePresenter->response->isAcknowledged, $service->isAcknowledged());
        $this->assertEquals($findServicePresenter->response->isInDowntime, $service->isInDowntime());
        $this->assertEquals($findServicePresenter->response->output, $service->getOutput());
        $this->assertEquals($findServicePresenter->response->commandLine, $service->getCommandLine());
        $this->assertEquals($findServicePresenter->response->performanceData, $service->getPerformanceData());
        $this->assertEquals($findServicePresenter->response->notificationNumber, $service->getNotificationNumber());
        $this->assertEquals($findServicePresenter->response->latency, $service->getLatency());
        $this->assertEquals($findServicePresenter->response->executionTime, $service->getExecutionTime());
        $this->assertEquals(
            $findServicePresenter->response->statusChangePercentage,
            $service->getStatusChangePercentage()
        );
        $this->assertEquals($findServicePresenter->response->hasActiveChecks, $service->hasActiveChecks());
        $this->assertEquals($findServicePresenter->response->hasPassiveChecks, $service->hasPassiveChecks());
        $this->assertEquals($findServicePresenter->response->severityLevel, $service->getSeverityLevel());
        $this->assertEquals($findServicePresenter->response->checkAttempts, $service->getCheckAttempts());
        $this->assertEquals($findServicePresenter->response->maxCheckAttempts, $service->getMaxCheckAttempts());
        $this->assertEquals($findServicePresenter->response->lastTimeOk, $service->getLastTimeOk());
        $this->assertEquals($findServicePresenter->response->lastCheck, $service->getLastCheck());
        $this->assertEquals($findServicePresenter->response->nextCheck, $service->getNextCheck());
        $this->assertEquals($findServicePresenter->response->lastNotification, $service->getLastNotification());
        $this->assertEquals($findServicePresenter->response->lastStatusChange, $service->getLastStatusChange());
        $this->assertEquals($findServicePresenter->response->status['code'], $service->getStatus()->getCode());
        $this->assertEquals($findServicePresenter->response->status['name'], $service->getStatus()->getName());
        $this->assertEquals($findServicePresenter->response->status['type'], $service->getStatus()->getType());
        $this->assertEquals(
            $findServicePresenter->response->status['severity_code'],
            $service->getStatus()->getOrder()
        );
        $this->assertEquals(
            $findServicePresenter->response->servicegroups[0]['id'],
            $service->getServicegroups()[0]->getId()
        );
        $this->assertEquals(
            $findServicePresenter->response->servicegroups[0]['name'],
            $service->getServicegroups()[0]->getName()
        );
        $this->assertEquals($findServicePresenter->response->icon['name'], $service->getIcon()?->getName());
        $this->assertEquals($findServicePresenter->response->icon['url'], $service->getIcon()?->getUrl());
        $this->assertEquals($findServicePresenter->response->downtimes[0]['id'], $downtimes[0]->getId());
        $this->assertEquals($findServicePresenter->response->downtimes[0]['service_id'], $downtimes[0]->getServiceId());
        $this->assertEquals($findServicePresenter->response->downtimes[0]['host_id'], $downtimes[0]->getHostId());
        $this->assertEquals($findServicePresenter->response->acknowledgement['id'], $acknowledgement->getId());
        $this->assertEquals(
            $findServicePresenter->response->acknowledgement['service_id'],
            $acknowledgement->getServiceId()
        );
        $this->assertEquals($findServicePresenter->response->acknowledgement['host_id'], $acknowledgement->getHostId());
    }

    /**
     * test find service with an admin user
     */
    public function testFindServiceAsNonAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(1)
            ->setName('user')
            ->setAdmin(false);

        $findService = new FindService(
            $this->repository,
            $this->hostRepository,
            $this->servicegroupRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository,
            $this->monitoringService
        );

        $host = HostTest::createHostModel();
        $service = ServiceTest::createServiceModel();
        $servicegroup = new Servicegroup(1, 'ALL');

        $this->hostRepository
            ->expects($this->once())
            ->method('findHostByIdAndAccessGroupIds')
            ->willReturn($host);

        $this->repository
            ->expects($this->once())
            ->method('findServiceByIdAndAccessGroupIds')
            ->willReturn($service);

        $this->servicegroupRepository
            ->expects($this->once())
            ->method('findAllByHostIdAndServiceIdAndAccessGroupIds')
            ->willReturn([$servicegroup]);

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

        $findServicePresenter = new FindServicePresenterStub();

        $findService(1, 10, $findServicePresenter);

        $this->assertEquals($findServicePresenter->response->name, $service->getName());
        $this->assertEquals($findServicePresenter->response->id, $service->getId());
        $this->assertEquals(
            $findServicePresenter->response->host['monitoring_server_name'],
            $host->getMonitoringServerName()
        );
        $this->assertEquals($findServicePresenter->response->isFlapping, $service->isFlapping());
        $this->assertEquals($findServicePresenter->response->isAcknowledged, $service->isAcknowledged());
        $this->assertEquals($findServicePresenter->response->isInDowntime, $service->isInDowntime());
        $this->assertEquals($findServicePresenter->response->output, $service->getOutput());
        $this->assertEquals($findServicePresenter->response->commandLine, $service->getCommandLine());
        $this->assertEquals($findServicePresenter->response->performanceData, $service->getPerformanceData());
        $this->assertEquals($findServicePresenter->response->notificationNumber, $service->getNotificationNumber());
        $this->assertEquals($findServicePresenter->response->latency, $service->getLatency());
        $this->assertEquals($findServicePresenter->response->executionTime, $service->getExecutionTime());
        $this->assertEquals(
            $findServicePresenter->response->statusChangePercentage,
            $service->getStatusChangePercentage()
        );
        $this->assertEquals($findServicePresenter->response->hasActiveChecks, $service->hasActiveChecks());
        $this->assertEquals($findServicePresenter->response->hasPassiveChecks, $service->hasPassiveChecks());
        $this->assertEquals($findServicePresenter->response->severityLevel, $service->getSeverityLevel());
        $this->assertEquals($findServicePresenter->response->checkAttempts, $service->getCheckAttempts());
        $this->assertEquals($findServicePresenter->response->maxCheckAttempts, $service->getMaxCheckAttempts());
        $this->assertEquals($findServicePresenter->response->lastTimeOk, $service->getLastTimeOk());
        $this->assertEquals($findServicePresenter->response->lastCheck, $service->getLastCheck());
        $this->assertEquals($findServicePresenter->response->nextCheck, $service->getNextCheck());
        $this->assertEquals($findServicePresenter->response->lastNotification, $service->getLastNotification());
        $this->assertEquals($findServicePresenter->response->lastStatusChange, $service->getLastStatusChange());
        $this->assertEquals($findServicePresenter->response->status['code'], $service->getStatus()->getCode());
        $this->assertEquals($findServicePresenter->response->status['name'], $service->getStatus()->getName());
        $this->assertEquals($findServicePresenter->response->status['type'], $service->getStatus()->getType());
        $this->assertEquals(
            $findServicePresenter->response->status['severity_code'],
            $service->getStatus()->getOrder()
        );
        $this->assertEquals(
            $findServicePresenter->response->servicegroups[0]['id'],
            $service->getServicegroups()[0]->getId()
        );
        $this->assertEquals(
            $findServicePresenter->response->servicegroups[0]['name'],
            $service->getServicegroups()[0]->getName()
        );
        $this->assertEquals($findServicePresenter->response->icon['name'], $service->getIcon()?->getName());
        $this->assertEquals($findServicePresenter->response->icon['url'], $service->getIcon()?->getUrl());
        $this->assertEquals($findServicePresenter->response->downtimes[0]['id'], $downtimes[0]->getId());
        $this->assertEquals($findServicePresenter->response->downtimes[0]['service_id'], $downtimes[0]->getServiceId());
        $this->assertEquals($findServicePresenter->response->downtimes[0]['host_id'], $downtimes[0]->getHostId());
        $this->assertEquals($findServicePresenter->response->acknowledgement['id'], $acknowledgement->getId());
        $this->assertEquals(
            $findServicePresenter->response->acknowledgement['service_id'],
            $acknowledgement->getServiceId()
        );
        $this->assertEquals($findServicePresenter->response->acknowledgement['host_id'], $acknowledgement->getHostId());
    }
}

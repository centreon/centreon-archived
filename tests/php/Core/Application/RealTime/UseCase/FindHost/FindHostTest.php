<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Core\Domain\RealTime\Model\Host;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Hostgroup;
use Tests\Core\Domain\RealTime\Model\HostTest;
use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Application\Common\UseCase\NotFoundResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\RealTime\UseCase\FindHost\FindHost;
use Core\Infrastructure\RealTime\Api\FindHost\FindHostPresenter;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaService;
use Core\Application\RealTime\UseCase\FindHost\HostNotFoundResponse;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadHostgroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;

/**
 * @package Tests\Core\Application\RealTime\UseCase\FindHost
 */
class FindHostTest extends TestCase
{
    /**
     * @var ReadHostRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    /**
     * @var ReadHostgroupRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostgroupRepository;

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
     * @var HypermediaService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hypermediaService;

    /**
     * @var PresenterFormatterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenterFormatter;

    /**
     * @var MonitoringServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $monitoringService;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ReadHostRepositoryInterface::class);
        $this->hostgroupRepository = $this->createMock(ReadHostgroupRepositoryInterface::class);
        $this->accessGroupRepository = $this->createMock(AccessGroupRepositoryInterface::class);
        $this->downtimeRepository = $this->createMock(ReadDowntimeRepositoryInterface::class);
        $this->acknowledgementRepository = $this->createMock(ReadAcknowledgementRepositoryInterface::class);
        $this->hypermediaService = $this->createMock(HypermediaService::class);
        $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
        $this->monitoringService = $this->createMock(MonitoringServiceInterface::class);
    }

    public function testNotFoundAsAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $findHost = new FindHost(
            $this->repository,
            $this->hostgroupRepository,
            $contact,
            $this->accessGroupRepository,
            $this->downtimeRepository,
            $this->acknowledgementRepository,
            $this->monitoringService
        );

        $this->repository
            ->expects($this->once())
            ->method('findHostById')
            ->willReturn(null);

        $findHostPresenter = new FindHostPresenter($this->hypermediaService, $this->presenterFormatter);

        $findHost(1, $findHostPresenter);

        $this->assertEquals($findHostPresenter->getResponseStatus(), new HostNotFoundResponse());
    }

    public function testNotFoundAsNonAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(2)
            ->setName('user')
            ->setAdmin(false);

        $findHost = new FindHost(
            $this->repository,
            $this->hostgroupRepository,
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

        $this->repository
            ->expects($this->once())
            ->method('findHostByIdAndAccessGroupIds')
            ->willReturn(null);

        $findHostPresenter = new FindHostPresenter($this->hypermediaService, $this->presenterFormatter);

        $findHost(1, $findHostPresenter);

        $this->assertEquals($findHostPresenter->getResponseStatus(), new HostNotFoundResponse());
    }

    public function testFindHostAsAdmin(): void
    {
        /**
         * @var ContactInterface
         */
        $contact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $findHost = new FindHost(
            $this->repository,
            $this->hostgroupRepository,
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

        $this->repository
            ->expects($this->once())
            ->method('findHostById')
            ->willReturn($host);

        $downtimes[] = (new Downtime(1, 1, 10))
            ->setCancelled(false);

        $acknowledgement = new Acknowledgement(1, 1, 10, new \DateTime('yesterday'));

        $hostgroups[] = new Hostgroup(10, 'ALL');

        /**
         * Ajouter les downtimes + ack
         */
        $this->downtimeRepository
            ->expects($this->once())
            ->method('findOnGoingDowntimesByHostId')
            ->willReturn($downtimes);

        $this->acknowledgementRepository
            ->expects($this->once())
            ->method('findOnGoingAcknowledgementByHostId')
            ->willReturn($acknowledgement);

        $this->hostgroupRepository
            ->expects($this->once())
            ->method('findAllByHostId')
            ->willReturn($hostgroups);

        $findHostPresenter = new FindHostPresenterFake($this->hypermediaService, $this->presenterFormatter);

        $findHost(1, $findHostPresenter);

        $this->assertEquals($findHostPresenter->response->name, $host->getName());
        $this->assertEquals($findHostPresenter->response->id, $host->getId());
        $this->assertEquals($findHostPresenter->response->address, $host->getAddress());
        $this->assertEquals($findHostPresenter->response->monitoringServerName, $host->getMonitoringServerName());
        $this->assertEquals($findHostPresenter->response->timezone, $host->getTimezone());
        $this->assertEquals($findHostPresenter->response->alias, $host->getAlias());
        $this->assertEquals($findHostPresenter->response->isFlapping, $host->isFlapping());
        $this->assertEquals($findHostPresenter->response->isAcknowledged, $host->isAcknowledged());
        $this->assertEquals($findHostPresenter->response->isInDowntime, $host->isInDowntime());
        $this->assertEquals($findHostPresenter->response->output, $host->getOutput());
        $this->assertEquals($findHostPresenter->response->commandLine, $host->getCommandLine());
        $this->assertEquals($findHostPresenter->response->performanceData, $host->getPerformanceData());
        $this->assertEquals($findHostPresenter->response->notificationNumber, $host->getNotificationNumber());
        $this->assertEquals($findHostPresenter->response->latency, $host->getLatency());
        $this->assertEquals($findHostPresenter->response->executionTime, $host->getExecutionTime());
        $this->assertEquals($findHostPresenter->response->statusChangePercentage, $host->getStatusChangePercentage());
        $this->assertEquals($findHostPresenter->response->hasActiveChecks, $host->hasActiveChecks());
        $this->assertEquals($findHostPresenter->response->hasPassiveChecks, $host->hasPassiveChecks());
        $this->assertEquals($findHostPresenter->response->severityLevel, $host->getSeverityLevel());
        $this->assertEquals($findHostPresenter->response->checkAttempts, $host->getCheckAttempts());
        $this->assertEquals($findHostPresenter->response->maxCheckAttempts, $host->getMaxCheckAttempts());
        $this->assertEquals($findHostPresenter->response->lastTimeUp, $host->getLastTimeUp());
        $this->assertEquals($findHostPresenter->response->lastCheck, $host->getLastCheck());
        $this->assertEquals($findHostPresenter->response->nextCheck, $host->getNextCheck());
        $this->assertEquals($findHostPresenter->response->lastNotification, $host->getLastNotification());
        $this->assertEquals($findHostPresenter->response->lastStatusChange, $host->getLastStatusChange());
        $this->assertEquals($findHostPresenter->response->status['code'], $host->getStatus()->getCode());
        $this->assertEquals($findHostPresenter->response->status['name'], $host->getStatus()->getName());
        $this->assertEquals($findHostPresenter->response->status['type'], $host->getStatus()->getType());
        $this->assertEquals($findHostPresenter->response->status['severity_code'], $host->getStatus()->getOrder());
        $this->assertEquals($findHostPresenter->response->hostgroups[0]['id'], $host->getHostgroups()[0]->getId());
        $this->assertEquals($findHostPresenter->response->hostgroups[0]['name'], $host->getHostgroups()[0]->getName());
        $this->assertEquals($findHostPresenter->response->icon['name'], $host->getIcon()->getName());
        $this->assertEquals($findHostPresenter->response->icon['url'], $host->getIcon()->getUrl());
        $this->assertEquals($findHostPresenter->response->downtimes[0]['id'], $downtimes[0]->getId());
        $this->assertEquals($findHostPresenter->response->downtimes[0]['service_id'], $downtimes[0]->getServiceId());
        $this->assertEquals($findHostPresenter->response->downtimes[0]['host_id'], $downtimes[0]->getHostId());
        $this->assertEquals($findHostPresenter->response->acknowledgement['id'], $acknowledgement->getId());
        $this->assertEquals($findHostPresenter->response->acknowledgement['service_id'], $acknowledgement->getServiceId());
        $this->assertEquals($findHostPresenter->response->acknowledgement['host_id'], $acknowledgement->getHostId());
    }
}

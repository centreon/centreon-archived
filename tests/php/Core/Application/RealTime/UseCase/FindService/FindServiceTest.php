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

namespace Tests\Core\Application\RealTime\UseCase\FindService;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\RealTime\UseCase\FindService\FindService;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaService;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Core\Application\RealTime\Repository\ReadHostRepositoryInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Infrastructure\RealTime\Api\FindService\FindServicePresenter;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServiceRepositoryInterface;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;
use Core\Application\RealTime\Repository\ReadServicegroupRepositoryInterface;
use Core\Application\RealTime\Repository\ReadAcknowledgementRepositoryInterface;
use Core\Application\RealTime\UseCase\FindHost\HostNotFoundResponse;
use Core\Application\RealTime\UseCase\FindService\ServiceNotFoundResponse;
use Service;
use Tests\Core\Domain\RealTime\Model\HostTest;

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
        $this->hypermediaService = $this->createMock(HypermediaService::class);
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
            $this->servicegroupRepository,
            $this->hostRepository,
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

        $findServicePresenter = new FindServicePresenter($this->hypermediaService, $this->presenterFormatter);

        $findService(1, 20, $findServicePresenter);

        $this->assertEquals($findServicePresenter->getResponseStatus(), new HostNotFoundResponse());
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
            $this->servicegroupRepository,
            $this->hostRepository,
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

        $findServicePresenter = new FindServicePresenter($this->hypermediaService, $this->presenterFormatter);

        $findService(1, 20, $findServicePresenter);

        $this->assertEquals($findServicePresenter->getResponseStatus(), new HostNotFoundResponse());
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
            $this->servicegroupRepository,
            $this->hostRepository,
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

        $findServicePresenter = new FindServicePresenter($this->hypermediaService, $this->presenterFormatter);

        $findService(1, 20, $findServicePresenter);

        $this->assertEquals($findServicePresenter->getResponseStatus(), new ServiceNotFoundResponse());
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
            $this->servicegroupRepository,
            $this->hostRepository,
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

        $findServicePresenter = new FindServicePresenter($this->hypermediaService, $this->presenterFormatter);

        $findService(1, 20, $findServicePresenter);

        $this->assertEquals($findServicePresenter->getResponseStatus(), new ServiceNotFoundResponse());
    }
}

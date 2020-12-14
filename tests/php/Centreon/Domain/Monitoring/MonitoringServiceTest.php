<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Tests\Centreon\Domain\Monitoring;

use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationServiceInterface;
use Centreon\Domain\Monitoring\Entity\CommentEventObject;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\TimelineRepositoryInterface;
use Centreon\Domain\Monitoring\MonitoringService;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\ServiceGroup;
use Centreon\Domain\Monitoring\TimelineEvent;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationServiceInterface;
use PHPUnit\Framework\TestCase;

class MonitoringServiceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testFindServices()
    {
        $service = (new Service())
            ->setId(1)
            ->setDisplayName('test');

        $repository = $this->createMock(MonitoringRepositoryInterface::class);
        $repository->expects(self::any())
            ->method('findServices')
            ->willReturn([$service]); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );

        $servicesFound = $monitoringService->findServices();
        $this->assertCount(
            1,
            $servicesFound,
            "Error, this method must relay the 'findServices' method of the monitoring repository"
        );
    }

    /**
     * @throws \Exception
     */
    public function testFindServicesByHost()
    {
        $service = (new Service())
            ->setId(1)
            ->setDisplayName('test');
        $hostId = 1;

        $repository = $this->createMock(MonitoringRepositoryInterface::class);
        $repository->expects(self::any())
            ->method('findServicesByHostWithRequestParameters')
            ->with($hostId)
            ->willReturn([$service]); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );

        $servicesFound = $monitoringService->findServicesByHost($hostId);
        $this->assertCount(
            1,
            $servicesFound,
            "Error, this method must relay the 'findServicesByHost' method of the monitoring repository"
        );
    }

    /**
     * @throws \Exception
     */
    public function testFindHosts()
    {
        $service = (new Service())
            ->setId(1)
            ->setDisplayName('test');

        $host = (new Host())
            ->setId(1)
            ->setDisplayName('test');

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('findHosts')
            ->willReturn([$host]); // values returned for the all next tests

        $repository->expects(self::any())
            ->method('findServicesByHosts')
            ->with([$host->getId()])
            ->willReturn([$host->getId() => [$service]]); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);

        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );

        /**
         * @var Host[] $hostsFound
         */
        $hostsFound = $monitoringService->findHosts(true);
        $this->assertCount(
            1,
            $hostsFound,
            "Error, the number of hosts is not equal to the number given by the "
            . "'findHosts' method of the monitoring repository"
        );
        $this->assertEquals($hostsFound[0]->getId(), $host->getId());

        $this->assertCount(
            1,
            $hostsFound[0]->getServices(),
            "Error, the service of the first host does not match the one given by the "
            . "'findServicesOnMultipleHosts' method of the monitoring repository"
        );
        $this->assertEquals($hostsFound[0]->getServices()[0]->getId(), $service->getId());
    }

    /**
     * @throws \Exception
     */
    public function testFindServiceGroups()
    {
        $service = (new Service())
            ->setId(1)
            ->setDisplayName('test');

        $host = (new Host())
            ->setId(2)
            ->setDisplayName('test');
        $host->addService($service);

        $serviceGroup = (new ServiceGroup())
            ->setId(3)
            ->setHosts([$host]);

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('findServiceGroups')
            ->willReturn([$serviceGroup]); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);

        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );
        /**
         * @var ServiceGroup[] $servicesGroupsFound
         */
        $servicesGroupsFound = $monitoringService->findServiceGroups();
        $this->assertCount(
            1,
            $servicesGroupsFound,
            "Error, this method must relay the 'findServiceGroups' method of the monitoring repository"
        );
        $this->assertEquals($serviceGroup->getId(), $servicesGroupsFound[0]->getId());
        $this->assertEquals($host->getId(), $serviceGroup->getHosts()[0]->getId());
        $this->assertEquals($service->getId(), $serviceGroup->getHosts()[0]->getServices()[0]->getId());
    }

    /**
     * @throws \Exception
     */
    public function testFindOneService()
    {
        $service = (new Service())
            ->setId(1)
            ->setDisplayName('test');

        $host = (new Host())
            ->setId(1)
            ->setDisplayName('test');

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('findOneService')
            ->with($host->getId(), $service->getId())
            ->willReturn($service); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);

        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );

        $oneService = $monitoringService->findOneService($host->getId(), $service->getId());
        $this->assertNotNull($oneService);
        $this->assertEquals(
            $oneService->getId(),
            $service->getId(),
            "Error, this method must relay the 'findOneService' method of the monitoring repository"
        );
    }

    /**
     * @throws \Exception
     */
    public function testFindOneHost()
    {
        $service = (new Service())
            ->setId(1)
            ->setDisplayName('test');

        $host = (new Host())
            ->setId(1)
            ->setDisplayName('test');
        $host->addService($service);

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('findOneHost')
            ->with($host->getId())
            ->willReturn($host, null);

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );

        $hostFound = $monitoringService->findOneHost($host->getId());
        $this->assertNotNull($hostFound);
        $this->assertEquals($host->getId(), $hostFound->getId());
        $this->assertEquals($host->getServices()[0]->getId(), $service->getId());

        $hostFound = $monitoringService->findOneHost($host->getId());
        $this->assertNull($hostFound);
    }

    /**
     * @throws \Exception
     */
    public function testFindHostGroups()
    {
        $service = (new Service())
            ->setId(3)
            ->setDisplayName('test');

        $host = (new Host())
            ->setId(2)
            ->setDisplayName('test');
        $host->addService($service);

        $hostGroup = (new HostGroup())
            ->setId(1)
            ->addHost($host);

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('findHostGroups')
            ->willReturn([$hostGroup]);

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );

        /**
         * @var HostGroup[] $hostsGroupsFound
         */
        $hostsGroupsFound = $monitoringService->findHostGroups();
        $this->assertCount(
            1,
            $hostsGroupsFound,
            "Error, this method must relay the 'findHostGroups' method of the monitoring repository"
        );
        $this->assertEquals($hostsGroupsFound[0]->getId(), $hostGroup->getId());
        $this->assertEquals($hostsGroupsFound[0]->getHosts()[0]->getId(), $host->getId());
        $this->assertEquals($hostsGroupsFound[0]->getHosts()[0]->getServices()[0]->getId(), $service->getId());
    }

    /**
     * @throws \Exception
     */
    public function testIsHostExist()
    {
        $host = (new Host())
            ->setId(1)
            ->setDisplayName('test');

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('findOneHost')
            ->with($host->getId())
            ->willReturn($host, null);

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );

        // First test when the 'findOneHost' returns one host
        $isHostExist = $monitoringService->isHostExists($host->getId());
        $this->assertTrue($isHostExist);

        // Second test when the 'findOneHost' returns null
        $isHostExist = $monitoringService->isHostExists($host->getId());
        $this->assertfalse($isHostExist);
    }

    /**
     * @throws \Exception
     */
    public function testIsServiceExist()
    {
        $host = (new Host())
            ->setId(1)
            ->setDisplayName('test');

        $service = (new Service())
            ->setId(1)
            ->setHost($host);

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('findOneService')
            ->with($host->getId(), $service->getId())
            ->willReturn($service, null);

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );

        $exists = $monitoringService->isServiceExists($host->getId(), $service->getId());
        $this->assertTrue($exists);
    }

    /**
     * @throws \Exception
     */
    public function findServiceGroupsByHostAndService()
    {
        $service = (new Service())
            ->setId(1)
            ->setDisplayName('test');

        $host = (new Host())
            ->setId(2)
            ->setDisplayName('test');
        $host->addService($service);

        $serviceGroup = (new ServiceGroup())
            ->setId(3)
            ->setHosts([$host]);

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('findServiceGroupsByHostAndService')
            ->with($host->getId(), $service->getId())
            ->willReturn([$serviceGroup]); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );
        /**
         * @var ServiceGroup[] $servicesGroupsFound
         */
        $servicesGroupsFound = $monitoringService->findServiceGroupsByHostAndService($host->getId(), $service->getId());
        $this->assertCount(
            1,
            $servicesGroupsFound,
            "Error, this method must relay the 'findServiceGroupsByHostAndService' method of the monitoring repository"
        );
        $this->assertEquals($serviceGroup->getId(), $servicesGroupsFound[0]->getId());
        $this->assertEquals($host->getId(), $serviceGroup->getHosts()[0]->getId());
        $this->assertEquals($service->getId(), $serviceGroup->getHosts()[0]->getServices()[0]->getId());
    }

    /**
     * @throws \Exception
     */
    public function findTimelineEvents()
    {
        $commentObject = new CommentEventObject();
        $timelineEvent = new TimelineEvent($commentObject);
        $repository = $this->createMock(TimelineRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('findTimelineEvents')
            ->with(2, 2)
            ->willReturn([$timelineEvent]); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $serviceConfiguration = $this->createMock(ServiceConfigurationServiceInterface::class);
        $hostConfiguration = $this->createMock(HostConfigurationServiceInterface::class);
        $monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);

        $monitoringService = new MonitoringService(
            $repository,
            $accessGroup,
            $serviceConfiguration,
            $hostConfiguration,
            $monitoringServerService
        );
        /**
         * @var TimelineEvent[] $timelineEventsFound
         */
        $timelineEventsFound = $monitoringService->findTimelineEvents(2, 2);
        $this->assertCount(
            1,
            $timelineEventsFound,
            "Error, this method must relay the 'findTimelineEvents' method of the timeline repository"
        );
    }
}

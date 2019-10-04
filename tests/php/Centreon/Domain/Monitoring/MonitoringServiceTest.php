<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\MonitoringService;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\ServiceGroup;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
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
            ->method('filterByAccessGroups')
            ->will(self::returnSelf());

        $repository->expects(self::any())
            ->method('findServices')
            ->willReturn([$service]); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $accessGroup->expects(self::any())
            ->method('findByContact')
            ->willReturn([1], []); // values returned for the next 2 tests

        $monitoringService = new MonitoringService($repository, $accessGroup);

        $contact = $this->createMock(ContactInterface::class);
        $contact->expects(self::any())
            ->method('isAdmin')
            ->willReturn(true, false, false); // values returned for the next 3 tests

        $monitoringService->filterByContact($contact);

        // First test when the contact is an admin (no need to filter by access group ids)
        $servicesFound = $monitoringService->findServices();
        $this->assertCount(
            1,
            $servicesFound,
            "Error when the contact is an admin (no need to filter by access group ids)"
        );
        $this->assertEquals($servicesFound[0]->getId(), $service->getId());

        // Second test when the contact is not an admin and the access group filter is not empty
        $servicesFound = $monitoringService->findServices();
        $this->assertCount(
            1,
            $servicesFound,
            "Error when the contact is not an admin and the access group filter is not empty"
        );
        $this->assertEquals($servicesFound[0]->getId(), $service->getId());

        // Third test when the contact is not an administrator and the access group filter is empty
        $servicesFound = $monitoringService->findServices();
        $this->assertCount(
            0,
            $servicesFound,
            "Error when the contact is not an administrator and the access group filter is empty"
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

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('filterByAccessGroups')
            ->will(self::returnSelf());

        $repository->expects(self::any())
            ->method('findServicesByHost')
            ->willReturn([$service])
            ->with($service->getId());

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $accessGroup->expects(self::any())
            ->method('findByContact')
            ->willReturn([1], []); // values returned for the next 2 tests

        $monitoringService = new MonitoringService($repository, $accessGroup);

        $contact = $this->createMock(ContactInterface::class);
        $contact->expects(self::any())
            ->method('isAdmin')
            ->willReturn(true, false, false); // values returned for the next 3 tests

        $monitoringService->filterByContact($contact);

        // First test when the contact is an admin (no need to filter by access group ids)
        $servicesByHostFound = $monitoringService->findServicesByHost($service->getId());
        $this->assertCount(
            1,
            $servicesByHostFound,
            "Error when the contact is an admin (no need to filter by access group ids)"
        );
        $this->assertEquals($servicesByHostFound[0]->getId(), $service->getId());

        // Second test when the contact is not an admin and the access group filter is not empty
        $servicesByHostFound = $monitoringService->findServicesByHost($service->getId());
        $this->assertCount(
            1,
            $servicesByHostFound,
            "Error when the contact is not an admin and the access group filter is not empty"
        );
        $this->assertEquals($servicesByHostFound[0]->getId(), $service->getId());

        // Third test when the contact is not an administrator and the access group filter is empty
        $servicesByHostFound = $monitoringService->findServicesByHost($service->getId());
        $this->assertCount(
            0,
            $servicesByHostFound,
            "Error when the contact is not an administrator and the access group filter is empty"
        );
    }

    /**
     * @throws \Exception
     */
    public function testFindHosts()
    {
        $host = (new Host())
            ->setId(1)
            ->setDisplayName('test');

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('filterByAccessGroups')
            ->will(self::returnSelf());

        $repository->expects(self::any())
            ->method('findHosts')
            ->willReturn([$host]); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $accessGroup->expects(self::any())
            ->method('findByContact')
            ->willReturn([1], []); // values returned for the next 2 tests

        $monitoringService = new MonitoringService($repository, $accessGroup);

        $contact = $this->createMock(ContactInterface::class);
        $contact->expects(self::any())
            ->method('isAdmin')
            ->willReturn(true, false, false); // values returned for the next 3 tests

        $monitoringService->filterByContact($contact);

        // First test when the contact is an admin (no need to filter by access group ids)
        $hostsFound = $monitoringService->findHosts();
        $this->assertCount(
            1,
            $hostsFound,
            "Error when the contact is an admin (no need to filter by access group ids)"
        );
        $this->assertEquals($hostsFound[0]->getId(), $host->getId());

        // Second test when the contact is not an admin and the access group filter is not empty
        $hostsFound = $monitoringService->findHosts();
        $this->assertCount(
            1,
            $hostsFound,
            "Error when the contact is not an admin and the access group filter is not empty"
        );
        $this->assertEquals($hostsFound[0]->getId(), $host->getId());

        // Third test when the contact is not an administrator and the access group filter is empty
        $hostsFound = $monitoringService->findHosts();
        $this->assertCount(
            0,
            $hostsFound,
            "Error when the contact is not an administrator and the access group filter is empty"
        );
    }

    /**
     * @throws \Exception
     */
    public function testFindServiceGroups()
    {
        $serviceGroup = (new ServiceGroup())
            ->setId(1)
            ->setName('test');

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('filterByAccessGroups')
            ->will(self::returnSelf());

        $repository->expects(self::any())
            ->method('findServiceGroups')
            ->willReturn([$serviceGroup]);

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $accessGroup->expects(self::any())
            ->method('findByContact')
            ->willReturn([1], []); // values returned for the next 2 tests

        $monitoringService = new MonitoringService($repository, $accessGroup);

        $contact = $this->createMock(ContactInterface::class);
        $contact->expects(self::any())
            ->method('isAdmin')
            ->willReturn(true, false, false); // values returned for the next 3 tests

        $monitoringService->filterByContact($contact);


        // First test when the contact is an admin (no need to filter by access group ids)
        $serviceGroupsFound = $monitoringService->findServiceGroups();
        $this->assertCount(
            1,
            $serviceGroupsFound,
            "Error when the contact is an admin (no need to filter by access group ids)"
        );
        $this->assertEquals($serviceGroupsFound[0]->getId(), $serviceGroup->getId());

        // Second test when the contact is not an admin and the access group filter is not empty
        $serviceGroupsFound = $monitoringService->findServiceGroups();
        $this->assertCount(
            1,
            $serviceGroupsFound,
            "Error when the contact is not an admin and the access group filter is not empty"
        );
        $this->assertEquals($serviceGroupsFound[0]->getId(), $serviceGroup->getId());

        // Third test when the contact is not an administrator and the access group filter is empty
        $serviceGroupsFound = $monitoringService->findServiceGroups();
        $this->assertCount(
            0,
            $serviceGroupsFound,
            "Error when the contact is not an administrator and the access group filter is empty"
        );
    }

    /**
     * @throws \Exception
     */
    public function testFindOneService()
    {
        $service = (new Service())
            ->setId(1)
            ->setDisplayName('test');

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('filterByAccessGroups')
            ->will(self::returnSelf());

        $repository->expects(self::any())
            ->method('findOneService')
            ->willReturn($service)
            ->with(0, $service->getId());

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $accessGroup->expects(self::any())
            ->method('findByContact')
            ->willReturn([1], []); // values returned for the next 2 tests

        $monitoringService = new MonitoringService($repository, $accessGroup);

        $contact = $this->createMock(ContactInterface::class);
        $contact->expects(self::any())
            ->method('isAdmin')
            ->willReturn(true, false, false); // values returned for the next 3 tests

        $monitoringService->filterByContact($contact);

        // First test when the contact is an admin (no need to filter by access group ids)
        $serviceFound = $monitoringService->findOneService(0, $service->getId());
        $this->assertNotNull(
            $serviceFound,
            "Error when the contact is an admin (no need to filter by access group ids)"
        );
        $this->assertEquals($serviceFound->getId(), $service->getId());

        // Second test when the contact is not an admin and the access group filter is not empty
        $serviceFound = $monitoringService->findOneService(0, $service->getId());
        $this->assertNotNull(
            $serviceFound,
            "Error when the contact is not an admin and the access group filter is not empty"
        );
        $this->assertEquals($serviceFound->getId(), $service->getId());

        // Third test when the contact is not an administrator and the access group filter is empty
        $serviceFound = $monitoringService->findOneService(0, $service->getId());
        $this->assertNull(
            $serviceFound,
            "Error when the contact is not an administrator and the access group filter is empty"
        );
    }

    /**
     * @throws \Exception
     */
    public function testFindOneHost()
    {
        $host = (new Host())
            ->setId(1)
            ->setDisplayName('test');

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('filterByAccessGroups')
            ->will(self::returnSelf());

        $repository->expects(self::any())
            ->method('findOneHost')
            ->willReturn($host)
            ->with($host->getId());

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $accessGroup->expects(self::any())
            ->method('findByContact')
            ->willReturn([1], []); // values returned for the next 2 tests

        $monitoringService = new MonitoringService($repository, $accessGroup);

        $contact = $this->createMock(ContactInterface::class);
        $contact->expects(self::any())
            ->method('isAdmin')
            ->willReturn(true, false, false); // values returned for the next 3 tests

        $monitoringService->filterByContact($contact);

        // First test when the contact is an admin (no need to filter by access group ids)
        $hostFound = $monitoringService->findOneHost($host->getId());
        $this->assertNotNull(
            $hostFound,
            "Error when the contact is an admin (no need to filter by access group ids)"
        );
        $this->assertEquals($hostFound->getId(), $host->getId());

        // Second test when the contact is not an admin and the access group filter is not empty
        $hostFound = $monitoringService->findOneHost($host->getId());
        $this->assertNotNull(
            $hostFound,
            "Error when the contact is not an admin and the access group filter is not empty"
        );
        $this->assertEquals($hostFound->getId(), $host->getId());

        // Third test when the contact is not an administrator and the access group filter is empty
        $hostFound = $monitoringService->findOneHost($host->getId());
        $this->assertNull(
            $hostFound,
            "Error when the contact is not an administrator and the access group filter is empty"
        );
    }

    /**
     * @throws \Exception
     */
    public function testFindHostGroups()
    {
        $hostGroup = (new HostGroup())
            ->setId(1)
            ->setName('test');

        $repository = $this->createMock(MonitoringRepositoryInterface::class);

        $repository->expects(self::any())
            ->method('filterByAccessGroups')
            ->will(self::returnSelf());

        $repository->expects(self::any())
            ->method('findHostGroups')
            ->willReturn([$hostGroup]);

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $accessGroup->expects(self::any())
            ->method('findByContact')
            ->willReturn([1], []); // values returned for the next 2 tests

        $monitoringService = new MonitoringService($repository, $accessGroup);

        $contact = $this->createMock(ContactInterface::class);
        $contact->expects(self::any())
            ->method('isAdmin')
            ->willReturn(true, false, false); // values returned for the next 3 tests

        $monitoringService->filterByContact($contact);


        // First test when the contact is an admin (no need to filter by access group ids)
        $hostGroupsFound = $monitoringService->findHostGroups();
        $this->assertCount(
            1,
            $hostGroupsFound,
            "Error when the contact is an admin (no need to filter by access group ids)"
        );
        $this->assertEquals($hostGroupsFound[0]->getId(), $hostGroup->getId());

        // Second test when the contact is not an admin and the access group filter is not empty
        $hostGroupsFound = $monitoringService->findHostGroups();
        $this->assertCount(
            1,
            $hostGroupsFound,
            "Error when the contact is not an admin and the access group filter is not empty"
        );
        $this->assertEquals($hostGroupsFound[0]->getId(), $hostGroup->getId());

        // Third test when the contact is not an administrator and the access group filter is empty
        $hostGroupsFound = $monitoringService->findHostGroups();
        $this->assertCount(
            0,
            $hostGroupsFound,
            "Error when the contact is not an administrator and the access group filter is empty"
        );
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
            ->method('filterByAccessGroups')
            ->will(self::returnSelf());

        $repository->expects(self::any())
            ->method('findOneHost')
            ->willReturn($host)
            ->with($host->getId());

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $accessGroup->expects(self::any())
            ->method('findByContact')
            ->willReturn([1], []); // values returned for the next 2 tests

        $monitoringService = new MonitoringService($repository, $accessGroup);

        $contact = $this->createMock(ContactInterface::class);
        $contact->expects(self::any())
            ->method('isAdmin')
            ->willReturn(true, false, false); // values returned for the next 3 tests

        $monitoringService->filterByContact($contact);

        // First test when the contact is an admin (no need to filter by access group ids)
        $isHostExist = $monitoringService->isHostExists($host->getId());
        $this->assertTrue($isHostExist);

        // Second test when the contact is not an admin and the access group filter is not empty
        $isHostExist = $monitoringService->isHostExists($host->getId());
        $this->assertTrue($isHostExist);

        // Third test when the contact is not an administrator and the access group filter is empty
        $isHostExist = $monitoringService->isHostExists($host->getId());
        $this->assertFalse($isHostExist);
    }
}

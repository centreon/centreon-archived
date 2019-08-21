<?php

namespace Centreon\Tests\Domain\Service\Test;


use Centreon\Domain\Entity\Interfaces\ContactInterface;
use Centreon\Domain\Entity\Service;
use Centreon\Domain\Pagination;
use Centreon\Domain\Repository\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Repository\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Service\MonitoringService;
use Centreon\Infrastructure\Repository\MonitoringRepositoryRDB;
use PHPUnit\Framework\TestCase;

class MonitoringServiceTest extends TestCase
{


    public function testFindOneHost()
    {

    }

    public function testFilterByContact()
    {
    }

    /**
     * @throws \Exception
     */
    public function testFindServices()
    {
        $contact = $this->createMock(ContactInterface::class);
        $contact->expects(self::any())
            ->method('isAdmin')
            ->willReturn(true, false, false); // values returned for the next 3 tests

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

        $mon = new MonitoringService($repository, $accessGroup);
        $mon->filterByContact($contact);

        // First test when the contact is an admin (no need to filter by access group ids)
        $servicesFound = $mon->findServices(new Pagination());
        $this->assertCount(
            1,
            $servicesFound,
            "Error when the contact is an admin (no need to filter by access group ids)"
        );
        $this->assertEquals($servicesFound[0]->getId(), $service->getId());

        // Second test when the contact is not an admin and the access group filter is not empty
        $servicesFound = $mon->findServices(new Pagination());
        $this->assertCount(
            1,
            $servicesFound,
            "Error when the contact is not an admin and the access group filter is not empty"
        );
        $this->assertEquals($servicesFound[0]->getId(), $service->getId());

        // Third test when the contact is not an administrator and the access group filter is empty
        $servicesFound = $mon->findServices(new Pagination());
        $this->assertCount(
            0,
            $servicesFound,
            "Error when the contact is not an administrator and the access group filter is empty"
        );
    }

    public function testFindHosts()
    {

    }

    public function testFindOneService()
    {

    }
}

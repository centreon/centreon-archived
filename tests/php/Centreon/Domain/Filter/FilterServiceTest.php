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

use Centreon\Domain\Filter\FilterService;
use Centreon\Domain\Filter\Interfaces\FilterRepositoryInterface;
use Centreon\Domain\Filter\Filter;
use Centreon\Domain\Filter\FilterCriteria;
use Centreon\Domain\Monitoring\HostGroup\Interfaces\HostGroupServiceInterface;
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Monitoring\ServiceGroup\Interfaces\ServiceGroupServiceInterface;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Filter\FilterException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class FilterServiceTest extends TestCase
{
    /**
     * @var Contact|null $adminContact
     */
    protected $adminContact;

    /**
     * @var Filter|null $filter
     */
    protected $filter;

    /**
     * @var FilterRepositoryInterface&MockObject $filterRepository
     */
    protected $filterRepository;

    /**
     * @var HostGroupServiceInterface&MockObject $hostGroupService
     */
    protected $hostGroupService;

    /**
     * @var ServiceGroupServiceInterface&MockObject $serviceGroupService
     */
    protected $serviceGroupService;

    protected function setUp(): void
    {
        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $this->filter = (new Filter())
        ->setId(1)
        ->setName('filter1')
        ->setUserId(1)
        ->setPageName('events-view')
        ->setCriterias([
            (new FilterCriteria())
                ->setName("host_groups")
                ->setType("multi_select")
                ->setValue([
                    [
                      "id" => 1,
                      "name" => "linux"
                    ]
                ])
                ->setObjectType("host_groups"),
            (new FilterCriteria())
                ->setName("service_groups")
                ->setType("multi_select")
                ->setValue([
                    [
                      "id" => 1,
                      "name" => "sg_ping"
                    ]
                ])
                ->setObjectType("service_groups"),
            (new FilterCriteria())
                ->setName("search")
                ->setType("text")
                ->setValue("my search"),
        ]);

        $this->filterRepository = $this->createMock(FilterRepositoryInterface::class);

        $this->hostGroupService = $this->createMock(HostGroupServiceInterface::class);

        $this->serviceGroupService = $this->createMock(ServiceGroupServiceInterface::class);
    }

    /**
     * test checkCriterias with renamed objects
     */
    public function testCheckCriteriasRenamedObjects()
    {
        $renamedHostGroup = (new HostGroup())
            ->setId(1)
            ->setName('renamed_linux');
        $this->hostGroupService->expects($this->once())
            ->method('filterByContact')
            ->willReturn($this->hostGroupService);
        $this->hostGroupService->expects($this->once())
            ->method('findHostGroupsByNames')
            ->willReturn([$renamedHostGroup]);

        $filterService = new FilterService(
            $this->hostGroupService,
            $this->serviceGroupService,
            $this->filterRepository
        );

        $filterService->checkCriterias($this->filter->getCriterias());

        $this->assertCount(
            1,
            $this->filter->getCriterias()[0]->getValue()
        );

        $this->assertEquals(
            [
                'id' => $renamedHostGroup->getId(),
                'name' => $renamedHostGroup->getName(),
            ],
            $this->filter->getCriterias()[0]->getValue()[0]
        );
    }

    /**
     * test checkCriterias with deleted objects
     */
    public function testCheckCriteriasDeletedObjects()
    {
        $this->serviceGroupService->expects($this->once())
            ->method('filterByContact')
            ->willReturn($this->serviceGroupService);
        $this->serviceGroupService->expects($this->once())
            ->method('findServiceGroupsByNames')
            ->willReturn([]);

        $filterService = new FilterService(
            $this->hostGroupService,
            $this->serviceGroupService,
            $this->filterRepository
        );

        $filterService->checkCriterias($this->filter->getCriterias());

        $this->assertCount(
            0,
            $this->filter->getCriterias()[1]->getValue()
        );
    }

    /**
     * test update filter with name already in use
     */
    public function testUpdateFilterNameExists(): void
    {
        $this->filterRepository->expects($this->once())
            ->method('findFilterByUserIdAndName')
            ->willReturn($this->filter);

        $filterUpdate = (new Filter())
            ->setId(2)
            ->setName($this->filter->getName())
            ->setUserId($this->filter->getUserId())
            ->setPageName($this->filter->getPageName());

        $filterService = new FilterService(
            $this->hostGroupService,
            $this->serviceGroupService,
            $this->filterRepository
        );

        $this->expectException(FilterException::class);
        $this->expectExceptionMessage('Filter name already used');

        $filterService->updateFilter($filterUpdate);
    }
}

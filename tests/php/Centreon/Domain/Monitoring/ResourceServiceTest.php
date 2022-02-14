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

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceService;
use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;

class ResourceServiceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testFindResources()
    {
        $hostResource = (new Resource())
            ->setType('host')
            ->setId(1)
            ->setName('host1');
        $serviceResource = (new Resource())
            ->setType('service')
            ->setId(1)
            ->setName('service1')
            ->setParent($hostResource);

        $resourceRepository = $this->createMock(ResourceRepositoryInterface::class);
        $resourceRepository->expects(self::any())
            ->method('findResources')
            ->willReturn([$hostResource, $serviceResource]); // values returned for the all next tests

        $monitoringRepository = $this->createMock(MonitoringRepositoryInterface::class);

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);

        $resourceService = new ResourceService(
            $resourceRepository,
            $monitoringRepository,
            $accessGroup
        );

        $resourcesFound = $resourceService->findResources(new ResourceFilter());

        $this->assertCount(2, $resourcesFound);
        $this->assertEquals('h1', $resourcesFound[0]->getUuid());
        $this->assertEquals('h1-s1', $resourcesFound[1]->getUuid());
    }
}

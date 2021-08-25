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
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\Monitoring\MonitoringResource\MonitoringResourceService;
use Centreon\Domain\Monitoring\MonitoringResource\Interfaces\MonitoringResourceRepositoryInterface;
use Centreon\Infrastructure\Monitoring\MonitoringResource\Repository\MonitoringResourceRepositoryRDB;

class ResourceServiceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testFindResources(): void
    {
        $contact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $hostResource = new MonitoringResource(1, 'host1', 'host');
        $serviceResource = (new MonitoringResource(1, 'service1', 'service'))
            ->setParent($hostResource);

        $monitoringResourceRepository = $this->createMock(MonitoringResourceRepositoryInterface::class);
        $monitoringResourceRepository->expects(self::any())
            ->method('findAll')
            ->willReturn([$hostResource, $serviceResource]); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);

        $monitoringRepository = $this->createMock(MonitoringRepositoryInterface::class);

        $resourceService = new MonitoringResourceService(
            $monitoringResourceRepository,
            $contact,
            $accessGroup,
            $monitoringRepository
        );

        $resourcesFound = $resourceService->findAllWithoutAcl(new ResourceFilter());

        $this->assertCount(2, $resourcesFound);
        $this->assertEquals('h1', $resourcesFound[0]->getUuid());
        $this->assertEquals('h1-s1', $resourcesFound[1]->getUuid());
    }
}

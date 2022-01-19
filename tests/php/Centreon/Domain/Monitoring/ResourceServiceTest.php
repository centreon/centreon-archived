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
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceService;
use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroReadRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Centreon\Infrastructure\MetaServiceConfiguration\Repository\MetaServiceConfigurationRepositoryRDB;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationReadRepositoryInterface;

class ResourceServiceTest extends TestCase
{
    /**
     * @var MonitoringRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $monitoringRepository;

    /**
     * @var ResourceRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceRepository;

    /**
     * @var AccessGroupRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $accessGroupRepository;

    /**
     * @var MetaServiceConfigurationReadRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $metaServiceConfigurationRepository;

    /**
     * @var HostMacroReadRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostMacroConfigurationRepository;

    /**
     * @var ServiceConfigurationRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $serviceConfigurationRepository;

    protected function setUp(): void
    {
        $this->resourceRepository = $this->createMock(ResourceRepositoryInterface::class);
        $this->monitoringRepository = $this->createMock(MonitoringRepositoryInterface::class);
        $this->accessGroupRepository = $this->createMock(AccessGroupRepositoryInterface::class);
        $this->metaServiceConfigurationRepository = $this->createMock(MetaServiceConfigurationReadRepositoryInterface::class);
        $this->hostMacroConfigurationRepository = $this->createMock(HostMacroReadRepositoryInterface::class);
        $this->serviceConfigurationRepository = $this->createMock(ServiceConfigurationRepositoryInterface::class);
    }
    /**
     * @throws \Exception
     */
    public function testFindResources(): void
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

        $this->resourceRepository->expects(self::any())
            ->method('findResources')
            ->willReturn([$hostResource, $serviceResource]);

        $resourceService = new ResourceService(
            $this->resourceRepository,
            $this->monitoringRepository,
            $this->accessGroupRepository,
            $this->metaServiceConfigurationRepository,
            $this->hostMacroConfigurationRepository,
            $this->serviceConfigurationRepository
        );

        $resourcesFound = $resourceService->findResources(new ResourceFilter());

        $this->assertCount(2, $resourcesFound);
        $this->assertEquals('h1', $resourcesFound[0]->getUuid());
        $this->assertEquals('h1-s1', $resourcesFound[1]->getUuid());
    }

    public function testReplaceMacrosInHost(): void
    {
        $host = (new Host())
            ->setId(10)
            ->setName('Centreon-Central')
            ->setPollerName('central')
            ->setActionUrl('http://$HOSTADDRESS$/$HOSTNAME$/$_HOSTCUSTOMVAL$');
        $resourceService = new ResourceService(
            $this->resourceRepository,
            $this->monitoringRepository,
            $this->accessGroupRepository,
            $this->metaServiceConfigurationRepository,
            $this->hostMacroConfigurationRepository,
            $this->serviceConfigurationRepository
        );
    }
}

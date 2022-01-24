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
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\ResourceService;
use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroReadRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Centreon\Infrastructure\MetaServiceConfiguration\Repository\MetaServiceConfigurationRepositoryRDB;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationReadRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;

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
        $this->metaServiceConfigurationRepository = $this->createMock(
            MetaServiceConfigurationReadRepositoryInterface::class
        );
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

    /**
     * test host macros replacement
     */
    public function testReplaceMacrosInHost(): void
    {
        $host = (new Host())
            ->setId(10)
            ->setName('Centreon-Central')
            ->setPollerName('central')
            ->setAddressIp('127.0.0.1')
            ->setActionUrl('http://$INSTANCENAME$/$HOSTADDRESS$/$HOSTNAME$/$_HOSTCUSTOMVAL$/$HOSTSTATE$')
            ->setStatus((new ResourceStatus())->setName('UP')->setCode(0));

        $customMacrosValues = [
            '$_HOSTCUSTOMVAL$' => 'helloworld'
        ];

        $hostMacro = (new HostMacro())
            ->setName('$_HOSTCUSTOMVAL$')
            ->setValue('helloworld')
            ->setPassword(false);

        $resourceService = new ResourceService(
            $this->resourceRepository,
            $this->monitoringRepository,
            $this->accessGroupRepository,
            $this->metaServiceConfigurationRepository,
            $this->hostMacroConfigurationRepository,
            $this->serviceConfigurationRepository
        );

        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($host);

        $this->monitoringRepository->expects($this->once())
            ->method('findCustomMacrosValues')
            ->willReturn(['CUSTOMVAL' => 'helloworld']);

        $this->hostMacroConfigurationRepository->expects($this->once())
            ->method('findOnDemandHostMacros')
            ->willReturn([$hostMacro]);

        $actionUrl = $host->getActionUrl() !== null ? $host->getActionUrl() : '';
        $expected = str_replace('$HOSTADDRESS$', (string) $host->getAddressIp(), $actionUrl);
        $expected = str_replace('$HOSTNAME$', (string) $host->getName(), $expected);
        $expected = str_replace('$INSTANCENAME$', (string) $host->getPollerName(), $expected);
        $expected = str_replace('$HOSTSTATE$', (string) $host->getStatus()->getName(), $expected);
        $expected = str_replace('$_HOSTCUSTOMVAL$', $customMacrosValues['$_HOSTCUSTOMVAL$'], $expected);
        $this->assertEquals($resourceService->replaceMacrosInHostUrl(10, 'action-url'), $expected);
    }


    /**
     * test host macros replacement
     */
    public function testReplaceMacroInService(): void
    {
        $host = (new Host())
            ->setId(10)
            ->setName('Centreon-Central')
            ->setPollerName('central')
            ->setAddressIp('127.0.0.1')
            ->setActionUrl('http://$INSTANCENAME$/$HOSTADDRESS$/$HOSTNAME$/$_HOSTCUSTOMVAL$/$HOSTSTATE$')
            ->setStatus((new ResourceStatus())->setName('UP')->setCode(0));

        $service = (new Service())
            ->setId(25)
            ->setDescription('Ping')
            ->setStatus((new ResourceStatus())->setName('OK')->setCode(0))
            ->setActionUrl(
                'http://$INSTANCENAME$/$_SERVICECUSTOMPASSWORD$/$_HOSTCUSTOMVAL$/$_SERVICECUSTOMARG1$/$SERVICEDESC$'
            )
            ->setHost($host);

        $customMacrosValues = [
            '$_HOSTCUSTOMVAL$' => 'helloworld',
            '$_SERVICECUSTOMARG1$' => 'service-hello',
            '$_SERVICECUSTOMPASSWORD$' => 'password'
        ];

        $hostMacro = (new HostMacro())
            ->setName('$_HOSTCUSTOMVAL$')
            ->setValue('helloworld')
            ->setPassword(false);

        $serviceMacroNotPassword = (new ServiceMacro())
            ->setName('$_SERVICECUSTOMARG1$')
            ->setValue('service-hello')
            ->setPassword(false);

        $serviceMacroPassword = (new ServiceMacro())
            ->setName('$_SERVICECUSTOMPASSWORD$')
            ->setValue('password')
            ->setPassword(true);

        $resourceService = new ResourceService(
            $this->resourceRepository,
            $this->monitoringRepository,
            $this->accessGroupRepository,
            $this->metaServiceConfigurationRepository,
            $this->hostMacroConfigurationRepository,
            $this->serviceConfigurationRepository
        );

        $this->monitoringRepository->expects($this->once())
            ->method('findOneHost')
            ->willReturn($host);

        $this->monitoringRepository->expects($this->once())
            ->method('findOneService')
            ->willReturn($service);

        $this->monitoringRepository->expects($this->any())
            ->method('findCustomMacrosValues')
            ->willReturn([
                'CUSTOMVAL' => 'helloworld',
                'CUSTOMARG1' => 'service-hello',
                'CUSTOMPASSWORD' => 'password'
            ]);

        $this->hostMacroConfigurationRepository->expects($this->once())
            ->method('findOnDemandHostMacros')
            ->willReturn([$hostMacro]);

        $this->serviceConfigurationRepository->expects($this->once())
            ->method('findOnDemandServiceMacros')
            ->willReturn([$serviceMacroNotPassword, $serviceMacroPassword]);

        $actionUrl = $service->getActionUrl() !== null ? $service->getActionUrl() : '';
        $expected = str_replace('$HOSTADDRESS$', (string) $host->getAddressIp(), $actionUrl);
        $expected = str_replace('$HOSTNAME$', (string) $host->getName(), $expected);
        $expected = str_replace('$INSTANCENAME$', (string) $host->getPollerName(), $expected);
        $expected = str_replace('$HOSTSTATE$', (string) $host->getStatus()->getName(), $expected);
        $expected = str_replace('$_HOSTCUSTOMVAL$', $customMacrosValues['$_HOSTCUSTOMVAL$'], $expected);
        $expected = str_replace('$SERVICEDESC$', (string) $service->getDescription(), $expected);
        $expected = str_replace('$_SERVICECUSTOMARG1$', $customMacrosValues['$_SERVICECUSTOMARG1$'], $expected);
        $expected = str_replace('$_SERVICECUSTOMPASSWORD$', '', $expected);

        $this->assertEquals($resourceService->replaceMacrosInServiceUrl(10, 25, 'action-url'), $expected);
    }
}

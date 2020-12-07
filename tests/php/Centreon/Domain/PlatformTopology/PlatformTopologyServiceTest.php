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

namespace Tests\Centreon\Domain\PlatformTopology;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Broker\Broker;
use Centreon\Domain\Broker\BrokerConfiguration;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Engine\EngineException;
use PHPUnit\Framework\MockObject\MockObject;
use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Repository\RepositoryException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\PlatformTopology\Platform;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;
use Centreon\Domain\PlatformTopology\PlatformTopologyService;
use Centreon\Domain\MonitoringServer\MonitoringServerException;
use Centreon\Domain\PlatformTopology\PlatformException;
use Centreon\Domain\PlatformInformation\PlatformInformationException;
use Centreon\Domain\PlatformTopology\PlatformConflictException;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRegisterRepositoryInterface;

class PlatformTopologyServiceTest extends TestCase
{
    /**
     * @var Contact|null $adminContact
     */
    protected $adminContact;

    /**
     * @var Platform|null $platform
     */
    protected $platform;

    /**
     * @var Platform|null $registeredParent
     */
    protected $registeredParent;

    /**
     * @var PlatformRepositoryInterface&MockObject $platformTopologyRepository
     */
    protected $platformTopologyRepository;

    /**
     * @var HttpClientInterface|null $httpClient
     */
    protected $httpClient;

    /**
     * @var PlatformInformationServiceInterface&MockObject $platformInformationService
     */
    private $platformInformationService;

    /**
     * @var ProxyServiceInterface&MockObject $proxyService
     */
    private $proxyService;

    /**
     * @var EngineConfiguration|null $engineConfiguration
     */
    protected $engineConfiguration;

    /**
     * @var BrokerConfiguration $brokerConfiguration
     */
    protected $brokerConfiguration;

    /**
     * @var EngineConfigurationServiceInterface&MockObject $engineConfigurationService
     */
    protected $engineConfigurationService;

    /**
     * @var MonitoringServerServiceInterface&MockObject $monitoringServerService
     */
    protected $monitoringServerService;

    /**
     * @var MonitoringServer;
     */
    protected $monitoringServer;

    /**
     * @var BrokerRepositoryInterface&MockObject $brokerRepository
     */
    protected $brokerRepository;

    /**
     * @var PlatformRegisterRepositoryInterface
     */
    private $platformTopologyRegisterRepository;

    /**
     * initiate query data
     */
    protected function setUp(): void
    {
        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $this->platform = (new Platform())
            ->setId(2)
            ->setName('poller1')
            ->setAddress('1.1.1.2')
            ->setType('poller')
            ->setParentAddress('1.1.1.1')
            ->setHostname('localhost.localdomain');

        $this->registeredParent = (new Platform())
            ->setName('Central')
            ->setAddress('1.1.1.1')
            ->setType('central')
            ->setId(1)
            ->setHostname('central.localdomain');

        $this->engineConfiguration = (new EngineConfiguration())
            ->setId(1)
            ->setIllegalObjectNameCharacters('$!?')
            ->setMonitoringServerId(1)
            ->setName('Central');

        $this->monitoringServer = (new MonitoringServer())
            ->setId(1)
            ->setName('Central');

        $this->brokerConfiguration = (new BrokerConfiguration())
            ->setConfigurationKey('one_peer_retention_mode')
            ->setConfigurationValue('no');

        $this->platformTopologyRepository = $this->createMock(PlatformTopologyRepositoryInterface::class);
        $this->platformInformationService = $this->createMock(PlatformInformationServiceInterface::class);
        $this->proxyService = $this->createMock(ProxyServiceInterface::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->engineConfigurationService = $this->createMock(EngineConfigurationServiceInterface::class);
        $this->monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);
        $this->brokerRepository = $this->createMock(BrokerRepositoryInterface::class);
        $this->platformTopologyRegisterRepository = $this->createMock(
            PlatformTopologyRegisterRepositoryInterface::class
        );
    }

    /**
     * test addPlatformToTopology with already existing platform
     * @throws PlatformConflictException
     * @throws MonitoringServerException
     * @throws EngineException
     * @throws PlatformException
     * @throws EntityNotFoundException
     * @throws RepositoryException
     * @throws PlatformInformationException
     */
    public function testAddPlatformToTopologyAlreadyExists(): void
    {
        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('isPlatformAlreadyRegisteredInTopology')
            ->willReturn(true);

        $this->monitoringServerService
            ->expects($this->once())
            ->method('findLocalServer')
            ->willReturn($this->monitoringServer);

        $this->engineConfigurationService
            ->expects($this->once())
            ->method('findEngineConfigurationByName')
            ->willReturn($this->engineConfiguration);

        $platformTopologyService = new PlatformTopologyService(
            $this->platformTopologyRepository,
            $this->platformInformationService,
            $this->proxyService,
            $this->engineConfigurationService,
            $this->monitoringServerService,
            $this->brokerRepository,
            $this->platformTopologyRegisterRepository
        );

        $this->expectException(PlatformConflictException::class);
        $this->expectExceptionMessage("A platform using the name : 'poller1' or address : '1.1.1.2' already exists");
        $platformTopologyService->addPlatformToTopology($this->platform);
    }

    /**
     * test addPlatformToTopology with not found parent
     * @throws PlatformConflictException
     * @throws MonitoringServerException
     * @throws EngineException
     * @throws PlatformException
     * @throws EntityNotFoundException
     * @throws PlatformInformationException
     * @throws RepositoryException
     */
    public function testAddPlatformToTopologyNotFoundParent(): void
    {
        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('isPlatformAlreadyRegisteredInTopology')
            ->willReturn(false);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('findPlatformByAddress')
            ->willReturn(null);

        $this->monitoringServerService
            ->expects($this->once())
            ->method('findLocalServer')
            ->willReturn($this->monitoringServer);

        $this->engineConfigurationService
            ->expects($this->once())
            ->method('findEngineConfigurationByName')
            ->willReturn($this->engineConfiguration);

        $platformTopologyService = new PlatformTopologyService(
            $this->platformTopologyRepository,
            $this->platformInformationService,
            $this->proxyService,
            $this->engineConfigurationService,
            $this->monitoringServerService,
            $this->brokerRepository,
            $this->platformTopologyRegisterRepository
        );

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("No parent platform was found for : 'poller1'@'1.1.1.2'");
        $platformTopologyService->addPlatformToTopology($this->platform);
    }

    /**
     * test addPlatformToTopology which succeed
     * @throws PlatformConflictException
     * @throws MonitoringServerException
     * @throws EngineException
     * @throws PlatformException
     * @throws EntityNotFoundException
     * @throws PlatformInformationException
     * @throws RepositoryException
     */
    public function testAddPlatformToTopologySuccess(): void
    {
        $this->platform->setParentId(1);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('isPlatformAlreadyRegisteredInTopology')
            ->willReturn(false);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('findPlatformByAddress')
            ->willReturn($this->registeredParent);

        $this->monitoringServerService
            ->expects($this->once())
            ->method('findLocalServer')
            ->willReturn($this->monitoringServer);

        $this->engineConfigurationService
            ->expects($this->once())
            ->method('findEngineConfigurationByName')
            ->willReturn($this->engineConfiguration);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('addPlatformToTopology')
            ->willReturn(null);

        $platformTopologyService = new PlatformTopologyService(
            $this->platformTopologyRepository,
            $this->platformInformationService,
            $this->proxyService,
            $this->engineConfigurationService,
            $this->monitoringServerService,
            $this->brokerRepository,
            $this->platformTopologyRegisterRepository
        );

        $this->assertNull($platformTopologyService->addPlatformToTopology($this->platform));
    }

    public function testGetPlatformTopologySuccess(): void
    {
        $this->platform
            ->setParentId(1)
            ->setServerId(2);

        $this->registeredParent
            ->setServerId(1);

        $this->brokerRepository
            ->expects($this->any())
            ->method('findByMonitoringServerAndParameterName')
            ->willReturn([$this->brokerConfiguration]);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('getPlatformTopology')
            ->willReturn([$this->platform, $this->registeredParent]);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('findPlatformAddressById')
            ->willReturn('1.1.1.1');

        $platformTopologyService = new PlatformTopologyService(
            $this->platformTopologyRepository,
            $this->platformInformationService,
            $this->proxyService,
            $this->engineConfigurationService,
            $this->monitoringServerService,
            $this->brokerRepository,
            $this->platformTopologyRegisterRepository
        );

        $this->assertIsArray($platformTopologyService->getPlatformTopology());
    }

    public function testGetPlatformTopologyWithoutParentId(): void
    {
        $this->registeredParent
            ->setServerId(1);

        $this->brokerRepository
            ->expects($this->any())
            ->method('findByMonitoringServerAndParameterName')
            ->willReturn([$this->brokerConfiguration]);

        $this->platformTopologyRepository
            ->expects($this->once(0))
            ->method('getPlatformTopology')
            ->willReturn([$this->registeredParent]);

        $platformTopologyService = new PlatformTopologyService(
            $this->platformTopologyRepository,
            $this->platformInformationService,
            $this->proxyService,
            $this->engineConfigurationService,
            $this->monitoringServerService,
            $this->brokerRepository,
            $this->platformTopologyRegisterRepository
        );

        /**
         * Central Case
         */
        $this->assertIsArray($platformTopologyService->getPlatformTopology());
    }

    public function testGetPlatformTopologyRelationSetting(): void
    {
        $this->registeredParent
            ->setServerId(1);

        $this->platform
            ->setId(2)
            ->setParentId(1)
            ->setServerId(2);

        $this->platformTopologyRepository
            ->expects($this->exactly(2))
            ->method('getPlatformTopology')
            ->willReturn([$this->registeredParent, $this->platform]);

        $this->platformTopologyRepository
            ->expects($this->exactly(2))
            ->method('findPlatform')
            ->willReturn($this->registeredParent);

        $brokerConfigurationPeerRetention = (new BrokerConfiguration())
            ->setConfigurationKey('one_peer_retention_mode')
            ->setConfigurationValue('yes');

        $this->brokerRepository
            ->expects($this->at(0))
            ->method('findByMonitoringServerAndParameterName')
            ->willReturn([$this->brokerConfiguration]);

        $this->brokerRepository
            ->expects($this->at(1))
            ->method('findByMonitoringServerAndParameterName')
            ->willReturn([$this->brokerConfiguration]);

        $this->brokerRepository
            ->expects($this->at(2))
            ->method('findByMonitoringServerAndParameterName')
            ->willReturn([$brokerConfigurationPeerRetention]);

        $this->brokerRepository
            ->expects($this->at(3))
            ->method('findByMonitoringServerAndParameterName')
            ->willReturn([$brokerConfigurationPeerRetention]);

        $platformTopologyService = new PlatformTopologyService(
            $this->platformTopologyRepository,
            $this->platformInformationService,
            $this->proxyService,
            $this->engineConfigurationService,
            $this->monitoringServerService,
            $this->brokerRepository,
            $this->platformTopologyRegisterRepository
        );

        /**
         * Normal Relation
         */
        $completeTopology = $platformTopologyService->getPlatformTopology();

        $centralRelation = $completeTopology[0]->getRelation();
        $pollerRelation = $completeTopology[1]->getRelation();

        $this->assertEquals(null, $centralRelation);
        $this->assertEquals('normal', $pollerRelation->getRelation());

        /**
         * One Peer Retention Relation
         */
        $completeTopology = $platformTopologyService->getPlatformTopology();

        $centralRelation = $completeTopology[0]->getRelation();
        $pollerRelation = $completeTopology[1]->getRelation();

        $this->assertEquals(null, $centralRelation);
        $this->assertEquals('peer_retention', $pollerRelation->getRelation());
    }

    public function testDeletePlatformTopologySuccess(): void
    {
        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('findPlatform')
            ->willReturn($this->platform);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('deletePlatform')
            ->willReturn(null);

        $platformTopologyService = new PlatformTopologyService(
            $this->platformTopologyRepository,
            $this->platformInformationService,
            $this->proxyService,
            $this->engineConfigurationService,
            $this->monitoringServerService,
            $this->brokerRepository,
            $this->platformTopologyRegisterRepository
        );

        $this->assertEquals(null, $platformTopologyService->deletePlatform($this->platform->getId()));
    }

    public function testDeletePlatformTopologyWithBadId(): void
    {
        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('findPlatform')
            ->willReturn(null);

        $platformTopologyService = new PlatformTopologyService(
            $this->platformTopologyRepository,
            $this->platformInformationService,
            $this->proxyService,
            $this->engineConfigurationService,
            $this->monitoringServerService,
            $this->brokerRepository,
            $this->platformTopologyRegisterRepository
        );

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Platform not found.');

        $platformTopologyService->deletePlatform($this->platform->getId());
    }
}

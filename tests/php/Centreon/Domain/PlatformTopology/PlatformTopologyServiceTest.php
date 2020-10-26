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

use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Engine\EngineException;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\MonitoringServer\MonitoringServerException;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\PlatformInformation\PlatformInformationException;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRegisterRepositoryInterface;
use Centreon\Domain\PlatformTopology\PlatformTopologyException;
use Centreon\Domain\PlatformTopology\PlatformTopologyService;
use Centreon\Domain\PlatformTopology\PlatformTopology;
use Centreon\Domain\PlatformTopology\PlatformTopologyConflictException;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\Repository\RepositoryException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PlatformTopologyServiceTest extends TestCase
{
    /**
     * @var Contact|null $adminContact
     */
    protected $adminContact;

    /**
     * @var PlatformTopology|null $platformTopology
     */
    protected $platformTopology;

    /**
     * @var PlatformTopology|null $registeredParent
     */
    protected $registeredParent;

    /**
     * @var PlatformTopologyRepositoryInterface&MockObject $platformTopologyRepository
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
     * @var PlatformTopologyRegisterRepositoryInterface
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

        $this->platformTopology = (new PlatformTopology())
            ->setName('poller1')
            ->setAddress('1.1.1.2')
            ->setType('poller')
            ->setParentAddress('1.1.1.1')
            ->setHostname('localhost.localdomain');

        $this->registeredParent = (new PlatformTopology())
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

        $this->platformTopologyRepository = $this->createMock(PlatformTopologyRepositoryInterface::class);
        $this->platformInformationService = $this->createMock(PlatformInformationServiceInterface::class);
        $this->proxyService = $this->createMock(ProxyServiceInterface::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->engineConfigurationService = $this->createMock(EngineConfigurationServiceInterface::class);
        $this->monitoringServerService = $this->createMock(MonitoringServerServiceInterface::class);
        $this->platformTopologyRegisterRepository = $this->createMock(
            PlatformTopologyRegisterRepositoryInterface::class
        );
    }

    /**
     * test addPlatformToTopology with already existing platform
     * @throws PlatformTopologyConflictException
     * @throws MonitoringServerException
     * @throws EngineException
     * @throws PlatformTopologyException
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
            $this->platformTopologyRegisterRepository
        );

        $this->expectException(PlatformTopologyConflictException::class);
        $this->expectExceptionMessage("A platform using the name : 'poller1' or address : '1.1.1.2' already exists");
        $platformTopologyService->addPlatformToTopology($this->platformTopology);
    }

    /**
     * test addPlatformToTopology with not found parent
     * @throws PlatformTopologyConflictException
     * @throws MonitoringServerException
     * @throws EngineException
     * @throws PlatformTopologyException
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
            ->method('findPlatformTopologyByAddress')
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
            $this->platformTopologyRegisterRepository
        );

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("No parent platform was found for : 'poller1'@'1.1.1.2'");
        $platformTopologyService->addPlatformToTopology($this->platformTopology);
    }

    /**
     * test addPlatformToTopology which succeed
     * @throws PlatformTopologyConflictException
     * @throws MonitoringServerException
     * @throws EngineException
     * @throws PlatformTopologyException
     * @throws EntityNotFoundException
     * @throws PlatformInformationException
     * @throws RepositoryException
     */
    public function testAddPlatformToTopologySuccess(): void
    {
        $this->platformTopology->setParentId(1);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('isPlatformAlreadyRegisteredInTopology')
            ->willReturn(false);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('findPlatformTopologyByAddress')
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
            $this->platformTopologyRegisterRepository
        );

        $this->assertNull($platformTopologyService->addPlatformToTopology($this->platformTopology));
    }
}

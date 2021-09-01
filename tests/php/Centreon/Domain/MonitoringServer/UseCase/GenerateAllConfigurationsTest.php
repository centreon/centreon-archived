<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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
declare(strict_types=1);

namespace Tests\Centreon\Domain\MonitoringServer\UseCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\MonitoringServer\UseCase\GenerateAllConfigurations;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerRepositoryInterface;
use Centreon\Domain\MonitoringServer\Exception\ConfigurationMonitoringServerException;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerConfigurationRepositoryInterface;
use Centreon\Domain\Repository\RepositoryException;

class GenerateAllConfigurationsTest extends TestCase
{
    /**
     * @var MonitoringServerRepositoryInterface&MockObject
     */
    private $monitoringServerRepository;

    /**
     * @var MonitoringServerConfigurationRepositoryInterface&MockObject
     */
    private $monitoringServerConfigurationRepository;

    protected function setUp(): void
    {
        $this->monitoringServerRepository = $this->createMock(MonitoringServerRepositoryInterface::class);
        $this->monitoringServerConfigurationRepository =
            $this->createMock(MonitoringServerConfigurationRepositoryInterface::class);
    }

    public function testErrorRetrievingMonitoringServersException(): void
    {
        $exception = new \Exception();
        $this->monitoringServerRepository
            ->expects($this->once())
            ->method('findServersWithRequestParameters')
            ->willThrowException($exception);

        $this->expectException(ConfigurationMonitoringServerException::class);
        $this->expectExceptionMessage(
            ConfigurationMonitoringServerException::errorRetrievingMonitoringServers($exception)->getMessage()
        );
        $useCase = new GenerateAllConfigurations(
            $this->monitoringServerRepository,
            $this->monitoringServerConfigurationRepository
        );
        $useCase->execute();
    }

    public function testErrorOnGeneration(): void
    {
        $monitoringServers = [
            (new MonitoringServer())->setId(1)
        ];

        $repositoryException = new RepositoryException('Test exception message');

        $this->monitoringServerRepository
            ->expects($this->once())
            ->method('findServersWithRequestParameters')
            ->willReturn($monitoringServers);

        $this->monitoringServerConfigurationRepository
            ->expects($this->once())
            ->method('generateConfiguration')
            ->willThrowException($repositoryException);

        $useCase = new GenerateAllConfigurations(
            $this->monitoringServerRepository,
            $this->monitoringServerConfigurationRepository
        );

        $this->expectException(ConfigurationMonitoringServerException::class);
        $this->expectExceptionMessage(ConfigurationMonitoringServerException::errorOnGeneration(
            $monitoringServers[0]->getId(),
            $repositoryException->getMessage()
        )->getMessage());
        $useCase->execute();
    }

    public function testSuccess(): void
    {
        $monitoringServer = (new MonitoringServer())->setId(1);
        $monitoringServers = [$monitoringServer];
        $this->monitoringServerRepository
            ->expects($this->once())
            ->method('findServersWithRequestParameters')
            ->willReturn($monitoringServers);

        $this->monitoringServerConfigurationRepository
            ->expects($this->once())
            ->method('generateConfiguration')
            ->with($monitoringServer->getId());

        $this->monitoringServerConfigurationRepository
            ->expects($this->once())
            ->method('moveExportFiles')
            ->with($monitoringServer->getId());

        $useCase = new GenerateAllConfigurations(
            $this->monitoringServerRepository,
            $this->monitoringServerConfigurationRepository
        );

        $useCase->execute();
    }
}

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
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\MonitoringServer\UseCase\ReloadConfiguration;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerRepositoryInterface;
use Centreon\Domain\MonitoringServer\Exception\ConfigurationMonitoringServerException;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerConfigurationRepositoryInterface;

class ReloadConfigurationTest extends TestCase
{
    /**
     * @var MonitoringServerRepositoryInterface&MockObject
     */
    private $monitoringServerRepository;

    /**
     * @var MonitoringServerConfigurationRepositoryInterface&MockObject
     */
    private $monitoringServerConfigurationRepository;

    /**
     * @var MonitoringServer
     */
    private $monitoringServer;

    protected function setUp(): void
    {
        $this->monitoringServerRepository = $this->createMock(MonitoringServerRepositoryInterface::class);
        $this->monitoringServerConfigurationRepository =
            $this->createMock(MonitoringServerConfigurationRepositoryInterface::class);

        $this->monitoringServer = (new MonitoringServer())->setId(1);
    }

    public function testErrorRetrievingMonitoringServerException(): void
    {
        $this->monitoringServerRepository
            ->expects($this->once())
            ->method('findServer')
            ->willReturn(null);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage(
            ConfigurationMonitoringServerException::notFound($this->monitoringServer->getId())->getMessage()
        );
        $useCase = new ReloadConfiguration(
            $this->monitoringServerRepository,
            $this->monitoringServerConfigurationRepository
        );
        $useCase->execute($this->monitoringServer->getId());
    }

    public function testErrorOnReload(): void
    {
        $repositoryException = new RepositoryException('Test exception message');

        $this->monitoringServerRepository
            ->expects($this->once())
            ->method('findServer')
            ->willReturn($this->monitoringServer);

        $this->monitoringServerConfigurationRepository
            ->expects($this->once())
            ->method('reloadConfiguration')
            ->willThrowException($repositoryException);

        $useCase = new ReloadConfiguration(
            $this->monitoringServerRepository,
            $this->monitoringServerConfigurationRepository
        );
        $this->expectException(ConfigurationMonitoringServerException::class);
        $this->expectExceptionMessage(ConfigurationMonitoringServerException::errorOnReload(
            $this->monitoringServer->getId(),
            $repositoryException->getMessage()
        )->getMessage());

        $useCase->execute($this->monitoringServer->getId());
    }

    public function testSuccess(): void
    {
        $this->monitoringServerRepository
            ->expects($this->once())
            ->method('findServer')
            ->willReturn($this->monitoringServer);

        $this->monitoringServerConfigurationRepository
            ->expects($this->once())
            ->method('reloadConfiguration')
            ->with($this->monitoringServer->getId());

        $useCase = new ReloadConfiguration(
            $this->monitoringServerRepository,
            $this->monitoringServerConfigurationRepository
        );
        $useCase->execute($this->monitoringServer->getId());
    }
}

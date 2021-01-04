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

namespace Tests\Centreon\Domain\PlatformInformation;

use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationRepositoryInterface;
use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Domain\PlatformInformation\PlatformInformationService;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use PHPUnit\Framework\TestCase;

class PlatformInformationServiceTest extends TestCase
{

    /**
     * @var PlatformInformationRepositoryInterface&MockObject $platformInformationRepository
     */
    private $platformInformationRepository;

    /**
     * @var RemoteServerServiceInterface&MockObject $remoteServerService
     */
    private $remoteServerService;

    /**
     * @var PlatformInformation
     */
    private $remoteInformation;

    /**
     * @var PlatformInformation
     */
    private $centralInformation;

    protected function setUp(): void
    {
        $this->platformInformationRepository = $this->createMock(PlatformInformationRepositoryInterface::class);
        $this->remoteServerService = $this->createMock(RemoteServerServiceInterface::class);
        $this->remoteInformation = (new PlatformInformation())->setIsRemote(true);
        $this->centralInformation = (new PlatformInformation())->setIsCentral(true);
    }

    public function testUpdatePlatformToRemoteSuccess()
    {
        $this->platformInformationRepository
            ->expects($this->once())
            ->method('findPlatformInformation')
            ->willReturn($this->centralInformation);

        $platformInformationService = new PlatformInformationService(
            $this->platformInformationRepository,
            $this->remoteServerService
        );

        $this->remoteServerService->expects($this->once())->method('convertCentralToRemote');

        $platformInformationService->updatePlatformInformation($this->remoteInformation);
    }

    public function testUpdatePlatformToCentralSuccess()
    {
        $this->platformInformationRepository
            ->expects($this->once())
            ->method('findPlatformInformation')
            ->willReturn($this->remoteInformation);

        $platformInformationService = new PlatformInformationService(
            $this->platformInformationRepository,
            $this->remoteServerService
        );

        $this->remoteServerService->expects($this->once())->method('convertRemoteToCentral');

        $platformInformationService->updatePlatformInformation($this->centralInformation);
    }
}

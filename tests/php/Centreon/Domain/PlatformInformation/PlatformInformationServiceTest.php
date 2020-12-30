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
     * Basic Mock Informations
     *
     * @var PlatformInformation
     */
    private $baseInformation;

    /**
     * Information for a remote conversion
     *
     * @var PlatformInformation
     */
    private $informationRemote;

    /**
     * @var PlatformInformationRepositoryInterface&MockObject $platformInformationRepository
     */
    private $platformInformationRepository;

    /**
     * @var RemoteServerServiceInterface&MockObject $remoteServerService
     */
    private $remoteServerService;

    protected function setUp(): void
    {
        $this->baseInformation = (new PlatformInformation())
            ->setVersion('21.04.0')
            ->setAppKey('xxXxxXXxXXxxx')
            ->setIsCentral(true)
            ->setIsRemote(false);

        $this->informationRemote = (new PlatformInformation())
            ->setIsRemote(true)
            ->setApiUsername('admin')
            ->setApiCredentials('centreon')
            ->setApiScheme('http')
            ->setApiPort(80)
            ->setApiPath('path')
            ->setCentralServerAddress('192.168.0.1');

        $this->platformInformationRepository = $this->createMock(PlatformInformationRepositoryInterface::class);
        $this->remoteServerService = $this->createMock(RemoteServerServiceInterface::class);
    }

    public function testUpdatePlatformSuccess(): void
    {
        $this->platformInformationRepository
            ->expects($this->once())
            ->method('findPlatformInformation')
            ->willReturn($this->baseInformation);

        $this->platformInformationRepository
            ->expects($this->once())
            ->method('updatePlatformInformation')
            ->willReturn($this->informationRemote);

        $platformInformationService = new PlatformInformationService(
            $this->platformInformationRepository,
            $this->remoteServerService
        );

        $this->assertInstanceOf(
            PlatformInformation::class,
            $platformInformationService->updatePlatformInformation($this->informationRemote)
        );
    }
}

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

namespace Tests\Centreon\Domain\PlatformInformation\UseCase;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Tests\Centreon\Domain\PlatformInformation\Model\PlatformInformationTest;
use Centreon\Domain\PlatformInformation\UseCase\V20\UpdatePartiallyPlatformInformation;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationReadRepositoryInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationRepositoryInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationWriteRepositoryInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;

class UpdatePartiallyPlatformInformationTest extends TestCase
{
    /**
     * @var PlatformInformationReadRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $readRepository;

    /**
     * @var PlatformInformationWriteRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $writeRepository;

    /**
     * @var PlatformInformation
     */
    private $centralInformation;

    /**
     * @var PlatformInformation
     */
    private $remoteInformation;

    /**
     * @var ProxyServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $proxyService;

    /**
     * @var RemoteServerServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $remoteServerService;

    /**
     * @var PlatformTopologyServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $platformTopologyService;

    /**
     * @var array<string,bool>
     */
    private $centralInformationRequest;

    /**
     * @var array<string, mixed>
     */
    private $remoteInformationRequest;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(PlatformInformationReadRepositoryInterface::class);
        $this->writeRepository = $this->createMock(PlatformInformationWriteRepositoryInterface::class);
        $this->proxyService = $this->createMock(ProxyServiceInterface::class);
        $this->remoteServerService = $this->createMock(RemoteServerServiceInterface::class);
        $this->platformTopologyService = $this->createMock(PlatformTopologyServiceInterface::class);
        $this->centralInformation = PlatformInformationTest::createEntityForCentralInformation();
        $this->remoteInformation = PlatformInformationTest::createEntityForRemoteInformation();
        $this->centralInformationRequest = ['isRemote' => false];
        $this->remoteInformationRequest = [
            'isRemote' => true,
            'centralServerAddress' => '1.1.1.10',
            'apiUsername' => 'admin',
            'apiCredentials' => 'centreon',
            'apiScheme' => 'http',
            'apiPort' => 80,
            'apiPath' => 'centreon',
            'peerValidation' => false
        ];
    }

    /**
     * @return UpdatePartiallyPlatformInformation
     */
    private function createUpdatePartiallyPlatformUseCase(): UpdatePartiallyPlatformInformation
    {
        $useCase = new UpdatePartiallyPlatformInformation(
            $this->writeRepository,
            $this->readRepository,
            $this->proxyService,
            $this->remoteServerService,
            $this->platformTopologyService
        );
        $useCase->setEncryptionFirstKey('encryptionF0rT3st');
        return $useCase;
    }

    /**
     * Test that the use case will call the central to remote conversion method
     *
     * @return void
     */
    public function testExecuteUpdateToRemote(): void
    {
        $this->readRepository->expects($this->any())
            ->method('findPlatformInformation')
            ->willReturn($this->centralInformation);
        $updatePartiallyPlatformInformation = $this->createUpdatePartiallyPlatformUseCase();
        $this->remoteServerService->expects($this->once())->method('convertCentralToRemote');
        $updatePartiallyPlatformInformation->execute($this->remoteInformationRequest);
    }

    /**
     * Test that the use case will call the remote to central conversion method
     *
     * @return void
     */
    public function testExecuteUpdateToCentral(): void
    {
        $this->readRepository->expects($this->any())
            ->method('findPlatformInformation')
            ->willReturn($this->remoteInformation);
        $updatePartiallyPlatformInformation = $this->createUpdatePartiallyPlatformUseCase();
        $this->remoteServerService->expects($this->once())->method('convertRemoteToCentral');
        $updatePartiallyPlatformInformation->execute($this->centralInformationRequest);
    }
}

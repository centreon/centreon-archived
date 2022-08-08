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

namespace Tests\Centreon\Domain\PlatformInformation\Model;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\PlatformInformation\Model\PlatformInformationFactory;

class PlatformInformationFactoryTest extends TestCase
{
    /**
     * @var PlatformInformation
     */
    private $centralPlatformInformationStub;

    /**
     * @var PlatformInformation
     */
    private $remotePlatformInformationStub;

    /**
     * @var array<string,mixed>
     */
    private $remoteRequest;

    /**
     * @var array<string,boolean>
     */
    private $centralRequest;

    private $platformInformationFactory;

    protected function setUp(): void
    {
        $this->centralPlatformInformationStub = PlatformInformationTest::createEntityForCentralInformation();
        $this->remotePlatformInformationStub = PlatformInformationTest::createEntityForRemoteInformation();
        $this->remoteRequest = [
            'isRemote' => true,
            'centralServerAddress' => '1.1.1.10',
            'apiUsername' => 'admin',
            'apiCredentials' => 'centreon',
            'apiScheme' => 'http',
            'apiPort' => 80,
            'apiPath' => 'centreon',
            'peerValidation' => false
        ];
        $this->centralRequest = [
            'isRemote' => false
        ];
        $this->platformInformationFactory = new PlatformInformationFactory(
            'encryptionF0rT3st'
        );
    }

    public function testCreateRemotePlatformInformation(): void
    {
        $remotePlatformInformation = $this->platformInformationFactory->createRemoteInformation($this->remoteRequest);
        $this->assertEquals($this->remotePlatformInformationStub->isRemote(), $remotePlatformInformation->isRemote());
        $this->assertEquals(
            $this->remotePlatformInformationStub->getCentralServerAddress(),
            $remotePlatformInformation->getCentralServerAddress()
        );
        $this->assertEquals(
            $this->remotePlatformInformationStub->getApiUsername(),
            $remotePlatformInformation->getApiUsername()
        );
        $this->assertEquals(
            $this->remotePlatformInformationStub->getApiCredentials(),
            $remotePlatformInformation->getApiCredentials()
        );
        $this->assertEquals(
            $this->remotePlatformInformationStub->getApiScheme(),
            $remotePlatformInformation->getApiScheme()
        );
        $this->assertEquals(
            $this->remotePlatformInformationStub->getApiPort(),
            $remotePlatformInformation->getApiPort()
        );
        $this->assertEquals(
            $this->remotePlatformInformationStub->getApiPath(),
            $remotePlatformInformation->getApiPath()
        );
        $this->assertEquals(
            $this->remotePlatformInformationStub->hasApiPeerValidation(),
            $remotePlatformInformation->hasApiPeerValidation()
        );
    }

    public function testCreateCentralPlatformInformation(): void
    {
        $centralPlatformInformation = $this->platformInformationFactory->createCentralInformation(
            $this->centralRequest
        );
        $this->assertEquals($this->centralPlatformInformationStub->isRemote(), $centralPlatformInformation->isRemote());
        $this->assertEquals(
            $this->centralPlatformInformationStub->getCentralServerAddress(),
            $centralPlatformInformation->getCentralServerAddress()
        );
        $this->assertEquals(
            $this->centralPlatformInformationStub->getApiUsername(),
            $centralPlatformInformation->getApiUsername()
        );
        $this->assertEquals(
            $this->centralPlatformInformationStub->getApiCredentials(),
            $centralPlatformInformation->getApiCredentials()
        );
        $this->assertEquals(
            $this->centralPlatformInformationStub->getApiScheme(),
            $centralPlatformInformation->getApiScheme()
        );
        $this->assertEquals(
            $this->centralPlatformInformationStub->getApiPort(),
            $centralPlatformInformation->getApiPort()
        );
        $this->assertEquals(
            $this->centralPlatformInformationStub->getApiPath(),
            $centralPlatformInformation->getApiPath()
        );
        $this->assertEquals(
            $this->centralPlatformInformationStub->hasApiPeerValidation(),
            $centralPlatformInformation->hasApiPeerValidation()
        );
    }
}

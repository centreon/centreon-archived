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
use Centreon\Domain\PlatformInformation\Model\InformationV21Factory;
use Tests\Centreon\Domain\PlatformInformation\Model\InformationTest;
use Centreon\Domain\PlatformInformation\Model\PlatformInformationFactory;

class PlatformInformationFactoryTest extends TestCase
{
    /**
     * @var PlatformInformation
     */
    private $centralPlatformInformation;

    /**
     * @var PlatformInformation
     */
    private $remotePlatformInformationStub;

    /**
     * @var array<InformationV21>
     */
    private $informationV21;

    protected function setUp(): void
    {
        $this->centralPlatformInformation = PlatformInformationTest::createEntityForCentralInformation();
        $this->remotePlatformInformationStub = PlatformInformationTest::createEntityForRemoteInformation();
        $remoteInformation = InformationTest::createEntities();
        $this->informationV21 = InformationV21Factory::create($remoteInformation);
    }

    public function testCreate(): void
    {
        $remotePlatformInformation = PlatformInformationFactory::create($this->informationV21);
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
}

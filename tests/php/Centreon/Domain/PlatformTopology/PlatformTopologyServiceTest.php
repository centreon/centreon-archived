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

use Centreon\Domain\PlatformTopology\PlatformTopologyService;
use Centreon\Domain\PlatformTopology\PlatformTopology;
use Centreon\Domain\PlatformTopology\PlatformTopologyException;
use Centreon\Domain\PlatformTopology\PlatformTopologyConflictException;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Contact\Contact;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

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
     * @var PlatformTopologyRepositoryInterface&MockObject $platformTopologyRepository
     */
    protected $platformTopologyRepository;

    protected function setUp()
    {
        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $this->platformTopology = (new PlatformTopology())
            ->setName('poller1')
            ->setType('poller')
            ->setAddress('1.1.1.2')
            ->setParentAddress('1.1.1.1');

        $this->platformTopologyRepository = $this->createMock(PlatformTopologyRepositoryInterface::class);
    }

    /**
     * test addPlatformToTopology with already existing platform
     */
    public function testAddPlatformToTopologyAlreadyExists()
    {
        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('isPlatformAlreadyRegisteredInTopology')
            ->willReturn(true);

        $platformTopologyService = new PlatformTopologyService($this->platformTopologyRepository);

        $this->expectException(PlatformTopologyConflictException::class);
        $this->expectExceptionMessage("A platform using the name : 'poller1' or address : '1.1.1.2' already exists");
        $platformTopologyService->addPlatformToTopology($this->platformTopology);
    }

    /**
     * test addPlatformToTopology with not found parent
     */
    public function testAddPlatformToTopologyNotFoundParent()
    {
        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('isPlatformAlreadyRegisteredInTopology')
            ->willReturn(false);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('findPlatformTopologyByAddress')
            ->willReturn(null);

        $platformTopologyService = new PlatformTopologyService($this->platformTopologyRepository);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage("No parent platform was found for : 'poller1'@'1.1.1.2'");
        $platformTopologyService->addPlatformToTopology($this->platformTopology);
    }

    /**
     * test addPlatformToTopology which succeed
     */
    public function testAddPlatformToTopologySuccess()
    {
        $this->platformTopology->setParentAddress(null);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('isPlatformAlreadyRegisteredInTopology')
            ->willReturn(false);

        $this->platformTopologyRepository
            ->expects($this->once())
            ->method('addPlatformToTopology')
            ->willReturn(null);

        $platformTopologyService = new PlatformTopologyService($this->platformTopologyRepository);

        $this->assertNull($platformTopologyService->addPlatformToTopology($this->platformTopology));
    }
}

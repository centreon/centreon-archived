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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V2110\HostSeverity;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\HostConfiguration\Interfaces\HostSeverity\HostSeverityServiceInterface;
use Centreon\Domain\HostConfiguration\UseCase\V2110\HostSeverity\FindHostSeverities;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostSeverityTest;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\UseCase\V2110\HostSeverity
 */
class FindHostSeveritiesTest extends TestCase
{
    /**
     * @var HostSeverityServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostSeveritySerive;
    /**
     * @var \Centreon\Domain\HostConfiguration\Model\HostSeverity
     */
    private $hostSeverity;

    protected function setUp(): void
    {
        $this->hostSeveritySerive = $this->createMock(HostSeverityServiceInterface::class);
        $this->hostSeverity = hostSeverityTest::createEntity();
    }

    /**
     * @param bool $isAdmin
     * @return FindHostSeverities
     */
    private function createHostSeverityUseCase(bool $isAdmin = false): FindHostSeverities
    {
        $contact = new Contact();
        $contact->setAdmin($isAdmin);
        return (new FindHostSeverities($this->hostSeveritySerive, $contact));
    }

    /**
     * Test as admin user
     */
    public function testExecuteAsAdmin(): void
    {
        $this->hostSeveritySerive->expects($this->once())
            ->method('findAllWithoutAcl')
            ->willReturn([$this->hostSeverity]);
        $findHostSeverities = $this->createHostSeverityUseCase(true);
        $response = $findHostSeverities->execute();
        $this->assertCount(1, $response->getHostSeverities());
    }

    /**
     * Test as non admin user
     */
    public function testExecuteAsNonAdmin(): void
    {
        $this->hostSeveritySerive->expects($this->once())
            ->method('findAllWithAcl')
            ->willReturn([$this->hostSeverity]);
        $findHostSeverities = $this->createHostSeverityUseCase();
        $response = $findHostSeverities->execute();
        $this->assertCount(1, $response->getHostSeverities());
    }
}

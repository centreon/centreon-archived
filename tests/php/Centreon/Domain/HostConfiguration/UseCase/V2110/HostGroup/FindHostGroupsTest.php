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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V2110\HostGroup;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupServiceInterface;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\HostConfiguration\UseCase\V2110\HostGroup\FindHostGroups;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostGroupTest;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\UseCase\V2110\HostGroup
 */
class FindHostGroupsTest extends TestCase
{
    /**
     * @var HostGroupServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostGroupService;
    /**
     * @var HostGroup
     */
    private $hostGroup;

    protected function setUp(): void
    {
        $this->hostGroupService = $this->createMock(HostGroupServiceInterface::class);
        $this->hostGroup = HostGroupTest::createEntity();
    }

    private function createHostGroupUseCase(bool $isAdmin = false): FindHostGroups
    {
        $contact = new Contact();
        $contact->setAdmin($isAdmin);
        return (new FindHostGroups($this->hostGroupService, $contact));
    }

    /**
     * Test as admin user
     */
    public function testExecuteAsAdmin(): void
    {
        $this->hostGroupService->expects($this->once())
            ->method('findAllWithoutAcl')
            ->willReturn([$this->hostGroup]);
        $findHostGroup = $this->createHostGroupUseCase(true);
        $response = $findHostGroup->execute();
        $this->assertCount(1, $response->getHostGroups());
    }

    /**
     * Test as non admin user
     */
    public function testExecuteAsNonAdmin(): void
    {
        $this->hostGroupService->expects($this->once())
            ->method('findAllWithAcl')
            ->willReturn([$this->hostGroup]);
        $findHostGroup = $this->createHostGroupUseCase(false);
        $response = $findHostGroup->execute();
        $this->assertCount(1, $response->getHostGroups());
    }
}

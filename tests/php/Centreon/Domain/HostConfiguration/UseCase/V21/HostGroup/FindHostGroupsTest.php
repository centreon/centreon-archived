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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V21\HostGroup;

use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\HostConfiguration\UseCase\V21\HostGroup\FindHostGroups;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostGroupTest;

class FindHostGroupsTest extends TestCase
{
    /**
     * @var HostGroupReadRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostGroupRepository;
    /**
     * @var HostGroup
     */
    private $hostGroup;

    protected function setUp(): void
    {
        $this->hostGroupRepository = $this->createMock(HostGroupReadRepositoryInterface::class);
        $this->hostGroup = HostGroupTest::createEntity();
    }

    private function createHostGroupUseCase(): FindHostGroups
    {
        return (new FindHostGroups($this->hostGroupRepository));
    }

    public function testExecute(): void
    {
        $this->hostGroupRepository->expects($this->once())
            ->method('findHostGroups')
            ->willReturn([$this->hostGroup]);
        $findHostGroup = $this->createHostGroupUseCase();
        $response = $findHostGroup->execute();
        $this->assertCount(1, $response->getHostGroups());
    }
}

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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V21;

use Centreon\Domain\HostConfiguration\Interfaces\HostSeverityReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\UseCase\V21\HostSeverity\FindHostSeverities;
use PHPStan\Testing\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostSeverityTest;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\UseCase\V21
 */
class FindHostSeveritiesTest extends TestCase
{
    /**
     * @var HostSeverityReadRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostSeverityReadRepository;
    /**
     * @var \Centreon\Domain\HostConfiguration\Model\HostSeverity
     */
    private $hostSeverity;

    protected function setUp(): void
    {
        $this->hostSeverityReadRepository = $this->createMock(HostSeverityReadRepositoryInterface::class);
        $this->hostSeverity = hostSeverityTest::createEntity();
    }

    /**
     * @return FindHostSeverities
     */
    private function createHostSeverityUseCase(): FindHostSeverities
    {
        return (new FindHostSeverities($this->hostSeverityReadRepository));
    }

    public function testExecute(): void
    {
        $this->hostSeverityReadRepository->expects($this->once())
            ->method('findHostSeverities')
            ->willReturn([$this->hostSeverity]);
        $findHostSeverities = $this->createHostSeverityUseCase();
        $response = $findHostSeverities->execute();
        $this->assertCount(1, $response->getHostSeverities());
    }
}

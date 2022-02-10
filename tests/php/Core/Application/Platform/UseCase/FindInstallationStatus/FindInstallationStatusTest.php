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

namespace Tests\Core\Application\Platform\UseCase\FindInstallationStatus;

use PHPUnit\Framework\TestCase;
use Core\Application\Platform\Repository\ReadPlatformRepositoryInterface;
use Core\Application\Platform\UseCase\FindInstallationStatus\FindInstallationStatus;
use Tests\Core\Application\Platform\UseCase\FindInstallationStatus\FindInstallationStatusPresenterStub;

class FindInstallationStatusTest extends TestCase
{
    /**
     * @var ReadPlatformRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    public $repository;

    public function setUp(): void
    {
        $this->repository = $this->createMock(ReadPlatformRepositoryInterface::class);
    }

    /**
     * Test that the use case will correctly pass the versions to the presenter.
     */
    public function testFindInstallationStatus(): void
    {
        $useCase = new FindInstallationStatus($this->repository);

        $presenter = new FindInstallationStatusPresenterStub();

        $this->repository
            ->expects($this->once())
            ->method('isCentreonWebUpgradeAvailable')
            ->willReturn(true);

        $this->repository
            ->expects($this->once())
            ->method('isCentreonWebInstalled')
            ->willReturn(true);

        $useCase($presenter);

        $this->assertEquals($presenter->response->isCentreonWebInstalled, true);
        $this->assertEquals($presenter->response->isCentreonWebUpgradeAvailable, true);
    }
}

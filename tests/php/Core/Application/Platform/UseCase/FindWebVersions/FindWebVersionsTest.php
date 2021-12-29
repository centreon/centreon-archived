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

namespace Tests\Core\Application\Platform\UseCase\FindWebVersions;

use Core\Application\Platform\Service\PlatformVersionServiceInterface;
use Core\Application\Platform\UseCase\FindWebVersions\FindWebVersions;
use PHPUnit\Framework\TestCase;

class FindWebVersionsTest extends TestCase
{
    /**
     * @var PlatformVersionServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    public $platformVersionService;

    public function setUp(): void
    {
        $this->platformVersionService = $this->createMock(PlatformVersionServiceInterface::class);
    }

    /**
     * Test that the use case will correctly pass the versions to the presenter.
     */
    public function testFindWebVersions(): void
    {
        $useCase = new FindWebVersions($this->platformVersionService);

        $presenter = new FindWebVersionsPresenterFake();

        $this->platformVersionService
            ->expects($this->once())
            ->method('getWebUpgradeVersion')
            ->willReturn('22.04.1');

        $this->platformVersionService
            ->expects($this->once())
            ->method('isCentreonWebInstalled')
            ->willReturn(true);

        $useCase($presenter);

        $this->assertEquals($presenter->response->isCentreonWebInstalled, true);
        $this->assertEquals($presenter->response->centreonUpgradeVersion, '22.04.1');
    }
}

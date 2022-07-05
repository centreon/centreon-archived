<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Tests\Core\Platform\Application\UseCase\UpdateVersions;

use Core\Platform\Application\UseCase\UpdateVersions\UpdateVersions;
use Core\Platform\Application\UseCase\UpdateVersions\UpdateVersionsPresenterInterface;
use Core\Platform\Application\Repository\ReadVersionRepositoryInterface;
use Core\Platform\Application\Repository\ReadUpdateRepositoryInterface;
use Core\Platform\Application\Repository\WriteUpdateRepositoryInterface;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;

beforeEach(function () {
    $this->readVersionRepository = $this->createMock(ReadVersionRepositoryInterface::class);
    $this->readUpdateRepository = $this->createMock(ReadUpdateRepositoryInterface::class);
    $this->writeUpdateRepository = $this->createMock(WriteUpdateRepositoryInterface::class);
    $this->presenter = $this->createMock(UpdateVersionsPresenterInterface::class);
});

it('should present an error response if current version is not found', function () {
    $updateVersions = new UpdateVersions(
        $this->readVersionRepository,
        $this->readUpdateRepository,
        $this->writeUpdateRepository,
    );

    $this->readVersionRepository
        ->expects($this->once())
        ->method('findCurrentVersion')
        ->willReturn(null);

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new ErrorResponse('Cannot retrieve current version'));

    $updateVersions($this->presenter);
});

it('should run found updates', function () {
    $updateVersions = new UpdateVersions(
        $this->readVersionRepository,
        $this->readUpdateRepository,
        $this->writeUpdateRepository,
    );

    $this->readVersionRepository
        ->expects($this->once())
        ->method('findCurrentVersion')
        ->willReturn('22.04.0');

    $this->readUpdateRepository
        ->expects($this->once())
        ->method('findOrderedAvailableUpdates')
        ->with('22.04.0')
        ->willReturn(['22.10.0-beta.1', '22.10.0', '22.10.1']);

    $this->writeUpdateRepository
        ->expects($this->exactly(3))
        ->method('runUpdate')
        ->withConsecutive(
            [$this->equalTo('22.10.0-beta.1')],
            [$this->equalTo('22.10.0')],
            [$this->equalTo('22.10.1')],
        );

    $this->presenter
        ->expects($this->once())
        ->method('setResponseStatus')
        ->with(new NoContentResponse());

    $updateVersions($this->presenter);
});

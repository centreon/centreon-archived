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

namespace Tests\Core\Platform\Infrastructure\Repository;

use Core\Platform\Infrastructure\Repository\FsReadUpdateRepository;
use Core\Platform\Application\Repository\UpdateNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

beforeEach(function () {
    $this->filesystem = $this->createMock(Filesystem::class);
    $this->finder = $this->createMock(Finder::class);
});

it('should return an error when install directory does not exist', function () {
    $repository = new FsReadUpdateRepository(sys_get_temp_dir(), $this->filesystem, $this->finder);

    $this->filesystem
        ->expects($this->once())
        ->method('exists')
        ->willReturn(false);

    $availableUpdates = $repository->findOrderedAvailableUpdates('22.04.0');
})->throws(
    UpdateNotFoundException::class,
    UpdateNotFoundException::updatesNotFound()->getMessage(),
);

it('should order found updates', function () {
    $repository = new FsReadUpdateRepository(sys_get_temp_dir(), $this->filesystem, $this->finder);

    $this->filesystem
        ->expects($this->once())
        ->method('exists')
        ->willReturn(true);

    $this->finder
        ->expects($this->once())
        ->method('files')
        ->willReturn($this->finder);

    $this->finder
        ->expects($this->once())
        ->method('in')
        ->willReturn($this->finder);

    $this->finder
        ->expects($this->once())
        ->method('name')
        ->willReturn(
            [
                new \SplFileInfo('Update-21.10.0.php'),
                new \SplFileInfo('Update-22.04.0.php'),
                new \SplFileInfo('Update-22.10.11.php'),
                new \SplFileInfo('Update-22.10.1.php'),
                new \SplFileInfo('Update-22.10.0-beta.3.php'),
                new \SplFileInfo('Update-22.10.0-alpha.1.php'),
            ]
        );

    $availableUpdates = $repository->findOrderedAvailableUpdates('22.04.0');
    expect($availableUpdates)->toEqual([
        '22.10.0-alpha.1',
        '22.10.0-beta.3',
        '22.10.1',
        '22.10.11'
    ]);
});

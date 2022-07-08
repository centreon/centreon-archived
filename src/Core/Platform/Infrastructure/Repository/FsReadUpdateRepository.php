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

namespace Core\Platform\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Core\Platform\Application\Repository\ReadUpdateRepositoryInterface;
use Core\Platform\Application\Repository\UpdateNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FsReadUpdateRepository implements ReadUpdateRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param string $installDir
     * @param Filesystem $filesystem
     * @param Finder $finder
     */
    public function __construct(
        private string $installDir,
        private Filesystem $filesystem,
        private Finder $finder,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function findOrderedAvailableUpdates(string $currentVersion): array
    {
        $availableUpdates = $this->findAvailableUpdates($currentVersion);

        return $this->orderUpdates($availableUpdates);
    }

    /**
     * Get available updates
     *
     * @param string $currentVersion
     * @return string[]
     */
    private function findAvailableUpdates(string $currentVersion): array
    {
        if (! $this->filesystem->exists($this->installDir)) {
            $this->error('Install directory not found on filesystem: ' . $this->installDir);
            throw UpdateNotFoundException::updatesNotFound();
        }

        $fileNameVersionRegex = '/Update-(?<version>[a-zA-Z0-9\-\.]+)\.php/';
        $availableUpdates = [];

        $updateFiles = $this->finder->files()
            ->in($this->installDir)
            ->name($fileNameVersionRegex);

        foreach ($updateFiles as $updateFile) {
            if (preg_match($fileNameVersionRegex, $updateFile->getFilename(), $matches)) {
                if (version_compare($matches['version'], $currentVersion, '>')) {
                    $this->info('Update version found: ' . $matches['version']);
                    $availableUpdates[] = $matches['version'];
                }
            }
        }

        return $availableUpdates;
    }

    /**
     * Order updates
     *
     * @param string[] $updates
     * @return string[]
     */
    private function orderUpdates(array $updates): array
    {
        usort(
            $updates,
            fn (string $versionA, string $versionB) => version_compare($versionA, $versionB),
        );

        return $updates;
    }
}

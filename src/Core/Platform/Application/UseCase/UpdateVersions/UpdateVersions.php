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

namespace Core\Platform\Application\UseCase\UpdateVersions;

use Centreon\Domain\Log\LoggerTrait;
use Core\Platform\Application\Repository\ReadVersionRepositoryInterface;
use Core\Platform\Application\Repository\ReadUpdateRepositoryInterface;
use Core\Platform\Application\Repository\WriteUpdateRepositoryInterface;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;

class UpdateVersions
{
    use LoggerTrait;

    /**
     * @param ReadVersionRepositoryInterface $readVersionRepository
     * @param ReadUpdateRepositoryInterface $readUpdateRepository
     * @param WriteUpdateRepositoryInterface $writeUpdateRepository
     */
    public function __construct(
        private ReadVersionRepositoryInterface $readVersionRepository,
        private ReadUpdateRepositoryInterface $readUpdateRepository,
        private WriteUpdateRepositoryInterface $writeUpdateRepository,
    ) {
    }

    /**
     * @param UpdateVersionsPresenterInterface $presenter
     */
    public function __invoke(
        UpdateVersionsPresenterInterface $presenter,
    ): void {
        $this->info('Updating versions');

        try {
            $currentVersion = $this->getCurrentVersionOrFail();

            $availableUpdates = $this->getAvailableUpdatesOrFail($currentVersion);

            $this->runUpdates($availableUpdates);
        } catch (\Throwable $e) {
            $this->error(
                $e->getMessage(),
                ['trace' => $e->getTraceAsString()],
            );

            $presenter->setResponseStatus(new ErrorResponse($e->getMessage()));

            return;
        }

        $presenter->setResponseStatus(new NoContentResponse());
    }

    /**
     * Get current version or fail
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getCurrentVersionOrFail(): string
    {
        $this->info('Getting current version');

        try {
            $currentVersion = $this->readVersionRepository->findCurrentVersion();
        } catch (\Exception $e) {
            throw new \Exception('An error occurred when retrieving current version', 0, $e);
        }

        if ($currentVersion === null) {
            throw new \Exception('Cannot retrieve current version');
        }

        return $currentVersion;
    }

    /**
     * Get available updates
     *
     * @param string $currentVersion
     * @return string[]
     */
    private function getAvailableUpdatesOrFail(string $currentVersion): array
    {
        try {
            $this->info(
                'Getting available updates',
                [
                    'current_version' => $currentVersion,
                ],
            );

            return $this->readUpdateRepository->findOrderedAvailableUpdates($currentVersion);
        } catch (\Throwable $e) {
            throw new \Exception('An error occurred when getting available updates', 0, $e);
        }
    }

    /**
     * Run given version updates
     *
     * @param string[] $versions
     *
     * @throws \Throwable
     */
    private function runUpdates(array $versions): void
    {
        foreach ($versions as $version) {
            try {
                $this->info("Running update $version");
                $this->writeUpdateRepository->runUpdate($version);
            } catch (\Throwable $e) {
                throw new \Exception(
                    'An error occurred when applying update ' . $version . ': ' . $e->getMessage(),
                    0,
                    $e,
                );
            }
        }
    }
}

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
use Core\Platform\Application\Repository\UpdateLockerRepositoryInterface;
use Core\Platform\Application\Repository\ReadVersionRepositoryInterface;
use Core\Platform\Application\Repository\WriteVersionRepositoryInterface;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;

class UpdateVersions
{
    use LoggerTrait;

    /**
     * @param UpdateLockerRepositoryInterface $updateLocker
     * @param ReadVersionRepositoryInterface $readVersionRepository
     * @param WriteVersionRepositoryInterface $writeVersionRepository
     */
    public function __construct(
        private UpdateLockerRepositoryInterface $updateLocker,
        private ReadVersionRepositoryInterface $readVersionRepository,
        private WriteVersionRepositoryInterface $writeVersionRepository,
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
            $this->lockUpdate();

            $currentVersion = $this->getCurrentVersion();

            $availableUpdates = $this->getAvailableUpdates($currentVersion);

            $this->runUpdates($availableUpdates);

            $this->unlockUpdate();
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
     * Lock update process
     */
    private function lockUpdate(): void
    {
        $this->info('Locking centreon update process...');

        if (!$this->updateLocker->lock()) {
            throw new \Exception('Update already in progress');
        }
    }

    /**
     * Unlock update process
     */
    private function unlockUpdate(): void
    {
        $this->info('Unlocking centreon update process...');

        $this->updateLocker->unlock();
    }

    /**
     * Get current version or fail
     *
     * @return string
     * @throws \Exception
     */
    private function getCurrentVersion(): string
    {
        $this->info('Getting current version');
        $currentVersion = $this->readVersionRepository->getCurrentVersion();

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
    private function getAvailableUpdates(string $currentVersion): array
    {
        try {
            $this->info('Getting available updates');

            return $this->readVersionRepository->getOrderedAvailableUpdates($currentVersion);
        } catch (\Throwable $e) {
            $this->error(
                'An error occurred when getting available updates',
                ['trace' => $e->getTraceAsString()],
            );

            throw $e;
        }
    }

    /**
     * Run given updates
     *
     * @param string[] $updates
     */
    private function runUpdates(array $updates): void
    {
        foreach ($updates as $update) {
            try {
                $this->info("Running update $update");
                $this->writeVersionRepository->runUpdate($update);
            } catch (\Throwable $e) {
                $this->error(
                    'An error occurred when applying update',
                    [
                        'update' => $update,
                        'trace' => $e->getTraceAsString(),
                    ],
                );

                throw $e;
            }
        }
    }
}

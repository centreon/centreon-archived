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
use Core\Platform\Application\Repository\WriteVersionRepositoryInterface;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;

class UpdateVersions
{
    use LoggerTrait;

    /**
     * @param ReadVersionRepositoryInterface $readVersionRepository
     * @param WriteVersionRepositoryInterface $writeVersionRepository
     */
    public function __construct(
        private ReadVersionRepositoryInterface $readVersionRepository,
        private WriteVersionRepositoryInterface $writeVersionRepository,
    ) {
    }

    /**
     * @param UpdateVersionsPresenterInterface $presenter
     * @param UpdateVersionsRequest $request
     */
    public function __invoke(
        UpdateVersionsPresenterInterface $presenter,
        UpdateVersionsRequest $request,
    ): void {
        $this->info('Updating versions');

        try {
            $availableUpdates = $this->getAvailableUpdates();

            $desiredUpdates = $this->filterUpdatesUntil($request->centreonWebVersion, $availableUpdates);

            $this->runUpdates($desiredUpdates);
        } catch (\Throwable $e) {
            $presenter->setResponseStatus(new ErrorResponse($e->getMessage()));
            return;
        }

        $presenter->setResponseStatus(new NoContentResponse());
    }

    /**
     * Get available updates
     *
     * @return string[]
     */
    private function getAvailableUpdates(): array
    {
        try {
            $this->info('Getting available updates');

            return $this->readVersionRepository->getAvailableUpdates();
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

    /**
     * filter updates which are anterior to given version
     *
     * @param string $version
     * @param string[] $updates
     * @return array
     *
     * @throws \Exception
     */
    private function filterUpdatesUntil(string $version, array $updates): array
    {
        $filteredUpdates = [];
        foreach ($updates as $update) {
            $filteredUpdates[] = $update;
            if ($update === $version) {
                return $filteredUpdates;
            }
        }

        $errorMessage = "Update to $version is not available";
        $this->error(
            $errorMessage,
            ['available_versions' => implode(', ', $updates)],
        );

        throw new \Exception($errorMessage);
    }
}

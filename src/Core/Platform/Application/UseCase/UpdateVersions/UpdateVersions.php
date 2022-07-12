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
use Core\Platform\Application\Validator\RequirementValidatorsInterface;
use Core\Platform\Application\Repository\UpdateLockerRepositoryInterface;
use Core\Platform\Application\Repository\ReadVersionRepositoryInterface;
use Core\Platform\Application\Repository\ReadUpdateRepositoryInterface;
use Core\Platform\Application\Repository\WriteUpdateRepositoryInterface;
use Core\Platform\Application\Repository\UpdateNotFoundException;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\Common\UseCase\NoContentResponse;

class UpdateVersions
{
    use LoggerTrait;

    /**
     * @param RequirementValidatorsInterface $requirementValidators
     * @param UpdateLockerRepositoryInterface $updateLocker
     * @param ReadVersionRepositoryInterface $readVersionRepository
     * @param ReadUpdateRepositoryInterface $readUpdateRepository
     * @param WriteUpdateRepositoryInterface $writeUpdateRepository
     */
    public function __construct(
        private RequirementValidatorsInterface $requirementValidators,
        private UpdateLockerRepositoryInterface $updateLocker,
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
            $this->validateRequirementsOrFail();

            $this->lockUpdate();

            $currentVersion = $this->getCurrentVersionOrFail();

            $availableUpdates = $this->getAvailableUpdatesOrFail($currentVersion);

            $this->runUpdates($availableUpdates);

            $this->unlockUpdate();

            $this->runPostUpdate($this->getCurrentVersionOrFail());
        } catch (UpdateNotFoundException $e) {
            $this->error(
                $e->getMessage(),
                ['trace' => $e->getTraceAsString()],
            );

            $presenter->setResponseStatus(new NotFoundResponse('Updates'));

            return;
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
     * Validate platform requirements or fail
     *
     * @throws \Exception
     */
    private function validateRequirementsOrFail(): void
    {
        $this->info('Validating platform requirements');

        $this->requirementValidators->validateRequirementsOrFail();
    }

    /**
     * Lock update process
     */
    private function lockUpdate(): void
    {
        $this->info('Locking centreon update process...');

        if (!$this->updateLocker->lock()) {
            throw UpdateVersionsException::updateAlreadyInProgress();
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
     *
     * @throws \Exception
     */
    private function getCurrentVersionOrFail(): string
    {
        $this->info('Getting current version');

        try {
            $currentVersion = $this->readVersionRepository->findCurrentVersion();
        } catch (\Exception $e) {
            throw UpdateVersionsException::errorWhenRetrievingCurrentVersion($e);
        }

        if ($currentVersion === null) {
            throw UpdateVersionsException::cannotRetrieveCurrentVersion();
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
        } catch (UpdateNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw UpdateVersionsException::errorWhenRetrievingAvailableUpdates($e);
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
                throw UpdateVersionsException::errorWhenApplyingUpdate($version, $e->getMessage(), $e);
            }
        }
    }

    /**
     * Run post update actions
     *
     * @param string $currentVersion
     *
     * @throws UpdateVersionsException
     */
    private function runPostUpdate(string $currentVersion): void
    {
        $this->info("Running post update actions");

        try {
            $this->writeUpdateRepository->runPostUpdate($currentVersion);
        } catch (\Throwable $e) {
            throw UpdateVersionsException::errorWhenApplyingPostUpdate($e);
        }
    }
}

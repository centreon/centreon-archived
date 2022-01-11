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

namespace Core\Application\Platform\UseCase\FindInstallationStatus;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Platform\Repository\ReadPlatformRepositoryInterface;
use Core\Application\Platform\UseCase\FindInstallationStatus\FindInstallationStatusResponse;

class FindInstallationStatus
{
    use LoggerTrait;

    /**
     * @param ReadPlatformRepositoryInterface $repository
     */
    public function __construct(private ReadPlatformRepositoryInterface $repository)
    {
    }

    /**
     * @param FindInstallationStatusPresenterInterface $presenter
     */
    public function __invoke(FindInstallationStatusPresenterInterface $presenter): void
    {
        $this->info('check installation status');
        $isCentreonWebInstalled = $this->repository->isCentreonWebInstalled();
        $isCentreonWebUpgradeAvailable = $this->repository->isCentreonWebUpgradeAvailable();

        if ($isCentreonWebInstalled === false && $isCentreonWebUpgradeAvailable === false) {
            $this->critical(
                'something went wrong during your rpm installation, no centreon.conf.php or install dir was found'
            );
            $presenter->setResponseStatus(new ErrorResponse(
                _('centreon is not properly installed')
            ));
        }

        $presenter->present($this->createResponse(
            $isCentreonWebInstalled,
            $isCentreonWebUpgradeAvailable
        ));
    }

    /**
     * @param boolean $isCentreonWebInstalled
     * @param boolean $isCentreonWebUpgradeAvailable
     * @return FindInstallationStatusResponse
     */
    private function createResponse(
        bool $isCentreonWebInstalled,
        bool $isCentreonWebUpgradeAvailable
    ): FindInstallationStatusResponse {
        $response = new FindInstallationStatusResponse();
        $response->isCentreonWebInstalled = $isCentreonWebInstalled;
        $response->isCentreonWebUpgradeAvailable = $isCentreonWebUpgradeAvailable;

        return $response;
    }
}

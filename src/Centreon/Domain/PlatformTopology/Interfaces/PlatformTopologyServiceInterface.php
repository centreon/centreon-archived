<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\PlatformTopology\Interfaces;

use Centreon\Domain\Engine\EngineException;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\MonitoringServer\MonitoringServerException;
use Centreon\Domain\PlatformTopology\Platform;
use Centreon\Domain\PlatformTopology\PlatformConflictException;
use Centreon\Domain\PlatformTopology\PlatformException;
use Centreon\Domain\PlatformInformation\PlatformInformationException;
use Centreon\Domain\Repository\RepositoryException;

interface PlatformTopologyServiceInterface
{
    /**
     * Add new server
     *
     * @param Platform $platform
     * @throws PlatformConflictException
     * @throws MonitoringServerException
     * @throws EngineException
     * @throws PlatformException
     * @throws EntityNotFoundException
     * @throws PlatformInformationException
     * @throws RepositoryException
     */
    public function addPlatformToTopology(Platform $platform): void;

    /**
     * Get a topology with detailed nodes
     *
     * @return Platform[]
     * @throws PlatformException
     * @throws EntityNotFoundException
     */
    public function getPlatformTopology(): array;

    /**
     * Delete a platform
     *
     * @param integer $platformId
     * @throws PlatformException
     * @throws EntityNotFoundException
     */
    public function deletePlatform(int $platformId): void;
}

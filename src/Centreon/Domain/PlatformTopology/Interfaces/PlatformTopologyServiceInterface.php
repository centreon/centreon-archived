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

namespace Centreon\Domain\PlatformTopology\Interfaces;

use Centreon\Domain\Engine\EngineException;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\MonitoringServer\MonitoringServerException;
use Centreon\Domain\PlatformTopology\Exception\PlatformTopologyException;
use Centreon\Domain\PlatformInformation\Exception\PlatformInformationException;
use Centreon\Domain\Repository\PlatformTopologyRepositoryExceptionInterface;

interface PlatformTopologyServiceInterface
{
    /**
     * Add new server as a pending platform
     *
     * @param PlatformInterface $platformPending
     * @throws MonitoringServerException
     * @throws EngineException
     * @throws PlatformTopologyException
     * @throws EntityNotFoundException
     * @throws PlatformInformationException
     * @throws PlatformTopologyRepositoryExceptionInterface
     */
    public function addPendingPlatformToTopology(PlatformInterface $platformPending): void;

    /**
     * Get a topology with detailed nodes
     *
     * @return PlatformInterface[]
     * @throws PlatformTopologyException
     * @throws EntityNotFoundException
     */
    public function getPlatformTopology(): array;

    /**
     * Delete a Platform and allocate its children to top level platform.
     *
     * @param int $serverId
     * @throws PlatformTopologyException
     * @throws EntityNotFoundException
     */
    public function deletePlatformAndReallocateChildren(int $serverId): void;

    /**
     * Update a platform with given parameters.
     *
     * @param PlatformInterface $platform
     * @return void
     * @throws PlatformTopologyException
     */
    public function updatePlatformParameters(PlatformInterface $platform): void;

    /**
     * Find the top level platform of the topology.
     *
     * @return PlatformInterface|null
     * @throws PlatformTopologyException
     */
    public function findTopLevelPlatform(): ?PlatformInterface;
}

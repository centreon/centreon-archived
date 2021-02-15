<?php

/*
 *
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

use Centreon\Domain\PlatformTopology\Platform;

interface PlatformTopologyReadRepositoryInterface
{
    /**
     * Search for already registered servers using same name or address
     *
     * @param string $serverName
     * @return Platform|null
     * @throws \Exception
     */
    public function findPlatformByName(string $serverName): ?Platform;

    /**
     * Search for platform's ID using its address
     *
     * @param string $serverAddress
     * @return Platform|null
     * @throws \Exception
     */
    public function findPlatformByAddress(string $serverAddress): ?Platform;

    /**
     * Search for platform's name and address using its type
     *
     * @param string $serverType
     * @return Platform|null
     * @throws \Exception
     */
    public function findTopLevelPlatformByType(string $serverType): ?Platform;

    /**
     * Search for local platform's monitoring Id using its name
     *
     * @param string $serverName
     * @return Platform|null
     * @throws \Exception
     */
    public function findLocalMonitoringIdFromName(string $serverName): ?Platform;

    /**
     * Search for the global topology of the platform
     *
     * @return Platform[]
     */
    public function getPlatformTopology(): array;

    /**
     * Search for the address of a topology using its Id
     *
     * @param int $serverId
     * @return Platform|null
     */
    public function findPlatform(int $serverId): ?Platform;

    /**
     * Find the Top Level Platform.
     *
     * @return Platform|null
     */
    public function findTopLevelPlatform(): ?Platform;

    /**
     * Find the children Platforms of another Platform.
     *
     * @param int $parentId
     * @return Platform[]
     */
    public function findChildrenPlatformsByParentId(int $parentId): array;

    /**
     * find all the type 'remote' children of a Central
     *
     * @return Platform[]
     * @throws \Exception
     */
    public function findCentralRemoteChildren(): array;
}

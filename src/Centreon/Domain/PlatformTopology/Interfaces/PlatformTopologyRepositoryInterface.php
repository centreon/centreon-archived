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

use Centreon\Domain\PlatformTopology\PlatformTopology;

interface PlatformTopologyRepositoryInterface
{
    /**
     * Register a new platform to topology
     *
     * @param PlatformTopology $platformTopology
     */
    public function addPlatformToTopology(PlatformTopology $platformTopology): void;

    /**
     * Search for already registered servers using same name or address
     *
     * @param string $serverAddress
     * @param string $serverName
     * @return bool returns true if a server is already registered using same address or name
     */
    public function isPlatformAlreadyRegisteredInTopology(
        string $serverAddress,
        string $serverName
    ): bool;

    /**
     * Search for platform id using its address
     *
     * @param string $serverAddress
     * @return PlatformTopology|null
     */
    public function findPlatformTopologyByAddress(
        string $serverAddress
    ): ?PlatformTopology;

    /**
     * Search for platform id using its type
     *
     * @param string $serverType
     * @return PlatformTopology|null
     */
    public function findPlatformTopologyByType(
        string $serverType
    ): ?PlatformTopology;
}

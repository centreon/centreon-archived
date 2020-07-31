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
     *
     * @return array<mixed> returns already registered servers
     */
    public function findRegisteredPlatformsInTopology(
        string $serverAddress,
        string $serverName
    ): array;

    /**
     * Search for parent data
     *
     * @param string $serverAddress
     *
     * @return array<mixed> return nagios_server ID and parent_id found in DB
     */
    public function findParentInTopology(string $serverAddress): array;
}

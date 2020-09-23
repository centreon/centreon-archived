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

use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\PlatformTopology\PlatformTopology;
use Centreon\Domain\PlatformTopology\PlatformTopologyConflictException;
use Centreon\Domain\PlatformTopology\PlatformTopologyException;

interface PlatformTopologyServiceInterface
{
    /**
     * Add new server
     *
     * @param PlatformTopology $platformTopology
     * @throws PlatformTopologyConflictException
     * @throws PlatformTopologyException
     * @throws EntityNotFoundException
     */
    public function addPlatformToTopology(PlatformTopology $platformTopology): void;

    /**
     * Used when parent_address is null, to check if this type of platform is already registered
     *
     * @param string $type platform type to find
     * @throws PlatformTopologyConflictException
     */
    public function checkForAlreadyRegisteredPlatformType(string $type): void;

    /**
     * Search for platforms nagios_server ID and set it as serverId
     *
     * @param PlatformTopology $platformTopology
     * @param bool $isLocalhost
     * @throws PlatformTopologyConflictException
     */
    public function setServerNagiosId(PlatformTopology $platformTopology, bool $isLocalhost): void;

    /**
     * Search for already registered platforms using same name or address
     *
     * @param PlatformTopology $platformTopology
     * @throws PlatformTopologyConflictException
     */
    public function checkForAlreadyRegisteredSameNameOrAddress(PlatformTopology $platformTopology): void;

    /**
     * Search for parent platform ID in topology
     *
     * @param PlatformTopology $platformTopology
     * @return PlatformTopology|null
     * @throws EntityNotFoundException
     * @throws PlatformTopologyConflictException
     */
    public function searchForParentPlatformAndSetId(PlatformTopology $platformTopology): ?PlatformTopology;
}

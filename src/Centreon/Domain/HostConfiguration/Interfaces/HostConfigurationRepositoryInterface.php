<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\HostConfiguration\Interfaces;

use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\Repository\RepositoryException;

interface HostConfigurationRepositoryInterface
{
    /**
     * Add a host
     *
     * @param Host $host Host to add
     * @return int Returns the host id
     * @throws RepositoryException
     * @throws \Exception
     */
    public function addHost(Host $host): int;

    /**
     * Find a host.
     *
     * @param int $hostId Host Id to be found
     * @return Host|null Returns a host otherwise null
     * @throws \Throwable
     */
    public function findHost(int $hostId): ?Host;

    /**
     * Indicates if a hostname is already in use.
     *
     * @param string $hostName Hostname to be found
     * @return bool True if the hostname is already in use
     */
    public function hasHostWithSameName(string $hostName): bool;

    /**
     * Returns the number of hosts.
     *
     * @return int Number of hosts
     */
    public function getNumberOfHosts(): int;

    /**
     * Find all host macros for the host.
     *
     * @param int $hostId Id of the host
     * @param bool $isUsingInheritance Indicates whether to use inheritance to find host macros (FALSE by default)
     * @return array<HostMacro> List of host macros found
     * @throws \Throwable
     */
    public function findOnDemandHostMacros(int $hostId, bool $isUsingInheritance = false): array;
}

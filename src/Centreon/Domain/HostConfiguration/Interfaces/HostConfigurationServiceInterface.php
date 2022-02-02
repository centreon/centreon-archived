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

use Centreon\Domain\HostConfiguration\Exception\HostConfigurationServiceException;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\HostConfigurationException;
use Centreon\Domain\HostConfiguration\HostMacro;

interface HostConfigurationServiceInterface
{
    /**
     * Add a host.
     *
     * @param Host $host
     * @throws HostConfigurationServiceException
     */
    public function addHost(Host $host): void;

    /**
     * Find a host.
     *
     * @param int $hostId Host Id to be found
     * @return Host|null Returns a host otherwise null
     * @throws HostConfigurationException
     */
    public function findHost(int $hostId): ?Host;

    /**
     * Returns the number of host.
     *
     * @return int Number of host
     * @throws HostConfigurationException
     */
    public function getNumberOfHosts(): int;

    /**
     * Find host templates recursively.
     *
     * **The priority order of host templates is maintained!**
     *
     * @param Host $host Host for which we want to find all host templates recursively
     * @return Host[]
     * @throws HostConfigurationException
     */
    public function findHostTemplatesRecursively(Host $host): array;

    /**
     * Find the command of a host.
     * A recursive search will be performed in the inherited templates in the
     * case where the host does not have a command.
     *
     * @param int $hostId Host id
     * @return string|null Return the command if found
     * @throws HostConfigurationException
     */
    public function findCommandLine(int $hostId): ?string;

    /**
     * Find all host macros for the host.
     *
     * @param int $hostId Id of the host
     * @param bool $isUsingInheritance Indicates whether to use inheritance to find host macros (FALSE by default)
     * @return HostMacro[] List of host macros found
     * @throws HostConfigurationException
     */
    public function findOnDemandHostMacros(int $hostId, bool $isUsingInheritance = false): array;

    /**
     * Find all on-demand host macros needed for this command.
     *
     * @param int $hostId Host id
     * @param string $command Command to analyse
     * @return HostMacro[] List of host macros
     * @throws HostConfigurationException
     */
    public function findHostMacrosFromCommandLine(int $hostId, string $command): array;

    /**
     * Change the activation status of host.
     *
     * @param Host $host Host for which we want to change the activation status
     * @param bool $shouldBeActivated TRUE to activate a host
     * @throws HostConfigurationException
     */
    public function changeActivationStatus(Host $host, bool $shouldBeActivated): void;

    /**
     * Find host names already used by hosts.
     *
     * @param string[] $namesToCheck List of names to find
     * @return string[] Return the host names found
     * @throws HostConfigurationException
     */
    public function findHostNamesAlreadyUsed(array $namesToCheck): array;

    /**
     * Update a host.
     *
     * @param Host $host
     * @throws HostConfigurationServiceException
     */
    public function updateHost(Host $host): void;

    /**
     * Find host templates by host id (non recursive)
     *
     * **The priority order of host templates is maintained!**
     *
     * @param Host $host
     * @return Host[]
     */
    public function findHostTemplatesByHost(Host $host): array;

    /**
     * Find host templates by names
     *
     * @param string[] $names
     * @return Host[]
     */
    public function findHostTemplatesByNames(array $names): array;
}

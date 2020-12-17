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

namespace Centreon\Domain\HostConfiguration\Interfaces;

use Centreon\Domain\HostConfiguration\Model\HostGroup;

/**
 * This interface gathers all the reading operations on the repository.
 *
 * @package Centreon\Domain\HostConfiguration\Interfaces
 */
interface HostGroupReadRepositoryInterface
{
    /**
     * Find all host groups.
     *
     * @return HostGroup[]
     */
    public function findHostGroups(): array;

    /**
     * Indicates if a host group name is already in use.
     *
     * @param string $hgName Host group name to be found
     * @return bool True if the hostname is already in use
     */
    public function hasHostGroupWithSameName(string $hgName): bool;

    /**
     * Returns the number of host groups.
     *
     * @return int Number of hosts
     */
    public function getNumberOfHostGroups(): int;
}

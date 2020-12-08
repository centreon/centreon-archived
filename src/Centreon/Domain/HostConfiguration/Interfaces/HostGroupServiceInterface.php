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

use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\HostConfiguration\Exception\HostGroupException;

interface HostGroupServiceInterface
{

    /**
     * Find a host group.
     *
     * @param int $hgId Host Id to be found
     * @return array
     * @throws HostGroupException
     */
    public function findHostGroup(int $hgId): array;

    /**
     * Returns the number of host.
     *
     * @return int Number of host
     * @throws HostGroupException
     */
    public function getNumberOfHostGroups(): int;
    
    /**
     * Change the activation status of host.
     *
     * @param HostGroup $hg Host for which we want to change the activation status
     * @param bool $shouldBeActivated TRUE to activate a host
     * @throws HostGroupException
     */
    public function changeActivationStatus(HostGroup $hg, bool $shouldBeActivated): void;

    /**
     * Find host group names already used by hosts.
     *
     * @param string[] $namesToCheck List of names to find
     * @return string[] Return the host names found
     * @throws HostGroupException
     */
    public function findHostGroupNamesAlreadyUsed(array $namesToCheck): array;
}

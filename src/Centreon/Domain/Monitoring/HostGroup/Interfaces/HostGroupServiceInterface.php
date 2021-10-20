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

namespace Centreon\Domain\Monitoring\HostGroup\Interfaces;

use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Monitoring\HostGroup\HostGroupException;

interface HostGroupServiceInterface
{
    /**
     * Used to filter requests according to a contact.
     * If the filter is defined, all requests will use the ACL of the contact
     * to fetch data.
     *
     * @param mixed $contact Contact to use as a ACL filter
     * @return HostGroupServiceInterface
     * @throws \Exception
     */
    public function filterByContact($contact): HostGroupServiceInterface;

    /**
     * Retrieve hostgroups from their ids
     *
     * @param array<int, int> $hostGroupIds
     * @return HostGroup[]
     * @throws HostGroupException
     */
    public function findHostGroupsByIds(array $hostGroupIds): array;

    /**
     * Retrieve hostgroups from their names
     *
     * @param string[] $hostGroupNames
     * @return HostGroup[]
     * @throws HostGroupException
     */
    public function findHostGroupsByNames(array $hostGroupNames): array;
}

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

namespace Centreon\Domain\HostConfiguration\Interfaces\HostGroup;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\Repository\RepositoryException;

/**
 * This interface gathers all the reading operations on the host group repository.
 *
 * @package Centreon\Domain\HostConfiguration\Interfaces\HostGroup
 */
interface HostGroupReadRepositoryInterface
{
    /**
     * Find all host groups.
     *
     * @return HostGroup[]
     * @throws RepositoryException
     * @throws \Exception
     */
    public function findAll(): array;

    /**
     * Find all host groups by contact.
     *
     * @param ContactInterface $contact Contact related to host groups
     * @return HostGroup[]
     * @throws RepositoryException
     * @throws \Exception
     */
    public function findAllByContact(ContactInterface $contact): array;

    /**
     * Find a host group by id.
     *
     * @param int $hostGroupId Id of the host group to be found
     * @return HostGroup|null
     * @throws RepositoryException
     * @throws \Exception
     */
    public function findById(int $hostGroupId): ?HostGroup;

    /**
     * Find a host group by id and contact.
     *
     * @param int $hostGroupId Id of the host group to be found
     * @param ContactInterface $contact Contact related to host group
     * @return HostGroup|null
     * @throws RepositoryException
     * @throws \Exception
     */
    public function findByIdAndContact(int $hostGroupId, ContactInterface $contact): ?HostGroup;

    /**
     * Find host groups by name (for admin user).
     *
     * @param string[] $groupsName List of names of host groups to be found
     * @return HostGroup[]
     */
    public function findByNames(array $groupsName): array;

    /**
     * Find all host groups.
     *
     * @return HostGroup[]
     */
    public function findHostGroups(): array;
}

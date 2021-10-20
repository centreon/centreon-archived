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

namespace Centreon\Domain\Monitoring\ServiceGroup\Interfaces;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\ServiceGroup;

interface ServiceGroupRepositoryInterface
{
    /**
     * Retrieve servicegroups from their ids
     *
     * @param array<int, int> $serviceGroupIds
     * @return ServiceGroup[]
     */
    public function findServiceGroupsByIds(array $serviceGroupIds): array;

    /**
     * Retrieve servicegroups from their names
     *
     * @param string[] $serviceGroupNames
     * @return ServiceGroup[]
     */
    public function findServiceGroupsByNames(array $serviceGroupNames): array;

    /**
     * @param ContactInterface $contact
     * @return ServiceGroupRepositoryInterface
     */
    public function setContact(ContactInterface $contact): ServiceGroupRepositoryInterface;

    /**
     * Sets the access groups that will be used to filter.
     *
     * @param \Centreon\Domain\Security\AccessGroup[]|null $accessGroups
     * @return ServiceGroupRepositoryInterface
     */
    public function filterByAccessGroups(?array $accessGroups): ServiceGroupRepositoryInterface;
}

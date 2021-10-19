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

use Centreon\Domain\Monitoring\ServiceGroup;
use Centreon\Domain\Monitoring\ServiceGroup\ServiceGroupException;

interface ServiceGroupServiceInterface
{
    /**
     * Used to filter requests according to a contact.
     * If the filter is defined, all requests will use the ACL of the contact
     * to fetch data.
     *
     * @param mixed $contact Contact to use as a ACL filter
     * @return ServiceGroupServiceInterface
     * @throws \Exception
     */
    public function filterByContact($contact): ServiceGroupServiceInterface;

    /**
     * Retrieve servicegroups from their ids
     *
     * @param array $serviceGroupIds
     * @return ServiceGroup[]
     * @throws ServiceGroupException
     */
    public function findServiceGroupsByIds(array $serviceGroupIds): array;

    /**
     * Retrieve servicegroups from their ids
     *
     * @param array $serviceGroupNames
     * @return ServiceGroup[]
     * @throws ServiceGroupException
     */
    public function findServiceGroupsByNames(array $serviceGroupNames): array;
}

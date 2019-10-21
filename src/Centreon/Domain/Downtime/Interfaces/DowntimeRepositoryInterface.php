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

namespace Centreon\Domain\Downtime\Interfaces;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Infrastructure\Downtime\DowntimeRepositoryRDB;

interface DowntimeRepositoryInterface
{

    /**
     * @param ContactInterface $contact
     * @return DowntimeRepositoryInterface
     */
    public function setContact(ContactInterface $contact): self;

    /**
     * Sets the access groups that will be used to filter downtime.
     *
     * @param AccessGroup[]|null $accessGroups
     * @return self
     */
    public function filterByAccessGroups(?array $accessGroups): DowntimeRepositoryInterface;

    /**
     * Find downtime of all hosts.
     *
     * @return Downtime[]
     */
    public function findHostDowntime(): array;
}
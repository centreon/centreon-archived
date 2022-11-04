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

use Centreon\Domain\Downtime\Downtime;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;

interface DowntimeRepositoryInterface
{
    /**
     * Sets the access groups that will be used to filter downtime.
     *
     * @param AccessGroup[] $accessGroups
     * @return self
     */
    public function forAccessGroups(array $accessGroups): DowntimeRepositoryInterface;

    /**
     * Find downtime of all hosts **taking into account** the ACLs of user.
     *
     * @return Downtime[]
     * @throws \Exception
     */
    public function findHostDowntimesForNonAdminUser(): array;

    /**
     * Find downtime of all hosts **without taking into account** the ACLs of user.
     *
     * @return Downtime[]
     * @throws \Exception
     */
    public function findHostDowntimesForAdminUser(): array;

    /**
     * Find one downtime linked to a host **without taking into account** the ACLs of user.
     *
     * @param int $downtimeId Downtime id
     * @return Downtime|null Return NULL if the downtime has not been found
     * @throws \Exception
     */
    public function findOneDowntimeForAdminUser(int $downtimeId): ?Downtime;

    /**
     * Find one downtime linked to a host **taking into account** the ACLs of user.
     *
     * @param int $downtimeId Downtime id
     * @return Downtime|null Return NULL if the downtime has not been found
     * @throws \Exception
     */
    public function findOneDowntimeForNonAdminUser(int $downtimeId): ?Downtime;

    /**
     * Find all downtimes **without taking into account** the ACLs of user.
     *
     * @return Downtime[] Return the downtimes found
     * @throws \Exception
     */
    public function findDowntimesForAdminUser(): array;

    /**
     * Find all downtimes **taking into account** the ACLs of user.
     *
     * @return Downtime[] Return the downtimes found
     * @throws \Exception
     */
    public function findDowntimesForNonAdminUser(): array;

    /**
     * Find all downtimes for a host **taking into account** the ACLs of user.
     *
     * @param int $hostId Host id for which we want to find downtimes
     * @param bool $withServices Display downtimes of host-related services also
     * @return Downtime[]
     * @throws \Exception
     */
    public function findDowntimesByHostForAdminUser(int $hostId, bool $withServices): array;

    /**
     * Find all downtimes for a host **without taking into account** the ACLs of user.
     *
     * @param int $hostId Host id for which we want to find downtimes
     * @param bool $withServices Display downtimes of host-related services also
     * @return Downtime[]
     * @throws \Exception
     */
    public function findDowntimesByHostForNonAdminUser(int $hostId, bool $withServices): array;

    /**
     * Find all downtimes of all services **taking into account** the ACLs of user.
     *
     * @return Downtime[]
     * @throws \Exception
     */
    public function findServicesDowntimesForNonAdminUser(): array;

    /**
     * Find all downtimes of all services **without taking into account** the ACLs of user.
     *
     * @return Downtime[]
     * @throws \Exception
     */
    public function findServicesDowntimesForAdminUser(): array;

    /**
     * Find all downtimes for a service (linked to a host) **taking into account** the ACLs of user.
     *
     * @param int $hostId Host id linked to this service
     * @param int $serviceId Service id for which we want to find downtimes
     * @return Downtime[]
     * @throws \Exception
     */
    public function findDowntimesByServiceForNonAdminUser(int $hostId, int $serviceId): array;

    /**
     * Find all downtimes for a service (linked to a host) **without taking into account** the ACLs of user.
     *
     * @param int $hostId Host id linked to this service
     * @param int $serviceId Service id for which we want to find downtimes
     * @return Downtime[]
     * @throws \Exception
     */
    public function findDowntimesByServiceForAdminUser(int $hostId, int $serviceId): array;
}

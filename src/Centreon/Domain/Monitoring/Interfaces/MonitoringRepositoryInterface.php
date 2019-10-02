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

namespace Centreon\Domain\Monitoring\Interfaces;

use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Infrastructure\Monitoring\MonitoringRepositoryRDB;

interface MonitoringRepositoryInterface
{
    /**
     * Sets the access groups that will be used to filter services and the host.
     *
     * @param AccessGroup[]|null $accessGroups
     * @return self
     */
    public function filterByAccessGroups(?array $accessGroups): self;

    /**
     * Find all real time hosts according to access group.
     *
     * @return Host[]
     * @throws \Exception
     */
    public function findHosts(): array;

    /**
     * Find all services grouped by host groups
     *
     * @return HostGroup[]
     * @throws \Exception
     */
    public function findHostGroups(): array;

    /**
     * Find one host based on its id and according to ACL.
     *
     * @param int $hostId Id of the host to be found
     * @return Host|null
     * @throws \Exception
     */
    public function findOneHost(int $hostId): ?Host;

    /**
     * Find one service based on its id and according to ACL.
     *
     * @param int $hostId Host id of the service
     * @param int $serviceId Service Id
     * @return Service|null
     * @throws \Exception
     */
    public function findOneService(int $hostId, int $serviceId): ?Service;

    /**
     * Find all services grouped by service groups
     *
     * @return \Servicegroup[]
     * @throws \Exception
     */
    public function findServiceGroups(): array;

    /**
     * Find all real time services according to access group.
     *
     * @return Service[]
     * @throws \Exception
     */
    public function findServices(): array;

    /**
     * Retrieve all real time services according to ACL of contact and host id
     *
     * @param int $hostId Host ID for which we want to find services
     * @return Service[]
     * @throws \Exception
     */
    public function findServicesByHost(int $hostId): array;

    /**
     * Find services according to the host id and service ids given
     *
     * @param int $hostId Host id
     * @param int[] $serviceIds Service Ids
     * @return Service[]
     * @throws \Exception
     */
    public function findSelectedServicesByHost(int $hostId, array $serviceIds): array;

    /**
     * Finds services from a list of hosts.
     *
     * @param array $hostIds List of host for which we want to get services
     * @return array Return a list of services indexed by host
     * [host_id => Service[], ...]
     * @throws \Exception
     */
    public function findServicesOnMultipleHosts(array $hostIds): array;

    /**
     * Indicates whether the contact is an admin or not.
     *
     * @param bool $isAdmin Set TRUE if the contact is an admin
     * @return self
     */
    public function setAdmin(bool $isAdmin): self;
}

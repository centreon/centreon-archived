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

namespace Centreon\Domain\Monitoring\Interfaces;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Monitoring\ServiceGroup;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Acknowledgement\Acknowledgement;

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
     * Find the hosts of the hosts groups ids given.
     *
     * @param int[] $hostsGroupsIds List of hosts groups Ids for which we want to retrieve the hosts
     * @return array<int, array<int, Host>> [hostGroupId => hostId,...]
     * @throws \Exception
     */
    public function findHostsByHostsGroups(array $hostsGroupsIds): array;

    /**
     * Find the hosts of the services groups ids given.
     *
     * @param int[] $servicesGroupsIds List of services groups Ids for which we want to retrieve the hosts
     * @return array<int, array<int, Host>> [serviceGroupId => hostId,...]
     * @throws \Exception
     */
    public function findHostsByServiceGroups(array $servicesGroupsIds): array;

    /**
     * Find all hostgroups with option to provide host id
     *
     * @param int $hostId Id of host to filter hostgroups by
     * @return HostGroup[]
     */
    public function findHostGroups(?int $hostId): array;

    /**
     * Find one host based on its id and according to ACL.
     *
     * @param int $hostId Id of the host to be found
     * @return Host|null
     * @throws \Exception
     */
    public function findOneHost(int $hostId): ?Host;

    /**
     * Find all hosts from an array of hostIds for a non admin user
     *
     * @param int[] $hostIds
     * @return Host[]
     */
    public function findHostsByIdsForNonAdminUser(array $hostIds): array;

    /**
     * Find all hosts from an array of hostIds for an admin user
     *
     * @param int[] $hostIds
     * @return Host[]
     */
    public function findHostsByIdsForAdminUser(array $hostIds): array;

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
     * Find one service based on its id and according to ACL.
     *
     * @param string $serviceDescription
     * @return Service|null
     * @throws \Exception
     */
    public function findOneServiceByDescription(string $serviceDescription): ?Service;

    /**
     * Find all services from an array of serviceIds for a non admin user
     *
     * @param array<string, mixed> $serviceIds
     * @return Service[]
     */
    public function findServicesByIdsForNonAdminUser(array $serviceIds): array;

    /**
     * Find all services from an array of serviceIds for an admin user
     *
     * @param array<string, mixed> $serviceIds
     * @return Service[]
     */
    public function findServicesByIdsForAdminUser(array $serviceIds): array;

    /**
     * Find all services grouped by service groups
     *
     * @return Servicegroup[]
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
     * using request parameters (search, sort, pagination)
     *
     * @param int $hostId Host ID for which we want to find services
     * @return Service[]
     * @throws \Exception
     */
    public function findServicesByHostWithRequestParameters(int $hostId): array;

    /**
     * Retrieve all real time services according to ACL of contact and host id
     * without request parameters (no search, no sort, no pagination)
     *
     * @param int $hostId Host ID for which we want to find services
     * @return Service[]
     * @throws \Exception
     */
    public function findServicesByHostWithoutRequestParameters(int $hostId): array;

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
     * @param int[] $hostIds List of host for which we want to get services
     * @return array<int, array<int, mixed>> Return a list of services indexed by host
     */
    public function findServicesByHosts(array $hostIds): array;

    /**
     * @param ContactInterface $contact
     * @return MonitoringRepositoryInterface
     */
    public function setContact(ContactInterface $contact): self;

    /**
     * @param int[] $serviceGroupIds
     * @return array<int, array<int, mixed>>
     * @throws \Exception
     */
    public function findServicesByServiceGroups(array $serviceGroupIds): array;

    /**
     * @param int $hostId
     * @param int $serviceId
     * @return array<int, ServiceGroup>
     */
    public function findServiceGroupsByHostAndService(int $hostId, int $serviceId): array;

    /**
     * Find downtimes for host or service
     * @param int $hostId
     * @param int $serviceId
     * @return Downtime[]
     */
    public function findDowntimes(int $hostId, int $serviceId): array;

    /**
     * Find acknowledgements for host or service
     * @param int $hostId
     * @param int $serviceId
     * @return Acknowledgement[]
     */
    public function findAcknowledgements(int $hostId, int $serviceId): array;
}

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

namespace Centreon\Domain\Downtime\Interfaces;

use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;

interface DowntimeServiceInterface
{
    /**
     * Used to filter requests according to a contact.
     * If the filter is defined, all requests will use the ACL of the contact
     * to fetch data.
     *
     * @param mixed $contact Contact to use as a ACL filter
     * @return self
     * @throws \Exception
     */
    public function filterByContact($contact): self;

    /**
     * Find downtime of all hosts.
     *
     * @return Downtime[]
     * @throws \Exception
     */
    public function findHostDowntimes(): array;

    /**
     * Find one downtime linked to a host.
     *
     * @param int $downtimeId Downtime id
     * @return Downtime|null Return NULL if the downtime has not been found
     * @throws \Exception
     */
    public function findOneDowntime(int $downtimeId): ?Downtime;

    /**
     * Find all downtimes.
     *
     * @return Downtime[]
     * @throws \Exception
     */
    public function findDowntimes(): array;

    /**
     * Find all downtimes linked to a host.
     *
     * @param int $hostId Host id for which we want to find host
     * @param bool $withServices Display downtimes of host-related services also
     * @return Downtime[]
     * @throws \Exception
     */
    public function findDowntimesByHost(int $hostId, bool $withServices): array;

    /**
     * Find downtime of all services.
     *
     * @return Downtime[]
     * @throws \Exception
     */
    public function findServicesDowntimes(): array;

    /**
     * Find all downtimes for a service (linked to a host).
     *
     * @param int $hostId Host id linked to this service
     * @param int $serviceId Service id for which we want to find downtimes
     * @return Downtime[]
     * @throws \Exception
     */
    public function findDowntimesByService(int $hostId, int $serviceId): array;

    /**
     * Find all downtimes for a metaservice.
     *
     * @param int $metaId ID of the metaservice
     * @return Downtime[]
     * @throws \Exception
     */
    public function findDowntimesByMetaService(int $metaId): array;

    /**
     * Add a downtime on multiple hosts.
     *
     * @param Downtime $downtime Downtime to add
     * @param Host $host Host to add a downtime
     * @throws \Exception
     */
    public function addHostDowntime(Downtime $downtime, Host $host): void;

    /**
     * Add a downtime on multiple services.
     *
     * @param Downtime $downtime Downtime to add for each service
     * @param Service $service Service (the host property of each service must to be correctly defined)
     * @throws \Exception
     */
    public function addServiceDowntime(Downtime $downtime, Service $service): void;

    /**
     * Cancel one downtime.
     *
     * @param int $downtimeId Downtime id to cancel
     * @param Host $host Downtime-related host
     * @throws \Exception
     */
    public function cancelDowntime(int $downtimeId, Host $host): void;

    /**
     * @param ResourceEntity $resource
     * @param Downtime $downtime
     * @throws \Exception
     */
    public function addResourceDowntime(ResourceEntity $resource, Downtime $downtime): void;
}

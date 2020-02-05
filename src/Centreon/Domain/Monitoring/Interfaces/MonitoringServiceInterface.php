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

use Centreon\Domain\Contact\Interfaces\ContactFilterInterface;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\HostGroup;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\ServiceGroup;

interface MonitoringServiceInterface extends ContactFilterInterface
{
    /**
     * Find all hosts.
     *
     * @param bool $withServices Indicates whether hosts must be completed with their services
     * @return Host[]
     * @throws \Exception
     */
    public function findHosts(bool $withServices = false): array;

    /**
     * Find all host groups.
     *
     * @param bool $withHosts Indicates whether hosts groups must be completed with their hosts
     * @param bool $withServices Indicates whether hosts must be completed with their services
     * @return HostGroup[]
     * @throws \Exception
     */
    public function findHostGroups(bool $withHosts = false, bool $withServices = false): array;

    /**
     * Find a host based on his ID.
     *
     * @param int $hostId Id of the host to be found
     * @return Host|null
     * @throws \Exception
     */
    public function findOneHost(int $hostId): ?Host;

    /**
     * Find a service based on his ID and a host ID.
     *
     * @param int $hostId Host ID for which the service belongs
     * @param int $serviceId Service ID to find
     * @return Service|null
     * @throws \Exception
     */
    public function findOneService(int $hostId, int $serviceId): ?Service;

    /**
     * Find all service groups.
     *
     * @param bool $withHosts Indicates whether service groups must be completed with their hosts
     * @param bool $withServices Indicates whether hosts must be completed with their services
     * @return ServiceGroup[]
     * @throws \Exception
     */
    public function findServiceGroups(bool $withHosts = false, bool $withServices = false): array;

    /**
     * Find all services.
     *
     * @return Service[]
     * @throws \Exception
     */
    public function findServices(): array;

    /**
     * Find all services belonging to the host.
     *
     * @param int $hostId
     * @throws \Exception
     * @return Service[]
     */
    public function findServicesByHost(int $hostId): array;

    /**
     * Indicates whether a host exists.
     *
     * @param int $hostId Host id to find
     * @return bool
     * @throws \Exception
     */
    public function isHostExists(int $hostId): bool;
}

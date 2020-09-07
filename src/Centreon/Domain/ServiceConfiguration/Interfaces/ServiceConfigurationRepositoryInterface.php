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

namespace Centreon\Domain\ServiceConfiguration\Interfaces;

use Centreon\Domain\AccessControlList\Interfaces\AccessControlListRepositoryInterface;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\ServiceConfiguration\HostTemplateService;
use Centreon\Domain\ServiceConfiguration\Service;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;

interface ServiceConfigurationRepositoryInterface extends AccessControlListRepositoryInterface
{
    /**
     * Add services to host.
     *
     * @param Host $host Host for which we want to add services
     * @param Service[] $servicesToBeCreated Services to be created
     * @throws \Throwable
     */
    public function addServicesToHost(Host $host, array $servicesToBeCreated): void;

    /**
     * Find all service macros for the service.
     *
     * @param int $serviceId Id of the service
     * @param bool $isUsingInheritance Indicates whether to use inheritance to find service macros (FALSE by default)
     * @return array<ServiceMacro> List of service macros found
     * @throws \Throwable
     */
    public function findOnDemandServiceMacros(int $serviceId, bool $isUsingInheritance = false): array;

    /**
     * Find the command of a service.
     *
     * A recursive search will be performed in the inherited templates in the
     * case where the service does not have a command.
     *
     * @param int $serviceId Service id
     * @return string|null Return the command if found
     * @throws \Throwable
     */
    public function findCommandLine(int $serviceId): ?string;

    /**
     * Find all service templates associated with the given host templates.
     *
     * @param int[] $hostTemplateIds Ids of the host templates for which we want to find the service templates
     * @return HostTemplateService[]
     * @throws \Exception
     */
    public function findHostTemplateServices(array $hostTemplateIds): array;

    /**
     * Find a service.
     *
     * @param int $serviceId Service id
     * @return Service|null
     * @throws \Exception
     */
    public function findService(int $serviceId): ?Service;

    /**
     * Find all services associated to host.
     *
     * @param Host $host Host for which we want to find services
     * @return Service[]
     * @throws \Exception
     */
    public function findServicesByHost(Host $host): array;
}

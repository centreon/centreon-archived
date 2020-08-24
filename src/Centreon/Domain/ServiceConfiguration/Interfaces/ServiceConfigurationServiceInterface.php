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

use Centreon\Domain\Contact\Interfaces\ContactFilterInterface;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\HostGroup;
use Centreon\Domain\ServiceConfiguration\HostTemplateService;
use Centreon\Domain\ServiceConfiguration\Service;
use Centreon\Domain\ServiceConfiguration\ServiceConfigurationException;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;

interface ServiceConfigurationServiceInterface extends ContactFilterInterface
{
    /**
     * Find all service templates associated with the given host templates.
     *
     * @param array $hostTemplateIds Ids of the host templates for which we want to find the service templates
     * @return HostTemplateService[]
     * @throws ServiceConfigurationException
     */
    public function findHostTemplateServices(array $hostTemplateIds): array;

    /**
     * Find all service macros for the service.
     *
     * @param int $serviceId Id of the service
     * @param bool $isUsingInheritance Indicates whether to use inheritance to find service macros (FALSE by default)
     * @return ServiceMacro[] List of service macros found
     * @throws ServiceConfigurationException
     */
    public function findOnDemandServiceMacros(int $serviceId, bool $isUsingInheritance = false): array;

    /**
     * Find a service.
     *
     * @param int $serviceId Service id
     * @return Service|null
     * @throws ServiceConfigurationException
     */
    public function findService(int $serviceId): ?Service;

    /**
     * Find all services associated to host.
     *
     * @param Host $host Host for which we want to find services
     * @return Service[]
     * @throws ServiceConfigurationException
     */
    public function findServicesByHost(Host $host): array;

    /**
     * Find all services associated to host groups.
     *
     * @param HostGroup[] $hostGroups Host groups for which we want to find services
     * @return Service[]
     * @throws ServiceConfigurationException
     */
    public function findServicesByHostGroups(array $hostGroups): array;

    /**
     * Find all on-demand service macros of type password needed for this command.
     *
     * @param int $serviceId Service id
     * @param string $command Command to analyse
     * @return ServiceMacro[] List of service macros of type password
     * @throws ServiceConfigurationException
     */
    public function findServiceMacrosPassword(int $serviceId, string $command): array;

    /**
     * Find the command of a service.
     *
     * @param int $serviceId Service id
     * @return string|null Return the command if found
     * @throws ServiceConfigurationException
     */
    public function findCommandLine(int $serviceId): ?string;
}

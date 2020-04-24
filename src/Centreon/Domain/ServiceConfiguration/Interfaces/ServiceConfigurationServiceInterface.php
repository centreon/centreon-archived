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

use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\ServiceConfiguration\Service;
use Centreon\Domain\ServiceConfiguration\ServiceConfigurationException;
use Centreon\Domain\ServiceConfiguration\ServiceConfigurationService;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;

interface ServiceConfigurationServiceInterface
{
    /**
     * Find all service macros for the service.
     *
     * @param int $serviceId Id of the service
     * @return ServiceMacro[] List of service macros found
     * @throws ServiceConfigurationException
     * @throws RepositoryException
     */
    public function findOnDemandServiceMacros(int $serviceId): array;

    /**
     * Find a service.
     *
     * @param int $serviceId Service id
     * @return Service|null
     * @throws RepositoryException
     * @throws ServiceConfigurationException
     */
    public function findService(int $serviceId): ?Service;

    /**
     * Find all on-demand service macros of type password needed for this command.
     *
     * @param int $serviceId Service id
     * @param string $command Command to analyse
     * @return ServiceMacro[] List of service macros of type password
     * @throws ServiceConfigurationException
     * @throws RepositoryException
     */
    public function findServiceMacrosPassword(int $serviceId, string $command): array;

    /**
     * Find the command of a service.
     *
     * @param int $serviceId Service id
     * @return string|null Return the command if found
     * @throws RepositoryException
     * @throws ServiceConfigurationException
     */
    public function findCommandLine(int $serviceId): ?string;
}

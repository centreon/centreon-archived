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

namespace Centreon\Domain\ServiceConfiguration;

use Centreon\Domain\HostConfiguration\HostConfigurationException;
use Centreon\Domain\HostConfiguration\HostConfigurationService;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationServiceInterface;

class ServiceConfigurationService implements ServiceConfigurationServiceInterface
{
    /**
     * @var ServiceConfigurationRepositoryInterface
     */
    private $serviceRepository;
    /**
     * @var HostConfigurationService
     */
    private $hostService;

    /**
     * ServiceConfigurationService constructor.
     *
     * @param ServiceConfigurationRepositoryInterface $serviceConfigurationRepository
     * @param HostConfigurationService $hostConfigurationService
     */
    public function __construct(
        ServiceConfigurationRepositoryInterface $serviceConfigurationRepository,
        HostConfigurationService $hostConfigurationService
    ) {
        $this->serviceRepository = $serviceConfigurationRepository;
        $this->hostService = $hostConfigurationService;
    }

    /**
     * @inheritDoc
     */
    public function findService(int $serviceId): ?Service
    {
        try {
            return $this->serviceRepository->findService($serviceId);
        } catch (RepositoryException $ex) {
            throw $ex;
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException('Error while searching for the service', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findCommandLine(int $serviceId): ?string
    {
        try {
            return $this->serviceRepository->findCommandLine($serviceId);
        } catch (RepositoryException $ex) {
            throw $ex;
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException('Error while searching for the command of service', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findOnDemandServiceMacros(int $serviceId): array
    {
        try {
            return $this->serviceRepository->findOnDemandServiceMacros($serviceId);
        } catch (RepositoryException $ex) {
            throw $ex;
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException('Error while searching for the on-demand service macros', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findServiceMacrosPassword(int $serviceId, string $command): array
    {
        $serviceMacrosPassword = [];
        // If contains on-demand service macros
        if (strpos($command, '$_SERVICE') !== false) {
            $onDemandServiceMacros = $this->findOnDemandServiceMacros($serviceId);
            foreach ($onDemandServiceMacros as $serviceMacro) {
                if ($serviceMacro->isPassword()) {
                    $serviceMacrosPassword[] = $serviceMacro;
                }
            }
        }
        return $serviceMacrosPassword;
    }
}

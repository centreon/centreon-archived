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

use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\HostGroup;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationServiceInterface;

class ServiceConfigurationService extends AbstractCentreonService implements ServiceConfigurationServiceInterface
{
    /**
     * @var ServiceConfigurationRepositoryInterface
     */
    private $serviceRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * ServiceConfigurationService constructor.
     *
     * @param ServiceConfigurationRepositoryInterface $serviceConfigurationRepository
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        ServiceConfigurationRepositoryInterface $serviceConfigurationRepository,
        AccessGroupRepositoryInterface $accessGroupRepository
    ) {
        $this->serviceRepository = $serviceConfigurationRepository;
        $this->accessGroupRepository = $accessGroupRepository;
    }

    /**
     * {@inheritDoc}
     * @return ServiceConfigurationServiceInterface
     */
    public function filterByContact($contact): self
    {
        parent::filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);
        $this->serviceRepository
            ->setContact($contact)
            ->filterByAccessGroups($accessGroups);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findHostTemplateServices(array $hostTemplateIds): array
    {
        try {
            return $this->serviceRepository->findHostTemplateServices($hostTemplateIds);
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException('', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findService(int $serviceId): ?Service
    {
        try {
            return $this->serviceRepository->findService($serviceId);
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException(_('Error while searching for the service'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findServicesByHost(Host $host): array
    {
        try {
            return $this->serviceRepository->findServicesByHost($host);
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException(_('Error when searching for services by host'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findServicesByHostGroups(array $hostGroups): array
    {
        try {
            foreach ($hostGroups as $hostGroup) {
                if (!($hostGroup instanceof HostGroup)) {
                    throw new \InvalidArgumentException(_('One of the expected elements is not a host group object'));
                }
            }
            return $this->serviceRepository->findServicesByHostGroups($hostGroups);
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException(_('Error when searching for services by host groups'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findCommandLine(int $serviceId): ?string
    {
        try {
            return $this->serviceRepository->findCommandLine($serviceId);
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException(_('Error while searching for the command of service'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findOnDemandServiceMacros(int $serviceId, bool $isUsingInheritance = false): array
    {
        try {
            return $this->serviceRepository->findOnDemandServiceMacros($serviceId, $isUsingInheritance);
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException(_('Error while searching for the service macros'), 0, $ex);
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
            $onDemandServiceMacros = $this->findOnDemandServiceMacros($serviceId, true);
            foreach ($onDemandServiceMacros as $serviceMacro) {
                if ($serviceMacro->isPassword()) {
                    $serviceMacrosPassword[] = $serviceMacro;
                }
            }
        }
        return $serviceMacrosPassword;
    }
}

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

namespace Centreon\Domain\HostConfiguration;

use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationServiceInterface;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationServiceInterface;
use Centreon\Domain\ServiceConfiguration\Service;

class HostConfigurationService implements HostConfigurationServiceInterface
{
    /**
     * @var HostConfigurationRepositoryInterface
     */
    private $hostRepository;
    /**
     * @var ServiceConfigurationServiceInterface
     */
    private $serviceConfiguration;
    /**
     * @var EngineConfigurationServiceInterface
     */
    private $engineConfigurationService;

    /**
     * HostConfigurationService constructor.
     *
     * @param HostConfigurationRepositoryInterface $hostRepository
     * @param ServiceConfigurationServiceInterface $serviceConfiguration
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     */
    public function __construct(
        HostConfigurationRepositoryInterface $hostRepository,
        ServiceConfigurationServiceInterface $serviceConfiguration,
        EngineConfigurationServiceInterface $engineConfigurationService
    ) {
        $this->hostRepository = $hostRepository;
        $this->serviceConfiguration = $serviceConfiguration;
        $this->engineConfigurationService = $engineConfigurationService;
    }

    /**
     * @inheritDoc
     */
    public function addHost(Host $host): int
    {
        if (empty($host->getName())) {
            throw new HostConfigurationException(_('Host name can not be empty'));
        }
        try {
            $hasHostWithSameName = $this->hostRepository->hasHostWithSameName($host->getName());
            if ($hasHostWithSameName) {
                throw new HostConfigurationException(_('Host name already exists'));
            }
            if ($host->getExtendedHost() === null) {
                $host->setExtendedHost(new ExtendedHost());
            }
            return $this->hostRepository->addHost($host);
        } catch (HostConfigurationException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new HostConfigurationException(_('Error while creation of host'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function applyServices(Host $host): void
    {
        $this->hostRepository->findAndAddHostTemplates($host);
        if (empty($host->getTemplates())) {
            return;
        }
        /**
         * To avoid defining a service description with illegal characters,
         * we retrieve the engine configuration to retrieve the list of these characters.
         */
        $engineConfiguration = $this->engineConfigurationService->findEngineConfigurationByHost($host);
        if ($engineConfiguration === null) {
            throw new HostConfigurationException(_('Impossible to find the Engine configuration'));
        }
        $hostTemplateIds = [];

        /**
         * Find all host templates recursively and copy their id into the given list.
         *
         * @param Host $host Host for which we will find all host template.
         * @param $hostTemplateIds
         */
        $extractHostTemplateIdsFromHost =
            function (Host $host, &$hostTemplateIds) use (&$extractHostTemplateIdsFromHost): void {
                foreach ($host->getTemplates() as $hostTemplate) {
                    $hostTemplateIds[] = $hostTemplate->getId();
                    if (!empty($hostTemplate->getTemplates())) {
                        // The recursive call here allow you to keep the priority orders of the host templates
                        $extractHostTemplateIdsFromHost($hostTemplate, $hostTemplateIds);
                    }
                }
            };
        $extractHostTemplateIdsFromHost($host, $hostTemplateIds);

        /**
         * First, we will search for services already associated with the host to avoid creating a new one with
         * same service description.
         */
        $serviceAlreadyExists = [];//$this->serviceConfiguration->findServicesByHost($host);

        /**
         * Then, we memorize the alias of service.
         * The service description is based on the alias of service template when it was created.
         */
        $serviceAliasAlreadyUsed = [];
        foreach ($serviceAlreadyExists as $service) {
            $serviceAliasAlreadyUsed[] = $service->getDescription();
        }

        /**
         * Then, we will search for all service templates associated with the host templates
         */
        $hostTemplateServices = $this->serviceConfiguration->findHostTemplateServices($hostTemplateIds);

        /**
         * @param int $hostTemplateId Host template id for which we want to find the services templates
         * @return Service[]
         */
        $findServiceTemplatesByHostTemplate = function (int $hostTemplateId) use ($hostTemplateServices): array {
            $serviceTemplates = [];
            foreach ($hostTemplateServices as $hostTemplateService) {
                if ($hostTemplateService->getHostTemplate()->getId() === $hostTemplateId) {
                    $serviceTemplates[] = $hostTemplateService->getServiceTemplate();
                }
            }
            return $serviceTemplates;
        };

        $servicesToBeCreated = [];

        /**
         * Then, we set aside the services to be created.
         * We must not have two services with the same description (alias of the service model).
         * The priority order is defined by the list of host templates.
         */
        foreach ($hostTemplateIds as $hostTemplateId) {
            $serviceTemplates = $findServiceTemplatesByHostTemplate($hostTemplateId);
            foreach ($serviceTemplates as $serviceTemplate) {
                if (!in_array($serviceTemplate->getAlias(), $serviceAliasAlreadyUsed)) {
                    $serviceAliasAlreadyUsed[] = $serviceTemplate->getAlias();
                    $serviceDescription = EngineConfiguration::removeIllegalCharacters(
                        $serviceTemplate->getAlias(),
                        $engineConfiguration->getIllegalObjectNameCharacters()
                    );
                    if (empty($serviceDescription)) {
                        continue;
                    }
                    $serviceToBeCreated = (new Service())
                        ->setServiceType(Service::TYPE_SERVICE)
                        ->setTemplateId($serviceTemplate->getId())
                        ->setDescription($serviceDescription)
                        ->setActivated(true);
                    $servicesToBeCreated[] = $serviceToBeCreated;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function findHost(int $hostId): ?Host
    {
        try {
            return $this->hostRepository->findHost($hostId);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error while searching for the host'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function getNumberOfHosts(): int
    {
        try {
            return $this->hostRepository->getNumberOfHosts();
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error while searching for the number of host'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findOnDemandHostMacros(int $hostId, bool $isUsingInheritance = false): array
    {
        try {
            return $this->hostRepository->findOnDemandHostMacros($hostId, $isUsingInheritance);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error while searching for the host macros'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findHostGroups(Host $host): array
    {
        try {
            return $this->hostRepository->findHostGroups($host);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error when searching for all hostgroups'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findHostMacrosPassword(int $hostId, string $command): array
    {
        $hostMacrosPassword = [];
        // If contains on-demand host macros
        if (strpos($command, '$_HOST') !== false) {
            $onDemandHostMacros = $this->findOnDemandHostMacros($hostId, true);
            foreach ($onDemandHostMacros as $hostMacro) {
                if ($hostMacro->isPassword()) {
                    $hostMacrosPassword[] = $hostMacro;
                }
            }
        }
        return $hostMacrosPassword;
    }
}

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

namespace Centreon\Domain\ServiceConfiguration;

use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationServiceInterface;
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
     * @var EngineConfigurationServiceInterface
     */
    private $engineConfigurationService;
    /**
     * @var HostConfigurationServiceInterface
     */
    private $hostConfigurationService;

    /**
     * ServiceConfigurationService constructor.
     *
     * @param ServiceConfigurationRepositoryInterface $serviceConfigurationRepository
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param HostConfigurationServiceInterface $hostConfigurationService
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     */
    public function __construct(
        ServiceConfigurationRepositoryInterface $serviceConfigurationRepository,
        AccessGroupRepositoryInterface $accessGroupRepository,
        HostConfigurationServiceInterface $hostConfigurationService,
        EngineConfigurationServiceInterface $engineConfigurationService
    ) {
        $this->serviceRepository = $serviceConfigurationRepository;
        $this->accessGroupRepository = $accessGroupRepository;
        $this->engineConfigurationService = $engineConfigurationService;
        $this->hostConfigurationService = $hostConfigurationService;
    }

    /**
     * {@inheritDoc}
     * @return ServiceConfigurationServiceInterface
     */
    public function filterByContact($contact): ServiceConfigurationServiceInterface
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
    public function applyServices(Host $host): void
    {
        if ($host->getId() == null) {
            throw new ServiceConfigurationException(_('The host id cannot be null'));
        }
        $hostTemplates = $this->hostConfigurationService->findHostTemplatesRecursively($host);
        if (empty($hostTemplates)) {
            return;
        }
        $host->setTemplates($hostTemplates);
        /**
         * To avoid defining a service description with illegal characters,
         * we retrieve the engine configuration to retrieve the list of these characters.
         */
        $engineConfiguration = $this->engineConfigurationService->findEngineConfigurationByHost($host);
        if ($engineConfiguration === null) {
            throw new ServiceConfigurationException(_('Unable to find the Engine configuration'));
        }

        /**
         * Find all host templates recursively and copy their id into the given list.
         *
         * **We only retrieve templates that are enabled.**
         *
         * @param Host $host Host for which we will find all host template.
         * @return int[]
         */
        $extractHostTemplateIdsFromHost =
            function (Host $host) use (&$extractHostTemplateIdsFromHost): array {
                $hostTemplateIds = [];
                foreach ($host->getTemplates() as $hostTemplate) {
                    if ($hostTemplate->isActivated() === false) {
                        continue;
                    }
                    $hostTemplateIds[] = $hostTemplate->getId();
                    if (!empty($hostTemplate->getTemplates())) {
                        // The recursive call here allow you to keep the priority orders of the host templates
                        $hostTemplateIds = array_merge(
                            $hostTemplateIds,
                            $extractHostTemplateIdsFromHost($hostTemplate)
                        );
                    }
                }
                return $hostTemplateIds;
            };

        $hostTemplateIds = $extractHostTemplateIdsFromHost($host);

        /**
         * First, we will search for services already associated with the host to avoid creating a new one with
         * same service description.
         */
        $serviceAlreadyExists = $this->findServicesByHost($host);

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
        $hostTemplateServices = $this->findHostTemplateServices($hostTemplateIds);

        /**
         * Extract service templates associated to host template.
         *
         * **We retrieve service templates only from an enabled host template.**
         *
         * @param int $hostTemplateId Host template id for which we want to find the services templates
         * @return Service[]
         */
        $extractServiceTemplatesByHostTemplate = function (int $hostTemplateId) use ($hostTemplateServices): array {
            $serviceTemplates = [];
            foreach ($hostTemplateServices as $hostTemplateService) {
                if ($hostTemplateService->getHostTemplate()->getId() === $hostTemplateId) {
                    // Only if the host template is activated
                    if ($hostTemplateService->getHostTemplate()->isActivated()) {
                        $serviceTemplates[] = $hostTemplateService->getServiceTemplate();
                    }
                }
            }
            return $serviceTemplates;
        };

        $servicesToBeCreated = [];

        /**
         * Then, we set aside the services to be created.
         * We must not have two services with the same description (alias of the service template).
         * The priority order is defined by the list of host templates.
         * We only retrieve the service templates that are activated.
         */
        foreach ($hostTemplateIds as $hostTemplateId) {
            $serviceTemplates = $extractServiceTemplatesByHostTemplate($hostTemplateId);

            foreach ($serviceTemplates as $serviceTemplate) {
                if (!$serviceTemplate->isActivated()) {
                    continue;
                }

                if (
                    $serviceTemplate->getAlias() !== null
                    && !in_array($serviceTemplate->getAlias(), $serviceAliasAlreadyUsed)
                ) {
                    $serviceDescription = $engineConfiguration->removeIllegalCharacters($serviceTemplate->getAlias());

                    if (empty($serviceDescription)) {
                        continue;
                    }

                    $serviceAliasAlreadyUsed[] = $serviceDescription;
                    $serviceToBeCreated = (new Service())
                        ->setServiceType(Service::TYPE_SERVICE)
                        ->setTemplateId($serviceTemplate->getId())
                        ->setDescription($serviceDescription)
                        ->setActivated(true);
                    $servicesToBeCreated[] = $serviceToBeCreated;
                }
            }
        }

        try {
            $this->serviceRepository->addServicesToHost($host, $servicesToBeCreated);
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException(
                sprintf(
                    _('Error when adding services to the host %d'),
                    $host->getId()
                )
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function findHostTemplateServices(array $hostTemplateIds): array
    {
        try {
            return $this->serviceRepository->findHostTemplateServices($hostTemplateIds);
        } catch (\Throwable $ex) {
            throw new ServiceConfigurationException(
                _('Error when searching for host and related service templates'),
                0,
                $ex
            );
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
    public function findServiceMacrosFromCommandLine(int $serviceId, string $command): array
    {
        $serviceMacros = [];
        if (preg_match_all('/(\$_SERVICE\S+?\$)/', $command, $matches)) {
            $matchedMacros = $matches[0];

            foreach ($matchedMacros as $matchedMacroName) {
                $hostMacros[$matchedMacroName] = (new ServiceMacro())
                    ->setName($matchedMacroName)
                    ->setValue('');
            }

            $linkedServiceMacros = $this->findOnDemandServiceMacros($serviceId, true);
            foreach ($linkedServiceMacros as $linkedServiceMacro) {
                if (in_array($linkedServiceMacro->getName(), $matchedMacros)) {
                    $serviceMacros[$linkedServiceMacro->getName()] = $linkedServiceMacro;
                }
            }
        }

        return array_values($serviceMacros);
    }

    /**
     * @return HostConfigurationServiceInterface
     */
    public function getHostConfigurationService(): HostConfigurationServiceInterface
    {
        return $this->hostConfigurationService;
    }
}

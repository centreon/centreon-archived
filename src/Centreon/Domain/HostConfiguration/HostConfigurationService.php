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

use Centreon\Domain\ActionLog\ActionLog;
use Centreon\Domain\ActionLog\Interfaces\ActionLogServiceInterface;
use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostCategory\HostCategoryServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroServiceInterface;
use Centreon\Domain\HostConfiguration\Model\HostCategory;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Centreon\Infrastructure\HostConfiguration\API\Model\HostCategory\HostCategoryV2110Factory;

/**
 * @package Centreon\Domain\HostConfiguration
 */
class HostConfigurationService implements HostConfigurationServiceInterface
{
    /**
     * @var HostConfigurationRepositoryInterface
     */
    private $hostConfigurationRepository;
    /**
     * @var EngineConfigurationServiceInterface
     */
    private $engineConfigurationService;
    /**
     * @var ActionLogServiceInterface
     */
    private $actionLogService;
    /**
     * @var DataStorageEngineInterface
     */
    private $dataStorageEngine;
    /**
     * @var HostMacroServiceInterface
     */
    private $hostMacroService;
    /**
     * @var HostCategoryServiceInterface
     */
    private $hostCategoryService;
    /**
     * @var HostGroupServiceInterface
     */
    private $hostGroupService;

    /**
     * @param HostConfigurationRepositoryInterface $hostConfigurationRepository
     * @param ActionLogServiceInterface $actionLogService
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     * @param HostMacroServiceInterface $hostMacroService
     * @param HostCategoryServiceInterface $hostCategoryService
     * @param HostGroupServiceInterface $hostGroupService
     * @param DataStorageEngineInterface $dataStorageEngine
     */
    public function __construct(
        HostConfigurationRepositoryInterface $hostConfigurationRepository,
        ActionLogServiceInterface $actionLogService,
        EngineConfigurationServiceInterface $engineConfigurationService,
        HostMacroServiceInterface $hostMacroService,
        HostCategoryServiceInterface $hostCategoryService,
        HostGroupServiceInterface $hostGroupService,
        DataStorageEngineInterface $dataStorageEngine
    ) {
        $this->hostConfigurationRepository = $hostConfigurationRepository;
        $this->actionLogService = $actionLogService;
        $this->engineConfigurationService = $engineConfigurationService;
        $this->hostMacroService = $hostMacroService;
        $this->hostCategoryService = $hostCategoryService;
        $this->hostGroupService = $hostGroupService;
        $this->dataStorageEngine = $dataStorageEngine;
    }

    /**
     * @inheritDoc
     */
    public function addHost(Host $host): void
    {
        if (empty($host->getName())) {
            throw new HostConfigurationException(_('Host name can not be empty'));
        }
        try {
            if (empty($host->getIpAddress())) {
                throw new HostConfigurationException(_('Ip address can not be empty'));
            }

            if ($host->getMonitoringServer() === null || $host->getMonitoringServer()->getName() === null) {
                throw new HostConfigurationException(_('Monitoring server is not correctly defined'));
            }

            /*
             * To avoid defining a host name with illegal characters,
             * we retrieve the engine configuration to retrieve the list of these characters.
             */
            $engineConfiguration = $this->engineConfigurationService->findEngineConfigurationByName(
                $host->getMonitoringServer()->getName()
            );
            if ($engineConfiguration === null) {
                throw new HostConfigurationException(_('Unable to find the Engine configuration'));
            }

            $safedHostName = $engineConfiguration->removeIllegalCharacters($host->getName());
            if (empty($safedHostName)) {
                throw new HostConfigurationException(_('Host name can not be empty'));
            }
            $host->setName($safedHostName);

            if ($this->hostConfigurationRepository->hasHostWithSameName($host->getName())) {
                throw new HostConfigurationException(_('Host name already exists'));
            }
            if ($host->getExtendedHost() === null) {
                $host->setExtendedHost(new ExtendedHost());
            }

            if ($host->getMonitoringServer()->getId() === null) {
                $host->getMonitoringServer()->setId($engineConfiguration->getMonitoringServerId());
            }
            $this->dataStorageEngine->startTransaction();
            try {
                /**
                 * Create all the entities that will be associated with the host and that
                 * must exist beforehand and provided that their identifier is defined.
                 */
                $this->createHostCategoriesBeforeLinking($host->getCategories());
                $this->createHostGroupsBeforeLinking($host->getGroups());
                $this->hostConfigurationRepository->addHost($host);
                /**
                 * Create all the entities that will be associated with the host that must be created first.
                 */
                foreach ($host->getMacros() as $macro) {
                    $this->hostMacroService->addMacroToHost($host, $macro);
                }
            } catch (\Throwable $ex) {
                $this->dataStorageEngine->rollbackTransaction();
                throw new HostConfigurationException(
                    sprintf(
                        _('Error when adding a host (Reason: %s)'),
                        $ex->getMessage()
                    ),
                    0,
                    $ex
                );
            }
            $this->dataStorageEngine->commitTransaction();

            if ($host->getId() !== null) {
                $defaultStatus = 'Default';

                // We create the list of changes concerning the creation of the host
                $actionsDetails = [
                    'Host name' => $host->getName() ?? '',
                    'Host alias' => $host->getAlias() ?? '',
                    'Host IP address' => $host->getIpAddress() ?? '',
                    'Monitoring server name' => $host->getMonitoringServer()->getName() ?? '',
                    'Create services linked to templates' => 'true',
                    'Is activated' => $host->isActivated() ? 'true' : 'false',

                    // We don't have these properties in the host object yet, so we display these default values
                    'Active checks enabled' => $defaultStatus,
                    'Passive checks enabled' => $defaultStatus,
                    'Notifications enabled' => $defaultStatus,
                    'Obsess over host' => $defaultStatus,
                    'Check freshness' => $defaultStatus,
                    'Flap detection enabled' => $defaultStatus,
                    'Retain status information' => $defaultStatus,
                    'Retain nonstatus information' => $defaultStatus,
                    'Event handler enabled' => $defaultStatus,
                ];
                if (!empty($host->getTemplates())) {
                    $templateNames = [];
                    foreach ($host->getTemplates() as $template) {
                        if (!empty($template->getName())) {
                            $templateNames[] = $template->getName();
                        }
                    }
                    $actionsDetails = array_merge(
                        $actionsDetails,
                        ['Templates selected' => implode(', ', $templateNames)]
                    );
                }

                if (!empty($host->getMacros())) {
                    $macroDetails = [];
                    foreach ($host->getMacros() as $macro) {
                        if (!empty($macro->getName())) {
                            // We remove the symbol characters in the macro name
                            $macroDetails[substr($macro->getName(), 2, strlen($macro->getName()) - 3)] =
                                $macro->isPassword() ? '*****' : $macro->getValue() ?? '';
                        }
                    }
                    $actionsDetails = array_merge(
                        $actionsDetails,
                        [
                            'Macro names' => implode(', ', array_keys($macroDetails)),
                            'Macro values' => implode(', ', array_values($macroDetails))
                        ]
                    );
                }
                $this->actionLogService->addAction(
                // The userId is set to 0 because it is not yet possible to determine who initiated the action.
                // We will see later how to get it back.
                    new ActionLog('host', $host->getId(), $host->getName(), ActionLog::ACTION_TYPE_ADD, 0),
                    $actionsDetails
                );
            }
        } catch (HostConfigurationException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new HostConfigurationException(_('Error while creation of host'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findHostTemplatesRecursively(Host $host): array
    {
        try {
            return $this->hostConfigurationRepository->findHostTemplatesRecursively($host);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error when searching for host templates'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findHost(int $hostId): ?Host
    {
        try {
            return $this->hostConfigurationRepository->findHost($hostId);
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
            return $this->hostConfigurationRepository->getNumberOfHosts();
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error while searching for the number of host'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findCommandLine(int $hostId): ?string
    {
        try {
            return $this->hostConfigurationRepository->findCommandLine($hostId);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error while searching for the command of host'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findOnDemandHostMacros(int $hostId, bool $isUsingInheritance = false): array
    {
        try {
            return $this->hostConfigurationRepository->findOnDemandHostMacros($hostId, $isUsingInheritance);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error while searching for the host macros'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findHostMacrosFromCommandLine(int $hostId, string $command): array
    {
        $hostMacros = [];
        if (preg_match_all('/(\$_HOST\S+?\$)/', $command, $matches)) {
            $matchedMacros = $matches[0];

            foreach ($matchedMacros as $matchedMacroName) {
                // snmp macros are not custom macros
                if (!in_array($matchedMacroName, ['$_HOSTSNMPCOMMUNITY$', '$_HOSTSNMPVERSION$'])) {
                    $hostMacros[$matchedMacroName] = (new HostMacro())
                        ->setName($matchedMacroName)
                        ->setValue('');
                }
            }

            $linkedHostMacros = $this->findOnDemandHostMacros($hostId, true);
            foreach ($linkedHostMacros as $linkedHostMacro) {
                if (in_array($linkedHostMacro->getName(), $matchedMacros)) {
                    $hostMacros[$linkedHostMacro->getName()] = $linkedHostMacro;
                }
            }
        }

        return array_values($hostMacros);
    }

    /**
     * @inheritDoc
     */
    public function changeActivationStatus(Host $host, bool $shouldBeActivated): void
    {
        try {
            if ($host->getId() === null) {
                throw new HostConfigurationException(_('Host id cannot be null'));
            }
            if ($host->getName() === null) {
                throw new HostConfigurationException(_('Host name cannot be null'));
            }
            $loadedHost = $this->findHost($host->getId());
            if ($loadedHost === null) {
                throw new HostConfigurationException(sprintf(_('Host %d not found'), $host->getId()));
            }
            if ($loadedHost->getId() ===  null) {
                throw new HostConfigurationException(_('Host id cannot be null'));
            }
            $this->hostConfigurationRepository->changeActivationStatus($loadedHost->getId(), $shouldBeActivated);
            $this->actionLogService->addAction(
            // The userId is set to 0 because it is not yet possible to determine who initiated the action.
            // We will see later how to get it back.
                new ActionLog(
                    'host',
                    $host->getId(),
                    $host->getName(),
                    $shouldBeActivated ? ActionLog::ACTION_TYPE_ENABLE : ActionLog::ACTION_TYPE_DISABLE,
                    0
                )
            );
        } catch (HostConfigurationException $ex) {
            throw $ex;
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(
                sprintf(
                    _('Error when changing host status (%d to %s)'),
                    $host->getId(),
                    $shouldBeActivated ? 'true' : 'false'
                ),
                0,
                $ex
            );
        }
    }

    /**
    * @inheritDoc
    */
    public function findHostNamesAlreadyUsed(array $namesToCheck): array
    {
        try {
            return $this->hostConfigurationRepository->findHostNamesAlreadyUsed($namesToCheck);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error when searching for already used host names'));
        }
    }

    /**
     * Create the host categories if they do not exist.
     *
     * @param HostCategory[] $categories Host categories to be created
     * @throws Exception\HostCategoryException
     */
    private function createHostCategoriesBeforeLinking(array $categories): void
    {
        $namesToCheckBeforeCreation = [];
        foreach ($categories as $category) {
            if ($category->getId() === null) {
                $namesToCheckBeforeCreation[] = $category->getName();
            }
        }
        $categoriesAlreadyCreated = $this->hostCategoryService->findByNamesWithoutAcl($namesToCheckBeforeCreation);
        $categoriesToBeCreated = [];

        foreach ($categories as $category) {
            if ($category->getId() !== null) {
                continue;
            }
            $found = false;
            foreach ($categoriesAlreadyCreated as $categoryAlreadyCreated) {
                if ($category->getName() == $categoryAlreadyCreated->getName()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $categoriesToBeCreated[] = $category;
            }
        }
        foreach ($categoriesToBeCreated as $categoryToBeCreated) {
            $this->hostCategoryService->addCategory($categoryToBeCreated);
        }
        /*
         * We retrieve the id of already created or newly created categories and associate them with the list of
         * original categories so that they can be linked to the host.
         */
        foreach ($categories as $category) {
            if ($category->getId() === null) {
                foreach ($categoriesToBeCreated as $categoryCreated) {
                    if (
                        $category->getName() === $categoryCreated->getName()
                        && $categoryCreated->getId() !== null
                    ) {
                        $category->setId($categoryCreated->getId());
                    }
                }
                foreach ($categoriesAlreadyCreated as $categoryAlreadyCreated) {
                    if (
                        $category->getName() === $categoryAlreadyCreated->getName()
                        && $categoryAlreadyCreated->getId() !== null
                    ) {
                        $category->setId($categoryAlreadyCreated->getId());
                    }
                }
            }
        }
    }

    /**
     * Create the host groups if they do not exist.
     *
     * @param HostGroup[] $groups Host groups to be created
     * @throws Exception\HostGroupException
     */
    private function createHostGroupsBeforeLinking(array $groups): void
    {
        $namesToCheckBeforeCreation = [];
        foreach ($groups as $group) {
            if ($group->getId() === null) {
                $namesToCheckBeforeCreation[] = $group->getName();
            }
        }
        $groupsAlreadyCreated = $this->hostGroupService->findByNamesWithoutAcl($namesToCheckBeforeCreation);
        $groupsToBeCreated = [];

        foreach ($groups as $group) {
            if ($group->getId() !== null) {
                continue;
            }
            $found = false;
            foreach ($groupsAlreadyCreated as $groupAlreadyCreated) {
                if ($group->getName() == $groupAlreadyCreated->getName()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $groupsToBeCreated[] = $group;
            }
        }
        foreach ($groupsToBeCreated as $groupToBeCreated) {
            $this->hostGroupService->addGroup($groupToBeCreated);
        }
        /*
         * We retrieve the id of already created or newly created groups and associate them with the list of original
         * groups so that they can be linked to the host.
         */
        foreach ($groups as $group) {
            if ($group->getId() === null) {
                foreach ($groupsToBeCreated as $groupCreated) {
                    if (
                        $group->getName() === $groupCreated->getName()
                        && $groupCreated->getId() !== null
                    ) {
                        $group->setId($groupCreated->getId());
                    }
                }
                foreach ($groupsAlreadyCreated as $groupCreated) {
                    if (
                        $group->getName() === $groupCreated->getName()
                        && $groupCreated->getId() !== null
                    ) {
                        $group->setId($groupCreated->getId());
                    }
                }
            }
        }
    }
}

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
use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\HostConfiguration\Exception\HostConfigurationServiceException;
use Centreon\Domain\HostConfiguration\Interfaces\HostCategory\HostCategoryServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroServiceInterface;
use Centreon\Domain\HostConfiguration\Model\HostCategory;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;

/**
 * @package Centreon\Domain\HostConfiguration
 */
class HostConfigurationService implements HostConfigurationServiceInterface
{
    use LoggerTrait;

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
     * @var ContactInterface
     */
    private $contact;

    /**
     * @param HostConfigurationRepositoryInterface $hostConfigurationRepository
     * @param ActionLogServiceInterface $actionLogService
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     * @param HostMacroServiceInterface $hostMacroService
     * @param HostCategoryServiceInterface $hostCategoryService
     * @param HostGroupServiceInterface $hostGroupService
     * @param DataStorageEngineInterface $dataStorageEngine
     * @param ContactInterface $contact
     */
    public function __construct(
        HostConfigurationRepositoryInterface $hostConfigurationRepository,
        ActionLogServiceInterface $actionLogService,
        EngineConfigurationServiceInterface $engineConfigurationService,
        HostMacroServiceInterface $hostMacroService,
        HostCategoryServiceInterface $hostCategoryService,
        HostGroupServiceInterface $hostGroupService,
        DataStorageEngineInterface $dataStorageEngine,
        ContactInterface $contact
    ) {
        $this->hostConfigurationRepository = $hostConfigurationRepository;
        $this->actionLogService = $actionLogService;
        $this->engineConfigurationService = $engineConfigurationService;
        $this->hostMacroService = $hostMacroService;
        $this->hostCategoryService = $hostCategoryService;
        $this->hostGroupService = $hostGroupService;
        $this->dataStorageEngine = $dataStorageEngine;
        $this->contact = $contact;
    }

    /**
     * {@inheritDoc]
     * @throws \Assert\AssertionFailedException
     */
    public function addHost(Host $host): void
    {
        $this->info('Add host');
        Assertion::notEmpty($host->getName(), 'Host::name');
        Assertion::notEmpty($host->getIpAddress(), 'Host::ipAddress');

        if ($host->getMonitoringServer() === null || $host->getMonitoringServer()->getName() === null) {
            throw HostConfigurationServiceException::monitoringServerNotCorrectlyDefined();
        }
        $this->debug(
            'Host details',
            ['host_name' => $host->getName(), 'monitoring_server' => $host->getMonitoringServer()->getName()]
        );
        $transactionAlreadyStarted = $this->dataStorageEngine->isAlreadyinTransaction();
        try {
            $this->checkIllegalCharactersInHostName($host);

            if ($this->hostConfigurationRepository->hasHostWithSameName($host->getName())) {
                throw HostConfigurationServiceException::hostNameAlreadyExists();
            }
            try {
                if ($transactionAlreadyStarted === false) {
                    $this->debug('Start transaction');
                    $this->dataStorageEngine->startTransaction();
                }
                /**
                 * Create all the entities that will be associated with the host and that
                 * must exist beforehand and provided that their identifier is defined.
                 */
                $this->createHostCategoriesBeforeLinking($host->getCategories());
                $this->createHostGroupsBeforeLinking($host->getGroups());
                $this->debug('Adding host');
                $this->hostConfigurationRepository->addHost($host);
                /**
                 * Create all the entities that will be associated with the host that must be created first.
                 */
                foreach ($host->getMacros() as $macro) {
                    $this->debug('Add macro ' . $macro->getName());
                    $this->hostMacroService->addMacroToHost($host, $macro);
                }
                if ($transactionAlreadyStarted === false) {
                    $this->debug('Commit transaction');
                    $this->dataStorageEngine->commitTransaction();
                }
            } catch (\Throwable $ex) {
                if ($transactionAlreadyStarted === false) {
                    $this->debug('Rollback transaction');
                    $this->dataStorageEngine->rollbackTransaction();
                }
                throw HostConfigurationServiceException::errorOnAddingAHost($ex);
            }

            if ($host->getId() !== null) {
                $this->addActionLog($host, ActionLog::ACTION_TYPE_ADD);
            }
        } catch (HostConfigurationServiceException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw HostConfigurationServiceException::errorOnAddingAHost($ex);
        }
    }

    /**
     * {@inheritDoc}
     * @throws HostConfigurationServiceException
     * @throws \Throwable
     */
    public function updateHost(Host $host): void
    {
        $this->info('Update host');
        if (empty($host->getName())) {
            throw HostConfigurationServiceException::hostNameCanNotBeEmpty();
        }
        if (empty($host->getIpAddress())) {
            throw HostConfigurationServiceException::ipAddressCanNotBeEmpty();
        }
        if ($host->getMonitoringServer() === null || $host->getMonitoringServer()->getName() === null) {
            throw HostConfigurationServiceException::monitoringServerNotCorrectlyDefined();
        }
        $transactionAlreadyStarted = $this->dataStorageEngine->isAlreadyinTransaction();
        try {
            $this->checkIllegalCharactersInHostName($host);

            if ($transactionAlreadyStarted === false) {
                $this->debug('Start transaction');
                $this->dataStorageEngine->startTransaction();
            }

            $this->debug('Updating host');
            $this->hostConfigurationRepository->updateHost($host);

            $this->addAndUpdateHostMacros($host);

            if ($transactionAlreadyStarted === false) {
                $this->debug('Commit transaction');
                $this->dataStorageEngine->commitTransaction();
            }
        } catch (\Throwable $ex) {
            if ($transactionAlreadyStarted === false) {
                $this->debug('Rollback transaction');
                $this->dataStorageEngine->rollbackTransaction();
            }
            throw HostConfigurationServiceException::errorOnUpdatingAHost($ex);
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
                if (in_array($matchedMacroName, ['$_HOSTSNMPCOMMUNITY$', '$_HOSTSNMPVERSION$']) === false) {
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
     * Create host categories if they don't exist, otherwise we initialise the ids with the existing categories.
     *
     * @param HostCategory[] $categories Host categories to be created
     * @throws Exception\HostCategoryException
     */
    private function createHostCategoriesBeforeLinking(array $categories): void
    {
        $this->info('Create host categories before linking');
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
            if ($found === false) {
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
     * Create host groups if they don't exist, otherwise we initialise the ids with the existing host groups.
     *
     * @param HostGroup[] $groups Host groups to be created
     * @throws Exception\HostGroupException
     */
    private function createHostGroupsBeforeLinking(array $groups): void
    {
        $this->info('Create host groups before linking');
        $namesToCheckBeforeCreation = [];
        foreach ($groups as $group) {
            if ($group->getId() === null) {
                $namesToCheckBeforeCreation[] = $group->getName();
            }
        }

        // We search for them as if we were an admin user
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
            if ($found === false) {
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

    /**
     * The host name is checked to remove any illegal characters that monitoring server cannot accept.
     *
     * @param Host $host
     * @throws HostConfigurationException
     * @throws \Centreon\Domain\Engine\EngineException
     */
    private function checkIllegalCharactersInHostName(Host $host): void
    {
        $this->info('Check illegal characters in host name');
        $engineConfiguration = $this->engineConfigurationService->findEngineConfigurationByName(
            $host->getMonitoringServer()->getName()
        );
        if ($engineConfiguration === null) {
            throw HostConfigurationServiceException::engineConfigurationNotFound();
        }
        $this->debug(
            'Engine configuration',
            ['id' => $engineConfiguration->getId(), 'name' => $engineConfiguration->getName()]
        );
        $safedHostName = $engineConfiguration->removeIllegalCharacters($host->getName());
        if (empty($safedHostName)) {
            throw HostConfigurationServiceException::hostNameCanNotBeEmpty();
        }
        $this->debug('Safed host name: ' . $safedHostName);
        $host->setName($safedHostName);
        if ($host->getExtendedHost() === null) {
            $host->setExtendedHost(new ExtendedHost());
            $this->debug('ExtendedHost created');
        }

        if ($host->getMonitoringServer()->getId() === null) {
            $host->getMonitoringServer()->setId($engineConfiguration->getMonitoringServerId());
            $this->debug('Monitoring server id defined #' . $engineConfiguration->getMonitoringServerId());
        }
    }

    /**
     * @throws \Centreon\Domain\ActionLog\ActionLogException
     */
    private function addActionLog(Host $host, string $action): void
    {
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
        if (empty($host->getTemplates()) === false) {
            $templateNames = [];
            foreach ($host->getTemplates() as $template) {
                if (empty($template->getName())  === false) {
                    $templateNames[] = $template->getName();
                }
            }
            $actionsDetails = array_merge(
                $actionsDetails,
                ['Templates selected' => implode(', ', $templateNames)]
            );
        }

        if (empty($host->getMacros()) === false) {
            $macroDetails = [];
            foreach ($host->getMacros() as $macro) {
                if (empty($macro->getName()) === false) {
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
            new ActionLog('host', $host->getId(), $host->getName(), $action, $this->contact->getId()),
            $actionsDetails
        );
    }

    /**
     * Add and update all host macros.
     *
     * @param Host $host
     * @throws Exception\HostMacroServiceException
     */
    private function addAndUpdateHostMacros(Host $host): void
    {
        $this->debug('Add and update host macros');
        $existingHostMacros = $this->hostMacroService->findHostMacros($host);
        $this->updateHostMacros($host, $existingHostMacros);
        $this->addNewHostMacros($host, $existingHostMacros);
    }

    /**
     * Update all macros on the host that already exist.
     * We use the existing macros to identify which ones need to be updated in the host.
     *
     * @param Host $host
     * @param HostMacro[] $existingHostMacros
     * @throws Exception\HostMacroServiceException
     */
    private function updateHostMacros(Host $host, array $existingHostMacros): void
    {
        $this->debug('Update existing host macros');
        foreach ($host->getMacros() as $macroToUpdate) {
            foreach ($existingHostMacros as $existingHostMacro) {
                if ($macroToUpdate->getName() === $existingHostMacro->getName()) {
                    $this->debug('Update macro ' . $macroToUpdate->getName());
                    $this->hostMacroService->updateMacro($macroToUpdate);
                    break;
                }
            }
        }
    }

    /**
     *  @inheritDoc
     */
    public function findHostTemplatesByHost(Host $host): array
    {
        try {
            return $this->hostConfigurationRepository->findHostTemplatesByHost($host);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error when searching for host templates'), 0, $ex);
        }
    }

    /**
     * Add the host macros that does not exist.
     * The host macros added will be those that are not present in the $existingHostMacros list.
     *
     * @param Host $host
     * @param HostMacro[] $existingHostMacros
     * @throws Exception\HostMacroServiceException
     */
    private function addNewHostMacros(Host $host, array $existingHostMacros): void
    {
        $this->debug('Add new host macros');
        foreach ($host->getMacros() as $hostMacro) {
            $isHostMacroFound = false;
            foreach ($existingHostMacros as $existingHostMacro) {
                if ($hostMacro->getName() === $existingHostMacro->getName()) {
                    $isHostMacroFound = true;
                    break;
                }
            }
            if (! $isHostMacroFound) {
                $this->debug(
                    'Add new host macro',
                    [],
                    fn () => [
                        'name' => $hostMacro->getName(),
                        'is_password' => $hostMacro->isPassword(),
                        'value' => (! $hostMacro->isPassword()) ? $hostMacro->getValue() : '*****'
                    ]
                );
                $this->hostMacroService->addMacroToHost($host, $hostMacro);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function findHostByName(string $hostName): ?Host
    {
        try {
            return $this->hostConfigurationRepository->findHostByName($hostName);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error while searching for the host'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     * @throws HostConfigurationException
     */
    public function findHostTemplateById(int $hostTemplateId): ?Host
    {
        try {
            return $this->hostConfigurationRepository->findHostTemplateById($hostTemplateId);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error while searching for the host template'), 0, $ex);
        }
    }
}

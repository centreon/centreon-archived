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
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationServiceInterface;
use Centreon\Domain\Repository\RepositoryException;

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
     * @param HostConfigurationRepositoryInterface $hostConfigurationRepository
     * @param ActionLogServiceInterface $actionLogService
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     */
    public function __construct(
        HostConfigurationRepositoryInterface $hostConfigurationRepository,
        ActionLogServiceInterface $actionLogService,
        EngineConfigurationServiceInterface $engineConfigurationService
    ) {
        $this->hostConfigurationRepository = $hostConfigurationRepository;
        $this->actionLogService = $actionLogService;
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

            $safedHostName = EngineConfiguration::removeIllegalCharacters(
                $host->getName(),
                $engineConfiguration->getIllegalObjectNameCharacters()
            );
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
            $hostId = $this->hostConfigurationRepository->addHost($host);
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
                $actionsDetails = array_merge($actionsDetails, ['Templates selected' => implode(', ', $templateNames)]);
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
                $actionsDetails = array_merge($actionsDetails, [
                    'Macro names' => implode(', ', array_keys($macroDetails)),
                    'Macro values' => implode(', ', array_values($macroDetails))
                ]);
            }
            $this->actionLogService->addAction(
                // The userId is set to 0 because it is not yet possible to determine who initiated the action.
                // We will see later how to get it back.
                new ActionLog('host', $hostId, $host->getName(), ActionLog::ACTION_TYPE_ADD, 0),
                $actionsDetails
            );
            return $hostId;
        } catch (HostConfigurationException $ex) {
            throw $ex;
        } catch (RepositoryException $ex) {
            throw new HostConfigurationException($ex->getMessage(), 0, $ex);
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
                $hostMacros[$matchedMacroName] = (new HostMacro())
                    ->setName($matchedMacroName)
                    ->setValue('');
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
}

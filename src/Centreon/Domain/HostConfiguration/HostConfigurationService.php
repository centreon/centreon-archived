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
     * HostConfigurationService constructor.
     *
     * @param HostConfigurationRepositoryInterface $hostConfigurationRepository
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     */
    public function __construct(
        HostConfigurationRepositoryInterface $hostConfigurationRepository,
        EngineConfigurationServiceInterface $engineConfigurationService
    ) {
        $this->hostConfigurationRepository = $hostConfigurationRepository;
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
                throw new HostConfigurationException(_('Impossible to find the Engine configuration'));
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
            return $this->hostConfigurationRepository->addHost($host);
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
    public function findAndAddHostTemplates(Host $host): void
    {
        try {
            $this->hostConfigurationRepository->findAndAddHostTemplates($host);
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

    /**
     * @inheritDoc
     */
    public function changeActivationStatus(int $hostId, bool $shouldBeActivated): void
    {
        try {
            $this->hostConfigurationRepository->changeActivationStatus($hostId, $shouldBeActivated);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(
                sprintf(
                    _('Error when changing host status (%d to %s)'),
                    $hostId,
                    $shouldBeActivated ? 'true' : 'false'
                )
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

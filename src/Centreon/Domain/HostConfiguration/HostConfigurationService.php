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
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationServiceInterface;
use Centreon\Domain\ServiceConfiguration\ServiceConfigurationException;
use Symfony\Component\Security\Core\Security;

class HostConfigurationService implements HostConfigurationServiceInterface
{
    /**
     * @var HostConfigurationRepositoryInterface
     */
    private $hostConfigurationRepository;

    /**
     * @var ActionLogServiceInterface
     */
    private $actionLogService;

    /**
     * @var Contact
     */
    private $contact;

    /**
     * @var EngineConfigurationServiceInterface
     */
    private $engineConfigurationService;

    /**
     * HostConfigurationService constructor.
     *
     * @param HostConfigurationRepositoryInterface $hostConfigurationRepository
     * @param ActionLogServiceInterface $actionLogService
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     * @param Security $security
     */
    public function __construct(
        HostConfigurationRepositoryInterface $hostConfigurationRepository,
        ActionLogServiceInterface $actionLogService,
        EngineConfigurationServiceInterface $engineConfigurationService,
        Security $security
    ) {
        $this->hostConfigurationRepository = $hostConfigurationRepository;
        $this->actionLogService = $actionLogService;
        $this->contact = $security->getUser();
        $this->engineConfigurationService = $engineConfigurationService;
    }

    /**
     * @inheritDoc
     */
    public function addHost(Host $host): int
    {
        if (empty($host->getIpAddress())) {
            throw new HostConfigurationException(_('Host ip can not be empty'));
        }
        if (empty($host->getIpAddress())) {
            throw new HostConfigurationException(_('Host ip can not be empty'));
        }
        try {
            /**
             * To avoid recording a host name with illegal characters,
             * we retrieve the engine configuration to retrieve the list of these characters.
             */
            $engineConfiguration = $this->engineConfigurationService->findEngineConfigurationByHost($host);
            if ($engineConfiguration === null) {
                throw new ServiceConfigurationException(_('Impossible to find the Engine configuration'));
            }
            $safedHostName = EngineConfiguration::removeIllegalCharacters(
                $host->getName(),
                $engineConfiguration->getIllegalObjectNameCharacters()
            );
            if (empty($safedHostName)) {
                throw new HostConfigurationException(_('Host name can not be empty'));
            }
            $host->setName($safedHostName);

            $hasHostWithSameName = $this->hostConfigurationRepository->hasHostWithSameName($host->getName());
            if ($hasHostWithSameName) {
                throw new HostConfigurationException(_('Host name already exists'));
            }
            if ($host->getExtendedHost() === null) {
                $host->setExtendedHost(new ExtendedHost());
            }
            $hostId = $this->hostConfigurationRepository->addHost($host);
            $this->actionLogService->addLog(
                new ActionLog('host', $hostId, $host->getName(), ActionLog::ACTION_TYPE_ADD, $this->contact->getId())
            );
            return $hostId;
        } catch (HostConfigurationException $ex) {
            throw $ex;
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
    public function checkNamesAlreadyUsed(array $namesToCheck): array
    {
        try {
            return $this->hostConfigurationRepository->checkNamesAlreadyUsed($namesToCheck);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException(_('Error when searching for already used host names'));
        }
    }
}

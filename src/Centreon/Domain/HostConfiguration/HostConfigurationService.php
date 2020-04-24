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

use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationServiceInterface;

class HostConfigurationService implements HostConfigurationServiceInterface
{

    /**
     * @var HostConfigurationRepositoryInterface
     */
    private $hostConfigurationRepository;

    public function __construct(HostConfigurationRepositoryInterface $configurationRepository)
    {
        $this->hostConfigurationRepository = $configurationRepository;
    }

    /**
     * @inheritDoc
     */
    public function addHost(Host $host): int
    {
        if (empty($host->getName())) {
            throw new HostConfigurationException('Host name can not be empty');
        }
        try {
            $hasHostWithSameName = $this->hostConfigurationRepository->hasHostWithSameName($host->getName());
            if ($hasHostWithSameName) {
                throw new HostConfigurationException('Host name already exists');
            }
            if ($host->getExtendedHost() === null) {
                $host->setExtendedHost(new ExtendedHost());
            }
            return $this->hostConfigurationRepository->addHost($host);
        } catch (HostConfigurationException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new HostConfigurationException('Error while creation of host', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findHost(int $hostId): ?Host
    {
        try {
            return $this->hostConfigurationRepository->findHost($hostId);
        } catch (\Exception $ex) {
            throw new HostConfigurationException('Error while searching for the host', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function getNumberOfHosts(): int
    {
        try {
            return $this->hostConfigurationRepository->getNumberOfHosts();
        } catch (\Exception $ex) {
            throw new HostConfigurationException('Error while searching for the number of host', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findOnDemandHostMacros(int $hostId): array
    {
        try {
            return $this->hostConfigurationRepository->findOnDemandHostMacros($hostId);
        } catch (\Throwable $ex) {
            throw new HostConfigurationException('', 0, $ex);
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
            $onDemandHostMacros = $this->findOnDemandHostMacros($hostId);
            foreach ($onDemandHostMacros as $hostMacro) {
                if ($hostMacro->isPassword()) {
                    $hostMacrosPassword[] = $hostMacro;
                }
            }
        }
        return $hostMacrosPassword;
    }
}

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
    public function addHost(Host $host): void
    {
        try {
            $this->hostConfigurationRepository->addHost($host);
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
}

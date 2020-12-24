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

namespace Centreon\Domain\RemoteServer;

use Centreon\Domain\PlatformTopology\Platform;
use Centreon\Domain\RemoteServer\RemoteServerException;
use Centreon\Domain\Topology\Interfaces\TopologyRepositoryInterface;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;

class RemoteServerService implements RemoteServerServiceInterface
{

    /**
     * @var TopologyRepositoryInterface
     */
    private $topologyRepository;

    /**
     * @var PlatformTopologyRepositoryInterface
     */
    private $platformTopologyRepository;

    /**
     * @var string
     */
    private $centreonEtcPath;

    /**
     * @param TopologyRepositoryInterface $topologyRepository
     * @param PlatformTopologyRepositoryInterface $platformTopologyRepository
     */
    public function __construct(
        TopologyRepositoryInterface $topologyRepository,
        PlatformTopologyRepositoryInterface $platformTopologyRepository
    ) {
        $this->topologyRepository = $topologyRepository;
        $this->platformTopologyRepository = $platformTopologyRepository;
    }

    public function setCentreonEtcPath(string $centreonEtcPath): void
    {
        if ($centreonEtcPath[-1] !== DIRECTORY_SEPARATOR) {
            $centreonEtcPath .= DIRECTORY_SEPARATOR;
        }
        $this->centreonEtcPath = $centreonEtcPath;
    }

    /**
     * @inheritDoc
     */
    public function convertCentralToRemote(): void
    {
        /**
         * Stop conversion if the Central has remote children
         */
        $platformChildren = $this->platformTopologyRepository->findCentralRemoteChildren();
        if (!empty($platformChildren)) {
            throw new RemoteServerException(
                "Your Central is linked to another remote(s), conversion in Remote isn't allowed"
            );
        }

        $this->topologyRepository->disableMenus();

        /**
         * Set Remote type into Platform_Topology
         */
        $platform = $this->platformTopologyRepository->findTopLevelPlatform();
        $platform->setType(Platform::TYPE_REMOTE);
        $this->platformTopologyRepository->updatePlatformParameters($platform);

        /**
         * Apply Remote Server mode in configuration file
         */
        system(
            "sed -i -r 's/(\\\$instance_mode?\s+=?\s+\")([a-z]+)(\";)/\\1remote\\3/' "
            . $this->centreonEtcPath . "conf.pm"
        );
    }

    /**
     * @inheritDoc
     */
    public function convertRemoteToCentral(): void
    {
        $this->topologyRepository->enableMenus();

        /**
         * Set Central type into Platform_Topology
         */
        $platform = $this->platformTopologyRepository->findTopLevelPlatform();
        $platform->setType(Platform::TYPE_CENTRAL);
        $this->platformTopologyRepository->updatePlatformParameters($platform);

        /**
         * Apply Central mode in configuration file
         */
        system(
            "sed -i -r 's/(\\\$instance_mode?\s+=?\s+\")([a-z]+)(\";)/\\central\\3/' "
            . $this->centreonEtcPath . "conf.pm"
        );
    }
}

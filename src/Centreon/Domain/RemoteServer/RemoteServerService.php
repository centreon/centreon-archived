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

use Centreon\Domain\Menu\MenuException;
use Centreon\Domain\PlatformTopology\Platform;
use Centreon\Domain\PlatformTopology\PlatformException;
use Centreon\Domain\RemoteServer\RemoteServerException;
use Centreon\Domain\Menu\Interfaces\MenuRepositoryInterface;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;

class RemoteServerService implements RemoteServerServiceInterface
{

    /**
     * @var MenuRepositoryInterface
     */
    private $menuRepository;

    /**
     * @var PlatformTopologyRepositoryInterface
     */
    private $platformTopologyRepository;

    /**
     * @var string
     */
    private $centreonEtcPath;

    /**
     * @param MenuRepositoryInterface $menuRepository
     * @param PlatformTopologyRepositoryInterface $platformTopologyRepository
     */
    public function __construct(
        MenuRepositoryInterface $menuRepository,
        PlatformTopologyRepositoryInterface $platformTopologyRepository
    ) {
        $this->menuRepository = $menuRepository;
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
        try {
            $platformChildren = $this->platformTopologyRepository->findCentralRemoteChildren();
            if (!empty($platformChildren)) {
                throw new RemoteServerException(
                    "Your Central is linked to another remote(s), conversion in Remote isn't allowed"
                );
            }
        } catch (RemoteServerException $ex) {
            throw $ex;
        }

        /**
         * Set Remote type into Platform_Topology
         */
        $this->updatePlatformTypeParameters(Platform::TYPE_REMOTE);

        try {
            $this->menuRepository->disableCentralMenus();
        } catch (\Exception $ex) {
            throw new MenuException(_('An error occured while disabling the central menus'));
        }

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

        $this->updatePlatformTypeParameters(Platform::TYPE_CENTRAL);

        try {
            $this->menuRepository->enableCentralMenus();
        } catch (\Exception $ex) {
            throw new MenuException(_('An error occured while enabling the central menus'));
        }

        /**
         * Apply Central mode in configuration file
         */
        system(
            "sed -i -r 's/(\\\$instance_mode?\s+=?\s+\")([a-z]+)(\";)/\\1central\\3/' "
            . $this->centreonEtcPath . "conf.pm"
        );
    }

    private function updatePlatformTypeParameters(string $type) {
        try {
            $platform = $this->platformTopologyRepository->findTopLevelPlatform();
            $platform->setType($type);
            $this->platformTopologyRepository->updatePlatformParameters($platform);
        } catch (\Exception $ex) {
            throw new PlatformException(_('An error occured while updating the platform topology'));
        }
    }
}

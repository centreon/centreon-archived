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
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerRepositoryInterface;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerRepositoryInterface;

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
     * @var RemoteServerRepositoryInterface
     */
    private $remoteServerRepository;

    /**
     * @var MonitoringServerRepositoryInterface
     */
    private $monitoringServerRepository;

    /**
     * @param MenuRepositoryInterface $menuRepository
     * @param PlatformTopologyRepositoryInterface $platformTopologyRepository
     */
    public function __construct(
        MenuRepositoryInterface $menuRepository,
        PlatformTopologyRepositoryInterface $platformTopologyRepository,
        RemoteServerRepositoryInterface $remoteServerRepository,
        MonitoringServerRepositoryInterface $monitoringServerRepository
    ) {
        $this->menuRepository = $menuRepository;
        $this->platformTopologyRepository = $platformTopologyRepository;
        $this->remoteServerRepository = $remoteServerRepository;
        $this->monitoringServerRepository = $monitoringServerRepository;
    }

    /**
     * @inheritDoc
     */
    public function convertCentralToRemote(string $centralAddress): void
    {
        /**
         * Stop conversion if the Central has remote children
         */
        try {
            $platformChildren = $this->platformTopologyRepository->findCentralRemoteChildren();
            if (!empty($platformChildren)) {
                throw new RemoteServerException(
                    _("Your Central is linked to another remote(s), conversion in Remote isn't allowed")
                );
            }
        } catch (RemoteServerException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new RemoteServerException(_('An error occured while searching any remote children'));
        }

        /**
         * Find any children platform and forward them to Central Parent.
         */
        dd($this->monitoringServerRepository->findServersWithoutRequestParameters());

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
        $this->remoteServerRepository->updateInstanceModeRemote();
    }

    /**
     * @inheritDoc
     */
    public function convertRemoteToCentral(): void
    {
        /**
         * Set Central type into Platform_Topology
         */
        $this->updatePlatformTypeParameters(Platform::TYPE_CENTRAL);

        try {
            $this->menuRepository->enableCentralMenus();
        } catch (\Exception $ex) {
            throw new MenuException(_('An error occured while enabling the central menus'));
        }

        /**
         * Apply Central mode in configuration file
         */
        $this->remoteServerRepository->updateInstanceModeCentral();
    }

    /**
     * Update the platform type
     *
     * @param string $type
     */
    private function updatePlatformTypeParameters(string $type): void
    {
        try {
            $platform = $this->platformTopologyRepository->findTopLevelPlatform();
            $platform->setType($type);
            $this->platformTopologyRepository->updatePlatformParameters($platform);
        } catch (\Exception $ex) {
            throw new PlatformException(_('An error occured while updating the platform topology'));
        }
    }
}

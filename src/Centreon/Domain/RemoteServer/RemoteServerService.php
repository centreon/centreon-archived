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
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\PlatformTopology\PlatformException;
use Centreon\Domain\RemoteServer\RemoteServerException;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\Menu\Interfaces\MenuRepositoryInterface;
use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Domain\PlatformTopology\PlatformConflictException;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerRepositoryInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerRepositoryInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRegisterRepositoryInterface;

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
     * @var PlatformTopologyRegisterRepositoryInterface
     */
    private $platformTopologyRegisterRepository;

    /**
     * @var ProxyServiceInterface
     */
    private $proxyService;

    /**
     * @param MenuRepositoryInterface $menuRepository
     * @param PlatformTopologyRepositoryInterface $platformTopologyRepository
     */
    public function __construct(
        MenuRepositoryInterface $menuRepository,
        PlatformTopologyRepositoryInterface $platformTopologyRepository,
        RemoteServerRepositoryInterface $remoteServerRepository,
        PlatformTopologyRegisterRepositoryInterface $platformTopologyRegisterRepository,
        ProxyServiceInterface $proxyService
    ) {
        $this->menuRepository = $menuRepository;
        $this->platformTopologyRepository = $platformTopologyRepository;
        $this->remoteServerRepository = $remoteServerRepository;
        $this->platformTopologyRegisterRepository = $platformTopologyRegisterRepository;
        $this->proxyService = $proxyService;
    }

    /**
     * @inheritDoc
     */
    public function convertCentralToRemote(PlatformInformation $platformInformation): void
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
            throw new RemoteServerException(_('An error occured while searching any remote children'), 0, $ex);
        }

        /**
         * Set Remote type into Platform_Topology
         */
        $this->updatePlatformTypeParameters(Platform::TYPE_REMOTE);

        /**
         * Get the parent platform to register it later.
         *
         * @var Platform|null $topLevelPlatform
         */
        $topLevelPlatform = $this->platformTopologyRepository->findTopLevelPlatform();
        if ($topLevelPlatform === null) {
            throw new EntityNotFoundException(_('No top level platform found to link the child platforms'));
        }
        /**
         * Add the future Parent Central as Parent Address to be able to register it later.
         *
         */
        $topLevelPlatform->setParentAddress($platformInformation->getCentralServerAddress());

        /**
         * Find any children platform and forward them to Central Parent.
         *
         * @var Platform[] $platforms
         */
        $platforms = $this->platformTopologyRepository->findChildrenPlatformsByParentId(
            $topLevelPlatform->getId()
        );
        /**
         * Insert the Top Level Platform at the beginning of array, as it need to be registered first.
         */
        array_unshift($platforms, $topLevelPlatform);
        /**
         * Register the platforms on the Parent Central
         */
        foreach ($platforms as $platform) {
            if ($platform->getParentId() !== null) {
                $platform->setParentAddress($topLevelPlatform->getAddress());
            }

            $this->platformTopologyRegisterRepository->registerPlatformToParent(
                $platform,
                $platformInformation,
                $this->proxyService->getProxy()
            );
        }

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

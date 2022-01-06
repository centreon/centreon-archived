<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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
use Centreon\Domain\PlatformTopology\Model\PlatformRegistered;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyRepositoryExceptionInterface;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\PlatformTopology\Exception\PlatformTopologyException;
use Centreon\Domain\RemoteServer\RemoteServerException;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\Menu\Interfaces\MenuRepositoryInterface;
use Centreon\Domain\PlatformInformation\Model\PlatformInformation;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerLocalConfigurationRepositoryInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
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
     * @var RemoteServerLocalConfigurationRepositoryInterface
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
     * @var MonitoringServerServiceInterface
     */
    private $monitoringServerService;

    /**
     * @param MenuRepositoryInterface $menuRepository
     * @param PlatformTopologyRepositoryInterface $platformTopologyRepository
     * @param RemoteServerLocalConfigurationRepositoryInterface $remoteServerRepository
     * @param PlatformTopologyRegisterRepositoryInterface $platformTopologyRegisterRepository
     * @param ProxyServiceInterface $proxyService
     * @param MonitoringServerServiceInterface $monitoringServerService
     */
    public function __construct(
        MenuRepositoryInterface $menuRepository,
        PlatformTopologyRepositoryInterface $platformTopologyRepository,
        RemoteServerLocalConfigurationRepositoryInterface $remoteServerRepository,
        PlatformTopologyRegisterRepositoryInterface $platformTopologyRegisterRepository,
        ProxyServiceInterface $proxyService,
        MonitoringServerServiceInterface $monitoringServerService
    ) {
        $this->menuRepository = $menuRepository;
        $this->platformTopologyRepository = $platformTopologyRepository;
        $this->remoteServerRepository = $remoteServerRepository;
        $this->platformTopologyRegisterRepository = $platformTopologyRegisterRepository;
        $this->proxyService = $proxyService;
        $this->monitoringServerService = $monitoringServerService;
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
            throw new RemoteServerException(_('An error occurred while searching any remote children'), 0, $ex);
        }

        /**
         * Set Remote type into Platform_Topology
         */
        $this->updatePlatformTypeParameters(PlatformRegistered::TYPE_REMOTE);

        /**
         * Get the parent platform to register it later.
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

        if ($platformInformation->getPlatformName() !== null) {
            $topLevelPlatform->setName($platformInformation->getPlatformName());
        }
        /**
         * Find any children platform and forward them to Central Parent.
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
        try {
            foreach ($platforms as $platform) {
                if ($platform->getParentId() !== null) {
                    $platform->setParentAddress($topLevelPlatform->getAddress());
                }

                if ($platform->getServerId() !== null) {
                    $this->platformTopologyRegisterRepository->registerPlatformToParent(
                        $platform,
                        $platformInformation,
                        $this->proxyService->getProxy()
                    );
                }
            }

            $this->menuRepository->disableCentralMenus();
        } catch (PlatformTopologyRepositoryExceptionInterface | PlatformTopologyException $ex) {
            $this->updatePlatformTypeParameters(PlatformRegistered::TYPE_CENTRAL);
            throw $ex;
        } catch (\Exception $ex) {
            $this->updatePlatformTypeParameters(PlatformRegistered::TYPE_CENTRAL);
            throw new MenuException(_('An error occurred while disabling the central menus'));
        }

        /**
         * Apply Remote Server mode in configuration file
         */
        $this->remoteServerRepository->updateInstanceModeRemote();
    }

    /**
     * @inheritDoc
     */
    public function convertRemoteToCentral(PlatformInformation $platformInformation): void
    {
        /**
         * Delete the platform on its parent before anything else,
         * If this step throw an exception, don't go further and avoid decorelation betweens platforms.
         */
        $platform = $this->platformTopologyRepository->findTopLevelPlatform();
        $this->platformTopologyRegisterRepository->deletePlatformToParent(
            $platform,
            $platformInformation,
            $this->proxyService->getProxy()
        );

        /**
         * Find any children platform and remove them,
         * as they are now attached to the Central and no longer to this platform.
         */
        $childrenPlatforms = $this->platformTopologyRepository->findChildrenPlatformsByParentId(
            $platform->getId()
        );

        foreach ($childrenPlatforms as $childrenPlatform) {
            if ($childrenPlatform->getServerId() !== null) {
                $this->monitoringServerService->deleteServer($childrenPlatform->getServerId());
            }
            $this->platformTopologyRepository->deletePlatform($childrenPlatform->getId());
        }

        /**
         * Set Central type into Platform_Topology
         */
        $this->updatePlatformTypeParameters(PlatformRegistered::TYPE_CENTRAL);

        try {
            $this->menuRepository->enableCentralMenus();
        } catch (\Exception $ex) {
            throw new MenuException(_('An error occurred while enabling the central menus'));
        }

        /**
         * Apply Central mode in configuration file
         */
        $this->remoteServerRepository->updateInstanceModeCentral();
    }

    /**
     * Update the platform type
     * @param string $type
     * @throws PlatformTopologyException
     */
    private function updatePlatformTypeParameters(string $type): void
    {
        try {
            $platform = $this->platformTopologyRepository->findTopLevelPlatform();
            $platform->setType($type);
            $this->platformTopologyRepository->updatePlatformParameters($platform);
        } catch (\Exception $ex) {
            throw new PlatformTopologyException(_('An error occurred while updating the platform topology'));
        }
    }
}

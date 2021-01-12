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

namespace Centreon\Domain\PlatformInformation;

use Centreon\Domain\Menu\MenuException;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationRepositoryInterface;
use Centreon\Domain\PlatformTopology\PlatformException;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use Centreon\Domain\RemoteServer\RemoteServerException;

/**
 * Service intended to use rest API on 'information' specific configuration data
 *
 * @package Centreon\Domain\PlatformInformation
 */
class PlatformInformationService implements PlatformInformationServiceInterface
{

    /**
     * @var PlatformInformationRepositoryInterface
     */
    private $platformInformationRepository;

    /**
     * @var RemoteServerServiceInterface
     */
    private $remoteServerService;

    public function __construct(
        PlatformInformationRepositoryInterface $platformInformationRepository,
        RemoteServerServiceInterface $remoteServerService
    ) {
        $this->platformInformationRepository = $platformInformationRepository;
        $this->remoteServerService = $remoteServerService;
    }

    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
    public function getInformation(): ?PlatformInformation
    {
        $foundPlatformInformation = null;
        try {
            $foundPlatformInformation = $this->platformInformationRepository->findPlatformInformation();
        } catch (\InvalidArgumentException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new PlatformInformationException(
                _("Unable to retrieve platform information's data.")
            );
        }

        return $foundPlatformInformation;
    }

    /**
     * @inheritDoc
     */
    public function updatePlatformInformation(PlatformInformation $platformInformationUpdate): void
    {
        $currentPlatformInformation = $this->platformInformationRepository->findPlatformInformation();

        /**
         * Convert the Remote to Central or opposite
         */
        try {
            if ($platformInformationUpdate->isRemote() && !$currentPlatformInformation->isRemote()) {
                if ($platformInformationUpdate->getCentralServerAddress() !== null) {
                    $this->remoteServerService->convertCentralToRemote(
                        $platformInformationUpdate
                    );
                /**
                 * If the updated information as no Central Server Address, check in the existing information if its
                 * provided.
                 */
                } elseif ($currentPlatformInformation->getCentralServerAddress() !== null) {
                    $platformInformationUpdate->setCentralServerAddress(
                        $currentPlatformInformation->getCentralServerAddress()
                    );
                    $this->remoteServerService->convertCentralToRemote(
                        $platformInformationUpdate
                    );
                /**
                 * If no CentralServerAddress are provided into the updated or current information,
                 * we can't convert the platform in remote.
                 */
                } else {
                    throw new RemoteServerException(
                        _("Unable to convert in remote server, no Central to attached provided.")
                    );
                }
            } elseif ($platformInformationUpdate->isCentral() && !$currentPlatformInformation->isCentral()) {
                $this->remoteServerService->convertRemoteToCentral();
            }

            $this->platformInformationRepository->updatePlatformInformation($platformInformationUpdate);
        } catch (RemoteServerException | MenuException | PlatformException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new PlatformInformationException(_("An error occured while your platform update"), 0, $ex);
        }
    }
}

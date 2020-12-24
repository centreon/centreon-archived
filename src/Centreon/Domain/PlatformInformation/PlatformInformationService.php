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

use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationRepositoryInterface;
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
    public function updatePlatformInformation(PlatformInformation $platformInformationUpdate): ?PlatformInformation
    {
        /**
         * Convert the Remote to Central or opposite
         */
        try {
            if ($platformInformationUpdate->isRemote()) {
                $this->remoteServerService->convertCentralToRemote();
            } else {
                $this->remoteServerService->convertRemoteToCentral();
            }

            return $this->platformInformationRepository->updatePlatformInformation($platformInformationUpdate);
        } catch (RemoteServerException $ex) {
            throw new PlatformInformationException(_("An error occured while converting your platform"), null, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function updateExistingInformationFromArray(array $platformToUpdateProperty): ?PlatformInformation
    {
        /**
         * Get the current existing informations
         * @var PlatformInformation
         */
        $platformInformation = $this->getInformation();

        /**
         * Update the existing informations
         */
        if ($platformInformation !== null) {
            foreach ($platformToUpdateProperty as $platformProperty => $platformValue) {
                switch ($platformProperty) {
                    case 'version':
                        $platformInformation->setVersion($platformValue);
                        break;
                    case 'appKey':
                        $platformInformation->setAppKey($platformValue);
                        break;
                    case 'isRemote':
                        if ($platformValue === true) {
                            $platformInformation->setIsRemote('yes');
                            $platformInformation->setIsCentral('no');
                        }
                        break;
                    case 'isCentral':
                        if ($platformValue === true) {
                            $platformInformation->setIsCentral('yes');
                            $platformInformation->setIsRemote('no');
                        }
                        break;
                    case 'centralServerAddress':
                        $platformInformation->setCentralServerAddress($platformValue);
                        break;
                    case 'apiUsername':
                        $platformInformation->setApiUsername($platformValue);
                        break;
                    case 'apiCredentials':
                        $platformInformation->setApiCredentials($platformValue);
                        break;
                    case 'apiScheme':
                        $platformInformation->setApiScheme($platformValue);
                        break;
                    case 'apiPort':
                        $platformInformation->setApiPort($platformValue);
                        break;
                    case 'apiPath':
                        $platformInformation->setApiPath($platformValue);
                        break;
                    case 'peerValidation':
                        if ($platformValue === true) {
                            $platformInformation->setApiPeerValidation('yes');
                        }
                        break;
                }
            }

        }
        return $platformInformation;
    }
}

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

namespace Centreon\Domain\PlatformInformation\UseCase\V21;

use Centreon\Domain\Proxy\Proxy;
use Centreon\Domain\RemoteServer\RemoteServerException;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\PlatformInformation\Model\PlatformInformation;
use Centreon\Domain\PlatformInformation\Model\InformationDtoFactory;
use Centreon\Domain\PlatformInformation\Interfaces\DtoValidatorInterface;
use Centreon\Domain\PlatformInformation\Model\PlatformInformationFactory;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationReadRepositoryInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationWriteRepositoryInterface;

class UpdatePartiallyPlatformInformation
{
    /**
     * @var PlatformInformationWriteRepositoryInterface
     */
    private $writeRepository;

    /**
     * @var PlatformInformationReadRepositoryInterface
     */
    private $readRepository;

    /**
     * @var ProxyServiceInterface
     */
    private $proxyService;

    /**
     * @var RemoteServerServiceInterface
     */
    private $remoteServerService;

    public function __construct(
        PlatformInformationWriteRepositoryInterface $writeRepository,
        PlatformInformationReadRepositoryInterface $readRepository,
        ProxyServiceInterface $proxyService,
        RemoteServerServiceInterface $remoteServerService
    ) {
        $this->writeRepository = $writeRepository;
        $this->readRepository = $readRepository;
        $this->proxyService = $proxyService;
        $this->remoteServerService = $remoteServerService;
    }

    /**
     * Array of all available validators for this use case.
     *
     * @var array
     */
    private $validators = [];

    /**
     * @param array $validators
     */
    public function addValidators(array $validators): void
    {
        foreach ($validators as $validator)
        {
            $this->addValidator($validator);
        }
    }

    /**
     * @param DtoValidatorInterface $dtoValidator
     */
    private function addValidator(DtoValidatorInterface $dtoValidator): void
    {
        $this->validators[] = $dtoValidator;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return FindHostCategoriesResponse
     * @throws \Exception
     */
    public function execute(array $request)
    {
        foreach ($this->validators as $validator) {
            $validator->validateOrFail($request);
        }
        $information = InformationDtoFactory::createFromRequest($request);

        foreach($information as $informationDto) {
            if ($informationDto->key === "proxy") {
                $this->updateProxyOptions($informationDto->value);
            }
            break;
        }
        $platformInformationUpdate = PlatformInformationFactory::create($information);
        $currentPlatformInformation = $this->readRepository->findPlatformInformation();
        //utiliser les services déjà existant
        $this->updatePlatformTypeOrFail($platformInformationUpdate, $currentPlatformInformation);
        //writeRepository->update
        $this->writeRepository->updatePlatformInformation($platformInformationUpdate);
    }

    /**
     * Update Proxy Options.
     *
     * @param array $proxyOptions
     */
    private function updateProxyOptions(array $proxyOptions): void
    {
        $proxy = new Proxy();
        if (isset($proxyOptions['host'])) {
            $proxy->setUrl($proxyOptions['host']);
        }
        if (isset($proxyOptions['scheme'])) {
            $proxy->setProtocol($proxyOptions['scheme']);
        }
        if (isset($proxyOptions['port'])) {
            $proxy->setPort($proxyOptions['port']);
        }
        if (isset($proxyOptions['user'])) {
            $proxy->setUser($proxyOptions['user']);
            if (isset($proxyOptions['password'])) {
                $proxy->setPassword($proxyOptions['password']);
            }
        }
        $this->proxyService->updateProxy($proxy);
    }

    /**
     * Update the Platform Type
     *
     * @param PlatformInformation $platformInformationUpdate
     * @param PlatformInformation $currentPlatformInformation
     * @throws RemoteServerException
     */
    private function updatePlatformTypeOrFail(
        PlatformInformation $platformInformationUpdate,
        PlatformInformation $currentPlatformInformation
    ): void {
        if ($platformInformationUpdate->isRemote() && !$currentPlatformInformation->isRemote()) {
            $this->convertCentralToRemote($platformInformationUpdate, $currentPlatformInformation);
        } elseif (!$platformInformationUpdate->isRemote() && $currentPlatformInformation->isRemote()) {
            /**
             * Use the current information as they contains all the information
             * required to remove the Remote to its Parent
             */
            $this->remoteServerService->convertRemoteToCentral($currentPlatformInformation);
        }
    }

    private function convertCentralToRemote(
        PlatformInformation $platformInformationUpdate,
        PlatformInformation $currentPlatformInformation
    ): void {
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
                _("Unable to convert in remote server, no Central to link provided")
            );
        }
    }
}
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

namespace Centreon\Domain\PlatformInformation\UseCase\V20;

use Centreon\Domain\PlatformInformation\Exception\PlatformInformationException;
use Centreon\Domain\Proxy\Proxy;
use Centreon\Domain\RemoteServer\RemoteServerException;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\PlatformInformation\Model\InformationFactory;
use Centreon\Domain\PlatformInformation\Model\PlatformInformation;
use Centreon\Domain\PlatformInformation\Interfaces\DtoValidatorInterface;
use Centreon\Domain\PlatformInformation\Model\PlatformInformationFactory;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationReadRepositoryInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationWriteRepositoryInterface;
use Centreon\Domain\PlatformInformation\Model\Information;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;

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

    /**
     * @var PlatformTopologyServiceInterface
     */
    private $platformTopologyService;

    public function __construct(
        PlatformInformationWriteRepositoryInterface $writeRepository,
        PlatformInformationReadRepositoryInterface $readRepository,
        ProxyServiceInterface $proxyService,
        RemoteServerServiceInterface $remoteServerService,
        PlatformTopologyServiceInterface $platformTopologyService
    ) {
        $this->writeRepository = $writeRepository;
        $this->readRepository = $readRepository;
        $this->proxyService = $proxyService;
        $this->remoteServerService = $remoteServerService;
        $this->platformTopologyService = $platformTopologyService;
    }

    /**
     * Array of all available validators for this use case.
     *
     * @var array<DtoValidatorInterface>
     */
    private $validators = [];

    /**
     * @param array<DtoValidatorInterface> $validators
     */
    public function addValidators(array $validators): void
    {
        foreach ($validators as $validator) {
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
     * @param array<string,mixed> $request
     * @throws \Throwable
     */
    public function execute(array $request): void
    {
        foreach ($this->validators as $validator) {
            $validator->validateOrFail($request);
        }

        /**
         * Create Information from Factory to be able to access them independently
         * and validate the length of each value.
         */
        $informationList = InformationFactory::createFromRequest($request);
        $platformInformationFactory = new PlatformInformationFactory($_ENV['APP_SECRET']);
        $platformInformationToUpdate = $platformInformationFactory->create($request);

        foreach ($informationList as $information) {
            if ($information->getKey() === "proxy") {
                $this->updateProxyOptions($information, $platformInformationToUpdate->getCentralServerAddress());
            }
        }

        if ($platformInformationToUpdate->getCentralServerAddress() !== null) {
            $this->validateCentralServerAddressOrFail($platformInformationToUpdate->getCentralServerAddress());
        }

        $currentPlatformInformation = $this->readRepository->findPlatformInformation();

        if ($platformInformationToUpdate->isRemote() !== null) {
            $this->updateRemoteOrCentralType($platformInformationToUpdate, $currentPlatformInformation);
        }

        $this->writeRepository->updatePlatformInformation($platformInformationToUpdate);
    }

    /**
     * Update Proxy Options.
     *
     * @param Information $proxyInformation
     * @param string|null $centralServerAddress
     * @throws \InvalidArgumentException
     */
    private function updateProxyOptions(Information $proxyInformation, ?string $centralServerAddress): void
    {
        /**
         * Verify that proxy address and central address are different before continue the update.
         */
        $proxyOptions = $proxyInformation->getValue();
        if ($centralServerAddress !== null && isset($proxyOptions['host'])) {
            $this->validateCentralServerAddressOrFail(
                $centralServerAddress,
                $proxyOptions['host']
            );
        }

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
     * @param PlatformInformation $platformInformationToUpdate
     * @param PlatformInformation $currentPlatformInformation
     * @throws RemoteServerException
     */
    private function updateRemoteOrCentralType(
        PlatformInformation $platformInformationToUpdate,
        PlatformInformation $currentPlatformInformation
    ): void {
        if ($platformInformationToUpdate->isRemote()) {
            $this->convertCentralToRemote($platformInformationToUpdate, $currentPlatformInformation);
        } elseif (!$platformInformationToUpdate->isRemote() && $currentPlatformInformation->isRemote()) {
            /**
             * Use the current information
             * as they contains all the information required to remove the Remote to its Parent
             */
            $this->remoteServerService->convertRemoteToCentral($currentPlatformInformation);
        }
    }

    /**
     * This method verify the existing and updated type before sending information to Remote Server Service.
     *
     * @param PlatformInformation $platformInformationToUpdate
     * @param PlatformInformation $currentPlatformInformation
     * @throws RemoteServerException
     * @return void
     */
    private function convertCentralToRemote(
        PlatformInformation $platformInformationToUpdate,
        PlatformInformation $currentPlatformInformation
    ): void {
        if ($platformInformationToUpdate->getCentralServerAddress() !== null) {
            $this->remoteServerService->convertCentralToRemote(
                $platformInformationToUpdate
            );
        /**
         * If the updated information as no Central Server Address, check in the existing information if its
         * provided.
         */
        } elseif ($currentPlatformInformation->getCentralServerAddress() !== null) {
            $platformInformationToUpdate->setCentralServerAddress(
                $currentPlatformInformation->getCentralServerAddress()
            );
            $this->remoteServerService->convertCentralToRemote(
                $platformInformationToUpdate
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

    /**
     * Validate that central server address is not already in used by another platform or by the proxy.
     *
     * @param string $centralServerAddress
     * @param string|null $proxyAddress
     * @throws PlatformInformationException
     */
    private function validateCentralServerAddressOrFail(
        string $centralServerAddress,
        ?string $proxyAddress = null
    ): void {
        $topology = $this->platformTopologyService->getPlatformTopology();
        foreach ($topology as $platform) {
            if ($centralServerAddress === $platform->getAddress()) {
                throw new PlatformInformationException(
                    sprintf(
                        _('the address %s is already used in the topology and can\'t ' .
                        'be provided as Central Server Address'),
                        $centralServerAddress
                    )
                );
            }
        }
        if ($centralServerAddress === $proxyAddress) {
            throw new PlatformInformationException(
                sprintf(
                    _('the address %s is already used has proxy address and can\'t ' .
                    'be provided as Central Server Address'),
                    $centralServerAddress
                )
            );
        }
    }
}

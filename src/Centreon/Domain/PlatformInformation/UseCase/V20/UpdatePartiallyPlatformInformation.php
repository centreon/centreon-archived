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

use Centreon\Domain\Proxy\Proxy;
use Centreon\Domain\RemoteServer\RemoteServerException;
use Centreon\Domain\PlatformInformation\Model\Information;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\PlatformInformation\Model\InformationFactory;
use Centreon\Domain\PlatformInformation\Model\PlatformInformation;
use Centreon\Domain\PlatformInformation\Interfaces\DtoValidatorInterface;
use Centreon\Domain\PlatformInformation\Model\PlatformInformationFactory;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerServiceInterface;
use Centreon\Domain\PlatformInformation\Exception\PlatformInformationException;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
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

    /**
     * @var PlatformTopologyServiceInterface
     */
    private $platformTopologyService;

    /**
     * Array of all available validators for this use case.
     *
     * @var array<DtoValidatorInterface>
     */
    private $validators = [];

    /**
     * @var string|null
     */
    private $encryptionFirstKey;

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

    public function setEncryptionFirstKey(?string $encryptionFirstKey): void
    {
        $this->encryptionFirstKey = $encryptionFirstKey;
    }

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
        $informationList = InformationFactory::createFromDto($request);
        $currentPlatformInformation = $this->readRepository->findPlatformInformation();
        $platformInformationFactory = new PlatformInformationFactory($this->encryptionFirstKey);

        /**
         * Take account if the isRemote request key is provided to create the PlatformInformation and trigger
         * the conversion.
         * If this key is not provided in the request, we dont want to convert the platform
         * and just update the informations.
         */
        if (
            (isset($request["isRemote"]) && $request["isRemote"] === true)
            || (!isset($request["isRemote"]) && $currentPlatformInformation->isRemote() === true)
        ) {
            $platformInformationToUpdate = $platformInformationFactory->createRemoteInformation($request);
        } else {
            $platformInformationToUpdate = $platformInformationFactory->createCentralInformation();
        }
        if (isset($request["isRemote"])) {
            $this->updateRemoteOrCentralType($platformInformationToUpdate, $currentPlatformInformation);
        }

        foreach ($informationList as $information) {
            if ($information->getKey() === "proxy") {
                $this->updateProxyOptions($information, $platformInformationToUpdate->getCentralServerAddress());
            }
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
        } elseif ($platformInformationToUpdate->isRemote() === false && $currentPlatformInformation->isRemote()) {
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
        /**
         * If some parameters required fort registering the Remote Server are missing,
         * populate them with existing values.
         */
        $platformInformationToUpdate = $this->populateMissingInformationValues(
            $platformInformationToUpdate,
            $currentPlatformInformation
        );
        $this->remoteServerService->convertCentralToRemote(
            $platformInformationToUpdate
        );
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
        $platforms = $this->platformTopologyService->getPlatformTopology();
        foreach ($platforms as $platform) {
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

    /**
     * Populate the PlatformInformation missing values.
     * This method is useful if some properties are already existing in data storage.
     *
     * @param PlatformInformation $platformInformationToUpdate
     * @param PlatformInformation $currentPlatformInformation
     * @return PlatformInformation
     */
    private function populateMissingInformationValues(
        PlatformInformation $platformInformationToUpdate,
        PlatformInformation $currentPlatformInformation
    ): PlatformInformation {
        if ($platformInformationToUpdate->getCentralServerAddress() !== null) {
            $this->validateCentralServerAddressOrFail($platformInformationToUpdate->getCentralServerAddress());
        }
        if ($platformInformationToUpdate->getCentralServerAddress() === null) {
            $platformInformationToUpdate->setCentralServerAddress(
                $currentPlatformInformation->getCentralServerAddress()
            );
        }
        if ($platformInformationToUpdate->getApiCredentials() === null) {
            $platformInformationToUpdate->setApiCredentials(
                $currentPlatformInformation->getApiCredentials()
            );
        }
        if ($platformInformationToUpdate->getApiUsername() === null) {
            $platformInformationToUpdate->setApiUsername(
                $currentPlatformInformation->getApiUsername()
            );
        }
        if ($platformInformationToUpdate->getApiPath() === null) {
            $platformInformationToUpdate->setApiPath(
                $currentPlatformInformation->getApiPath()
            );
        }
        if ($platformInformationToUpdate->getApiScheme() === null) {
            $platformInformationToUpdate->setApiScheme(
                $currentPlatformInformation->getApiScheme()
            );
        }
        if ($platformInformationToUpdate->getApiPort() === null) {
            $platformInformationToUpdate->setApiPort(
                $currentPlatformInformation->getApiPort()
            );
        }

        return $platformInformationToUpdate;
    }
}

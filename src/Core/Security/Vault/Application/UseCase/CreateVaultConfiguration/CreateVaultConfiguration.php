<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Security\Vault\Application\UseCase\CreateVaultConfiguration;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\CreatedResponse;
use Core\Security\Vault\Domain\Model\NewVaultConfiguration;
use Core\Application\Common\UseCase\InvalidArgumentResponse;
use Core\Security\Vault\Domain\Exceptions\VaultConfigurationException;
use Core\Security\Vault\Application\Repository\ReadVaultConfigurationRepositoryInterface;
use Core\Security\Vault\Application\Repository\WriteVaultConfigurationRepositoryInterface;

class CreateVaultConfiguration
{
    use LoggerTrait;

    /**
     * @param ReadVaultConfigurationRepositoryInterface $readRepository
     * @param WriteVaultConfigurationRepositoryInterface $writeRepository
     */
    public function __construct(
        private ReadVaultConfigurationRepositoryInterface $readRepository,
        private WriteVaultConfigurationRepositoryInterface $writeRepository,
    ) {

    }

    /**
     * @param CreateVaultConfigurationPresenterInterface $presenter
     * @param CreateVaultConfigurationRequest $createVaultConfigurationRequest
     */
    public function __invoke(
        CreateVaultConfigurationPresenterInterface $presenter,
        CreateVaultConfigurationRequest $createVaultConfigurationRequest
    ): void {
        try {
            if (
                $this->isSameVaultConfigurationExists(
                    $createVaultConfigurationRequest->address,
                    $createVaultConfigurationRequest->port,
                    $createVaultConfigurationRequest->storage,
                )
            ) {
                $presenter->setResponseStatus(
                    new InvalidArgumentResponse(VaultConfigurationException::configurationExists()->getMessage())
                );

                return;
            }

            $newVaultConfiguration = new NewVaultConfiguration(
                $createVaultConfigurationRequest->name,
                $createVaultConfigurationRequest->address,
                $createVaultConfigurationRequest->type,
                $createVaultConfigurationRequest->port,
                $createVaultConfigurationRequest->storage
            );

            $this->writeRepository->createVaultConfiguration($newVaultConfiguration);
            $presenter->setResponseStatus(new CreatedResponse());
        } catch (VaultConfigurationException $ex) {
            $this->error('Some parameters are not valid', ['trace' => (string) $ex]);
            $presenter->setResponseStatus(
                new InvalidArgumentResponse($ex->getMessage())
            );

            return;
        } catch (\Throwable $ex) {
            $this->error(
                'An error occured in while creating vault configuration',
                ['trace' => (string) $ex]
            );
            $presenter->setResponseStatus(
                new ErrorResponse(VaultConfigurationException::impossibleToCreate()->getMessage())
            );

            return;
        }
    }

    /**
     * Checks if same vault configuration exists
     *
     * @param string $address
     * @param int $port
     * @param string $storage
     * @param CreateVaultConfigurationPresenterInterface $presenter
     * @return bool
     * @throws \Throwable
     */
    private function isSameVaultConfigurationExists(
        string $address,
        int $port,
        string $storage,
    ): bool {
        if (
            $this->readRepository->findVaultConfigurationByAddressAndPortAndStorage($address, $port, $storage) !== null
        ) {
            $this->error(
                'Vault configuration with these properties already exists',
                [
                    'address' => $address,
                    'port' => $port,
                    'storage' => $storage
                ]
            );

            return true;
        }

        return false;
    }
}

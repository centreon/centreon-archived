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
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Security\Vault\Domain\Model\NewVaultConfiguration;
use Core\Application\Common\UseCase\InvalidArgumentResponse;
use Core\Security\Vault\Domain\Model\VaultConfigurationFactory;
use Core\Security\Vault\Domain\Exceptions\VaultConfigurationException;
use Core\Security\Vault\Application\Interface\VaultHealthCheckerInterface;
use Core\Security\Vault\Application\Repository\ReadVaultConfigurationRepositoryInterface;
use Core\Security\Vault\Application\Repository\WriteVaultConfigurationRepositoryInterface;
use Core\Security\Vault\Infrastructure\Exceptions\VaultHealthCheckerException;

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
        private VaultHealthCheckerInterface $vaultHealthChecker
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
                    new InvalidArgumentResponse(_('Vault configuration with these properties already exists'))
                );

                return;
            }

            $newVaultConfiguration = VaultConfigurationFactory::createNewVaultConfiguration(
                $createVaultConfigurationRequest
            );
            if (! $this->isHealthCheckValid($newVaultConfiguration)) {
                // What kind of response should be returned
                $presenter->setResponseStatus(
                    new InvalidArgumentResponse(
                        _(sprintf(
                            'Vault health check response is not an OK status: %d given, 200 expected',
                            $this->vaultHealthChecker->getStatusCode()
                        ))
                    )
                );

                return;
            }
            $this->writeRepository->createVaultConfiguration($newVaultConfiguration);
            // replace hardcoded error msgs by $ex->getMsg
        } catch (AssertionException|VaultConfigurationException $ex) {
            $this->error('Some parameters are not valid', ['trace' => (string) $ex]);
            $presenter->setResponseStatus(
                new InvalidArgumentResponse(_('Some parameters are not valid'))
            );

            return;
        } catch (VaultHealthCheckerException $ex) {
            $this->error('Unable to check vault\'s health', ['trace' => (string) $ex]);
            $presenter->setResponseStatus(
                new ErrorResponse(_('Unable to check vault\'s health'))
            );

            return;
        } catch (\Throwable $ex) {
            $this->error(
                'An error occured in while creating vault configuration',
                ['trace' => (string) $ex]
            );
            $presenter->setResponseStatus(
                new ErrorResponse(_('Impossible to create vault configuration'))
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

    /**
     * Checks if vault health is OK
     *
     * @param NewVaultConfiguration $newVaultConfiguration
     * @param CreateVaultConfigurationPresenterInterface $presenter
     * @return bool
     * @throws VaultHealthCheckerException
     */
    private function isHealthCheckValid(
        NewVaultConfiguration $newVaultConfiguration,
    ): bool {
        $vaultHealthCheckAddress = $newVaultConfiguration->getAddress() . ':' . $newVaultConfiguration->getPort()
            . NewVaultConfiguration::ENDPOINTS_BY_TYPE[$newVaultConfiguration->getType()];

        // change: sendRequest... method to isVaultHealthCheckValid()
        $this->vaultHealthChecker->sendRequestToHealthEndpoint($vaultHealthCheckAddress);
        if ($this->vaultHealthChecker->getStatusCode() !== 200) {
            $this->error(
                'Vault health check response is not an OK status',
                [
                    'address' => $vaultHealthCheckAddress,
                    'statusCode' => $this->vaultHealthChecker->getStatusCode()
                ]
            );

            return false;
        }

        return true;
    }
}

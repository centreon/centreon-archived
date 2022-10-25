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

namespace Core\Security\Vault\Domain\Model;

use Core\Security\Vault\Application\UseCase\CreateVaultConfiguration\CreateVaultConfigurationRequest;
use Core\Security\Vault\Domain\Model\NewVaultConfiguration;
use Core\Security\Vault\Domain\Exceptions\VaultConfigurationException;

class VaultConfigurationFactory
{
    /**
     * Create a new vault configuration
     *
     * @param CreateVaultConfigurationRequest $request
     * @return NewVaultConfiguration
     * @throws VaultConfigurationException
     */
    public static function createNewVaultConfiguration(CreateVaultConfigurationRequest $request): NewVaultConfiguration
    {
        self::validateParameters($request->address, $request->type);

        return new NewVaultConfiguration(
            $request->name,
            $request->type,
            $request->address,
            $request->port,
            $request->storage
        );
    }

    /**
     * This method validates that address is correct and the vault type is valid
     *
     * @param string $address
     * @param string $type
     * @throws VaultConfigurationException
     */
    private static function validateParameters(string $address, string $type)
    {
        $errors = [];
        if (
            filter_var($address, FILTER_VALIDATE_IP) === false
            && filter_var($address, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
        ) {
            $errors[] = 'address';
        }
        if (! in_array($type, NewVaultConfiguration::ALLOWED_TYPES)) {
            $errors[] = 'type';
        }

        if (! empty($errors)) {
            throw VaultConfigurationException::invalidParameters($errors);
        }
    }
}

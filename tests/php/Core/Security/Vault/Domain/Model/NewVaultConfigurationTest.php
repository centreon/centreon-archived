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

namespace Tests\Core\Security\Vault\Domain\Model;

use Core\Security\Vault\Domain\Exceptions\VaultConfigurationException;
use Core\Security\Vault\Domain\Model\NewVaultConfiguration;

it('should throw VaultConfigurationException when vault name is empty', function (): void {
    new NewVaultConfiguration(
        '',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId'
    );
})->throws(VaultConfigurationException::class, VaultConfigurationException::invalidParameters(['name'])->getMessage());

it('should throw VaultConfigurationException when vault type is invalid', function (): void {
    new NewVaultConfiguration(
        'myVault',
        'myVaultType',
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId'
    );
})->throws(VaultConfigurationException::class, VaultConfigurationException::invalidParameters(['type'])->getMessage());

it('should throw VaultConfigurationException when vault address is \'._@\'', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '._@',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId'
    );
})->throws(
    VaultConfigurationException::class,
    VaultConfigurationException::invalidParameters(['address'])->getMessage()
);

it('should throw VaultConfigurationException when vault storage is empty', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        '',
        'myRoleId',
        'mySecretId'
    );
})->throws(
    VaultConfigurationException::class,
    VaultConfigurationException::invalidParameters(['storage'])->getMessage()
);

it('should throw VaultConfigurationException when vault role id is empty', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        'myStorage',
        '',
        'mySecretId'
    );
})->throws(
    VaultConfigurationException::class,
    VaultConfigurationException::invalidParameters(['role_id'])->getMessage()
);

it('should throw VaultConfigurationException when vault secret id is empty', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        ''
    );
})->throws(
    VaultConfigurationException::class,
    VaultConfigurationException::invalidParameters(['secret_id'])->getMessage()
);

it('should return an instance of NewVaultConfiguration when all vault parametes are valid', function (): void {
    $newVaultConfiguration = new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId'
    );

    expect($newVaultConfiguration->getName())->toBe('myVault');
    expect($newVaultConfiguration->getType())->toBe(NewVaultConfiguration::TYPE_HASHICORP);
    expect($newVaultConfiguration->getAddress())->toBe('127.0.0.1');
    expect($newVaultConfiguration->getPort())->toBe(8200);
    expect($newVaultConfiguration->getStorage())->toBe('myStorage');
    expect($newVaultConfiguration->getRoleId())->toBe('myRoleId');
    expect($newVaultConfiguration->getSecretId())->toBe('mySecretId');
});

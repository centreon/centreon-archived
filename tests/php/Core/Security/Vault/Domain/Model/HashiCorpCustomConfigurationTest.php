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
use Core\Security\Vault\Domain\Model\HashiCorpCustomConfiguration;

it('should throw VaultConfigurationException when role_id is empty', function (): void {
    new HashiCorpCustomConfiguration('', 'mySecretId');
})->throws(
    VaultConfigurationException::class,
    VaultConfigurationException::invalidParameters(['role_id'])->getMessage()
);

it('should throw VaultConfigurationException when secret_id is empty', function (): void {
    new HashiCorpCustomConfiguration('myRoleId', '');
})->throws(
    VaultConfigurationException::class,
    VaultConfigurationException::invalidParameters(['secret_id'])->getMessage()
);

it('should return an instance of HashiCorpVaultConfiguration when role_id and secret_id are valid', function (): void {
    $customConfiguration = new HashiCorpCustomConfiguration('myRoleId', 'mySecretId');

    expect($customConfiguration->getRoleId())->toBe('myRoleId');
    expect($customConfiguration->getSecretId())->toBe('mySecretId');
});

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

namespace Tests\Core\Security\Vault\Application\UseCase\CreateVaultConfiguration;

use Core\Security\Vault\Domain\Model\NewVaultConfiguration;
use Core\Security\Vault\Application\UseCase\CreateVaultConfiguration\{
    NewVaultConfigurationFactory,
    CreateVaultConfigurationRequest
};
use Security\Encryption;

it(
    'should return an instance of NewVaultConfiguration when a valid request is passed to create method',
    function (): void {
        $encryption = new Encryption();
        $factory = new NewVaultConfigurationFactory(
            $encryption->setFirstKey('myFirstKey')->setSecondKey('mySecondKey')
        );
        $createVaultConfiguraitonRequest = new CreateVaultConfigurationRequest();
        $createVaultConfiguraitonRequest->name = 'myVault';
        $createVaultConfiguraitonRequest->type = NewVaultConfiguration::TYPE_HASHICORP;
        $createVaultConfiguraitonRequest->address = '127.0.0.1';
        $createVaultConfiguraitonRequest->port = 8200;
        $createVaultConfiguraitonRequest->storage = 'myStorage';
        $createVaultConfiguraitonRequest->roleId = 'myRoleId';
        $createVaultConfiguraitonRequest->secretId = 'mySecretId';

        $newVaultConfiguration = $factory->create($createVaultConfiguraitonRequest);

        expect($newVaultConfiguration)->toBeInstanceOf(NewVaultConfiguration::class);
    }
);

it('should encrypt roleId and secretId correctly', function (): void {
    $encryption = new Encryption();
    $encryption = $encryption->setFirstKey('myFirstKey')->setSecondKey('mySecondKey');
    $factory = new NewVaultConfigurationFactory($encryption);
    $createVaultConfiguraitonRequest = new CreateVaultConfigurationRequest();
    $createVaultConfiguraitonRequest->name = 'myVault';
    $createVaultConfiguraitonRequest->type = NewVaultConfiguration::TYPE_HASHICORP;
    $createVaultConfiguraitonRequest->address = '127.0.0.1';
    $createVaultConfiguraitonRequest->port = 8200;
    $createVaultConfiguraitonRequest->storage = 'myStorage';
    $createVaultConfiguraitonRequest->roleId = 'myRoleId';
    $createVaultConfiguraitonRequest->secretId = 'mySecretId';

    $newVaultConfiguration = $factory->create($createVaultConfiguraitonRequest);

    $roleId = $encryption->decrypt($newVaultConfiguration->getRoleId());
    $secretId = $encryption->decrypt($newVaultConfiguration->getSecretId());

    expect($roleId)->toBe($createVaultConfiguraitonRequest->roleId);
    expect($secretId)->toBe($createVaultConfiguraitonRequest->secretId);
});

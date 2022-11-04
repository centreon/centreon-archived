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

use Assert\InvalidArgumentException;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Security\Vault\Domain\Model\NewVaultConfiguration;

$invalidMaxLengthString = '';
for ($index = 0; $index <= NewVaultConfiguration::MAX_LENGTH; $index++) {
    $invalidMaxLengthString .= 'a';
}

it('should throw InvalidArgumentException when vault name is empty', function (): void {
    new NewVaultConfiguration(
        '',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId',
        'mySalt'
    );
})->throws(InvalidArgumentException::class, AssertionException::notEmpty('NewVaultConfiguration::name')->getMessage());

it(
    'should throw InvalidArgumentException when vault name exceeds allowed max length',
    function () use ($invalidMaxLengthString): void {
        new NewVaultConfiguration(
            $invalidMaxLengthString,
            NewVaultConfiguration::TYPE_HASHICORP,
            '127.0.0.1',
            8200,
            'myStorage',
            'myRoleId',
            'mySecretId',
            'mySalt'
        );
    }
)->throws(
    InvalidArgumentException::class,
    AssertionException::maxLength(
        $invalidMaxLengthString,
        strlen($invalidMaxLengthString),
        NewVaultConfiguration::MAX_LENGTH,
        'NewVaultConfiguration::name'
    )->getMessage()
);

it('should throw InvalidArgumentException when vault type is empty', function (): void {
    new NewVaultConfiguration(
        'myVault',
        '',
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId',
        'mySalt'
    );
})->throws(InvalidArgumentException::class, AssertionException::notEmpty('NewVaultConfiguration::type')->getMessage());

it('should throw InvalidArgumentException when vault type is invalid', function (): void {
    new NewVaultConfiguration(
        'myVault',
        'myVaultType',
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId',
        'mySalt'
    );
})->throws(
    InvalidArgumentException::class,
    AssertionException::inArray(
        'myVaultType',
        NewVaultConfiguration::ALLOWED_TYPES,
        'NewVaultConfiguration::type'
    )->getMessage()
);

it('should throw InvalidArgumentException when vault address is empty', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId',
        'mySalt'

    );
})->throws(
    InvalidArgumentException::class,
    AssertionException::notEmpty('NewVaultConfiguration::address')->getMessage()
);

it('should throw AssertionException when vault address is \'._@\'', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '._@',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId',
        'mySalt'
    );
})->throws(
    AssertionException::class,
    AssertionException::ipOrDomain('._@', 'NewVaultConfiguration::address')->getMessage()
);

it('should throw InvalidArgumentException when vault port value is lower than allowed range', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        0,
        'myStorage',
        'myRoleId',
        'mySecretId',
        'mySalt'
    );
})->throws(
    InvalidArgumentException::class,
    AssertionException::min(
        NewVaultConfiguration::MIN_PORT_VALUE - 1,
        NewVaultConfiguration::MIN_PORT_VALUE,
        'NewVaultConfiguration::port'
    )->getMessage()
);

it('should throw InvalidArgumentException when vault port exceeds allowed range', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        NewVaultConfiguration::MAX_PORT_VALUE + 1,
        'myStorage',
        'myRoleId',
        'mySecretId',
        'mySalt'
    );
})->throws(
    InvalidArgumentException::class,
    AssertionException::max(
        NewVaultConfiguration::MAX_PORT_VALUE + 1,
        NewVaultConfiguration::MAX_PORT_VALUE,
        'NewVaultConfiguration::port'
    )->getMessage()
);

it('should throw InvalidArgumentException when vault storage is empty', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        '',
        'myRoleId',
        'mySecretId',
        'mySalt'
    );
})->throws(
    InvalidArgumentException::class,
    AssertionException::notEmpty('NewVaultConfiguration::storage')->getMessage()
);

it(
    'should throw InvalidArgumentException when vault storage exeeds allowed max length',
    function () use ($invalidMaxLengthString): void {
        new NewVaultConfiguration(
            'myVault',
            NewVaultConfiguration::TYPE_HASHICORP,
            '127.0.0.1',
            8200,
            $invalidMaxLengthString,
            'myRoleId',
            'mySecretId',
            'mySalt'
        );
    }
)->throws(
    InvalidArgumentException::class,
    AssertionException::maxLength(
        $invalidMaxLengthString,
        strlen($invalidMaxLengthString),
        NewVaultConfiguration::MAX_LENGTH,
        'NewVaultConfiguration::storage'
    )->getMessage()
);

it('should throw InvalidArgumentException when vault role id is empty', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        'myStorage',
        '',
        'mySecretId',
        'mySalt'
    );
})->throws(
    InvalidArgumentException::class,
    AssertionException::notEmpty('NewVaultConfiguration::roleId')->getMessage()
);

it(
    'should throw InvalidArgumentException when vault role id exeeds allowed max length',
    function () use ($invalidMaxLengthString): void {
        new NewVaultConfiguration(
            'myVault',
            NewVaultConfiguration::TYPE_HASHICORP,
            '127.0.0.1',
            8200,
            'myStorage',
            $invalidMaxLengthString,
            'mySecretId',
            'mySalt'
        );
    }
)->throws(
    InvalidArgumentException::class,
    AssertionException::maxLength(
        $invalidMaxLengthString,
        strlen($invalidMaxLengthString),
        NewVaultConfiguration::MAX_LENGTH,
        'NewVaultConfiguration::roleId'
    )->getMessage()
);

it('should throw InvalidArgumentException when vault secret id is empty', function (): void {
    new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        '',
        'mySalt'
    );
})->throws(
    InvalidArgumentException::class,
    AssertionException::notEmpty('NewVaultConfiguration::secretId')->getMessage()
);

it(
    'should throw InvalidArgumentException when vault secret id exeeds allowed max length',
    function () use ($invalidMaxLengthString): void {
        new NewVaultConfiguration(
            'myVault',
            NewVaultConfiguration::TYPE_HASHICORP,
            '127.0.0.1',
            8200,
            'myStorage',
            'myRoleId',
            $invalidMaxLengthString,
            'mySalt'
        );
    }
)->throws(
    InvalidArgumentException::class,
    AssertionException::maxLength(
        $invalidMaxLengthString,
        strlen($invalidMaxLengthString),
        NewVaultConfiguration::MAX_LENGTH,
        'NewVaultConfiguration::secretId'
    )->getMessage()
);

it('should return an instance of NewVaultConfiguration when all vault parametes are valid', function (): void {
    $newVaultConfiguration = new NewVaultConfiguration(
        'myVault',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId',
        'mySalt'
    );

    expect($newVaultConfiguration->getName())->toBe('myVault');
    expect($newVaultConfiguration->getType())->toBe(NewVaultConfiguration::TYPE_HASHICORP);
    expect($newVaultConfiguration->getAddress())->toBe('127.0.0.1');
    expect($newVaultConfiguration->getPort())->toBe(8200);
    expect($newVaultConfiguration->getStorage())->toBe('myStorage');
    expect($newVaultConfiguration->getRoleId())->toBe('myRoleId');
    expect($newVaultConfiguration->getSecretId())->toBe('mySecretId');
});

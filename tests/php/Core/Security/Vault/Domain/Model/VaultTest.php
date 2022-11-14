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
use Core\Security\Vault\Domain\Model\Vault;

$invalidMinLengthString = '';
$invalidMaxLengthString = '';
for ($index = 0; $index <= Vault::MAX_LENGTH; $index++) {
    $invalidMaxLengthString .= 'a';
}

it('should throw an InvalidArgumentException when vault id is lower than allowed minimum', function (): void {
    new Vault(0, 'myVault');
})->throws(
    InvalidArgumentException::class,
    AssertionException::min(
        Vault::MIN_ID - 1,
        Vault::MIN_ID,
        'Vault::id'
    )->getMessage()
);

it(
    'should throw InvalidArgumentException when vault name is empty',
    function () use ($invalidMinLengthString): void {
        new Vault(Vault::MIN_ID, $invalidMinLengthString);
    }
)->throws(
    InvalidArgumentException::class,
    AssertionException::minLength(
        $invalidMinLengthString,
        strlen($invalidMinLengthString),
        Vault::MIN_LENGTH,
        'Vault::name'
    )->getMessage()
);

it(
    'should throw InvalidArgumentException when vault name exceeds allowed max length',
    function () use ($invalidMaxLengthString): void {
        new Vault(Vault::MIN_ID, $invalidMaxLengthString);
    }
)->throws(
    InvalidArgumentException::class,
    AssertionException::maxLength(
        $invalidMaxLengthString,
        strlen($invalidMaxLengthString),
        Vault::MAX_LENGTH,
        'Vault::name'
    )->getMessage()
);

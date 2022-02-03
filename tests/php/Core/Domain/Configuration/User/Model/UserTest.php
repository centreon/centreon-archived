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

namespace Tests\Core\Infrastructure\Security\ProviderConfiguration\Local\Api\FindConfiguration;

use PHPUnit\Framework\TestCase;
use Core\Domain\Configuration\User\Model\User;
use Centreon\Domain\Common\Assertion\AssertionException;

class UserTest extends TestCase
{
    /**
     * Test user creation with empty alias
     */
    public function testAliasEmpty(): void
    {
        $alias = '';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $alias,
                strlen($alias),
                User::MIN_ALIAS_LENGTH,
                'User::alias'
            )->getMessage()
        );

        new User(
            1,
            $alias,
            'name',
            'email',
            false,
        );
    }

    /**
     * Test user creation with too long alias
     */
    public function testAliasTooLong(): void
    {
        $alias = str_repeat('a', 256);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $alias,
                strlen($alias),
                User::MAX_ALIAS_LENGTH,
                'User::alias'
            )->getMessage()
        );

        new User(
            1,
            $alias,
            'name',
            'email',
            false,
        );
    }

    /**
     * Test user creation with empty name
     */
    public function testNameEmpty(): void
    {
        $name = '';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $name,
                strlen($name),
                User::MIN_NAME_LENGTH,
                'User::name'
            )->getMessage()
        );

        new User(
            1,
            'alias',
            $name,
            'email',
            false,
        );
    }

    /**
     * Test user creation with too long name
     */
    public function testNameTooLong(): void
    {
        $name = str_repeat('a', 256);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                User::MAX_NAME_LENGTH,
                'User::name'
            )->getMessage()
        );

        new User(
            1,
            'alias',
            $name,
            'email',
            false,
        );
    }

    /**
     * Test user creation with empty email
     */
    public function testEmailEmpty(): void
    {
        $email = '';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $email,
                strlen($email),
                User::MIN_EMAIL_LENGTH,
                'User::email'
            )->getMessage()
        );

        new User(
            1,
            'alias',
            'name',
            $email,
            false,
        );
    }

    /**
     * Test user creation with too long email
     */
    public function testEmailTooLong(): void
    {
        $email = str_repeat('a', 256) . '@centreon.com';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $email,
                strlen($email),
                User::MAX_EMAIL_LENGTH,
                'User::email'
            )->getMessage()
        );

        new User(
            1,
            'alias',
            'name',
            $email,
            false,
        );
    }

    /**
     * Test user creation
     */
    public function testUserCreation(): void
    {
        $id = 1;
        $alias = 'alias';
        $name = 'name';
        $email = 'root@localhost';
        $isAdmin = true;

        $user = new User(
            $id,
            $alias,
            $name,
            $email,
            $isAdmin,
        );

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($alias, $user->getAlias());
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($isAdmin, $user->isAdmin());
    }
}

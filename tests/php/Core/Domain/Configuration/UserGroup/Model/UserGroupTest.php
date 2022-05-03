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

namespace Tests\Core\Domain\Configuration\UserGroup\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use PHPUnit\Framework\TestCase;
use Core\Domain\Configuration\UserGroup\Model\UserGroup;

class UserGroupTest extends TestCase
{
    /**
     * test name empty exception
     */
    public function testNameEmpty(): void
    {
        $name = '';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::notEmpty(
                'UserGroup::name'
            )->getMessage()
        );

        new UserGroup(1, $name, 'userGroupAlias');
    }

    /**
     * test name too long exception
     */
    public function testNameTooLong(): void
    {
        $name = str_repeat('.', UserGroup::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                UserGroup::MAX_NAME_LENGTH,
                'UserGroup::name'
            )->getMessage()
        );
        new UserGroup(1, $name, 'userGroupAlias');
    }

    /**
     * test alias empty exception
     */
    public function testAliasEmpty(): void
    {
        $alias = '';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::notEmpty(
                'UserGroup::alias'
            )->getMessage()
        );

        new UserGroup(1, 'userGroupName', $alias);
    }

    /**
     * test alias too long exception
     */
    public function testAliasTooLong(): void
    {
        $alias = str_repeat('.', UserGroup::MAX_ALIAS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $alias,
                strlen($alias),
                UserGroup::MAX_ALIAS_LENGTH,
                'UserGroup::alias'
            )->getMessage()
        );
        new UserGroup(1, 'userGroupName', $alias);
    }

    /**
     * @return UserGroup
     */
    public static function createUserGroupModel(): UserGroup
    {
        return new UserGroup(10, 'userGroupName', 'userGroupAlias');
    }
}

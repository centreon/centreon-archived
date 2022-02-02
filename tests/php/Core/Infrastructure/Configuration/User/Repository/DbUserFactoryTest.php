<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Tests\Core\Infrastructure\Configuration\User\Repository;

use PHPUnit\Framework\TestCase;
use Core\Domain\Configuration\User\Model\User;
use Core\Infrastructure\Configuration\User\Repository\DbUserFactory;

class DbUserFactoryTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private $userRecord;

    public function setUp(): void
    {
        $this->userRecord = [
            'contact_id' => '1',
            'contact_alias' => 'alias',
            'contact_name' => 'name',
            'contact_email' => 'root@localhost',
            'contact_admin' => '1',
        ];
    }

    /**
     * Test that user is properly created
     */
    public function testUserCreation(): void
    {
        $user = DbUserFactory::createFromRecord($this->userRecord);

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('alias', $user->getAlias());
        $this->assertEquals(('name'), $user->getName());
        $this->assertEquals('root@localhost', $user->getEmail());
        $this->assertEquals(true, $user->isAdmin());
    }
}

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

namespace Core\Application\Configuration\User\Repository;

use Assert\AssertionFailedException;
use Core\Domain\Configuration\User\Model\User;

interface ReadUserRepositoryInterface
{
    /**
     * Find configured users
     *
     * @return User[]
     */
    public function findAllUsers(): array;

    /**
     * Find user ids from a list of alias
     *
     * @param string[] $userAliases
     * @return int[]
     */
    public function findUserIdsByAliases(array $userAliases): array;

    /**
     * Find user by its id
     *
     * @param int $userId
     * @return User|null
     * @throws AssertionFailedException
     */
    public function findById(int $userId): ?User;

    /**
     * Find all available themes.
     *
     * @return string[]
     */
    public function findAvailableThemes(): array;
}

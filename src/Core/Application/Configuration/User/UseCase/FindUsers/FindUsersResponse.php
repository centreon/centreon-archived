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

namespace Core\Application\Configuration\User\UseCase\FindUsers;

use Core\Domain\Configuration\User\Model\User;

class FindUsersResponse
{
    /**
     * @var array<string,mixed>
     */
    public $users;

    /**
     * @param User[] $users
     */
    public function __construct(array $users)
    {
        $this->users = $this->usersToArray($users);
    }

    /**
     * Converts an array of User models into an array
     *
     * @param User[] $users
     * @return array<string,mixed>
     */
    public function usersToArray(array $users): array
    {
        return array_map(
            fn (User $user) => [
                'id' => $user->getId(),
                'alias' => $user->getAlias(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'is_admin' => $user->isAdmin(),
            ],
            $users
        );
    }
}

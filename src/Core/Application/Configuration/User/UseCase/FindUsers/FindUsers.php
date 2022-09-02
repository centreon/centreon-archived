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

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Configuration\User\Repository\ReadUserRepositoryInterface;
use Core\Application\Configuration\User\UseCase\FindUsers\FindUsersPresenterInterface;
use Core\Domain\Configuration\User\Model\User;

class FindUsers
{
    use LoggerTrait;

    /**
     * @param ReadUserRepositoryInterface $usersRepository
     */
    public function __construct(private ReadUserRepositoryInterface $usersRepository)
    {
    }

    /**
     * @param FindUsersPresenterInterface $presenter
     */
    public function __invoke(FindUsersPresenterInterface $presenter): void
    {
        $this->debug('Searching for configured users');

        try {
            $users = $this->usersRepository->findAllUsers();
        } catch (\Throwable $ex) {
            $this->critical($ex->getMessage());
            $presenter->setResponseStatus(
                new FindUsersErrorResponse($ex->getMessage())
            );
            return;
        }

        $presenter->present($this->createResponse($users));
    }

    /**
     * @param User[] $users
     * @return FindUsersResponse
     */
    public function createResponse(array $users): FindUsersResponse
    {
        return new FindUsersResponse($users);
    }
}

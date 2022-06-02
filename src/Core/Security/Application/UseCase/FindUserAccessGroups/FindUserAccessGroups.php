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

namespace Core\Security\Application\UseCase\FindUserAccessGroups;

use Core\Application\Common\UseCase\ErrorResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Log\LoggerTrait;
use Core\Security\Application\Repository\ReadAccessGroupRepositoryInterface;

class FindUserAccessGroups
{
    use LoggerTrait;

    /**
     * @param ReadAccessGroupRepositoryInterface $repository
     * @param ContactInterface $user
     */
    public function __construct(private ReadAccessGroupRepositoryInterface $repository, private ContactInterface $user)
    {
    }

    /**
     * @param FindUserAccessGroupsPresenterInterface $presenter
     * @return void
     */
    public function __invoke(FindUserAccessGroupsPresenterInterface $presenter): void
    {
        try {
            if ($this->user->isAdmin()) {
                $accessGroups = $this->repository->findAll();
            } else {
                $accessGroups = $this->repository->findByContact($this->user);
            }
        } catch (\Throwable $ex) {
            $this->error(
                'An error occured in data storage while getting contact groups',
                ['trace' => $ex->getTraceAsString()]
            );
            $presenter->setResponseStatus(
                new ErrorResponse('Impossible to get contact groups from data storage')
            );

            return;
        }

        $presenter->present(new FindUserAccessGroupsResponse($accessGroups));
    }
}

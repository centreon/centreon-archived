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

namespace Core\Contact\Application\UseCase\FindContactGroups;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\Common\UseCase\ForbiddenResponse;
use Core\Contact\Application\Repository\ReadContactGroupRepositoryInterface;

class FindContactGroups
{
    use LoggerTrait;

    public function __construct(
        private ReadContactGroupRepositoryInterface $repository,
        private ContactInterface $user
    ) {
    }

    public function __invoke(FindContactGroupsPresenterInterface $presenter): void
    {
        try {
            if ($this->user->isAdmin()) {
                $contactGroups = $this->repository->findAll();
            } else {
                if (! $this->user->hasTopologyRole(Contact::ROLE_CONFIGURATION_USERS_CONTACT_GROUPS_READ)) {
                    $this->error('User doesn\'t have sufficient right to see contact groups', [
                        'user_id' => $this->user->getId(),
                    ]);
                    $presenter->setResponseStatus(
                        new ForbiddenResponse('You are not allowed to access contact groups')
                    );
                    return;
                }
                $contactGroups = $this->repository->findAllByUserId($this->user->getId());
            }
        } catch (\Throwable $ex) {
            $this->error('An error occured in data storage while getting contact groups', [
                'trace' => $ex->getTraceAsString()
            ]);
            $presenter->setResponseStatus(new ErrorResponse(
                'Impossible to get contact groups from data storage'
            ));
            return;
        }

        $presenter->present(new FindContactGroupsResponse());
    }
}

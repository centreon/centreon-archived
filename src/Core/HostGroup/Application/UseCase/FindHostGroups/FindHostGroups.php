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

namespace Core\HostGroup\Application\UseCase\FindHostGroups;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\HostGroup\Application\Repository\ReadHostGroupRepositoryInterface;
use Core\HostGroup\Domain\Model\HostGroup;

class FindHostGroups
{
    use LoggerTrait;

    /**
     * @param ReadHostGroupRepositoryInterface $readHostGroupRepository
     * @param ContactInterface $contact
     */
    public function __construct(
        private ReadHostGroupRepositoryInterface $readHostGroupRepository,
        private ContactInterface $contact
    ) {
    }

    /**
     * @param FindHostGroupsPresenterInterface $presenter
     */
    public function __invoke(FindHostGroupsPresenterInterface $presenter): void
    {
        try {
            $hostGroups = $this->contact->isAdmin()
                ? $this->readHostGroupRepository->findAllWithoutAcl()
                : $this->readHostGroupRepository->findAllWithAcl($this->contact);

            $presenter->present($this->createResponse($hostGroups));
        } catch (\Throwable $ex) {
            $presenter->setResponseStatus(new ErrorResponse('Error while searching for the hostgroups'));
            $this->error($ex->getMessage());
        }
    }

    /**
     * @param HostGroup[] $hostGroups
     *
     * @return FindHostGroupsResponse
     */
    private function createResponse(array $hostGroups): FindHostGroupsResponse
    {
        $response = new FindHostGroupsResponse();

        foreach ($hostGroups as $hostGroup) {
            $response->hostgroups[] = [
                'id' => $hostGroup->getId(),
                'name' => $hostGroup->getName(),
                'alias' => $hostGroup->getAlias(),
            ];
        }

        return $response;
    }
}

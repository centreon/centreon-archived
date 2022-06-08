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

use Core\Contact\Domain\Model\ContactGroup;

class FindContactGroupsResponse
{
    /**
     * @var array<array<string,string|int>>
     */
    public array $contactGroups;

    /**
     * @param array<ContactGroup> $contactGroups
     */
    public function __construct(array $contactGroups)
    {
        $this->contactGroups = $this->contactGroupsToArray($contactGroups);
    }

    /**
     * @param array<ContactGroup> $contactGroups
     * @return array<array<string,string|int>>
     */
    private function contactGroupsToArray(array $contactGroups): array
    {
        return array_map(
            fn (ContactGroup $contactGroup) => [
                'id' => $contactGroup->getId(),
                'name' => $contactGroup->getName(),
            ],
            $contactGroups
        );
    }
}

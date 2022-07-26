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

namespace Core\Security\AccessGroup\Application\UseCase\FindLocalUserAccessGroups;

use Core\Security\AccessGroup\Domain\Model\AccessGroup;

class FindLocalUserAccessGroupsResponse
{
    /**
     * @var array<array<string,mixed>>
     */
    public array $accessGroups;

    /**
     * @param array<AccessGroup> $accessGroups
     */
    public function __construct(array $accessGroups)
    {
        $this->accessGroups = $this->accessGroupsToArray($accessGroups);
    }

    /**
     * @param array<AccessGroup> $accessGroups
     * @return array<array<string,mixed>>
     */
    private function accessGroupsToArray(array $accessGroups): array
    {
        return array_map(
            fn (AccessGroup $accessGroup) => [
                'id' => $accessGroup->getId(),
                'name' => $accessGroup->getName(),
                'alias' => $accessGroup->getAlias(),
                'has_changed' => $accessGroup->hasChanged(),
                'is_activated' => $accessGroup->isActivate()
            ],
            $accessGroups
        );
    }
}

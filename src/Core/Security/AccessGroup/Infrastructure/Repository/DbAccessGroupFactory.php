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

namespace Core\Security\AccessGroup\Infrastructure\Repository;

use Core\Security\AccessGroup\Domain\Model\AccessGroup;

class DbAccessGroupFactory
{
    /**
     * @param array<string, string|int> $record
     * @return AccessGroup
     */
    public static function createFromRecord(array $record): AccessGroup
    {
        return (new AccessGroup((int) $record['acl_group_id'], $record['acl_group_name'], $record['acl_group_alias']))
            ->setActivate($record['acl_group_activate'] === '1')
            ->setChanged($record['acl_group_changed'] === 1);
    }
}

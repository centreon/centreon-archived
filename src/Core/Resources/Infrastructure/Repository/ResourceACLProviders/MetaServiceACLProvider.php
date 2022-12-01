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

namespace Core\Resources\Infrastructure\Repository\ResourceACLProviders;

use Core\Domain\RealTime\Model\ResourceTypes\MetaServiceResourceType;

class MetaServiceACLProvider implements ResourceACLProviderInterface
{
    /**
     * @inheritDoc
     */
    public function buildACLSubRequest(array $accessGroupIds): string
    {
        $requestPattern = 'EXISTS (
            SELECT 1
            FROM `:dbstg`.centreon_acl acl
            WHERE
                resources.type = %d
                AND resources.parent_id = acl.host_id
                AND resources.id = acl.service_id
                AND acl.group_id IN (%s)
        )';

        return sprintf($requestPattern, MetaServiceResourceType::TYPE_ID, implode(', ', $accessGroupIds));
    }
}

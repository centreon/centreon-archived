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

namespace Core\Application\RealTime\Repository;

use Core\Domain\RealTime\Model\Host;

interface ReadHostRepositoryInterface
{
    /**
     * Find Host without ACL
     *
     * @param int $hostId
     * @return Host|null
     */
    public function findHostById(int $hostId): ?Host;

    /**
     * Find Host regarding user ACL
     *
     * @param int $hostId
     * @param int[] $accessGroupIds
     * @return Host|null
     */
    public function findHostByIdAndAccessGroupIds(int $hostId, array $accessGroupIds): ?Host;

    /**
     * Check if user is allowed to get host information
     *
     * @param int $hostId

     * @param int[] $accessGroupIds
     * @return bool
     */
    public function isAllowedToFindHostByAccessGroupIds(int $hostId, array $accessGroupIds): bool;
}

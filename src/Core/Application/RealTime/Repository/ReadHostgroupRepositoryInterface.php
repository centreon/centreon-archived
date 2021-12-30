<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

use Core\Domain\RealTime\Model\Hostgroup;

interface ReadHostgroupRepositoryInterface
{
    /**
     * Find related hostgroups regarding a host without ACL
     *
     * @param int $hostId
     * @return Hostgroup[]
     */
    public function findAllByHostId(int $hostId): array;

    /**
     * Find related hostgroups regarding a host with ACL
     *
     * @param int $hostId
     * @param int[] $accessGroupIds
     * @return Hostgroup[]
     */
    public function findAllByHostIdAndAccessGroupIds(int $hostId, array $accessGroupIds): array;
}

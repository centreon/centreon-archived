<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Core\Resources\Application\Repository;

use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;

interface ReadResourceRepositoryInterface
{
    /**
     * Find all resources.
     *
     * @param ResourceFilter $filter
     * @return \Centreon\Domain\Monitoring\Resource[]
     */
    public function findResources(ResourceFilter $filter): array;

    /**
     * Find all resources with filter on access group IDs
     *
     * @param ResourceFilter $filter
     * @param int[] $accessGroupIds
     * @return \Centreon\Domain\Monitoring\Resource[]
     */
    public function findResourcesByAccessGroupIds(ResourceFilter $filter, array $accessGroupIds): array;

    /**
     * Get list of resources with graph data.
     *
     * @param ResourceEntity[] $resources
     * @return ResourceEntity[]
     */
    public function extractResourcesWithGraphData(array $resources): array;
}

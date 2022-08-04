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

namespace Centreon\Domain\Monitoring\Interfaces;

use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;

interface ResourceServiceInterface
{
    /**
     * Find all resources.
     *
     * @param ResourceFilter $filter
     * @return ResourceEntity[]
     * @throws \Exception
     */
    public function findResources(ResourceFilter $filter): array;

    /**
     * Get list of resources with graph data.
     *
     * @param ResourceEntity[] $resources
     * @return ResourceEntity[]
     */
    public function extractResourcesWithGraphData(array $resources): array;

    /**
     * Replace macros set in the external links by their actual values
     *
     * @param ResourceEntity $resource
     * @return void
     */
    public function replaceMacrosInExternalLinks(ResourceEntity $resource): void;

    /**
     * Used to filter requests according to a contact.
     * If the filter is defined, all requests will use the ACL of the contact
     * to fetch data.
     *
     * @param mixed $contact Contact to use as a ACL filter
     * @return ResourceServiceInterface
     * @throws \Exception
     */
    public function filterByContact($contact);
}

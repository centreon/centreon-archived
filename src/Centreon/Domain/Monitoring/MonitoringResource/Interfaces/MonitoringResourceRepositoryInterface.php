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

namespace Centreon\Domain\Monitoring\MonitoringResource\Interfaces;

use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;

/**
 * This interface gathers all the operations on the monitoring resource repository.
 *
 * @package Centreon\Domain\Monitoring\MonitoringResource\Interfaces
 */
interface MonitoringResourceRepositoryInterface
{
    /**
     * Find all monitoring resources.
     *
     * @param ResourceFilter $filter
     * @return MonitoringResource[]
     * @throws \Throwable
     */
    public function findAll(ResourceFilter $filter): array;

    /**
     * Find all monitoring resources by contact.
     *
     * @param ResourceFilter $filter
     * @param AccessGroup[] $accessGroups
     * @return MonitoringResource[]
     * @throws \Throwable
     */
    public function findAllByAccessGroups(ResourceFilter $filter, array $accessGroups): array;

    /**
     * Only returns MonitoringResources with graph data available.
     *
     * @param MonitoringResource[] $monitoringResources
     * @return MonitoringResource[]
     */
    public function extractResourcesWithGraphData(array $monitoringResources): array;
}

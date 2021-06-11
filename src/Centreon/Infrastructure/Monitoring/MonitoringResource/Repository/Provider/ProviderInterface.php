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

namespace Centreon\Infrastructure\Monitoring\MonitoringResource\Repository\Provider;

use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;

interface ProviderInterface
{
    /**
     * Initialized by the dependency injector.
     *
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function setSqlRequestTranslator(SqlRequestParametersTranslator $sqlRequestTranslator): void;

    /**
     * Check if filter match criterias to be searched
     *
     * @param ResourceFilter $filter
     * @return bool
     */
    public function shouldBeSearched(ResourceFilter $filter): bool;

    /**
     * Prepare SQL query for an admin user
     *
     * @param ResourceFilter $filter
     * @param StatementCollector $collector
     * @return string
     */
    public function prepareSubQueryWithoutAcl(ResourceFilter $filter, StatementCollector $collector): string;

    /**
     * Prepare SQL query for a non admin user
     *
     * @param ResourceFilter $filter
     * @param StatementCollector $collector
     * @param int[] $accessGroupIds
     * @return string
     */
    public function prepareSubQueryWithAcl(
        ResourceFilter $filter,
        StatementCollector $collector,
        array $accessGroupIds
    ): string;

    /**
     * Remove resources which do not have performance metrics
     *
     * @param MonitoringResource[] $resources
     * @return MonitoringResource[]
     */
    public function excludeResourcesWithoutMetrics(array $resources): array;
}

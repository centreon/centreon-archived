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

namespace Core\Infrastructure\RealTime\Api\Hypermedia;

use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaProviderInterface;

class HypermediaCreator
{
    /**
     * @var HypermediaProviderInterface[]
     */
    private $hypermediaProviders;

    /**
     * @param iterable<HypermediaProviderInterface> $hypermediaProviders
     * @return void
     */
    public function setHypermediaProviders(iterable $hypermediaProviders): void
    {
        $hypermediaProviders = $hypermediaProviders instanceof \Traversable
            ? iterator_to_array($hypermediaProviders)
            : $hypermediaProviders;

            $this->hypermediaProviders = $hypermediaProviders;
    }

    /**
     * This method will create the internal redirection endpoints for the given resource.
     * Those links will be created regarding the Users rights and Resource type.
     * ex: For a service resource type
     * [
     *  "timeline": "/centreon/api/v21.10/monitoring/hosts/14/services/26/timeline",
     *  "status_graph": "/centreon/api/v21.10/monitoring/hosts/14/services/26/metrics/status",
     *  "performance_graph": "/centreon/api/v21.10/monitoring/hosts/14/services/26/metrics/performance"
     * ]
     *
     * @param mixed $response
     * @return array<string, string|null>
     */
    public function createEndpoints(mixed $response): array
    {
        foreach ($this->hypermediaProviders as $hypermediaProvider) {
            if ($hypermediaProvider->isValidFor($response)) {
                return $hypermediaProvider->createEndpoints($response);
            }
        }
        return [];
    }

    /**
     * This method will create the internal redirection links for the given resource.
     * Those links will be created regarding the Users rights and Resource type.
     * ex: For a service resource type
     * [
     *   "configuration": "/centreon/main.php?p=60201&o=c&service_id=26",
     *   "logs": "/centreon/main.php?p=20301&svc=14_26",
     *   "reporting": "/centreon/main.php?p=30702&period=yesterday&start=&end=&host_id=14&item=26"
     * ]
     *
     * @param mixed $response
     * @return array<string, string|null>
     */
    public function createInternalUris(mixed $response): array
    {
        foreach ($this->hypermediaProviders as $hypermediaProvider) {
            if ($hypermediaProvider->isValidFor($response)) {
                return $hypermediaProvider->createInternalUris($response);
            }
        }
        return [];
    }

    /**
     * This method will add the redirection uri to the group configuration page.
     * This will be done regarding the Users rights and the Resource Type.
     * ex: For a Host resource type will add the redirection link to the hostgroup
     * configuration page.
     * [
     *   [
     *      'id' => 1,
     *      'name' => ALL,
     *      'configuration_uri' => 'http://localhost:8080/centreon/main.php?p=60102&o=c&hg_id=53'
     *   ]
     * ]
     *
     * @param mixed $response
     * @return array<array<string, string|null>>
     */
    public function createInternalGroupsUri(mixed $response): array
    {
        foreach ($this->hypermediaProviders as $hypermediaProvider) {
            if ($hypermediaProvider->isValidFor($response)) {
                return $hypermediaProvider->createInternalGroupsUri($response);
            }
        }
        return [];
    }
}

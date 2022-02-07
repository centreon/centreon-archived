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

class HypermediaService
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

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

namespace Core\Infrastructure\RealTime\Hypermedia;

interface HypermediaProviderInterface
{
    /**
     * @param string $resourceType
     * @return boolean
     */
    public function isValidFor(string $resourceType): bool;

    /**
     * Create endpoints for the Resource provided
     *
     * @param array<string, mixed> $parameters
     * @return array<string, string|null>
     */
    public function createEndpoints(array $parameters): array;

    /**
     * Create internal redirection uris for the Resource provided
     *
     * @param array<string, mixed> $parameters
     * @return array<string, string|null>
     */
    public function createInternalUris(array $parameters): array;

    /**
     * Create internal redirection uris for the Resource group(s)
     *
     * @param array<int, array<string, string|int>> $groups
     * @return array<array<string, string|int|null>>
     */
    public function convertGroupsForPresenter(array $groups): array;

    /**
     * Create internal redirection uris for the Resource categories
     *
     * @param array<int, array<string, string|int>> $categories
     * @return array<int, array<string, string|int|null>>
     */
    public function convertCategoriesForPresenter(array $categories): array;

    /**
     * @param array<string, int> $parameters
     * @return string
     */
    public function generateResourceDetailsUri(array $parameters): string;
}

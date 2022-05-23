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

namespace Core\Application\RealTime\UseCase\FindHostCategory;

use Core\Domain\RealTime\Model\HostCategory;

class FindHostCategoryResponse
{
    /**
     * @var array<int, array<string, int|string>>
     */
    public array $categories;

    /**
     * @param \Traversable<int, HostCategory> $categories
     */
    public function __construct(\Traversable $categories)
    {
        $this->categories = $this->categoriesToArray($categories);
    }

    /**
     * Convert array of HostCategory models into an array made of scalars
     *
     * @param \Traversable<int, HostCategory> $hostCategories
     * @return array<int, array<string, int|string>>
     */
    private function categoriesToArray(\Traversable $hostCategories): array
    {
        return array_map(
            fn (HostCategory $hostCategory) => [
                'id' => $hostCategory->getId(),
                'name' => $hostCategory->getName()
            ],
            iterator_to_array($hostCategories)
        );
    }
}

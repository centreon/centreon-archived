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

namespace Centreon\Infrastructure\HostConfiguration\API\Model\HostCategory;

use Centreon\Domain\HostConfiguration\UseCase\V2110\HostCategory\FindHostCategoriesResponse;

/**
 * This class is designed to create the hostCategoryV21 entity
 *
 * @package Centreon\Infrastructure\HostConfiguration\API\Model\HostCategory
 */
class HostCategoryV2110Factory
{
    /**
     * @param FindHostCategoriesResponse $response
     * @return HostCategoryV2110[]
     */
    public static function createFromResponse(FindHostCategoriesResponse $response): array
    {
        $hostCategories = [];
        foreach ($response->getHostCategories() as $hostCategory) {
            $newHostCategory = new HostCategoryV2110();
            $newHostCategory->id = $hostCategory['id'];
            $newHostCategory->name = $hostCategory['name'];
            $newHostCategory->alias = $hostCategory['alias'];
            $newHostCategory->comments = $hostCategory['comments'];
            $newHostCategory->isActivated = $hostCategory['is_activated'];

            $hostCategories[] = $newHostCategory;
        }
        return $hostCategories;
    }
}

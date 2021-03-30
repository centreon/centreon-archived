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

namespace Centreon\Domain\HostConfiguration\Interfaces\HostCategory;

use Centreon\Domain\HostConfiguration\Exception\HostCategoryException;
use Centreon\Domain\HostConfiguration\Model\HostCategory;

/**
 * @package Centreon\Domain\HostConfiguration\Interfaces\HostCategory
 */
interface HostCategoryServiceInterface
{
    /**
     * Add a host category.
     *
     * @param HostCategory $category Host category to be added
     * @throws HostCategoryException
     */
    public function addCategory(HostCategory $category): void;

    /**
     * Find a host category (for non admin user).
     *
     * @param int $categoryId Id of the host category to be found
     * @return HostCategory|null
     * @throws HostCategoryException
     */
    public function findWithAcl(int $categoryId): ?HostCategory;

    /**
     * Find a host category (for admin user).
     *
     * @param int $categoryId Id of the host category to be found
     * @return HostCategory|null
     * @throws HostCategoryException
     */
    public function findWithoutAcl(int $categoryId): ?HostCategory;

    /**
     * Find all host categories (for non admin user).
     *
     * @return HostCategory[]
     * @throws HostCategoryException
     */
    public function findAllWithAcl(): array;

    /**
     * Find all host categories (for admin user).
     *
     * @return HostCategory[]
     * @throws HostCategoryException
     */
    public function findAllWithoutAcl(): array;

    /**
     * Find a host category by name (for non admin user).
     *
     * @param string $categoryName Name of the host category to be found
     * @return HostCategory|null
     * @throws HostCategoryException
     */
    public function findByNameWithAcl(string $categoryName): ?HostCategory;

    /**
     * Find a host category by name (for admin user).
     *
     * @param string $categoryName Name of the host category to be found
     * @return HostCategory|null
     * @throws HostCategoryException
     */
    public function findByNameWithoutAcl(string $categoryName): ?HostCategory;

    /**
     * Find host categories by name (for admin user).
     *
     * @param string[] $categoriesName List of names of host categories to be found
     * @return HostCategory[]
     * @throws HostCategoryException
     */
    public function findByNamesWithoutAcl(array $categoriesName): array;
}

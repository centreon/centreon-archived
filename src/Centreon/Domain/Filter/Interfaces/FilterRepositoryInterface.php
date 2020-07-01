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

namespace Centreon\Domain\Filter\Interfaces;

use Centreon\Domain\Filter\Filter;

interface FilterRepositoryInterface
{
    /**
     * Add filter.
     *
     * @param Filter $filter
     * @return void
     * @throws FilterException
     */
    public function addFilter(Filter $filter): void;

    /**
     * Find filters.
     *
     * @return Filter[]
     * @throws \Exception
     */
    public function findFilters(): array;
}

<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\RequestParameters\Interfaces;

use Centreon\Domain\RequestParameters\RequestParameters;

/**
 * @package Centreon\Domain\RequestParameters\Interfaces
 */
interface RequestParametersInterface
{
    /**
     * Add an extra parameter.
     *
     * @param string $parameterName Parameter name
     * @param mixed $value Parameter value
     */
    public function addExtraParameter(string $parameterName, $value): void;

    /**
     * Check if a search parameter exists.
     *
     * @param string $keyToFind Name of the search parameter
     * @param array $parameters List of parameters
     * @return bool Returns true if the parameter exists
     */
    public function hasSearchParameter(string $keyToFind, array $parameters): bool;

    /**
     * @return int
     */
    public function getConcordanceStrictMode(): int;

    /**
     * @param int $concordanceStrictMode
     */
    public function setConcordanceStrictMode(int $concordanceStrictMode);

    /**
     * @return int
     */
    public function getConcordanceErrorMode(): int;

    /**
     * Set error mode (exception or silent)
     * @param int $concordanceErrorMode
     */
    public function setConcordanceErrorMode(int $concordanceErrorMode);

    /**
     * Returns the value of the extra parameter.
     *
     * @param string $parameterName Parameter name
     * @return mixed Returns the value or null
     */
    public function getExtraParameter(string $parameterName);

    /**
     * @see Pagination::$limit
     * @return int
     */
    public function getLimit(): int;

    /**
     * @see RequestParameters::$page
     * @return int
     */
    public function getPage(): int;

    /**
     * @return array
     */
    public function getSearch(): array;

    /**
     * @return array
     */
    public function getSort(): array;

    /**
     * @return int
     */
    public function getTotal(): int;

    /**
     * @param int $limit Number of records per page
     */
    public function setLimit(int $limit): void;

    /**
     * @param int $page Number of the page
     */
    public function setPage(int $page): void;

    /**
     * @param string $search
     * @throws \Exception
     */
    public function setSearch(string $search): void;

    /**
     * @param string $sortRequest
     * @throws \Exception
     */
    public function setSort(string $sortRequest): void;

    /**
     * @param int $total
     */
    public function setTotal(int $total): void;

    /**
     * Converts this requestParameter instance into an array allowing its
     * encoding in JSON format.
     *
     * @return array ['sort_by' => ..., 'limit' => ..., 'total' => ..., ...]
     */
    public function toArray(): array;

    /**
     * Remove a search parameter.
     *
     * @param string $parameterToExtract Parameter to remove
     * @throws \Exception
     */
    public function unsetSearchParameter(string $parameterToExtract);

    /**
     * @return array
     */
    public function extractSearchNames(): array;
}

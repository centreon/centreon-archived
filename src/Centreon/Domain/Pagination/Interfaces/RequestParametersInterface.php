<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\Pagination\Interfaces;

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
     * Find the search parameter.
     *
     * @param string $keyToFind Name of the search parameter
     * @param array $parameters List of parameters
     * @return string|null Returns the value of the search parameter
     */
    public function findSearchParameter(string $keyToFind, array $parameters): ?string;

    /**
     * @return int
     */
    public function getConcordanceStrictMode(): int;

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
     * Indicate is the parameter has been defined.
     *
     * @param string $parameter Parameter to find
     * @return bool
     */
    public function isSearchParameterDefined(string $parameter): bool;

    /**
     * @param int $concordanceStrictMode
     */
    public function setConcordanceStrictMode(int $concordanceStrictMode): void;

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
}

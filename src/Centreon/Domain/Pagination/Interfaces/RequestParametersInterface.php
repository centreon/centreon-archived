<?php

namespace Centreon\Domain\Pagination\Interfaces;

interface RequestParametersInterface
{
    /**
     * @param string $keyToFind
     * @param array $parameters
     * @return mixed
     */
    public function findSearchParameter(string $keyToFind, array $parameters);

    /**
     * @return int
     */
    public function getConcordanceStrictMode(): int;

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
     * Converts this pagination instance to an array and can be used to be
     * encoded in JSON format.
     *
     * @return array ['sort_by' => ..., 'limit' => ..., 'total' => ..., ...]
     */
    public function toArray(): array;

    /**
     * @param string $parameterToExtract
     * @throws \Exception
     */
    public function unsetSearchParameter(string $parameterToExtract);
}

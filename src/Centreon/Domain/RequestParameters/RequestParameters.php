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

namespace Centreon\Domain\RequestParameters;

use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;

/**
 * @package Centreon\Domain\RequestParameters
 */
class RequestParameters implements RequestParametersInterface
{
    public const NAME_FOR_LIMIT = 'limit';
    public const NAME_FOR_PAGE = 'page';
    public const NAME_FOR_SEARCH = 'search';
    public const NAME_FOR_SORT = 'sort_by';
    public const NAME_FOR_TOTAL = 'total';

    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';
    public const DEFAULT_ORDER = self::ORDER_ASC;

    public const DEFAULT_LIMIT = 10;
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_SEARCH_OPERATOR = self::OPERATOR_EQUAL;

    public const OPERATOR_EQUAL = '$eq';
    public const OPERATOR_NOT_EQUAL = '$neq';
    public const OPERATOR_LESS_THAN = '$lt';
    public const OPERATOR_LESS_THAN_OR_EQUAL = '$le';
    public const OPERATOR_GREATER_THAN = '$gt';
    public const OPERATOR_GREATER_THAN_OR_EQUAL = '$ge';
    public const OPERATOR_LIKE = '$lk';
    public const OPERATOR_NOT_LIKE = '$nk';
    public const OPERATOR_REGEXP = '$rg';
    public const OPERATOR_IN = '$in';
    public const OPERATOR_NOT_IN = '$ni';

    public const AGGREGATE_OPERATOR_OR = '$or';
    public const AGGREGATE_OPERATOR_AND = '$and';

    public const CONCORDANCE_MODE_NO_STRICT = 0;
    public const CONCORDANCE_MODE_STRICT = 1;

    public const CONCORDANCE_ERRMODE_SILENT = 0;
    public const CONCORDANCE_ERRMODE_EXCEPTION = 1;

    private $authorizedOrders = [self::ORDER_ASC, self::ORDER_DESC];

    private $extraParameters = [];

    /**
     * @var int Indicates whether we should consider only known search parameters.
     * Used in the data repository classes.
     */
    private $concordanceStrictMode = self::CONCORDANCE_MODE_NO_STRICT;

    /**
     * @var int Indicates error behaviour when there unknown search parameters in strict mode.
     */
    private $concordanceErrorMode = self::CONCORDANCE_ERRMODE_EXCEPTION;

    /**
     * @var array Array representing fields to search for
     */
    private $search = [];

    /**
     * @var array Field to order
     */
    private $sort = [];

    /**
     * @var int Number of the page
     */
    private $page = 1;

    /**
     * @var int Number of records per page
     */
    private $limit = 10;

    /**
     * @var int Total of lines founds without limit
     */
    private $total = 0;

    /**
     * @inheritDoc
     */
    public function addExtraParameter(string $parameterName, $value): void
    {
        $this->extraParameters[$parameterName] = $value;
    }

    /**
     * @inheritDoc
     */
    public function getExtraParameter(string $parameterName)
    {
        if (array_key_exists($parameterName, $this->extraParameters)) {
            return $this->extraParameters[$parameterName];
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getConcordanceStrictMode(): int
    {
        return $this->concordanceStrictMode;
    }

    /**
     * {@inheritDoc}
     * @return RequestParameters
     */
    public function setConcordanceStrictMode(int $concordanceStrictMode): self
    {
        $this->concordanceStrictMode = $concordanceStrictMode;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConcordanceErrorMode(): int
    {
        return $this->concordanceErrorMode;
    }

    /**
     * {@inheritDoc}
     * @return RequestParameters
     */
    public function setConcordanceErrorMode(int $concordanceErrorMode): self
    {
        $this->concordanceErrorMode = $concordanceErrorMode;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasSearchParameter(string $keyToFind, array $parameters): bool
    {
        foreach ($parameters as $key => $value) {
            if ($key === $keyToFind) {
                return true;
            } elseif (is_array($value) || is_object($value)) {
                $value = (array) $value;
                if ($this->hasSearchParameter($keyToFind, $value)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function extractSearchNames(): array
    {
        $notAllowedKeys = [
            self::AGGREGATE_OPERATOR_AND,
            self::AGGREGATE_OPERATOR_OR,
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
            self::OPERATOR_LESS_THAN,
            self::OPERATOR_LESS_THAN_OR_EQUAL,
            self::OPERATOR_GREATER_THAN,
            self::OPERATOR_GREATER_THAN_OR_EQUAL,
            self::OPERATOR_LIKE,
            self::OPERATOR_NOT_LIKE,
            self::OPERATOR_REGEXP,
            self::OPERATOR_IN,
            self::OPERATOR_NOT_IN
        ];
        $names = [];
        $searchIn = function ($data) use (&$searchIn, &$names, $notAllowedKeys) {
            foreach ($data as $key => $value) {
                if (!in_array($key, $names) && !in_array($key, $notAllowedKeys) && !is_int($key)) {
                    $names[] = $key;
                }
                if (is_object($value) || is_array($value)) {
                    $searchIn((array) $value);
                }
            }
        };
        $searchIn($this->search);
        return $names;
    }

    /**
     * Try to fix the schema in case of bad structure
     *
     * @throws \Exception
     */
    private function fixSchema()
    {
        $search = $this->search;

        if (!empty($search)) {
            if (
                (
                    !isset($search[RequestParameters::AGGREGATE_OPERATOR_AND])
                    && !isset($search[RequestParameters::AGGREGATE_OPERATOR_OR])
                )
                || (count($this->search) > 1)
            ) {
                $newSearch[RequestParameters::AGGREGATE_OPERATOR_AND] = $search;
                $this->search = $newSearch;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            self::NAME_FOR_PAGE => $this->page,
            self::NAME_FOR_LIMIT => $this->limit,
            self::NAME_FOR_SEARCH => !empty($this->search)
                ? json_decode(json_encode($this->search), true)
                : new \stdClass(),
            self::NAME_FOR_SORT => !empty($this->sort)
                ? json_decode(json_encode($this->sort), true)
                : new \stdClass(),
            self::NAME_FOR_TOTAL => $this->total
        ];
    }

    /**
     * @inheritDoc
     */
    public function unsetSearchParameter(string $parameterToExtract)
    {
        $parameters = $this->search;

        $extractFunction = function (string $parameterToExtract, &$parameters) use (&$extractFunction) {
            foreach ($parameters as $key => &$value) {
                if ($key === $parameterToExtract) {
                    unset($parameters[$key]);
                } elseif (is_array($value) || is_object($value)) {
                    $value = (array)$value;
                    $extractFunction($parameterToExtract, $value);
                }
            }
        };
        $extractFunction($parameterToExtract, $parameters);
        $this->search = $parameters;
    }

    /**
     * @inheritDoc
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @inheritDoc
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @inheritDoc
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @inheritDoc
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @inheritDoc
     */
    public function getSearch(): array
    {
        return $this->search;
    }

    /**
     * @inheritDoc
     * @param string $search
     */
    public function setSearch(string $search): void
    {
        $search = json_decode($search ?? '{}', true);
        $this->search = (array) $search;
        $this->fixSchema();
    }

    /**
     * @inheritDoc
     * @return array<mixed>
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @inheritDoc
     */
    public function setSort(string $sortRequest): void
    {
        $sortRequestToAnalyze = json_decode($sortRequest ?? self::DEFAULT_SEARCH_OPERATOR, true);
        if (!is_array($sortRequestToAnalyze)) {
            if ($sortRequest[0] != '{') {
                $this->sort = [$sortRequest => self::DEFAULT_ORDER];
            } else {
                throw new \RestBadRequestException("Bad format for the sort request parameter");
            }
        } else {
            foreach ($sortRequestToAnalyze as $name => $order) {
                $isMatched = preg_match(
                    '/^([a-zA-Z0-9_.-]*)$/i',
                    $name,
                    $sortFound,
                    PREG_OFFSET_CAPTURE
                );
                if (!$isMatched || !in_array(strtoupper($order), $this->authorizedOrders)) {
                    unset($sortRequestToAnalyze[$name]);
                } else {
                    $sortRequestToAnalyze[$name] = strtoupper($order);
                }
            }
            $this->sort = $sortRequestToAnalyze;
        }
    }

    /**
     * @inheritDoc
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @inheritDoc
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }
}

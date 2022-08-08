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

namespace Centreon\Infrastructure\RequestParameters;

use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\RequestParameters\Interfaces\NormalizerInterface;

/**
 * @package Centreon\Infrastructure\RequestParameters
 */
class SqlRequestParametersTranslator
{
    /**
     * @var string[]
     */
    private $aggregateOperators = [
        RequestParameters::AGGREGATE_OPERATOR_OR,
        RequestParameters::AGGREGATE_OPERATOR_AND
    ];

    /**
     * @var array<string, string> $concordanceArray Concordance table between the search column
     * and the real column name in the database. ['id' => 'my_table_id', ...]
     */
    private $concordanceArray = [];

    /**
     * @var array<string, mixed>
     */
    private $searchValues = [];

    /**
     * @var array<string, NormalizerInterface>
     */
    private $normalizers = [];

    /**
     * @var RequestParametersInterface
     */
    private $requestParameters;

    /**
     * SqlRequestParametersTranslator constructor.
     * @param RequestParametersInterface $requestParameters
     */
    public function __construct(RequestParametersInterface $requestParameters)
    {
        $this->requestParameters = $requestParameters;
    }

    /**
     * Create the database query based on the search parameters.
     *
     * @param array<mixed, mixed> $search Array containing search parameters
     * @param string|null $aggregateOperator Aggregate operator
     * @return string Return the processed database query
     * @throws RequestParametersTranslatorException
     */
    private function createDatabaseQuery(array $search, string $aggregateOperator = null): string
    {
        $databaseQuery = '';
        $databaseSubQuery = '';
        foreach ($search as $key => $searchRequests) {
            if ($this->isAggregateOperator($key)) {
                if (is_object($searchRequests) || is_array($searchRequests)) {
                    if (is_object($searchRequests)) {
                        $searchRequests = (array) $searchRequests;
                    }
                    // Recursive call until to read key/value data
                    $databaseSubQuery = $this->createDatabaseQuery($searchRequests, $key);
                }
            } elseif (is_int($key) && (is_object($searchRequests) || is_array($searchRequests))) {
                // It's a list of object to process
                $searchRequests = (array) $searchRequests;
                if (!empty($searchRequests)) {
                    // Recursive call until to read key/value data
                    $databaseSubQuery = $this->createDatabaseQuery($searchRequests, $aggregateOperator);
                }
            } elseif (!is_int($key)) {
                // It's a pair on key/value to translate into a database query
                if (is_object($searchRequests)) {
                    $searchRequests = (array) $searchRequests;
                }
                $databaseSubQuery = $this->createQueryOnKeyValue($key, $searchRequests);
            }
            if (!empty($databaseQuery)) {
                if (is_null($aggregateOperator)) {
                    $aggregateOperator = RequestParameters::AGGREGATE_OPERATOR_AND;
                }
                if (!empty($databaseSubQuery)) {
                    $databaseQuery .= ' '
                        . $this->translateAggregateOperator($aggregateOperator)
                        . ' '
                        . $databaseSubQuery;
                }
            } else {
                $databaseQuery .= $databaseSubQuery;
            }
        }
        return count($search) > 1 && !empty($databaseQuery)
            ? '(' . $databaseQuery . ')'
            : $databaseQuery;
    }

    /**
     * @return RequestParametersInterface
     */
    public function getRequestParameters(): RequestParametersInterface
    {
        return $this->requestParameters;
    }

    /**
     * Translate the pagination (page and limit parameters) into SQL request.
     *
     * @return string
     */
    public function translatePaginationToSql(): string
    {
        return sprintf(
            ' LIMIT %d, %d',
            ($this->requestParameters->getPage() - 1) * $this->requestParameters->getLimit(),
            $this->requestParameters->getLimit()
        );
    }

    /**
     * Returns the rest of the query to make a filter based on the paging system.
     *
     * Usage:
     * <code>
     *      list($whereQuery, $bindValues) = $pagination->createQuery([...]);
     * </code>
     *
     * @return string|null SQL request according to the search parameters
     * @throws RequestParametersTranslatorException
     */
    public function translateSearchParameterToSql(): ?string
    {
        $whereQuery = '';
        $search = $this->requestParameters->getSearch();
        if (!empty($search) && is_array($search)) {
            $whereQuery .= $this->createDatabaseQuery($search);
        }
        return !empty($whereQuery) ? ' WHERE ' . $whereQuery : null;
    }

    /**
     * Translate the sort parameters into SQL request.
     *
     * @return string|null Returns null if no sorting parameter is defined
     */
    public function translateSortParameterToSql(): ?string
    {
        $orderQuery = '';
        foreach ($this->requestParameters->getSort() as $name => $order) {
            if (array_key_exists($name, $this->concordanceArray)) {
                if (!empty($orderQuery)) {
                    $orderQuery .= ', ';
                }
                $orderQuery .= sprintf(
                    '%s IS NULL, %s %s',
                    $this->concordanceArray[$name],
                    $this->concordanceArray[$name],
                    $order
                );
            }
        }
        return !empty($orderQuery) ? ' ORDER BY ' . $orderQuery : null;
    }

    /**
     *
     * @param string $key Key representing the entity to search
     * @param mixed $valueOrArray Mixed value or array representing the value to search.
     * @return string Part of the database query.
     * @throws RequestParametersTranslatorException
     */
    private function createQueryOnKeyValue(string $key, $valueOrArray): string
    {
        if (
            $this->requestParameters->getConcordanceStrictMode() === RequestParameters::CONCORDANCE_MODE_STRICT
            && !key_exists($key, $this->concordanceArray)
        ) {
            if (
                $this->requestParameters
                    ->getConcordanceErrorMode() === RequestParameters::CONCORDANCE_ERRMODE_EXCEPTION
            ) {
                throw new RequestParametersTranslatorException(
                    sprintf(_('The parameter %s is not allowed'), $key)
                );
            }
            return '';
        }
        if (is_array($valueOrArray)) {
            $searchOperator = (string) key($valueOrArray);
            $mixedValue = $valueOrArray[$searchOperator];
        } else {
            $searchOperator = RequestParameters::DEFAULT_SEARCH_OPERATOR;
            $mixedValue = $valueOrArray;
        }

        if ($mixedValue === null) {
            $mixedValue = $this->normalizeValue($key, $mixedValue);
        }

        if ($mixedValue === null) {
            if ($searchOperator === RequestParameters::OPERATOR_EQUAL) {
                $bindKey = 'NULL';
            } elseif ($searchOperator === RequestParameters::OPERATOR_NOT_EQUAL) {
                $bindKey = 'NOT NULL';
            } else {
                throw new RequestParametersTranslatorException(
                    'The value "null" is only supported by the operators '
                    . RequestParameters::OPERATOR_EQUAL
                    . ' and '
                    . RequestParameters::OPERATOR_NOT_EQUAL
                );
            }
        } elseif (
            $searchOperator === RequestParameters::OPERATOR_IN
            || $searchOperator === RequestParameters::OPERATOR_NOT_IN
        ) {
            if (is_array($mixedValue)) {
                $bindKey = '(';
                foreach ($mixedValue as $index => $newValue) {
                    $newValue = $this->normalizeValue($key, $newValue);
                    $type = \PDO::PARAM_STR;
                    if (is_int($newValue)) {
                        $type = \PDO::PARAM_INT;
                    } elseif (is_bool($newValue)) {
                        $type = \PDO::PARAM_BOOL;
                    }
                    $currentBindKey = ':value_' . (count($this->searchValues) + 1);
                    $this->searchValues[$currentBindKey] = [$type => $newValue];
                    if ($index > 0) {
                        $bindKey .= ',';
                    }
                    $bindKey .= $currentBindKey;
                }
                $bindKey .= ')';
            } else {
                $mixedValue = $this->normalizeValue($key, $mixedValue);
                $type = \PDO::PARAM_STR;
                if (is_int($mixedValue)) {
                    $type = \PDO::PARAM_INT;
                } elseif (is_bool($mixedValue)) {
                    $type = \PDO::PARAM_BOOL;
                }
                $bindKey = ':value_' . (count($this->searchValues) + 1);
                $this->searchValues[$bindKey] = [$type => $mixedValue];
                $bindKey = '(' . $bindKey . ')';
            }
        } elseif (
            $searchOperator === RequestParameters::OPERATOR_LIKE
            || $searchOperator === RequestParameters::OPERATOR_NOT_LIKE
            || $searchOperator === RequestParameters::OPERATOR_REGEXP
        ) {
            // We check the regex
            if ($searchOperator === RequestParameters::OPERATOR_REGEXP) {
                try {
                    preg_match('/' . $mixedValue . '/', '');
                } catch (\Throwable $ex) {
                    // No exception in prod environment
                    throw new RequestParametersTranslatorException('Bad regex format \'' . $mixedValue . '\'', 0, $ex);
                }
                if (preg_last_error() !== PREG_NO_ERROR) {
                    throw new RequestParametersTranslatorException('Bad regex format \'' . $mixedValue . '\'', 0);
                }
            }
            $type = \PDO::PARAM_STR;
            $bindKey = ':value_' . (count($this->searchValues) + 1);
            $this->searchValues[$bindKey] = [$type => $mixedValue];
        } else {
            $mixedValue = $this->normalizeValue($key, $mixedValue);
            $type = \PDO::PARAM_STR;
            if (is_int($mixedValue)) {
                $type = \PDO::PARAM_INT;
            } elseif (is_bool($mixedValue)) {
                $type = \PDO::PARAM_BOOL;
            }
            $bindKey = ':value_' . (count($this->searchValues) + 1);
            $this->searchValues[$bindKey] = [$type => $mixedValue];
        }

        return sprintf(
            '%s %s %s',
            (array_key_exists($key, $this->concordanceArray)
                ? $this->concordanceArray[$key]
                : $key),
            ($mixedValue !== null) ? $this->translateSearchOperator($searchOperator) : 'IS',
            $bindKey
        );
    }

    /**
     * Indicates if the key is an aggregate operator
     *
     * @param mixed $key Key to test
     * @return bool Return TRUE if the key is an aggregate operator otherwise FALSE
     */
    private function isAggregateOperator($key): bool
    {
        return is_string($key) && in_array($key, $this->aggregateOperators);
    }

    /**
     * @param string $aggregateOperator
     * @return string
     * @throws RequestParametersTranslatorException
     */
    private function translateAggregateOperator(string $aggregateOperator): string
    {
        if ($aggregateOperator === RequestParameters::AGGREGATE_OPERATOR_AND) {
            return 'AND';
        } elseif ($aggregateOperator === RequestParameters::AGGREGATE_OPERATOR_OR) {
            return 'OR';
        }
        throw new RequestParametersTranslatorException(_('Bad search operator'));
    }

    /**
     * Translates the search operators (RequestParameters::OPERATOR_LIKE, ...)
     * in their SQL equivalent (LIKE, ...).
     *
     * @param string $operator Operator to translate
     * @return string Operator translated in his SQL equivalent
     */
    private function translateSearchOperator(string $operator): string
    {
        switch ($operator) {
            case RequestParameters::OPERATOR_LIKE:
                return 'LIKE';
            case RequestParameters::OPERATOR_NOT_LIKE:
                return 'NOT LIKE';
            case RequestParameters::OPERATOR_REGEXP:
                return 'REGEXP';
            case RequestParameters::OPERATOR_LESS_THAN:
                return '<';
            case RequestParameters::OPERATOR_LESS_THAN_OR_EQUAL:
                return '<=';
            case RequestParameters::OPERATOR_GREATER_THAN:
                return '>';
            case RequestParameters::OPERATOR_GREATER_THAN_OR_EQUAL:
                return '>=';
            case RequestParameters::OPERATOR_NOT_EQUAL:
                return '!=';
            case RequestParameters::OPERATOR_IN:
                return 'IN';
            case RequestParameters::OPERATOR_NOT_IN:
                return 'NOT IN';
            case RequestParameters::OPERATOR_EQUAL:
            default:
                return '=';
        }
    }

    /**
     * @return array<string, string>
     */
    public function getConcordanceArray(): array
    {
        return $this->concordanceArray;
    }

    /**
     * @param array<string, string> $concordanceArray
     */
    public function setConcordanceArray(array $concordanceArray): void
    {
        $this->concordanceArray = $concordanceArray;
    }

    /**
     * Add a search value
     *
     * @param string $key Key
     * @param array<int, mixed> $value Array [type_value => value]
     */
    public function addSearchValue(string $key, array $value): void
    {
        $this->searchValues[$key] = $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSearchValues(): array
    {
        return $this->searchValues;
    }

    /**
     * @param array<string, mixed> $searchValues
     */
    public function setSearchValues(array $searchValues): void
    {
        $this->searchValues = $searchValues;
    }

    /**
     * Add a normalizer for a property name to be declared in the search parameters.
     * <code>
     * $sqlRequestTranslator = new SqlRequestParametersTranslator(new RequestParameters());
     * $sqlRequestTranslator->addNormalizer(
     *      'name',
     *      new class() implements NormalizerInterface
     *      {
     *          public function normalize($valueToNormalize)
     *          {
     *              if ($valueToNormalize === "localhost") {
     *                  return "127.0.0.1";
     *              }
     *              return $valueToNormalize;
     *          }
     *      }
     * );
     * </code>
     * @param string $propertyName Property name for which the normalizer is applied
     * @param NormalizerInterface $normalizer Normalizer to applied
     * @throws \InvalidArgumentException
     */
    public function addNormalizer(string $propertyName, NormalizerInterface $normalizer): void
    {
        if (empty($propertyName)) {
            throw new \InvalidArgumentException(_('The property name of the normalizer cannot be empty.'));
        }
        $this->normalizers[$propertyName] = $normalizer;
    }

    /**
     * Normalize a value.
     *
     * @param string $propertyName Property name to be normalized if it exists
     * @param string|bool|int|null $valueToNormalize Value to be normalized
     * @return string|bool|int|null
     */
    private function normalizeValue(string $propertyName, $valueToNormalize)
    {
        if (array_key_exists($propertyName, $this->normalizers)) {
            return $this->normalizers[$propertyName]->normalize($valueToNormalize);
        }
        return $valueToNormalize;
    }
}

<?php
/*
 * CENTREON
 *
 * Source Copyright 2005-2019 CENTREON
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
*/

namespace Centreon\Domain;

/**
 * This class can be used to add a paging system.
 *
 * @package Centreon\Domain\Entity
 */
class Pagination
{
    private const NAME_FOR_LIMIT = 'limit';
    private const NAME_FOR_PAGE = 'page';
    private const NAME_FOR_ORDER = 'order_by';
    private const NAME_FOR_SORT = 'sort_by';
    private const NAME_FOR_SEARCH = 'search';
    private const NAME_FOR_TOTAL = 'total';

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;
    const DEFAULT_ORDER_ASC = 'ASC';
    const DEFAULT_ORDER_DESC = 'DESC';
    const DEFAULT_ORDER = self::DEFAULT_ORDER_ASC;
    const DEFAULT_SEARCH = '{}';
    const DEFAULT_SEARCH_OPERATOR = self::OPERATOR_EQUAL;

    const OPERATOR_EQUAL = '$eq';
    const OPERATOR_NOT_EQUAL = '$neq';
    const OPERATOR_LESS_THAN = '$lt';
    const OPERATOR_LESS_THAN_OR_EQUAL = '$le';
    const OPERATOR_GREATER_THAN = '$gt';
    const OPERATOR_GREATER_THAN_OR_EQUAL = '$ge';
    const OPERATOR_LIKE = '$lk';
    const OPERATOR_NOT_LIKE = '$nk';
    const OPERATOR_IN = '$in';
    const OPERATOR_NOT_IN = '$ni';

    const AGREGATE_OPERATOR_OR = '$or';
    const AGREGATE_OPERATOR_AND = '$and';

    /**
     * @var int Number of records per page
     */
    private $limit;

    /**
     * @var int Number of the page
     */
    private $page;

    /**
     * @var string Sense of sort (ASC or DESC only)
     */
    private $order;

    /**
     * @var string Field to order
     */
    private $sort;

    /**
     * @var array Array representing fields to search for
     */
    private $search;

    /**
     * @var int Total of lines founds without limit
     */
    private $total;

    private $databaseRequestValue = [];

    private $concordanceArray = [];

    /**
     * Create an instance of the Pagination class and configure it with the
     * data found in the $ _GET parameters.
     * order_by=desc&limit=10&page=1&sort_by=item_name&search={"item_name": "my_item"}
     */
    public function init(array $parameters)
    {
        $limit = $parameters[self::NAME_FOR_LIMIT] ?? self::DEFAULT_LIMIT;
        $this->setLimit((int)$limit);

        $page = $parameters[self::NAME_FOR_PAGE] ?? self::DEFAULT_PAGE;
        $this->setPage((int)$page);

        if (isset($parameters[self::NAME_FOR_ORDER])) {
            $order = in_array(
                strtoupper($parameters[self::NAME_FOR_ORDER]),
                [self::DEFAULT_ORDER_ASC, self::DEFAULT_ORDER_DESC]
            ) ? strtoupper($parameters[self::NAME_FOR_ORDER]) : self::DEFAULT_ORDER;
            $this->setOrder($order);
        }

        if (isset($parameters[self::NAME_FOR_SORT])) {
            $sort = preg_match(
                '/^([a-zA-Z0-9_-]*)$/i',
                $parameters[self::NAME_FOR_SORT],
                $sortFound,
                PREG_OFFSET_CAPTURE
            ) ? $parameters[self::NAME_FOR_SORT] : null;
            $this->setSort($sort);
        }

        $search = json_decode($parameters[self::NAME_FOR_SEARCH] ?? '{}');
        $this->setSearch((array)$search);
    }

    /**
     * Returns the rest of the query to make a filter based on the paging system.
     *
     * Usage:
     * <code>
     *      list($whereQuery, $bindValues) = $pagination->createQuery([...]);
     * </code>
     *
     * @param array $concordanceArray Concordance table between the search column
     * and the real column name in the database. ['id' => 'my_table_id', ...]
     * @return array Array containing the query and data to bind.
     * @throws \Exception
     */
    public function createQuery($concordanceArray = []): array
    {
        $this->concordanceArray = $concordanceArray;
        $whereQuery = '';
        $search = $this->getSearch();
        if (!empty($search) && is_array($search)) {
            $whereQuery .= $this->createDatabaseQuery($search);
        }
        if (!empty($whereQuery)) {
            $whereQuery = ' WHERE ' . $whereQuery;
        }

        if (array_key_exists($this->getSort(), $concordanceArray)) {
            $whereQuery .= sprintf(
                ' ORDER BY %s %s',
                $concordanceArray[$this->getSort()],
                $this->getOrder()
            );
        }

        $whereQuery .= ' LIMIT :from, :limit';
        $this->databaseRequestValue[':from'] = [
            \PDO::PARAM_INT => ($this->getPage() - 1) * $this->getLimit()
        ];
        $this->databaseRequestValue[':limit'] = [\PDO::PARAM_INT => $this->getLimit()];

        return array($whereQuery, $this->databaseRequestValue);
    }

    /**
     * Create the database query based on the search parameters.
     *
     * @param array $search Array containing search parameters
     * @param string|null $agregateOperator Agregate operator
     * @return string Return the processed database query
     * @throws \Exception
     */
    private function createDatabaseQuery(array $search, string $agregateOperator = null): string
    {
        $databaseQuery = '';
        foreach ($search as $key => $searchRequests) {
            if ($this->isAgregateOperator($key)) {
                if (is_array($searchRequests)) {
                    // Recursive call until to read key/value data
                    $databaseSubQuery = $this->createDatabaseQuery($searchRequests, $key);
                }
            } else {
                if (is_int($key) && (is_object($searchRequests) || is_array($searchRequests))) {
                    // It's a list of object to process
                    $searchRequests = (array) $searchRequests;
                    // Recursive call until to read key/value data
                    $databaseSubQuery = $this->createDatabaseQuery($searchRequests, $agregateOperator);
                } elseif (!is_int($key)) {
                    // It's a pair on key/value to translate into a database query
                    if (is_object($searchRequests)) {
                        $searchRequests = (array) $searchRequests;
                    }
                    $databaseSubQuery = $this->createQueryOnKeyValue($key, $searchRequests);
                }
            }
            if (!empty($databaseQuery)) {
                if (is_null($agregateOperator)) {
                    $agregateOperator = self::AGREGATE_OPERATOR_AND;
                }
                $databaseQuery .= ' '
                    . $this->translateAgregateOperator($agregateOperator)
                    . ' '
                    . $databaseSubQuery;
            } else {
                $databaseQuery .= $databaseSubQuery;
            }
        }
        return count($search) > 1
            ? '(' . $databaseQuery . ')'
            : $databaseQuery;
    }

    /**
     * @param string $agregateOperator
     * @return string
     * @throws \Exception
     */
    private function translateAgregateOperator(string $agregateOperator): string
    {
        if ($agregateOperator === self::AGREGATE_OPERATOR_AND) {
            return 'AND';
        } elseif ($agregateOperator === self::AGREGATE_OPERATOR_OR) {
            return 'OR';
        }
        throw new \Exception('Bad search operator');
    }

    /**
     *
     * @param string $key Key representing the entity to search
     * @param $valueOrArray String value or array representing the value to search.
     * @return string Part of the database query.
     */
    private function createQueryOnKeyValue(
        string $key,
        $valueOrArray
    ): string {
        if (is_array($valueOrArray)) {
            $searchOperator = key($valueOrArray);
            $mixedValue = $valueOrArray[$searchOperator];
        } else {
            $searchOperator = self::DEFAULT_SEARCH_OPERATOR;
            $mixedValue = $valueOrArray;
        }

        if ($searchOperator === self::OPERATOR_IN || $searchOperator === self::OPERATOR_NOT_IN) {
            if (is_array($mixedValue)) {
                $bindKey = '(';
                foreach ($mixedValue as $index => $newValue) {
                    $type = \PDO::PARAM_STR;
                    if (is_int($newValue)) {
                        $type = \PDO::PARAM_INT;
                    } elseif (is_bool($newValue)) {
                        $type = \PDO::PARAM_BOOL;
                    }
                    $currentBindKey = ':value_' . (count($this->databaseRequestValue) + 1);
                    $this->databaseRequestValue[$currentBindKey] = [$type => $newValue];
                    if ($index > 0) {
                        $bindKey .= ',';
                    }
                    $bindKey .= $currentBindKey;
                }
                $bindKey .= ')';
            } else {
                $type = \PDO::PARAM_STR;
                if (is_int($mixedValue)) {
                    $type = \PDO::PARAM_INT;
                } elseif (is_bool($mixedValue)) {
                    $type = \PDO::PARAM_BOOL;
                }
                $bindKey = '(:value_' . (count($this->databaseRequestValue) + 1) . ')';
                $this->databaseRequestValue[$bindKey] = [$type => $mixedValue];
            }
        } elseif ($searchOperator === self::OPERATOR_LIKE || $searchOperator === self::OPERATOR_NOT_LIKE) {
            $type = \PDO::PARAM_STR;
            $bindKey = ':value_' . (count($this->databaseRequestValue) + 1);
            $this->databaseRequestValue[$bindKey] = [$type => $mixedValue];
        } else {
            $type = \PDO::PARAM_STR;
            if (is_int($mixedValue)) {
                $type = \PDO::PARAM_INT;
            } elseif (is_bool($mixedValue)) {
                $type = \PDO::PARAM_BOOL;
            }
            $bindKey = ':value_' . (count($this->databaseRequestValue) + 1);
            $this->databaseRequestValue[$bindKey] = [$type => $mixedValue];
        }

        return sprintf(
            '%s %s %s',
            (array_key_exists($key, $this->concordanceArray)
                ? $this->concordanceArray[$key]
                : $key),
            $this->translateSearchOperator($searchOperator),
            $bindKey
        );
    }

    private function translateSearchOperator(string $operator): string
    {
        switch ($operator) {
            case self::OPERATOR_LIKE:
                return 'LIKE';
            case self::OPERATOR_NOT_LIKE:
                return 'NOT LIKE';
            case self::OPERATOR_LESS_THAN:
                return '<';
            case self::OPERATOR_LESS_THAN_OR_EQUAL:
                return '<=';
            case self::OPERATOR_GREATER_THAN:
                return '>';
            case self::OPERATOR_GREATER_THAN_OR_EQUAL:
                return '>=';
            case self::OPERATOR_NOT_EQUAL:
                return '!=';
            case self::OPERATOR_IN:
                return 'IN';
            case self::OPERATOR_NOT_IN:
                return 'NOT IN';
            case self::OPERATOR_EQUAL:
            default:
                return '=';
        }
    }

    /**
     * @see Pagination::$limit
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit Number of records per page
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @see Pagination::$page
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page Number of the page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @see Pagination::$order
     * @return string Sense of sort (ASC or DESC only)
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @param string $order Sense of sort (ASC or DESC only)
     */
    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    /**
     * @see Pagination::$sort
     * @return string Field to order
     */
    public function getSort(): ?string
    {
        return $this->sort;
    }

    /**
     * @param string $sort Field to order
     */
    public function setSort(?string $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * Return an array representing fields to search for.
     *
     * @see Pagination::$search
     * @return array Array representing fields to search for.
     * ['field1' => ...., 'field2' => ...]
     */
    public function getSearch(): array
    {
        return $this->search;
    }

    /**
     * @param array $search Array representing fields to search for.
     * @throws \Exception
     * @see Pagination::$search
     */
    public function setSearch(array $search): void
    {
        $this->search = $search;
        $this->checkSearchSchema();
    }

    /**
     * @see Pagination::$total
     * @return int Total of lines founds without limit
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total Total of lines founds without limit
     * @see Pagination::$total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * Converts this pagination instance to an array and can be used to be
     * encoded in JSON format.
     *
     * @return array ['sort_by' => ..., 'limit' => ..., 'total' => ..., ...]
     */
    public function toArray(): array
    {
        return [
            self::NAME_FOR_PAGE => $this->page,
            self::NAME_FOR_LIMIT => $this->limit,
            self::NAME_FOR_SEARCH => !empty($this->search)
                ? json_decode(json_encode($this->search), true)
                : new \stdClass,
            self::NAME_FOR_SORT => $this->sort,
            self::NAME_FOR_ORDER => $this->order,
            self::NAME_FOR_TOTAL => $this->total
        ];
    }

    /**
     * Indicates if the key is an agregate operator
     *
     * @param string $key Key to test
     * @return bool Return TRUE if the key is an agregate operator otherwise FALSE
     */
    private function isAgregateOperator(string $key): bool
    {
        $agregateOperators = [
            self::AGREGATE_OPERATOR_OR,
            self::AGREGATE_OPERATOR_AND
        ];
        return in_array($key, $agregateOperators);
    }

    /**
     * Indicate is the parameter has been defined.
     *
     * @param string $parameter Parameter to find
     * @return bool
     */
    public function isParameterDefined(string $parameter): bool
    {
        return $this->findParameter($parameter, (array)$this->getSearch()) !== null;
    }

    private function findParameter(string $keyToFind, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if ($key === $keyToFind) {
                if (is_object($value)) {
                    $value = (array)$value;
                    return $value[key($value)];
                } else {
                    return $value;
                }
            } else {
                if (is_array($value) || is_object($value)) {
                    $value = (array)$value;
                    if ($this->findParameter($keyToFind, $value) !== null) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function checkSearchSchema()
    {
        $search = $this->search;
        $nbrElements = count($search, COUNT_RECURSIVE);

        if ($nbrElements === 1) {
            if (!isset($search[Pagination::AGREGATE_OPERATOR_AND])
                || !isset($search[Pagination::AGREGATE_OPERATOR_OR])
            ) {
                $newSearch = [Pagination::AGREGATE_OPERATOR_AND => []];
                $newSearch[Pagination::AGREGATE_OPERATOR_AND][] = $search;
                $this->search = $newSearch;
            }
        } elseif ($nbrElements > 1) {
            if (!isset($search[Pagination::AGREGATE_OPERATOR_AND])
                && !isset($search[Pagination::AGREGATE_OPERATOR_OR])
            ) {
                throw new \Exception('Bad format of search attribute');
            }
        }
    }
}

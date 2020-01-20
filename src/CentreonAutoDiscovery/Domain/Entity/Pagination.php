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

namespace CentreonAutoDiscovery\Domain\Entity;

/**
 * This class can be used to add a paging system.
 *
 * @package CentreonAutoDiscovery\Domain\Entity
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

    /**
     * @var array values to bind to database
     */
    private $bindValues = [];

    /**
     * Concordance table with database column
     *
     * @var array
     */
    private $concordance = [];

    /**
     * Create an instance of the Pagination class and configure it with the
     * data found in the $ _GET parameters.
     * order_by=desc&limit=10&page=1&sort_by=item_name&search={"item_name": "my_item"}
     *
     * @return Pagination
     */
    public static function fromGetParameters()
    {
        $pagination = new Pagination();

        $limit = $_GET[self::NAME_FOR_LIMIT] ?? self::DEFAULT_LIMIT;
        $pagination->setLimit((int)$limit);

        $page = $_GET[self::NAME_FOR_PAGE] ?? self::DEFAULT_PAGE;
        $pagination->setPage((int)$page);

        if (isset($_GET[self::NAME_FOR_ORDER])) {
            $order = in_array(
                strtoupper($_GET[self::NAME_FOR_ORDER]),
                [self::DEFAULT_ORDER_ASC, self::DEFAULT_ORDER_DESC]
            ) ? strtoupper($_GET[self::NAME_FOR_ORDER]) : self::DEFAULT_ORDER;
            $pagination->setOrder($order);
        }

        if (isset($_GET[self::NAME_FOR_SORT])) {
            $sort = preg_match(
                '/^([a-zA-Z0-9_\.\-]*)$/i',
                $_GET[self::NAME_FOR_SORT],
                $sortFound,
                PREG_OFFSET_CAPTURE
            ) ? $_GET[self::NAME_FOR_SORT] : null;
            $pagination->setSort($sort);
        }

        $search = json_decode($_GET[self::NAME_FOR_SEARCH] ?? '{}');
        if (!empty($search)) {
            $pagination->setSearch((array)$search);
        }
        return $pagination;
    }

    /**
     * Separate if possible the name of the column and the search operator.
     *
     * Usage:
     * <code>
     * list($columnName, $searchOperator) = $this->>separateKeyAndOperator($parameter);
     * </code>
     *
     * @param string $parameter String representing the column name and search operator
     * @return array Return a array containing the column name and search operator separate
     * in two array value.
     */
    private function separateKeyAndOperator(string $parameter): array
    {
        $defaultOperator = self::OPERATOR_EQUAL;
        if (strpos($parameter, ',') !== false) {
            list($parameter, $operator) = explode(',', $parameter);
            if (is_numeric($operator)) {
                return [$parameter, (int)$operator];
            } else {
                return [$parameter, $defaultOperator];
            }
        }
        return [$parameter, $defaultOperator];
    }

    /**
     * Returns the rest of the query to make a filter based on the paging system.
     *
     * @return string WHERE condition of the query.
     * @throws \RestInternalServerErrorException
     */
    public function createQuery(): string
    {
        $whereQuery = '';

        $search = $this->getSearch();
        if (!empty($search) && is_array($search)) {
            $whereQuery .= $this->createDatabaseQuery($search);
        }
        if (!empty($whereQuery)) {
            $whereQuery = ' WHERE ' . $whereQuery;
        }

        return $whereQuery;
    }

    /**
     * Returns the order by part of the query
     *
     * @return string ORDER BY part of the query
     */
    public function createOrder(): string
    {
        $orderQuery = '';

        if (array_key_exists($this->getSort(), $this->concordance)) {
            $orderQuery .= sprintf(
                ' ORDER BY %s %s',
                $this->concordance[$this->getSort()],
                $this->getOrder()
            );
        }

        return $orderQuery;
    }

    /**
     * Returns the part of the query which represent the limit and offset
     *
     * @return string limit and offset
     */
    public function createPagination(): string
    {
        $query = ' LIMIT :from, :limit';

        $this->bindValues[':from'] = [\PDO::PARAM_INT => ($this->getPage() - 1) * $this->getLimit()];
        $this->bindValues[':limit'] = [\PDO::PARAM_INT => $this->getLimit()];

        return $query;
    }

    /**
     * Create the database query based on the search parameters.
     *
     * @param array $search Array containing search parameters
     * @param string|null $agregateOperator Agregate operator
     * @return string Return the processed database query
     * @throws \RestInternalServerErrorException
     */
    private function createDatabaseQuery(array $search, string $agregateOperator = null): string
    {
        $databaseQuery = '';
        foreach ($search as $key => $searchRequests) {
            if ($this->isAgregateOperator($key)) {
                if (is_array($searchRequests)) {
                    $databaseSubQuery = $this->createDatabaseQuery($searchRequests, $key);
                }
            } else {
                if (is_int($key) && (is_object($searchRequests) || is_array($searchRequests))) {
                    // It's a list of object to process
                    $searchRequests = (array) $searchRequests;
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
     * @throws \RestInternalServerErrorException
     */
    private function translateAgregateOperator(string $agregateOperator): string
    {
        if ($agregateOperator === self::AGREGATE_OPERATOR_AND) {
            return 'AND';
        } elseif ($agregateOperator === self::AGREGATE_OPERATOR_OR) {
            return 'OR';
        }
        throw new \RestInternalServerErrorException('Bad search operator');
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
            $value = $valueOrArray[$searchOperator];
        } else {
            $searchOperator = self::DEFAULT_SEARCH_OPERATOR;
            $value = $valueOrArray;
        }

        $type = \PDO::PARAM_STR;
        if (is_int($value)) {
            $type = \PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            $type = \PDO::PARAM_BOOL;
        } elseif ($searchOperator === self::OPERATOR_LIKE) {
            $value = '%' . $value . '%';
        }

        $bindKey = ':value_' . (count($this->bindValues) + 1);
        $this->bindValues[$bindKey] = [$type => $value];

        return sprintf(
            '%s %s %s',
            (array_key_exists($key, $this->concordance)
                ? $this->concordance[$key]
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
            case self::OPERATOR_EQUAL:
            default:
                return '=';
        }
    }

    /**
     * Retrieve the query operator of database based on the search operator.
     *
     * @param int $searchOperator Search operator
     * @return string Query operator to bind the column name and its value.
     * ( =, !=, <, <=, >, >=, LIKE)
     */
    private function getQueryOperator(int $searchOperator): string
    {
        switch ($searchOperator) {
            case self::OPERATOR_NOT_EQUAL:
                return '!=';
            case self::OPERATOR_LESS_THAN:
                return '<';
            case self::OPERATOR_LESS_THAN_OR_EQUAL:
                return '<=';
            case self::OPERATOR_GREATER_THAN:
                return '>';
            case self::OPERATOR_GREATER_THAN_OR_EQUAL:
                return '>=';
            case self::OPERATOR_LIKE:
                return 'LIKE';
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
     * @see Pagination::$search
     * &search={"field1": ..., "field2": ...}
     */
    public function setSearch(array $search): void
    {
        $this->search = $search;
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
     * @see Pagination::$concordance
     * @return array concordance table
     */
    public function getConcordance(): array
    {
        return $this->concordance;
    }

    /**
     * @param array $concordance concordance table
     * @see Pagination::$concordance
     */
    public function setConcordance(array $concordance): void
    {
        $this->concordance = $concordance;
    }

    /**
     * @see Pagination::$bindValues
     * @return array
     */
    public function getBindValues(): array
    {
        return $this->bindValues;
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
            self::NAME_FOR_SEARCH =>
                (!empty($this->search)
                    ? $this->search
                    : new \stdClass()),
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
     * extract parameter from search
     *
     * @param string $parameterToExtract
     * @return string return extracted parameter if found
     */
    public function extractParameter(string $parameterToExtract)
    {
        $parameters = (array)$this->getSearch();
        $extractFunction = null;
        $extractFunction = function (string $parameterToExtract, &$parameters) use (&$extractFunction) {
            foreach ($parameters as $key => &$value) {
                if ($key === $parameterToExtract) {
                    $extractedParameter = $parameters[$key];
                    unset($parameters[$key]);
                    return $extractedParameter;
                } elseif (is_array($value) || is_object($value)) {
                    $value = (array)$value;
                    $extractedParameter = $extractFunction($parameterToExtract, $value);
                    if (!is_null($extractedParameter)) {
                        return $extractedParameter;
                    }
                }
            }
            return null;
        };
        $extractedParameter = $extractFunction($parameterToExtract, $parameters);
        $this->setSearch($parameters);

        return $extractedParameter;
    }
}

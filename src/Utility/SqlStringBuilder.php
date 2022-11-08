<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Utility;

/**
 * This is *NOT* a SQL Builder.
 * This is a *String Builder* dedicated to SQL queries.
 *
 * > See it like a SQL "mutable string".
 * > No more, no less : only string concatenations.
 *
 * The aim is to facilitate string manipulations for SQL queries.
 * *DO NOT USE* for simple queries where a basic string is preferable.
 *
 * This class should very probably not have any evolution at all in the future !
 */
class SqlStringBuilder implements \Stringable
{
    private const SEPARATOR_JOINS = ' ';
    private const SEPARATOR_CONDITIONS = ' AND ';
    private const SEPARATOR_EXPRESSIONS = ', ';
    private const PREFIX_SELECT = 'SELECT ';
    private const PREFIX_FROM = 'FROM ';
    private const PREFIX_WHERE = 'WHERE ';
    private const PREFIX_GROUP_BY = 'GROUP BY ';
    private const PREFIX_HAVING = 'HAVING ';
    private const PREFIX_ORDER_BY = 'ORDER BY ';
    private const PREFIX_LIMIT = 'LIMIT ';

    /** @var bool */
    private bool $withNoCache = false;

    /** @var bool */
    private bool $withCalcFoundRows = false;

    /** @var array<string|\Stringable> */
    private array $select = [];

    /** @var string|\Stringable */
    private string|\Stringable $from = '';

    /** @var array<string|\Stringable> */
    private array $joins = [];

    /** @var array<string|\Stringable> */
    private array $where = [];

    /** @var array<string|\Stringable> */
    private array $groupBy = [];

    /** @var array<string|\Stringable> */
    private array $having = [];

    /** @var array<string|\Stringable> */
    private array $orderBy = [];

    /** @var string|\Stringable */
    private string|\Stringable $limit = '';

    /** @var array<string, array{mixed, int}> */
    private array $bindValues = [];

    public function __toString(): string
    {
        return $this->getSql();
    }

    /**
     * @param bool $bool
     *
     * @return $this
     */
    public function withCalcFoundRows(bool $bool): self
    {
        $this->withCalcFoundRows = $bool;

        return $this;
    }

    /**
     * @param bool $bool
     *
     * @return $this
     */
    public function withNoCache(bool $bool): self
    {
        $this->withNoCache = $bool;

        return $this;
    }

    /**
     * @param string|\Stringable ...$expressions
     *
     * @return $this
     */
    public function setSelect(string|\Stringable ...$expressions): self
    {
        $this->select = $this->trimPrefix(self::PREFIX_SELECT, ...$expressions);

        return $this;
    }

    /**
     * @param string|\Stringable ...$expressions
     *
     * @return $this
     */
    public function addSelect(string|\Stringable ...$expressions): self
    {
        $this->select = array_merge($this->select, $this->trimPrefix(self::PREFIX_SELECT, ...$expressions));

        return $this;
    }

    /**
     * @param string|\Stringable $table
     *
     * @return $this
     */
    public function setFrom(string|\Stringable $table): self
    {
        $this->from = $this->trimPrefix(self::PREFIX_FROM, $table)[0];

        return $this;
    }

    /**
     * @param string|\Stringable ...$joins
     *
     * @return $this
     */
    public function setJoins(string|\Stringable ...$joins): self
    {
        $this->joins = $joins;

        return $this;
    }

    /**
     * @param string|\Stringable ...$joins
     *
     * @return $this
     */
    public function addJoins(string|\Stringable ...$joins): self
    {
        $this->joins = array_merge($this->joins, $joins);

        return $this;
    }

    /**
     * @param string|\Stringable ...$conditions
     *
     * @return $this
     */
    public function setWhere(string|\Stringable ...$conditions): self
    {
        $this->where = $this->trimPrefix(self::PREFIX_WHERE, ...$conditions);

        return $this;
    }

    /**
     * @param string|\Stringable ...$conditions
     *
     * @return $this
     */
    public function addWhere(string|\Stringable ...$conditions): self
    {
        $this->where = array_merge($this->where, $this->trimPrefix(self::PREFIX_WHERE, ...$conditions));

        return $this;
    }

    /**
     * @param string|\Stringable ...$expressions
     *
     * @return $this
     */
    public function setGroupBy(string|\Stringable ...$expressions): self
    {
        $this->groupBy = $this->trimPrefix(self::PREFIX_GROUP_BY, ...$expressions);

        return $this;
    }

    /**
     * @param string|\Stringable ...$expressions
     *
     * @return $this
     */
    public function addGroupBy(string|\Stringable ...$expressions): self
    {
        $this->groupBy = array_merge($this->groupBy, $this->trimPrefix(self::PREFIX_GROUP_BY, ...$expressions));

        return $this;
    }

    /**
     * @param string|\Stringable ...$expressions
     *
     * @return $this
     */
    public function setHaving(string|\Stringable ...$expressions): self
    {
        $this->having = $this->trimPrefix(self::PREFIX_HAVING, ...$expressions);

        return $this;
    }

    /**
     * @param string|\Stringable ...$conditions
     *
     * @return $this
     */
    public function addHaving(string|\Stringable ...$conditions): self
    {
        $this->having = array_merge($this->having, $this->trimPrefix(self::PREFIX_HAVING, ...$conditions));

        return $this;
    }

    /**
     * @param string|\Stringable ...$expressions
     *
     * @return $this
     */
    public function setOrderBy(string|\Stringable ...$expressions): self
    {
        $this->orderBy = $this->trimPrefix(self::PREFIX_ORDER_BY, ...$expressions);

        return $this;
    }

    /**
     * @param string|\Stringable ...$expressions
     *
     * @return $this
     */
    public function addOrderBy(string|\Stringable ...$expressions): self
    {
        $this->orderBy = array_merge($this->orderBy, $this->trimPrefix(self::PREFIX_ORDER_BY, ...$expressions));

        return $this;
    }

    /**
     * @param string|\Stringable $limit
     *
     * @return $this
     */
    public function setLimit(string|\Stringable $limit): self
    {
        $this->limit = $this->trimPrefix(self::PREFIX_LIMIT, $limit)[0];

        return $this;
    }

    /**
     * This method serve only to store bind values in an easy way close to the sql strings.
     *
     * @param string $param
     * @param mixed $value
     * @param int $type
     *
     * @return $this
     */
    public function addBindValue(string $param, mixed $value, int $type = \PDO::PARAM_STR): self
    {
        $this->bindValues[$param] = [$value, $type];

        return $this;
    }

    /**
     * Empty the bindValues array.
     *
     * @return $this
     */
    public function clearBindValues(): self
    {
        $this->bindValues = [];

        return $this;
    }

    /**
     * @return array<string, array{mixed, int}> Format: $param => [$value, $type]
     */
    public function getBindValues(): array
    {
        return $this->bindValues;
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return rtrim(
            $this->buildSelect()
            . $this->buildFrom()
            . $this->buildJoins()
            . $this->buildWhere()
            . $this->buildGroupBy()
            . $this->buildHaving()
            . $this->buildOrderBy()
            . $this->buildLimit()
        );
    }

    /**
     * @return string
     */
    protected function buildSelect(): string
    {
        $noCache = $this->withNoCache ? 'SQL_NO_CACHE ' : '';
        $calcFoundRows = $this->withCalcFoundRows ? 'SQL_CALC_FOUND_ROWS ' : '';

        return self::PREFIX_SELECT . $noCache . $calcFoundRows
            . implode(self::SEPARATOR_EXPRESSIONS, $this->select ?: ['*']) . ' ';
    }

    /**
     * @return string
     */
    protected function buildFrom(): string
    {
        return self::PREFIX_FROM . $this->from . ' ';
    }

    /**
     * @return string
     */
    protected function buildJoins(): string
    {
        return [] === $this->joins ? '' : implode(self::SEPARATOR_JOINS, $this->joins) . ' ';
    }

    /**
     * @return string
     */
    protected function buildWhere(): string
    {
        return [] === $this->where ? ''
            : self::PREFIX_WHERE . implode(self::SEPARATOR_CONDITIONS, $this->where) . ' ';
    }

    /**
     * @return string
     */
    protected function buildGroupBy(): string
    {
        return [] === $this->groupBy ? ''
            : self::PREFIX_GROUP_BY . implode(self::SEPARATOR_EXPRESSIONS, $this->groupBy) . ' ';
    }

    /**
     * @return string
     */
    protected function buildHaving(): string
    {
        return [] === $this->having ? ''
            : self::PREFIX_HAVING . implode(self::SEPARATOR_CONDITIONS, $this->having) . ' ';
    }

    /**
     * @return string
     */
    protected function buildOrderBy(): string
    {
        return [] === $this->orderBy ? ''
            : self::PREFIX_ORDER_BY . implode(self::SEPARATOR_EXPRESSIONS, $this->orderBy) . ' ';
    }

    /**
     * @return string
     */
    protected function buildLimit(): string
    {
        return '' === $this->limit ? '' : self::PREFIX_LIMIT . $this->limit;
    }

    /**
     * We remove spaces and prefix in front of the string, and we skip empty string values.
     *
     * @param string $prefix
     * @param string|\Stringable ...$strings
     *
     * @return array<string|\Stringable>
     */
    protected function trimPrefix(string $prefix, string|\Stringable ...$strings): array
    {
        $regex = "!^\s*" . preg_quote(trim($prefix), '!') . "\s+!i";

        $sanitized = [];
        foreach ($strings as $string) {
            if ($string instanceof \Stringable) {
                $sanitized[] = $string;
            } elseif ('' !== $string) {
                $sanitized[] = preg_replace($regex, '', $string);
            }
        }

        return $sanitized;
    }
}

<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

namespace Centreon\Infrastructure\CentreonLegacyDB\Mapping;

use PDO;

class ClassMetadata
{
    public const COLUMN = 'column';
    public const TYPE = 'type';
    public const FORMATTER = 'formatter';

    /**
     * Table name of entity
     *
     * @var string
     */
    protected $tableName;

    /**
     * List of properties of entity vs columns in DB table
     *
     * @var array
     */
    protected $columns;

    /**
     * Name of property that is the primary key
     *
     * @var string
     */
    protected $primaryKey;

    /**
     * Set table name
     *
     * @param string $name
     * @return \self
     */
    public function setTableName($name): self
    {
        $this->tableName = $name;

        return $this;
    }

    /**
     * Get table name of entity
     *
     * @return string
     */
    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    /**
     * Add information about the property
     *
     * @param string $property      name of the property in the Entity class
     * @param string $columnName    name of the column in DB
     * @param int $dataType         type of data use PDO::PARAM_*
     * @param callable $dataFormatter
     * @param bool $primaryKey      is it PK
     * @return \self
     */
    public function add(
        string $property,
        string $columnName,
        int $dataType = PDO::PARAM_STR,
        callable $dataFormatter = null,
        $primaryKey = false
    ): self {
        $this->columns[$property] = [
            static::COLUMN => $columnName,
            static::TYPE => $dataType,
            static::FORMATTER => $dataFormatter,
        ];

        // mark property as primary kay
        if ($primaryKey === true) {
            $this->primaryKey = $property;
        }

        return $this;
    }

    /**
     * Has PK
     *
     * @return bool
     */
    public function hasPrimaryKey(): bool
    {
        return $this->primaryKey !== null;
    }

    /**
     * Get PK property
     *
     * @return string
     */
    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    /**
     * Get PK column
     *
     * @return string
     */
    public function getPrimaryKeyColumn(): ?string
    {
        return $this->hasPrimaryKey() ? $this->getColumn($this->getPrimaryKey()) : null;
    }

    /**
     * Check is property exists in metadata
     *
     * @param string $property
     * @return bool
     */
    public function has(string $property): bool
    {
        return array_key_exists($property, $this->columns);
    }

    /**
     * Get metadata of the property
     *
     * @param string $property
     * @return array|null
     */
    public function get(string $property): ?array
    {
        return $this->has($property) ? $this->columns[$property] : null;
    }

    /**
     * Get column name of the property
     *
     * @param string $property
     * @return string|null
     */
    public function getColumn(string $property): ?string
    {
        return $this->has($property) ? $this->columns[$property][static::COLUMN] : null;
    }

    /**
     * Get data for columns
     *
     * @return array|null
     */
    public function getColumns(): ?array
    {
        return $this->columns;
    }

    /**
     * Get data type of the property
     *
     * @param string $property
     * @return int|null
     */
    public function getType(string $property): int
    {
        return $this->has($property) ? $this->columns[$property][static::TYPE] : PDO::PARAM_INT;
    }

    /**
     * Get data formatter function
     *
     * @param string $property
     * @return callable|null
     */
    public function getFormatter(string $property): ?callable
    {
        return $this->has($property) ? $this->columns[$property][static::FORMATTER] : null;
    }

    /**
     * Get the property by the column name
     *
     * @param string $column
     * @return string|null
     */
    public function getProperty(string $column): ?string
    {
        foreach ($this->columns as $property => $data) {
            if (strtolower($data[self::COLUMN]) === strtolower($column)) {
                return $property;
            }
        }

        return null;
    }
}

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

namespace Centreon\Infrastructure\CentreonLegacyDB;

use PDO;
use PDOStatement;

/**
 * The purpose of the collector is to have the ability to bind a value data (parameters
 * and column too) to statement before the statement object to be initialized
 */
class StatementCollector
{
    /**
     * Collection of columns
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Collection of values
     *
     * @var array
     */
    protected $values = [];

    /**
     * Collection of parameters
     *
     * @var array
     */
    protected $params = [];

    /**
     * Add a column
     *
     * @param string $parameter
     * @param mixed $value
     * @param int $data_type
     */
    public function addColumn($parameter, $value, int $data_type = PDO::PARAM_STR)
    {
        $this->columns[$parameter] = [
            'value' => $value,
            'data_type' => $data_type,
        ];
    }

    /**
     * Add a value
     *
     * @param string $parameter
     * @param mixed $value
     * @param int $data_type
     */
    public function addValue($parameter, $value, int $data_type = PDO::PARAM_STR)
    {
        $this->values[$parameter] = [
            'value' => $value,
            'data_type' => $data_type,
        ];
    }

    /**
     * Add a parameter
     *
     * @param string $parameter
     * @param mixed $value
     * @param int $data_type
     */
    public function addParam($parameter, $value, int $data_type = PDO::PARAM_STR)
    {
        $this->params[$parameter] = [
            'value' => $value,
            'data_type' => $data_type,
        ];
    }

    /**
     * Bind collected data to statement
     *
     * @param PDOStatement $stmt
     */
    public function bind(PDOStatement $stmt)
    {
        // bind columns to statment
        foreach ($this->values as $parameter => $data) {
            $stmt->bindColumn($parameter, $data['value'], $data['data_type']);
        }

        // bind values to statment
        foreach ($this->values as $parameter => $data) {
            $stmt->bindValue($parameter, $data['value'], $data['data_type']);
        }

        // bind parameters to statment
        foreach ($this->values as $parameter => $data) {
            $stmt->bindParam($parameter, $data['value'], $data['data_type']);
        }
    }
}

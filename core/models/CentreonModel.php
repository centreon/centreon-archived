<?php
/*
 * Copyright 2005-2014 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace Centreon\Models;

use Centreon\Internal\Exception;
use Centreon\Internal\Di;

/**
 * Abtract class for manage models
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
abstract class CentreonModel
{
    /**
     * Database logical name
     *
     * @var string
     */
    protected static $databaseName = null;

    /**
     * List all objects with all their parameters
     * Data heavy, use with as many parameters as possible
     * in order to limit it
     *
     * @param mixed $parameterNames
     * @param int $count
     * @param int $offset
     * @param string $order
     * @param string $sort
     * @param array $filters
     * @param string $filterType
     * @return array
     * @throws Exception
     */
    public static function getList(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR",
        $tablesString = null,
        $staticFilter = null,
        $aAddFilters  = array()
    ) {
       
        if (is_string($filterType) && $filterType != "OR" && $filterType != "AND") {
            throw new Exception('Unknown filter type');
        } elseif (is_array($filterType)) {
            foreach ($filterType as $key => $type) {
                if ($type != "OR" && $type != "AND") {
                    throw new Exception('Unknown filter type');
                }
            }
            /* Add default if not set */
            if (!isset($filterType['*'])) {
                $filterType['*'] = 'OR';
            }
        }
        if (is_array($parameterNames)) {
            $params = implode(",", $parameterNames);
        } else {
            $params = $parameterNames;
        }
        $sql = "SELECT $params FROM ";
        if (is_null($tablesString)) {
            $sql .=  static::$table;
        } else {
            $sql .= $tablesString;
        } 

        if (!is_null($aAddFilters) && isset($aAddFilters['tables'])) {
            $sql .= ", ".implode(", ", $aAddFilters['tables']);
        }
       
        $filterTab = array();
        $nextFilterType = null;
        $first = true;
        if (false === is_null($staticFilter)) {
            $sql .= " WHERE " . $staticFilter;
            $first = false;
            $nextFilterType = "AND";
        } 
        
        if (count($filters)) {
            foreach ($filters as $key => $rawvalue) {
                if (is_array($rawvalue)) {
                    $filterStr = "(";
                    $filterStr .= join(" OR ",
                        array_pad(array(), count($rawvalue), $key . " LIKE ?")
                    );
                    $filterStr .= ")";
                    $filterTab = array_merge(
                        $filterTab,
                        array_map(
                            array('static', 'parseValueForSearch'),
                            $rawvalue
                        )
                    );
                } else {
                    $filterStr = $key . " LIKE ?";
                    $filterTab[] = CentreonBaseModel::parseValueForSearch($rawvalue);
                }
                if ($first) {
                    $sql .= " WHERE " . $filterStr;
                    $first = false;
                } else {
                    if (false === is_null($nextFilterType)) {
                        $sql .= " $nextFilterType " . $filterStr;
                    } elseif (is_string($filterType)) {
                        $sql .= " $filterType " . $filterStr;
                    } elseif (is_array($filterType)) {
                        if (isset($filterType[$key])) {
                            $sql .= $filterType[$key] . " " . $filterStr;
                        } else {
                            $sql .= $filterType['*'] . " " . $filterStr;
                        }
                    }
                }
            }
        }
        if (!is_null($aAddFilters) && isset($aAddFilters['join'])) {
            $sql .= " AND ".implode(" AND ", $aAddFilters['join']);
        }
             
        if (isset($order) && isset($sort) && (strtoupper($sort) == "ASC" || strtoupper($sort) == "DESC")) {
            $sql .= " ORDER BY $order $sort ";
        }
        if (isset($count) && $count != -1) {
            $db = Di::getDefault()->get(static::$databaseName);
            $sql = $db->limit($sql, $count, $offset);
        }
        //echo $sql;
        return static::getResult($sql, $filterTab, "fetchAll");
    }
    
    /**
     * List all objects with all their parameters
     * Data heavy, use with as many parameters as possible
     * in order to limit it
     *
     * @param mixed $parameterNames
     * @param int $count
     * @param int $offset
     * @param string $order
     * @param string $sort
     * @param array $filters
     * @param string $filterType
     * @return array
     * @throws Exception
     */
    public static function getListBySearch(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR",
        $tablesString = null,
        $staticFilter = null,
        $aAddFilters = array()
    ) {
        
        $searchFilters = array();
        foreach ($filters as $name => $values) {
            if (is_array($values)) {
                $searchFilters[$name] = array_map(function($value) {
                    return '%' . $value . '%';
                }, $values);
            } else {
                $searchFilters[$name] = '%' . $values . '%';
            }
        }

        return static::getList(
            $parameterNames,
            $count,
            $offset,
            $order,
            $sort,
            $searchFilters,
            $filterType,
            $tablesString,
            $staticFilter,
            $aAddFilters
        );
    }

    /**
     * Get result
     *
     * @param string $sql The SQL query
     * @param array $params The list of params
     * @return array The fetch all of result
     */
    protected static function getResult($sql, $params = array())
    {
        $db = Di::getDefault()->get(static::$databaseName);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Convert value for searching
     *
     * @param string $value The value to parse
     * @return string
     */
    protected static function parseValueForSearch($value)
    {
        $value = trim($value);
        $value = str_replace("\\", "\\\\", $value);
        $value = str_replace("_", "\_", $value);
        $value = str_replace(" ", "\ ", $value);
        return $value;
    }
}

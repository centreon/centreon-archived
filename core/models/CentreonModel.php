<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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
        $aAddFilters  = array(),
        $sGroup = array()
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

        $tablesColumns = self::getColumns();

        if (!is_array($parameterNames)) {
            $parameterNames = explode (",", $parameterNames);
        }

        foreach ($parameterNames as &$parameterName) {
            if (!preg_match('/\.|count\(|concat\(|"/i', $parameterName)) {
                foreach ($tablesColumns as $tempTableName => $tempColumnNames) {
                    if (in_array($parameterName, $tempColumnNames)) {
                        $parameterName = $tempTableName . '.' . $parameterName;
                    }
                }
            }
        }

        $params = implode(",", $parameterNames);

        $sql = "SELECT DISTINCT $params FROM ";
        if (is_null($tablesString)) {
            $tableToParse = static::$table;
            $primaryKey = static::$primaryKey;
            if (isset(static::$aclResourceType)) {
                $aclResourceType = static::$aclResourceType;
            }
            $sql .= static::$table;
        } else {
            $explodedTables = explode(',',$tablesString);
            $tableToParse = $explodedTables[0];
            if (isset(static::$primaryKey)) {
                $primaryKey = static::$primaryKey;
                if (isset(static::$aclResourceType)) {
                    $aclResourceType = static::$aclResourceType;
                }
            } else if (isset(static::$firstObject)) {
                $firstObject = static::$firstObject;
                $primaryKey = $firstObject::$primaryKey;
                if (isset($firstObject::$aclResourceType)) {
                    $aclResourceType = $firstObject::$aclResourceType;
                }
            }
            $sql .= $tablesString;
        }

        if (isset($aclResourceType) && isset($_SESSION['user']) && !$_SESSION['user']->isAdmin()) {
            $sql .= ', cfg_acl_resources_cache, cfg_usergroups, cfg_users_usergroups_relations, cfg_acl_resources, cfg_acl_resources_usergroups_relations';
        }

        if (!is_null($aAddFilters) && isset($aAddFilters['tables'])) {
            $sql .= ", ".implode(", ", $aAddFilters['tables']);
        }

        $filterTab = array();
        $nextFilterType = null;
        $first = true;
        if (!is_null($staticFilter)) {
            $sql .= " WHERE " . $staticFilter;
            $first = false;
            $nextFilterType = "AND";
        }
        
        if (count($filters)) {
            foreach ($filters as $key => $rawvalue) {
                $completeKey = $key;
                if (!preg_match('/\.|count\(|concat\(|"/i', $key)) {
                    foreach ($tablesColumns as $tempTableName => $tempColumnNames) {
                        if (in_array($key, $tempColumnNames)) {
                            $completeKey =  $tempTableName . '.' . $key;
                        }
                    }
                }

                if (is_array($rawvalue)) {
                    $filterStr = "(";
                    $filterStr .= join(" OR ",
                        array_pad(array(), count($rawvalue), $completeKey . " LIKE ?")
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
                    $filterStr = $completeKey . " LIKE ?";
                    $filterTab[] = self::parseValueForSearch($rawvalue);
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

        if (isset($aclResourceType) && isset($_SESSION['user']) && !$_SESSION['user']->isAdmin()) {
            if ($first) {
                $sql .= " WHERE ";
                $first = false;
            } else {
                $sql .= " AND ";
            }

            $sql .= " cfg_users_usergroups_relations.user_id = " . $_SESSION['user']->getId()
                . " AND cfg_users_usergroups_relations.usergroup_id = cfg_acl_resources_usergroups_relations.usergroup_id"
                . " AND cfg_acl_resources_usergroups_relations.acl_resource_id = cfg_acl_resources.acl_resource_id"
                . " AND cfg_acl_resources.acl_resource_id = cfg_acl_resources_cache.acl_resource_id"
                . " AND cfg_acl_resources_cache.resource_type = " . $aclResourceType
                . " AND cfg_acl_resources_cache.resource_id = ";

            $tempTable = preg_split('/\s+/', $tableToParse);
            if (isset($tempTable[1]) && is_string($tempTable[1])) {
                $sql .= $tempTable[1] . "." . $primaryKey;
            } else if (isset($tempTable[0]) && is_string($tempTable[0])) {
                $sql .= $tempTable[0] . "." . $primaryKey;
            } else {
                $sql .= $primaryKey;
            }
        }

        if (!is_null($aAddFilters) && isset($aAddFilters['join'])) {
            $sql .= " AND ".implode(" AND ", $aAddFilters['join']);
        }
       
        if (!empty($sGroup) && isset($sGroup['nb']) && isset($sGroup['sField'])) {
           $iNb = $sGroup['nb']  - 1;
           $sql .= " GROUP BY ".$sGroup['sField']." having count(*) > ".$iNb;
        }

        if (isset($order) && isset($sort) && (strtoupper($sort) == "ASC" || strtoupper($sort) == "DESC")) {
            $sql .= " ORDER BY $order $sort ";
        }

        if (isset($count) && $count != -1) {
            $db = Di::getDefault()->get(static::$databaseName);
            $sql = $db->limit($sql, $count, $offset);
        }
        
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
        $aAddFilters = array(),
        $sGroup = array()
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
            $aAddFilters,
            $sGroup
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

     /**
     * Get columns
     *
     * @return array
     */
    public static function getColumns()
    {
        $result = array();
        $tables = array();

        if (isset(static::$databaseName)) {
            if(isset(static::$table)){
                $tables[] = static::$table;
            }

            if(isset(static::$firstObject)){
                $firstObject = static::$firstObject;
                if (isset($firstObject::$table)) {
                    $tables[] = $firstObject::$table;
                }
            }

            if(isset(static::$secondObject)){
                $secondObject = static::$secondObject;
                if (isset($secondObject::$table)) {
                    $tables[] = $secondObject::$table;
                }
            }
        }

        foreach ($tables as $table) {
            $aliasTable = explode(" ", $table);
            if (isset($aliasTable[1])) {
                $showTableName = $aliasTable[0];
                $aliasTable = $aliasTable[1];
            } else {
                $showTableName = $table;
                $aliasTable = $table;
            }

            $db = Di::getDefault()->get(static::$databaseName);
            $stmt = $db->prepare("SHOW COLUMNS FROM " . $showTableName);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $result[$aliasTable][] = $row['Field'];
            }
        }
        return $result;
    }
}

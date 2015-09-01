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

use Centreon\Internal\Di;
use Centreon\Internal\Exception;

/**
 * Abstract Centreon Object class
 *
 * @author sylvestre
 */
abstract class CentreonStorageBaseModel
{
    /**
     * Table name of the object
     */
    protected static $table = null;

    /**
     * Primary key name
     */
    protected static $primaryKey = null;

    /**
     * Unique label field
     */
    protected static $uniqueLabelField = null;


    /**
     * Array of relation objects 
     *
     * @var array of strings
     */
    protected static $relations = array();

    /**
     * Get result from sql query
     *
     * @param string $sqlQuery
     * @param array $sqlParams
     * @param string $fetchMethod
     * @return array
     */
    protected static function getResult($sqlQuery, $sqlParams = array(), $fetchMethod = "fetchAll")
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sqlQuery);
        //print $sqlQuery."\n";
        //print_r($sqlParams);
        $stmt->execute($sqlParams);
        $result = $stmt->{$fetchMethod}(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Set attribute properties
     *
     * @param array $not_null_attributes attributes that cannot be set to null
     * @param array $is_int_attribute attributes that are int based
     */
    protected static function setAttributeProps($params, &$not_null_attributes, &$is_int_attribute)
    {
        $params = (array)$params;
        if (array_search("", $params)) {
            $sql_attr = "SHOW FIELDS FROM " . static::$table;
            $res = static::getResult($sql_attr, array(), "fetchAll");
            foreach ($res as $tab) {
                if ($tab['Null'] == 'No') {
                    $not_null_attributes[$tab['Field']] = true;
                }
                if (strstr($tab['Type'], 'int')) {
                    $is_int_attribute[$tab['Field']] = true;
                }
            }
        }
    }

    /**
     * Used for inserting object into database
     *
     * @param array $params
     * @return int
     */
    public static function insert($params = array())
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = "INSERT INTO " . static::$table;
        $sqlFields = "";
        $sqlValues = "";
        $sqlParams = array();
        $not_null_attributes = array();
        $is_int_attribute = array();
        static::setAttributeProps($params, $not_null_attributes, $is_int_attribute);

        foreach ($params as $key => $value) {
            if ($key == static::$primaryKey || is_null($value)) {
                continue;
            }
            if ($sqlFields != "") {
                $sqlFields .= ",";
            }
            if ($sqlValues != "") {
                $sqlValues .= ",";
            }
            $sqlFields .= $key;
            $sqlValues .= "?";
            if ($value == "" && !isset($not_null_attributes[$key])) {
                $value = null;
            } elseif (!is_numeric($value) && isset($is_int_attribute[$key])) {
                $value = null;
            }
            $type = \PDO::PARAM_STR;
            if (is_null($value)) {
                $type = \PDO::PARAM_NULL;
            }
            $sqlParams[] = array('value' => trim($value), 'type' => $type);
        }
        if ($sqlFields && $sqlValues) {
            $sql .= "(".$sqlFields.") VALUES (".$sqlValues.")";
            $stmt = $db->prepare($sql);
            $i = 1;
            foreach ($sqlParams as $v) {
                $stmt->bindValue($i, $v['value'], $v['type']);
                $i++;
            }
            $stmt->execute();
            return $db->lastInsertId(static::$table, static::$primaryKey);
        }
        return null;
    }

    /**
     * Used for deleteing object from database
     *
     * @param int $objectId
     */
    public static function delete($objectId, $notFoundError = true)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = "DELETE FROM  " . static::$table . " WHERE ". static::$primaryKey . " = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($objectId));
    }

    /**
     * Used for updating object in database
     *
     * @param int $objectId
     * @param array $params
     * @return void
     */
    public static function update($objectId, $params = array())
    {
        $sql = "UPDATE " . static::$table . " SET ";
        $sqlUpdate = "";
        $sqlParams = array();
        $not_null_attributes = array();
        $is_int_attribute = array();
        static::setAttributeProps($params, $not_null_attributes, $is_int_attribute);

        foreach ($params as $key => $value) {
            if ($key == static::$primaryKey) {
                continue;
            }
            if ($sqlUpdate != "") {
                $sqlUpdate .= ",";
            }
            $sqlUpdate .= $key . " = ? ";
            if ($value == "" && !isset($not_null_attributes[$key])) {
                $value = null;
            } elseif (!is_numeric($value) && isset($is_int_attribute[$key])) {
                $value = null;
            }
            $type = \PDO::PARAM_STR;
            if (is_null($value)) {
                $type = \PDO::PARAM_NULL;
            }
            $sqlParams[] = array('value' => $value, 'type' => $type);
        }

        if ($sqlUpdate) {
            $db = Di::getDefault()->get('db_centreon');
            $sqlParams[] = array('value' => $objectId, 'type' => \PDO::PARAM_INT);
            $sql .= $sqlUpdate . " WHERE " . static::$primaryKey . " =  ?";
            $stmt = $db->prepare($sql);
            $i = 1;
            foreach ($sqlParams as $v) {
                $stmt->bindValue($i, $v['value'], $v['type']);
                $i++;
            }
            $stmt->execute();
        }
    }

    /**
     * Used for duplicating object
     *
     * @param int $sourceObjectId
     * @param int $duplicateEntries
     */
    public static function duplicate($sourceObjectId, $duplicateEntries = 1)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sourceParams = static::getParameters($sourceObjectId, "*");
        if (isset($sourceParams[static::$primaryKey])) {
            unset($sourceParams[static::$primaryKey]);
        }
        $originalName = $sourceParams[static::$uniqueLabelField];
        $firstKeyCopy = array();
        $secondKeyCopy = array();
        foreach (static::$relations as $relation) {
            $relationObj = new $relation();
            if ($relation::$firstObject == "\\".get_called_class()) {
                $firstKeyCopy[$relation] = $relationObj->getTargetIdFromSourceId(
                    $relationObj->getSecondKey(),
                    $relationObj->getFirstKey(),
                    $sourceObjectId
                );
            } elseif ($relation::$secondObject == "\\".get_called_class()) {
                $secondKeyCopy[$relation] = $relationObj->getTargetIdFromSourceId(
                    $relationObj->getFirstKey(),
                    $relationObj->getSecondKey(),
                    $sourceObjectId
                );
            }
            unset($relationObj);
        }
        $i = 1;
        $j = 1;
        while ($i <= $duplicateEntries) {
            if (isset($sourceParams[static::$uniqueLabelField]) && isset($originalName)) {
                $sourceParams[static::$uniqueLabelField] = $originalName . "_" . $j;
            }
            $ids = static::getIdByParameter(static::$uniqueLabelField, array($sourceParams[static::$uniqueLabelField]));
            if (!count($ids)) {
                $lastId = static::insert($sourceParams);
                $db->beginTransaction();
                foreach ($firstKeyCopy as $relation => $idArray) {
                    foreach ($idArray as $relationId) {
                        $relation::insert($lastId, $relationId);
                    }
                }
                foreach ($secondKeyCopy as $relation => $idArray) {
                    foreach ($idArray as $relationId) {
                        $relation::insert($relationId, $lastId);
                    }
                }
                $db->commit();
                $i++;
            }
            $j++;
        }
    }

    /**
     * Get object parameters
     *
     * @param int $objectId
     * @param mixed $parameterNames
     * @return array
     */
    public static function getParameters($objectId, $parameterNames)
    {
        if (is_array($parameterNames)) {
            $params = implode(",", $parameterNames);
        } else {
            $params = $parameterNames;
        }
        $sql = "SELECT $params FROM " . static::$table . " WHERE ". static::$primaryKey . " = ?";
        return static::getResult($sql, array($objectId), "fetch");
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
    public static function getList(
        $parameterNames = "*",
        $additionnalTables = "",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR",
        $filterForTables = array()
    ) {
        if ($filterType != "OR" && $filterType != "AND") {
            throw new Exception('Unknown filter type');
        }

        if (is_array($parameterNames)) {
            $params = implode(",", $parameterNames);
        } else {
            $params = $parameterNames;
        }
        
        if (is_array($additionnalTables)) {
            $tables = implode(",", $additionnalTables);
        } else {
            $tables = $additionnalTables;
        }
        
        $firstFilter = true;
        $tablesFilter = '';
        if (!empty($tables)) {
            $tables = ', ' . $tables;
            foreach ($filterForTables as $key => $rawvalue) {
                $value = trim($rawvalue);
                if ($firstFilter) {
                    $tablesFilter .= " WHERE $key = $value ";
                } else {
                    $tablesFilter .= " $filterType $key = '$value' ";
                }
                $firstFilter = false;
            }
        }
        
        $sql = "SELECT $params FROM " . static::$table . $tables . $tablesFilter;
        $filterTab = array();
        if (count($filters)) {
            foreach ($filters as $key => $rawvalue) {
                if (strstr($key, 'enabled')) {
                    $COMP = '=';
                } else {
                    $COMP = 'LIKE';
                }
                if ($firstFilter) {
                    $sql .= " WHERE $key $COMP ? ";
                } else {
                    $sql .= " $filterType $key $COMP ? ";
                }
                $value = trim($rawvalue);
                $value = str_replace("\\", "\\\\", $value);
                $value = str_replace("_", "\_", $value);
                $value = str_replace(" ", "\ ", $value);
                if (!strstr($key, 'enabled')) {
                    $filterTab[] = '%'.$value.'%';
                } else {
                    $filterTab[] = $value;
                }
                
                $firstFilter = false;
            }
        }
        if (isset($order) && isset($sort) && (strtoupper($sort) == "ASC" || strtoupper($sort) == "DESC")) {
            $sql .= " ORDER BY $order $sort ";
        }
        if (isset($count) && $count != -1) {
            $db = Di::getDefault()->get('db_centreon');
            $sql = $db->limit($sql, $count, $offset);
        }
        return static::getResult($sql, $filterTab, "fetchAll");
    }
    
    /**
     * 
     * @param type $id
     * @param type $parameterNames
     * @return type
     */
    public static function get($id, $parameterNames = "*")
    {
        if (is_array($parameterNames)) {
            $params = implode(",", $parameterNames);
        } else {
            $params = $parameterNames;
        }
        $sql = "SELECT $params FROM " . static::$table;
        $sql .= " WHERE " . static::$primaryKey . " LIKE ? ";
        $result = static::getResult($sql, array($id), "fetchAll");
        return $result[0];
    }

    /**
     * Generic method that allows to retrieve object ids
     * from another object parameter
     *
     * @param string $paramName
     * @param array $paramValues
     * @return array
     */
    public static function getIdByParameter($paramName, $paramValues = array())
    {
        $sql = "SELECT " . static::$primaryKey . " FROM " . static::$table . " WHERE ";
        $condition = "";
        if (!is_array($paramValues)) {
            $paramValues = array($paramValues);
        }
        foreach ($paramValues as $val) {
            if ($condition != "") {
                $condition .= " OR ";
            }
            $condition .= $paramName . " = ? ";
        }
        if ($condition) {
            $sql .= $condition;
            $rows = static::getResult($sql, $paramValues, "fetchAll");
            $tab = array();
            foreach ($rows as $val) {
                $tab[] = $val[static::$primaryKey];
            }
            return $tab;
        }
        return array();
    }

    /**
     * Generic method that allows to retrieve object ids
     * from another object parameter
     *
     * @param string $name
     * @param array $args
     * @return array
     * @throws Exception
     */
    public function __call($name, $args)
    {
        if (preg_match('/^getIdBy([a-zA-Z0-9_]+)/', $name, $matches)) {
            return static::getIdByParameter($matches[1], $args);
        } else {
            throw new Exception('Unknown method');
        }
    }

    /**
     * Primary Key Getter
     *
     * @return string
     */
    public static function getPrimaryKey()
    {
        return static::$primaryKey;
    }

    /**
     * Unique label field getter
     *
     * @return string
     */
    public static function getUniqueLabelField()
    {
        return static::$uniqueLabelField;
    }

    /**
     * Get relations
     *
     * @return array
     */
    public static function getRelations()
    {
        return static::$relations;
    }
    
    /**
     * 
     * @param string $uniqueFieldvalue
     * @param integer $id
     * @return boolean
     */
    public static function isUnique($uniqueFieldvalue, $id)
    {
        $isUnique = true;
        $dbconn = Di::getDefault()->get('db_centreon');
        $unicityRequest = "SELECT ".static::$uniqueLabelField.", ".static::$primaryKey
            ." FROM " . static::$table
            ." WHERE " . static::$uniqueLabelField . " = :uniqueValue";
        $stmt = $dbconn->prepare($unicityRequest);
        $stmt->bindParam(':uniqueValue', $uniqueValue, \PDO::PARAM_STR);
        $stmt->execute();
        $resultUnique = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (count($resultUnique) > 0) {
            if ($resultUnique[static::$primaryKey] == $id) {
                $isUnique = true;
            } else {
                $isUnique = false;
            }
        }
        
        return $isUnique;
    }

    /**
     * Get Table Name
     *
     * @return string
     */
    public static function getTableName()
    {
        return static::$table;
    }

    /**
     * Get columns
     *
     * @return array
     */
    public static function getColumns()
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("SHOW COLUMNS FROM " . static::$table);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach ($rows as $row) {
            $result[] = $row['Field'];
        }
        return $result;
    }
}

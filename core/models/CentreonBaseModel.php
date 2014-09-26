<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
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

use \Centreon\Internal\Exception;
use \Centreon\Internal\Di;

/**
 * Abstract Centreon Object class
 *
 * @author sylvestre
 */
abstract class CentreonBaseModel extends CentreonModel
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
     * Database logical name
     *
     * @var string
     */
    protected static $databaseName = 'db_centreon';


    /**
     * Array of relation objects 
     *
     * @var array of strings
     */
    protected static $relations = array();


    const OBJ_NOT_EXIST = 'Object not in database.';

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
                if (strtoupper($tab['Null']) == 'NO') {
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
        $db = Di::getDefault()->get(static::$databaseName);
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
            if (is_null($value)) {
                $type = \PDO::PARAM_NULL;
            } else if (isset($is_int_attribute[$key])) {
                $type = \PDO::PARAM_INT;
            } else {
                $type = \PDO::PARAM_STR;
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
    public static function delete($objectId)
    {
        $db = Di::getDefault()->get(static::$databaseName);
        $sql = "DELETE FROM  " . static::$table . " WHERE ". static::$primaryKey . " = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($objectId));
        if (1 !== $stmt->rowCount()) {
            throw new Exception(static::OBJ_NOT_EXIST);
        }
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
            $paramKey = ":{$key}";
            $sqlUpdate .= $key . " = {$paramKey} ";
            if ($value == "" && !isset($not_null_attributes[$key])) {
                $value = null;
            } elseif (!is_numeric($value) && isset($is_int_attribute[$key])) {
                $value = null;
            }
            if (is_null($value)) {
                $type = \PDO::PARAM_NULL;
            } else if (isset($is_int_attribute[$key])) {
                $type = \PDO::PARAM_INT;
            } else {
                $type = \PDO::PARAM_STR;
            }
            $sqlParams[$paramKey] = array('value' => $value, 'type' => $type);
        }

        if ($sqlUpdate) {
            $db = Di::getDefault()->get(static::$databaseName);
            $sqlParams[':source_object_id'] = array('value' => $objectId, 'type' => \PDO::PARAM_INT);
            $sql .= $sqlUpdate . " WHERE " . static::$primaryKey . " =  :source_object_id";
            $stmt = $db->prepare($sql);
            foreach ($sqlParams as $k => $v) {
                $stmt->bindParam($k, $v['value'], $v['type']);
            }
            $stmt->execute();
            if (1 !== $stmt->rowCount()) {
                throw new Exception(static::OBJ_NOT_EXIST);
            }
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
        $db = Di::getDefault()->get(static::$databaseName);
        $sourceParams = static::getParameters($sourceObjectId, "*");
        if (false === $sourceParams) {
            throw new Exception(static::OBJ_NOT_EXIST);
        }
        if (isset($sourceParams[static::$primaryKey])) {
            unset($sourceParams[static::$primaryKey]);
        }
        /* If multiple unique field */
        if (is_array(static::$uniqueLabelField)) {
            $originalName = array();
            foreach (static::$uniqueLabelField as $uniqueField) {
                $originalName[$uniqueField] = $sourceParams[$uniqueField];
            }
        } else {
            $originalName = $sourceParams[static::$uniqueLabelField];
        }
        /* Get relations */
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
        /* Add the number for new entries */
        while ($i <= $duplicateEntries) {
            /* Test if unique fields are unique */
            if (is_array(static::$uniqueLabelField)) {
                $unique = true;
                foreach (static::$uniqueLabelField as $uniqueField) {
                    $sourceParams[$uniqueField] = $originalName[$uniqueField] . '_' . $j;
                    if (false === self::isUnique($originalName[$uniqueField] . '_' . $j, $sourceObjectId, $uniqueField)) {
                        $unique = false;
                    }
                }
            } else {
                $unique = false;
                $sourceParams[static::$uniqueLabelField] = $originalName . '_' . $j;
                if (self::isUnique($originalName . '_' . $j, $sourceObjectId)) {
                    $unique = true;
                }
            }
            if ($unique) {
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
        $result = static::getResult($sql, array($objectId), "fetch");

        /* Raise exception if object doesn't exist */
        if (false === $result) {
            throw new Exception(static::OBJ_NOT_EXIST);
        }
        if (count($result) !== 1) {
            throw new Exception(static::OBJ_NOT_EXIST);
        }
        return $result[0];
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
        if (1 !== count($result)) {
            throw new Exception(static::OBJ_NOT_EXIST);
        }
        return $result[0];
    }

    /**
     * Generic method that allows to retrieve object ids
     * from another object parameter
     *
     * @param string $paramName
     * @param array $paramValues
     * @param array $extraConditions used for precising query with AND clauses
     * @return array
     */
    public static function getIdByParameter($paramName, $paramValues = array(), $extraConditions = array())
    {
        $sql = "SELECT " . static::$primaryKey . " FROM " . static::$table . " WHERE ";
        $condition = "";
        if (!is_array($paramValues)) {
            $paramValues = array($paramValues);
        }
        foreach ($paramValues as $val) {
            if ($condition != "") {
                $condition .= " OR ";
            } else {
                $condition .= "(";
            }
            $condition .= $paramName . " = ? ";
        }
        if ($condition) {
            $condition .= ")";
            $sql .= $condition;
            if (is_array($extraConditions)) {
                foreach ($extraConditions as $k => $v) {
                    $sql .= " AND $k = ? ";
                    $paramValues[] = $v;
                }
            }
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
    public static function isUnique($uniqueFieldvalue, $id = 0, $fieldName=null)
    {
        $dbconn = Di::getDefault()->get(static::$databaseName);
        /* Test if the field name is in unique field */
        if (false === is_null($fieldName)) {
            if (is_array(static::$uniqueLabelField) && false === in_array($fieldName, static::$uniqueLabelField)) {
                throw new Exception(); // @TODO Exception text
            } elseif (is_string(static::$uniqueLabelField) && $fieldName != static::$uniqueLabelField) {
                throw new Exception(); // @TODO Exception text
            }
        }
        $columns = array();
        $unicityRequest = 'SELECT count(' . static::$primaryKey . ') as nb
            FROM ' . static::$table . '
            WHERE ' . static::$primaryKey . ' != :id AND ';
        if (false === is_null($fieldName)) {
            $unicityRequest .= $fieldName . ' = :' . $fieldName;
            $columns[] = $fieldName;
        } elseif (is_array(static::$uniqueLabelField)) {
            $unicityRequest .= "(" . join(
                ' OR ',
                array_map(
                    function($value) { return $value . ' = :' . $value; }
                    , static::$uniqueLabelField
                )
            ) . ")";
            $columns = static::$uniqueLabelField;
        } else {
            $unicityRequest .= static::$uniqueLabelField . ' = :' . static::$uniqueLabelField;
            $columns[] = static::$uniqueLabelField;
        }
        $stmt = $dbconn->prepare($unicityRequest);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        foreach ($columns as $column) {
            $stmt->bindParam(':' . $column, $uniqueFieldvalue);
        }
        $stmt->execute();
        $resultUnique = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($resultUnique['nb'] > 0) {
            return false;
        }
        return true;
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
        $db = Di::getDefault()->get(static::$databaseName);
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

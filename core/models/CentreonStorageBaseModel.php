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
        $db = \Centreon\Internal\Di::getDefault()->get('db_storage');
        $stmt = $db->prepare($sqlQuery);
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
        $db = \Centreon\Internal\Di::getDefault()->get('db_storage');
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
    public static function delete($objectId)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_storage');
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
            $db = \Centreon\Internal\Di::getDefault()->get('db_storage');
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
        $db = \Centreon\Internal\Di::getDefault()->get('db_storage');
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
                if ($firstFilter) {
                    $sql .= " WHERE $key LIKE ? ";
                } else {
                    $sql .= " $filterType $key LIKE ? ";
                }
                $value = trim($rawvalue);
                $value = str_replace("\\", "\\\\", $value);
                $value = str_replace("_", "\_", $value);
                $value = str_replace(" ", "\ ", $value);
                $filterTab[] = $value;
                $firstFilter = false;
            }
        }
        if (isset($order) && isset($sort) && (strtoupper($sort) == "ASC" || strtoupper($sort) == "DESC")) {
            $sql .= " ORDER BY $order $sort ";
        }
        if (isset($count) && $count != -1) {
            $db = \Centreon\Internal\Di::getDefault()->get('db_storage');
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
        $dbconn = \Centreon\Internal\Di::getDefault()->get('db_storage');
        $unicityRequest = "SELECT ".static::$uniqueLabelField.", ".static::$primaryKey
            ." FROM ".static::$table
            ." WHERE ".static::$uniqueLabelField."='$uniqueFieldvalue'";
        $stmt = $dbconn->query($unicityRequest);
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
        $db = \Centreon\Internal\Di::getDefault()->get('db_storage');
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

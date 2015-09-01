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
 * Centreon Object Relation
 *
 * @author sylvestre
 */
abstract class CentreonRelationModel extends CentreonModel
{
    protected static $errMsg = 'Object not in database.';

    /**
     * Relation Table
     */
    protected static $relationTable = null;

    /**
     * First key
     */
    protected static $firstKey = null;

    /**
     * Second key
     */
    protected static $secondKey = null;

    /**
     * @var string
     */
    public static $firstObject = null;

    /**
     *
     * @var string
     */
    public static $secondObject = null;

    /**
     * Database logical name
     *
     * @var string
     */
    protected static $databaseName = 'db_centreon';

    /**
     * Used for inserting relation into database
     *
     * @param int $fkey The value of first relation key
     * @param int $skey The value of first relation key
     * @param array $extra The list of key value for extra fields for the relation
     */
    public static function insert($fkey, $skey, $extra = array())
    {
    	$sql = "INSERT INTO " . static::$relationTable . " ( " . static::$firstKey . ", " . static::$secondKey;
        if (count($extra) > 0) {
            $sql .= ", " . join(', ', array_keys($extra));
        }
        $sql .= ") VALUES (?, ?";
        if (count($extra) > 0) {
            $sql .= ", " . join(', ', array_fill(0, count($extra), '?'));
        }
        $sql .= ")";
        $db = Di::getDefault()->get(static::$databaseName);
        $stmt = $db->prepare($sql);
        $values = array_values($extra);
        array_unshift($values, $fkey, $skey);
        $stmt->execute($values);
    }

    /**
     * Used for deleting relation from database
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public static function delete($fkey, $skey = null)
    {
        if (!is_null($fkey) && !is_null($skey)) {
            $sql = "DELETE FROM " . static::$relationTable .
                " WHERE " . static::$firstKey . " = ? AND " . static::$secondKey . " = ?";
            $args = array($fkey, $skey);
        } elseif (!is_null($skey)) {
            $sql = "DELETE FROM " . static::$relationTable . " WHERE ". static::$secondKey . " = ?";
            $args = array($skey);
        } else {
            $sql = "DELETE FROM " . static::$relationTable . " WHERE " . static::$firstKey . " = ?";
            $args = array($fkey);
        }
        $db = Di::getDefault()->get(static::$databaseName);
        $stmt = $db->prepare($sql);
        $stmt->execute($args);
        /*if (0 === $stmt->rowCount()) {
            throw new Exception(self::$errMsg);
        }*/
    }

    /**
     * Get Merged Parameters from seperate tables
     *
     * @param array $firstTableParams
     * @param array $secondTableParams
     * @param int $count
     * @param string $order
     * @param string $sort
     * @param array $filters
     * @param string $filterType
     * @param array $relationTableParams
     * @return array
     */
    public static function getMergedParameters(
        $firstTableParams = array(),
        $secondTableParams = array(),
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR",
        $relationTableParams = array()
    ) {
        /* Convert params for getList */
        $params = array();
        $firstObject = static::$firstObject;
        $secondObject = static::$secondObject;
        array_walk($firstTableParams, array('static', 'addTablePrefix'), $firstObject::getTableName());
        array_walk($secondTableParams, array('static', 'addTablePrefix'), $secondObject::getTableName());
        array_walk($relationTableParams, array('static', 'addTablePrefix'), static::$relationTable);
        $params = array_merge($firstTableParams, $secondTableParams, $relationTableParams);
        if (count($params) == 0) {
            $params = '*';
        }
        $listTable = $firstObject::getTableName() . ", " . $secondObject::getTableName() . ", " . static::$relationTable;
        $staticFilter = $firstObject::getTableName() . "." . $firstObject::getPrimaryKey() . " = " . static::$relationTable . "." . static::$firstKey;
        $staticFilter .= " AND ";
        $staticFilter .= $secondObject::getTableName() . "." . $secondObject::getPrimaryKey() . " = " . static::$relationTable . "." . static::$secondKey;

        return static::getList($params, $count, $offset, $order, $sort, $filters, $filterType, $listTable, $staticFilter);
    }

    /**
     * 
     * @param type $firstTableParams
     * @param type $secondTableParams
     * @param type $count
     * @param type $offset
     * @param type $order
     * @param type $sort
     * @param type $filters
     * @param type $filterType
     * @param type $relationTableParams
     * @return type
     */
    public static function getMergedParametersBySearch(
        $firstTableParams = array(),
        $secondTableParams = array(),
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR",
        $relationTableParams = array(),
        $aAddFilters = array(),
        $aGroup = array()
    ) {
        /* Convert params for getList */
        $params = array();
        $firstObject = static::$firstObject;
        $secondObject = static::$secondObject;
        array_walk($firstTableParams, array('static', 'addTablePrefix'), $firstObject::getTableName());
        array_walk($secondTableParams, array('static', 'addTablePrefix'), $secondObject::getTableName());
        array_walk($relationTableParams, array('static', 'addTablePrefix'), static::$relationTable);
        $params = array_merge($firstTableParams, $secondTableParams, $relationTableParams);
        if (count($params) == 0) {
            $params = '*';
        }
        $listTable = $firstObject::getTableName() . ", " . $secondObject::getTableName() . ", " . static::$relationTable;
        $staticFilter = $firstObject::getTableName() . "." . $firstObject::getPrimaryKey() . " = " . static::$relationTable . "." . static::$firstKey;
        $staticFilter .= " AND ";
        $staticFilter .= $secondObject::getTableName() . "." . $secondObject::getPrimaryKey() . " = " . static::$relationTable . "." . static::$secondKey;
        
        return static::getListBySearch($params, $count, $offset, $order, $sort, $filters, $filterType, $listTable, $staticFilter, $aAddFilters, $aGroup);
    }

    /**
     * Get target id from source id
     *
     * @param int $sourceKey
     * @param int $targetKey
     * @param array $sourceId
     * @return array
     */
    public static function getTargetIdFromSourceId($targetKey, $sourceKey, $sourceId)
    {
        if (!is_array($sourceId)) {
            $sourceId = array($sourceId);
        }
        $sql = "SELECT $targetKey FROM " . static::$relationTable . " WHERE $sourceKey = ?";
        $result = static::getResult($sql, $sourceId);
        $tab = array();
        foreach ($result as $rez) {
            $tab[] = $rez[$targetKey];
        }
        return $tab;
    }

    /**
     * Generic method that allows to retrieve target ids
     * from another another source id
     *
     * @param string $name
     * @param array $args
     * @return array
     * @throws Exception
     */
    public function __call($name, $args = array())
    {
        if (!count($args)) {
            throw new Exception('Missing arguments');
        }
        if (!isset(static::$secondKey)) {
            throw new Exception("Not a relation table");
        }
        if (preg_match('/^get([a-zA-Z0-9_]+)From([a-zA-Z0-9_]+)/', $name, $matches)) {
            if (($matches[1] != static::$firstKey && $matches[1] != static::$secondKey) ||
                ($matches[2] != static::$firstKey && $matches[2] != static::$secondKey)) {
                throw new Exception('Unknown field');
            }
            return static::getTargetIdFromSourceId($matches[1], $matches[2], $args);
        } elseif (preg_match('/^delete_([a-zA-Z0-9_]+)/', $name, $matches)) {
            if ($matches[1] == static::$firstKey) {
                static::delete($args[0]);
            } elseif ($matches[1] == static::$secondKey) {
                static::delete(null, $args[0]);
            } else {
                throw new Exception('Unknown field');
            }
        } else {
            throw new Exception('Unknown method');
        }
    }

    /**
     * Get First Key
     *
     * @return string
     */
    public static function getFirstKey()
    {
        return static::$firstKey;
    }

    /**
     * Get Second Key
     *
     * @return string
     */
    public static function getSecondKey()
    {
        return static::$secondKey;
    }

    /**
     * Add table prefix to a column
     *
     * @param string $value The value
     * @param string $key The column name
     * @param string $prefix The table name
     */
    private static function addTablePrefix(&$value, $key, $prefix)
    {
        $value = $prefix . '.' . $value;
    }
}

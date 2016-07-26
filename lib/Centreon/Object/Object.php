<?php
/*
 * Copyright 2005-2015 CENTREON
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
 */

require_once "Centreon/Db/Manager/Manager.php";
require_once "Centreon/Cache/Manager/Manager.php";

/**
 * Abstract Centreon Object class
 *
 * @author sylvestre
 */
abstract class Centreon_Object
{
    /**
     * Database Connector
     */
    protected $db;

    /**
     * Database Cache
     */
    protected $cache;

    /**
     * Use cache or not
     */
    protected $useCache;

    /**
     * Table name of the object
     */
    protected $table = null;

    /**
     * Primary key name
     */
    protected $primaryKey = null;

    /**
     * Unique label field
     */
    protected $uniqueLabelField = null;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = Centreon_Db_Manager::factory('centreon');
        $this->cache = Centreon_Cache_Manager::factory('centreonObjects', 60, '/tmp/');
        $this->useCache = false;
    }

    /**
     * Get result from sql query or from cache
     *
     * @param string $sqlQuery
     * @param array $sqlParams
     * @param string $fetchMethod
     * @return array
     */
    protected function getResult($sqlQuery, $sqlParams = array(), $fetchMethod = "fetchAll")
    {
        $cacheFileName = Centreon_Cache_Manager::getCacheFileName($sqlQuery, $sqlParams);
        if (($this->useCache === false) || ($result = $this->cache->load($cacheFileName)) === false) {
            $res = $this->db->query($sqlQuery, $sqlParams);
            $result = $res->{$fetchMethod}();
            if ($this->useCache === true) {
                $this->cache->save($result, $cacheFileName);
            }
        }
        return $result;
    }

    /**
     * Used for inserting object into database
     *
     * @param array $params
     * @return int
     */
    public function insert($params = array())
    {
        $sql = "INSERT INTO $this->table ";
        $sqlFields = "";
        $sqlValues = "";
        $sqlParams = array();
        foreach ($params as $key => $value) {
            if ($key == $this->primaryKey) {
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
            $sqlParams[] = trim($value);
        }
        if ($sqlFields && $sqlValues) {
            $sql .= "(".$sqlFields.") VALUES (".$sqlValues.")";
            $this->db->query($sql, $sqlParams);
            return $this->db->lastInsertId($this->table, $this->primaryKey);
        }
        return null;
    }

    /**
     * Used for deleteing object from database
     *
     * @param int $objectId
     */
    public function delete($objectId)
    {
        $sql = "DELETE FROM  $this->table WHERE $this->primaryKey = ?";
        $this->db->query($sql, array($objectId));
    }

    /**
     * Used for updating object in database
     *
     * @param int $objectId
     * @param array $params
     * @return void
     */
    public function update($objectId, $params = array())
    {
        $sql = "UPDATE $this->table SET ";
        $sqlUpdate = "";
        $sqlParams = array();
        $not_null_attributes = array();

        if (array_search("", $params)) {
            $sql_attr = "SHOW FIELDS FROM $this->table";
            $res = $this->getResult($sql_attr, array(), "fetchAll");
            foreach ($res as $tab) {
                if ($tab['Null'] == 'NO') {
                    $not_null_attributes[$tab['Field']] = true;
                }
            }
        }

        foreach ($params as $key => $value) {
            if ($key == $this->primaryKey) {
                continue;
            }
            if ($sqlUpdate != "") {
                $sqlUpdate .= ",";
            }
            $sqlUpdate .= $key . " = ? ";
            if ($value == "" && !isset($not_null_attributes[$key])) {
                $value = null;
            }
            $value = str_replace("<br/>", "\n", $value);
            $sqlParams[] = $value;
        }

        if ($sqlUpdate) {
            $sqlParams[] = $objectId;
            $sql .= $sqlUpdate . " WHERE $this->primaryKey = ?";
            $this->db->query($sql, $sqlParams);
        }
    }

    /**
     * Used for duplicating object
     *
     * @param int $sourceObjectId
     * @param int $duplicateEntries
     * @todo relations
     */
    public function duplicate($sourceObjectId, $duplicateEntries = 1)
    {
        $sourceParams = $this->getParameters($sourceObjectId, "*");
        if (isset($sourceParams[$this->primaryKey])) {
            unset($sourceParams[$this->primaryKey]);
        }
        if (isset($sourceParams[$this->uniqueLabelField])) {
            $originalName = $sourceParams[$this->uniqueLabelField];
        }
        $originalName = $sourceParams[$this->uniqueLabelField];
        for ($i = 1; $i <= $duplicateEntries; $i++) {
            if (isset($sourceParams[$this->uniqueLabelField]) && isset($originalName)) {
                $sourceParams[$this->uniqueLabelField] = $originalName . "_" . $i;
            }
            $ids = $this->getIdByParameter($this->uniqueLabelField, array($sourceParams[$this->uniqueLabelField]));
            if (!count($ids)) {
                $this->insert($sourceParams);
            }
        }
    }

    /**
     * Get object parameters
     *
     * @param int $objectId
     * @param mixed $parameterNames
     * @return array
     */
    public function getParameters($objectId, $parameterNames)
    {
        if (is_array($parameterNames)) {
            $params = implode(",", $parameterNames);
        } else {
            $params = $parameterNames;
        }
        $sql = "SELECT $params FROM $this->table WHERE $this->primaryKey = ?";
        return $this->getResult($sql, array($objectId), "fetch");
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
    public function getList($parameterNames = "*", $count = -1, $offset = 0, $order = null, $sort = "ASC", $filters = array(), $filterType = "OR")
    {
        if ($filterType != "OR" && $filterType != "AND") {
            throw new Exception('Unknown filter type');
        }
        if (is_array($parameterNames)) {
            $params = implode(",", $parameterNames);
        } else {
            $params = $parameterNames;
        }
        $sql = "SELECT $params FROM $this->table ";
        $filterTab = array();
        if (count($filters)) {
            foreach ($filters as $key => $rawvalue) {
                if (!count($filterTab)) {
                    $sql .= " WHERE $key LIKE ? ";
                } else {
                    $sql .= " $filterType $key LIKE ? ";
                }
                $value = trim($rawvalue);
                $value = str_replace("\\", "\\\\", $value);
                $value = str_replace("_", "\_", $value);
                $value = str_replace(" ", "\ ", $value);
                $filterTab[] = $value;
            }
        }
        if (isset($order) && isset($sort) && (strtoupper($sort) == "ASC" || strtoupper($sort) == "DESC")) {
            $sql .= " ORDER BY $order $sort ";
        }
        if (isset($count) && $count != -1) {
            $sql = $this->db->limit($sql, $count, $offset);
        }
        return $this->getResult($sql, $filterTab, "fetchAll");
    }

    /**
     * Generic method that allows to retrieve object ids
     * from another object parameter
     *
     * @param string $paramName
     * @param array $paramValues
     * @return array
     */
    public function getIdByParameter($paramName, $paramValues = array())
    {
        $sql = "SELECT $this->primaryKey FROM $this->table WHERE ";
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
            $rows = $this->getResult($sql, $paramValues, "fetchAll");
            $tab = array();
            foreach ($rows as $val) {
                $tab[] = $val[$this->primaryKey];
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
            return $this->getIdByParameter($matches[1], $args);
        } else {
            throw new Exception('Unknown method');
        }
    }

    /**
     * Primary Key Getter
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Unique label field getter
     *
     * @return string
     */
    public function getUniqueLabelField()
    {
        return $this->uniqueLabelField;
    }

    /**
     * Get Table Name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * Set Cache
     *
     * @param bool $value
     * @return void
     */
    public function setCache($value)
    {
        $this->useCache = $value;
    }
}

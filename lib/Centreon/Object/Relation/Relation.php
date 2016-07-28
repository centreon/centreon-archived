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
 * Centreon Object Relation
 *
 * @author sylvestre
 */
abstract class Centreon_Object_Relation
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
     * Relation Table
     */
    protected $relationTable = null;

    /**
     * First key
     */
    protected $firstKey = null;

    /**
     * Second key
     */
    protected $secondKey = null;

    /**
     *
     * @var bool
     */
    protected $useCache;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = Centreon_Db_Manager::factory('centreon');
        $this->cache = Centreon_Cache_Manager::factory('centreonRelations');
        $this->useCache = false;
    }

    /**
     * Used for inserting relation into database
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public function insert($fkey, $skey = null)
    {
        $sql = "INSERT INTO $this->relationTable ($this->firstKey, $this->secondKey) VALUES (?, ?)";
        $this->db->query($sql, array($fkey, $skey));
    }

    /**
     * Used for deleting relation from database
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public function delete($fkey, $skey = null)
    {
        if (isset($fkey) && isset($skey)) {
            $sql = "DELETE FROM $this->relationTable WHERE $this->firstKey = ? AND $this->secondKey = ?";
            $args = array($fkey, $skey);
        } elseif (isset($skey)) {
            $sql = "DELETE FROM $this->relationTable WHERE $this->secondKey = ?";
            $args = array($skey);
        } else {
            $sql = "DELETE FROM $this->relationTable WHERE $this->firstKey = ?";
            $args = array($fkey);
        }
        $this->db->query($sql, $args);
    }

    protected function getResult($sql, $params = array())
    {
        $cacheFileName = Centreon_Cache_Manager::getCacheFileName($sql, $params);
        if (($this->useCache === false) || ($result = $this->cache->load($cacheFileName)) === false) {
            $res = $this->db->query($sql, $params);
            $result = $res->fetchAll();
            if ($this->useCache == true) {
                $this->cache->save($result, $cacheFileName);
            }
        }
        return $result;
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
     * @return array
     */
    public function getMergedParameters($firstTableParams = array(), $secondTableParams = array(), $count = -1, $offset = 0, $order = null, $sort = "ASC", $filters = array(), $filterType = "OR")
    {
        if (!isset($this->firstObject) || !isset($this->secondObject)) {
            throw new Exception('Unsupported method on this object');
        }
        $fString = "";
        $sString = "";
        foreach ($firstTableParams as $fparams) {
            if ($fString != "") {
                $fString .= ",";
            }
            $fString .= $this->firstObject->getTableName().".".$fparams;
        }
        foreach ($secondTableParams as $sparams) {
            if ($fString != "" || $sString != "") {
                $sString .= ",";
            }
            $sString .= $this->secondObject->getTableName().".".$sparams;
        }
        $sql = "SELECT ".$fString.$sString."
        		FROM ".$this->firstObject->getTableName().",".$this->secondObject->getTableName().",".$this->relationTable."
        		WHERE ".$this->firstObject->getTableName().".".$this->firstObject->getPrimaryKey()." = ".$this->relationTable.".".$this->firstKey."
        		AND ".$this->relationTable.".".$this->secondKey." = ".$this->secondObject->getTableName().".".$this->secondObject->getPrimaryKey();
        $filterTab = array();
        if (count($filters)) {
            foreach ($filters as $key => $rawvalue) {
                $sql .= " $filterType $key LIKE ? ";
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
        $result = $this->getResult($sql, $filterTab);
        return $result;
    }


    /**
     * Get target id from source id
     *
     * @param int $sourceKey
     * @param int $targetKey
     * @param array $sourceId
     * @return array
     */
    public function getTargetIdFromSourceId($targetKey, $sourceKey, $sourceId)
    {
        if (!is_array($sourceId)) {
            $sourceId = array($sourceId);
        }
        $sql = "SELECT $targetKey FROM $this->relationTable WHERE $sourceKey = ?";
        $result = $this->getResult($sql, $sourceId);
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
        if (!isset($this->secondKey)) {
            throw new Exception("Not a relation table");
        }
        if (preg_match('/^get([a-zA-Z0-9_]+)From([a-zA-Z0-9_]+)/', $name, $matches)) {
            if (($matches[1] != $this->firstKey && $matches[1] != $this->secondKey) ||
                ($matches[2] != $this->firstKey && $matches[2] != $this->secondKey)) {
                throw new Exception('Unknown field');
            }
            return $this->getTargetIdFromSourceId($matches[1], $matches[2], $args);
        } elseif (preg_match('/^delete_([a-zA-Z0-9_]+)/', $name, $matches)) {
            if ($matches[1] == $this->firstKey) {
                $this->delete($args[0]);
            } elseif ($matches[1] == $this->secondKey) {
                $this->delete(null, $args[0]);
            } else {
                throw new Exception('Unknown field');
            }
        } else {
            throw new Exception('Unknown method');
        }
    }

    /**
     * Set cache
     *
     * @param bool $value
     * @return void
     */
    public function setCache($value)
    {
        $this->useCache = $value;
    }

    /**
     * Get First Key
     *
     * @return string
     */
    public function getFirstKey()
    {
        return $this->firstKey;
    }

    /**
     * Get Second Key
     *
     * @return string
     */
    public function getSecondKey()
    {
        return $this->secondKey;
    }
}

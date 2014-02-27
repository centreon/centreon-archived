<?php

namespace Models\Configuration;

/**
 * Centreon Object Relation
 *
 * @author sylvestre
 */
abstract class Relation
{
    /**
     * Database Connector
     */
    protected $db;

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
     * @var string
     */
    public static $firstObject = null;

    /**
     *
     * @var string
     */
    public static $secondObject = null;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = \Centreon\Core\Di::getDefault()->get('db_centreon');
        $this->firstObj = new static::$firstObject();
        $this->secondObj = new static::$secondObject();
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
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($fkey, $skey));
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
        $stmt = $this->db->prepare($sql);
        $stmt->execute($args);
    }

    protected function getResult($sql, $params = array())
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
     * @param array $relationTableParams
     * @return array
     */
    public function getMergedParameters($firstTableParams = array(), $secondTableParams = array(), $count = -1, 
        $offset = 0, $order = null, $sort = "ASC", $filters = array(), $filterType = "OR", 
        $relationTableParams = array())
    {
        if (!isset($this->firstObj) || !isset($this->secondObj)) {
            throw new Exception('Unsupported method on this object');
        }
        $fString = "";
        $sString = "";
        $rString = "";
        foreach ($firstTableParams as $fparams) {
            if ($fString != "") {
                $fString .= ",";
            }
            $fString .= $this->firstObj->getTableName().".".$fparams;
        }
        foreach ($secondTableParams as $sparams) {
            if ($fString != "" || $sString != "") {
                $sString .= ",";
            }
            $sString .= $this->secondObj->getTableName().".".$sparams;
        }
        foreach ($relationTableParams as $rparams) {
            if ($fString != "" || $sString != "" || $rString != "") {
                $rString .= ",";
            }
            $rString .= $this->relationTable.".".$rparams;
        }
        $sql = "SELECT $fString $sString $rString
        		FROM ".$this->firstObj->getTableName().",".$this->secondObj->getTableName().",".$this->relationTable."
        		WHERE ".$this->firstObj->getTableName().".".$this->firstObj->getPrimaryKey()." = ".$this->relationTable.".".$this->firstKey."
        		AND ".$this->relationTable.".".$this->secondKey." = ".$this->secondObj->getTableName().".".$this->secondObj->getPrimaryKey();
        $filterTab = array();
        if (count($filters)) {
            foreach ($filters as $key => $rawvalue) {
                $sql .= " $filterType $key LIKE ? ";
                $value = trim ($rawvalue);
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

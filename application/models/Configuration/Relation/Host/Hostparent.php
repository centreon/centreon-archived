<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Hostparent extends Relation
{
    protected $relationTable = "host_hostparent_relation";
    protected $firstKey;
    protected $secondKey;
    protected $firstObject = "\\Models\\Configuration\\Host";
    protected $secondObject = "\\Models\\Configuration\\Host";

    /**
     * 
     * @param type $relationType
     */
    public function __construct($relationType)
    {
        if (strtolower($relationType) === 'child') {
            $this->firstKey = "host_host_id";
            $this->secondKey = "host_parent_hp_id";
        } else {
            $this->firstKey = "host_parent_hp_id";
            $this->secondKey = "host_host_id";
        }
        parent::__construct();
        $this->firstObj = new $this->firstObject();
        $this->secondObj = new $this->secondObject();
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
        if (!isset($this->firstObj) || !isset($this->secondObj)) {
            throw new Exception('Unsupported method on this object');
        }
        $fString = "";
        $sString = "";
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
        
        $sql = "SELECT ".$fString.$sString."
        		FROM host, ".$this->relationTable."
        		WHERE ".$this->firstObj->getTableName().".".$this->firstObj->getPrimaryKey()." = ".$this->relationTable.".".$this->firstKey;
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
}

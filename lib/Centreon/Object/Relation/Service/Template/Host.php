<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Service_Template_Host extends Centreon_Object_Relation
{
    protected $relationTable = "host_service_relation";
    protected $firstKey = "service_service_id";
    protected $secondKey = "host_host_id";

    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Service_Template();
        $this->secondObject = new Centreon_Object_Host_Template();
    }

    /**
     * Insert host template / host relation
     * Order has importance
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public function insert($fkey, $skey)
    {
        $sql = "INSERT INTO $this->relationTable ($this->secondKey, $this->firstKey) VALUES (?, ?)";
        $this->db->query($sql, array($fkey, $skey));
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
        $sql = "SELECT $targetKey FROM $this->relationTable WHERE $sourceKey = ? ";
        $result = $this->getResult($sql, $sourceId);
        $tab = array();
        foreach ($result as $rez) {
            $tab[] = $rez[$targetKey];
        }
        return $tab;
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
            $fString .= "h.".$fparams;
        }
        foreach ($secondTableParams as $sparams) {
            if ($fString != "" || $sString != "") {
                $sString .= ",";
            }
            $sString .= "h2.".$sparams;
        }
        $sql = "SELECT ".$fString.$sString."
        		FROM ".$this->firstObject->getTableName()." h,".$this->relationTable."
        		JOIN ".$this->secondObject->getTableName(). " h2 ON ".$this->relationTable.".".$this->firstKey." = h2.".$this->secondObject->getPrimaryKey() ."
        		WHERE h.".$this->firstObject->getPrimaryKey()." = ".$this->relationTable.".".$this->secondKey;
        $filterTab = array();
        if (count($filters)) {
            foreach ($filters as $key => $rawvalue) {
                $sql .= " $filterType $key LIKE ? ";
                $value = trim($rawvalue);
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
     * Delete host template / host relation
     * Order has importance
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public function delete($fkey, $skey)
    {
        if (isset($fkey) && isset($skey)) {
            $sql = "DELETE FROM $this->relationTable WHERE $this->firstKey = ? AND $this->secondKey = ?";
            $args = array($skey, $fkey);
        } elseif (isset($skey)) {
            $sql = "DELETE FROM $this->relationTable WHERE $this->firstKey = ?";
            $args = array($skey);
        } else {
            $sql = "DELETE FROM $this->relationTable WHERE $this->secondKey = ?";
            $args = array($fkey);
        }
        $this->db->query($sql, $args);
        
    }
}

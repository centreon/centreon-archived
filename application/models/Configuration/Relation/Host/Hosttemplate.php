<?php

namespace Models\Configuration;

class Relation\Host\Hosttemplate extends Centreon_Object_Relation
{
    protected $relationTable = "host_template_relation";
    protected $firstKey = "host_tpl_id";
    protected $secondKey = "host_host_id";
    public static $firstObject = "Models\\Configuration\\Host";
    public static $secondObject = "Models\\Configuration\\Host";

    public function __construct()
    {
        parent::__construct();
        $this->firstObj = new static::$firstObject();
        $this->secondObj = new static::$secondObject();
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
        $sql = "SELECT MAX(`order`) as maxorder FROM " .$this->relationTable . " WHERE " .$this->secondKey . " = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($skey));
        $row = $stmt->fetch();
        $order = 1;
        if (isset($row['maxorder'])) {
            $order = $row['maxorder']+1;
        }
        unset($res);
        $sql = "INSERT INTO $this->relationTable ($this->firstKey, $this->secondKey, `order`) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($fkey, $skey, $order));
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
        $sql = "SELECT $targetKey FROM $this->relationTable WHERE $sourceKey = ? ORDER BY `order`";
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
        if (!isset($this->firstObj) || !isset($this->secondObj)) {
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
        		FROM ".$this->firstObj->getTableName()." h,".$this->relationTable."
        		JOIN ".$this->secondObject->getTableName(). " h2 ON ".$this->relationTable.".".$this->firstKey." = h2.".$this->secondObj->getPrimaryKey() ."
        		WHERE h.".$this->firstObj->getPrimaryKey()." = ".$this->relationTable.".".$this->secondKey;
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
}

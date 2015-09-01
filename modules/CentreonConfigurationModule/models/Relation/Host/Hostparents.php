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

namespace CentreonConfiguration\Models\Relation\Host;

use Centreon\Internal\Di;
use Centreon\Models\CentreonRelationModel;

class Hostparents extends CentreonRelationModel
{
    protected static $relationTable = "cfg_hosts_hostparents_relations";
    protected static $firstKey = "host_parent_hp_id";
    protected static $secondKey = "host_host_id";
    public static $firstObject = "\CentreonConfiguration\Models\Host";
    public static $secondObject = "\CentreonConfiguration\Models\Host";
    
    /**
     * Used for inserting relation into database
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public static function insert($fkey, $skey = null)
    {
        $sql = "INSERT INTO " . static::$relationTable . " ( " . static::$firstKey . ", " . static::$secondKey . ") 
            VALUES (?, ?)";
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array($skey, $fkey));
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
    public static function getMergedParameters(
        $firstTableParams = array(),
        $secondTableParams = array(),
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR"
    ) {
        $fString = "";
        $sString = "";
        $firstObj = static::$firstObject;
        foreach ($firstTableParams as $fparams) {
            if ($fString != "") {
                $fString .= ",";
            }
            $fString .= $firstObj::getTableName().".".$fparams;
        }
        $secondObj = static::$secondObject;
        foreach ($secondTableParams as $sparams) {
            if ($fString != "" || $sString != "") {
                $sString .= ",";
            }
            $sString .= $secondObj::getTableName().".".$sparams;
        }
        
        $sql = "SELECT ".$fString.$sString."
        		FROM cfg_hosts, ".static::$relationTable."
        		WHERE ".$firstObj::getTableName().".".$firstObj::getPrimaryKey()
                ." = ".static::$relationTable.".".static::$firstKey;
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
            $db = Di::getDefault()->get('db_centreon');
            $sql = $db->limit($sql, $count, $offset);
        }
        $result = static::getResult($sql, $filterTab);
        return $result;
    }
}

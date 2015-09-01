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

namespace CentreonConfiguration\Models\Relation\Trap;

use Centreon\Internal\Di;
use Centreon\Models\CentreonRelationModel;

class Servicetemplate extends CentreonRelationModel
{
    protected static $relationTable = "cfg_traps_services_relations";
    protected static $firstKey = "traps_id";
    protected static $secondKey = "service_id";
    public static $firstObject = "\CentreonConfiguration\Models\Trap";
    public static $secondObject = "\CentreonConfiguration\Models\Servicetemplate";
    
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
        $fString = "";
        $sString = "";
        $rString = "";
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
        foreach ($relationTableParams as $rparams) {
            if ($fString != "" || $sString != "" || $rString != "") {
                $rString .= ",";
            }
            $rString .= static::$relationTable.".".$rparams;
        }
        $sql = "SELECT $fString $sString $rString
        		FROM ". $firstObj::getTableName().",".$secondObj::getTableName().",". static::$relationTable."
        		WHERE ".$firstObj::getTableName().".".$firstObj::getPrimaryKey()
                    ." = ".static::$relationTable.".".static::$firstKey."
        		AND ".static::$relationTable.".".static::$secondKey
                    ." = ".$secondObj::getTableName().".".$secondObj::getPrimaryKey().
                " AND " . $secondObj::getTableName() . ".service_register = '0'";
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

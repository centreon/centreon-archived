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


namespace CentreonConfiguration\Models\Relation\Host;

use \Centreon\Models\CentreonRelationModel;

class Hosttemplate extends CentreonRelationModel
{
    protected static $relationTable = "host_template_relation";
    protected static $firstKey = "host_host_id";
    protected static $secondKey = "host_tpl_id";
    public static $firstObject = "\CentreonConfiguration\Models\Host";
    public static $secondObject = "\CentreonConfiguration\Models\Host";

    /**
     * Insert host template / host relation
     * Order has importance
     *
     * @param int $fkey
     * @param int $skey
     * @return void
     */
    public static function insert($fkey, $skey)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $sql = "SELECT MAX(`order`) as maxorder FROM " .static::$relationTable . " WHERE " .static::$firstKey . " = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($fkey));
        $row = $stmt->fetch();
        $order = 1;
        if (isset($row['maxorder'])) {
            $order = $row['maxorder']+1;
        }
        unset($res);
        $sql = "INSERT INTO ".static::$relationTable
            ." (".static::$firstKey.", ".static::$secondKey.", `order`) "
            . "VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
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
    public static function getTargetIdFromSourceId($targetKey, $sourceKey, $sourceId)
    {
        if (!is_array($sourceId)) {
            $sourceId = array($sourceId);
        }
        $sql = "SELECT $targetKey FROM ".static::$relationTable." WHERE $sourceKey = ? ORDER BY `order`";
        $result = static::getResult($sql, $sourceId);
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
        if (!isset(static::$firstObject) || !isset(static::$secondObject)) {
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
        $firstObject = static::$firstObject;
        $secondObject = static::$secondObject;
        $sql = "SELECT ".$fString.$sString."
        		FROM ".$firstObject::getTableName()." h,".static::$relationTable."
        		JOIN ".$secondObject::getTableName()
                ." h2 ON ".static::$relationTable.".".static::$secondKey
                ." = h2.".$secondObject::getPrimaryKey() ."
        		WHERE h.".$firstObject::getPrimaryKey()." = ".static::$relationTable.".".static::$firstKey;
        $filterTab = array();
        if (count($filters)) {
            foreach ($filters as $key => $rawvalue) {
                $key = str_replace('host.', 'h.', $key);
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
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        if (isset($count) && $count != -1) {
            $sql = $db->limit($sql, $count, $offset);
        }
        $result = static::getResult($sql, $filterTab);
        return $result;
    }
}

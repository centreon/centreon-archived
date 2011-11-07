<?php
/**
 * Copyright 2005-2011 MERETHIS
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
 */


require_once $centreon_path . 'www/class/centreonDB.class.php';
require_once $centreon_path . 'www/class/centreonGMT.class.php';

class CentreonUtils
{

    /**
     * Converts Object into Array
     *
     * @param mixed $arrObjData
     * @param array $arrSkipIndices
     * @return mixed
     */
    public function objectIntoArray($arrObjData, $arrSkipIndices = array())
    {
        $arrData = array();

        if (is_object($arrObjData)) {
            $arrObjData = get_object_vars($arrObjData);
        }

        if (is_array($arrObjData)) {
            foreach ($arrObjData as $index => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = self::objectIntoArray($value, $arrSkipIndices);
                }
                if (in_array($index, $arrSkipIndices)) {
                    continue;
                }
                $arrData[$index] = $value;
            }
        }
        if (!count($arrData)) {
            $arrData = "";
        }
        return $arrData;
    }

    /**
     * Condition Builder
     * Creates condition with WHERE or AND or OR
     *
     * @param string $query
     * @param string $condition
     * @param bool $or
     * @return string
     */
    public function conditionBuilder($query, $condition, $or = false)
    {
        if (preg_match('/ WHERE /', $query)) {
            if ($or === true) {
                $query .= " OR ";
            } else {
                $query .= " AND ";
            }
        } else {
            $query .= " WHERE ";
        }
        $query .= $condition  . " ";
        return $query;
    }

    /**
     * Get datetime timestamp
     *
     * @param string $datetime
     * @return int
     * @throws Exception
     */
    public function getDateTimeTimestamp($datetime)
    {
        static $db;
        static $centreonGmt;

        $invalidString = "Date format is not valid";
        if (!isset($db)) {
            $db = new CentreonDB();
        }
        if (!isset($centreonGmt)) {
            $centreonGmt = new CentreonGMT($db);
        }
        $datetime = trim($datetime);
        $res = explode(" ", $datetime);
        if (count($res) != 2) {
            throw new Exception($invalidString);
        }
        $res1 = explode("/", $res[0]);
        if (count($res1) != 3) {
            throw new Exception($invalidString);
        }
        $res2 = explode(":", $res[1]);
        if (count($res2) != 2) {
            throw new Exception($invalidString);
        }
        $timestamp = mktime($res2[0], $res2[1], "0", $res1[1], $res1[2], $res1[0]);
        $timestamp = $centreonGmt->getUTCDate($timestamp);
        return $timestamp;
    }

    /**
     * Convert operand to Mysql format
     *
     * @param string $str
     * @return string;
     */
    public function operandToMysqlFormat($str)
    {
        $result = "";
        switch ($str) {
            case "gt": $result = ">"; break;
            case "lt": $result = "<"; break;
            case "gte": $result = ">="; break;
            case "lte": $result = "<="; break;
            case "eq": $result = "="; break;
            case "ne": $result = "!="; break;
            case "like": $result = "LIKE"; break;
            case "notlike": $result = "NOT LIKE"; break;
            default: $result = ""; break;
        }
        return $result;
    }
}
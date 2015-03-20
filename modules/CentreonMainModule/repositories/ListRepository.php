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
 */

namespace CentreonMain\Repository;

use Centreon\Internal\Exception as CentreonException;


class ListRepository extends BasicRepository
{
    /**
     * 
     * @param integer $id
     * @param string $params
     * @param boolean $normalize
     * @return array
     */
    public static function get($id, $params = '*', $normalize = true, $strict = true)
    {
        if (!empty(static::$secondaryObjectClass)) {
            $class = static::$secondaryObjectClass;
        } else {
            $class = static::$objectClass;
        }
        
        try {
            $dataset = $class::get($id, $params);
        } catch (CentreonException $e) {
            
        }
        
        if ($normalize) {
            self::normalizeDataset($dataset, $strict);
        }
        
        return $dataset;
    }
    
    /**
     * 
     * @param array $dataset
     */
    public static function normalizeDataset(&$dataset, $strict = true)
    {
        
    }
    
    
    /**
     * 
     * @param type $fields
     * @param type $count
     * @param type $offset
     * @param type $order
     * @param type $sort
     * @param type $filters
     * @param type $filterType
     * @return type
     */
    public static function getList(
        $fields = '*',
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = 'asc',
        $filters = array(),
        $filterType = 'OR'
    ) {
        if (!empty(static::$secondaryObjectClass)) {
            $class = static::$secondaryObjectClass;
        } else {
            $class = static::$objectClass;
        }
        
        return $class::getList($fields, $count, $offset, $order, $sort, $filters, $filterType);
    }
    
    /**
     * 
     * @param type $id
     * @param type $params
     * @return type
     */
    public static function load($id, $params = '*')
    {
        if (!empty(static::$secondaryObjectClass)) {
            $class = static::$secondaryObjectClass;
        } else {
            $class = static::$objectClass;
        }
        return $class::get($id, $params);
    }
    
    /**
     * Get relations 
     *
     * @param string $relClass
     * @param int $id
     * @return array 
     */
    public static function getRelations($relClass, $id)
    {
        $curObj = static::$objectClass;
        if ($relClass::$firstObject == $curObj) {
            $tmp = $relClass::$secondObject;
            $fArr = array();
            $sArr = array($tmp::getPrimaryKey(), $tmp::getUniqueLabelField());
        } else {
            $tmp = $relClass::$firstObject;
            $fArr = array($tmp::getPrimaryKey(), $tmp::getUniqueLabelField());
            $sArr = array();
        }
        $cmp = $curObj::getTableName() . '.' . $curObj::getPrimaryKey();
        $list = $relClass::getMergedParameters(
            $fArr,
            $sArr,
            -1,
            0,
            null,
            "ASC",
            array($cmp => $id),
            "AND"
        );
        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$tmp::getPrimaryKey()],
                "text" => $obj[$tmp::getUniqueLabelField()]
            );
        }
        return $finalList;
    }

    /**
     * Get simple relation (1-N)
     *
     * @param string $fieldName
     * @param string $targetObj
     * @param int $id
     * @param bool $reverse
     */
    public static function getSimpleRelation($fieldName, $targetObj, $id, $reverse = false)
    {
        if ($reverse === false) {
            $obj = static::$objectClass;
            $pk = $obj::getPrimaryKey();
            $fields = $fieldName;
        } else {
            $obj = $targetObj;
            $pk = $fieldName;
            $fields = $targetObj::getPrimaryKey().','.$targetObj::getUniqueLabelField();
        }
        $filters = array(
            $obj::getTableName().'.'.$pk => $id
        );
        $list = $obj::getList($fields, -1, 0, null, "ASC", $filters, "AND");

        if (count($list) == 0) {
            return array('id' => null, 'text' => null);
        } elseif ($reverse === true) {
            $finalList = array();
            foreach ($list as $obj) {
                $finalList[] = array(
                    "id" => $obj[$targetObj::getPrimaryKey()],
                    "text" => $obj[$targetObj::getUniqueLabelField()]
                );
            }
            return $finalList;
        }

        $filters = array($targetObj::getPrimaryKey() => $list[0][$fieldName]);
        $targetPrimaryKey = $targetObj::getPrimaryKey();
        $targetName = $targetObj::getUniqueLabelField();
        $targetList = $targetObj::getList(
            $targetPrimaryKey.','.$targetName,
            -1,
            0,
            null,
            "ASC",
            $filters,
            "AND"
        );

        $finalList = array();
        if (count($targetList) > 0) {
            $finalList["id"] = $targetList[0][$targetPrimaryKey];
            $finalList["text"] = $targetList[0][$targetName];
        }
        return $finalList;
    }
}

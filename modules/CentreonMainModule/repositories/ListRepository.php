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
        //var_dump(get_called_class());
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

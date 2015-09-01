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

namespace CentreonCustomview\Repository;

use Centreon\Internal\Di;
use Centreon\Internal\Datatable;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Repository
 * @todo refactor, this class will extend the Core repository dedicated to datatable structures
 */
abstract class Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = '';
    
    /**
     *
     * @var string
     */
    public static $objectName = '';
    
    /**
     *
     * @var string
     */
    public static $moduleName = 'CentreonCustomview';
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array();
    
    /**
     *
     * @var array 
     */
    public static $additionalColumn = array();
    
    /**
     *
     * @var array This array should math the additional column variable
     */
    public static $researchIndex = array();
    
    /**
     *
     * @var string 
     */
    public static $specificConditions = '';
    
    /**
     *
     * @var string Acl string
     */
    public static $aclConditions = '';
    
    /**
     *
     * @var string 
     */
    public static $linkedTables = '';
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array();
    
    /**
     *
     * @var array 
     */
    public static $columnCast = array();
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array();

    /**
     * @var bool If this object has category
     */
    public static $hasCategory = false;

    /**
     * @var string The name of group, if the object does not have group it's a empty string
     */
    public static $groupname = '';
    
    /**
     * 
     * @return array
     */
    public static function getParametersForDatatable()
    {
        return array(
            'column' => static::$datatableColumn,
            'header' => static::$datatableHeader,
            'footer' => static::$datatableFooter,
            'hasCategory' => static::$hasCategory,
            'groupname' => _(static::$groupname)
        );
    }
    
    /**
     * 
     * @param array $params
     * @return array
     */
    public static function getDatasForDatatable($params)
    {
        // Init vars
        $additionalTables = '';
        $conditions = '';
        $limitations = '';
        $sort = '';
        
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        // Getting selected field(s)
        $field_list = '';
        foreach (static::$datatableColumn as $field) {
            if (!is_array($field) && (substr($field, 0, 11) !== '[SPECFIELD]')) {
                $field_list .= $field.',';
            }
        }
        
        foreach (static::$additionalColumn as $field) {
            $field_list .= $field.',';
        }
        
        $field_list = trim($field_list, ',');

        
        // Getting table column
        $c = array_values(static::$researchIndex);
        
        if (!empty(static::$specificConditions)) {
            $conditions = "WHERE ".static::$specificConditions;
        }
        
        if (!empty(static::$aclConditions)) {
            if (empty($conditions)) {
                $conditions = "WHERE ".static::$aclConditions;
            } else {
                $conditions = "AND ".static::$aclConditions;
            }
        }
        
        if (!empty(static::$linkedTables)) {
            $additionalTables = ', '.static::$linkedTables;
        }
        
        // Conditions (Recherche)
        foreach ($params as $paramName => $paramValue) {
            if (strpos($paramName, 'sSearch_') !== false) {
                if (!empty($paramValue) || $paramValue === "0") {
                    $colNumber = substr($paramName, strlen('sSearch_'));
                    if (substr($c[$colNumber], 0, 11) !== '[SPECFIELD]') {
                        $searchString = $c[$colNumber]." like '%" . $dbconn->quote($paramValue) . "%' ";
                    } else {
                        $customSearchString = substr($c[$colNumber], 11);
                        $searchString = str_replace('::search_value::', '%' . $dbconn->quote($paramValue) . '%', $customSearchString);
                    }
                    
                    if (empty($conditions)) {
                        $conditions = "WHERE ".$searchString;
                    } else {
                        $conditions .= "AND ".$searchString;
                    }
                }
            }
        }
        
        // Sort
        if ((substr($sort, 0, 11) !== '[SPECFIELD]')) {
            $sort = 'ORDER BY '.$c[$params['iSortCol_0']].' '.$params['sSortDir_0'];
        }
        
        // Processing the limit
        if ($params['iDisplayLength'] > 0) {
            $limitations = 'LIMIT '.$params['iDisplayStart'].','.$params['iDisplayLength'];
        }
        
        // Building the final request
        $finalRequest = "SELECT "
            . "SQL_CALC_FOUND_ROWS $field_list "
            . "FROM ".static::$tableName."$additionalTables $conditions "
            . "$sort $limitations";
        
        $stmt = $dbconn->query($finalRequest);
        
        // Returning the result
        $resultSet = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $countTab = count($resultSet);
        $objectTab = array();
        for ($i=0; $i<$countTab; $i++) {
            $objectTab[] = array(
                static::$objectName,
                static::$moduleName
            );
        }
        
        static::formatDatas($resultSet);
        
        return self::arrayValuesRecursive(
            \array_values(
                Datatable::removeUnwantedFields(
                    static::$moduleName,
                    static::$objectName,
                    \array_map(
                        "\\Centreon\\Internal\\Datatable::castResult",
                        $resultSet,
                        $objectTab
                    )
                )
            )
        );
    }
    
    /**
     * Format datas before return to the calling script/function/object
     * @param array $resultSet
     */
    public static function formatDatas(&$resultSet)
    {
        
    }
    
    /**
     * 
     * @param array $params
     * @return array
     */
    public static function getCustomDatas($params)
    {
        
    }
    
    /**
     * 
     * @param array $params
     * @return integer
     */
    public static function getTotalRecordsForDatatable($params)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $conditions = '';
        $additionalTables = '';
        
        // Getting table column
        $c = array_values(static::$researchIndex);
        
        if (!empty(static::$specificConditions)) {
            $conditions = "WHERE ".static::$specificConditions;
        }
        
        if (!empty(static::$aclConditions)) {
            if (empty($conditions)) {
                $conditions = "WHERE ".static::$aclConditions;
            } else {
                $conditions = "AND ".static::$aclConditions;
            }
        }
        
        if (!empty(static::$linkedTables)) {
            $additionalTables = ', '.static::$linkedTables;
        }
        
        // Conditions (Recherche)
        foreach ($params as $paramName => $paramValue) {
            if (strpos($paramName, 'sSearch_') !== false) {
                if (!empty($paramValue) || $paramValue === "0") {
                    $colNumber = substr($paramName, strlen('sSearch_'));
                    
                    if (substr($c[$colNumber], 0, 11) === '[SPECFIELD]') {
                        $research = str_replace('::search_value::', '%' . $dbconn->quote($paramValue) . '%', substr($c[$colNumber], 11));
                    } else {
                        $research = $c[$colNumber]." like '%" . $dbconn->quote($paramValue) ."%' ";
                    }
                    
                    if (empty($conditions)) {
                        $conditions = "WHERE ".$research;
                    } else {
                        $conditions .= "AND ".$research;
                    }
                }
            }
        }
        
        // Building the final request
        $request = "SELECT COUNT('id') as nb".ucwords(static::$tableName).
            " FROM ".static::$tableName."$additionalTables $conditions";
        
        // Executing the request
        $stmt = $dbconn->query($request);
        
        // Getting the result
        $result = $stmt->fetchAll();
        
        // Returing the result
        return $result[0]['nb'.ucwords(static::$tableName)];
    }
    
    /**
     * 
     * @param type $array
     * @return type
     */
    public static function arrayValuesRecursive($array)
    {
        $array = array_values($array);
        for ($i = 0, $n = count($array); $i < $n; $i++) {
            $element = $array[$i];
            if (is_array($element)) {
                $array[$i] = self::arrayValuesRecursive($element);
            }
        }
        return $array;
    }
    
    /**
     * 
     * @param array $params
     */
    public static function getTotalRecords($params)
    {
        
    }
}

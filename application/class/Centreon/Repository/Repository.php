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

namespace Centreon\Repository;

use Models\Configuration\Host;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
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
    public static function  getParametersForDatatable()
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
        $di = \Centreon\Core\Di::getDefault();
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
        foreach ($params as $paramName=>$paramValue) {
            if (strpos($paramName, 'sSearch_') !== false) {
                if (!empty($paramValue) || $paramValue === "0") {
                    $colNumber = substr($paramName, strlen('sSearch_'));
                    if (substr($c[$colNumber], 0, 11) !== '[SPECFIELD]') {
                        $searchString = $c[$colNumber]." like '%".$paramValue."%' ";
                    } else {
                        $customSearchString = substr($c[$colNumber], 11);
                        $searchString = str_replace('::search_value::', '%'.$paramValue.'%', $customSearchString);
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
        $finalRequest = "SELECT SQL_CALC_FOUND_ROWS $field_list FROM ".static::$tableName."$additionalTables $conditions "
            . "$sort $limitations";
        
        $stmt = $dbconn->query($finalRequest);
        
        // Returning the result
        $resultSet = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $countTab = count($resultSet);
        $objectTab = array();
        for($i=0; $i<$countTab; $i++) {
            $objectTab[] = static::$objectName;
        }
        
        static::formatDatas($resultSet);
        
        return self::array_values_recursive(
            \array_values(
                \Centreon\Core\Datatable::removeUnwantedFields(
                    static::$objectName,
                    \array_map(
                        "\\Centreon\\Core\\Datatable::castResult",
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
        $di = \Centreon\Core\Di::getDefault();
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
        foreach ($params as $paramName=>$paramValue) {
            if (strpos($paramName, 'sSearch_') !== false) {
                if (!empty($paramValue) || $paramValue === "0") {
                    $colNumber = substr($paramName, strlen('sSearch_'));
                    
                    if (substr($c[$colNumber], 0, 11) === '[SPECFIELD]') {
                        $research = str_replace('::search_value::', '%'.$paramValue.'%', substr($c[$colNumber], 11));
                    } else {
                        $research = $c[$colNumber]." like '%".$paramValue."%' ";
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
     * @param array $params
     * @return array
     */
    public static function newGetTotalRecordsForDatatable($params)
    {
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $request = "SELECT FOUND_ROWS()";
        // Executing the request
        $stmt = $dbconn->query($request);
        
        // Getting the result
        $result = $stmt->fetchAll();
        
        // Returing the result
        return $result[0][0];
    }
    
    /**
     * 
     * @param type $element
     * @return type
     */
    public static function castColumn($element)
    {
        $elementField = array_keys($element);
        $originalElement = $element;
        foreach (static::$columnCast as $castField=>$castValues) {
            if (is_array($castValues)) {
                if (\in_array($castField, $elementField)) {
                    $element[$castField] = $castValues[$element[$castField]];
                }
            } else {
                $castedElement = \array_map(function($n) {return "::$n::";}, $elementField);
                $element[$castField] = str_replace($castedElement, $originalElement, $castValues);
            }
        }
        return $element;
    }
    
    /**
     * 
     * @param type $array
     * @return type
     */
    public static function array_values_recursive($array)
    {
        $array = array_values( $array );
        for ( $i = 0, $n = count( $array ); $i < $n; $i++ ) {
            $element = $array[$i];
            if ( is_array( $element ) ) {
                $array[$i] = self::array_values_recursive( $element );
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

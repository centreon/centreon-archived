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

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class ServicecategoryRepository extends \Centreon\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'service_categories';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Servicecategory';
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        '<input id="allServicecategory" class="allServicecategory" type="checkbox">' => 'sc_id',
        'Name' => 'sc_name',
        'Alias' => 'sc_description',
        'Linked services' => '[NBOBJECT]',
        'Status' => 'sc_activate'
    );
    
    /**
     *
     * @var array 
     */
    public static $researchIndex = array(
        'sc_id',
        'sc_name',
        'sc_description',
        '[NBOBJECT]',
        'sc_activate'
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'search_name',
        'search_description',
        'none',
        array('select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $columnCast = array(
        'sc_activate' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>',
            )
        ),
        'sc_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::sc_name::'
            )
        ),
        'sc_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/servicecategory/[i:id]/[i:advanced]',
                'routeParams' => array(
                    'id' => '::sc_id::',
                    'advanced' => '0'
                ),
                'linkName' => '::sc_description::'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array(
        'none',
        'search_name',
        'search_description',
        'none',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        )
    );
    
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
            if ($field !== '[NBOBJECT]') {
                $field_list .= $field.',';
            }
        }
        $field_list = trim($field_list, ',');

        
        // Getting table column
        $c = array_values(static::$datatableColumn);
        
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
                    if (empty($conditions)) {
                        $conditions = "WHERE ".$c[$colNumber]." like '%".$paramValue."%' ";
                    } else {
                        $conditions .= "AND ".$c[$colNumber]." like '%".$paramValue."%' ";
                    }
                }
            }
        }
        
        // Sort
        $sort = 'ORDER BY '.$c[$params['iSortCol_0']].' '.$params['sSortDir_0'];
        
        // Processing the limit
        $limitations = 'LIMIT '.$params['iDisplayStart'].','.$params['iDisplayLength'];
        
        // Building the final request
        $finalRequest = "SELECT "
            . "SQL_CALC_FOUND_ROWS $field_list "
            . "FROM ".static::$tableName."$additionalTables $conditions "
            . "$sort $limitations";
        
        try {
            // Executing the request
            $stmt = $dbconn->query($finalRequest);
        } catch (Exception $e) {
            
        }
        
        // Returning the result
        $resultSet = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $countTab = count($resultSet);
        $objectTab = array();
        for ($i=0; $i<$countTab; $i++) {
            $objectTab[] = static::$objectName;
        }
        
        foreach ($resultSet as &$mySC) {
            $stmt = $dbconn->query(
                "SELECT COUNT(*) FROM `service_categories_relation` WHERE `sc_id` = '".$mySC['sc_id']."'"
            );
            $nb_svc = $stmt->fetch();
            $save = array_pop($mySC);
            $mySC['sc_linked_svc'] = $nb_svc[0];
            $mySC['sc_activate'] = $save;
        }
        
        return self::arrayValuesRecursive(
            \array_values(
                \array_map(
                    "\\Centreon\\Core\\Datatable::castResult",
                    $resultSet,
                    $objectTab
                )
            )
        );
    }
}

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

namespace CentreonCustomview\Repository;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package CentreonCustomview
 * @subpackage Repository
 */
class WidgetmodelRepository extends \CentreonCustomview\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'widget_models';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'WidgetModel';
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        '<input id="allWidgets" class="allWidgets" type="checkbox">' => 'widget_model_id',
        'Name' => 'name',
        'Description' => 'description',
        'Version' => 'version',
        'Author' => 'author',
        'Status' => 'isactivated',
        'Install status' => 'isinstalled'
    );
    
    /**
     *
     * @var array 
     */
    public static $researchIndex = array(
        'widget_model_id',
        'name',
        'description',
        'version',
        'author',
        'isactivated',
        'isinstalled'
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'search_name',
        'search_description',
        'search_version',
        'search_author',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        ),
        array(
            'select' => array(
                'Installed' => '1',
                'Not installed' => '0'
            )
        ),
    );
    
    /**
     *
     * @var array 
     */
    public static $columnCast = array(
        'isactivated' => array(
            'type' => 'select',
            'parameters' =>array(
                'selecttype' => 'url',
                'parameters' => array(
                    '0' => array(
                        'parameters' => array(
                            'route' => '/administration/extensions/widgets/[i:id]/enable',
                            'routeParams' => array(
                                'id' => '::widget_model_id::'
                            ),
                            'linkName' => 'Disabled',
                            'styleClass' => 'btn btn-danger btn-block'
                        )
                    ),
                    '1' => array(
                        'parameters' => array(
                            'route' => '/administration/extensions/widgets/[i:id]/disable',
                            'routeParams' => array(
                                'id' => '::widget_model_id::'
                            ),
                            'linkName' => 'Enabled',
                            'styleClass' => 'btn btn-success btn-block'
                        )
                    ),
                )
            )
        ),
        'isinstalled' => array(
            'type' => 'select',
            'parameters' => array(
                'selecttype' => 'url',
                'parameters' => array(
                    '0' => array(
                        'parameters' => array(
                            'route' => '/administration/extensions/widgets/[*:shortname]/install',
                            'routeParams' => array(
                                'shortname' => '::shortname::'
                            ),
                            'linkName' => 'Uninstalled',
                            'styleClass' => 'btn btn-danger btn-block'
                        )
                    ),
                    '1' => array(
                        'parameters' => array(
                            'route' => '/administration/extensions/widgets/[i:id]/uninstall',
                            'routeParams' => array(
                                'id' => '::widget_model_id::'
                            ),
                            'linkName' => 'Installed',
                            'styleClass' => 'btn btn-success btn-block'
                        )
                    ),
                )
            )
        ),
        'widget_model_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::name::'
            )
        ),
        'name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/administration/extensions/widgets/[i:id]',
                'routeParams' => array(
                    'id' => '::widget_model_id::'
                ),
                'linkName' => '::name::'
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
        $di = \Centreon\Internal\Di::getDefault();
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
        $finalRequest = "SELECT "
            . "SQL_CALC_FOUND_ROWS $field_list "
            . "FROM ".static::$tableName."$additionalTables $conditions "
            . "$sort $limitations";
        
        $stmt = $dbconn->query($finalRequest);
        
        // Returning the result
        $resultSet = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get current moduleName
        $widgetNameList = array();
        foreach($resultSet as $cWidget) {
            $widgetNameList[] = $cWidget['shortname'];
        }
        
        // Add file system repo
        $rawWidgetList = glob(__DIR__."/../../../widgets/*/");
        foreach ($rawWidgetList as $widget) {
            if ($widget == "." || $widget == "..") {
                continue;
            }
            if (file_exists(realpath($widget . '/install/config.json'))) {
                $info = json_decode(file_get_contents($widget . '/install/config.json'), true);
                if (!in_array($info['shortname'], $widgetNameList)) {
                    $resultSet[] = array(
                        'widget_model_id' => 0,
                        'name' => $info['name'],
                        'description' => $info['description'],
                        'version' => $info['version'],
                        'author' => $info['author'],
                        'isactivated' => 0,
                        'isinstalled' => 0
                    );
                }
            }
        }
        
        /*var_dump($resultSet);
        var_dump(array_unique($resultSet));*/
        
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
                \Centreon\Internal\Datatable::removeUnwantedFields(
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
}

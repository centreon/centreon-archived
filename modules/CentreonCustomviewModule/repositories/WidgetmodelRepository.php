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
use CentreonCustomview\Repository\Repository;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package CentreonCustomview
 * @subpackage Repository
 */
class WidgetmodelRepository extends Repository
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
        'Shortname' => 'shortname',
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
        'shortname',
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
        'search_shortname',
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
                            'route' => '/centreon-administration/extensions/widgets/[i:id]/enable',
                            'routeParams' => array(
                                'id' => '::widget_model_id::'
                            ),
                            'linkName' => 'Disabled',
                            'styleClass' => 'btn btn-danger btn-block'
                        )
                    ),
                    '1' => array(
                        'parameters' => array(
                            'route' => '/centreon-administration/extensions/widgets/[i:id]/disable',
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
                            'route' => '/centreon-administration/extensions/widgets/[*:shortname]/install',
                            'routeParams' => array(
                                'shortname' => '::shortname::'
                            ),
                            'linkName' => 'Uninstalled',
                            'styleClass' => 'btn btn-danger btn-block'
                        )
                    ),
                    '1' => array(
                        'parameters' => array(
                            'route' => '/centreon-administration/extensions/widgets/[i:id]/uninstall',
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
                'route' => '/centreon-administration/extensions/widgets/[i:id]',
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
        
        // Get current moduleName
        $widgetNameList = array();
        foreach($resultSet as $cWidget) {
            $widgetNameList[] = $cWidget['shortname'];
        }

        $path = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
        // Add file system repo
        $possibleWidgetDir = array(
            $path . "/widgets/*Widget/",
            $path . "/modules/*Module/widgets/*Widget/"
        );
        foreach ($possibleWidgetDir as $d) {
            $rawWidgetList = glob($d);
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
                            'shortname' => $info['shortname'],
                            'description' => $info['description'],
                            'version' => $info['version'],
                            'author' => $info['author'],
                            'isactivated' => 0,
                            'isinstalled' => 0
                        );
                    }
                }
            }
        }
        
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
}

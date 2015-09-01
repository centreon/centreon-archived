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

namespace CentreonCustomview\Internal;

use Centreon\Internal\Di;
use Centreon\Internal\Module\Informations;
use Centreon\Internal\Datatable;

/**
 * Description of WidgetDatatable
 *
 * @author lionel
 */
class WidgetDatatable extends Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonCustomview\Models\WidgetModel';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'widget_model_id', 'name' => 'name');
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('name', 'asc')
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    /**
     *
     * @var array 
     */
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'widget_model_id',
            'data' => 'widget_model_id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false
        ),
        array (
            'title' => 'Name',
            'name' => 'name',
            'data' => 'name',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-customview/extensions/widgets/[i:id]',
                    'routeParams' => array(
                        'id' => '::widget_model_id::'
                    ),
                    'linkName' => '::name::'
                )
            )
        ),
        array (
            'title' => 'Shortname',
            'name' => 'shortname',
            'data' => 'shortname',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Description',
            'name' => 'description',
            'data' => 'description',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Version',
            'name' => 'version',
            'data' => 'version',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Author',
            'name' => 'author',
            'data' => 'author',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Install Status',
            'name' => 'isinstalled',
            'data' => 'isinstalled',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'className' => 'cell_center',
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    'selecttype' => 'template',
                    'parameters' => array(
                        '0' => array(
                            'parameters' => array(
                                'tmpl' => '<span class="label label-default">Not installed</span>'
                            )
                        ),
                        '1' => array(
                            'parameters' => array(
                                'tmpl' => '<span class="label label-primary">Installed</span>'
                            )
                        ),
                        '2' => array(
                            'parameters' => array(
                                'tmpl' => '<span class="label label-primary">Core</span>'
                            )
                        ),
                    )
                )
            )
        ),
        array (
            'title' => 'Status',
            'name' => 'isactivated',
            'data' => 'isactivated',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'className' => 'cell_center',
            'cast' => array(
                'type' => 'checkbox',
                'parameters' => array(
                    'styleClass' => 'enabled',
                    'data' => array(
                        'urlEnabled' => array(
                            'type' => 'url',
                            'route' => '/centreon-customview/extensions/widgets/[i:id]/enable',
                            'routeParams' => array(
                                'id' => '::widget_model_id::'
                            )
                        ),
                        'urlDisabled' => array(
                            'type' => 'url',
                            'route' => '/centreon-customview/extensions/widgets/[i:id]/disable',
                            'routeParams' => array(
                                'id' => '::widget_model_id::'
                            )
                        )
                    )
                )
            )
        ),
        array(
            'title' => 'Action',
            'name' => 'action',
            'data' => 'action',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'className' => 'cell_center',
            'source' => 'other',
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    'selecttype' => 'template',
                    'parameters' => array(
                        '0' => array(
                            'parameters' => array(
                                'tmpl' => '<a class="btn btn-sm btn-primary" href="::link::">Install</a>',
                                'route' => '/centreon-customview/extensions/widgets/[*:shortname]/install',
                                'routeParams' => array(
                                    'shortname' => '::shortname::'
                                )
                            )
                        ),
                        '1' => array(
                            'parameters' => array(
                                'tmpl' => '<a class="btn btn-sm btn-danger" href="::link::">Uninstall</a>',
                                'route' => '/centreon-customview/extensions/widgets/[i:id]/uninstall',
                                'routeParams' => array(
                                    'id' => '::widget_model_id::'
                                )
                            )
                        ),
                        '2' => array(
                            'parameters' => array(
                                'tmpl' => ''
                            )
                        ),
                    )
                )
            )
        )
    );
    
    /**
     * 
     * @param array $params
     */
    public function __construct($params, $objectModelClass = '')
    {
        parent::__construct($params, $objectModelClass);
    }
    
    /**
     * 
     * @param type $resultSet
     */
    protected static function addAdditionnalDatas(&$resultSet)
    {
        static::getFilesystemWidget($resultSet);
    }

    protected function formatDatas(&$resultSet) 
    {
        foreach ($resultSet as &$result) {
            $result['action'] = $result['isinstalled'];
        }
    }
    
    private static function getFilesystemWidget(& $resultSet)
    {
       // Get current moduleName
        $widgetNameList = array();
        foreach($resultSet as $cWidget) {
            $widgetNameList[] = $cWidget['shortname'];
        }

        $path = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
        $modules = Informations::getModuleList(true);
        // Add file system repo
        $possibleWidgetDir = array(
            $path . "/widgets/*Widget/"
        );
        foreach ($modules as $module) {
            $directoryModule = str_replace(' ', '', ucwords(str_replace('-', ' ', $module))) . "Module";
            $possibleWidgetDir[] = $path . "/modules/" . $directoryModule . "/widgets/*Widget/";
        }
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
    }

}

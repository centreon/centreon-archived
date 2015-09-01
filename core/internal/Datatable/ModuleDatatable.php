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
 */

namespace Centreon\Internal\Datatable;

use Centreon\Internal\Module\Informations;
use Centreon\Internal\Di;
use Centreon\Internal\Datatable;

/**
 * Description of ModuleDatatable
 *
 * @author lionel
 */
class ModuleDatatable extends Datatable
{
    /**
     *
     * @var string 
     */
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var string 
     */
    protected static $datasource = '\Centreon\Models\Module';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'id', 'name' => 'name');
    
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
            'name' => 'id',
            'data' => 'id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false, 
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
                    'route' => '/centreon-administration/extensions/module/[i:id]',
                    'routeParams' => array(
                        'id' => '::id::'
                    ),
                    'linkName' => '::name::'
                )
            )
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
                            'route' => '/centreon-administration/extensions/module/[i:id]/enable',
                            'routeParams' => array(
                                'id' => '::id::'
                            )
                        ),
                        'urlDisabled' => array(
                            'type' => 'url',
                            'route' => '/centreon-administration/extensions/module/[i:id]/disable',
                            'routeParams' => array(
                                'id' => '::id::'
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
                                'route' => '/centreon-administration/extensions/module/[*:shortname]/install',
                                'routeParams' => array(
                                    'shortname' => '::name::'
                                )
                            )
                        ),
                        '1' => array(
                            'parameters' => array(
                                'tmpl' => '<a class="btn btn-sm btn-danger" href="::link::">Uninstall</a>',
                                'route' => '/centreon-administration/extensions/module/[i:id]/uninstall',
                                'routeParams' => array(
                                    'id' => '::id::'
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
        self::getFilesystemModule($resultSet);
    }

    /**
     * 
     * @param type $resultSet
     */
    protected function formatDatas(&$resultSet) 
    {
        foreach ($resultSet as &$result) {
            $result['action'] = $result['isinstalled'];
        }
    }
    
    /**
     * 
     * @param type $resultSet
     */
    private static function getFilesystemModule(& $resultSet)
    {
        // Get current moduleName
        $moduleNameList = Informations::getModuleList();
        $path = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
        $rawModuleList = glob($path."/modules/*Module/");
        foreach ($rawModuleList as $module) {
            if (file_exists(realpath($module . 'install/config.json'))) {
                $b = json_decode(file_get_contents($module . 'install/config.json'), true);
                if (!in_array($b['shortname'], $moduleNameList)) {
                    $resultSet[] = array(
                        'id' => 0,
                        'name' => $b['shortname'],
                        'description' => $b['name'],
                        'version' => $b['version'],
                        'author' => implode(", ", $b['author']),
                        'isactivated' => 0,
                        'isinstalled' => 0,
                        'action' => 0,
                        'alias' => $b['name'],
                    );
                }
            }
        }
    }
}

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

namespace Centreon\Internal\Datatable;

/**
 * Description of ModuleDatatable
 *
 * @author lionel
 */
class ModuleDatatable extends \Centreon\Internal\Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\Centreon\Models\Module';
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('name', 'asc'),
            array('id', 'asc')
        ),
        'stateSave' => true,
        'paging' => true,
    );
    
    /**
     *
     * @var array 
     */
    public static $columns = array(
        array (
            'title' => "<input id='allModuleid' class='allModuleid' type='checkbox'>",
            'name' => 'id',
            'data' => 'id',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'checkbox',
                'parameters' => array(
                    'displayName' => '::name::'
                )
            )
        ),
        array (
            'title' => 'Name',
            'name' => 'name',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/administration/extensions/module/[i:id]',
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
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Version',
            'name' => 'version',
            'data' => 'version',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Author',
            'name' => 'author',
            'data' => 'author',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Status',
            'name' => 'isactivated',
            'data' => 'isactivated',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    'selecttype' => 'url',
                    'parameters' => array(
                        '0' => array(
                            'parameters' => array(
                                'route' => '/administration/extensions/module/[i:id]/enable',
                                'routeParams' => array(
                                    'id' => '::id::'
                                ),
                                'linkName' => 'Disabled',
                                'styleClass' => 'btn btn-danger btn-block'
                            )
                        ),
                        '1' => array(
                            'parameters' => array(
                                'route' => '/administration/extensions/module/[i:id]/disable',
                                'routeParams' => array(
                                    'id' => '::id::'
                                ),
                                'linkName' => 'Enabled',
                                'styleClass' => 'btn btn-success btn-block'
                            )
                        ),
                        '2' => array(
                            'parameters' => array(
                                'route' => '/administration/extensions/module/[i:id]',
                                'routeParams' => array(
                                    'id' => '::id::'
                                ),
                                'linkName' => 'Not Disableable',
                                'styleClass' => 'btn btn-primary btn-block'
                            )
                        ),
                    )
                )
            )
        ),
        array (
            'title' => 'Install Status',
            'name' => 'isinstalled',
            'data' => 'isinstalled',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    'selecttype' => 'url',
                    'parameters' => array(
                        '0' => array(
                            'parameters' => array(
                                'route' => '/administration/extensions/module/[*:shortname]/install',
                                'routeParams' => array(
                                    'shortname' => '::name::'
                                ),
                                'linkName' => 'Uninstalled',
                                'styleClass' => 'btn btn-danger btn-block'
                            )
                        ),
                        '1' => array(
                            'parameters' => array(
                                'route' => '/administration/extensions/module/[i:id]/uninstall',
                                'routeParams' => array(
                                    'id' => '::id::'
                                ),
                                'linkName' => 'Installed',
                                'styleClass' => 'btn btn-success btn-block'
                            )
                        ),
                        '2' => array(
                            'parameters' => array(
                                'route' => '/administration/extensions/module/[i:id]',
                                'routeParams' => array(
                                    'id' => '::id::'
                                ),
                                'linkName' => 'Core Module',
                                'styleClass' => 'btn btn-primary btn-block'
                            )
                        ),
                    )
                )
            )
        ),
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
    
    private static function getFilesystemModule(& $resultSet)
    {
        // Get current moduleName
        $moduleNameList = \Centreon\Custom\Module\ModuleInformations::getModuleList();
        $path = rtrim(\Centreon\Internal\Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
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
                        'alias' => $b['name'],
                    );
                }
            }
        }
    }

}

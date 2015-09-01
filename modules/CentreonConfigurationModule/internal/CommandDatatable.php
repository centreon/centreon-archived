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

namespace CentreonConfiguration\Internal;

use Centreon\Internal\Datatable\Datasource\CentreonDb;
use Centreon\Internal\Datatable;
use CentreonConfiguration\Repository\CommandRepository;

/**
 * Description of CommandDatatable
 *
 * @author lionel
 */
class CommandDatatable extends Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\Command';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'command_id', 'name' => 'command_name');
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('command_name', 'asc'),
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
            'name' => 'command_id',
            'data' => 'command_id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'width' => '5%',
            "className" => 'cell_center',
            "width" => "20px"
        ),
        array (
            'title' => 'Name',
            'name' => 'command_name',
            'data' => 'command_name',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'command',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-configuration/command/[i:id]',
                    'routeParams' => array(
                        'id' => '::command_id::'
                    ),
                    'linkName' => '::command_name::'
                )
            )
        ),
        array (
            'title' => 'Command Line',
            'name' => 'command_line',
            'data' => 'command_line',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Type',
            'name' => 'command_type',
            'data' => 'command_type',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '1' => '<span class="label label-info">Notifications</span>',
                    '2' => '<span class="label label-info">Check</span>',
                    '3' => '<span class="label label-info">Miscelleanous</span>',
                    '4' => '<span class="label label-info">Discovery</span>',
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'multiple' => "true",
                'additionnalParams' => array(
                    'Notifications' => '1',
                    'Check' => '2',
                    'Miscelleanous' => '3',
                    'Discovery' => '4',
                )
            ),
            "className" => 'cell_center',
            'width' => "40px"
            
        ),
        array (
            'title' => 'Host use',
            'name' => 'NULL AS host_use',
            'data' => 'host_use',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            "className" => 'cell_center',
            'width' => "50px"
        ),
        array (
            'title' => 'Service use',
            'name' => 'NULL AS svc_use',
            'data' => 'svc_use',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            "className" => 'cell_center',
            'width' => "50px"
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
     * @param array $resultSet
     */
    public function formatDatas(&$resultSet)
    {
        foreach ($resultSet as $key => &$myCmdSet) {
            $myCmdSet['command_line'] = sprintf('%.70s', $myCmdSet['command_line'])."...";
            $myCmdSet['host_use'] = CommandRepository::getUseNumber($myCmdSet["command_id"], "host");
            $myCmdSet['svc_use'] =  CommandRepository::getUseNumber($myCmdSet["command_id"], "service");
        }
        $resultSet = array_values($resultSet);
    }
}

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
 */

namespace CentreonBam\Internal;

use Centreon\Internal\Datatable\Datasource\CentreonDb;
use Centreon\Internal\Datatable;
use Centreon\Internal\Utils\Datetime;
use CentreonBam\Repository\BusinessActivityRepository;
use CentreonAdministration\Repository\TagsRepository;

/**
 * Description of BaDatatable
 *
 * @author lionel
 */
class BusinessActivityRealtimeDatatable extends Datatable
{
    /**
     *
     * @var type 
     */
    protected static $objectId = 'ba_id';
    
    /**
     *
     * @var type 
     */
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonBam\Models\BusinessActivityRealtime';
   
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'ba_id', 'name' => 'name');
     /**
     *
     * @var array 
     */
    protected static  $aFieldNotAuthorized = array('tagname');
    
    /**addToTag
     *
     * @var array 
     */
    
    protected static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('name', 'asc')
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'ba_id',
            'data' => 'ba_id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'Business Activity',
            'name' => 'name',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-bam/businessactivity/realtime/[i:id]',
                    'routeParams' => array(
                        'id' => '::ba_id::'
                    ),
                    'linkName' => '::name::'
                )
            )
        ),
        array (
            'title' => 'Status',
            'name' => 'current_status',
            'data' => 'current_status',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-success">OK</span>',
                    '1' => '<span class="label label-warning">Warning</span>',
                    '2' => '<span class="label label-danger">Critical</span>',
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'multiple' => "true",
                'additionnalParams' => array(
                    'OK' => '0',
                    'Warning' => '1',
                    'Critical' => '2'
                )
            ),
            'width' => '50px',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Type',
            'name' => 'ba_type_id',
            'data' => 'ba_type_id',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => 70,
            'searchParam' => array(
                'type' => 'select',
                'multiple' => "true",
                'additionnalParams' => array(
                    'Business Unit' => '1',
                    'Application' => '2',
                    'Middleware' => '3'
                )
            ),
        ),
        array (
            'title' => 'Availability',
            'name' => 'current_level',
            'data' => 'current_level',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => 50,
        ),
        array (
            'title' => 'Duration',
            'name' => '(unix_timestamp(NOW())-last_state_change) AS duration',
            'data' => 'duration',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '10%',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Tags',
            'name' => 'tagname',
            'data' => 'tagname',
            'orderable' => false,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '40px',
            'tablename' => 'cfg_tags'
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
 
    protected static $extraParams = array(
        'addToHook' => array(
            'objectType' => 'ba'
        )
    );

    //protected static $hook = 'displayTagList';
    protected static $hookParams = array(
        'resourceType' => 'ba'
    );

    /**
     * 
     * @param array $resultSet
     */
    protected function formatDatas(&$resultSet)
    {
        $previousType = '';
        foreach ($resultSet as &$myBaSet) {
            // Set business activity type
            $baType = \CentreonBam\Models\BusinessActivityType::getParameters($myBaSet['ba_type_id'], array('name'));
            $myBaSet['ba_type_id'] = $baType['name'];
            if ($myBaSet['ba_type_id'] === $previousType) {
                $myBaSet['ba_type_id'] = '';
            } else {
                $previousType = $myBaSet['ba_type_id'];
            }

            // Set business activity availability
            $myBaSet['current_level'] = $myBaSet['current_level'] . '%';

            // Set business activity name with its icon
            $myBaSet['name'] = BusinessActivityRepository::getIconImage($myBaSet['name']) . $myBaSet['name'];

            // Set human readable duration
            $myBaSet['duration'] = Datetime::humanReadable(
                $myBaSet['duration'],
                Datetime::PRECISION_FORMAT,
                2
            );
            
            /* Tags */
            $myBaSet['tagname']  = "";
            $aTags = TagsRepository::getList('ba', $myBaSet['ba_id'], 2);
            foreach ($aTags as $oTags) {
                $myBaSet['tagname'] .= TagsRepository::getTag('ba', $myBaSet['ba_id'], $oTags['id'], $oTags['text'], $oTags['user_id'], $oTags['template_id']);
            }
            $myBaSet['tagname'] .= TagsRepository::getAddTag('ba', $myBaSet['ba_id']);
        }
    }
}

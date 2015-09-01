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
use CentreonBam\Repository\BusinessActivityRepository;
use CentreonAdministration\Repository\TagsRepository;
use Centreon\Internal\Di;
use CentreonMain\Events\SlideMenu;

/**
 * Description of BaDatatable
 *
 * @author lionel
 */
class BusinessActivityDatatable extends Datatable
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
    protected static $datasource = '\CentreonBam\Models\BusinessActivity';
   
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
    
    /**
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
                    'route' => '/centreon-bam/businessactivity/[i:id]',
                    'routeParams' => array(
                        'id' => '::ba_id::'
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
            'title' => 'Status',
            'name' => 'activate',
            'data' => 'activate',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-danger">Disabled</span>',
                    '1' => '<span class="label label-success">Enabled</span>',
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'Enabled' => '1',
                    'Disabled' => '0'
                )
            ),
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
            $sideMenuCustom = new SlideMenu($myBaSet['ba_id']);
            $events = Di::getDefault()->get('events');
            $events->emit('centreon-bam.slide.menu.business.activity', array($sideMenuCustom));

            $myBaSet['DT_RowData']['right_side_menu_list'] = $sideMenuCustom->getMenu();
            $myBaSet['DT_RowData']['right_side_default_menu'] = $sideMenuCustom->getDefaultMenu();

            // Set business activity type
            $baType = \CentreonBam\Models\BusinessActivityType::getParameters($myBaSet['ba_type_id'], array('name'));
            $myBaSet['ba_type_id'] = $baType['name'];
            if ($myBaSet['ba_type_id'] === $previousType) {
                $myBaSet['ba_type_id'] = '';
            } else {
                $previousType = $myBaSet['ba_type_id'];
            }

            // set business activity name
            $myBaSet['name'] = BusinessActivityRepository::getIconImage($myBaSet['name']) . $myBaSet['name'];
                      
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

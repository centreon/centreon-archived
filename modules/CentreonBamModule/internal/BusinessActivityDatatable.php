<?php

/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonBam\Internal;

use Centreon\Internal\Datatable\Datasource\CentreonDb;
use Centreon\Internal\Datatable;
use CentreonBam\Repository\BusinessActivityRepository;
use CentreonAdministration\Repository\TagsRepository;

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
                'additionnalParams' => array(
                    'Business Unit' => '1',
                    'Application' => '2',
                    'Middleware' => '3'
                )
            ),
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
                $myBaSet['tagname'] .= TagsRepository::getTag('ba', $myBaSet['ba_id'], $oTags['id'], $oTags['text'], $oTags['user_id']);
            }
            $myBaSet['tagname'] .= TagsRepository::getAddTag('ba', $myBaSet['ba_id']);
        }
    }
}

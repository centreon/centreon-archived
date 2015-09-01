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

namespace CentreonAdministration\Internal;


use Centreon\Internal\Datatable;

/**
 * Description of AuthDatatable
 *
 * @author bsauveton
 */
class AuthDatatable extends Datatable
{
    
    
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'ar_id', 'name' => 'ar_name','description'=>'ar_description','status' =>'ar_enable');
    
    
    /**
     *
     * @var type 
     */
    protected static $objectId = 'ar_id';
    
    /**
     *
     * @var type 
     */
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonAdministration\Models\AuthRessource';
    
    /**
     *
     * @var array 
     */
    public static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('ar_name', 'asc')
        ),
        'stateSave' => false,
        'paging' => true
    );
    
    
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'ar_id',
            'data' => 'ar_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'width' => '20px',
            'className' => "cell_center"
        ),
        array (
            'title' => "Name",
            'name' => 'ar_name',
            'data' => 'ar_name',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '20px',
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-administration/auth/[i:id]',
                    'routeParams' => array(
                        'id' => '::ar_id::'
                    ),
                    'linkName' => '::ar_name::'
                )
            ),
            'className' => "cell_center"
        ),
        array (
            'title' => "Description",
            'name' => 'ar_description',
            'data' => 'ar_description',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '20px',
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-administration/auth/[i:id]',
                    'routeParams' => array(
                        'id' => '::ar_id::'
                    ),
                    'linkName' => '::ar_description::'
                )
            ),
            'className' => "cell_center"
        ),
        array (
            'title' => "Status",
            'name' => 'ar_enable',
            'data' => 'ar_enable',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '20px',
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-administration/auth/[i:id]',
                    'routeParams' => array(
                        'id' => '::ar_id::'
                    ),
                    'linkName' => '::ar_enable::'
                )
            ),
            'className' => "cell_center"
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
     * @param array $resultSet
     */
    protected function formatDatas(&$resultSet)
    {
        foreach ($resultSet as &$myAuthSet) {
            if($myAuthSet['ar_enable'] == 0){
                $myAuthSet['ar_enable'] = 'Disabled';
            }else if($myAuthSet['ar_enable'] == 1){
                $myAuthSet['ar_enable'] = 'Enabled';
            }
        }
    }
    
    //put your code here
}

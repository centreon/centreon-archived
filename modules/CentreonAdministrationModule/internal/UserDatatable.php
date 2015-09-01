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

use Centreon\Internal\Datatable\Datasource\CentreonDb;
use Centreon\Internal\Utils\CentreonArray;
use Centreon\Internal\Datatable;
use CentreonConfiguration\Repository\UserRepository;

/**
 * Description of UserGroupDatatable
 *
 * @author lionel
 */
class UserDatatable extends Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonAdministration\Models\User';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'user_id', 'name' => 'login');
    
    /**
     *
     * @var array 
     */
    protected static $administration = array(
        'autowidth' => true,
        'order' => array(
            array('firstname', 'asc')
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
            'name' => 'user_id',
            'data' => 'user_id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'Alias / Login',
            'name' => 'login',
            'data' => 'login',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-administration/user/[i:id]',
                    'routeParams' => array(
                        'id' => '::user_id::'
                    ),
                    'linkName' => '::login::'
                )
            )
        ),
        array (
            'title' => 'Full Name',
            'name' => 'CONCAT(firstname," ",lastname) as fullname',
            'data' => 'fullname',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Admin',
            'name' => 'is_admin',
            'data' => 'is_admin',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-danger">No</span>',
                    '1' => '<span class="label label-success">Yes</span>'
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'No' => '0',
                    'Yes' => '1'
                )
            )
        ),
        array (
            'title' => 'Status',
            'name' => 'is_activated',
            'data' => 'is_activated',
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
     * @param array $resultSet
     */
    public static function addAdditionnalDatas(&$resultSet)
    {
    }

    /**
     * 
     * @param array $resultSet
     */
    public function formatDatas(&$resultSet)
    {
    }
}

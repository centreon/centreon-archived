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

/**
 * Manage list views for notification rule
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package CentreonConfiguration
 * @subpackage Datatable
 * @version 3.0.0
 */
class NotificationRuleDatatable extends Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';

    /**
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\NotificationRule';
    
    /**
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'rule_id', 'name' => 'name');

    /**
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
     * @var array 
     */
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'rule_id',
            'data' => 'rule_id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'Name',
            'name' => 'name',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'notif_rule',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-configuration/notification-rule/[i:id]',
                    'routeParams' => array(
                        'id' => '::rule_id::'
                    ),
                    'linkName' => '::name::'
                )
            )
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
            'data' => 'description',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true
        )
    );

    /**
     * Constructor
     */
    public function __construct($params, $objectModelClass = '')
    {
        parent::__construct($params, $objectModelClass);
    }
}

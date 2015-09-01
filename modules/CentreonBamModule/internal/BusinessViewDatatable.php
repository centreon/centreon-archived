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

/**
 * Description of BaDatatable
 *
 * @author lionel
 */
class BusinessViewDatatable extends Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonBam\Models\BusinessView';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'id_ba_group', 'name' => 'ba_group_name');
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('ba_group_name', 'asc')
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'id_ba_group',
            'data' => 'id_ba_group',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false
        ),
        array (
            'title' => 'Name',
            'name' => 'ba_group_name',
            'data' => 'ba_group_name',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-bam/businessview/[i:id]',
                    'routeParams' => array(
                        'id' => '::id_ba_group::'
                    ),
                    'linkName' => '::ba_group_name::'
                )
            )
        ),
        array (
            'title' => 'Description',
            'name' => 'ba_group_description',
            'data' => 'ba_group_description',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Status',
            'name' => 'visible',
            'data' => 'visible',
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
            )
        ),
    );
}

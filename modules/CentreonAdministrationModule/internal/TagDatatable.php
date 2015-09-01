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

/**
 * Description of LanguageDatatable
 *
 * @author lionel
 */
class TagDatatable extends \Centreon\Internal\Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonAdministration\Models\Tag';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'tag_id', 'name' => 'tagname');
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('tagname', 'asc')
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
            'title' => "Id",
            'name' => 'tag_id',
            'data' => 'tag_id',
            'orderable' => true,
            'searchable' => false,
            'searchLabel' => 'tag',
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'Name',
            'name' => 'tagname',
            'data' => 'tagname',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-administration/tag/[i:id]',
                    'routeParams' => array(
                        'id' => '::tag_id::'
                    ),
                    'linkName' => '::tagname::'
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
}

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
use Centreon\Internal\Di;
use CentreonAdministration\Repository\TagsRepository;

/**
 * Description of ContactDatatable
 *
 * @author lionel
 */
class ContactDatatable extends Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonAdministration\Models\Contact';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'contact_id', 'name' => 'description');
    
    /**
     *
     * @var array 
     */

    public static  $aFieldNotAuthorized = array('tagname');
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('description', 'asc')
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
            'name' => 'contact_id',
            'data' => 'contact_id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'Description',
            'name' => 'description',
            'data' => 'description',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'contact',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-administration/contact/[i:id]',
                    'routeParams' => array(
                        'id' => '::contact_id::'
                    ),
                    'linkName' => '::description::'
                )
            )
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
            'objectType' => 'contact'
        )
    );

    protected static $hookParams = array(
        'resourceType' => 'contact'
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
        $previousHost = '';
        $router = Di::getDefault()->get('router');
        foreach ($resultSet as &$myContactSet) {      
            /* Tags */
            $myContactSet['tagname']  = "";
            $aTags = TagsRepository::getList('contact', $myContactSet['contact_id'], 2);
            foreach ($aTags as $oTags) {
                $myContactSet['tagname'] .= TagsRepository::getTag('contact', $myContactSet['contact_id'], $oTags['id'], $oTags['text'], $oTags['user_id']);
            }
            $myContactSet['tagname'] .= TagsRepository::getAddTag('contact', $myContactSet['contact_id']);
        }
    }
}

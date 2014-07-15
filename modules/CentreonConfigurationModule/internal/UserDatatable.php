<?php

/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonConfiguration\Internal;

use \Centreon\Internal\Datatable\Datasource\CentreonDb;

/**
 * Description of UserGroupDatatable
 *
 * @author lionel
 */
class UserDatatable extends \Centreon\Internal\Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\Contact';
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('contact_name', 'asc'),
            array('contact_id', 'asc')
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
            'title' => "<input id='allUserGroupid' class='allUserGroupid' type='checkbox'>",
            'name' => 'contact_id',
            'data' => 'contact_id',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'checkbox',
                'parameters' => array(
                    'displayName' => '::contact_alias::'
                )
            )
        ),
        array (
            'title' => 'Alias / Login',
            'name' => 'contact_alias',
            'data' => 'contact_alias',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/configuration/user/[i:id]',
                    'routeParams' => array(
                        'id' => '::contact_id::'
                    ),
                    'linkName' => '::contact_alias::'
                )
            )
        ),
        array (
            'title' => 'Full Name',
            'name' => 'contact_name',
            'data' => 'contact_name',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
         array (
            'title' => 'Email',
            'name' => 'contact_email',
            'data' => 'contact_email',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
         array (
            'title' => 'Hosts',
            'name' => 'contact_host_notification_options',
            'data' => 'contact_host_notification_options',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
         array (
            'title' => 'Services',
            'name' => 'contact_service_notification_options',
            'data' => 'contact_service_notification_options',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
         array (
            'title' => 'Language',
            'name' => 'contact_lang',
            'data' => 'contact_lang',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Access',
            'name' => 'contact_oreon',
            'data' => 'contact_oreon',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-danger">Disabled</span>',
                    '1' => '<span class="label label-success">Enabled</span>'
                )
            )
        ),
        array (
            'title' => 'Admin',
            'name' => 'contact_admin',
            'data' => 'contact_admin',
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
            )
        ),
        array (
            'title' => 'Status',
            'name' => 'contact_activate',
            'data' => 'contact_activate',
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
        foreach ($resultSet as &$myUserSet) {
            insertAfter(
                $myUserSet,
                'contact_email',
                array(
                    'contact_host_notification_options' => \CentreonConfiguration\Repository\UserRepository::getNotificationInfos(
                        $myUserSet['contact_id'],
                        'host'
                    ),
                    'contact_service_notification_options' => \CentreonConfiguration\Repository\UserRepository::getNotificationInfos(
                        $myUserSet['contact_id'],
                        'service'
                    )
                )
            );

            $myUserSet['contact_alias'] = \CentreonConfiguration\Repository\UserRepository::getUserIcon(
                $myUserSet['contact_alias'],
                $myUserSet['contact_email']
            );
        }
    }
}

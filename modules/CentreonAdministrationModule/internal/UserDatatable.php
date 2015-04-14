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
    protected static $rowIdColumn = array('id' => 'user_id', 'name' => 'firstname');
    
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
            'name' => 'firstname',
            'data' => 'firstname',
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
    }
}

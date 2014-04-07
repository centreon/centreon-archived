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

namespace CentreonConfiguration\Repository;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class AclgroupRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'acl_groups';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Aclgroup';
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        '<input id="allAclGroups" class="allAclGroups" type="checkbox">' => 'acl_group_id',
        'Name' => 'acl_group_name',
        'Alias' => 'acl_group_alias',
        'Status' => 'acl_group_activate'
    );
    
    /**
     *
     * @var array 
     */
    public static $researchIndex = array(
        'acl_group_id',
        'acl_group_name',
        'acl_group_alias',
        'acl_group_activate'
    );
    
    public static $columnCast = array(
        'acl_group_activate' => array(
            'type' => 'select',
            'parameters' => array(
                '1' => '<span class="label label-success">Enabled</span>',
                '0' => '<span class="label label-danger">Disabled</span>',
            )
        ),
        'acl_group_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::acl_group_name::'
            )
        ),
        'acl_group_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/administration/aclgroup/[i:id]',
                'routeParams' => array(
                    'id' => '::acl_group_id::'
                ),
                'linkName' => '::acl_group_name::'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'search_name',
        'search_alias',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array(
        'none',
        'search_name',
        'search_alias',
        array('select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        )
    );
}

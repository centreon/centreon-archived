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
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class UsergroupRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'contactgroup';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Usergroup';
    
    /**
     *
     * @var array Main database field to get
     */
    public static $datatableColumn = array(
        '<input id="allContact" class="allContact" type="checkbox">' => 'cg_id',
        'Name' => 'cg_name',
        'Description' => 'cg_alias',
        'Status' => 'cg_activate'
    );
    
    /**
     *
     * @var array Column name for the search index
     */
    public static $researchIndex = array(
        'cg_id',
        'cg_alias',
        'cg_name',
        'cg_activate'
    );
    
    /**
     *
     * @var string 
     */
    public static $specificConditions = "";
    
    /**
     * @inherit doc
     * @var array 
     */
    public static $columnCast = array(
        'cg_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::cg_name::'
            )
        ),
        'cg_activate' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>'
            )
        ),
        'cg_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/usergroup/[i:id]',
                'routeParams' => array(
                    'id' => '::cg_id::'
                ),
                'linkName' => '::cg_name::'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'text',
        'text',
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
        'text',
        'text',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        )
    );
}

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
class TrapRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'traps';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Trap';
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        '<input id="allTrap" class="allTrap" type="checkbox">' => 'traps_id',
        'Name' => 'traps_name',
        'OID' => 'traps_oid',
        'Status' => 'traps_status'
    );
    
    /**
     *
     * @var array 
     */
    public static $researchIndex = array(
        'traps_id',
        'traps_name',
        'traps_oid',
        'traps_status'
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'search_name',
        'search_oid',
        array('select' => array(
                'None' => '-1',
                'OK' => '0',
                'Warning' => '1',
                'Critical' => '2',
                'Unknown' => '3'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $columnCast = array(
        'traps_status' => array(
            'type' => 'select',
            'parameters' =>array(
                '-1' => '<span></span>',
                '0' => '<span class="label label-success">OK</span>',
                '1' => '<span class="label label-warning">Warning</span>',
                '2' => '<span class="label label-danger">Critical</span>',
                '3' => '<span>Unknown</span>',
            )
        ),
        'traps_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::traps_name::'
            )
        ),
        'traps_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/trap/[i:id]',
                'routeParams' => array(
                    'id' => '::traps_id::'
                ),
                'linkName' => '::traps_name::'
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
        'search_oid',
        array('select' => array(
                'None' => '-1',
                'OK' => '0',
                'Warning' => '1',
                'Critical' => '2',
                'Unknown' => '3'
            )
        )
    );
}

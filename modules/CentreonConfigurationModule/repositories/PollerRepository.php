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
class PollerRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'nagios_server';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Poller';
    
    /**
     *
     * @var array Main database field to get
     */
    public static $datatableColumn = array(
        '<input id="allPoller" class="allPoller" type="checkbox">' => 'id',
        'Name' => 'name',
        'Ip Address' => 'ns_ip_address',
        'Last restart' => 'last_restart',
        'Engine name' => 'monitoring_engine',
        'Status' => 'ns_activate'
    );
    
    /**
     *
     * @var array Column name for the search index
     */
    public static $researchIndex = array(
        'id',
        'name',
        'ns_ip_address',
        'last_restart',
        'monitoring_engine',
        'ns_activate'
    );
    
    /**
     * @inherit doc
     * @var array 
     */
    public static $columnCast = array(
        'id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::name::'
            )
        ),
        'tp_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/poller/[i:id]',
                'routeParams' => array(
                    'id' => '::id::'
                ),
                'linkName' => '::name::'
            )
        ),
        'last_restart' => array(
            'type' => 'date',
            'parameters' => array(
                'date' => 'd/m/Y H:i:s'
            )
        ),
        'ns_activate' => array(
            'type' => 'select',
            'parameters' => array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>'
            )
        ),
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'text',
        'text',
        'none',
        'none',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        ),
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array(
        'none',
        'text',
        'text',
        'none',
        'none',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        ),
    );
}

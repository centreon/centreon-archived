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

namespace CentreonRealtime\Internal;

use \CentreonConfiguration\Repository\HostRepository as HostConfigurationRepository,
    \Centreon\Internal\Utils\Datetime;

/**
 * Description of HostDatatable
 *
 * @author lionel
 */
class HostDatatable extends \Centreon\Internal\ExperimentalDatatable
{
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('name', 'asc')
        ),
        'stateSave' => true,
        'paging' => true,
    );
    
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonStorageDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonRealtime\Models\Host';
    
    /**
     *
     * @var array 
     */
    protected static $columns = array(
        array (
            'title' => "<input id='allHost' class='allHost' type='checkbox'>",
            'name' => 'host_id',
            'data' => 'host_id',
            'orderable' => false,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                    'type' => 'checkbox',
                    'parameters' => array(
                    'displayName' => '::name::'
                )
            ),
            'className' => 'datatable-align-center',
            'width' => "20px"
        ),
        array (
            'title' => 'Name',
            'name' => 'name',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/realtime/host/[i:id]',
                    'routeParams' => array(
                        'id' => '::host_id::'
                    ),
                    'linkName' => '::name::'
                )
            )
        ),
        array (
            'title' => 'Address',
            'name' => 'address',
            'data' => 'address',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/realtime/host/[i:id]',
                    'routeParams' => array(
                        'id' => '::host_id::'
                    ),
                    'linkName' => '::address::'
                )
            )
        ),
        array (
            'title' => 'State',
            'name' => 'state',
            'data' => 'state',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-success">OK</span>',
                    '1' => '<span class="label label-warning">Warning</span>',
                    '2' => '<span class="label label-danger">Critical</span>',
                    '3' => '<span class="label label-default">Unknown</span>',
                    '4' => '<span class="label label-info">Pending</span>',
                )
            ),
            'searchtype' => 'select',
            'searchvalues' => array(
                'OK' => 0,
                'Warning' => 1,
                'Critical' => 2,
                'Unknown' => 3,
                'Pending' => 4
                                    ),
            'width' => "50px",
            'className' => 'cell_center'
        ),
        array(
            'title' => 'Last Check',
            'name' => '(unix_timestamp(NOW())-last_check) AS last_check',
            'data' => 'last_check',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Duration',
            'name' => '(unix_timestamp(NOW())-last_hard_state_change) AS duration',
            'data' => 'duration',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Retry',
            'name' => 'CONCAT(check_attempt, " / ", max_check_attempts) AS retry',
            'data' => 'retry',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '50px',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Output',
            'name' => 'output',
            'data' => 'output',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Perfdata',
            'name' => 'perfdata',
            'data' => 'perfdata',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => false,
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
    protected function formatDatas(&$resultSet)
    {
        $previousHost = '';
        foreach ($resultSet as &$myHostSet) {
            // Set host_name
            if ($myHostSet['name'] === $previousHost) {
                $myHostSet['name'] = '';
            } else {
                $previousHost = $myHostSet['name'];
                $myHostSet['name'] = \CentreonConfiguration\Repository\HostRepository::getIconImage(
                    $myHostSet['name']
                ).'&nbsp;&nbsp;'.$myHostSet['name'];
            }
            $myHostSet['duration'] = Datetime::humanReadable($myHostSet['duration'],
                                                             Datetime::PRECISION_FORMAT,
                                                             2
                                                             );
            $myHostSet['last_check'] = Datetime::humanReadable($myHostSet['last_check'],
                                                             Datetime::PRECISION_FORMAT,
                                                             2
                                                             );
        }
    }
}

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

use \Centreon\Internal\Utils\Datetime,
    \Centreon\Internal\Di,
    \CentreonConfiguration\Repository\PollerRepository;

/**
 * Description of PollerDatatable
 *
 * @author lionel
 */
class PollerDatatable extends \Centreon\Internal\Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\Poller';
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('name', 'asc'),
            array('id', 'asc')
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
            'title' => '<input id="allPoller" class="allPoller" type="checkbox">',
            'name' => 'id',
            'data' => 'id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'checkbox',
                'parameters' => array(
                    'displayName' => '::name::'
                )
            )
        ),
        array (
            'title' => 'Name',
            'name' => 'name',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'poller',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/configuration/poller/[i:id]',
                    'routeParams' => array(
                        'id' => '::id::'
                    ),
                    'linkName' => '::name::'
                )
            )
        ),
        array (
            'title' => 'IP Address',
            'name' => 'ip_address',
            'data' => 'ip_address',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Localhost',
            'name' => 'localhost',
            'data' => 'localhost',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '0' => 'No',
                    '1' => 'Yes'
                )
            ),
        ),
        array (
            'title' => 'Is Running',
            'name' => 'is_currently_running',
            'data' => 'is_currently_running',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'source' => 'other',
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '0' => '<span class="label label-danger">No</span>',
                    '1' => '<span class="label label-success">Yes</span>'
                )
            ),
        ),
        array (
            'title' => 'Has changed',
            'name' => 'hasChanged',
            'data' => 'hasChanged',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'source' => 'other',
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '0' => 'No',
                    '1' => '<span class="label label-warning">Yes</span>'
                )
            ),
        ),
        array (
            'title' => 'Start time',
            'name' => 'program_start_time',
            'data' => 'program_start_time',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'source' => 'other'
        ),
        array (
            'title' => 'Last Restart',
            'name' => 'last_restart',
            'data' => 'last_restart',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'Version',
            'name' => 'program_version',
            'data' => 'program_version',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'source' => 'other'
        ),
        array (
            'title' => 'Last Update',
            'name' => 'last_alive',
            'data' => 'last_alive',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'source' => 'other'
        ),
        array (
            'title' => 'Default',
            'name' => 'is_default',
            'data' => 'is_default',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '0' => 'No',
                    '1' => 'Yes'
                )
            ),
        ),
        array (
            'title' => 'Status',
            'name' => 'activate',
            'data' => 'activate',
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
     * @param type $resultSet
     */
    public static function addAdditionnalDatas(&$resultSet)
    {
        // Get datatabases connections
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $sqlBroker = "SELECT start_time AS program_start_time, running AS is_currently_running, 
            instance_id, name AS instance_name , last_alive, version AS program_version,  
            engine AS program_name
            FROM rt_instances";
        $stmtBroker = $dbconn->query($sqlBroker);
        $resultBroker = $stmtBroker->fetchAll(\PDO::FETCH_ASSOC);
        
        // Build Up the line
        foreach ($resultSet as &$engineServer) {
            $engineServer['program_start_time'] = '';
            $engineServer['is_currently_running'] = 0;
            $engineServer['last_alive'] = '';
            $engineServer['program_version'] = '';
            $engineServer['program_name'] = '';
            foreach ($resultBroker as $broker) {
                if ($broker['instance_name'] == $engineServer['name']) {
                    $engineServer = array_merge($engineServer, $broker);
                }
            }
            $engineServer['hasChanged'] = PollerRepository::checkChangeState(
                $engineServer['id'],
                $engineServer['last_restart']
            );
        }
    }
    
    /**
     * 
     * @param type $resultSet
     */
    protected function formatDatas(&$resultSet)
    {
        foreach ($resultSet as &$myPollerSet) {
            if (isset($myPollerSet['program_version'])) {
                $myPollerSet['program_version'] = $myPollerSet['program_name'] . ' ' . $myPollerSet['program_version'];
            }

            if (isset($myPollerSet['last_restart'])) {
                $myPollerSet['last_restart'] = Datetime::humanReadable(
                    $myPollerSet['last_restart'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
            }

            if (isset($myPollerSet['last_alive'])) {
                $myPollerSet['last_alive'] = Datetime::humanReadable(
                    $myPollerSet['last_alive'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
            }

            if (isset($myPollerSet['program_start_time'])) {
                $myPollerSet['program_start_time'] = Datetime::humanReadable(
                    $myPollerSet['program_start_time'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
            }
        }
    }
}

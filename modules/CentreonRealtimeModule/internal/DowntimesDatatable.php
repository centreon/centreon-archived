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

use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Datatable;
use Centreon\Internal\Di;

/**
 * Description of HostDatatable
 *
 * @author kevin duret <kduret@centreon.com>
 */
class DowntimesDatatable extends Datatable
{
    protected static $objectId = 'downtime_id';

    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('downtime_id', 'asc')
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonRealtime\Models\Downtimes';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'downtime_id', 'name' => 'object_name');
    
    /**
     *
     * @var array 
     */
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'downtime_id',
            'data' => 'downtime_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'className' => 'cell_center',
            'width' => "20px"
        ),
        array (
            'title' => 'Type',
            'name' => 'type',
            'data' => 'type',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'Type',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '1' => "<i class='icon-service ico-16'></i>",
                    '2' => "<i class='icon-host ico-16'></i>",
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'Host' => '2',
                    'Service' => '1',
                )
            ),
            'className' => 'cell_center',
            'width' => "10px"
        ),
        array (
            'title' => 'Object',
            'name' => 'object_name',
            'data' => 'object_name',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'source' => 'other',
        ),
        array (
            'title' => 'Start Time',
            'name' => 'FROM_UNIXTIME(start_time) as start_date',
            'data' => 'start_date',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '10%',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'End Time',
            'name' => 'FROM_UNIXTIME(end_time) as end_date',
            'data' => 'end_date',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '10%',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Duration',
            'name' => 'duration',
            'data' => 'duration',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '10%',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Author',
            'name' => 'author',
            'data' => 'author',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '10%',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Started',
            'name' => 'started',
            'data' => 'started',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => "No",
                    '1' => "Yes",
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'No' => '0',
                    'Yes' => '1',
                )
            ),
            'width' => '5%',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Fixed',
            'name' => 'fixed',
            'data' => 'fixed',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => "No",
                    '1' => "Yes",
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'No' => '0',
                    'Yes' => '1',
                )
            ),
            'width' => '5%',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Cancelled',
            'name' => 'cancelled',
            'data' => 'cancelled',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => "No",
                    '1' => "Yes",
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'No' => '0',
                    'Yes' => '1',
                )
            ),
            'width' => '5%',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Comments',
            'name' => 'comment_data',
            'data' => 'comment_data',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '40%',
            'className' => 'cell_center'
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

        $sqlServiceDowntime = 'SELECT d.downtime_id, h.name, s.service_id, s.description'
            . ' FROM rt_hosts h, rt_services s, rt_downtimes d'
            . ' WHERE s.service_id=d.service_id AND h.host_id=d.host_id';
        $stmtServiceDowntime = $dbconn->query($sqlServiceDowntime);
        $resultServiceDowntime = $stmtServiceDowntime->fetchAll(\PDO::FETCH_ASSOC);

        $sqlHostDowntime = 'SELECT d.downtime_id, h.host_id, h.name'
            . ' FROM rt_hosts h, rt_downtimes d'
            . ' WHERE h.host_id=d.host_id';
        $stmtHostDowntime = $dbconn->query($sqlHostDowntime);
        $resultHostDowntime = $stmtHostDowntime->fetchAll(\PDO::FETCH_ASSOC);

        // Add object column
        foreach ($resultSet as &$downtime) {
            if ($downtime['type'] == 1) {
                foreach ($resultServiceDowntime as $downtimeObject) {
                    if ($downtimeObject['downtime_id'] === $downtime['downtime_id']) {
                        $downtime['object_name'] = '<a href="/centreon-realtime/service/' . $downtimeObject['service_id'] . '">' . $downtimeObject['name'].' / '.$downtimeObject['description'] . '</a>';
                    }
                }
            } else if ($downtime['type'] == 2) {
                foreach ($resultHostDowntime as $downtimeObject) {
                    if ($downtimeObject['downtime_id'] === $downtime['downtime_id']) {
                        $downtime['object_name'] = '<a href="/centreon-realtime/host/' . $downtimeObject['host_id'] . '">' . $downtimeObject['name'] . '</a>';
                    }
                }
            }
        }
    }
    
    /**
     * 
     * @param array $resultSet
     */
    protected function formatDatas(&$resultSet)
    {
        $router = Di::getDefault()->get('router');
        foreach ($resultSet as &$downtime) {
            $downtime['DT_RowData']['right_side_details'] = $router->getPathFor('/centreon-realtime/downtimes/')
                . $downtime['downtime_id']
                . '/tooltip';

            $downtime['duration'] = Datetime::humanReadable(
                $downtime['duration'],
                Datetime::PRECISION_FORMAT,
                2
            );
        }
    }
}

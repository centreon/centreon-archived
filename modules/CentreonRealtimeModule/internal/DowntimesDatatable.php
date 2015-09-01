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
            'className' => 'cell_center'
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
                    '1' => "<i class='icon-service ico-20'></i>",
                    '2' => "<i class='icon-host ico-20'></i>",
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'Host' => '2',
                    'Service' => '1',
                )
            ),
            'className' => 'cell_center'
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

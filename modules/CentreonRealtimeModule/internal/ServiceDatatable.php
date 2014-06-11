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
    \CentreonConfiguration\Repository\ServiceRepository as ServiceConfigurationRepository,
    \Centreon\Internal\Utils\Datetime;

/**
 * Description of ServiceDatatable
 *
 * @author lionel
 */
class ServiceDatatable extends \Centreon\Internal\ExperimentalDatatable
{
    protected static $hook = 'displayServiceRtColumn';
    protected static $objectId = 'service_id';
    protected static $objectName = 'Service';

    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('h.name', 'asc'),
            array('s.description', 'asc')
        ),
        'stateSave' => true,
        'paging' => true,
    );
    
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonStorageDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonRealtime\Models\Service';
    
    /**
     *
     * @var array 
     */
    protected static $columns = array(
        array (
            'title' => "<input id='allService' class='allService' type='checkbox'>",
            'name' => 'service_id',
            'data' => 'service_id',
            'orderable' => false,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
            'type' => 'checkbox',
                'parameters' => array(
                    'displayName' => '::description::'
                )
            ),
            'className' => 'datatable-align-center', 
            'width' => '15px'
        ),
         array (
            'title' => 'Name',
            'name' => 'h.name',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'source' => array(
                'table' => 'hosts h',
                'condition' => array(
                    'first' => 'h.host_id',
                    'second' => 's.host_id'
                )
            ),
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
            'title' => 'Service',
            'name' => 's.description',
            'data' => 'description',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/realtime/service/[i:id]',
                    'routeParams' => array(
                        'id' => '::service_id::'
                    ),
                    'linkName' => '::description::'
                )
            ),
        ),
        array (
            'title' => "",
            'name' => 's.host_id',
            'data' => 'ico',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            "width" => '15px'
        ),
        array (
            'title' => 'Status',
            'name' => 's.state',
            'data' => 'state',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '0' => '<span class="label label-success">OK</span>',
                    '1' => '<span class="label label-warning">Warning</span>',
                    '2' => '<span class="label label-danger">Critical</span>',
                    '3' => '<span class="label label-default">Unknown</span>',
                    '4' => '<span class="label label-info">Pending</span>',
                )
            ),
            'searchtype' => 'select',
            'searchvalues' => array(
                'Enabled' => '1',
                'Disabled' => '0',
                                    ),
            'width' => '50px',
            'class' => 'cell_center'
        ),
        array (
            'title' => 'Last Check',
            'name' => '(unix_timestamp(NOW())-s.last_check) AS last_check',
            'data' => 'last_check',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '10%'
        ),
        array (
            'title' => 'Duration',
            'name' => '(unix_timestamp(NOW())-s.last_hard_state_change) AS duration',
            'data' => 'duration',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '10%'
        ),
        array (
            'title' => 'Retry',
            'name' => 'CONCAT(s.check_attempt, " / ", s.max_check_attempts) as retry',
            'data' => 'retry',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '25px'
        ),
        array (
            'title' => 'Output',
            'name' => 's.output',
            'data' => 'output',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
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
     * @todo fix getIconImage() (perf issue)
     */
    protected function formatDatas(&$resultSet)
    {
        $previousHost = '';
        foreach ($resultSet as &$myServiceSet) {
            // Set host_name
            if ($myServiceSet['name'] === $previousHost) {
                $myServiceSet['name'] = '';
            } else {
                $previousHost = $myServiceSet['name'];
                $icon = HostConfigurationRepository::getIconImage($myServiceSet['name']);
                $myServiceSet['name'] = '<span class="rt-tooltip">'.
                    $icon.
                    '&nbsp;'.$myServiceSet['name'].'</span>';
            }
            $icon = ServiceConfigurationRepository::getIconImage($myServiceSet['service_id']);
            $myServiceSet['description'] = '<span class="rt-tooltip">'.
                $icon.
                '&nbsp;'.$myServiceSet['description'].'</span>';
            $myServiceSet['ico'] = "<i class='fa fa-bar-chart-o'></i>"; 
            $myServiceSet['duration'] = Datetime::humanReadable(
                                                                $myServiceSet['duration'],
                                                                Datetime::PRECISION_FORMAT,
                                                                2
                                                                );
            $myServiceSet['last_check'] = Datetime::humanReadable(
                                                                $myServiceSet['last_check'],
                                                                Datetime::PRECISION_FORMAT,
                                                                2
                                                                );
            //$myServiceSet['last_check'] = date("d/m/Y - H:i:s", $myServiceSet['last_check']);
        }
    }

}

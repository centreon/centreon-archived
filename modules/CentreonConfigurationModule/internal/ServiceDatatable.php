<?php

/*
 * Copyright 2005-2014 CENTREON
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

namespace CentreonConfiguration\Internal;

use Centreon\Internal\Datatable\Datasource\CentreonDb;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Repository\HostRepository;
use CentreonRealtime\Repository\ServiceRepository as ServiceRealTimeRepository;
use Centreon\Internal\Di;
use Centreon\Internal\Datatable;

/**
 * Description of ServiceDatatable
 *
 * @author lionel
 */
class ServiceDatatable extends Datatable
{
    protected static $objectId = 'service_id';
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('host_name', 'asc'),
            array('service_description', 'asc')
        ),
        'searchCols' => array(
            'service_activate' => '1',
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\Service';
    
    /**
     *
     * @var type 
     */
    protected static $additionnalDatasource = '\CentreonConfiguration\Models\Relation\Service\Host';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'service_id', 'name' => 'service_description');
    
    /**
     *
     * @var array 
     */
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'service_id',
            'data' => 'service_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'className' => 'cell_center',
            'width' => "20px"
        ),
        array (
            'title' => 'Host Id',
            'name' => 'host_id',
            'data' => 'host_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'source' => 'relation',
        ),
        array (
            'title' => 'Host',
            'name' => 'host_name',
            'data' => 'host_name',
            'searchLabel' => 'host',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'source' => 'relation',
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-configuration/host/[i:id]',
                    'routeParams' => array(
                        'id' => '::host_id::'
                    ),
                    'linkName' => '::host_name::'
                )
            )
        ),
        array (
            'title' => 'Service',
            'name' => 'service_description',
            'data' => 'service_description',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'service',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-configuration/service/[i:id]',
                    'routeParams' => array(
                        'id' => '::service_id::',
                        'advanced' => '0'
                    ),
                    'linkName' => '::service_description::'
                )
            ),
        ),
        array (
            'title' => 'Interval',
            'name' => 'service_normal_check_interval',
            'data' => 'service_normal_check_interval',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            "className" => 'cell_center',
            "width" => '40px'
        ),
        array (
            'title' => 'Retry',
            'name' => 'service_retry_check_interval',
            'data' => 'service_retry_check_interval',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            "className" => 'cell_center',
            "width" => '40px'
        ),
        array (
            'title' => 'Atp',
            'name' => 'service_max_check_attempts',
            'data' => 'service_max_check_attempts',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            "className" => 'cell_center',
            "width" => '40px'
        ),
        array (
            'title' => 'Notifications',
            'name' => 'service_notifications_enabled',
            'data' => 'service_notifications_enabled',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-danger">Disabled</span>',
                    '1' => '<span class="label label-success">Enabled</span>',
                    '2' => '<span class="label label-info">Default</span>',
                )
            ),
            "className" => 'cell_center',
            "width" => '40px'
        ),
        array (
            'title' => 'Parent Template',
            'name' => 'service_template_model_stm_id',
            'data' => 'service_template_model_stm_id',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'servicetemplate',
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Status',
            'name' => 'service_activate',
            'data' => 'service_activate',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-danger">Disabled</span>',
                    '1' => '<span class="label label-success">Enabled</span>',
                    '2' => '<span class="label label-warning">Trash</span>',
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'Enabled' => '1',
                    'Disabled' => '0',
                    'Trash' => '2'
                )
            ),
            "className" => 'cell_center',
            "width" => '40px'
        ),
    );

    protected static $extraParams = array(
        'addToHook' => array(
            'objectType' => 'service'
        )
    );

    protected static $hook= 'displayTagList';
    protected static $hookParams = array(
        'resourceType' => 'service'
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
        $router = Di::getDefault()->get('router');
        foreach ($resultSet as &$myServiceSet) {
            
            // Keep up
            $save = $myServiceSet['service_activate'];
            unset($myServiceSet['service_activate']);
            
            // Set host_name
            if ($myServiceSet['host_name'] === $previousHost) {
                $myServiceSet['host_name'] = '';
            } else {
                $previousHost = $myServiceSet['host_name'];
                $myServiceSet['host_name'] = '<span data-overlay-url="'.$router->getPathFor(
                    '/centreon-configuration/host/snapshot/'
                ).
                $myServiceSet['host_id'].
                '"><span class="overlay">'.
                HostRepository::getIconImage(
                    $myServiceSet['host_name']
                ).'&nbsp;'.$myServiceSet['host_name'].
                '</span></span>';
            }
                        
            // Set Scheduling
            $myServiceSet['service_normal_check_interval'] = ServiceRepository::formatNotificationOptions(
                ServiceRepository::getMyServiceField($myServiceSet['service_id'], 'service_normal_check_interval')
            );
            $myServiceSet['service_retry_check_interval'] = ServiceRepository::formatNotificationOptions(
                ServiceRepository::getMyServiceField($myServiceSet['service_id'], 'service_normal_check_interval')
            );
            $myServiceSet['service_max_check_attempts'] = ServiceRepository::getMyServiceField(
                $myServiceSet['service_id'],
                'service_max_check_attempts'
            );
            $myServiceSet['service_notifications'] = ServiceRepository::getNotificicationsStatus(
                $myServiceSet['service_id']
            );
            
            // Get Real Service Description
            if (!$myServiceSet["service_description"]) {
                $myServiceSet["service_description"] = ServiceRepository::getMyServiceAlias(
                    $myServiceSet['service_template_model_stm_id']
                );
            }
            
            // Set Tpl Chain
            $tplStr = null;
            $tplArr = ServiceRepository::getMyServiceTemplateModels($myServiceSet["service_template_model_stm_id"]);
            
            if (!is_null($tplArr)) {
                $tplRoute = str_replace(
                    "//",
                    "/",
                    $router->getPathFor(
                        '/centreon-configuration/servicetemplate/[i:id]',
                        array('id' => $tplArr['id'])
                    )
                );

                $tplStr .= '<span data-overlay-url="'.$router->getPathFor('/centreon-configuration/servicetemplate/viewconf/').
                    $myServiceSet['service_template_model_stm_id'].
                    '"><a href="'.
                    $tplRoute.
                    '" class="overlay">'.
                    $tplArr['description'].
                    '</a></span>';

                $myServiceSet['service_template_model_stm_id'] = $tplStr;
            }
            
            $myServiceSet['service_description'] = '<span data-overlay-url="'.$router->getPathFor(
                '/centreon-configuration/service/snapshot/'
            ).
            $myServiceSet['service_id'].
            '"><span class="overlay">'.
            ServiceRepository::getIconImage($myServiceSet['service_id']).
            '&nbsp;'.
            $myServiceSet['service_description'].
            '</span></span>';
            $myServiceSet['service_description'] .= '</a><a href="#" data-overlay-url="'.$router->getPathFor(
                '/centreon-realtime/service/'.$myServiceSet['host_id'].'/'.$myServiceSet['service_id'].'/tooltip'
            ).'">';
            $myServiceSet['service_description'] .= ServiceRealTimeRepository::getStatusBadge(
                ServiceRealTimeRepository::getStatus($myServiceSet["host_id"], $myServiceSet["service_id"])
            );
            
            $myServiceSet['service_activate'] = $save;
        }
    }
}

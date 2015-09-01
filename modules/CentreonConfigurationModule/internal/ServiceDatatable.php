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

namespace CentreonConfiguration\Internal;

use Centreon\Internal\Datatable\Datasource\CentreonDb;
use CentreonMain\Events\SlideMenu;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Repository\HostRepository;
use CentreonRealtime\Repository\ServiceRepository as ServiceRealTimeRepository;
use CentreonAdministration\Repository\TagsRepository;
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
    public static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('host_id', 'asc'),
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
     * @var array 
     */

    public static  $aFieldNotAuthorized = array('tagname');
    
    /**
     *
     * @var type 
     */
    protected static $additionnalDatasource = 
        '\CentreonConfiguration\Models\Relation\Service\Host';
     
     
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
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            "className" => 'cell_center'
        ),
        array (
            'title' => 'Retry',
            'name' => 'service_retry_check_interval',
            'data' => 'service_retry_check_interval',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            "className" => 'cell_center',
            "width" => '40px'
        ),
        array (
            'title' => 'Attempts',
            'name' => 'service_max_check_attempts',
            'data' => 'service_max_check_attempts',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            "className" => 'cell_center',
            "width" => '40px'
        ),
        array (
            'title' => 'Parent Template',
            'name' => 'service_template_model_stm_id',
            'data' => 'service_template_model_stm_id',
            'orderable' => false,
            'searchable' => false,
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
                    'Disabled' => '0'
                )
            ),
            "className" => 'cell_center',
            "width" => '40px'
        ),
        array (
            'title' => 'Tags',
            'name' => 'tagname',
            'data' => 'tagname',
            'orderable' => false,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '40px',
            'tablename' => 'cfg_tags'
        )
    );
 
    protected static $extraParams = array(
        'addToHook' => array(
            'objectType' => 'service'
        )
    );

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
        HostRepository::setObjectClass('\CentreonConfiguration\Models\Host');
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
                $myServiceSet['host_name'] = '<span class="icoListing">'.HostRepository::getIconImage($myServiceSet['host_name']).'</span>'.$myServiceSet['host_name'];
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
            
            // Get Real Service Description
            if (!$myServiceSet["service_description"]) {
                $myServiceSet["service_description"] = ServiceRepository::getMyServiceAlias(
                    $myServiceSet['service_template_model_stm_id']
                );
            }
            
            $sideMenuCustom = new SlideMenu($myServiceSet['service_id']);

            $events = Di::getDefault()->get('events');
            $events->emit('centreon-configuration.slide.menu.service', array($sideMenuCustom));

            $myServiceSet['DT_RowData']['right_side_menu_list'] = $sideMenuCustom->getMenu();
            $myServiceSet['DT_RowData']['right_side_default_menu'] = $sideMenuCustom->getDefaultMenu();

            // Set Tpl Chain
            $tplStr = null;
            $tplArr = ServiceRepository::getMyServiceTemplateModels($myServiceSet["service_template_model_stm_id"]);
            $idServiceTpl = $myServiceSet["service_template_model_stm_id"];
            
            if (!is_null($tplArr)) {
                $tplRoute = str_replace(
                    "//",
                    "/",
                    $router->getPathFor(
                        '/centreon-configuration/servicetemplate/[i:id]',
                        array('id' => $tplArr['id'])
                    )
                );

                $tplStr .= '<span><a href="'.
                    $tplRoute.
                    '">'.
                    $tplArr['description'].
                    '</a></span>';

                $myServiceSet['service_template_model_stm_id'] = $tplStr;
            }
            
            $myServiceSet['service_description'] = '<span class="icoListing">'.
            ServiceRepository::getIconImage($myServiceSet['service_id']).'</span>'.
            $myServiceSet['service_description'];
            $myServiceSet['service_description'] .= '</a><a href="#">';
            $myServiceSet['service_description'] .= ServiceRealTimeRepository::getStatusBadge(
                ServiceRealTimeRepository::getStatus($myServiceSet["host_id"], $myServiceSet["service_id"])
            );
            
            $myServiceSet['service_activate'] = $save;

            /* Get personal tags */
            $myServiceSet['tagname'] = '';
            $aTagUsed = array();

            $aTags = TagsRepository::getList('service', $myServiceSet['service_id'], 0, 0);

            foreach ($aTags as $oTags) {
                if (!in_array($oTags['id'], $aTagUsed)) {
                    $aTagUsed[] = $oTags['id'];
                    $myServiceSet['tagname'] .= TagsRepository::getTag('service',$myServiceSet['service_id'], $oTags['id'], $oTags['text'], $oTags['user_id'], 1);
                }
            }

            $myServiceSet['tagname'] .= TagsRepository::getAddTag('service', $myServiceSet['service_id']);
        }
    }
}

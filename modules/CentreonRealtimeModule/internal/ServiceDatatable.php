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

use CentreonConfiguration\Repository\HostRepository as HostConfigurationRepository;
use CentreonConfiguration\Repository\ServiceRepository as ServiceConfigurationRepository;
use CentreonRealtime\Repository\ServiceRepository as ServiceRealtimeRepository;
use CentreonRealtime\Models\Host;
use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Datatable;
use CentreonAdministration\Repository\TagsRepository;
use Centreon\Internal\Di;
use CentreonMain\Events\SlideMenu;

/**
 * Description of ServiceDatatable
 *
 * @author lionel
 */
class ServiceDatatable extends Datatable
{
   /*
    protected static $hook = 'displayTagList';
    protected static $hookParams = array(
        'resourceType' => 'service'
    );
*/
    protected static $objectId = 'service_id';
    protected static $objectName = 'Service';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'service_id', 'name' => 'description');

    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('s.state', 'desc'),
            array('s.description', 'asc')
        ),
        'searchCols' => array(),
        'stateSave' => false,
        'paging' => true,
    );
    
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonRealtime\Models\Service';
    
    /**
     *
     * @var array 
     */
    protected static  $aFieldNotAuthorized = array('tagname');
    
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
            'width' => '15px',
            'className' => 'cell_center'
        ),
         array (
            'title' => 'Host',
            'name' => 'host_id',
            'data' => 'host_id',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'host',
            'type' => 'string',
            'visible' => true,
            'source' => 'relation',
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-realtime/host/[i:id]',
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
            'searchLabel' => 'service',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-realtime/service/[i:hid]/[i:sid]',
                    'routeParams' => array(
                        'hid' => '::host_id::',
                        'sid' => '::service_id::'
                    ),
                    'linkName' => '::description::'
                )
            ),
        ),
        array (
            'title' => 'Status',
            'name' => 's.state',
            'data' => 'state',
            'orderable' => true,
            'searchable' => true,
            'type' => 'integer',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '0' => '<span class="label label-success label-fade-success ">OK</span>',
                    '1' => '<span class="label label-warning">Warning</span>',
                    '2' => '<span class="label label-danger">Critical</span>',
                    '3' => '<span class="label label-default">Unknown</span>',
                    '4' => '<span class="label label-info">Pending</span>',
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'multiple' => "true",
                'additionnalParams' => array(
                    'OK' => '0',
                    'Warning' => '1',
                    'Critical' => '2',
                    'Unknown' => '3',
                    'Pending' => '4'
                )
            ),
            'className' => 'cell_center'
        ),
        array (
            'title' => "Graph",
            'name' => 's.host_id',
            'data' => 'ico',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            "width" => '15px',
            'className' => 'cell_center'
        ),

        array (
            'title' => 'Last Check',
            'name' => 's.last_check',
            'data' => 'last_check',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true
        ),
        array (
            'title' => 'Duration',
            'name' => 's.last_hard_state_change AS duration',
            'data' => 'duration',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '10%',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Retry',
            'name' => 'CONCAT(s.check_attempt, " / ", s.max_check_attempts) as retry',
            'data' => 'retry',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '25px',
            'className' => 'cell_center'
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
        array (
            'title' => 'Perfdata',
            'name' => 's.perfdata',
            'data' => 'perfdata',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'Tags',
            'name' => 'tagname',
            'data' => 'tagname',
            'orderable' => false,
            'searchable' => true,
            'type' => 'string',
            'visible' => false,
            'width' => '40px',
            'tablename' => 'cfg_tags'
        ),
    );

     protected static $extraParams = array(
        'addToHook' => array(
            'objectType' => 'service'
        )
    );

    //protected static $hook = 'displayTagList';
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
     * @todo fix getIconImage() (perf issue)
     */
    protected function formatDatas(&$resultSet)
    {
        $router = Di::getDefault()->get('router');
        $previousHost = '';
        HostConfigurationRepository::setObjectClass('\CentreonConfiguration\Models\Host');
        foreach ($resultSet as $key => &$myServiceSet) {
            $aTagUsed = array();
            // Set host_name
            $myHostName = Host::get($myServiceSet['host_id'], array('name'));
            $myServiceSet['name'] = $myHostName['name'];

            
            $sideMenuCustom = new SlideMenu($myServiceSet['service_id']);
            $events = Di::getDefault()->get('events');
            $events->emit('centreon-realtime.slide.menu.service', array($sideMenuCustom));
            $myServiceSet['DT_RowData']['right_side_menu_list'] = $sideMenuCustom->getMenu();
            $myServiceSet['DT_RowData']['right_side_default_menu'] = $sideMenuCustom->getDefaultMenu();
            
            
            
            // @todo remove virtual hosts and virtual services
            if ($myServiceSet['name'] === '_Module_BAM') {
                unset($resultSet[$key]);
                continue;
            }
            if ($myServiceSet['name'] === $previousHost) {
                $myServiceSet['name'] = '';
            } else {
                $previousHost = $myServiceSet['name'];
                $icon = '<span class="icoListing">'.HostConfigurationRepository::getIconImage($myServiceSet['name']).'</span>';
                $myServiceSet['name'] = $icon.$myServiceSet['name'];
            }
            
            $icon = '<span class="icoListing">'.ServiceConfigurationRepository::getIconImage($myServiceSet['service_id']).'</span>';
            /*$myServiceSet['DT_RowData']['right_side_details'] = $router->getPathFor('/centreon-realtime/service/')
                . $myServiceSet['host_id']
                . '/'.$myServiceSet['service_id']
                . '/tooltip';
            */
            
            $myServiceSet['description'] = '<span>'
                . $icon
                . ''.$myServiceSet['description'].'</span>';

            if ($myServiceSet['state'] != '0' && $myServiceSet['state'] != '4') {
                $acknowledgement = ServiceRealtimeRepository::getAcknowledgementInfos($myServiceSet['service_id']);
                if (count($acknowledgement) > 0) {
                    $myServiceSet['description'] .= ' <i class="fa fa-thumb-tack"></i>';
                }
            }

            if ($myServiceSet['perfdata'] != '') {
                $myServiceSet['ico'] = '<span data-overlay-url="/centreon-realtime/service/'
                    . $myServiceSet['host_id']
                    . '/' . $myServiceSet['service_id']
                    .     '/graph"><span class="overlay"><i class="fa fa-bar-chart-o"></i></span></span>';
            } else {
                $myServiceSet['ico'] = ''; 
            }

            $myServiceSet['duration'] = Datetime::humanReadable(
                time() - $myServiceSet['duration'],
                Datetime::PRECISION_FORMAT,
                2
            );

            $myServiceSet['last_check'] = Datetime::humanReadable(
                time() - $myServiceSet['last_check'],
                Datetime::PRECISION_FORMAT,
                2
            );
            
            /* Tags */
            $myServiceSet['tagname']  = "";
            $aTags = TagsRepository::getList('service', $myServiceSet['service_id'], 2, 0);
            foreach ($aTags as $oTags) {
                if (!in_array($oTags['id'], $aTagUsed)) {
                    $aTagUsed[] = $oTags['id'];
                    $myServiceSet['tagname'] .= TagsRepository::getTag('service', $myServiceSet['service_id'], $oTags['id'], $oTags['text'], $oTags['user_id'], $oTags['template_id']);
                }
            }
            /*
            $templates = ServiceConfigurationRepository::getListTemplates($myServiceSet['service_id'], array(), -1);
            foreach ($templates as $template) {
                $aTags = TagsRepository::getList('service', $template, 2, 0);
                foreach ($aTags as $oTags) {
                    if (!in_array($oTags['id'], $aTagUsed)) {
                        $aTagUsed[] = $oTags['id'];
                        $myServiceSet['tagname'] .= TagsRepository::getTag('service', $template, $oTags['id'], $oTags['text'], $oTags['user_id'], 1);
                    }
                }
            }
            */
            
            $myServiceSet['tagname'] .= TagsRepository::getAddTag('service', $myServiceSet['service_id']);
        }
        $resultSet = array_values($resultSet);
    }
}

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
use CentreonRealtime\Repository\HostRepository as HostRealtimeRepository;
use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Datatable;
use Centreon\Internal\Di;
use CentreonAdministration\Repository\TagsRepository;
use CentreonMain\Events\SlideMenu;

/**
 * Description of HostDatatable
 *
 * @author lionel
 */
class HostDatatable extends Datatable
{
    protected static $objectId = 'host_id';

    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('state', 'asc')
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonRealtime\Models\Host';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'host_id', 'name' => 'name');
    
    protected static  $aFieldNotAuthorized = array('tagname');
    
    /**
     *
     * @var array 
     */
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'host_id',
            'data' => 'host_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'className' => 'cell_center',
            'width' => "20px"
        ),
        array (
            'title' => 'Name',
            'name' => 'name',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'host',
            'type' => 'string',
            'visible' => true,
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
                    'route' => '/centreon-realtime/host/[i:id]',
                    'routeParams' => array(
                        'id' => '::host_id::'
                    ),
                    'linkName' => '::address::'
                )
            )
        ),
        array (
            'title' => 'Status',
            'name' => 'state',
            'data' => 'state',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-success">Up</span>',
                    '1' => '<span class="label label-danger">Down</span>',
                    '2' => '<span class="label label-primary">Unreachable</span>',
                    '4' => '<span class="label label-info">Pending</span>'
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'multiple' => "true",
                'additionnalParams' => array(
                    'UP' => '0',
                    'Down' => '1',
                    'Unreachable' => '2',
                    'Pending' => '4'
                )
            ),

            'width' => "50px",
            'className' => 'cell_center'
        ),
        array(
            'title' => 'Last Check',
            'name' => 'last_check',
            'data' => 'last_check',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Duration',
            'name' => 'last_hard_state_change AS duration',
            'data' => 'duration',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Retry',
            'name' => 'CONCAT(check_attempt, " / ", max_check_attempts) AS retry',
            'data' => 'retry',
            'orderable' => false,
            'searchable' => false,
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
            'objectType' => 'host'
        )
    );

    //protected static $hook = 'displayTagList';
    protected static $hookParams = array(
        'resourceType' => 'host'
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
        foreach ($resultSet as $key => &$myHostSet) {
            $sideMenuCustom = new SlideMenu($myHostSet['host_id']);
            $events = Di::getDefault()->get('events');
            $events->emit('centreon-realtime.slide.menu.host', array($sideMenuCustom));

            $myHostSet['DT_RowData']['right_side_menu_list'] = $sideMenuCustom->getMenu();
            $myHostSet['DT_RowData']['right_side_default_menu'] = $sideMenuCustom->getDefaultMenu();

            $aTagUsed = array();
            // @todo remove virtual hosts and virtual services
            if ($myHostSet['name'] === '_Module_BAM') {
                unset($resultSet[$key]);
                continue;
            }

            // Set host_name
            $myHostSet['name'] = '<span class="icoListing">'
                . HostConfigurationRepository::getIconImage($myHostSet['name'])
                . '</span>' . $myHostSet['name'];

            if ($myHostSet['state'] != '0' && $myHostSet['state'] != '4') {
                $acknowledgement = HostRealtimeRepository::getAcknowledgementInfos($myHostSet['host_id']);
                if (count($acknowledgement) > 0) {
                    $myHostSet['name'] .= ' <i class="fa fa-thumb-tack"></i>';
                }
            }

            $myHostSet['duration'] = Datetime::humanReadable(
                time() - $myHostSet['duration'],
                Datetime::PRECISION_FORMAT,
                2
            );

            $myHostSet['last_check'] = Datetime::humanReadable(
                time() - $myHostSet['last_check'],
                Datetime::PRECISION_FORMAT,
                2
            );
            
            /* Tags */
            $myHostSet['tagname']  = "";
            /*
            $aTags = TagsRepository::getList('host', $myHostSet['host_id'], 2, 0);
            foreach ($aTags as $oTags) {
                $myHostSet['tagname'] .= TagsRepository::getTag('host', $myHostSet['host_id'], $oTags['id'], $oTags['text'], $oTags['user_id'], $oTags['template_id']);
            }
             * 
             */
            $aTags = TagsRepository::getList('host', $myHostSet['host_id'], 2, 0);

            foreach ($aTags as $oTags) {
                if (!in_array($oTags['id'], $aTagUsed)) {
                    $aTagUsed[] = $oTags['id'];
                    $myHostSet['tagname'] .= TagsRepository::getTag('host', $myHostSet['host_id'], $oTags['id'], $oTags['text'], $oTags['user_id'], $oTags['template_id']);
                }
            }
            
            //Get tags affected by the template
            $templates = HostConfigurationRepository::getTemplateChain($myHostSet['host_id'], array(), -1);
            foreach ($templates as $template) {
                $aTags = TagsRepository::getList('host', $template['id'], 2, 0);
                foreach ($aTags as $oTags) {
                    if (!in_array($oTags['id'], $aTagUsed)) {
                        $aTagUsed[] = $oTags['id'];
                        $myHostSet['tagname'] .= TagsRepository::getTag('host',$template['id'], $oTags['id'], $oTags['text'], $oTags['user_id'], 1);
                    }
                }
            }
            
            $myHostSet['tagname'] .= TagsRepository::getAddTag('host', $myHostSet['host_id']);
        }
        $resultSet = array_values($resultSet);
    }
}

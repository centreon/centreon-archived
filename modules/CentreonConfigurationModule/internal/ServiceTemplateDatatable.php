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

use Centreon\Internal\Di;
use Centreon\Internal\Utils\HumanReadable;
use Centreon\Internal\Datatable\Datasource\CentreonDb;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Repository\ServicetemplateRepository;
use CentreonAdministration\Repository\TagsRepository;
use Centreon\Internal\Datatable;
use CentreonMain\Events\SlideMenu;

/**
 * Description of ServiceTemplateDatatable
 *
 * @author lionel
 */
class ServiceTemplateDatatable extends Datatable
{
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('service_description', 'asc')
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\Servicetemplate';
    

    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'service_id', 'name' => 'service_description');
    
    /**
     *
     * @var array 
     */
    protected static  $aFieldNotAuthorized = array();
    
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
            'title' => 'Name',
            'name' => 'service_description',
            'data' => 'service_description',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'servicetemplate',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-configuration/servicetemplate/[i:id]',
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
            "className" => 'cell_center',
            "width" => '40px'
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
            "className" => 'cell_center'
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
            'source' => 'relation'
        )
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
        $router = Di::getDefault()->get('router');

        foreach ($resultSet as &$myServiceSet) {
            $myServiceSet['service_description'] = '<span class="icoListing">'.
            ServiceRepository::getIconImage($myServiceSet['service_id']).'</span>'.
            $myServiceSet['service_description'];


            // Set Tpl Chain
            $tplStr = null;
            if (isset($myServiceSet["service_template_model_stm_id"])) {
                $tplArr = ServicetemplateRepository::getMyServiceTemplateModels($myServiceSet["service_template_model_stm_id"]);
                $tplRoute = str_replace(
                                        "//",
                                        "/",
                                        Di::getDefault()
                                        ->get('router')
                                        ->getPathFor(
                                                     '/centreon-configuration/servicetemplate/[i:id]',
                                                     array('id' => $tplArr['id'])
                                                     )
                                        );
                if (isset($tplArr['description'])) {
                    $tplStr .= '<span><a href="'.
                        $tplRoute.
                        '">'.
                        $tplArr['description'].
                        '</a></span>';
                }
            }
            
            
            $sideMenuCustom = new SlideMenu($myServiceSet['service_id']);
            
            $events = Di::getDefault()->get('events');
            $events->emit('centreon-configuration.slide.menu.service.template', array($sideMenuCustom));
            
            //$myHostSet['DT_RowData']['right_side_details'] = $router->getPathFor('/centreon-configuration/host/snapshot/').$myHostSet['host_id'];
            $myServiceSet['DT_RowData']['right_side_menu_list'] = $sideMenuCustom->getMenu();
            $myServiceSet['DT_RowData']['right_side_default_menu'] = $sideMenuCustom->getDefaultMenu();
            
            
            
            
            $myServiceSet['service_template_model_stm_id'] = $tplStr;

            /* Display human readable the check/retry interval */
            $myServiceSet['service_normal_check_interval'] = HumanReadable::convert($myServiceSet['service_normal_check_interval'], 's', $units, null, true);
            $myServiceSet['service_retry_check_interval'] = HumanReadable::convert($myServiceSet['service_retry_check_interval'], 's', $units, null, true);

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

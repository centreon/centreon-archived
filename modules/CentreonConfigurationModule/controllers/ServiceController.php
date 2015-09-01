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

namespace CentreonConfiguration\Controllers;

use Centreon\Internal\Di;
use CentreonConfiguration\Repository\CustomMacroRepository;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Host;
use CentreonConfiguration\Models\Relation\Host\Service as HostService;
use Centreon\Controllers\FormController;
use CentreonAdministration\Repository\TagsRepository;

class ServiceController extends FormController
{
    protected $objectDisplayName = 'Service';
    public static $objectName = 'service';
    public static $enableDisableFieldName = 'service_activate'; 
    protected $objectBaseUrl = '/centreon-configuration/service';
    protected $datatableObject = '\CentreonConfiguration\Internal\ServiceDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Service';
    protected $repository = '\CentreonConfiguration\Repository\ServiceRepository';
    public static $relationMap = array(
        'service_hosts' => '\CentreonConfiguration\Models\Relation\Service\Host',
        'service_parents' => '\CentreonConfiguration\Models\Relation\Service\Serviceparent',
        'service_childs' => '\CentreonConfiguration\Models\Relation\Service\Servicechild',
        'service_servicetemplates' => '\CentreonConfiguration\Models\Relation\Service\Servicetemplate',
        'service_traps' => '\CentreonConfiguration\Models\Relation\Trap\Service',
        'service_icon' => '\CentreonConfiguration\Models\Relation\Service\Icon'
    );
    
    public static $isDisableable = true;

    protected $inheritanceUrl = '/centreon-configuration/servicetemplate/[i:id]/inheritance';
    protected $inheritanceTmplUrl = '/centreon-configuration/servicetemplate/inheritance';
    protected $tmplField = '#service_template_model_stm_id';
    protected $inheritanceTagsUrl = '/centreon-administration/tag/[i:id]/service/herited';

    /**
     * List services
     *
     * @method get
     * @route /service
     */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('jquery.qtip.min.js')
        //addJs('centreon.overlay.js')
            ->addJs('hogan-3.0.0.min.js')
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addJs('moment-with-locales.js')
            ->addJs('moment-timezone-with-data.min.js')
            ->addJs('centreon-clone.js')
            ->addJs('component/custommacro.js')
            ->addCss('centreon.qtip.css');
        $urls = array(
            'tag' => array(
                'add' => $router->getPathFor('/centreon-administration/tag/add'),
                'del' => $router->getPathFor('/centreon-administration/tag/delete'),
                'getallGlobal' => $router->getPathFor('/centreon-administration/tag/all'),
                'getallPerso' => $router->getPathFor('/centreon-administration/tag/allPerso'),
                'addMassive' => $router->getPathFor('/centreon-administration/tag/addMassive')
            )
        );

        $this->tpl->addCustomJs('$(function () {
                $("#modal").on("loaded.bs.modal", function() {
                    initCustomMacro();
                });
            });');
                
        $this->tpl->append('jsUrl', $urls, true);
        $this->tpl->assign('configuration', true);
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /service/formlist
     */
    public function formListAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        $serviceId = Service::getPrimaryKey();
        $serviceDescription = Service::getUniqueLabelField();
        $hostId = Host::getPrimaryKey();
        $hostName = Host::getUniqueLabelField();
        $filters = array(
            $serviceDescription => '%'.$requestParams['q'].'%',
            $hostName => '%'.$requestParams['q'].'%',
        );
        $list = HostService::getMergedParameters(
            array($hostId, $hostName),
            array($serviceId, $serviceDescription),
            -1,
            0,
            null,
            "ASC",
            $filters,
            "OR"
        );
        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$serviceId],
                "text" => $obj[$hostName] . ' ' . $obj[$serviceDescription]
            );
        }
        $router->response()->json($finalList);
    }
    
    /**
     * 
     * @method get
     * @route /service/formlistcomplete
     */
    public function formListCompleteAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        $serviceId = Service::getPrimaryKey();
        $serviceDescription = Service::getUniqueLabelField();
        $hostId = Host::getPrimaryKey();
        $hostName = Host::getUniqueLabelField();
        $filters = array(
            $serviceDescription => '%'.$requestParams['q'].'%',
            $hostName => '%'.$requestParams['q'].'%',
        );
        $list = HostService::getMergedParameters(
            array($hostId, $hostName),
            array($serviceId, $serviceDescription),
            -1,
            0,
            null,
            "ASC",
            $filters,
            "OR"
        );
        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$serviceId] . '_' . $obj[$hostId],
                "text" => $obj[$hostName] . ' ' . $obj[$serviceDescription]
            );
        }
        $router->response()->json($finalList);
    }

    /**
     * Update a service
     *
     * @method post
     * @route /service/update
     */
    public function updateAction()
    {
        $macroList = array();
        $aTagList = array();
        $aTags = array();
        $aTagsInTpl = array();
        $aTagsIdTpl = array();
        $aTagsTemplate = array();

        $givenParameters = $this->getParams('post');
        
        if (isset($givenParameters['macro_name']) && isset($givenParameters['macro_value'])) {
            
            $macroName = $givenParameters['macro_name'];
            $macroValue = $givenParameters['macro_value'];
            
            $macroHidden = $givenParameters['macro_hidden'];
            
            foreach ($macroName as $key => $name) {
                if (!empty($name)) {
                    if (isset($macroHidden[$key])) {
                        $isPassword = '1';
                    } else {
                        $isPassword = '0';
                    }

                    $macroList[$name] = array(
                        'value' => $macroValue[$key],
                        'ispassword' => $isPassword
                    );
                }
            }
        }
        
        if (count($macroList) > 0) {
            try{
                CustomMacroRepository::saveServiceCustomMacro(self::$objectName, $givenParameters['object_id'], $macroList);
            } catch (\Exception $ex) {
                $errorMessage = $ex->getMessage();
                $this->router->response()->json(array('success' => false,'error' => $errorMessage));
            }
        }
        
        //Delete all tags
        TagsRepository::deleteTagsForResource(self::$objectName, $givenParameters['object_id'], 0);
        
        //Insert tags affected to the service
        if (isset($givenParameters['service_tags'])) {
            $aTagList = explode(",", $givenParameters['service_tags']);
            foreach ($aTagList as $var) {
                $var = trim($var);
                if (!empty($var)) {
                    array_push($aTags, $var);
                }
            }
            
            if (count($aTags) > 0) {
                TagsRepository::saveTagsForResource(self::$objectName, $givenParameters['object_id'], $aTags, '', false, 1);
            }
        }
  
        parent::updateAction();
    }
    
    /**
     * Add a service
     *
     * @method post
     * @route /service/add
     */
    public function createAction()
    {
        $macroList = array();
        $aTagList = array();
        $aTags = array();
        
        $givenParameters = $this->getParams('post');
        
        if (isset($givenParameters['macro_name']) && isset($givenParameters['macro_value'])) {
            
            $macroName = $givenParameters['macro_name'];
            $macroValue = $givenParameters['macro_value'];
            
            $macroHidden = $givenParameters['macro_hidden'];

            foreach ($macroName as $key => $name) {
                if (!empty($name)) {
                    if (isset($macroHidden[$key])) {
                        $isPassword = '1';
                    } else {
                        $isPassword = '0';
                    }

                    $macroList[$name] = array(
                        'value' => $macroValue[$key],
                        'ispassword' => $isPassword
                    );
                }
            }            
        }

        $id = parent::createAction(false);
        
        if (count($macroList) > 0) {
            try{
                CustomMacroRepository::saveServiceCustomMacro(self::$objectName, $id, $macroList);
            } catch (\Exception $ex) {
                $errorMessage = $ex->getMessage();
                $this->router->response()->json(array('success' => false,'error' => $errorMessage));
            }
            
        }
        
        if (isset($givenParameters['service_tags'])) {
            $aTagList = explode(",", $givenParameters['service_tags']);
            foreach ($aTagList as $var) {
                $var = trim($var);
                if (!empty($var)) {
                    array_push($aTags, $var);
                }
            }
            if (count($aTags) > 0) {
                TagsRepository::saveTagsForResource(self::$objectName, $id, $aTags, '', false, 1);
            }
        }
        
        $this->router->response()->json(array('success' => true));
    }

    /**
     * Get list of Environment for a specific host
     * 
     * @method get
     * @route /service/[i:id]/environment
     */
    public function environmentForServiceAction()
    {
        parent::getSimpleRelation('environment_id', '\CentreonAdministration\Models\Environment');
    }
    
    /**
     * Get list of Environment for a specific host
     * 
     * @method get
     * @route /service/[i:id]/domain
     */
    public function domainForServiceAction()
    {
        parent::getSimpleRelation('domain_id', '\CentreonAdministration\Models\Domain');
    }
    
    /**
     * Get list of Timeperiods for a specific service
     * 
     * @method get
     * @route /service/[i:id]/checkperiod
     */
    public function checkPeriodForServiceAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get check command for a specific service
     *
     * @method get
     * @route /service/[i:id]/checkcommand
     */
    public function checkCommandForServiceAction()
    {
        parent::getSimpleRelation('command_command_id', '\CentreonConfiguration\Models\Command');
    }

    /**
     * Get list of Commands for a specific service
     * 
     * @method get
     * @route /service/[i:id]/eventhandler
     */
    public function eventHandlerForServiceAction()
    {
        parent::getSimpleRelation('command_command_id2', '\CentreonConfiguration\Models\Command');
    }
    
    /**
     * Get list of contact hosts for a specific service
     * 
     * @method get
     * @route /service/[i:id]/host
     */
    public function hostForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_hosts']);
    }
    
    /**
     * Get list of service for a specific service
     *
     *
     * @method get
     * @route /service/[i:id]/servicetemplate
     */
    public function serviceTemplateForServiceAction()
    {
        parent::getSimpleRelation('service_template_model_stm_id', '\CentreonConfiguration\Models\Servicetemplate');
    }

    /**
     * Trap for a specific service
     *
     * @method get
     * @route /service/[i:id]/trap
     */
    public function trapForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_traps']);
    }
    
    /**
     * Get list of icons for a specific service
     *
     *
     * @method get
     * @route /service/[i:id]/icon
     */
    public function iconForServiceAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $objCall = static::$relationMap['service_icon'];
        $icon = $objCall::getIconForService($requestParam['id']);
        $finalIconList = array();
        if (count($icon) > 0) {
            $filenameExploded = explode('.', $icon['filename']);
            $nbOfOccurence = count($filenameExploded);
            $fileFormat = $filenameExploded[$nbOfOccurence-1];
            $filenameLength = strlen($icon['filename']);
            $routeAttr = array(
                'image' => substr($icon['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                'format' => '.'.$fileFormat
            );
            $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
            $finalIconList = array(
                "id" => $icon['binary_id'],
                "text" => $icon['filename'],
                "theming" => '<img src="'.$imgSrc.'" style="width:20px;height:20px;"> '.$icon['filename']
            );
        }
        
        $router->response()->json($finalIconList);
    }

    /**
     * Display the configuration snapshot of a service
     * with template inheritance
     *
     * @method get
     * @route /service/snapshot/[i:id]
     */
    public function snapshotAction()
    {
        $params = $this->getParams();
        $data = ServiceRepository::getConfigurationData($params['id']);
               
        //If service inherits a template
        if (isset($data['service_template_model_stm_id'])) {
            $data = ServiceRepository::getConfigurationData($data['service_template_model_stm_id']);   
        } else {
            $data = ServiceRepository::getConfigurationData($params['id']);
        }
    
        $checkdata = ServiceRepository::formatDataForTooltip($data);
        $this->tpl->assign('checkdata', $checkdata);
        $this->tpl->display('file:[CentreonConfigurationModule]service_conf_tooltip.tpl');
    }

    /**
     * Get services for a specific acl resource
     *
     * @method get
     * @route /aclresource/[i:id]/service
     */
    public function servicesForAclResourceAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');

        $requestParam = $this->getParams('named');
        $finalServiceList = ServiceRepository::getServicesByAclResourceId($requestParam['id']);

        $router->response()->json($finalServiceList);
    }

     /**
     * Get service tag list
     *
     * @method get
     * @route /service/tag/formlist
     */
     public function serviceTagsAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');

        $list = TagsRepository::getGlobalList('service');

      
        $router->response()->json($list);
    }
    /**
     * Display the configuration snapshot of a service
     * with template inheritance
     *
     * @method get
     * @route /service/snapshotslide/[i:id]
     */
    public function snapshotslideAction()
    {
        $params = $this->getParams();
        
        $data = ServiceRepository::getConfigurationData($params['id']);
        
        $serviceId = Service::getPrimaryKey();
        $serviceDescription = Service::getUniqueLabelField();
        $hostId = Host::getPrimaryKey();
        $hostName = Host::getUniqueLabelField();
        $filters = array(
            $serviceId => $params['id'],
        );
               
        //If service inherits a template
        if (isset($data['service_template_model_stm_id'])) {
            $data = ServiceRepository::getConfigurationData($data['service_template_model_stm_id']);   
        } else {
            $data = ServiceRepository::getConfigurationData($params['id']);
        }
        
        $list = HostService::getMergedParameters(
            array($hostId, $hostName),
            array($serviceId, $serviceDescription),
            -1,
            0,
            null,
            "ASC",
            $filters,
            "OR"
        );
        
        foreach ($list as $obj) {
            $data[$serviceDescription] = $obj[$hostName] . '|' . $obj[$serviceDescription];
        }
    
        $serviceConfiguration = ServiceRepository::formatDataForSlider($data);
        $edit_url = $this->router->getPathFor("/centreon-configuration/service/".$params['id']);
        
        $this->router->response()->json(
                array(
                    'serviceConfig' => $serviceConfiguration,
                    'edit_url' => $edit_url,
                    'success' => true
                )
        );
    }
    
    /**
     * Show all tags of a service
     *
     *
     * @method get
     * @route /service/[i:id]/tags
     */
    public function getServiceTagsAction()
    {
        $requestParam = $this->getParams('named');
                
        $globalTags = TagsRepository::getList('service', $requestParam['id'], 1, 1);
        $globalTagsValues = array();
        foreach($globalTags as $globalTag){
            $globalTagsValues[] = $globalTag['text'];
        }
        $heritedTags = TagsRepository::getHeritedTags('service', $requestParam['id']);
        $heritedTagsValues = $heritedTags['values'];
        
        $tags['tags'] = array('globals' => $globalTagsValues,'herited' => $heritedTagsValues);
        $tags['success'] = true;
        $this->router->response()->json($tags);
    }
}

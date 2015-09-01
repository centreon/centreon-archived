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

use CentreonConfiguration\Repository\CustomMacroRepository;
use Centreon\Internal\Di;
use CentreonConfiguration\Repository\ServicetemplateRepository;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Repository\HostTemplateRepository;
use Centreon\Controllers\FormController;
use CentreonAdministration\Repository\TagsRepository;
use CentreonConfiguration\Models\ServiceTemplate;


/**
 * 
 */
class ServiceTemplateController extends FormController
{
    protected $objectDisplayName = 'Service Template';
    public static $objectName = 'servicetemplate';
    public static $enableDisableFieldName = 'service_activate';
    protected $objectBaseUrl = '/centreon-configuration/servicetemplate';
    protected $datatableObject = '\CentreonConfiguration\Internal\ServiceTemplateDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Servicetemplate';
    protected $repository = '\CentreonConfiguration\Repository\ServicetemplateRepository';
    public static $relationMap = array(
        'service_template_hosts' => '\CentreonConfiguration\Models\Relation\Servicetemplate\Hosttemplate',
        'service_servicetemplates' => '\CentreonConfiguration\Models\Relation\Service\Servicetemplate',
        'service_traps' => '\CentreonConfiguration\Models\Relation\Trap\Servicetemplate',
        'service_icon' => '\CentreonConfiguration\Models\Relation\Servicetemplate\Icon'
    );


    protected $inheritanceUrl = '/centreon-configuration/servicetemplate/[i:id]/inheritance';
    protected $inheritanceTmplUrl = '/centreon-configuration/servicetemplate/inheritance';
    protected $tmplField = '#service_template_model_stm_id';
    protected $inheritanceTagsUrl = '/centreon-administration/tag/[i:id]/servicetemplate/herited';
    
    /**
     *
     * @var boolean 
     */
    public static $isDisableable = true;

    /**
     * List servicetemplates
     *
     * @method get
     * @route /servicetemplate
     */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('centreon.overlay.js')
                ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
                ->addJs('hogan-3.0.0.min.js')
                ->addJs('centreon-clone.js')
                ->addJs('component/custommacro.js');
        
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
     * Update a servicetemplate
     *
     *
     * @method post
     * @route /servicetemplate/update
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
     * Add a service template
     *
     *
     * @method post
     * @route /servicetemplate/add
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
                TagsRepository::saveTagsForResource('service', $id, $aTags, '', false, 1);
            }
        }
        
        $this->router->response()->json(array('success' => true));
    }
    
    /**
     * Get list of Timeperiods for a specific service
     *
     *
     * @method get
     * @route /servicetemplate/[i:id]/checkperiod
     */
    public function checkPeriodForServiceAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get check command for a specific service
     *
     * @method get
     * @route /servicetemplate/[i:id]/checkcommand
     */
    public function checkCommandForServiceAction()
    {
        parent::getSimpleRelation('command_command_id', '\CentreonConfiguration\Models\Command');
    }

    /**
     * Get list of Commands for a specific service
     *
     *
     * @method get
     * @route /servicetemplate/[i:id]/eventhandler
     */
    public function eventHandlerForServiceAction()
    {
        parent::getSimpleRelation('command_command_id2', '\CentreonConfiguration\Models\Command');
    }
    
    /**
     * Get list of contact hosts for a specific service
     *
     *
     * @method get
     * @route /servicetemplate/[i:id]/hosttemplate
     */
    public function hosttemplateForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_template_hosts']);
    }
    
    /**
     * Get list of service template for a specific service template
     *
     *
     * @method get
     * @route /servicetemplate/[i:id]/servicetemplate
     */
    public function serviceTemplateForServiceTemplateAction()
    {
        parent::getSimpleRelation('service_template_model_stm_id', '\CentreonConfiguration\Models\Servicetemplate');
    }
    
    /**
     * Get list of service categories for a specific service
     *
     *
     * @method get
     * @route /servicetemplate/[i:id]/trap
     */
    public function trapForServiceTemplateAction()
    {
        parent::getRelations(static::$relationMap['service_traps']);
    }
    
    /**
     * Get list of icons for a specific service
     *
     *
     * @method get
     * @route /servicetemplate/[i:id]/icon
     */
    public function iconForServiceTemplateAction()
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
     * Display service template configuration in a popin window
     *
     * @method get
     * @route /servicetemplate/viewconf/[i:id]
     */
    public function displayConfAction()
    {
        $params = $this->getParams();
        $data = ServiceRepository::getConfigurationData($params['id']);
        $checkdata = ServiceRepository::formatDataForTooltip($data);
        $this->tpl->assign('checkdata', $checkdata);
        $this->tpl->display('file:[CentreonConfigurationModule]service_conf_tooltip.tpl');
    }

    /**
     * Get inheritance values
     *
     * @method get
     * @route /servicetemplate/[i:id]/inheritance
     */
    public function getInheritanceAction()
    {
        $router = Di::getDefault()->get('router');
        $requestParam = $this->getParams('named');

        $inheritanceValues = ServicetemplateRepository::getInheritanceValues($requestParam['id']);
        array_walk($inheritanceValues, function(&$item, $key) {
            if (false === is_null($item)) {
                $item = ServicetemplateRepository::getTextValue($key, $item);
            }
        });
        $router->response()->json(array(
            'success' => true,
            'values' => $inheritanceValues));
    }

    /**
     * Get inheritance value from a list of template
     *
     * @method post
     * @route /servicetemplate/inheritance
     */
    public function getInheritanceTmplAction()
    {
        $router = Di::getDefault()->get('router');
        $params = $this->getParams('post');

        $tmpl = $params['tmpl'];
        if ($tmpl == ""){
            $router->response()->json(array(
                'success' => true,
                'values' => array()));
        } else {
            $values = ServicetemplateRepository::getInheritanceValues($tmpl, true);
            array_walk($values, function(&$item, $key) {
                if (false === is_null($item)) {
                    $item = HostTemplateRepository::getTextValue($key, $item);
                }
            });
            $router->response()->json(array(
                'success' => true,
                'values' => $values));
        }
    }

    /**
     * Get list of Environment for a specific service template
     * 
     * @method get
     * @route /servicetemplate/[i:id]/domain
     */
    public function domainForServiceTemplateAction()
    {
        parent::getSimpleRelation('domain_id', '\CentreonAdministration\Models\Domain');
    }
    
    /**
    * Display service template configuration in a popin window
    *
    * @method get
    * @route /servicetemplate/viewconfslide/[i:id]
    */
    public function displayConfSlideAction()
    {
        $params = $this->getParams();
        
        $data['service_template'] = ServiceRepository::formatDataForSlider(ServiceRepository::getConfigurationData($params['id']));
        $data['success'] = true;
        $this->router->response()->json($data);
   }
    
    /**
     * Display the configuration snapshot of a service
     * with template inheritance
     *
     * @method get
     * @route /servicetemplate/snapshotslide/[i:id]
     */
    public function snapshotslideAction()
    {
        $params = $this->getParams();
        
        $data = ServiceTemplateRepository::getConfigurationData($params['id']);

        //If service inherits a template
        /*if (isset($data['service_template_model_stm_id'])) {
            $data = ServiceTemplateRepository::getConfigurationData($data['service_template_model_stm_id']);   
        } else {
            $data = ServiceTemplateRepository::getConfigurationData($params['id']);
        }*/

        $serviceConfiguration = ServiceRepository::formatDataForSlider($data);
        $edit_url = $this->router->getPathFor("/centreon-configuration/servicetemplate/".$params['id']);
        
        $this->router->response()->json(
                array(
                    'serviceConfig' => $serviceConfiguration,
                    'edit_url' => $edit_url,
                    'success' => true
                )
        );
    }
    
    
    /**
     * Get command of a Host
     *
     *
     * @method get
     * @route /servicetemplate/[i:id]/command
     */
    public function getHostCommandAction()
    {
        parent::getSimpleRelation('command_command_id', '\CentreonConfiguration\Models\Command');
    }
   
   
   
   
   
   
}

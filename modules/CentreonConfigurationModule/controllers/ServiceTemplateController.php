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

namespace CentreonConfiguration\Controllers;

use CentreonConfiguration\Repository\CustomMacroRepository;
use Centreon\Internal\Di;
use CentreonConfiguration\Repository\ServicetemplateRepository;
use CentreonConfiguration\Repository\ServiceRepository;

/**
 * 
 */
class ServiceTemplateController extends \CentreonConfiguration\Controllers\BasicController
{
    protected $objectDisplayName = 'ServiceTemplate';
    protected $objectName = 'servicetemplate';
    protected $objectBaseUrl = '/configuration/servicetemplate';
    protected $datatableObject = '\CentreonConfiguration\Internal\ServiceTemplateDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Servicetemplate';
    protected $repository = '\CentreonConfiguration\Repository\ServicetemplateRepository';
    public static $relationMap = array(
        'service_servicegroups' => '\CentreonConfiguration\Models\Relation\Servicetemplate\Servicegroup',
        'service_template_hosts' => '\CentreonConfiguration\Models\Relation\Servicetemplate\Hosttemplate',
        'service_template_servicecategories' => '\CentreonConfiguration\Models\Relation\Servicetemplate\Servicecategory',
        'service_contacts' => '\CentreonConfiguration\Models\Relation\Servicetemplate\Contact',
        'service_contactgroups' => '\CentreonConfiguration\Models\Relation\Servicetemplate\Contactgroup',
        'service_servicetemplates' => '\CentreonConfiguration\Models\Relation\Service\Servicetemplate',
        'service_traps' => '\CentreonConfiguration\Models\Relation\Trap\Servicetemplate',
        'service_icon' => '\CentreonConfiguration\Models\Relation\Servicetemplate\Icon'
    );


    protected $inheritanceUrl = '/configuration/servicetemplate/[i:id]/inheritance';
    protected $inheritanceTmplUrl = '/configuration/servicetemplate/inheritance';
    protected $tmplField = '#service_template_model_stm_id';
    
    /**
     *
     * @var boolean 
     */
    public static $isDisableable = true;

    /**
     * List servicetemplates
     *
     * @method get
     * @route /configuration/servicetemplate
     */
    public function listAction()
    {
        $this->tpl->addJs('centreon.overlay.js');
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/servicetemplate/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/servicetemplate/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Update a servicetemplate
     *
     *
     * @method post
     * @route /configuration/servicetemplate/update
     */
    public function updateAction()
    {
        $macroList = array();
        
        $givenParameters = $this->getParams('post');
        
        if (isset($givenParameters['macro_name']) && isset($givenParameters['macro_value'])) {
            
            $macroName = $givenParameters['macro_name'];
            $macroValue = $givenParameters['macro_value'];
            
            $macroHidden = $givenParameters['macro_hidden'];
            
            $nbMacro = count($macroName);
            for($i=0; $i<$nbMacro; $i++) {
                if (!empty($macroName[$i])) {
                    if (isset($macroHidden[$i])) {
                        $isPassword = '1';
                    } else {
                        $isPassword = '0';
                    }
                    
                    $macroList[$macroName[$i]] = array(
                        'value' => $macroValue[$i],
                        'ispassword' => $isPassword
                    );
                }
            }
        }
        
        if (count($macroList) > 0) {
            CustomMacroRepository::saveServiceCustomMacro($givenParameters['object_id'], $macroList);
        }
        parent::updateAction();
    }
    
    /**
     * Add a service template
     *
     *
     * @method post
     * @route /configuration/servicetemplate/add
     */
    public function createAction()
    {
        $macroList = array();
        
        $givenParameters = $this->getParams('post');
        
        if (isset($givenParameters['macro_name']) && isset($givenParameters['macro_value'])) {
            
            $macroName = $givenParameters['macro_name'];
            $macroValue = $givenParameters['macro_value'];
            
            $macroHidden = $givenParameters['macro_hidden'];
            
            $nbMacro = count($macroName);
            for($i=0; $i<$nbMacro; $i++) {
                if (!empty($macroName[$i])) {
                    if (isset($macroHidden[$i])) {
                        $isPassword = '1';
                    } else {
                        $isPassword = '0';
                    }
                    
                    $macroList[$macroName[$i]] = array(
                        'value' => $macroValue[$i],
                        'ispassword' => $isPassword
                    );
                }
            }
        }
        
        $id = parent::createAction(false);
        
        if (count($macroList) > 0) {
            CustomMacroRepository::saveServiceCustomMacro($id, $macroList);
        }

        $this->router->response()->json(array('success' => true));
    }
    
    /**
     * Add a servicetemplate
     *
     * @method get
     * @route /configuration/servicetemplate/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/configuration/servicetemplate/add');
        parent::addAction();
    }
    
    /**
     * Update a service template
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/servicetemplate/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/servicetemplate/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate a hosts
     *
     * @method POST
     * @route /configuration/servicetemplate/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/servicetemplate/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for servicetemplate
     *
     * @method post
     * @route /configuration/servicetemplate/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
    
    /**
     * Enable action for service template
     * 
     * @method post
     * @route /configuration/servicetemplate/enable
     */
    public function enableAction()
    {
        parent::enableAction('service_activate');
    }
    
    /**
     * Disable action for service template
     * 
     * @method post
     * @route /configuration/servicetemplate/disable
     */
    public function disableAction()
    {
        parent::disableAction('service_activate');
    }
    
    /**
     * Get list of Timeperiods for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/checkperiod
     */
    public function checkPeriodForServiceAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get list of Timeperiods for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/notificationperiod
     */
    public function notificationPeriodForServiceAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id2', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get check command for a specific service
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/checkcommand
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
     * @route /configuration/servicetemplate/[i:id]/eventhandler
     */
    public function eventHandlerForServiceAction()
    {
        parent::getSimpleRelation('command_command_id2', '\CentreonConfiguration\Models\Command');
    }
    
    /**
     * Get list of contacts for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/contact
     */
    public function contactForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_contacts']);
    }
    
    /**
     * Get list of contact groups for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/contactgroup
     */
    public function contactgroupForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_contactgroups']);
    }
    
    /**
     * Get list of contact hosts for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/hosttemplate
     */
    public function hosttemplateForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_template_hosts']);
    }
    
    /**
     * Get list of service group for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/servicegroup
     */
    public function serviceGroupForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_servicegroups']);
    }

    /**
     * Get list of service categories for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/servicecategory
     */
    public function serviceCategoryForServiceTemplateAction()
    {
        parent::getRelations(static::$relationMap['service_template_servicecategories']);
    }
    
    /**
     * Get list of service template for a specific service template
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/servicetemplate
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
     * @route /configuration/servicetemplate/[i:id]/trap
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
     * @route /configuration/servicetemplate/[i:id]/icon
     */
    public function iconForServiceTemplateAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
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
     * @route /configuration/servicetemplate/viewconf/[i:id]
     */
    public function displayConfAction()
    {
        $params = $this->getParams();
        $data = ServiceRepository::getConfigurationData($params['id']);
        list($checkdata, $notifdata) = ServiceRepository::formatDataForTooltip($data);
        $this->tpl->assign('checkdata', $checkdata);
        $this->tpl->display('file:[CentreonConfigurationModule]service_conf_tooltip.tpl');
    }

    /**
     * Get inheritance values
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/inheritance
     */
    public function getInheritanceAction()
    {
        $router = Di::getDefault()->get('router');
        $requestParam = $this->getParams('named');

        $inheritanceValues = ServicetemplateRepository::getInheritanceValues($requestParam['id']);
        array_walk($inheritanceValues, function(&$item, $key) {
            if (false === is_null($item)) {
                $item = \CentreonConfiguration\Repository\ServicetemplateRepository::getTextValue($key, $item);
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
     * @route /configuration/servicetemplate/inheritance
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
                    $item = \CentreonConfiguration\Repository\HostTemplateRepository::getTextValue($key, $item);
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
     * @route /configuration/servicetemplate/[i:id]/domain
     */
    public function domainForServiceTemplateAction()
    {
        parent::getSimpleRelation('domain_id', '\CentreonAdministration\Models\Domain');
    }
}

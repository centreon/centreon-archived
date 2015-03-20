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

namespace CentreonConfiguration\Controllers;

use Centreon\Internal\Di;
use CentreonConfiguration\Repository\CustomMacroRepository;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Host;
use CentreonConfiguration\Models\Relation\Host\Service as HostService;
use Centreon\Controllers\FormController;

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
        'service_servicegroups' => '\CentreonConfiguration\Models\Relation\Service\Servicegroup',
        'service_hosts' => '\CentreonConfiguration\Models\Relation\Service\Host',
        'servicecategories' => '\CentreonConfiguration\Models\Relation\Service\Servicecategory',
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

    /**
     * List services
     *
     * @method get
     * @route /service
     */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('centreon.overlay.js')
            ->addJs('jquery.qtip.min.js')
            ->addJs('hogan-3.0.0.min.js')
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addCss('centreon.qtip.css')
            ->addCss('centreon.tag.css', 'centreon-administration');
        $urls = array(
            'tag' => array(
                'add' => $router->getPathFor('/centreon-administration/tag/add'),
                'del' => $router->getPathFor('/centreon-administration/tag/delete')
            )
        );
        $this->tpl->append('jsUrl', $urls, true);
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
     * Add a service
     *
     * @method post
     * @route /service/add
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
     * Get list of Timeperiods for a specific service
     * 
     * @method get
     * @route /service/[i:id]/notificationperiod
     */
    public function notificationPeriodForServiceAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id2', '\CentreonConfiguration\Models\Timeperiod');
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
     * Get list of service group for a specific service
     * 
     * @method get
     * @route /service/[i:id]/servicegroup
     */
    public function serviceGroupForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_servicegroups']);
    }

    /**
     * Get list of service categories for a specific service
     * 
     * @method get
     * @route /service/[i:id]/servicecategory
     */
    public function serviceCategoryForServiceAction()
    {
        parent::getRelations(static::$relationMap['servicecategories']);
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
        list($checkdata, $notifdata) = ServiceRepository::formatDataForTooltip($data);
        $this->tpl->assign('checkdata', $checkdata);
        $this->tpl->display('file:[CentreonConfigurationModule]service_conf_tooltip.tpl');
    }
}

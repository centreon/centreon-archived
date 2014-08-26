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

class ServiceController extends \CentreonConfiguration\Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'Service';
    protected $objectName = 'service';
    protected $objectBaseUrl = '/configuration/service';
    protected $datatableObject = '\CentreonConfiguration\Internal\ServiceDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Service';
    protected $repository = '\CentreonConfiguration\Repository\ServiceRepository';
    public static $relationMap = array(
        'service_servicegroups' => '\CentreonConfiguration\Models\Relation\Service\Servicegroup',
        'service_hosts' => '\CentreonConfiguration\Models\Relation\Service\Host',
        'servicecategories' => '\CentreonConfiguration\Models\Relation\Service\Servicecategory',
        'service_parents' => '\CentreonConfiguration\Models\Relation\Service\Serviceparent',
        'service_childs' => '\CentreonConfiguration\Models\Relation\Service\Servicechild',
        'service_contacts' => '\CentreonConfiguration\Models\Relation\Service\Contact',
        'service_contactgroups' => '\CentreonConfiguration\Models\Relation\Service\Contactgroup',
        'service_servicetemplates' => '\CentreonConfiguration\Models\Relation\Service\Servicetemplate',
        'service_traps' => '\CentreonConfiguration\Models\Relation\Trap\Service',
        'service_icon' => '\CentreonConfiguration\Models\Relation\Service\Icon'
    );
    
    public static $isDisableable = true;

    /**
     * List services
     *
     * @method get
     * @route /configuration/service
     */
    public function listAction()
    {
        $this->tpl->addJs('centreon.overlay.js')
            ->addJs('jquery.qtip.min.js')
            ->addCss('jquery.qtip.min.css')
            ->addCss('centreon.qtip.css');
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/service/formlist
     */
    public function formListAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        $serviceId = \CentreonConfiguration\Models\Service::getPrimaryKey();
        $serviceDescription = \CentreonConfiguration\Models\Service::getUniqueLabelField();
        $hostId = \CentreonConfiguration\Models\Host::getPrimaryKey();
        $hostName = \CentreonConfiguration\Models\Host::getUniqueLabelField();
        $filters = array(
            $serviceDescription => '%'.$requestParams['q'].'%',
            $hostName => '%'.$requestParams['q'].'%',
        );
        $list = \CentreonConfiguration\Models\Relation\Host\Service::getMergedParameters(
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
     * @route /configuration/service/formlistcomplete
     */
    public function formListCompleteAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        $serviceId = \CentreonConfiguration\Models\Service::getPrimaryKey();
        $serviceDescription = \CentreonConfiguration\Models\Service::getUniqueLabelField();
        $hostId = \CentreonConfiguration\Models\Host::getPrimaryKey();
        $hostName = \CentreonConfiguration\Models\Host::getUniqueLabelField();
        $filters = array(
            $serviceDescription => '%'.$requestParams['q'].'%',
            $hostName => '%'.$requestParams['q'].'%',
        );
        $list = \CentreonConfiguration\Models\Relation\Host\Service::getMergedParameters(
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
     * 
     * @method get
     * @route /configuration/service/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }

    /**
     * Update a service
     *
     * @method post
     * @route /configuration/service/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a service
     *
     * @method get
     * @route /configuration/service/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/configuration/service/add');
        parent::addAction();
    }
    
    /**
     * Add a service
     *
     * @method post
     * @route /configuration/service/add
     */
    public function createAction()
    {
        parent::createAction();
    }
    
    /**
     * Update a service
     *
     *
     * @method get
     * @route /configuration/service/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/service/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/service/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate a hosts
     *
     * @method POST
     * @route /configuration/service/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/service/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for service
     *
     * @method post
     * @route /configuration/service/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
    
    /**
     * Enable action for service
     * 
     * @method post
     * @route /configuration/service/enable
     */
    public function enableAction()
    {
        parent::enableAction('service_activate');
    }
    
    /**
     * Disable action for service
     * 
     * @method post
     * @route /configuration/service/disable
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
     * @route /configuration/service/[i:id]/checkperiod
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
     * @route /configuration/service/[i:id]/notificationperiod
     */
    public function notificationPeriodForServiceAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id2', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get check command for a specific service
     *
     * @method get
     * @route /configuration/service/[i:id]/checkcommand
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
     * @route /configuration/service/[i:id]/eventhandler
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
     * @route /configuration/service/[i:id]/contact
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
     * @route /configuration/service/[i:id]/contactgroup
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
     * @route /configuration/service/[i:id]/host
     */
    public function hostForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_hosts']);
    }
    
    /**
     * Get list of service group for a specific service
     *
     *
     * @method get
     * @route /configuration/service/[i:id]/servicegroup
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
     * @route /configuration/service/[i:id]/servicecategory
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
     * @route /configuration/service/[i:id]/servicetemplate
     */
    public function serviceTemplateForServiceAction()
    {
        parent::getSimpleRelation('service_template_model_stm_id', '\CentreonConfiguration\Models\Servicetemplate');
    }

    /**
     * Trap for a specific service
     *
     * @method get
     * @route /configuration/service/[i:id]/trap
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
     * @route /configuration/service/[i:id]/icon
     */
    public function iconForServiceAction()
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
     * Display the configuration snapshot of a service
     * with template inheritance
     *
     * @method get
     * @route /configuration/service/snapshot/[i:id]
     */
    public function snapshotAction()
    {
        $params = $this->getParams();
        $this->tpl->assign('id', $params['id']);
        $this->tpl->display('file:[CentreonConfigurationModule]service_conf_tooltip.tpl');
    }
}

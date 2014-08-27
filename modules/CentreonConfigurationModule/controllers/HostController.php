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

use \Centreon\Internal\Di;
use \CentreonConfiguration\Models\Host;
use \CentreonConfiguration\Models\Relation\Host\Contact;
use \CentreonConfiguration\Models\Relation\Host\Contactgroup;
use \CentreonConfiguration\Models\Relation\Host\Hostchild;
use \CentreonConfiguration\Models\Relation\Host\Hostparent;
use \CentreonConfiguration\Models\Relation\Host\Poller;
use \CentreonConfiguration\Models\Timeperiod;
use \CentreonConfiguration\Models\Command;
use \CentreonConfiguration\Internal\HostDatatable;
use \CentreonConfiguration\Repository\HostRepository;

class HostController extends \CentreonConfiguration\Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'Host';
    protected $objectName = 'host';
    protected $datatableObject = '\CentreonConfiguration\Internal\HostDatatable';
    protected $objectBaseUrl = '/configuration/host';
    protected $objectClass = '\CentreonConfiguration\Models\Host';
    protected $repository = '\CentreonConfiguration\Repository\HostRepository';
    
    public static $relationMap = array(
        'host_hostgroups' => '\CentreonConfiguration\Models\Relation\Host\Hostgroup',
        'host_hostcategories' => '\CentreonConfiguration\Models\Relation\Host\Hostcategory',
        'host_parents' => '\CentreonConfiguration\Models\Relation\Host\Hostparent',
        'host_childs' => '\CentreonConfiguration\Models\Relation\Host\Hostchild',
        'host_contacts' => '\CentreonConfiguration\Models\Relation\Host\Contact',
        'host_contactgroups' => '\CentreonConfiguration\Models\Relation\Host\Contactgroup',
        'host_hosttemplates' => '\CentreonConfiguration\Models\Relation\Host\Hosttemplate',
        'host_icon' => '\CentreonConfiguration\Models\Relation\Host\Icon'
    );
    
    public static $isDisableable = true;

    /**
     * List hosts
     *
     * @method get
     * @route /configuration/host
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
     * @route /configuration/host/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/host/list
     */
    public function datatableAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $myDatatable = new HostDatatable($this->getParams('get'), $this->objectClass);
        $myDataForDatatable = $myDatatable->getDatas();
        
        $router->response()->json($myDataForDatatable);
    }
    
    /**
     * Create a new host
     *
     * @method post
     * @route /configuration/host/add
     */
    public function createAction()
    {
        $givenParameters = $this->getParams('post');
        $givenParameters['host_register'] = 1;
        if (!isset($givenParameters['host_alias']) && isset($givenParameters['host_name'])) {
            $givenParameters['host_alias'] = $givenParameters['host_name'];
        }
        $id = parent::createAction();
        if (isset($givenParameters['host_create_services_from_template']) &&
            $givenParameters['host_create_services_from_template']) {
            \CentreonConfiguration\Models\Host::deployServices($id);
        }
    }

    /**
     * Update a host
     *
     *
     * @method post
     * @route /configuration/host/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        parent::updateAction();
        if ($givenParameters['host_create_services_from_template']) {
            Host::deployServices($givenParameters['object_id']);
        }
    }
    
    /**
     * Add a host
     *
     * @method get
     * @route /configuration/host/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/configuration/host/add');
        parent::addAction();
    }
    
    /**
     * Update a host
     *
     * @method get
     * @route /configuration/host/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }
    
    /**
     * Get list of contacts for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/contact
     */
    public function contactForHostAction()
    {
        parent::getRelations(static::$relationMap['host_contacts']);
    }
    
    /**
     * Get list of contact groups for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/contactgroup
     */
    public function contactgroupForHostAction()
    {
        parent::getRelations(static::$relationMap['host_contactgroups']);
    }
    
    /**
     * Get list of hostgroups for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/hostgroup
     */
    public function hostgroupForHostAction()
    {
        parent::getRelations(static::$relationMap['host_hostgroups']);
    }
    
    /**
     * Get list of hostcategories for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/hostcategory
     */
    public function hostcategoryForHostAction()
    {
        parent::getRelations(static::$relationMap['host_hostcategories']);
    }
    
    /**
     * Get list of hostcategories for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/icon
     */
    public function iconForHostAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $objCall = static::$relationMap['host_icon'];
        $icon = $objCall::getIconForHost($requestParam['id']);
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
     * Get host template for a specific host
     *
     * @method get
     * @route /configuration/host/[i:id]/hosttemplate
     */
    public function hostTemplateForHostAction()
    {
        parent::getRelations(static::$relationMap['host_hosttemplates']);
    }

    /**
     * 
     * @method get
     * @route /configuration/host/[i:id]/parent
     */
    public function parentForHostAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostparentList = Hostparent::getMergedParameters(
            array('host_id', 'host_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('host_hostparent_relation.host_host_id' => $requestParam['id']),
            "AND"
        );

        $finalHostList = array();
        foreach ($hostparentList as $hostparent) {
            $finalHostList[] = array(
                "id" => $hostparent['host_id'],
                "text" => $hostparent['host_name'],
                "theming" => HostRepository::getIconImage(
                    $hostparent['host_name']
                ).' '.$hostparent['host_name']
            );
        }
        
        $router->response()->json($finalHostList);
    }

    /**
     * 
     * @method get
     * @route /configuration/host/[i:id]/child
     */
    public function childForHostAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostchildList = Hostchild::getMergedParameters(
            array('host_id', 'host_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('host_hostparent_relation.host_parent_hp_id' => $requestParam['id']),
            "AND"
        );

        $finalHostList = array();
        foreach ($hostchildList as $hostchild) {
            $finalHostList[] = array(
                "id" => $hostchild['host_id'],
                "text" => $hostchild['host_name'],
                "theming" => HostRepository::getIconImage(
                    $hostchild['host_name']
                ).' '.$hostchild['host_name']
            );
        }
        
        $router->response()->json($finalHostList);
    }
    
    /**
     * Get list of Timeperiods for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/checkperiod
     */
    public function checkPeriodForHostAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get list of Timeperiods for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/notificationperiod
     */
    public function notificationPeriodForHostAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id2', '\CentreonConfiguration\Models\Timeperiod');
    }

    /**
     * Get check command for a specific host
     *
     * @method get
     * @route /configuration/host/[i:id]/checkcommand
     */
    public function checkcommandForHostAction()
    {
        parent::getSimpleRelation('command_command_id', '\CentreonConfiguration\Models\Command');
    }

    /**
     * Get list of Commands for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/eventhandler
     */
    public function eventHandlerForHostAction()
    {
        parent::getSimpleRelation('command_command_id2', '\CentreonConfiguration\Models\Command');
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/host/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/host/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }
    
    /**
     * Get list of pollers for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/poller
     */
    public function pollerForHostAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $pollerList = Poller::getMergedParameters(
            array('id', 'name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('host.host_id' => $requestParam['id']),
            "AND"
        );
        
        $finalPollerList = array();
        if (count($pollerList) > 0) {
            $finalPollerList["id"] = $pollerList[0]['id'];
            $finalPollerList["text"] = $pollerList[0]['name'];
        }
        
        $router->response()->json($finalPollerList);
    }
    
    /**
     * Duplicate a hosts
     *
     * @method post
     * @route /configuration/host/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/host/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for host
     *
     * @method post
     * @route /configuration/host/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
    
    /**
     * Enable action for host
     * 
     * @method post
     * @route /configuration/host/enable
     */
    public function enableAction()
    {
        parent::enableAction('host_activate');
    }
    
    /**
     * Disable action for host
     * 
     * @method post
     * @route /configuration/host/disable
     */
    public function disableAction()
    {
        parent::disableAction('host_activate');
    }

    /**
     * Display the configuration snapshot of a host
     * with template inheritance
     *
     * @method get
     * @route /configuration/host/snapshot/[i:id]
     */
    public function snapshotAction()
    {
        $params = $this->getParams();
        $data = HostRepository::getConfigurationData($params['id']);
        list($checkdata, $notifdata) = HostRepository::formatDataForTooltip($data);
        $this->tpl->assign('checkdata', $checkdata);
        $this->tpl->assign('notifdata', $notifdata);
        $this->tpl->display('file:[CentreonConfigurationModule]host_conf_tooltip.tpl');
    }
}

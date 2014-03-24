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

namespace Controllers\Configuration;

use \Models\Configuration\Host,
    \Models\Configuration\Relation\Host\Contact,
    \Models\Configuration\Relation\Host\Contactgroup,
    \Models\Configuration\Relation\Host\Hostchild,
    \Models\Configuration\Relation\Host\Hostparent,
    \Models\Configuration\Relation\Host\Poller,
    \Models\Configuration\Timeperiod,
    \Models\Configuration\Command,
    \Centreon\Core\Form,
    \Centreon\Core\Form\Generator;

class HostTemplateController extends \Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'Hosttemplate';
    protected $objectName = 'hosttemplate';
    protected $objectBaseUrl = '/configuration/hosttemplate';
    protected $objectClass = '\Models\Configuration\Host';
    public static $relationMap = array(
        'host_hostgroups' => '\Models\Configuration\Relation\Host\Hostgroup',
        'host_categories' => '\Models\Configuration\Relation\Host\Hostcategory',
        'host_parents' => '\Models\Configuration\Relation\Host\Hostparent',
        'host_childs' => '\Models\Configuration\Relation\Host\Hostchild',
        'host_contacts' => '\Models\Configuration\Relation\Host\Contact',
        'host_contactgroups' => '\Models\Configuration\Relation\Host\Contactgroup',
        'host_hosttemplates' => '\Models\Configuration\Relation\Host\Hosttemplate'
    );

    /**
     * List hosts
     *
     * @method get
     * @route /configuration/hosttemplate
     */
    public function listAction()
    {
        parent::listAction();
    }
    
    /**
     * 
     * @method get
     * @route /configuration/hosttemplate/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/hosttemplate/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Create a new host template
     *
     * @method post
     * @route /configuration/hosttemplate/add
     */
    public function createAction()
    {
        $givenParameters = $this->getParams('post');
        $givenParameters['host_register'] = 1;
        if (!isset($givenParameters['host_alias']) && isset($givenParameters['host_name'])) {
            $givenParameters['host_alias'] = $givenParameters['host_name'];
        }
        $id = parent::createAction();
        if ($givenParameters['host_create_services_from_template']) {
            \Models\Configuration\Host::deployServices($id);
        }
    }

    /**
     * Update a host
     *
     *
     * @method post
     * @route /configuration/hosttemplate/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        parent::updateAction();
        if ($givenParameters['host_create_services_from_template']) {
            \Models\Configuration\Host::deployServices($givenParameters['object_id']);
        }
    }
    
    /**
     * Add a host template
     *
     * @method get
     * @route /configuration/hosttemplate/add
     */
    public function addAction()
    {
        $tpl = \Centreon\Core\Di::getDefault()->get('template');
        $tpl->assign('validateUrl', '/configuration/hosttemplate/add');
        parent::addAction();
    }
    
    /**
     * Update a host template
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/[i:advanced]
     */
    public function editAction()
    {
        parent::editAction();
    }
    
    /**
     * Get list of contacts for a specific host template
     *
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/contact
     */
    public function contactForHostTemplateAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $contactList = Contact::getMergedParameters(array('contact_id', 'contact_name', 'contact_email'), array(), -1, 0, null, "ASC", array('host.host_id' => $requestParam['id']), "AND");
        
        $finalContactList = array();
        foreach($contactList as $contact) {
            $finalContactList[] = array(
                "id" => $contact['contact_id'],
                "text" => $contact['contact_name'],
                "theming" => \Centreon\Repository\UserRepository::getUserIcon($contact['contact_name'], $contact['contact_email'])
            );
        }
        
        $router->response()->json($finalContactList);
    }
    
    /**
     * Get list of contact groups for a specific host template
     *
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/contactgroup
     */
    public function contactgroupForHostTemplateAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $contactgroupList = Contactgroup::getMergedParameters(array('cg_id', 'cg_name'), array(), -1, 0, null, "ASC", array('host.host_id' => $requestParam['id']), "AND");
        
        $finalContactgroupList = array();
        foreach($contactgroupList as $contactgroup) {
            $finalContactgroupList[] = array(
                "id" => $contactgroup['cg_id'],
                "text" => $contactgroup['cg_name']
            );
        }
        
        $router->response()->json($finalContactgroupList);
    }
    
    /**
     * Get list of hostgroups for a specific host template
     *
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/hostgroup
     */
    public function hostgroupForHostTemplateAction()
    {
        parent::getRelations(static::$relationMap['host_hostgroups']);
    }
    
    /**
     * Get list of hostcategories for a specific host template
     *
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/hostcategory
     */
    public function hostcategoryForHostTemplateAction()
    {
        parent::getRelations(static::$relationMap['host_hostcategories']);
    }

    /**
     * Get host template for a specific host template
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/hosttemplate
     */
    public function hostTemplateForHostTemplateAction()
    {
        parent::getRelations(static::$relationMap['host_hosttemplates']);
    }

    /**
     * 
     * @method get
     * @route /configuration/hosttemplate/[i:id]/parent
     */
    public function parentForHostTemplateAction()
    {
        $di = \Centreon\Core\Di::getDefault();
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
        foreach($hostparentList as $hostparent) {
            $finalHostList[] = array(
                "id" => $hostparent['host_id'],
                "text" => $hostparent['host_name'],
                "theming" => \Centreon\Repository\HostRepository::getIconImage($hostparent['host_name']).' '.$hostparent['host_name']
            );
        }
        
        $router->response()->json($finalHostList);
    }

    /**
     * 
     * @method get
     * @route /configuration/hosttemplate/[i:id]/child
     */
    public function childForHostTemplateAction()
    {
        $di = \Centreon\Core\Di::getDefault();
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
        foreach($hostchildList as $hostchild) {
            $finalHostList[] = array(
                "id" => $hostchild['host_id'],
                "text" => $hostchild['host_name'],
                "theming" => \Centreon\Repository\HostRepository::getIconImage($hostchild['host_name']).' '.$hostchild['host_name']
            );
        }
        
        $router->response()->json($finalHostList);
    }
    
    /**
     * Get list of Timeperiods for a specific host template
     *
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/checkperiod
     */
    public function checkPeriodForHostTemplateAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $filters = array('host.host_id' => $requestParam['id']);
        $hostList = Host::getList('timeperiod_tp_id', -1, 0, null, "ASC", $filters, "AND");
        
        if (count($hostList) == 0) {
            $router->response()->json(array('id' => null, 'text' => null));
            return;
        }
        
        $filtersTimperiod = array('tp_id' => $hostList[0]['timeperiod_tp_id']);
        $timeperiodList = Timeperiod::getList('tp_id, tp_name', -1, 0, null, "ASC", $filtersTimperiod, "AND");
        
        $finalTimeperiodList = array();
        if (count($timeperiodList) > 0) {
            $finalTimeperiodList["id"] = $timeperiodList[0]['tp_id'];
            $finalTimeperiodList["text"] = $timeperiodList[0]['tp_name'];
        }
        $router->response()->json($finalTimeperiodList);
    }
    
    /**
     * Get list of Timeperiods for a specific host template
     *
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/notificationperiod
     */
    public function notificationPeriodForHostTemplateAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $filters = array('host.host_id' => $requestParam['id']);
        $hostList = Host::getList('timeperiod_tp_id2', -1, 0, null, "ASC", $filters, "AND");
        
        $filtersTimperiod = array('tp_id' => $hostList[0]['timeperiod_tp_id2']);
        $timeperiodList = Timeperiod::getList('tp_id, tp_name', -1, 0, null, "ASC", $filtersTimperiod, "AND");
        
        $finalTimeperiodList = array();
        if (count($timeperiodList) > 0) {
            $finalTimeperiodList["id"] = $timeperiodList[0]['tp_id'];
            $finalTimeperiodList["text"] = $timeperiodList[0]['tp_name'];
        }
        
        $router->response()->json($finalTimeperiodList);
    }
    
    /**
     * Get list of Commands for a specific host template
     *
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/eventhandler
     */
    public function eventHandlerForHostAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $filters = array('host.host_id' => $requestParam['id']);
        $hostList = Host::getList('command_command_id2', -1, 0, null, "ASC", $filters, "AND");
        
        $filtersCommand = array('command_id' => $hostList[0]['command_command_id2']);
        $commandList = Command::getList('command_id, command_name', -1, 0, null, "ASC", $filtersCommand, "AND");
        
        $finalCommandList = array();
        if (count($commandList) > 0) {
            $finalCommandList["id"] = $commandList[0]['command_id'];
            $finalCommandList["text"] = $commandList[0]['command_name'];
        }
        
        $router->response()->json($finalCommandList);
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/hosttemplate/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/hosttemplate/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }
    
    /**
     * Duplicate a host template
     *
     * @method post
     * @route /configuration/hosttemplate/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/hosttemplate/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for host template
     *
     * @method post
     * @route /configuration/hosttemplate/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
}

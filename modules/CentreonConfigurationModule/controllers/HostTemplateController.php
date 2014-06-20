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

use \CentreonConfiguration\Models\Relation\Host\Contact,
    \CentreonConfiguration\Models\Relation\Host\Contactgroup,
    \CentreonConfiguration\Models\Relation\Host\Hostchild,
    \CentreonConfiguration\Models\Relation\Host\Hostparent,
    \CentreonConfiguration\Repository\HostRepository;

class HostTemplateController extends \CentreonConfiguration\Controllers\ObjectAbstract
{
    /**
     *
     * @var string 
     */
    protected $objectDisplayName = 'Hosttemplate';
    
    /**
     *
     * @var string 
     */
    protected $objectName = 'hosttemplate';
    
    /**
     *
     * @var string 
     */
    protected $objectBaseUrl = '/configuration/hosttemplate';
    
    /**
     *
     * @var string 
     */
    protected $objectClass = '\CentreonConfiguration\Models\Hosttemplate';
    
    /**
     *
     * @var string 
     */
    protected $secondaryObjectClass = '\CentreonConfiguration\Models\Hosttemplate';
    
    /**
     *
     * @var type 
     */
    protected $datatableObject = '\CentreonConfiguration\Internal\HostTemplateDatatable';


    /**
     *
     * @var array 
     */
    public static $relationMap = array(
        'host_hostgroups' => '\CentreonConfiguration\Models\Relation\Host\Hostgroup',
        'host_hostcategories' => '\CentreonConfiguration\Models\Relation\Host\Hostcategory',
        'host_parents' => '\CentreonConfiguration\Models\Relation\Host\Hostparent',
        'host_childs' => '\CentreonConfiguration\Models\Relation\Host\Hostchild',
        'host_contacts' => '\CentreonConfiguration\Models\Relation\Host\Contact',
        'host_contactgroups' => '\CentreonConfiguration\Models\Relation\Host\Contactgroup',
        'host_hosttemplates' => '\CentreonConfiguration\Models\Relation\Host\Hosttemplate'
    );
    
    public static $isDisableable = true;

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
        $givenParameters['host_register'] = 0;
        if (!isset($givenParameters['host_alias']) && isset($givenParameters['host_name'])) {
            $givenParameters['host_alias'] = $givenParameters['host_name'];
        }
        parent::createAction();
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
            \CentreonConfiguration\Models\Host::deployServices($givenParameters['object_id']);
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
        $this->tpl->assign('validateUrl', '/configuration/hosttemplate/add');
        parent::addAction();
    }
    
    /**
     * Update a host template
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]
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
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $contactList = Contact::getMergedParameters(
            array('contact_id', 'contact_name', 'contact_email'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('host.host_id' => $requestParam['id']),
            "AND"
        );
        
        $finalContactList = array();
        foreach ($contactList as $contact) {
            $finalContactList[] = array(
                "id" => $contact['contact_id'],
                "text" => $contact['contact_name'],
                "theming" => \Centreon\Repository\UserRepository::getUserIcon(
                    $contact['contact_name'],
                    $contact['contact_email']
                )
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
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $contactgroupList = Contactgroup::getMergedParameters(
            array('cg_id', 'cg_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('host.host_id' => $requestParam['id']),
            "AND"
        );
        
        $finalContactgroupList = array();
        foreach ($contactgroupList as $contactgroup) {
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
                "theming" => \Centreon\Repository\HostRepository::getIconImage(
                    $hostparent['host_name']
                ).' '.$hostparent['host_name']
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
                "theming" => \Centreon\Repository\HostRepository::getIconImage(
                    $hostchild['host_name']
                ).' '.$hostchild['host_name']
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
        parent::getSimpleRelation('timeperiod_tp_id', '\CentreonConfiguration\Models\Timeperiod');
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
        parent::getSimpleRelation('timeperiod_tp_id2', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get check command for a specific host template
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/checkcommand
     */
    public function checkcommandForHostTemplateAction()
    {
        parent::getSimpleRelation('command_command_id', '\CentreonConfiguration\Models\Command');
    }

    /**
     * Get list of Commands for a specific host template
     *
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]/eventhandler
     */
    public function eventHandlerForHostTemplateAction()
    {
        parent::getSimpleRelation('command_command_id2', '\CentreonConfiguration\Models\Command');
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
    
    /**
     * Enable action for host template
     * 
     * @method post
     * @route /configuration/hosttemplate/enable
     */
    public function enableAction()
    {
        parent::enableAction('host_activate');
    }
    
    /**
     * Disable action for host template
     * 
     * @method post
     * @route /configuration/hosttemplate/disable
     */
    public function disableAction()
    {
        parent::disableAction('host_activate');
    }

    /**
     * Display host template configuration in a popin window
     *
     * @method get
     * @route /configuration/hosttemplate/viewconf/[i:id]
     */
    public function displayConfAction()
    {
        $params = $this->getParams();
        $data = HostRepository::getConfigurationData($params['id']);
        list($checkdata, $notifdata) = HostRepository::formatDataForTooltip($data);
        $this->tpl->assign('checkdata', $checkdata);
        $this->tpl->assign('notifdata', $notifdata);
        $this->tpl->display('file:[CentreonConfigurationModule]host_conf_tooltip.tpl');
    }
}

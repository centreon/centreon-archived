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
    \Models\Configuration\Relation\Host\Hostgroup,
    \Models\Configuration\Relation\Host\Hostparent,
    \Models\Configuration\Relation\Host\Hostcategory,
    \Models\Configuration\Relation\Host\Poller,
    \Models\Configuration\Timeperiod,
    \Models\Configuration\Command,
    \Centreon\Core\Form,
    \Centreon\Core\Form\Generator;

class HostController extends \Centreon\Core\Controller
{

    /**
     * List hosts
     *
     * @method get
     * @route /configuration/host
     */
    public function listAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');

        // Load CssFile
        $tpl->addCss('dataTables.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('dataTables-TableTools.css')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('centreon-wizard.css');

        // Load JsFile
        $tpl->addJs('jquery.dataTables.min.js')
            ->addJs('jquery.dataTables.TableTools.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('centreon-wizard.js');
        
        // Display page
        $tpl->assign('objectName', 'Host');
        $tpl->assign('objectAddUrl', '/configuration/host/add');
        $tpl->assign('objectListUrl', '/configuration/host/list');
        $tpl->assign('objectMcFields', '/configuration/host/mc_fields');
        $tpl->display('configuration/list.tpl');
    }
    
    /**
     * 
     * @method get
     * @route /configuration/host/formlist
     */
    public function formListAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        
        $hostObj = new Host();
        $filters = array('host_name' => $requestParams['q'].'%', 'host_register' => '1');
        $hostList = $hostObj->getList('host_id, host_name', -1, 0, null, "ASC", $filters, "AND");
        
        $finalHostList = array();
        foreach($hostList as $host) {
            $finalHostList[] = array(
                "id" => $host['host_id'],
                "text" => $host['host_name'],
                "theming" => \Centreon\Repository\HostRepository::getIconImage($host['host_name']).' '.$host['host_name']
            );
        }
        
        $router->response()->json($finalHostList);
    }

    /**
     * 
     * @method get
     * @route /configuration/host/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(\Centreon\Core\Datatable::getDatas(
            'host',
            $this->getParams('get')
            )
        );
    }
    
    /**
     * Create a new host
     *
     * @method post
     * @route /configuration/host/create
     */
    public function createAction()
    {
        
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
        
        if (!Form::validateSecurity($givenParameters['token'])) {
            echo "fail";
        }
        unset($givenParameters['token']);
        
        foreach ($givenParameters as $paramName=>$paramValue) {
            echo $paramName . " => " . $paramValue . "<br />";
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
        $form = new \Centreon\Core\Form\Wizard('/configuration/host/add', 0, array('id' => 0));
        echo $form->generate();
    }
    
    /**
     * Update a host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]
     */
    public function editAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');
        
        $requestParam = $this->getParams('named');
        $connObj = new Host();
        $currentHostValues = $connObj->getParameters($requestParam['id'], array(
            'host_id',
            'host_name',
            'host_alias',
            'host_address',
            'host_active_checks_enabled',
            'host_passive_checks_enabled',
            'host_obsess_over_host',
            'host_check_freshness',
            'host_freshness_threshold',
            'host_flap_detection_enabled',
            'host_process_perf_data',
            'host_retain_status_information',
            'host_retain_nonstatus_information',
            'host_stalking_options',
            'host_activate',
            'host_comment'
            )
        );
        
        if (isset($currentHostValues['host_activate']) && is_numeric($currentHostValues['host_activate'])) {
            $currentHostValues['host_activate'] = $currentHostValues['host_activate'];
        } else {
            $currentHostValues['host_activate'] = '0';
        }
        
        if (isset($currentHostValues['host_active_checks_enabled']) && is_numeric($currentHostValues['host_active_checks_enabled'])) {
            $currentHostValues['host_active_checks_enabled'] = $currentHostValues['host_active_checks_enabled'];
        } else {
            $currentHostValues['host_active_checks_enabled'] = '2';
        }
        
        if (isset($currentHostValues['host_passive_checks_enabled']) && is_numeric($currentHostValues['host_passive_checks_enabled'])) {
            $currentHostValues['host_passive_checks_enabled'] = $currentHostValues['host_passive_checks_enabled'];
        } else {
            $currentHostValues['host_passive_checks_enabled'] = '2';
        }
        
        $myForm = new Generator('/configuration/host/update', 0, array('id' => $requestParam['id']));
        $myForm->setDefaultValues($currentHostValues);
        $myForm->addHiddenComponent('host_id', $requestParam['id']);
        
        // Display page
        $tpl->assign('pageTitle', "Host");
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('validateUrl', '/configuration/host/update');
        $tpl->display('configuration/edit.tpl');
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
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostContactObj = new Contact();
        $contactList = $hostContactObj->getMergedParameters(array('contact_id', 'contact_name', 'contact_email'), array(), -1, 0, null, "ASC", array('host.host_id' => $requestParam['id']), "AND");
        
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
     * Get list of contact groups for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/contactgroup
     */
    public function contactgroupForHostAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostContactgroupObj = new Contactgroup();
        $contactgroupList = $hostContactgroupObj->getMergedParameters(array('cg_id', 'cg_name'), array(), -1, 0, null, "ASC", array('host.host_id' => $requestParam['id']), "AND");
        
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
     * Get list of hostgroups for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/hostgroup
     */
    public function hostgroupForHostAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostHostgroupObj = new Hostgroup();
        $hostgroupList = $hostHostgroupObj->getMergedParameters(array('hg_id', 'hg_name'), array(), -1, 0, null, "ASC", array('host.host_id' => $requestParam['id']), "AND");
        
        $finalHostgroupList = array();
        foreach($hostgroupList as $hostgroup) {
            $finalHostgroupList[] = array("id" => $hostgroup['hg_id'], "text" => $hostgroup['hg_name']);
        }
        
        $router->response()->json($finalHostgroupList);
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
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostCategoryObj = new Hostcategory();
        $hostcategoryList = $hostCategoryObj->getMergedParameters(array('hc_id', 'hc_name'), array(), -1, 0, null, "ASC", array('host.host_id' => $requestParam['id']), "AND");
        
        $finalHostcategoryList = array();
        foreach($hostcategoryList as $hostcategory) {
            $finalHostcategoryList[] = array("id" => $hostcategory['hc_id'], "text" => $hostcategory['hc_name']);
        }
        
        $router->response()->json($finalHostcategoryList);
    }
    
    /**
     * 
     * @method get
     * @route /configuration/host/[i:id]/parent
     */
    public function parentForHostAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostParentObj = new Hostparent('parent');
        $hostparentList = $hostParentObj->getMergedParameters(
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
     * @route /configuration/host/[i:id]/child
     */
    public function childForHostAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostChildObj = new Hostparent('child');
        $hostchildList = $hostChildObj->getMergedParameters(
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
     * Get list of Timeperiods for a specific host
     *
     *
     * @method get
     * @route /configuration/host/[i:id]/checkperiod
     */
    public function checkPeriodForHostAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostObj = new Host();
        $filters = array('host.host_id' => $requestParam['id']);
        $hostList = $hostObj->getList('timeperiod_tp_id', -1, 0, null, "ASC", $filters, "AND");
        
        $timeperiodObj = new Timeperiod();
        $filtersTimperiod = array('tp_id' => $hostList[0]['timeperiod_tp_id']);
        $timeperiodList = $timeperiodObj->getList('tp_id, tp_name', -1, 0, null, "ASC", $filtersTimperiod, "AND");
        
        $finalTimeperiodList = array(
            "id" => $timeperiodList[0]['tp_id'],
                "text" => $timeperiodList[0]['tp_name']
        );
        
        $router->response()->json($finalTimeperiodList);
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
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostObj = new Host();
        $filters = array('host.host_id' => $requestParam['id']);
        $hostList = $hostObj->getList('timeperiod_tp_id2', -1, 0, null, "ASC", $filters, "AND");
        
        $timeperiodObj = new Timeperiod();
        $filtersTimperiod = array('tp_id' => $hostList[0]['timeperiod_tp_id2']);
        $timeperiodList = $timeperiodObj->getList('tp_id, tp_name', -1, 0, null, "ASC", $filtersTimperiod, "AND");
        
        $finalTimeperiodList = array(
            "id" => $timeperiodList[0]['tp_id'],
                "text" => $timeperiodList[0]['tp_name']
        );
        
        $router->response()->json($finalTimeperiodList);
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
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostObj = new Host();
        $filters = array('host.host_id' => $requestParam['id']);
        $hostList = $hostObj->getList('command_command_id2', -1, 0, null, "ASC", $filters, "AND");
        
        $commandObj = new Command();
        $filtersCommand = array('command_id' => $hostList[0]['command_command_id2']);
        $commandList = $commandObj->getList('command_id, command_name', -1, 0, null, "ASC", $filtersCommand, "AND");
        
        $finalCommandList = array();
        if (count($commandList) > 0) {
            $finalCommandList = array(
                "id" => $commandList[0]['command_id'],
                "text" => $commandList[0]['command_name']
            );
        }
        
        $router->response()->json($finalCommandList);
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/host/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        $dbconn = $di->get('db_centreon');

        $data = array(
            'listMc' => array()
        );

        $stmt = $dbconn->prepare("SELECT f.field_id, f.label
            FROM form_field f, form_massive_change_field_relation mcfr, form_massive_change mc
            WHERE mc.route = :route
                AND mc.massive_change_id = mcfr.massive_change_id
                AND f.field_id = mcfr.field_id");
        $stmt->bindValue(':route', '/configuration/host/mc_fields', \PDO::PARAM_STR);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $data['listMc'][$row['field_id']] = $row['label'];
        }

        $router->response()->json($data);
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/host/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        $dbconn = $di->get('db_centreon');
        $tpl = $di->get('template');
        
        $requestParam = $this->getParams('named');

        $stmt = $dbconn->prepare("SELECT name, label, default_value, attributes, type, help
            FROM form_field
            WHERE field_id = :id");
        $stmt->bindValue(':id', $requestParam['id'], \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $form = new \Centreon\Core\Form('default');
        $form->add($row, array('id' => 0));
        $formElements = $form->toSmarty();
        $tpl->assign('field', $formElements[$row['name']]['html']);
        $tpl->display('tools/mcField.tpl');
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
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $hostPollerObj = new Poller();
        $pollerList = $hostPollerObj->getMergedParameters(array('id', 'name'), array(), -1, 0, null, "ASC", array('host.host_id' => $requestParam['id']), "AND");
        
        $finalPollerList = array();
        if (count($pollerList) > 0) {
            $finalPollerList = array(
                "id" => $pollerList[0]['id'],
                "text" => $pollerList[0]['name']
            );
        }
        
        $router->response()->json($finalPollerList);
    }
}

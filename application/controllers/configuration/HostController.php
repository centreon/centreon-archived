<?php

namespace Controllers\Configuration;

use \Models\Configuration\Host,
    \Models\Configuration\Relation\Host\Contact,
    \Models\Configuration\Relation\Host\Contactgroup,
    \Models\Configuration\Relation\Host\Hostgroup,
    \Models\Configuration\Relation\Host\Hostcategory,
    \Centreon\Core\Form\FormGenerator;

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
            ->addCss('dataTables-TableTools.css');

        // Load JsFile
        $tpl->addJs('jquery.dataTables.min.js')
            ->addJs('jquery.dataTables.TableTools.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js');
        
        // Display page
        $tpl->assign('objectName', 'Host');
        $tpl->assign('objectAddUrl', '/configuration/host/add');
        $tpl->assign('objectListUrl', '/configuration/host/list');
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
        
    }
    
    /**
     * Add a host
     *
     *
     * @method get
     * @route /configuration/host/add
     */
    public function addAction()
    {
        
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
        
        $urlCastParam = array(
            'id' => $requestParam['id']
        );
        $myForm = new FormGenerator('/configuration/host/update', 0, $urlCastParam);
        $myForm->setDefaultValues($currentHostValues);
        $myForm->addHiddenComponent('host_id', $requestParam['id']);
        
        // Load CssFile
        $tpl->addCss('select2.css')
            ->addCss('select2-bootstrap.css');

        // Load JsFile
        $tpl->addJs('jquery.select2/select2.min.js');
        
        // Display page
        $tpl->assign('pageTitle', "Host");
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('firstSection', $myForm->getFirstSection());
        $tpl->assign('select2Call', $myForm->getSelect2Js());
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
}

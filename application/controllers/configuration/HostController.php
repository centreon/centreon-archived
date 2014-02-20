<?php

namespace Controllers\Configuration;

use \Models\Configuration\Host,
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
        
        $myForm = new Generator('/configuration/host/update');
        $myForm->setDefaultValues($currentHostValues);
        $myForm->addHiddenComponent('host_id', $requestParam['id']);
        
        // Display page
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('firstSection', $myForm->getFirstSection());
        $tpl->assign('validateUrl', '/configuration/host/update');
        $tpl->display('configuration/edit.tpl');
    }
}

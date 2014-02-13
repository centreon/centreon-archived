<?php

namespace Controllers\Configuration;

class HostgroupController extends \Centreon\Core\Controller
{

    /**
     * List hostgroups
     *
     * @method get
     * @route /configuration/hostgroup
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
        $tpl->assign('objectName', 'Hostgroup');
        $tpl->assign('objectAddUrl', '/configuration/hostgroup/add');
        $tpl->assign('objectListUrl', '/configuration/hostgroup/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/hostgroup/list
     */
    public function datatableAction()
    {
        echo \Centreon\Core\Datatable::getDatas(
            'hostgroup',
            $this->getParams('get')
        );

    }
    
    /**
     * Create a new hostgroup
     *
     * @method post
     * @route /configuration/hostgroup/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a hostgroup
     *
     *
     * @method put
     * @route /configuration/hostgroup/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a hostgroup
     *
     *
     * @method get
     * @route /configuration/hostgroup/add
     */
    public function addAction()
    {
        
    }
    
    /**
     * Update a hostgroup
     *
     *
     * @method get
     * @route /configuration/hostgroup/[i:id]
     */
    public function editAction()
    {
        
    }
}

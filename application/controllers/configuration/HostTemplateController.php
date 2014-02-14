<?php

namespace Controllers\Configuration;

class HostTemplateController extends \Centreon\Core\Controller
{

    /**
     * List hosttemplates
     *
     * @method get
     * @route /configuration/hosttemplate
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
        $tpl->assign('objectName', 'Hosttemplate');
        $tpl->assign('objectAddUrl', '/configuration/hosttemplate/add');
        $tpl->assign('objectListUrl', '/configuration/hosttemplate/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/hosttemplate/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(\Centreon\Core\Datatable::getDatas(
            'hosttemplate',
            $this->getParams('get')
            )
        );
    }
    
    /**
     * Create a new hosttemplate
     *
     * @method post
     * @route /configuration/hosttemplate/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a hosttemplate
     *
     *
     * @method put
     * @route /configuration/hosttemplate/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a hosttemplate
     *
     *
     * @method get
     * @route /configuration/hosttemplate/add
     */
    public function addAction()
    {
        
    }
    
    /**
     * Update a hosttemplate
     *
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]
     */
    public function editAction()
    {
        
    }
}

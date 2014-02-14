<?php

namespace Controllers\Configuration;

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
     * @method put
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
        
    }
}

<?php

namespace Controllers\Configuration;

class ServiceController extends \Centreon\Core\Controller
{

    /**
     * List services
     *
     * @method get
     * @route /configuration/service
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
        $tpl->assign('objectName', 'Service');
        $tpl->assign('objectAddUrl', '/configuration/service/add');
        $tpl->assign('objectListUrl', '/configuration/service/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/service/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(\Centreon\Core\Datatable::getDatas(
            'service',
            $this->getParams('get')
            )
        );
    }
    
    /**
     * Create a new service
     *
     * @method post
     * @route /configuration/service/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a service
     *
     *
     * @method put
     * @route /configuration/service/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a service
     *
     *
     * @method get
     * @route /configuration/service/add
     */
    public function addAction()
    {
        
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
        
    }
}

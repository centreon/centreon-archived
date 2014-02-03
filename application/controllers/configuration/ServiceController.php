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
            ->addJs('bootstrap-dataTables-paging.js');
        
        // Display page
        $tpl->display('configuration/service/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/service/list
     */
    public function datatableAction()
    {
        echo \Centreon\Core\Datatable::getDatas(
            'service',
            $this->getParams('get')
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
}

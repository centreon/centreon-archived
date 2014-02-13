<?php

namespace Controllers\Configuration;

class ServicetemplateController extends \Centreon\Core\Controller
{

    /**
     * List servicetemplates
     *
     * @method get
     * @route /configuration/servicetemplate
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
        $tpl->assign('objectName', 'Servicetemplate');
        $tpl->assign('objectAddUrl', '/configuration/servicetemplate/add');
        $tpl->assign('objectListUrl', '/configuration/servicetemplate/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/servicetemplate/list
     */
    public function datatableAction()
    {
        echo \Centreon\Core\Datatable::getDatas(
            'servicetemplate',
            $this->getParams('get')
        );

    }
    
    /**
     * Create a new servicetemplate
     *
     * @method post
     * @route /configuration/servicetemplate/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a servicetemplate
     *
     *
     * @method put
     * @route /configuration/servicetemplate/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a service template
     *
     *
     * @method get
     * @route /configuration/servicetemplate/add
     */
    public function addAction()
    {
        
    }
    
    /**
     * Update a service template
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]
     */
    public function editAction()
    {
        
    }
}

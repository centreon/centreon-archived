<?php

namespace Controllers\Configuration;

class HostcategoryController extends \Centreon\Core\Controller
{

    /**
     * List hostcategories
     *
     * @method get
     * @route /configuration/hostcategory
     */
    public function listAction()
    {
        // Init category
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
        $tpl->display('configuration/hostcategory/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/hostcategory/list
     */
    public function datatableAction()
    {
        echo \Centreon\Core\Datatable::getDatas(
            'hostcategory',
            $this->getParams('get')
        );

    }
    
    /**
     * Create a new hostcategory
     *
     * @method post
     * @route /configuration/hostcategory/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a hostcategory
     *
     *
     * @method put
     * @route /configuration/hostcategory/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a hostcategory
     *
     *
     * @method get
     * @route /configuration/hostcategory/add
     */
    public function addAction()
    {
        
    }
    
    /**
     * Update a hostcategory
     *
     *
     * @method get
     * @route /configuration/hostcategory/[i:id]
     */
    public function editAction()
    {
        
    }
}

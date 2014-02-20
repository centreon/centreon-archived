<?php

namespace Controllers\Configuration;

use Models\Configuration\Contactgroup;

class UsergroupController extends \Centreon\Core\Controller
{

    /**
     * List usergroups
     *
     * @method get
     * @route /configuration/usergroup
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
        $tpl->assign('objectName', 'Usergroup');
        $tpl->assign('objectAddUrl', '/configuration/usergroup/add');
        $tpl->assign('objectListUrl', '/configuration/usergroup/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/usergroup/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(\Centreon\Core\Datatable::getDatas(
            'usergroup',
            $this->getParams('get')
            )
        );
    }
    
    /**
     * 
     * @method get
     * @route /configuration/usergroup/formlist
     */
    public function formListAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        
        $contactgroupObj = new Contactgroup();
        $filters = array('cg_name' => $requestParams['q'].'%');
        $contactgroupList = $contactgroupObj->getList('cg_id, cg_name', -1, 0, null, "ASC", $filters, "AND");
        
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
     * Create a new usergroup
     *
     * @method post
     * @route /configuration/usergroup/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a usergroup
     *
     *
     * @method put
     * @route /configuration/usergroup/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a usergroup
     *
     *
     * @method get
     * @route /configuration/usergroup/add
     */
    public function addAction()
    {
        
    }
    
    /**
     * Update a usergroup
     *
     *
     * @method get
     * @route /configuration/usergroup/[i:id]
     */
    public function editAction()
    {
        
    }
}

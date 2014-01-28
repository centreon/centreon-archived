<?php

namespace Controllers\Configuration;

class CommandController extends \Centreon\Core\Controller
{

    /**
     * List commands
     *
     * @method get
     * @route /configuration/command
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
        $tpl->display('configuration/command/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/command/list
     */
    public function datatableAction()
    {
        echo \Centreon\Core\Datatable::getDatas(
            'command',
            $this->getParams('get')
        );

    }
    
    /**
     * Create a new command
     *
     * @method post
     * @route /configuration/command/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a command
     *
     *
     * @method put
     * @route /configuration/command/update
     */
    public function updateAction()
    {
        
    }
}

<?php

namespace Controllers\Configuration;

class CommandController extends \Centreon\Core\Controller
{

    /**
     * List commands
     *
     * @method get
     * @route /configuration/command/list
     */
    public function listAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');

        // Load CssFile
        $tpl->addCss('bootstrap.css')
            ->addCss('dataTables.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('dataTables-TableTools.css')
            ->addCss('centreon.css');

        // Load JsFile
        $tpl->addJs('jquery.min.js')
            ->addJs('jquery.dataTables.min.js')
            ->addJs('jquery.dataTables.TableTools.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('bootstrap.min.js')
            ->addJs('centreon.functions.js');
        
        // Display page
        $tpl->display('configuration/command/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/command/datatable
     */
    public function datatableAction()
    {
        echo \Centreon\Core\Datatable::getDatas(
                'command',
                array('fields' => array('command_name', 'command_line', 'command_type'),
                'sEcho' => 1
            )
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

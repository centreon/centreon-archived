<?php

namespace Controllers\Configuration;

use \Centreon\Core\Form;

class ConnectorController extends \Centreon\Core\Controller
{

    /**
     * List connectors
     *
     * @method get
     * @route /configuration/connector
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
        $tpl->display('configuration/connector/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/connector/list
     */
    public function datatableAction()
    {
        echo \Centreon\Core\Datatable::getDatas(
            'connector',
            $this->getParams('get')
        );

    }
    
    /**
     * Create a new connector
     *
     * @method post
     * @route /configuration/connector/create
     */
    public function createAction()
    {
        var_dump($this->getParams('get'));
    }

    /**
     * Update a connector
     *
     *
     * @method put
     * @route /configuration/connector/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Update a connector
     *
     *
     * @method get
     * @route /configuration/connector/[i:id]
     */
    public function editAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');
        
        $form = new Form('connectorForm');
        $form->addText('name', _('Name'));
        $form->addText('description', _('Description'));
        $form->addTextarea('command_line', _('Commande Line'));
        
        $radios['list'] = array(
          array(
              'name' => 'Enabled',
              'label' => 'Enabled',
              'value' => '1'
          ),
          array(
              'name' => 'Disabled',
              'label' => 'Disabled',
              'value' => '0'
          )
        );
        $form->addRadio('status', _("Status"), 'status', '&nbsp;', $radios);
        
        $form->add('save_form', 'submit' , _("Save"), array("onClick" => "validForm();"));
        $tpl->assign('form', $form->toSmarty());
        
        // Display page
        $tpl->display('configuration/connector/edit.tpl');
    }
}

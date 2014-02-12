<?php

namespace Controllers\Configuration;

use \Models\Configuration\Connector,
    \Centreon\Core\Form,
    \Centreon\Core\Form\FormGenerator;

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
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js');
        
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
        var_dump($this->getParams());
    }

    /**
     * Update a connector
     *
     *
     * @method post
     * @route /configuration/connector/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        
        if (Form::validateSecurity($givenParameters['token'])) {
            $connector = array(
                'name' => $givenParameters['name'],
                'description' => $givenParameters['description'],
                'command_line' => $givenParameters['command_line'],
                'enabled' => $givenParameters['enabled'],
            );

            //$connObj = new Connector();
            $connObj = new \Models\Configuration\Connector();
            try {
                $connObj->update($givenParameters['id'], $connector);
            } catch (Exception $e) {
                echo "fail";
            }
            echo 'success';
        } else {
            echo "fail";
        }
    }
    
    /**
     * Add a connector
     *
     *
     * @method get
     * @route /configuration/connector/add
     */
    public function addAction()
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
        
        $requestParam = $this->getParams('named');
        $connObj = new Connector();
        $currentConnectorValues = $connObj->getParameters($requestParam['id'], array(
            'id',
            'name',
            'description',
            'command_line',
            'enabled'
            )
        );
        
        if (isset($currentConnectorValues['enabled']) && is_numeric($currentConnectorValues['enabled'])) {
            $currentConnectorValues['enabled'] = $currentConnectorValues['enabled'];
        } else {
            $currentConnectorValues['enabled'] = '0';
        }
        
        $myForm = new FormGenerator("/centreon-devel/configuration/connector/update");
        $myForm->setDefaultValues($currentConnectorValues);
        $myForm->addHiddenComponent('id', $requestParam['id']);
        
        // Display page
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('formValidate', $myForm->generateSubmitValidator());
        $tpl->display('configuration/connector/edit.tpl');
    }
}

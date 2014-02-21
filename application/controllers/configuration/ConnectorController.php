<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace Controllers\Configuration;

use \Models\Configuration\Connector,
    \Centreon\Core\Form,
    \Centreon\Core\Form\Generator;

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
        $tpl->assign('objectName', 'Connector');
        $tpl->assign('objectAddUrl', '/configuration/connector/add');
        $tpl->assign('objectListUrl', '/configuration/connector/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/connector/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(\Centreon\Core\Datatable::getDatas(
            'connector',
            $this->getParams('get')
            )
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
        
        $myForm = new Generator("/configuration/connector/update");
        $myForm->setDefaultValues($currentConnectorValues);
        $myForm->addHiddenComponent('id', $requestParam['id']);
        
        // Display page
        $tpl->assign('pageTitle', "Connector");
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('validateUrl', '/configuration/connector/update');
        $tpl->display('configuration/edit.tpl');
    }
}

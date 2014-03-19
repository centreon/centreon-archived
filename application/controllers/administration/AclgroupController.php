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


namespace Controllers\Administration;

use \Models\Configuration\Acl\Group,
    \Centreon\Core\Form,
    \Centreon\Core\Form\Generator;

class AclgroupController extends \Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'AclGroup';
    protected $objectName = 'aclgroup';
    protected $objectBaseUrl = '/administration/aclgroup';
    protected $objectClass = '\Models\Configuration\Acl\Group';
    
    /**
     * List aclgroups
     *
     * @method get
     * @route /administration/aclgroup
     */
    public function listAction()
    {
        parent::listAction();
        /*
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
        $tpl->assign('objectName', 'aclgroup');
        $tpl->assign('objectAddUrl', '/administration/aclgroup/add');
        $tpl->assign('objectListUrl', '/administration/aclgroup/list');
        $tpl->display('configuration/list.tpl');*/
    }

    /**
     * 
     * @method get
     * @route /administration/aclgroup/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(
            \Centreon\Core\Datatable::getDatas(
                'aclgroup',
                $this->getParams('get')
            )
        );
    }
    
    /**
     * Create a new ACL group
     *
     * @method post
     * @route /administration/aclgroup/create
     */
    public function createAction()
    {
        var_dump($this->getParams());
    }

    /**
     * Update an ACL group
     *
     *
     * @method post
     * @route /administration/aclgroup/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        
        if (Form::validateSecurity($givenParameters['token'])) {
            $aclgroup = array(
                'acl_group_name' => $givenParameters['acl_group_name'],
                'acl_group_alias' => $givenParameters['acl_group_alias'],
                'acl_group_activate' => $givenParameters['acl_group_activate'],
            );
            
            $aclgroupObj = new \Models\Configuration\Acl\Group();
            try {
                $aclgroupObj->update($givenParameters['acl_group_id'], $aclgroup);
            } catch (Exception $e) {
                echo "fail";
            }
            echo 'success';
        } else {
            echo "fail";
        }
    }
    
    /**
     * Add a aclgroup
     *
     *
     * @method get
     * @route /administration/aclgroup/add
     */
    public function addAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');
        
        $form = new Form('aclgroupForm');
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
        $tpl->display('configuration/aclgroup/edit.tpl');
    }
    
    /**
     * Update a aclgroup
     *
     *
     * @method get
     * @route /administration/aclgroup/[i:id]/[i:advanced]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Retrieve list of acl groups for a form
     *
     * @method get
     * @route /administration/aclgroup/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }
    
    /**
     * Duplicate action for aclgroup
     *
     * @method post
     * @route /administration/aclgroup/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }
    
    /**
     * Massive Change action for aclgroup
     *
     * @method post
     * @route /administration/aclgroup/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }
    
    /**
     * MC Field action for aclgroup
     *
     * @method post
     * @route /administration/aclgroup/mc_fields
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }
    
    /**
     * Delete action for aclgroup
     *
     * @method post
     * @route /administration/aclgroup/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
}

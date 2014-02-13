<?php

namespace Controllers\Administration;

use \Models\Configuration\Acl\Group,
    \Centreon\Core\Form,
    \Centreon\Core\Form\FormGenerator;

class AclgroupController extends \Centreon\Core\Controller
{

    /**
     * List aclgroups
     *
     * @method get
     * @route /administration/aclgroup
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
        $tpl->assign('objectName', 'aclgroup');
        $tpl->assign('objectAddUrl', '/administration/aclgroup/add');
        $tpl->assign('objectListUrl', '/administration/aclgroup/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /administration/aclgroup/list
     */
    public function datatableAction()
    {
        echo \Centreon\Core\Datatable::getDatas(
            'aclgroup',
            $this->getParams('get')
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
                'name' => $givenParameters['name'],
                'description' => $givenParameters['description'],
                'command_line' => $givenParameters['command_line'],
                'enabled' => $givenParameters['enabled'],
            );
            
            $connObj = new \Models\Configuration\aclgroup();
            try {
                $connObj->update($givenParameters['id'], $aclgroup);
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
     * @route /administration/aclgroup/[i:id]
     */
    public function editAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');
        
        $requestParam = $this->getParams('named');
        $aclgroupObj = new \Models\Configuration\Acl\Group();
        $currentaclgroupValues = $aclgroupObj->getParameters($requestParam['id'], array(
            'acl_group_id',
            'acl_group_name',
            'acl_group_alias',
            'acl_group_activate'
            )
        );

        if (!isset($currentaclgroupValues['acl_group_activate']) || !is_numeric($currentaclgroupValues['acl_group_activate'])) {
            $currentaclgroupValues['enabled'] = '0';
        }
        
        $myForm = new FormGenerator("/configuration/aclgroup/update");
        $myForm->setDefaultValues($currentaclgroupValues);
        $myForm->addHiddenComponent('id', $requestParam['id']);
        
        // Display page
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('validateUrl', '/configuration/aclgroup/update');
        $tpl->display('configuration/edit.tpl');
    }
}

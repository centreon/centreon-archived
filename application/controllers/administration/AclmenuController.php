<?php

namespace Controllers\Administration;

use \Models\Configuration\Acl\Menu,
    \Centreon\Core\Form,
    \Centreon\Core\Form\Generator;

class AclmenuController extends \Centreon\Core\Controller
{

    /**
     * List aclmenu
     *
     * @method get
     * @route /administration/aclmenu
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
        $tpl->assign('objectName', 'aclmenu');
        $tpl->assign('objectAddUrl', '/administration/aclmenu/add');
        $tpl->assign('objectListUrl', '/administration/aclmenu/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /administration/aclmenu/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(
            \Centreon\Core\Datatable::getDatas(
                'aclmenu',
                $this->getParams('get')
            )
        );
    }
    
    /**
     * Create a new ACL menu
     *
     * @method post
     * @route /administration/aclmenu/create
     */
    public function createAction()
    {
        var_dump($this->getParams());
    }

    /**
     * Update an ACL menu
     *
     *
     * @method post
     * @route /administration/aclmenu/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        
        if (Form::validateSecurity($givenParameters['token'])) {
            $aclmenu = array(
                'name' => $givenParameters['name'],
                'description' => $givenParameters['description'],
                'enabled' => $givenParameters['enabled'],
            );
            
            $aclmenuObj = new \Models\Configuration\Acl\Menu();
            try {
                $aclmenuObj->update($givenParameters['acl_menu_id'], $aclmenu);
            } catch (Exception $e) {
                echo "fail";
            }
            echo 'success';
        } else {
            echo "fail";
        }
    }
    
    /**
     * Add a aclmenu
     *
     *
     * @method get
     * @route /administration/aclmenu/add
     */
    public function addAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');
        
        $form = new Form('aclmenuForm');
        $form->addText('name', _('Name'));
        $form->addText('description', _('Description'));
        
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
        $form->addRadio('enabled', _("Status"), 'status', '&nbsp;', $radios);
        
        $form->add('save_form', 'submit' , _("Save"), array("onClick" => "validForm();"));
        $tpl->assign('form', $form->toSmarty());
        
        // Display page
        $tpl->display('administration/aclmenu/edit.tpl');
    }
    
    /**
     * Update a aclmenu
     *
     *
     * @method get
     * @route /administration/aclmenu/[i:id]
     */
    public function editAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');
        
        $requestParam = $this->getParams('named');
        $aclmenuObj = new \Models\Configuration\Acl\Menu();
        $currentaclmenuValues = $aclmenuObj->getParameters($requestParam['id'], array(
            'acl_menu_id',
            'name',
            'description',
            'enabled'
            )
        );

        if (!isset($currentaclmenuValues['enabled']) || !is_numeric($currentaclmenuValues['enabled'])) {
            $currentaclmenuValues['enabled'] = '0';
        }
        
        $myForm = new Generator("/administration/aclmenu/update");
        $myForm->setDefaultValues($currentaclmenuValues);
        $myForm->addHiddenComponent('acl_menu_id', $requestParam['id']);
        
        // Display page
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('validateUrl', '/administration/aclmenu/update');
        $tpl->display('configuration/edit.tpl');
    }
}

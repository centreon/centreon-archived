<?php

namespace Controllers\Configuration;

use \Models\Configuration\Servicecategory,
    \Centreon\Core\Form,
    \Centreon\Core\Form\FormGenerator;

class ServicecategoryController extends \Centreon\Core\Controller
{

    /**
     * List servicecategories
     *
     * @method get
     * @route /configuration/servicecategory
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
        $tpl->assign('objectName', 'Servicecategory');
        $tpl->assign('objectAddUrl', '/configuration/servicecategory/add');
        $tpl->assign('objectListUrl', '/configuration/servicecategory/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/servicecategory/list
     */
    public function datatableAction()
    {
        echo \Centreon\Core\Datatable::getDatas(
            'servicecategory',
            $this->getParams('get')
        );

    }
    
    /**
     * Create a new servicecategory
     *
     * @method post
     * @route /configuration/servicecategory/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a servicecategory
     *
     *
     * @method put
     * @route /configuration/servicecategory/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a servicecategory
     *
     *
     * @method get
     * @route /configuration/servicecategory/add
     */
    public function addAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');
        
        $form = new Form('servicecategoryForm');
        $form->addText('name', _('Service Category Name'));
        $form->addText('description', _('Service Category Description'));
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
        $form->addRadio('servicecategory_status', _("Status"), 'servicecategory_type', '&nbsp;', $radios);
        $form->add('save_form', 'submit' , _("Save"), array("onClick" => "validForm();"));
        $tpl->assign('form', $form->toSmarty());
        
        // Display page
        $tpl->display('configuration/servicecategory/edit.tpl');
    }
    
    /**
     * Update a servicecategory
     *
     *
     * @method get
     * @route /configuration/servicecategory/[i:id]
     */
    public function editAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');
        
        $requestParam = $this->getParams('named');
        $scObj = new Servicecategory();
        $currentServicecategoryValues = $scObj->getParameters($requestParam['id'], array(
            'sc_id',
            'sc_name',
            'sc_description',
            'sc_activate'
            )
        );
        
        if (isset($currentServicecategoryValues['sc_activate']) && is_numeric($currentServicecategoryValues['sc_activate'])) {
            $currentServicecategoryValues['sc_activate'] = $currentServicecategoryValues['sc_activate'];
        } else {
            $currentServicecategoryValues['sc_activate'] = '0';
        }
        
        $myForm = new FormGenerator("/configuration/servicecategory/update");
        $myForm->setDefaultValues($currentServicecategoryValues);
        $myForm->addHiddenComponent('sc_id', $requestParam['id']);
        
        // Display page
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('validateUrl', '/configuration/connector/update');
        $tpl->display('configuration/edit.tpl');
    }
}

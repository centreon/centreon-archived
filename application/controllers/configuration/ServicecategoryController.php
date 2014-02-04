<?php

namespace Controllers\Configuration;

use \Centreon\Core\Form;

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
            ->addJs('bootstrap-dataTables-paging.js');
        
        // Display page
        $tpl->display('configuration/servicecategory/list.tpl');
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
}

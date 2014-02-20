<?php

namespace Controllers\Configuration;

use \Models\Configuration\Servicecategory,
    \Centreon\Core\Form,
    \Centreon\Core\Form\Generator;

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
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(\Centreon\Core\Datatable::getDatas(
            'servicecategory',
            $this->getParams('get')
            )
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
     * @method post
     * @route /configuration/servicecategory/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        
        if (Form::validateSecurity($givenParameters['token'])) {
            $servicecategory = array(
                'sc_name' => $givenParameters['sc_name'],
                'sc_description' => $givenParameters['sc_description'],
                'sc_activate' => $givenParameters['sc_activate'],
            );
            
            $connObj = new \Models\Configuration\Servicecategory();
            try {
                $connObj->update($givenParameters['sc_id'], $servicecategory);
            } catch (Exception $e) {
                echo "fail";
            }
            echo 'success';
        } else {
            echo "fail";
        }
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
        
        $myForm = new Generator("/configuration/servicecategory/update");
        $myForm->setDefaultValues($currentServicecategoryValues);
        $myForm->addHiddenComponent('sc_id', $requestParam['id']);
        
        // Display page
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('formRedirect', $myForm->getRedirect());
        $tpl->assign('formRedirectRoute', $myForm->getRedirectRoute());
        $tpl->assign('validateUrl', '/configuration/servicecategory/update');
        $tpl->display('configuration/edit.tpl');
    }
}

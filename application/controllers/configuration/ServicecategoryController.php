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

use \Models\Configuration\Servicecategory,
    \Centreon\Core\Form,
    \Centreon\Core\Form\Generator;

class ServicecategoryController extends ObjectAbstract
{
    protected $objectDisplayName = 'Servicecategory';
    protected $objectName = 'servicecategory';
    protected $objectBaseUrl = '/configuration/servicecategory';
    protected $objectClass = '\Models\Configuration\Servicecategory';

    /**
     * List servicecategories
     *
     * @method get
     * @route /configuration/servicecategory
     */
    public function listAction()
    {
        parent::listAction();
    }

    public function formListAction()
    {
    }

    /**
     * 
     * @method get
     * @route /configuration/servicecategory/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
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
        parent::addAction();
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
        $tpl->assign('pageTitle', "Service Category");
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('formRedirect', $myForm->getRedirect());
        $tpl->assign('formRedirectRoute', $myForm->getRedirectRoute());
        $tpl->assign('validateUrl', '/configuration/servicecategory/update');
        $tpl->display('configuration/edit.tpl');
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/servicecategory/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/servicecategory/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate a hosts
     *
     * @method POST
     * @route /configuration/servicecategory/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/servicecategory/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for servicecategory
     *
     * @method post
     * @route /configuration/servicecategory/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
}

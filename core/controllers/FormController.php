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
 *
 */

namespace Centreon\Controllers;

use Centreon\Internal\Form;
use Centreon\Internal\Form\Wizard;
use Centreon\Internal\Form\Generator;
use Centreon\Internal\Di;
use Centreon\Internal\Exception;

/**
 * Abstact class for configuration controller
 *
 * @version 3.0.0
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 */
abstract class FormController extends ListController
{
    /**
     * 
     * @param type $request
     * @throws Exception
     */
    public function __construct($request)
    {
        parent::__construct($request);
        if (is_null($this->repository)) {
            throw new Exception('Repository unspecified');
        }
        $repository = $this->repository;
        $repository::setRelationMap(static::$relationMap);
        $repository::setObjectName($this->objectName);
        $repository::setObjectClass($this->objectClass);
        if (!empty($this->secondaryObjectClass)) {
            $repository::setSecondaryObjectClass($this->secondaryObjectClass);
        }
        $this->objectBaseUrl = '/' . static::$moduleShortName . '/' . $this->objectName;
    }

    /**
     * Action for getting list of objects
     *
     * JSON response
     */
    public function formListAction()
    {
        $requestParams = $this->getParams('get');
        $repository = $this->repository;
        $list = $repository::getFormList($requestParams['q']);
        $this->router->response()->json($list);
    }
    
    /**
     * 
     */
    public function editAction($additionnalParamsForSmarty = array())
    {
        $router = Di::getDefault()->get('router');
        $requestParam = $this->getParams('named');
        $objectFormUpdateUrl = $this->objectBaseUrl.'/update';
        $inheritanceUrl = null;
        if (false === is_null($this->inheritanceUrl)) {
            $inheritanceUrl = $router->getPathFor(
                $this->inheritanceUrl,
                array('id' => $requestParam['id'])
            );
        }
        
        $myForm = new Generator($objectFormUpdateUrl, array('id' => $requestParam['id']));
        $myForm->addHiddenComponent('object_id', $requestParam['id']);
        $myForm->addHiddenComponent('object', $this->objectName);
        
        // get object Current Values
        $myForm->setDefaultValues($this->objectClass, $requestParam['id']);
        
        $formModeUrl = Di::getDefault()
                        ->get('router')
                        ->getPathFor(
                            $this->objectBaseUrl.'/[i:id]',
                            array(
                                'id' => $requestParam['id']
                            )
                        );
        
        // Display page
        $this->tpl->assign('pageTitle', $this->objectDisplayName);
        $this->tpl->assign('form', $myForm->generate());
        $this->tpl->assign('advanced', $requestParam['advanced']);
        $this->tpl->assign('formModeUrl', $formModeUrl);
        $this->tpl->assign('formName', $myForm->getName());
        $this->tpl->assign('validateUrl', $objectFormUpdateUrl);
        
        foreach ($additionnalParamsForSmarty as $smartyVarName => $smartyVarValue) {
            $this->tpl->assign($smartyVarName, $smartyVarValue);
        }
        
        $this->tpl->assign('inheritanceUrl', $inheritanceUrl);
        
        if (isset($this->inheritanceTmplUrl)) {
            $this->tpl->assign(
                'inheritanceTmplUrl',
                $router->getPathFor(
                    $this->inheritanceTmplUrl
                )
            );
        }
        if (isset($this->tmplField)) {
            $this->tpl->assign('tmplField', $this->tmplField);
        }
        
        $this->tpl->display('file:[CentreonConfigurationModule]edit.tpl');
    }
    
    /**
     * Generic update function
     *
     */
    public function updateAction()
    {
        $givenParameters = clone $this->getParams('post');
        $updateSuccessful = true;
        $updateErrorMessage = '';
        
        $validationResult = Form::validate("form", $this->getUri(), static::$moduleName, $givenParameters);
        if ($validationResult['success']) {
            $repository = $this->repository;
            try {
                $repository::update($givenParameters);
            } catch (Exception $e) {
                $updateSuccessful = false;
                $updateErrorMessage = $e->getMessage();
            }
        } else {
            $updateSuccessful = false;
            $updateErrorMessage = $validationResult['error'];
        }
        
        $this->router = Di::getDefault()->get('router');
        if ($updateSuccessful) {
            unset($_SESSION['form_token']);
            unset($_SESSION['form_token_time']);
            $this->router->response()->json(array('success' => true));
        } else {
            $this->router->response()->json(array('success' => false,'error' => $updateErrorMessage));
        }
    }
}

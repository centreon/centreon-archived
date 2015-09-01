<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */

namespace Centreon\Controllers;

use Centreon\Internal\Form\Generator\Web\Full as WebFormGenerator;
use Centreon\Internal\Form\Validators\Validator\Validator;
use Centreon\Internal\Di;
use Centreon\Internal\Exception;

/**
 * Abstact class for configuration controller
 *
 * @version 3.0.0
 * @author Maximilien Bersoult <mbersoult@centreon.com>
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
        $repository::setObjectName(static::$objectName);
        $repository::setObjectClass($this->objectClass);
        if (!empty($this->secondaryObjectClass)) {
            $repository::setSecondaryObjectClass($this->secondaryObjectClass);
        }
        if (is_null($this->objectBaseUrl) || $this->objectBaseUrl === '') {
            $this->objectBaseUrl = '/' . static::$moduleShortName . '/' . static::$objectName;
        }
    }

    /**
     * Action for getting list of objects
     *
     * JSON response
     *
     * @method get
     * @route /{object}/formlist/[i:id]?
     */
    public function formListAction()
    {
        $requestParams = $this->getParams('get');
        $namedParams = $this->getParams('named');
        $repository = $this->repository;

        $objectId = null;
        if (isset($namedParams['id'])) {
            $objectId = $namedParams['id'];
        }
        
        $q = $requestParams['q'];
        unset($requestParams['q']);
        $list = $repository::getFormList($q, $objectId, $requestParams);
        $this->router->response()->json($list);
    }
    
    /**
     * 
     * @method get
     * @route /{object}/[i:id]
     */
    public function editAction($additionnalParamsForSmarty = array(), $defaultValues = array())
    {
        $requestParam = $this->getParams('named');
        $objectFormUpdateUrl = $this->objectBaseUrl.'/update';
        $inheritanceUrl = null;
        if (false === is_null($this->inheritanceUrl)) {
            $inheritanceUrl = $this->router->getPathFor(
                $this->inheritanceUrl,
                array('id' => $requestParam['id'])
            );
        }
        
        $myForm = new WebFormGenerator($objectFormUpdateUrl, array('id' => $requestParam['id'], 'objectName'=> static::$objectName));
        $myForm->getFormFromDatabase();
        $myForm->addHiddenComponent('object_id', $requestParam['id']);
        $myForm->addHiddenComponent('object', static::$objectName);
        
        // set specific object current values
        $myForm->setDefaultValues($defaultValues, $requestParam['id']);

        // set object current values
        $myForm->setDefaultValues($this->objectClass, $requestParam['id']);
        
        $formModeUrl = $this->router->getPathFor(
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
        
        if (isset($this->inheritanceTagsUrl) && false === is_null($this->inheritanceTagsUrl)) {
            $inheritanceTagsUrl = $this->router->getPathFor(
                $this->inheritanceTagsUrl,
                array('id' => $requestParam['id'])
            );
            $this->tpl->assign('inheritanceTagsUrl', $inheritanceTagsUrl);
        }
        
        if (isset($this->inheritanceTmplUrl)) {
            $this->tpl->assign(
                'inheritanceTmplUrl',
                $this->router->getPathFor(
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
     * @method post
     * @route /{object}/update
     */
    public function updateAction()
    {
        $givenParameters = clone $this->getParams('post');

        /* Convert array parameters */
        foreach ($givenParameters as $key => $value) {
            if (is_array($value)) {
                $givenParameters[$key] = join(',', $value);
            }
        }

        try {
            $repository = $this->repository;
            $repository::update($givenParameters, 'form', $this->getUri());
            
            unset($_SESSION['form_token']);
            unset($_SESSION['form_token_time']);
            $this->router->response()->json(array('success' => true));
        } catch (\Centreon\Internal\Exception $e) {
            $updateErrorMessage = $e->getMessage();
            $this->router->response()->json(array('success' => false,'error' => $updateErrorMessage));
        }
    }
}

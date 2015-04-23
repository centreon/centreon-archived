<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
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
        $list = $repository::getFormList($requestParams['q'], $objectId);
        $this->router->response()->json($list);
    }
    
    /**
     * 
     * @method get
     * @route /{object}/[i:id]
     */
    public function editAction($additionnalParamsForSmarty = array())
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
        
        // get object Current Values
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

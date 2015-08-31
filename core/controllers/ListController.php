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

use Centreon\Internal\Form;
use Centreon\Internal\Form\Generator\Web\Wizard;
use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use Centreon\Internal\Controller;
use Centreon\Internal\Module\Informations;
use Centreon\Internal\Utils\String;

/**
 * Abstact class for configuration controller
 *
 * @version 3.0.0
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 */
abstract class ListController extends Controller
{
    /**
     *
     * @var type 
     */
    public static $moduleName = '';
    
    /**
     *
     * @var type 
     */
    public static $moduleShortName = '';

    /**
     *
     * @var string
     */
    public static $enableDisableFieldName = ''; 

    /**
     *
     * @var type 
     */
    public static $objectName = '';
    
    /**
     *
     * @var type 
     */
    public static $isDisableable = false;

    /**
     *
     * @var type
     */
    public static $displaySearchBar = true;

    /**
     *
     * @var type 
     */
    protected $repository = null;

    /**
     *
     * @var type 
     */
    protected $inheritanceUrl = null;

    /**
     *
     * @var type 
     */
    protected $objectBaseUrl = ''; 

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

        $rc = new \ReflectionClass(get_class($this));
        static::$moduleName = Informations::getModuleFromPath($rc->getFileName());
        static::$moduleShortName = Informations::getModuleSlugName(static::$moduleName);

        if ($this->objectBaseUrl === '' || is_null($this->objectBaseUrl)) {
            $this->objectBaseUrl = '/' . static::$moduleShortName . '/' . static::$objectName;
        }
    }

    /**
     * List view for object
     *
     * @method get
     * @route /{object}
     */
    public function listAction()
    {
        // Load CssFile
        $this->tpl->addCss('jquery.fileupload.css');

        /* Load CssFile */
        $this->tpl->addCss('dataTables.tableTools.min.css')
            ->addCss('dataTables.colVis.min.css')
            ->addCss('dataTables.colReorder.min.css')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('centreon-wizard.css');

        /* Load JsFile */
        $this->tpl->addJs('jquery.dataTables.min.js')
            ->addJs('dataTables.tableTools.min.js')
            ->addJs('dataTables.colVis.min.js')
            ->addJs('hogan-3.0.0.min.js')
            ->addJs('sideSlide.plugin.js')

            ->addJs('dataTables.colReorder.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('dataTables.bootstrap.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validation/jquery.validate.min.js')
            ->addJs('jquery.validation/additional-methods.min.js')
            ->addJs('centreon.search.js')
            ->addJs('centreon-clone.js')
            ->addJs('tmpl.min.js')
            ->addJs('load-image.min.js')
            ->addJs('canvas-to-blob.min.js')
            ->addJs('jquery.fileupload.js')
            ->addJs('jquery.fileupload-process.js')
            ->addJs('jquery.fileupload-image.js')
            ->addJs('jquery.fileupload-validate.js')
            ->addJs('jquery.fileupload-ui.js')
            ->addJs('bootstrap3-typeahead.js')
            ->addJs('centreon-wizard.js')
            ->addJs('moment-with-locales.js')
            ->addJs('moment-timezone-with-data.min.js');
        
        
        /* Display variable */
        $this->tpl->assign('objectDisplayName', $this->objectDisplayName);
        $this->tpl->assign('objectName', static::$objectName);
        $this->tpl->assign('datatableObject', $this->datatableObject);
        $this->tpl->assign('moduleName', static::$moduleName);
        $this->tpl->assign('objectAddUrl', $this->objectBaseUrl . '/add');
        $this->tpl->assign('objectListUrl', $this->objectBaseUrl . '/list');
        $this->tpl->assign('objectMcUrl', $this->objectBaseUrl . '/massive_change');
        $this->tpl->assign('objectMcFieldsUrl', $this->objectBaseUrl . '/mc_fields');
        $this->tpl->assign('isDisableable', static::$isDisableable);
        $this->tpl->assign('displaySearchBar', static::$displaySearchBar);
        if (static::$isDisableable) {
            $this->tpl->assign('objectEnableUrl', $this->objectBaseUrl . '/enable');
            $this->tpl->assign('objectDisableUrl', $this->objectBaseUrl . '/disable');
        }
        $this->tpl->assign('objectDuplicateUrl', $this->objectBaseUrl . '/duplicate');
        $this->tpl->assign('objectDeleteUrl', $this->objectBaseUrl . '/delete');
        $this->tpl->display('file:[CentreonMainModule]list.tpl');
    }
    
    /**
     * Get wizard for add a object
     *
     * Response HTML
     *
     * @method get
     * @route /{object}/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', $this->objectBaseUrl . "/add");
        $form = new Wizard($this->objectBaseUrl . '/add', array('id' => 0));
        $form->getFormFromDatabase();
        $form->addHiddenComponent('object', static::$objectName);
        $form->addHiddenComponent('module', static::$moduleName);
        $this->tpl->assign('formName', $form->getName());
        $formGen = str_replace(
            array('alertMessage', 'alertClose'),
            array('alertModalMessage', 'alertModalClose'),
            $form->generate()
        );
        echo $formGen;
    }
    
    /**
     * Generic create action
     * 
     * @param boolean $sendResponse
     * @method post
     * @route /{object}/add
     * @return array
     */
    public function createAction($sendResponse = true)
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
            $id = $repository::create($givenParameters, 'wizard', $this->getUri());

            unset($_SESSION['form_token']);
            unset($_SESSION['form_token_time']);
            if ($sendResponse) {
                $this->router->response()->json(array('success' => true));
            } else {
                return $id;
            }
        } catch (\Centreon\Internal\Exception $e) {
            $updateErrorMessage = $e->getMessage();
            $this->router->response()->json(array('success' => false,'error' => $updateErrorMessage));
        }
    }
    
    /**
     * Delete a object
     *
     * Response JSON
     *
     * @method post
     * @route /{object}/delete
     */
    public function deleteAction()
    {
        $deleteSuccess = true;
        $errorMessage = '';
        
        try {
            $params = $this->router->request()->paramsPost();
            $repository = $this->repository;
            $repository::delete($params['ids']);
        } catch (Exception $e) {
            $deleteSuccess = false;
            $errorMessage = $e->getMessage();
        }
        
        $this->router->response()->json(
            array(
                'success' => $deleteSuccess,
                'errorMessage' => $errorMessage
            )
        );
    }

    /**
     *
     * @method get
     * @route /{object}/list
     */
    public function datatableAction()
    {
        $myDatatable = new $this->datatableObject($this->getParams('get'), $this->objectClass);
        $myDataForDatatable = $myDatatable->getDatas();
        /* Secure strings */
        for ($i = 0; $i < count($myDataForDatatable['data']); $i++) {
            foreach ($myDataForDatatable['data'][$i] as $key => $value) {
                if (is_string($value)) {
                    $myDataForDatatable['data'][$i][$key] = String::escapeSecure($value);
                }
            }
        }
        $this->router->response()->json($myDataForDatatable);
    }

    /**
     * Get the list of massive change fields
     *
     * Response JSON
     *
     * @method get
     * @route /{object}/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        $data = array(
            'listMc' => array()
        );

        $stmt = $this->db->prepare(
            "SELECT f.field_id, f.label
            FROM cfg_forms_fields f, cfg_forms_massive_change_fields_relations mcfr, cfg_forms_massive_change mc
            WHERE mc.route = :route
                AND mc.massive_change_id = mcfr.massive_change_id
                AND f.field_id = mcfr.field_id 
                ORDER BY f.label ASC"
        );
        $stmt->bindValue(':route', $this->objectBaseUrl . '/mc_fields', \PDO::PARAM_STR);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $data['listMc'][$row['field_id']] = $row['label'];
        }

        $data['success'] = true;
        $this->router->response()->json($data);
    }

    /**
     * Get field HTML
     *
     * Response HTML
     *
     * @method get
     * @route /{object}/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        $requestParam = $this->getParams('named');

        $stmt = $this->db->prepare(
            "SELECT name, label, default_value, attributes, type, help
            FROM cfg_forms_fields
            WHERE field_id = :id"
        );
        $stmt->bindValue(':id', $requestParam['id'], \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        
        $row['validators'] = Form::getValidatorsByField($requestParam['id']);
        $extraParams['id'] = 0;
        
        $form = new Form('default');
        $form->add($row, $extraParams);
        $formElements = $form->toSmarty();
        $this->tpl->assign('field', $formElements[$row['name']]['html']);
        $this->tpl->assign('formName', "massive_change");
        $this->tpl->assign('typeField',  $row['type']);
        $this->tpl->display('tools/mcField.tpl');
    }

    /**
     * Duplicate a object
     *
     * Response JSON
     *
     * @method post
     * @route /{object}/duplicate
     */
    public function duplicateAction()
    {
        $duplicateSuccess = true;
        $errorMessage = '';
        
        try {
            $listDuplicate = json_decode($this->router->request()->param('duplicate'));

            $objClass = $this->objectClass;
            $repository = $this->repository;
            $repository::duplicate($listDuplicate);
        } catch (Exception $e) {
            $duplicateSuccess = false;
            $errorMessage = $e->getMessage();
        }
        
        $this->router->response()->json(
            array(
                'success' => $duplicateSuccess,
                'errorMessage' => $errorMessage
            )
        );
    }
    
    /**
     * Enable object
     *
     * @method post
     * @route /{object}/enable
     */
    public function enableAction()
    {
        $this->setEnableDisableParameter('1');
    }
    
    /**
     * Disable object
     *
     * @method post
     * @route /{object}/disable
     */
    public function disableAction()
    {
        $this->setEnableDisableParameter('0');
    }

    /**
     * Apply the massive change to a object
     *
     * Response JSON
     *
     * @method post
     * @route /{object}/massive_change
     */
    public function massiveChangeAction()
    {
        $massiveChangeSuccess = true;
        $errorMessage = '';
        $repository = $this->repository;
        try {
            $params = $this->router->request()->paramsPost();
            $datas = $params['values'];

            foreach ($params['ids'] as $id) {
                $datas['object_id'] = $id;
                $repository::update(
                    $datas,
                    'form',
                    $this->objectBaseUrl . '/update',
                    true,
                    false
                );
            }
        } catch (Exception $e) {
            $massiveChangeSuccess = false;
            $errorMessage = $e->getMessage();
        }
        
        $this->router->response()->json(
            array(
                'success' => $massiveChangeSuccess,
                'errorMessage' => $errorMessage
            )
        );
    }
    
    /**
     * Get relations 
     *
     * @param string $relClass
     */
    protected function getRelations($relClass)
    {
        $requestParam = $this->getParams('named');
        $repository = $this->repository;
        $list = $repository::getRelations($relClass, $requestParam['id']);
        $this->router->response()->json($list);
    }

    /**
     * Get simple relation (1-N)
     *
     * @param string $fieldName
     * @param string $targetObj
     * @param bool $reverse
     */
    public function getSimpleRelation($fieldName, $targetObj, $reverse = false)
    {
        $requestParam = $this->getParams('named');
        $repository = $this->repository;
        $list = $repository::getSimpleRelation($fieldName, $targetObj, $requestParam['id'], $reverse);
        $this->router->response()->json($list);
    }

    /**
     * Set enable or disable
     *
     * @param string $value
     * @throws \Centreon\Internal\Exception
     */
    protected function setEnableDisableParameter($value)
    {
        if (static::$enableDisableFieldName == '') {
            throw new Exception('Cannot enable or disable this object');
        }
        $success = true;
        $errorMessage = '';
        $field = static::$enableDisableFieldName;

        try {
            $params = $this->router->request()->paramsPost();

            $repository = $this->repository;
            foreach ($params['ids'] as $id) {
                $repository::disable(
                    array(
                        'object_id' => $id,
                        $field => $value
                    )
                );
            }
        } catch (Exception $e) {
            $success = false;
            $errorMessage = $e->getMessage();
        }
        
        $this->router->response()->json(
            array(
                'success' => $success,
                'errorMessage' => $errorMessage
            )
        );

    }
}

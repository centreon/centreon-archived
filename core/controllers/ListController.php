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
use Centreon\Internal\Controller;
use Centreon\Internal\Module\Informations;

/**
 * Abstact class for configuration controller
 *
 * @version 3.0.0
 * @author Maximilien Bersoult <mbersoult@merethis.com>
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
     * @var type 
     */
    protected $objectBaseUrl = '';
    
    /**
     *
     * @var type 
     */
    protected $objectName = '';
    
    /**
     *
     * @var type 
     */
    public static $isDisableable = false;

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

        $rc = new \ReflectionClass(get_class($this));
        static::$moduleName = Informations::getModuleFromPath($rc->getFileName());
        static::$moduleShortName = Informations::getModuleSlugName(static::$moduleName);

        $this->objectBaseUrl = '/' . static::$moduleShortName . '/' . $this->objectName;
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
        $this->tpl->addCss('jquery.dataTables.min.css')
            ->addCss('jquery.fileupload.css')
            ->addCss('dataTables.tableTools.min.css')
            ->addCss('dataTables.colVis.min.css')
            ->addCss('dataTables.colReorder.min.css')
            ->addCss('dataTables.fixedHeader.min.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('centreon-wizard.css');

        /* Load JsFile */
        $this->tpl->addJs('jquery.dataTables.min.js')
            ->addJs('dataTables.tableTools.min.js')
            ->addJs('dataTables.colVis.min.js')
            ->addJs('dataTables.colReorder.min.js')
            ->addJs('dataTables.fixedHeader.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('dataTables.bootstrap.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validate.min.js')
            ->addJs('additional-methods.min.js')
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
            ->addJs('centreon-wizard.js');

        /* Set Cookie */
        $token = Form::getSecurityToken();
        setcookie("ajaxToken", $token, time()+15, '/');
        
        /* Display variable */
        $this->tpl->assign('objectName', $this->objectDisplayName);
        $this->tpl->assign('datatableObject', $this->datatableObject);
        $this->tpl->assign('moduleName', static::$moduleName);
        $this->tpl->assign('objectAddUrl', $this->objectBaseUrl . '/add');
        $this->tpl->assign('objectListUrl', $this->objectBaseUrl . '/list');
        $this->tpl->assign('objectMcUrl', $this->objectBaseUrl . '/massive_change');
        $this->tpl->assign('objectMcFieldsUrl', $this->objectBaseUrl . '/mc_fields');
        $this->tpl->assign('isDisableable', static::$isDisableable);
        if (static::$isDisableable) {
            $this->tpl->assign('objectEnableUrl', $this->objectBaseUrl . '/enable');
            $this->tpl->assign('objectDisableUrl', $this->objectBaseUrl . '/disable');
        }
        $this->tpl->assign('objectDuplicateUrl', $this->objectBaseUrl . '/duplicate');
        $this->tpl->assign('objectDeleteUrl', $this->objectBaseUrl . '/delete');
        $this->tpl->display('file:[CentreonConfigurationModule]list.tpl');
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
        $form = new Wizard($this->objectBaseUrl . '/add', array('id' => 0));
        $form->addHiddenComponent('object', $this->objectName);
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
        
        $validationResult = Form::validate("wizard", $this->getUri(), static::$moduleName, $givenParameters);
        if ($validationResult['success']) {
            $repository = $this->repository;
            try {
                $id = $repository::create($givenParameters);
            } catch (Exception $e) {
                $this->router->response()->json(array('success' => false, 'error' => $e->getMessage()));
            }
        } else {
            $this->router->response()->json(array('success' => false, 'error' => $validationResult['error']));
        }
        
        if ($sendResponse) {
            $this->router->response()->json(array('success' => true));
        } else {
            return $id;
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
            Form::validateSecurity(filter_input(INPUT_COOKIE, 'ajaxToken'));
            $params = $this->router->request()->paramsPost();
            $repository = $this->repository;
            $repository::delete($params['ids']);
            
            /* Set Cookie */
            $token = Form::getSecurityToken();
            setcookie("ajaxToken", $token, time()+15, '/');
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
                AND f.field_id = mcfr.field_id"
        );
        $stmt->bindValue(':route', $this->objectBaseUrl . '/mc_fields', \PDO::PARAM_STR);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $data['listMc'][$row['field_id']] = $row['label'];
        }

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
        $di = Di::getDefault();
        $this->router = $di->get('router');
        $dbconn = $di->get('db_centreon');
        
        $requestParam = $this->getParams('named');

        $stmt = $this->routeprepare(
            "SELECT name, label, default_value, attributes, type, help
            FROM cfg_forms_fields
            WHERE field_id = :id"
        );
        $stmt->bindValue(':id', $requestParam['id'], \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $form = new Form('default');
        $form->add($row, array('id' => 0));
        $formElements = $form->toSmarty();
        $this->tpl->assign('field', $formElements[$row['name']]['html']);
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
            Form::validateSecurity(filter_input(INPUT_COOKIE, 'ajaxToken'));
            $listDuplicate = json_decode($this->router->request()->param('duplicate'));

            $objClass = $this->objectClass;
            $repository = $this->repository;
            $repository::duplicate($listDuplicate);
            
            /* Set Cookie */
            $token = Form::getSecurityToken();
            setcookie("ajaxToken", $token, time()+15, '/');
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
     * @param string $field
     * @method post
     * @route /{object}/enable
     */
    public function enableAction($field)
    {
        $enableSuccess = true;
        $errorMessage = '';
        
        try {
            Form::validateSecurity(filter_input(INPUT_COOKIE, 'ajaxToken'));
            $params = $this->router->request()->paramsPost();

            $repository = $this->repository;
            foreach ($params['ids'] as $id) {
                $repository::update(
                    array(
                        'object_id' => $id,
                        $field => '1'
                    )
                );
            }

            /* Set Cookie */
            $token = Form::getSecurityToken();
            setcookie("ajaxToken", $token, time()+15, '/');
        } catch (Exception $e) {
            $enableSuccess = false;
            $errorMessage = $e->getMessage();
        }
        
        $this->router->response()->json(
            array(
                'success' => $enableSuccess,
                'errorMessage' => $errorMessage
            )
        );
    }
    
    /**
     * Disable object
     *
     * @param string $field
     * @method post
     * @route /{object}/disable
     */
    public function disableAction($field)
    {
        $enableSuccess = true;
        $errorMessage = '';
        
        try {
            Form::validateSecurity(filter_input(INPUT_COOKIE, 'ajaxToken'));
            $params = $this->router->request()->paramsPost();

            $repository = $this->repository;
            foreach ($params['ids'] as $id) {
                $repository::update(
                    array(
                        'object_id' => $id,
                        $field => '0'
                    )
                );
            }

            /* Set Cookie */
            $token = Form::getSecurityToken();
            setcookie("ajaxToken", $token, time()+15, '/');
        } catch (Exception $e) {
            $enableSuccess = false;
            $errorMessage = $e->getMessage();
        }
        
        $this->router->response()->json(
            array(
                'success' => $enableSuccess,
                'errorMessage' => $errorMessage
            )
        );
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
        
        try {
            Form::validateSecurity(filter_input(INPUT_COOKIE, 'ajaxToken'));
            $params = $this->router->request()->paramsPost();

            $objClass = $this->objectClass;
            foreach ($params['ids'] as $id) {
                $objClass::update($id, $params['values']);
            }
            
            /* Set Cookie */
            $token = Form::getSecurityToken();
            setcookie("ajaxToken", $token, time()+15, '/');
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
        $list = $repository::getSimpleRelation($fieldName, $targetObj, $requestParam['id']);
        $this->router->response()->json($list);
    }
}

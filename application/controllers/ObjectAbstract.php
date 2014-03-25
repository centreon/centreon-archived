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

namespace Controllers;

use \Centreon\Core\Form\Generator;

/**
 * Abstact class for configuration controller
 *
 * @version 3.0.0
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 */
abstract class ObjectAbstract extends \Centreon\Core\Controller
{
    /**
     * Array of field names => relation class names
     *
     * @var array
     */
    public static $relationMap;

    /**
     * List view for object
     */
    public function listAction()
    {
        /* Init template */
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');

        /* Load CssFile */
        $tpl->addCss('dataTables.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('dataTables-TableTools.css')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('centreon-wizard.css');

        /* Load JsFile */
        $tpl->addJs('jquery.dataTables.min.js')
            ->addJs('jquery.dataTables.TableTools.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validate.min.js')
            ->addJs('additional-methods.min.js')
            ->addJs('centreon-wizard.js');

        /* Set Cookie */
        $token = \Centreon\Core\Form::getSecurityToken();
        setcookie("ajaxToken", $token, time()+15, '/');
        
        /* Display variable */
        $tpl->assign('objectName', $this->objectDisplayName);
        $tpl->assign('objectAddUrl', $this->objectBaseUrl . '/add');
        $tpl->assign('objectListUrl', $this->objectBaseUrl . '/list');
        $tpl->assign('objectMcUrl', $this->objectBaseUrl . '/massive_change');
        $tpl->assign('objectMcFieldsUrl', $this->objectBaseUrl . '/mc_fields');
        $tpl->assign('objectDuplicateUrl', $this->objectBaseUrl . '/duplicate');
        $tpl->assign('objectDeleteUrl', $this->objectBaseUrl . '/delete');
        $tpl->display('configuration/list.tpl');
    }
    
    /**
     * Action for getting list of objects
     *
     * JSON response
     */
    public function formListAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        if (!empty($this->secondaryObjectClass)) {
            $class = $this->secondaryObjectClass;
        } else {
            $class = $this->objectClass;
        }
        $requestParams = $this->getParams('get');
        $idField = $class::getPrimaryKey();
        $uniqueField = $class::getUniqueLabelField();
        $filters = array(
            $uniqueField => '%'.$requestParams['q'].'%'
        );
        $list = $class::getList(array($idField, $uniqueField), -1, 0, null, "ASC", $filters, "AND");
        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$idField],
                "text" => $obj[$uniqueField]
            );
        }
        $router->response()->json($finalList);
    }
    
    /**
     * Get wizard for add a object
     *
     * Response HTML
     */
    public function addAction()
    {
        $form = new \Centreon\Core\Form\Wizard($this->objectBaseUrl . '/add', 0, array('id' => 0));
        $form->addHiddenComponent('object', $this->objectName);
        $tpl = \Centreon\Core\Di::getDefault()->get('template');
        $tpl->assign('formName', $form->getName());
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
     * @todo handle token
     * @return int id of created object
     */
    public function createAction()
    {
        $givenParameters = clone $this->getParams('post');
        $createSuccessful = true;
        $createErrorMessage = '';
        
        $validationResult = \Centreon\Core\Form::validate("wizard", $this->getUri(), $givenParameters);
        if ($validationResult['success']) {
            $class = $this->objectClass;
            $pk = $class::getPrimaryKey();
            $db = \Centreon\Core\Di::getDefault()->get('db_centreon');
            try {
                $columns = $class::getColumns();
                $insertParams = array();
                foreach ($givenParameters as $key => $value) {
                    if (in_array($key, $columns)) {
                        $insertParams[$key] = $value;
                    }
                }
                $id = $class::insert($insertParams);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            if (isset($id)) {
                foreach (static::$relationMap as $k => $rel) {
                    try {
                        if (!isset($givenParameters[$k])) {
                            continue;
                        }
                        $arr = explode(',', $givenParameters[$k]);
                        $db->beginTransaction();
                        foreach ($arr as $relId) {
                            if (!is_numeric($relId)) {
                                continue;
                            }
                            if ($rel::$firstObject == $this->objectClass) {
                                $rel::insert($id, $relId);
                            } else {
                                $rel::insert($relId, $id);
                            }
                        }
                        $db->commit();
                        unset($givenParameters[$k]);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
                \Centreon\Core\Di::getDefault()
                    ->get('router')
                    ->response()
                    ->json(array('success' => true));
                $this->postSave($id, 'add');
                return $id;
            }
        } else {
            $createSuccessful = false;
            $createErrorMessage = $validationResult['error'];
        }
        
        $router = \Centreon\Core\Di::getDefault()->get('router');
        if ($createSuccessful) {
            $router->response()->json(array('success' => true));
            $this->postSave($id, 'update');
        } else {
            $router->response()->json(array('success' => false,'error' => $createErrorMessage));
        }
    }
    
    /**
     * 
     */
    public function editAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');
        
        $requestParam = $this->getParams('named');
        $objectFormUpdateUrl = $this->objectBaseUrl.'/update';
        
        $myForm = new Generator($objectFormUpdateUrl, $requestParam['advanced'], array('id' => $requestParam['id']));
        $myForm->addHiddenComponent('object_id', $requestParam['id']);
        $myForm->addHiddenComponent('object', $this->objectName);
        
        // get object Current Values
        $myForm->setDefaultValues($this->objectClass, $requestParam['id']);
        
        $formModeUrl = \Centreon\Core\Di::getDefault()
                        ->get('router')
                        ->getPathFor(
                            $this->objectBaseUrl.'/[i:id]/[i:advanced]',
                            array(
                                'id' => $requestParam['id'],
                                'advanced' => (int)!$requestParam['advanced']
                            )
                        );
        
        // Display page
        $tpl->assign('pageTitle', $this->objectDisplayName);
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('advanced', $requestParam['advanced']);
        $tpl->assign('formModeUrl', $formModeUrl);
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('validateUrl', $objectFormUpdateUrl);
        $tpl->display('configuration/edit.tpl');
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
        
        $validationResult = \Centreon\Core\Form::validate("form", $this->getUri(), $givenParameters);
        if ($validationResult['success']) {
            $class = $this->objectClass;
            $pk = $class::getPrimaryKey();
            $givenParameters[$pk] = $givenParameters['object_id'];
            $db = \Centreon\Core\Di::getDefault()->get('db_centreon');
            if (isset($givenParameters[$pk])) {
                $id = $givenParameters[$pk];
                unset($givenParameters[$pk]);
                foreach (static::$relationMap as $k => $rel) {
                    try {
                        if (!isset($givenParameters[$k])) {
                            continue;
                        }
                        if ($rel::$firstObject == $this->objectClass) {
                            $rel::delete($id);
                        } else {
                            $rel::delete(null, $id);
                        }
                        $arr = explode(',', $givenParameters[$k]);
                        $db->beginTransaction();
                        foreach ($arr as $relId) {
                            if (!is_numeric($relId)) {
                                continue;
                            }
                            if ($rel::$firstObject == $this->objectClass) {
                                $rel::insert($id, $relId);
                            } else {
                                $rel::insert($relId, $id);
                            }
                        }
                        $db->commit();
                        unset($givenParameters[$k]);
                    } catch (Exception $e) {
                        $updateErrorMessage = $e->getMessage();
                    }
                }
                try {
                    $columns = $class::getColumns();
                    foreach ($givenParameters as $key => $value) {
                        if (!in_array($key, $columns)) {
                            unset($givenParameters[$key]);
                        }
                    }
                    $class::update($id, $givenParameters->all());
                } catch (Exception $e) {
                    $updateErrorMessage = $e->getMessage();
                }
            }
        } else {
            $updateSuccessful = false;
            $updateErrorMessage = $validationResult['error'];
        }
        
        $router = \Centreon\Core\Di::getDefault()->get('router');
        if ($updateSuccessful) {
            $router->response()->json(array('success' => true));
            $this->postSave($id, 'update');
        } else {
            $router->response()->json(array('success' => false,'error' => $updateErrorMessage));
        }
    }
    
    /**
     * Delete a object
     *
     * Response JSON
     */
    public function deleteAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        $deleteSuccess = true;
        $errorMessage = '';
        
        try {
            \Centreon\Core\Form::validateSecurity(filter_input(INPUT_COOKIE, 'ajaxToken'));
            $params = $router->request()->paramsPost();
            
            $objClass = $this->objectClass;
            foreach ($params['ids'] as $id) {
                $this->preSave($id, 'delete');
                $objClass::delete($id);
                $this->postSave($id, 'delete');
            }
            
            /* Set Cookie */
            $token = \Centreon\Core\Form::getSecurityToken();
            setcookie("ajaxToken", $token, time()+15, '/');
        } catch (\Centreon\Core\Exception $e) {
            $deleteSuccess = false;
            $errorMessage = $e->getMessage();
        }
        
        $router->response()->json(
            array(
                'success' => $deleteSuccess,
                'errorMessage' => $errorMessage
            )
        );
    }

    /**
     * 
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(
            \Centreon\Core\Datatable::getDatas(
                $this->objectName,
                $this->getParams('get')
            )
        );
    }

    /**
     * Get the list of massive change fields
     *
     * Response JSON
     */
    public function getMassiveChangeFieldsAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        $dbconn = $di->get('db_centreon');

        $data = array(
            'listMc' => array()
        );

        $stmt = $dbconn->prepare(
            "SELECT f.field_id, f.label
            FROM form_field f, form_massive_change_field_relation mcfr, form_massive_change mc
            WHERE mc.route = :route
                AND mc.massive_change_id = mcfr.massive_change_id
                AND f.field_id = mcfr.field_id"
        );
        $stmt->bindValue(':route', $this->objectBaseUrl . '/mc_fields', \PDO::PARAM_STR);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $data['listMc'][$row['field_id']] = $row['label'];
        }

        $router->response()->json($data);
    }

    /**
     * Get field HTML
     *
     * Response HTML
     */
    public function getMcFieldAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        $dbconn = $di->get('db_centreon');
        $tpl = $di->get('template');
        
        $requestParam = $this->getParams('named');

        $stmt = $dbconn->prepare(
            "SELECT name, label, default_value, attributes, type, help
            FROM form_field
            WHERE field_id = :id"
        );
        $stmt->bindValue(':id', $requestParam['id'], \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $form = new \Centreon\Core\Form('default');
        $form->add($row, array('id' => 0));
        $formElements = $form->toSmarty();
        $tpl->assign('field', $formElements[$row['name']]['html']);
        $tpl->display('tools/mcField.tpl');
    }

    /**
     * Duplicate a object
     *
     * Response JSON
     */
    public function duplicateAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        $duplicateSuccess = true;
        $errorMessage = '';
        
        try {
            \Centreon\Core\Form::validateSecurity(filter_input(INPUT_COOKIE, 'ajaxToken'));
            $listDuplicate = json_decode($di->get('router')->request()->param('duplicate'));

            $objClass = $this->objectClass;
            foreach ($listDuplicate as $id => $nb) {
                $objClass::duplicate($id, $nb);
            }
            
            /* Set Cookie */
            $token = \Centreon\Core\Form::getSecurityToken();
            setcookie("ajaxToken", $token, time()+15, '/');
        } catch (\Centreon\Core\Exception $e) {
            $duplicateSuccess = false;
            $errorMessage = $e->getMessage();
        }
        
        $router->response()->json(
            array(
                'success' => $duplicateSuccess,
                'errorMessage' => $errorMessage
            )
        );
    }

    /**
     * Apply the massive change to a object
     *
     * Response JSON
     */
    public function massiveChangeAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        $massiveChangeSuccess = true;
        $errorMessage = '';
        
        try {
            \Centreon\Core\Form::validateSecurity(filter_input(INPUT_COOKIE, 'ajaxToken'));
            $params = $router->request()->paramsPost();

            $objClass = $this->objectClass;
            foreach ($params['ids'] as $id) {
                $objClass::update($id, $params['values']);
            }
            
            /* Set Cookie */
            $token = \Centreon\Core\Form::getSecurityToken();
            setcookie("ajaxToken", $token, time()+15, '/');
        } catch (\Centreon\Core\Exception $e) {
            $massiveChangeSuccess = false;
            $errorMessage = $e->getMessage();
        }
        
        $router->response()->json(
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
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        $curObj = $this->objectClass;
        if ($relClass::$firstObject == $curObj) {
            $tmp = $relClass::$secondObject;
            $fArr = array();
            $sArr = array($tmp::getPrimaryKey(), $tmp::getUniqueLabelField());
        } else {
            $tmp = $relClass::$firstObject;
            $fArr = array($tmp::getPrimaryKey(), $tmp::getUniqueLabelField());
            $sArr = array();
        }
        $cmp = $curObj::getTableName() . '.' . $curObj::getPrimaryKey();
        $list = $relClass::getMergedParameters(
            $fArr,
            $sArr,
            -1,
            0,
            null,
            "ASC",
            array($cmp => $requestParam['id']),
            "AND"
        );
        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$tmp::getPrimaryKey()],
                "text" => $obj[$tmp::getUniqueLabelField()]
            );
        }
        $router->response()->json($finalList);
    }

    /**
     * Action before save
     *
     * * Emit event objectName.action
     *
     * @param $id int The object id
     * @param $action string The action (add, update, delete)
     */
    protected function preSave($id, $action = 'add')
    {
        $actionList = array(
            'delete' => 'd'
        );
        if (false === in_array($action, array_keys($actionList))) {
            return;
        }
        $objClass = $this->objectClass;
        $name = $objClass::getParameters($id, $objClass::getUniqueLabelField());
        $name = $name[$objClass::getUniqueLabelField()];
        /* Add change log */
        \Models\Tools\LogAction::addLog(
            $actionList[$action],
            $this->objectName,
            $id,
            $name,
            array()
        );
    }

    /**
     * Action after save
     *
     * * Emit event objectName.action
     *
     * @param $id int The object id
     * @param $action string The action (add, update, delete)
     */
    protected function postSave($id, $action = 'add')
    {
        $actionList = array(
            'add' => 'a',
            'update' => 'c'
        );
        $di = \Centreon\Core\Di::getDefault();
        $params = $di->get('router')->request()->params();
        $event = $di->get('action_hooks');
        $eventParams = array(
            'id' => $id,
            'params' => $params
        );
        $event->emit($this->objectName . '.' . $action, $eventParams);
        /* Add change log */
        if (false === in_array($action, array_keys($actionList))) {
            return;
        }
        $objClass = $this->objectClass;
        $name = $objClass::getParameters($id, $objClass::getUniqueLabelField());
        $name = $name[$objClass::getUniqueLabelField()];
        \Models\Tools\LogAction::addLog(
            $actionList[$action],
            $this->objectName,
            $id,
            $name,
            $params
        );
    }
}

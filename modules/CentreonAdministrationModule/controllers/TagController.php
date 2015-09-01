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
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonAdministration\Controllers;

use Centreon\Internal\Di;
use CentreonAdministration\Repository\TagsRepository;
use Centreon\Internal\Controller;
use CentreonAdministration\Internal\TagDatatable;
use Centreon\Internal\Form\Generator\Web\Full as WebFormGenerator;
use Centreon\Internal\Utils\String;

/**
 * Controller for tag action
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonAdministration
 */
class TagController extends Controller
{    
    protected $objectDisplayName = 'Tag';
    public static $objectName = 'tag';
    protected $objectBaseUrl = '/centreon-administration/tag';
    protected $objectClass = '\CentreonAdministration\Models\Tag';
    protected $repository = '\CentreonAdministration\Repository\TagRepository';
    
    public static $relationMap = array();
    
    protected $datatableObject = '\CentreonAdministration\Internal\TagDatatable';
    public static $isDisableable = false;
    
    public static $disableButton = true;
    
    public static $displayActionBar = true; 
    
    /**
     *
     * @var type 
     */
    //protected $objectBaseUrl = '/tag'; 
    
    /**
     * Add a tag
     *
     * @method post
     * @route /tag/add
     */
    public function addAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $post = $router->request()->paramsPost();
        $sGlobal = 0;

        if (!is_array($post['resourceId'])) {
            $listResources = array($post['resourceId']);
        } else {
            $listResources = $post['resourceId'];
        }
        if (isset($post['typeTag']) && $post['typeTag'] == 1) {
            $sGlobal = 1;
        }
     
        foreach ($listResources as $resourceId) {
            $tagId = TagsRepository::add(
                $post['tagName'],
                $post['resourceName'],
                $resourceId,
                $sGlobal
            );
        }
        return $router->response()->json(array('success' => true, 'tagId' => $tagId));
    }
    /**
     * Add a tag
     *
     * @method post
     * @route /tag/addMassive
     */
    public function addMassiveAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $post = $router->request()->paramsPost();
        $sGlobal = 0;
        $bStatus = false;
        $addErrorMessage = '';
        
        $bNotDelete = true;
        
        $sTagName = trim($post['tagName']);
        
        if (empty($sTagName)) {
            return;
        }

        if (!is_array($post['resourceId'])) {
            $listResources = array($post['resourceId']);
        } else {
            $listResources = $post['resourceId'];
        }
        if (!is_array($sTagName)) {
            $listTag = explode(",", $sTagName);
        } else {
            $listTag = $sTagName;
        }


        if (isset($post['typeTag']) && $post['typeTag'] == 1) {
            $sGlobal = 1;
        }
        
        $listTag = array_diff($listTag, array(''));
        
        if (count($listTag) > 0) {
            try {    
                foreach ($listResources as $resourceId) {
                    TagsRepository::saveTagsForResource($post['resourceName'], $resourceId, $listTag, 0, $bNotDelete, $sGlobal);
                }
                $bStatus = true;

            } catch (Exception $e) {
                $addErrorMessage = $e->getMessage();
                $bStatus = false;
            }
        }
        return $this->router->response()->json(array('success' => $bStatus,'error' => $addErrorMessage));
            
    }

    /**
     * Delete a tag
     *
     * @method post
     * @route /tag/delete
     */
    public function deleteAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $post = $router->request()->paramsPost();
 
        TagsRepository::delete(
            $post['tagId'],
            $post['resourceName'],
            $post['resourceId']
        );
        return $router->response()->json(array('success' => true));
    }

    /**
     * get tag list by object
     *
     * @method get
     * @route /tag/[a:objectName]/formlist
     */
    public function listTagByObjectAction()
    {
        $data = '';
        $params = $this->getParams('named');

        if (isset($params['objectName'])) {
            $data = TagsRepository::getList($params['objectName'], "", 1, 0);
        }
        $this->router->response()->json($data);
    }
    
    /**
     * get list tag
     * 
     * @method get
     * @route /tag/[i:id]/[a:objectName]/formlist
     */
    public function listAction()
    {
        $data = '';
        $get = $this->getParams('named');
        
        if (isset($get['objectName']) && isset($get['id']) && $get['id'] > 0) {
            $data = TagsRepository::getList($get['objectName'], $get['id'], 1, 0);
        }
        $this->router->response()->json($data);
    }
    
    
    /**
     * get all tag
     * 
     * @method get
     * @route /tag/allPerso
     */
    public function allPersoAction()
    {
        $data = '';
        $sSearch = '';
        $get = $this->getParams();
        if (isset($get['search'])) {
            $sSearch = trim($get['search']);
            $sSearch = str_replace('"','', $sSearch);
        }

        $data = TagsRepository::getAllList($sSearch, 2);
        $this->router->response()->json($data);
    }
    /**
     * get all tag
     * 
     * @method get
     * @route /tag/all
     */
    public function allAction()
    {
        $data = '';
        $sSearch = '';
        $get = $this->getParams();
        if (isset($get['search'])) {
            $sSearch = trim($get['search']);
            $sSearch = str_replace('"','', $sSearch);
        }
        
        $data = TagsRepository::getAllList($sSearch, 1);
        $this->router->response()->json($data);
    }
    /**
     *
     * @method get
     * @route /tag/list
     */
    public function datatableAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $myDatatable = new TagDatatable($this->getParams('get'), $this->objectClass);
        $myDataForDatatable = $myDatatable->getDatas();
        /* Secure strings */
        for ($i = 0; $i < count($myDataForDatatable['data']); $i++) {
            foreach ($myDataForDatatable['data'][$i] as $key => $value) {
                if (is_string($value)) {
                    $myDataForDatatable['data'][$i][$key] = String::escapeSecure($value);
                }
            }
        }
          
        $router->response()->json($myDataForDatatable);
    }
    
    
    /**
     * 
     * @method get
     * @route /tag
     */
    public function listeAction()
    {
        // Load CssFile
        $this->tpl->addCss('jquery.fileupload.css');

        /* Load CssFile */
        $this->tpl->addCss('dataTables.tableTools.min.css')
            ->addCss('jquery.fileupload.css')
            ->addCss('dataTables.colVis.min.css')
            ->addCss('dataTables.colReorder.min.css')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('centreon-wizard.css');

        /* Load JsFile */
        $this->tpl->addJs('jquery.dataTables.min.js')
            ->addJs('dataTables.tableTools.min.js')
            ->addJs('dataTables.colVis.min.js')
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
        $this->tpl->assign('objectName', static::$objectName);
        $this->tpl->assign('objectDisplayName', $this->objectDisplayName);
        $this->tpl->assign('datatableObject', $this->datatableObject);
        $this->tpl->assign('moduleName', static::$moduleName);
        $this->tpl->assign('objectAddUrl', '');
        $this->tpl->assign('objectListUrl', $this->objectBaseUrl . '/list');
        $this->tpl->assign('isDisableable', static::$isDisableable);
        $this->tpl->assign('disableButton', static::$disableButton);
        $this->tpl->assign('displayActionBar', static::$displayActionBar);
        
        $this->tpl->assign('objectDeleteUrl', $this->objectBaseUrl . '/del');
        $this->tpl->display('file:[CentreonMainModule]list.tpl');
    }
    
    /**
     * Delete a tag
     *
     * @method post
     * @route /tag/del
     */
    public function delAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $post = $router->request()->paramsPost();
      
        TagsRepository::deleteGlobal(
            $post['ids']
        );
        return $router->response()->json(array('success' => true));
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

        $this->tpl->assign('formModeUrl', $formModeUrl);
        $this->tpl->assign('formName', $myForm->getName());
        $this->tpl->assign('validateUrl', $objectFormUpdateUrl);
        
        foreach ($additionnalParamsForSmarty as $smartyVarName => $smartyVarValue) {
            $this->tpl->assign($smartyVarName, $smartyVarValue);
        }
        

        if (isset($this->tmplField)) {
            $this->tpl->assign('tmplField', $this->tmplField);
        }
        
        $this->tpl->display('file:[CentreonConfigurationModule]editTag.tpl');
    }
        
    /**
     * update function
     *
     * @method post
     * @route /{object}/update
     */
    public function updateAction()
    {
        $givenParameters = clone $this->getParams('post');
        try {
            $tagId = TagsRepository::isExist($givenParameters['tagname']);
            if ($tagId > 0 && $tagId != $givenParameters['object_id']) {
                $this->router->response()->json(array('success' => false,'error' => "This tag name already exists"));           
            } else {
                TagsRepository::update($givenParameters, "form", $this->getUri());
                unset($_SESSION['form_token']);
                unset($_SESSION['form_token_time']);
                $this->router->response()->json(array('success' => true));
            }
                   
        } catch (\Centreon\Internal\Exception $e) {
            $updateErrorMessage = $e->getMessage();
            $this->router->response()->json(array('success' => false,'error' => $updateErrorMessage));
        }
    }
    
    /**
     * get list herited tag
     * 
     * @method get
     * @route /tag/[i:id]/[a:objectName]/herited
     */
    public function heritedTagAction()
    {
        $data = '';
        $get = $this->getParams('named');
        if (isset($get['objectName']) && isset($get['id'])) {
            $data = TagsRepository::getHeritedTags($get['objectName'], $get['id']);
        }
        $this->router->response()->json($data);
    }
}

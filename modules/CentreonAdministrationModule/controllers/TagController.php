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
 */

namespace CentreonAdministration\Controllers;

use Centreon\Internal\Di;
use CentreonAdministration\Repository\TagsRepository;
use Centreon\Internal\Controller;
use CentreonAdministration\Internal\TagDatatable;
use Centreon\Internal\Form\Generator\Web\Full as WebFormGenerator;

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
      
        foreach ($listResources as $resourceId) {
            TagsRepository::saveTagsForResource($post['resourceName'], $resourceId, $listTag);
        }
     
        return $router->response()->json(array('success' => true));
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
     * get list tag
     * 
     * @method get
     * @route /tag/[i:id]/[a:objectName]/formlist
     */
    public function listAction()
    {
        $data = '';
        $get = $this->getParams('named');
        
        if (isset($get['objectName']) && isset($get['id'])) {
            $data = TagsRepository::getList($get['objectName'], $get['id'], 1);
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
        $this->tpl->assign('objectName', $this->objectDisplayName);
        $this->tpl->assign('datatableObject', $this->datatableObject);
        $this->tpl->assign('moduleName', static::$moduleName);
        $this->tpl->assign('objectAddUrl', $this->objectBaseUrl . '/add');
        $this->tpl->assign('objectListUrl', $this->objectBaseUrl . '/list');
        $this->tpl->assign('isDisableable', static::$isDisableable);
        $this->tpl->assign('disableButton', static::$disableButton);
        
        $this->tpl->assign('objectDeleteUrl', $this->objectBaseUrl . '/del');
        $this->tpl->display('file:[CentreonAdministrationModule]list.tpl');
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
                TagsRepository::update($givenParameters['object_id'], $givenParameters['tagname']);
                unset($_SESSION['form_token']);
                unset($_SESSION['form_token_time']);
                $this->router->response()->json(array('success' => true));
            }
                   
        } catch (\Centreon\Internal\Exception $e) {
            $updateErrorMessage = $e->getMessage();
            $this->router->response()->json(array('success' => false,'error' => $updateErrorMessage));
        }
    }
}

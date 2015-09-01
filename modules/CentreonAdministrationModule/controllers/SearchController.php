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

use Centreon\Internal\Controller;
use CentreonAdministration\Repository\SearchRepository;
use Centreon\Internal\Di;

class SearchController extends Controller
{
    /**
     * Save search
     *
     * @method post
     * @route /search/save
     */
    public function saveAction()
    {
        $saveSuccess = false;
        $error = '';
        
        $params = $this->getParams();
        
        try {
            SearchRepository::saveSearch($params['route'], $params['label'], $params['searchText']);
            $saveSuccess = true;
        } catch(Exception $e) {
            
        }
        
        $this->router->response()->json(
            array(
                'success' => $saveSuccess,
                'error' => $error
            )
        );
        
    }
    
    /**
     * Save search
     *
     * @method post
     * @route /search/bookmark
     */
    public function bookmarkAction()
    {
        $saveSuccess = false;
        $error = '';
        
        $params = $this->getParams();
        
        try {
            SearchRepository::saveSearch($params['route'], $params['label'], $params['searchText'], 1);
            $saveSuccess = true;
        } catch(Exception $e) {
            
        }
        
        $this->router->response()->json(
            array(
                'success' => $saveSuccess,
                'error' => $error
            )
        );
        
    }
    
    /**
     * Load search
     * 
     * @method post
     * @route /search/load
     */
    public function loadAction()
    {
        $params = $this->getParams();
        
        $loadSuccess = false;
        $error = '';
        $data = '';
        
        if (!isset($params['route'])) {
            
        } else {
            $data = SearchRepository::loadSearch($params['route'], $params['label']);
            $loadSuccess = true;
        }
        
        $this->router->response()->json(
            array(
                'success' => $loadSuccess,
                'data' => $data,
                'error' => $error
            )
        );
    }
    
    /**
     * Delete search
     * 
     * @method post
     * @route /search/delete
     */
    public function deleteAction()
    {
        $params = $this->getParams();
        $deleteSuccess = false;
        $error = '';
        
        if (!isset($params['route'])) {
            
        } else {
            SearchRepository::deleteSearch($params['route'], $params['label']);
            $deleteSuccess = true;
        }
        
        $this->router->response()->json(
            array(
                'success' => $deleteSuccess,
                'error' => $error
            )
        );
    }
    
    /**
     * Delete search
     * 
     * @method post
     * @route /search/list
     */
    public function listAction()
    {
        $params = $this->getParams();
        $listSuccess = false;
        $error = '';
        $data = '';
        
        if (!isset($params['searchText'])) {
            
        } else {
            $data = SearchRepository::getSearchList($params['route'], $params['searchText']);
            $listSuccess = true;
        }
        
        $this->router->response()->json(
            array(
                'success' => $listSuccess,
                'data' => $data,
                'error' => $error
            )
        );
    }
    
    /**
     * Save search
     *
     * @method get
     * @route /search/getbookmark
     */
    public function getBookmarkAction()
    {
        $bookmarkList = SearchRepository::getBookmark();
        $result = array(
            'success' => 1,
            'bookmark' => $bookmarkList
        );
        echo json_encode($result);
    }
}

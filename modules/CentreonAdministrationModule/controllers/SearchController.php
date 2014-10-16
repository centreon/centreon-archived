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
     * @route /administration/search/save
     */
    public function saveAction()
    {
        $saveSuccess = false;
        $error = '';
        
        $params = $this->getParams();
        
        $finalParamList = array();
        $paramsToSent = array('user_id', 'label', 'route', 'searchText');
        
        
        foreach ($params as $paramName => $paramValue) {
            if ((in_array($paramName, $paramsToSent))) {
                $finalParamList[$paramName] = $paramValue;
            }
        }
        
        try {
            SearchRepository::saveSearch($finalParamList);
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
     * @route /administration/search/load
     */
    public function loadAction()
    {
        $params = $this->getParams();
        
        
        var_dump($params); die();
        $loadSuccess = false;
        $error = '';
        $data = '';
        
        if (!isset($params['route'])) {
            
        } else {
            $data = SearchRepository::loadSearch($params['route']);
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
     * @route /administration/search/delete
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
     * @route /administration/search/list
     */
    public function listAction()
    {
        $params = $this->getParams();
        $listSuccess = false;
        $error = '';
        $data = '';
        
        if (!isset($params['searchText'])) {
            
        } else {
            $data = SearchRepository::getSearchList($params['searchText']);
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
}

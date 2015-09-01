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

namespace CentreonAdministration\Repository;

use CentreonAdministration\Models\Search;
use Centreon\Internal\Di;
use Centreon\Internal\Session;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class SearchRepository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_searches';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Search';
    
    /**
     * 
     * @param type $route
     * @param type $label
     * @param type $searchText
     */
    public static function saveSearch($route, $label, $searchText, $bookmark = 0)
    {
        $searchId = Search::getIdByParameter(
            'route',
            $route,
            array(
                'user_id' => $_SESSION['user']->getId(),
                'label' => $label
            )
        );
        
        if (count($searchId) > 0) {
            $result = Search::get($searchId[0]);
            if (count($result) > 0) {
                Search::update(
                    $searchId[0],
                    array(
                        'searchText' => $searchText,
                        'is_bookmarked' => $bookmark
                    )
                );
            }
        } else {
            Search::insert(
                array(
                    'user_id' => $_SESSION['user']->getId(),
                    'label' => $label,
                    'route' => $route,
                    'searchText' => $searchText
                )
            );
        }
    }
    
    /**
     * 
     * @param type $route
     */
    public static function loadSearch($route, $label)
    {
        $searchId = Search::getIdByParameter(
            'route',
            $route,
            array(
                'user_id' => $_SESSION['user']->getId(),
                'label' => $label
            )
        );
        
        if (count($searchId) > 0) {
            $searchText = Search::get($searchId[0]);
            $result = $searchText['searchText'];
        } else {
            throw new \Exception("Object not exist");
        }
        
        return $result;
    }
    
    /**
     * 
     * @param type $route
     * @param type $label
     */
    public static function deleteSearch($route, $label)
    {
        $searchId = Search::getIdByParameter(
            'route',
            $route,
            array(
                'user_id' => $_SESSION['user']->getId(),
                'label' => $label
            )
        );
        
        if (count($searchId) > 0) {
            Search::delete($searchId[0]);
        } else {
            throw new \Exception("Object not exist");
        }
    }
    
    /**
     * Get list of objects
     *
     * @param string $searchStr
     * @return array
     */
    public static function getSearchList($route, $searchStr = "")
    {
        $searchList = Search::getList(
            array('search_id', 'label'),
            -1,
            0,
            null,
            "ASC",
            array('user_id' => $_SESSION['user']->getId(), 'route' => $route, 'label' => $searchStr.'%'),
            "AND"
        );

        $finalList = array();
        foreach ($searchList as $obj) {
            $finalList[] = array(
                "id" => $obj['search_id'],
                "text" => $obj['label']
            );
        }
        return $finalList;
    }
    
    /**
     * 
     * @return type
     */
    public static function getBookmark()
    {
        $bookmarkList = Search::getList(
            array('search_id', 'searchText', 'route', 'label'),
            -1,
            0,
            null,
            "ASC",
            array('user_id' => $_SESSION['user']->getId()),
            "AND"
        );
        
        return $bookmarkList;
    }
}

<?php
/*
 * Copyright 2005-2014 CENTREON
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

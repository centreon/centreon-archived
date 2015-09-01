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

namespace CentreonMain\Repository;

use CentreonMain\Models\Bookmark;
use Centreon\Internal\Di;
use Centreon\Internal\Session;
use Centreon\Internal\Exception;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class BookmarkRepository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_bookmarks';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Bookmark';
    
    /**
     * 
     * @param type $label
     * @param type $route
     * @param type $type
     * @param type $bookmarkParam
     * @param type $isAlwaysVisible
     * @param type $isPublic
     * @return type
     */
    public static function saveBookmark($label, $route, $type, $bookmarkParam, $isAlwaysVisible = 0, $isPublic = 0)
    {
        $error = false;
        try {
            $bookmarkInfos = array(
                'label' => $label,
                'route' => $route,
                'type' => $type,
                'quick_access' => $bookmarkParam,
                'is_always_visible' => $isAlwaysVisible,
                'is_public' => $isPublic,
                'user_id' => $_SESSION['user']->getId(),
                'short_url_code' => ''
            );
            $bookmarkId = Bookmark::insert($bookmarkInfos);
            $error = true;
        } catch (Exception $e) {
            
        }
        
        return array('success' => $error, 'value' => $bookmarkId);
    }
    
    /**
     * 
     * @return type
     */
    public static function getBookmarkList()
    {
        $bookmarkList = Bookmark::getList(
            "*",
            -1,
            0,
            null,
            'ASC',
            array('user_id' => $_SESSION['user']->getId(), 'is_public' => 1),
            'OR'
        );
        
        return $bookmarkList;
    }
}

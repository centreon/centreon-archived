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

<?php
/**
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

namespace CentreonCustomview\Repository;

use \Centreon\Internal\Exception;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */

/**
 * Class for managing widgets
 */
class CustomviewRepository
{
    /**
     * Return last inserted view id
     *
     * @return int
     * @throws \Centreon\Internal\Exception
     */
    protected static function getLastViewId()
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("SELECT MAX(custom_view_id) as last_id FROM custom_views");
        $stmt->execute();
        if ($stmt->rowCount()) {
            $row = $stmt->fetch();
            return $row['last_id'];
        }
        throw new Exception('No view inserted.');
    }

    /**
     * Check Permission
     * Checks if user is allowed to modify view
     * Returns true if user can, false otherwise
     *
     * @param int $viewId
     * @param int $userId
     * @return bool
     */
    public static function checkPermission($viewId, $userId)
    {
        $views = self::getCustomViews($userId);
        if (!isset($views[$viewId]) || $views[$viewId]['locked']) {
            return false;
        }
        return true;
    }

    /**
     * Check Ownership
     * Checks if user is allowed to delete view
     * Returns true if user can, false otherwise
     *
     * @param int $viewId
     * @param int $userId
     * @return bool
     */
    public static function checkOwnership($viewId, $userId)
    {
        $views = self::getCustomViews($userId);
        if (isset($views[$viewId]) && $views[$viewId]['owner_id'] == $userId) {
            return true;
        }
        return false;

    }

    /**
     * Set Default
     *
     * @param int $viewId
     * @param int $userId
     * @return void
     */
    public static function setDefault($viewId, $userId)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("DELETE FROM custom_view_default WHERE user_id = ?");
        $stmt->execute(array($userId));
        $stmt = $db->query("INSERT INTO custom_view_default (custom_view_id, user_id) VALUES (?, ?)");
        $stmt->execute(array($viewId, $userId));
    }

    /**
     * Get default view id
     *
     * @param int $userId
     * @return int
     */
    public static function getDefaultViewId($userId)
    {
        static $defaultViews = array();

        if (!isset($defaultView[$userId])) {
            $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare("SELECT custom_view_id FROM custom_view_default WHERE user_id = ?");
            $stmt->execute(array($userId));
            if ($stmt->rowCount()) {
                $row = $stmt->fetch();
                $defaultViews[$userId] = $row['custom_view_id'];
            }
        }
        if (isset($defaultViews[$userId])) {
            return $defaultViews[$userId];
        }
        return 0;
    }

    /**
     * Get Current View Id
     *
     * @param int $userId
     * @return int
     */
    public static function getCurrentView($userId)
    {
        static $currentView = null;

        if (is_null($currentView)) {
            if (isset($_REQUEST['currentView'])) {
                $currentView = $_REQUEST['currentView'];
            } else {
                $views = self::getCustomViews($userId);
                $i = 0;
                foreach ($views as $viewId => $view) {
                    if ($i == 0) {
                        $first = $viewId;
                    }
                    if (self::defaultViewId() == $viewId) {
                        $currentView = $viewId;
                        break;
                    }
                }
                if (is_null($currentView) && isset($first)) {
                    $currentView = $first;
                } elseif (is_null($currentView)) {
                    $currentView = 0;
                }
            }
        }
        return $currentView;
    }

    /**
     * Get Custom Views
     *
     * @param int $userId
     * @return array
     */
    public static function getCustomViews($userId)
    {
        static $customViews = null;

        if (is_null($customViews)) {
            $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare("SELECT cv.custom_view_id, name, owner_id, locked, user_id, position
                FROM custom_views cv, custom_view_user_relation cvur
            	WHERE cv.custom_view_id = cvur.custom_view_id
                AND cvur.user_id = ?
                ORDER BY user_id, name");
            $customViews = array();
            $stmt->execute(array($userId));
            $tmp = array();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $cvid = $row['custom_view_id'];
                $tmp[$cvid] = $row;
            }
            foreach ($tmp as $customViewId => $tab) {
                foreach ($tab as $key => $val) {
                    $customViews[$customViewId][$key] = $val;
                }
            }
        }
        return $customViews;
    }

    /**
     * Add Custom View
     * Returns newly added custom_view_id
     *
     * @param array $params
     * @param int $userId
     * @return int
     */
    public static function addCustomView($params, $userId)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("INSERT INTO custom_views (name, mode, locked, owner_id) VALUES (?, ?, ?, ?)");
        $stmt->execute(array($params['name'], $params['locked'], $params['mode'], $userId));
        $db->query($query);
        $lastId = self::getLastViewId();

        $stmt = $db->prepare("INSERT INTO custom_view_user_relation (custom_view_id, user_id) VALUES (?, ?)");
        $stmt->execute(array($lastId, $userId));
        return $lastId;
    }

    /**
     * Delete Custom View
     *
     * @param int $viewId
     * @param int $userId
     * @return void
     */
    public static function deleteCustomView($viewId, $userId)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("DELETE FROM custom_view_user_relation WHERE custom_view_id = ? AND user_id = ?");
        $stmt->execute(array($viewId, $userId));
        $stmt = $db->prepare("DELETE FROM custom_views WHERE custom_view_id = ? AND owner_id = ?");
        $stmt->execute(array($viewId, $userId));
    }

    /**
     * Update Custom View
     *
     * @param array $params
     * @param int $userId
     * @return int
     */
    public static function updateCustomView($params, $userId)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("UPDATE custom_views 
            SET name = :name, mode = :mode, locked = :locked 
            WHERE custom_view_id = :view_id AND owner_id = :user_id");
        $stmt->bindParam(':name', $params['name']);
        $stmt->bindParam(':mode', $params['mode']);
        $stmt->bindParam(':locked', $params['locked']);
        $stmt->bindParam(':view_id', $params['custom_view_id']);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $params['custom_view_id'];
    }

    /**
     * Update widget positions
     * 
     * @param int $viewId
     * @param string $position
     */
    public static function updatePosition($viewId, $position)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("UPDATE custom_views SET position = ? WHERE custom_view_id = ?");
        $stmt->execute(array($position, $viewId));
    }
}

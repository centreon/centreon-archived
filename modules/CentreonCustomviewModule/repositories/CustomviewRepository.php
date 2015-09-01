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

namespace CentreonCustomview\Repository;

use Centreon\Internal\Exception;
use Centreon\Internal\Di;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */

/**
 * Class for managing widgets
 */
class CustomviewRepository
{
    /**
     * Comparators
     */
    const EQUAL = 1;
    const NOT_EQUAL = 2;
    const CONTAINS = 3;
    const NOT_CONTAINS = 4;
    const GREATER = 5;
    const GREATER_EQUAL = 6;
    const LESSER = 7;
    const LESSER_EQUAL = 8;

    /**
     * Return last inserted view id
     *
     * @return int
     * @throws \Centreon\Internal\Exception
     */
    protected static function getLastViewId()
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("SELECT MAX(custom_view_id) as last_id FROM cfg_custom_views");
        $stmt->execute();
        if ($stmt->rowCount()) {
            $row = $stmt->fetch();
            return $row['last_id'];
        }
        throw new Exception('No view inserted.');
    }

    /**
     * Get all the filters that are used by widgets that present in the view
     *
     * @param int $viewId
     * @return array
     */
    public static function getViewFilters($viewId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = "SELECT DISTINCT parameter_name, parameter_code_name
            FROM cfg_widgets_parameters wp, cfg_widgets w
            WHERE wp.widget_model_id = w.widget_model_id
            AND w.custom_view_id = ?
            AND wp.is_filter = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($viewId));
        $result = array();
        while ($row = $stmt->fetch()) {
            $result[$row['parameter_code_name']] = $row['parameter_name'];
        }
        return $result;
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
     * Set default
     *
     * @param int $viewId
     * @param int $userId
     * @return void
     */
    public static function setDefault($viewId, $userId)
    {
        $db = Di::getDefault()->get('db_centreon');
        self::bookmark($viewId, $userId);
        $stmt = $db->prepare("UPDATE cfg_custom_views_users_relations SET is_default = 0 
            WHERE user_id = ?");
        $stmt->execute(array($userId));
        $stmt = $db->prepare("UPDATE cfg_custom_views_users_relations SET is_default = 1 
            WHERE custom_view_id = ?
            AND user_id = ?");
        $stmt->execute(array($viewId, $userId));
    }
    
    /**
     * Bookmark a view
     *
     * @param int $viewId
     * @param int $userId
     * @return void
     */
    public static function bookmark($viewId, $userId)
    {
        $db = Di::getDefault()->get('db_centreon');

        self::unbookmark($viewId, $userId);
        $stmt = $db->prepare("INSERT INTO cfg_custom_views_users_relations (custom_view_id, user_id) VALUES (?, ?)");
        $stmt->execute(array($viewId, $userId));
    }

    /**
     * Unbookmark view
     *
     * @param int $viewId
     * @param int $userId
     */
    public static function unbookmark($viewId, $userId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("DELETE FROM cfg_custom_views_users_relations WHERE custom_view_id = ? AND user_id = ?");
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
            $db = Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare("SELECT custom_view_id 
                FROM cfg_custom_views_users_relations 
                WHERE user_id = ? 
                AND is_default = 1");
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
    public static function getCurrentView($userId, $params)
    {
        static $currentView = null;

        if (is_null($currentView)) {
            if (isset($params['id'])) {
                $currentView = $params['id'];
            } else {
                $views = self::getCustomViewsOfUser($userId);
                $i = 0;
                foreach ($views as $viewId => $view) {
                    if ($i == 0) {
                        $first = $viewId;
                    }
                    if (self::getDefaultViewId($userId) == $viewId) {
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
     * Get custom view data
     *
     * @param int $viewId
     * @return array
     * @throws \Centreon\Internal\Exception
     */
    public static function getCustomViewData($viewId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("SELECT name, mode, locked, owner_id, position
            FROM cfg_custom_views
            WHERE custom_view_id = ?");
        $stmt->execute(array($viewId));
        if ($stmt->rowCount()) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        throw new Exception(sprintf('Could not find view id %s', $viewId));
    }

    /**
     * Get Custom Views for a user
     *
     * @param int $userId
     * @return array
     */
    public static function getCustomViewsOfUser($userId)
    {
        static $customViews = null;

        if (is_null($customViews)) {
            $db = Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare("SELECT cv.custom_view_id, name, owner_id, locked, user_id, position
                FROM cfg_custom_views cv, cfg_custom_views_users_relations cvur
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
    public static function insert($params, $userId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("INSERT INTO cfg_custom_views (name, mode, locked, owner_id) VALUES (?, ?, ?, ?)");
        $stmt->execute(array($params['name'], $params['locked'], $params['mode'], $userId));
        $lastId = self::getLastViewId();

        $stmt = $db->prepare("INSERT INTO cfg_custom_views_users_relations (custom_view_id, user_id) VALUES (?, ?)");
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
    public static function delete($viewId, $userId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("DELETE FROM cfg_custom_views_users_relations WHERE custom_view_id = ? AND user_id = ?");
        $stmt->execute(array($viewId, $userId));
        $stmt = $db->prepare("DELETE FROM cfg_custom_views WHERE custom_view_id = ? AND owner_id = ?");
        $stmt->execute(array($viewId, $userId));
    }

    /**
     * Update Custom View
     *
     * @param array $params
     * @param int $userId
     * @return int
     */
    public static function update($params, $userId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("UPDATE cfg_custom_views 
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
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("UPDATE cfg_custom_views SET position = ? WHERE custom_view_id = ?");
        $stmt->execute(array($position, $viewId));
    }

    /**
     * Get public views
     * 
     * @return array
     */
    public static function getPublicViews()
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare("SELECT custom_view_id, name, mode, locked, owner_id, position 
            FROM cfg_custom_views 
            WHERE mode = 1
            ORDER BY name");
        $stmt->execute();
        $views = array();
        while ($row = $stmt->fetch()) {
            $views[$row['custom_view_id']] = $row;
        }
        return $views;
    }

    /**
     * Return the SQL cmp string
     *
     * @param string $comparator
     * @param mixed $value
     * @return string
     */
    public static function getCmpString($comparator, $value)
    {
        switch ($comparator) {
            case self::EQUAL:
                $cmp = is_numeric($value) ? " = %s " : " = '%s'";
                break;
            case self::NOT_EQUAL:
                $cmp = is_numeric($value) ? " != %s " : " != '%s'";
                break;
            case self::CONTAINS:
                $cmp = " LIKE '%%%s%%' ";
                break;
            case self::NOT_CONTAINS:
                $cmp = " NOT LIKE '%%%s%%' ";
                break;
            case self::GREATER:
                $cmp = " > %d ";
                break;
            case self::GREATER_EQUAL:
                $cmp = " >= %d ";
                break;
            case self::LESSER:
                $cmp = " < %d ";
                break;
            case self::LESSER_EQUAL:
                $cmp = " <= %d ";
                break;
            default:
                throw new Exception(sprintf('Unknown comparator %s', $comparator));
                break;
        } 
        return sprintf($cmp, $value);
    }
}

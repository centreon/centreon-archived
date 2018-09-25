<?php
/**
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';

/**
 * Centreon Custom View Exception
 */
class CentreonCustomViewException extends Exception
{
}

/**
 * Class for managing widgets
 */
class CentreonCustomView
{
    protected $userId;
    protected $userGroups;
    protected $db;
    protected $customViews;
    protected $currentView;
    protected $defaultView;

    /**
     * CentreonCustomView constructor.
     *
     * @param $centreon
     * @param $db
     * @param null $userId
     * @throws Exception
     */
    public function __construct($centreon, $db, $userId = null)
    {
        if (is_null($userId)) {
            $this->userId = $centreon->user->user_id;
        } else {
            $this->userId = $userId;
        }
        $this->db = $db;
        $this->userGroups = array();
        $query = 'SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = :userId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        while ($row = $stmt->fetch()) {
            $this->userGroups[$row['contactgroup_cg_id']] = $row['contactgroup_cg_id'];
        }
        $query2 = 'SELECT custom_view_id FROM custom_view_default WHERE user_id = :userId LIMIT 1';
        $stmt2 = $this->db->prepare($query2);
        $stmt2->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        $dbResult2 = $stmt2->execute();
        if (!$dbResult2) {
            throw new \Exception("An error occured");
        }
        $this->defaultView = 0;
        if ($stmt2->rowCount()) {
            $row = $stmt2->fetch();
            $this->defaultView = $row['custom_view_id'];
        }
        $this->cg = new CentreonContactgroup($db);
    }

    /**
     * @return mixed
     * @throws CentreonCustomViewException
     */
    protected function getLastViewId()
    {
        $query = 'SELECT MAX(custom_view_id) as last_id FROM custom_views';
        $stmt = $this->db->query($query);
        if ($stmt->rowCount()) {
            $row = $stmt->fetch();
            return $row['last_id'];
        } else {
            throw new CentreonCustomViewException('No view inserted.');
        }
    }

    /**
     * Check number of view unlocked and consume
     *
     * @param $userId
     * @param $viewId
     * @return mixed
     * @throws Exception
     */
    public function checkOtherShareViewUnlocked($userId, $viewId)
    {
        $query = 'SELECT COUNT(user_id) as "nbuser" ' .
            'FROM custom_view_user_relation ' .
            'WHERE locked = 0 ' .
            'AND is_consumed = 1 ' .
            'AND user_id <> :userId ' .
            'AND custom_view_id = :viewId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $row = $stmt->fetch();
        return $row['nbuser'];
    }


    /**
     * Check number of view unlocked
     *
     * @param $viewId
     * @return mixed
     * @throws Exception
     */
    public function checkOwnerViewStatus($viewId)
    {
        $query = 'SELECT is_consumed ' .
            'FROM custom_view_user_relation ' .
            'WHERE is_owner = 1 ' .
            'AND custom_view_id = :viewId';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $row = $stmt->fetch();
        return $row['is_consumed'];
    }

    /**
     * Check Permission
     * Checks if user is allowed to modify view
     * Returns true if user can, false otherwise
     *
     * @param int $viewId
     * @return bool
     */
    public function checkPermission($viewId)
    {
        $views = $this->getCustomViews();
        if (!isset($views[$viewId]) || $views[$viewId]['locked']) {
            return false;
        }

        return true;
    }

    /**
     * Check if user is not owner but view shared with him
     *
     * @param int $viewId
     * @return bool
     */
    public function checkSharedPermission($viewId)
    {
        $views = $this->getCustomViews();
        if (!isset($views[$viewId]) || $views[$viewId]['is_owner'] == 1) {
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
     * @return bool
     */
    public function checkOwnership($viewId)
    {
        $views = $this->getCustomViews();
        if (isset($views[$viewId]) && $views[$viewId]['is_owner']) {
            return true;
        }

        return false;
    }

    /**
     * Set Default
     *
     * @param $viewId
     * @throws Exception
     */
    public function setDefault($viewId)
    {
        $query = 'DELETE FROM custom_view_default WHERE user_id = :userId ';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $query2 = 'INSERT INTO custom_view_default (custom_view_id, user_id) VALUES (:viewId, :userId)';
        $stmt2 = $this->db->prepare($query2);
        $stmt2->bindParam(':viewId', $viewId, PDO::PARAM_INT);
        $stmt2->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        $dbResult2 = $stmt2->execute();
        if (!$dbResult2) {
            throw new \Exception("An error occured");
        }
    }

    /**
     * Get default view id
     *
     * @return int
     */
    public function getDefaultViewId()
    {
        return $this->defaultView;
    }

    /**
     * Get Current View Id
     *
     * @return int
     */
    public function getCurrentView()
    {
        if (!isset($this->currentView)) {
            if (isset($_REQUEST['currentView'])) {
                $this->currentView = (int) $_REQUEST['currentView'];
            } else {
                $views = $this->getCustomViews();
                $i = 0;
                foreach ($views as $viewId => $view) {
                    if ($i == 0) {
                        $first = $viewId;
                    }
                    if ($this->defaultView == $viewId) {
                        $this->currentView = $viewId;
                        break;
                    }
                }
                if (!isset($this->currentView) && isset($first)) {
                    $this->currentView = $first;
                } elseif (!isset($this->currentView)) {
                    $this->currentView = 0;
                }
            }
        }

        return $this->currentView;
    }

    /**
     * Get Custom Views
     *
     * @return array
     * @throws Exception
     */
    public function getCustomViews()
    {

        $queryValue = array();
        $cglist = '';

        if (!isset($this->customViews)) {
            $query = 'SELECT cv.custom_view_id, name, layout, is_owner, locked, user_id, usergroup_id, public ' .
                'FROM custom_views cv, custom_view_user_relation cvur ' .
                'WHERE cv.custom_view_id = cvur.custom_view_id ' .
                'AND (cvur.user_id = ? ';
            $queryValue[] = (int)$this->userId;

            if (count($this->userGroups)) {
                foreach ($this->userGroups as $key => $value) {
                    $cglist .= '?, ';
                    $queryValue[] = (int)$value;
                }
                $query .= 'OR cvur.usergroup_id IN (' . rtrim($cglist, ', ') . ')';
            }
            $query .= ') AND is_consumed = 1 ORDER BY user_id, name';
            $this->customViews = array();
            $stmt = $this->db->prepare($query);
            $dbResult = $stmt->execute($queryValue);
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            $tmp = array();
            while ($row = $stmt->fetch()) {
                $cvid = $row['custom_view_id'];
                $tmp[$cvid]['name'] = $row['name'];
                $tmp[$cvid]['public'] = $row['public'];
                if (!isset($tmp[$cvid]['is_owner']) || !$tmp[$cvid]['is_owner'] || $row['user_id']) {
                    $tmp[$cvid]['is_owner'] = $row['is_owner'];
                }
                if (!isset($tmp[$cvid]['locked']) || !$tmp[$cvid]['locked'] || $row['user_id']) {
                    $tmp[$cvid]['locked'] = $row['locked'];
                }
                $tmp[$cvid]['layout'] = $row['layout'];
                $tmp[$cvid]['custom_view_id'] = $row['custom_view_id'];
            }

            foreach ($tmp as $customViewId => $tab) {
                foreach ($tab as $key => $val) {
                    $this->customViews[$customViewId][$key] = $val;
                }
            }
        }

        return $this->customViews;
    }

    /**
     * Add Custom View
     *
     * @param $params
     * @return int
     * @throws Exception
     */
    public function addCustomView($params)
    {
        if (!isset($params['public']) || is_null($params['public'])) {
            $public = 0;
        } else {
            $public = 1;
        }

        $query = 'INSERT INTO custom_views (`name`, `layout`, `public`) ' .
            'VALUES (:viewName, :layout , "' . $public . '")';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':viewName', $params['name'], PDO::PARAM_STR);
        $stmt->bindParam(':layout', $params['layout']['layout'], PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $lastId = $this->getLastViewId();
        $query = 'INSERT INTO custom_view_user_relation (custom_view_id, user_id, locked, is_owner) ' .
            'VALUES (:viewId, :userId, 0, 1)';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':viewId', $lastId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        return $lastId;
    }

    /**
     * @param $viewId
     * @throws Exception
     */
    public function deleteCustomView($viewId)
    {
        //owner
        if ($this->checkOwnership($viewId)) {
            //if not shared view consumed
            if (!$this->checkOtherShareViewUnlocked($this->userId, $viewId)) {
                $query = 'DELETE FROM custom_views WHERE custom_view_id = :viewId';
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
                $dbResult = $stmt->execute();
                if (!$dbResult) {
                    throw new \Exception("An error occured");
                }
            } else {
                $query = 'DELETE FROM custom_view_user_relation ' .
                    'WHERE custom_view_id = :viewId ' .
                    'AND (is_consumed = 0 OR is_owner = 1)';
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
                $dbResult = $stmt->execute();
                if (!$dbResult) {
                    throw new \Exception("An error occured");
                }
            }
            //other
        } else {
            // if owner consumed = 0 -> delete
            if ($this->checkOwnerViewStatus($viewId) == 0) {
                //if not other shared view consumed, delete all
                if (!$this->checkOtherShareViewUnlocked($viewId, $this->userId)) {
                    $query = 'DELETE FROM custom_views WHERE custom_view_id = :viewId ';
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                    //if shared view consumed, delete for me
                } else {
                    $query = 'DELETE FROM custom_view_user_relation ' .
                        'WHERE user_id = :userId ' .
                        'AND custom_view_id = :viewId';
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
                    $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                }
                //if owner not delete
            } else {
                $query = 'UPDATE custom_view_user_relation SET is_consumed = 0 ' .
                    'WHERE custom_view_id = :viewId AND user_id = :userId ';
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
                $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
                $dbResult = $stmt->execute();
                if (!$dbResult) {
                    throw new \Exception("An error occured");
                }
            }
        }
    }

    /**
     * Update Custom View
     *
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function updateCustomView($params)
    {
        if ($this->checkPermission($params['custom_view_id']) === true) {
            $public = 0;
            if (isset($params['public'])) {
                $public = $params['public'];
            }
            $query = 'UPDATE custom_views SET `name` = :viewName, `layout` = :layout, `public` = :typeView ' .
                'WHERE `custom_view_id` = :viewId';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewName', $params['name'], PDO::PARAM_STR);
            $stmt->bindParam(':layout', $params['layout']['layout'], PDO::PARAM_STR);
            $stmt->bindParam(':typeView', $public, PDO::PARAM_INT);
            $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        }
        return $params['custom_view_id'];
    }


    /**
     * Copy Preferences
     *
     * @param $viewId
     * @param null $userId
     * @param null $userGroupId
     * @return int|null
     * @throws Exception
     */
    protected function copyPreferences($viewId, $userId = null, $userGroupId = null)
    {
        if (isset($userId) && $userId) {
            $query = 'REPLACE INTO widget_preferences (widget_view_id, parameter_id, preference_value, user_id) ' .
                '(SELECT wp.widget_view_id, wp.parameter_id, wp.preference_value, :userId ' .
                'FROM widget_preferences wp, widget_views wv ' .
                'WHERE wv.custom_view_id = :viewId ' .
                'AND wv.widget_view_id = wp.widget_view_id ' .
                'AND wp.user_id = :widgetUser)';

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
            $stmt->bindParam(':widgetUser', $this->userId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        } elseif (isset($userGroupId) && $userGroupId) {
            if (!is_numeric($userGroupId)) {
                $userGroupId = $this->cg->insertLdapGroup($userGroupId);
            }
            $query = 'SELECT contact_contact_id FROM contactgroup_contact_relation WHERE contactgroup_cg_id = :id';

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $userGroupId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            while ($row = $stmt->fetch()) {
                $query2 = 'REPLACE INTO widget_preferences (widget_view_id, parameter_id, preference_value, user_id) ' .
                    '(SELECT wp.widget_view_id, wp.parameter_id, wp.preference_value, :contactId ' .
                    'FROM widget_preferences wp, widget_views wv ' .
                    'WHERE wv.custom_view_id = :viewId ' .
                    'AND wv.widget_view_id = wp.widget_view_id ' .
                    'AND wp.user_id = :userId)';
                $stmt2 = $this->db->prepare($query2);
                $stmt2->bindParam(':contactId', $row['contact_contact_id'], PDO::PARAM_INT);
                $stmt2->bindParam(':viewId', $viewId, PDO::PARAM_INT);
                $stmt2->bindParam(':userId', $this->userId, PDO::PARAM_INT);
                $dbResult2 = $stmt2->execute();
                if (!$dbResult2) {
                    throw new \Exception("An error occured");
                }
            }
        }
        if (!is_null($userId)) {
            return $userId;
        } elseif (!is_null($userGroupId)) {
            return $userGroupId;
        }
        return null;
    }

    /**
     * @param $customViewId
     * @param null $userId
     * @return null
     * @throws Exception
     */
    public function syncCustomView($customViewId, $userId = null)
    {
        if (!$this->checkOwnership($customViewId)) {
            return null;
        }

        if (!is_null($userId)) {
            $this->copyPreferences($customViewId, $userId);
        } else {
            $query = 'SELECT user_id, usergroup_id FROM custom_view_user_relation ' .
                'WHERE custom_view_id = :viewId ' .
                'AND locked = 1';

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            while ($row = $stmt->fetch()) {
                $this->copyPreferences(
                    $customViewId,
                    $row['user_id'],
                    $row['usergroup_id']
                );
            }
        }
    }

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function loadCustomView($params)
    {
        $isLocked = 1;
        $update = false;
        $query = 'SELECT custom_view_id, locked, user_id, usergroup_id ' .
            'FROM custom_view_user_relation ' .
            'WHERE custom_view_id = :viewLoad ' .
            'AND ' .
            '(user_id = :userId ' .
            'OR usergroup_id IN ( ' .
            'SELECT contactgroup_cg_id FROM contactgroup_contact_relation ' .
            'WHERE contact_contact_id = :userId ' .
            ') ' .
            ') ORDER BY user_id DESC';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':viewLoad', $params['viewLoad'], PDO::PARAM_INT);
        $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        if ($row = $stmt->fetch()) {
            if ($row['locked'] == "0") {
                $isLocked = $row['locked'];
            }
            if (!is_null($row['user_id']) && $row['user_id'] > 0 && is_null($row['usergroup_id'])) {
                $update = true;
            }
        }

        if ($update) {
            $query = 'UPDATE custom_view_user_relation SET is_consumed=1 WHERE ' .
                ' custom_view_id = :viewLoad AND user_id = :userId';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewLoad', $params['viewLoad'], PDO::PARAM_INT);
            $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        } else {
            $query = 'INSERT INTO custom_view_user_relation (custom_view_id,user_id,is_owner,locked,is_share) ' .
                'VALUES (:viewLoad, :userId, 0, :isLocked, 1)';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewLoad', $params['viewLoad'], PDO::PARAM_INT);
            $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
            $stmt->bindParam(':isLocked', $isLocked, PDO::PARAM_INT);
        }
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        return $params['viewLoad'];
    }


    /**
     * @param $params
     * @param $userId
     * @throws Exception
     */
    public function shareCustomView($params, $userId)
    {
        global $centreon;
        $queryValue = array();

        if ($this->checkPermission($params['custom_view_id'])) {
            // share with users
            $sharedUsers = array();
            $params['lockedUsers'] = isset($params['lockedUsers']) ? $params['lockedUsers'] : array();
            foreach ($params['lockedUsers'] as $lockedUser) {
                if ($lockedUser != $centreon->user->user_id) {
                    $sharedUsers[$lockedUser] = 1;
                }
            }
            $params['unlockedUsers'] = isset($params['unlockedUsers']) ? $params['unlockedUsers'] : array();
            foreach ($params['unlockedUsers'] as $unlockedUser) {
                if ($unlockedUser != $centreon->user->user_id) {
                    $sharedUsers[$unlockedUser] = 0;
                }
            }

            $query = 'SELECT user_id ' .
                'FROM custom_view_user_relation ' .
                'WHERE custom_view_id = :viewId ' .
                'AND user_id <> :userId ' .
                'AND usergroup_id IS NULL ';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            $oldSharedUsers = array();
            while ($row = $stmt->fetch()) {
                $oldSharedUsers[$row['user_id']] = 1;
            }

            foreach ($sharedUsers as $sharedUserId => $locked) {
                if (isset($oldSharedUsers[$sharedUserId])) {
                    $query = 'UPDATE custom_view_user_relation ' .
                        'SET is_share = 1, locked = :isLocked ' .
                        'WHERE user_id = :userId ' .
                        'AND custom_view_id = :viewId';
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':isLocked', $locked, PDO::PARAM_INT);
                    $stmt->bindParam(':userId', $sharedUserId, PDO::PARAM_INT);
                    $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                    unset($oldSharedUsers[$sharedUserId]);
                } else {
                    $query = 'INSERT INTO custom_view_user_relation ' .
                        '(custom_view_id, user_id, locked, is_consumed, is_share ) ' .
                        'VALUES ( :viewId, :sharedUser, :isLocked, 0, 1) ';
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
                    $stmt->bindParam(':sharedUser', $sharedUserId, PDO::PARAM_INT);
                    $stmt->bindParam(':isLocked', $locked, PDO::PARAM_INT);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                }
                $this->copyPreferences($params['custom_view_id'], $sharedUserId);
            }

            $queryValue[] = (int)$params['custom_view_id'];
            $userIdKey = '';

            if (!empty($oldSharedUsers)) {
                foreach ($oldSharedUsers as $k => $v) {
                    $userIdKey .= '?,';
                    $queryValue[] = (int)$k;
                }
                $userIdKey = rtrim($userIdKey, ',');
            } else {
                $userIdKey .= '""';
            }
            $query = 'DELETE FROM custom_view_user_relation ' .
                'WHERE custom_view_id = ? ' .
                'AND user_id IN (' . $userIdKey . ') ';
            $stmt = $this->db->prepare($query);
            $dbResult = $stmt->execute($queryValue);
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            // share with user groups
            $sharedUsergroups = array();
            $params['lockedUsergroups'] = isset($params['lockedUsergroups']) ? $params['lockedUsergroups'] : array();
            foreach ($params['lockedUsergroups'] as $lockedUsergroup) {
                $sharedUsergroups[$lockedUsergroup] = 1;
            }
            $params['unlockedUsergroups'] = isset($params['unlockedUsergroups']) ?
                $params['unlockedUsergroups'] : array();
            foreach ($params['unlockedUsergroups'] as $unlockedUsergroup) {
                $sharedUsergroups[$unlockedUsergroup] = 0;
            }

            $query = 'SELECT usergroup_id ' .
                'FROM custom_view_user_relation ' .
                'WHERE custom_view_id = :viewId ' .
                'AND user_id IS NULL ';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            $oldSharedUsergroups = array();
            while ($row = $stmt->fetch()) {
                $oldSharedUsergroups[$row['usergroup_id']] = 1;
            }

            foreach ($sharedUsergroups as $sharedUsergroupId => $locked) {
                if (isset($oldSharedUsergroups[$sharedUsergroupId])) {
                    $query = 'UPDATE custom_view_user_relation ' .
                        'SET is_share = 1, locked = :isLocked ' .
                        'WHERE usergroup_id = :sharedUser ' .
                        'AND custom_view_id = :viewId';

                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':isLocked', $locked, PDO::PARAM_INT);
                    $stmt->bindParam(':sharedUser', $sharedUsergroupId, PDO::PARAM_INT);
                    $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                    unset($oldSharedUsergroups[$sharedUsergroupId]);
                } else {
                    $query = 'INSERT INTO custom_view_user_relation ' .
                        '(custom_view_id, usergroup_id, locked, is_consumed, is_share ) ' .
                        'VALUES (:viewId, :sharedUser, :isLocked, 0, 1) ';
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
                    $stmt->bindParam(':sharedUser', $sharedUsergroupId, PDO::PARAM_INT);
                    $stmt->bindParam(':isLocked', $locked, PDO::PARAM_INT);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                }
                $this->copyPreferences($params['custom_view_id'], null, $sharedUsergroupId);
            }

            $queryValue2 = array();
            $queryValue2[] = (int)$params['custom_view_id'];
            $userGroupIdKey = '';
            if (!empty($oldSharedUsergroups)) {
                foreach ($oldSharedUsergroups as $k => $v) {
                    $userGroupIdKey .= '?,';
                    $queryValue2[] = (int)$k;
                }
                $userGroupIdKey = rtrim($userGroupIdKey, ',');
            } else {
                $userGroupIdKey .= '""';
            }

            $query = 'DELETE FROM custom_view_user_relation ' .
                'WHERE custom_view_id = ? ' .
                'AND usergroup_id IN (' . $userGroupIdKey . ') ';
            $stmt = $this->db->prepare($query);
            $dbResult = $stmt->execute($queryValue2);
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        }
    }

    /**
     * Get Layout
     *
     * @param int $viewId
     * @return string
     * @throws CentreonCustomViewException
     */
    public function getLayout($viewId)
    {
        $views = $this->getCustomViews();
        if (isset($views[$viewId]) && isset($views[$viewId]['layout'])) {
            return $views[$viewId]['layout'];
        } else {
            throw new CentreonCustomViewException(sprintf('No layout found for view_id : %s', $viewId));
        }
    }

    /**
     * @param $viewId
     * @return array
     * @throws Exception
     */
    public function getUsersFromViewId($viewId)
    {
        static $userList;

        if (!isset($userList)) {
            $userList = array();
            $query = 'SELECT contact_name, user_id, locked ' .
                'FROM contact c, custom_view_user_relation cvur ' .
                'WHERE c.contact_id = cvur.user_id ' .
                'AND cvur.custom_view_id = :viewId ' .
                'AND cvur.is_share = 1 ' .
                'ORDER BY contact_name';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            while ($row = $stmt->fetch()) {
                $userList[$row['user_id']]['contact_name'] = $row['contact_name'];
                $userList[$row['user_id']]['user_id'] = $row['user_id'];
                $userList[$row['user_id']]['locked'] = $row['locked'];
            }
        }
        return $userList;
    }

    /**
     * @param $viewId
     * @return array
     * @throws Exception
     */
    public function getUsergroupsFromViewId($viewId)
    {
        static $usergroupList;

        if (!isset($usergroupList)) {
            $usergroupList = array();
            $query = 'SELECT cg_name, usergroup_id, locked ' .
                'FROM contactgroup cg, custom_view_user_relation cvur ' .
                'WHERE cg.cg_id = cvur.usergroup_id ' .
                'AND cvur.custom_view_id = :viewId ' .
                'AND cvur.is_share = 1 ' .
                ' ORDER BY cg_name';

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            while ($row = $stmt->fetch()) {
                $usergroupList[$row['usergroup_id']]['cg_name'] = $row['cg_name'];
                $usergroupList[$row['usergroup_id']]['usergroup_id'] = $row['usergroup_id'];
                $usergroupList[$row['usergroup_id']]['locked'] = $row['locked'];
            }
        }
        return $usergroupList;
    }

    /**
     * @param $params
     * @throws Exception
     */
    public function removeUserFromView($params)
    {

        $query = 'SELECT is_public ' .
            'FROM custom_view_user_relation ' .
            'WHERE user_id = :userId ' .
            'AND custom_view_id = :viewId';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userId', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        while ($row = $stmt->fetch()) {
            $public = $row['is_public'];
        }

        if ($public == 0) {
            $query = 'DELETE FROM custom_view_user_relation ' .
                'WHERE user_id = :userId ' .
                'AND custom_view_id = :viewId';

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $params['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        } else {
            $query = 'UPDATE custom_view_user_relation SET is_share = 0, locked = 1 ' .
                'WHERE user_id = :userId ' .
                'AND custom_view_id = :viewId';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $params['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        }
    }

    /**
     * @param $params
     * @throws Exception
     */
    public function removeUsergroupFromView($params)
    {
        $query = 'DELETE FROM custom_view_user_relation ' .
            'WHERE usergroup_id = :groupId ' .
            'AND is_public <> 1 ' .
            'AND custom_view_id = :viewId';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':groupId', $params['usergroup_id'], PDO::PARAM_INT);
        $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
    }

    /**
     * @param $centreon
     * @param $db
     * @param $contactId
     * @return null
     * @throws Exception
     */
    public static function syncContactGroupCustomView($centreon, $db, $contactId)
    {
        $contactgroups = CentreonContact::getContactGroupsFromContact($db, $contactId);
        if (!count($contactgroups)) {
            return null;
        }
        $queryValue = array();
        $cgString = '';
        foreach ($contactgroups as $k => $v) {
            $cgString .= '?,';
            $queryValue[] = (int)$k;
        }
        $cgString = rtrim($cgString, ',');
        $query = 'SELECT c1.custom_view_id, c1.user_id as owner_id, c2.usergroup_id ' .
            'FROM custom_view_user_relation c1, custom_view_user_relation c2 ' .
            'WHERE c1.custom_view_id = c2.custom_view_id ' .
            'AND c1.is_owner = 1 ' .
            'AND c2.usergroup_id in (' . $cgString . ') ' .
            'GROUP BY custom_view_id';
        $stmt = $db->prepare($query);
        $dbResult = $stmt->execute($queryValue);
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        while ($row = $stmt->fetch()) {
            $customView = new CentreonCustomView($centreon, $db, (int)$row['owner_id']);
            $customView->syncCustomView((int)$row['custom_view_id'], $contactId);
            unset($customView);
        }
    }
}

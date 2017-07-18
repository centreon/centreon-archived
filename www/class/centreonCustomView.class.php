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

;

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
     * Constructor
     *
     * @param Centreon $centreon
     * @param CentreonDB $db
     * @param int $userId
     * @return void
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
        $query = 'SELECT contactgroup_cg_id ' .
            'FROM contactgroup_contact_relation ' .
            'WHERE contact_contact_id = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$this->userId));

        while ($row = $res->fetchRow()) {
            $this->userGroups[$row['contactgroup_cg_id']] = $row['contactgroup_cg_id'];
        }

        $query = 'SELECT custom_view_id FROM custom_view_default WHERE user_id = ? LIMIT 1';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$this->userId));

        $this->defaultView = 0;
        if ($res->numRows()) {
            $row = $res->fetchRow();
            $this->defaultView = $row['custom_view_id'];
        }
        $this->cg = new CentreonContactgroup($db);
    }

    /**
     * Return last inserted view id
     *
     * @return int
     * @throws CentreonCustomViewException
     */
    protected function getLastViewId()
    {
        $query = "SELECT MAX(custom_view_id) as last_id FROM custom_views";
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            return $row['last_id'];
        } else {
            throw new CentreonCustomViewException('No view inserted.');
        }
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
     * @param int $viewId
     * @return void
     */
    public function setDefault($viewId)
    {
        $query = 'DELETE FROM custom_view_default WHERE user_id = ? ';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$this->userId));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }
        $query2 = 'INSERT INTO custom_view_default (custom_view_id, user_id) VALUES (?, ?)';
        $stmt2 = $this->db->prepare($query2);
        $res2 = $this->db->execute($stmt2, array((int)$viewId, (int)$this->userId));
        if (PEAR::isError($res2)) {
            throw new Exception('Bad Request');
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
                $this->currentView = $_REQUEST['currentView'];
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
            $res = $this->db->execute($stmt, $queryValue);
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }

            $tmp = array();
            while ($row = $res->fetchRow()) {
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
     * Returns newly added custom_view_id
     *
     * @param array $params
     * @return int
     */
    public function addCustomView($params)
    {
        if (!isset($params['public']) || is_null($params['public'])) {
            $public = 0;
        } else {
            $public = 1;
        }
        $query = 'INSERT INTO custom_views (name, layout, public) VALUES (?, ? , "' . $public . '")';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((string)$params['name'], (string)$params['layout']['layout']));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }
        $lastId = $this->getLastViewId();

        $query = 'INSERT INTO custom_view_user_relation (custom_view_id, user_id, locked, is_owner) ' .
            'VALUES (?, ?, 0, 1)';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$lastId, (int)$this->userId));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }
        return $lastId;
    }

    /**
     * Delete Custom View
     *
     * @param int $viewId
     * @return void
     */
    public function deleteCustomView($viewId)
    {
        if ($this->checkPermission($viewId) === true) {
            $query = 'DELETE FROM custom_views WHERE custom_view_id = ?';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$viewId));
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }
        } else if ($this->checkSharedPermission($viewId) === true) {
            $query = "UPDATE custom_view_user_relation SET is_consumed = 0 WHERE custom_view_id = ? AND user_id = ?";
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$viewId, (int)$this->userId));
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }
        }
    }

    /**
     * Update Custom View
     *
     * @param array $params
     * @return int
     */
    public function updateCustomView($params)
    {
        if ($this->checkPermission($params['custom_view_id']) === true) {
            $public = 0;
            if (isset($params['public'])) {
                $public = $params['public'];
            }
            $query = 'UPDATE custom_views SET name = ?, layout = ?, public = ? WHERE custom_view_id = ?';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute(
                $stmt,
                array(
                    (string)$params['name'],
                    (string)$params['layout']['layout'],
                    (int)$public,
                    (int)$params['custom_view_id'],
                )
            );
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }
        }
        return $params['custom_view_id'];
    }

    /**
     * Copy Preferences
     *
     * @param int $viewId
     * @param int $userId
     * @param int $userGroupId
     */
    protected function copyPreferences($viewId, $userId = null, $userGroupId = null)
    {
        if (isset($userId) && $userId) {
            $query = 'REPLACE INTO widget_preferences (widget_view_id, parameter_id, preference_value, user_id) ' .
                '(SELECT wp.widget_view_id, wp.parameter_id, wp.preference_value, ? ' .
                'FROM widget_preferences wp, widget_views wv ' .
                'WHERE wv.custom_view_id = ? ' .
                'AND wv.widget_view_id = wp.widget_view_id ' .
                'AND wp.user_id = ?)';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$userId, (int)$viewId, (int)$this->userId));
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }
        } elseif (isset($userGroupId) && $userGroupId) {
            if (!is_numeric($userGroupId)) {
                $userGroupId = $this->cg->insertLdapGroup($userGroupId);
            }
            $query = 'SELECT contact_contact_id ' .
                'FROM contactgroup_contact_relation ' .
                'WHERE contactgroup_cg_id = ?';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$userGroupId));

            while ($row = $res->fetchRow()) {
                $query2 = 'REPLACE INTO widget_preferences (widget_view_id, parameter_id, preference_value, user_id) ' .
                    '(SELECT wp.widget_view_id, wp.parameter_id, wp.preference_value, ? ' .
                    'FROM widget_preferences wp, widget_views wv ' .
                    'WHERE wv.custom_view_id = ? ' .
                    'AND wv.widget_view_id = wp.widget_view_id ' .
                    'AND wp.user_id = ?)';
                $stmt2 = $this->db->prepare($query2);
                $res2 = $this->db->execute(
                    $stmt2,
                    array((int)$row['contact_contact_id'], (int)$viewId, (int)$this->userId)
                );
                if (PEAR::isError($res2)) {
                    throw new Exception('Bad Request');
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
     * Sync custom view with locked users
     *
     * @param int custom_view_id
     * @param int $userId
     * @return void
     */
    public function syncCustomView($custom_view_id, $userId = null)
    {
        if (!$this->checkOwnership($custom_view_id)) {
            return null;
        }

        if (!is_null($userId)) {
            $this->copyPreferences($custom_view_id, $userId);
        } else {

            $query = 'SELECT user_id, usergroup_id FROM custom_view_user_relation ' .
                'WHERE custom_view_id = ? ' .
                'AND locked = 1';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$custom_view_id));

            while ($row = $res->fetchRow()) {
                $this->copyPreferences(
                    $custom_view_id,
                    $row['user_id'],
                    $row['usergroup_id']
                );
            }
        }
    }

    public function loadCustomView($params)
    {
        if (isset($params['viewLoad']) && is_numeric($params['viewLoad']) && $params['viewLoad'] != -1) {
            $is_owner = 0;
            $is_consumed = 1;
            $query = "SELECT custom_view_id, user_id, locked, is_owner, is_consumed " .
                "FROM custom_view_user_relation " .
                "WHERE custom_view_id = ? " .
                "AND user_id = ?";
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$params['viewLoad'], (int)$this->userId));

            if ($row = $res->fetchRow()) {
                $is_owner = $row['is_owner'];
            }

            $query = "delete from custom_view_user_relation where custom_view_id = ? and user_id = ?";
            $stmt = $this->db->prepare($query);
            $this->db->execute($stmt, array((int)$params['viewLoad'], (int)$this->userId));

            $query2 = "insert into custom_view_user_relation (custom_view_id,user_id,is_owner,locked) " .
                "VALUES (?, ?, ?, 1)";
            $stmt2 = $this->db->prepare($query2);
            $this->db->execute($stmt2, array((int)$params['viewLoad'], (int)$this->userId, (int)$is_owner));

            return $params['viewLoad'];
        } else if (isset($params['viewLoadShare']) && is_numeric($params['viewLoadShare']) && $params['viewLoadShare'] != -1) {

            $query = "update custom_view_user_relation set is_consumed = 1 " .
                "where custom_view_id = ? and user_id = ?";
            $stmt = $this->db->prepare($query);
            $this->db->execute($stmt, array((int)$params['viewLoadShare'], (int)$this->userId));

            return $params['viewLoadShare'];
        }
    }


    /**
     * Share Custom View
     *
     * @param array $params
     * @return void
     */
    public function shareCustomView($params)
    {
        $queryValue = array();
        if ($this->checkPermission($params['custom_view_id'])) {
            // share with users
            $str = "";
            if (isset($params['user_id']) && is_array($params['user_id'])) {
                foreach ($params['user_id'] as $userId) {
                    if ($str != "") {
                        $str .= ", ";
                    }
                    $str .= "(?, ?, ?, 0)";
                    $queryValue[] = (int)$params['custom_view_id'];
                    $queryValue[] = (int)$userId;
                    $queryValue[] = (int)$params['locked']['locked'];
                    $this->copyPreferences($params['custom_view_id'], $userId);
                }
            }

            if ($str != "") {
                $query="REPLACE INTO custom_view_user_relation (custom_view_id, user_id, locked, is_consumed) VALUES $str";
                $stmt = $this->db->prepare($query);
                $this->db->execute($stmt, $queryValue);
            }

            // share with user groups
            $str = "";
            if (isset($params['usergroup_id']) && is_array($params['usergroup_id'])) {
                $queryValue = array();

                foreach ($params['usergroup_id'] as $usergroupId) {
                    if ($str != "") {
                        $str .= ", ";
                    }
                    $usergroupId = $this->copyPreferences($params['custom_view_id'], null, $usergroupId);
                    $str .= "(?, ?, ?, 0)";

                    $queryValue[] = (int)$params['custom_view_id'];
                    $queryValue[] = (int)$usergroupId;
                    $queryValue[] = (int)$params['locked']['locked'];
                }
            }
            if ($str != "") {
                $query="REPLACE INTO custom_view_user_relation (custom_view_id, usergroup_id, locked, is_consumed) VALUES $str";
                $stmt = $this->db->prepare($query);
                $this->db->execute($stmt, $queryValue);
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
     * Get Users From View Id
     *
     * @param $viewId
     * @return array
     */
    public function getUsersFromViewId($viewId)
    {
        static $userList;

        if (!isset($userList)) {
            $userList = array();
            $query = "SELECT contact_name, user_id, locked
            		  FROM contact c, custom_view_user_relation cvur
            		  WHERE c.contact_id = cvur.user_id
            		  AND cvur.custom_view_id  = ?
                      ORDER BY contact_name";
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$viewId));

            while ($row = $res->fetchRow()) {
                $userList[$row['user_id']]['contact_name'] = $row['contact_name'];
                $userList[$row['user_id']]['user_id'] = $row['user_id'];
                $userList[$row['user_id']]['locked'] = $row['locked'];
            }
        }
        return $userList;
    }

    /**
     * Get Usergroups From View Id
     *
     * @param $viewId
     * @return array
     */
    public function getUsergroupsFromViewId($viewId)
    {
        static $usergroupList;

        if (!isset($usergroupList)) {
            $usergroupList = array();
            $query = "SELECT cg_name, usergroup_id, locked
            		  FROM contactgroup cg, custom_view_user_relation cvur
            		  WHERE cg.cg_id = cvur.usergroup_id
            		  AND cvur.custom_view_id  = ?
                      ORDER BY cg_name";

            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$viewId));

            while ($row = $res->fetchRow()) {
                $usergroupList[$row['usergroup_id']]['cg_name'] = $row['cg_name'];
                $usergroupList[$row['usergroup_id']]['usergroup_id'] = $row['usergroup_id'];
                $usergroupList[$row['usergroup_id']]['locked'] = $row['locked'];
            }
        }
        return $usergroupList;
    }

    /**
     * Remove User From View
     *
     * @param array $params
     * @return void
     */
    public function removeUserFromView($params)
    {
        $query = "DELETE FROM custom_view_user_relation
        		  WHERE user_id = ?
        		  AND custom_view_id = ? ";

        $stmt = $this->db->prepare($query);
        $this->db->execute($stmt, array((int)$params['user_id'], (int)$params['custom_view_id']));
    }

    /**
     * Remove Usergroup From View
     *
     * @param array $params
     * @return void
     */
    public function removeUsergroupFromView($params)
    {
        $query = "DELETE FROM custom_view_user_relation
        		  WHERE usergroup_id = ?
        		  AND custom_view_id = ? ";
        $stmt = $this->db->prepare($query);
        $this->db->execute($stmt, array((int)$params['usergroup_id'], (int)$params['custom_view_id']));

    }

    /**
     * Remove User From View
     *
     * @param array $params
     * @return void
     */
    public function removeViewForAllUser($params)
    {
        $query = "DELETE FROM custom_view_user_relation
        		  WHERE user_id <> ?
        		  AND custom_view_id = ? ";

        $stmt = $this->db->prepare($query);
        $this->db->execute($stmt, array((int)$params['user_id'], (int)$params['custom_view_id']));

    }

    /**
     * This is called when a contact is added into a contact group
     *
     * @param CentreonDB $db
     * @param int $contactId
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

        $query = "SELECT c1.custom_view_id, c1.user_id as owner_id, c2.usergroup_id 
            FROM custom_view_user_relation c1, custom_view_user_relation c2  
            WHERE c1.custom_view_id = c2.custom_view_id 
            AND c1.is_owner = 1 
            AND c2.usergroup_id in ($cgString) 
            GROUP BY custom_view_id";

        $stmt = $db->prepare($query);
        $res = $db->execute($stmt, $queryValue);

        while ($row = $res->fetchRow()) {
            $customView = new CentreonCustomView($centreon, $db, $row['owner_id']);
            $customView->syncCustomView($row['custom_view_id'], $contactId);
            unset($customView);
        }
    }


}

<?php

/**
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

    public const TOPOLOGY_PAGE_EDIT_VIEW = 10301;
    public const TOPOLOGY_PAGE_SHARE_VIEW = 10302;
    public const TOPOLOGY_PAGE_SET_WIDGET_PREFERENCES = 10303;
    public const TOPOLOGY_PAGE_ADD_WIDGET = 10304;
    public const TOPOLOGY_PAGE_DELETE_WIDGET = 10304;
    public const TOPOLOGY_PAGE_SET_ROTATE = 10305;
    public const TOPOLOGY_PAGE_DELETE_VIEW = 10306;
    public const TOPOLOGY_PAGE_ADD_VIEW = 10307;
    public const TOPOLOGY_PAGE_SET_DEFAULT_VIEW = 10308;

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
     * Checks if user is allowed to modify a view
     * Returns true if user can, false otherwise
     *
     * @param int $viewId
     * @return bool
     */
    public function checkPermission(int $viewId): bool
    {
        $views = $this->getCustomViews();
        if (!isset($views[$viewId]) || $views[$viewId]['locked']) {
            return false;
        }

        return true;
    }

    /**
     * checkUserActions - Check wether or not the user
     * can do the provided action
     *
     * @param  string $action
     * @return bool
     */
    public function checkUserActions(string $action): bool
    {
        /* associate the called action with the toplogy page.
         * Special behaviour for the deleteWidget action which does not have any possible configuration in ACL Menus.
         * We do consider through that if user can create a widget then he also can delete it.
         * Also "defaultEditMode" action has no link with ACL this action is authorized for all.
         */
        if ($action == 'defaultEditMode') {
            return true;
        }
        $associativeActions = [
            'edit' => self::TOPOLOGY_PAGE_EDIT_VIEW,
            'share' => self::TOPOLOGY_PAGE_SHARE_VIEW,
            'setPreferences' => self::TOPOLOGY_PAGE_SET_WIDGET_PREFERENCES,
            'addWidget' => self::TOPOLOGY_PAGE_ADD_WIDGET,
            'deleteWidget' => self::TOPOLOGY_PAGE_DELETE_WIDGET,
            'setRotate' => self::TOPOLOGY_PAGE_SET_ROTATE,
            'deleteView' => self::TOPOLOGY_PAGE_DELETE_VIEW,
            'add' => self::TOPOLOGY_PAGE_ADD_VIEW,
            'setDefault' => self::TOPOLOGY_PAGE_SET_DEFAULT_VIEW
        ];

        // retrieving menu access rights of the current user.
        $acls = new CentreonACL($this->userId);
        $userCustomViewActions = $acls->getTopology();
        if (array_key_exists($associativeActions[$action], $userCustomViewActions)) {
            return true;
        }
        return false;
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
                $this->currentView = (int)$_REQUEST['currentView'];
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

        return (int)$this->currentView;
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
     * @param string $viewName
     * @param string $layout
     * @param ?int $publicView
     * @param bool $authorized
     * @return int $lastId
     * @throws Exception
     */
    public function addCustomView(string $viewName, string $layout, ?int $publicView, bool $authorized): int
    {
        if (!$authorized) {
            throw new CentreonCustomViewException('You are not allowed to add a custom view.');
        }
        $public = 1;
        if (!$publicView) {
            $public = 0;
        }

        $query = 'INSERT INTO custom_views (`name`, `layout`, `public`) ' .
            'VALUES (:viewName, :layout , "' . $public . '")';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':viewName', $viewName, PDO::PARAM_STR);
        $stmt->bindParam(':layout', $layout, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $lastId = $this->getLastViewId();
        $query = 'INSERT INTO custom_view_user_relation (custom_view_id, user_id, locked, is_owner) ' .
            'VALUES (:lastId, :userId, 0, 1)';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':lastId', $lastId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        return $lastId;
    }

    /**
     * @param $customViewId
     * @param bool $authorized
     * @throws Exception
     */
    public function deleteCustomView($customViewId, bool $authorized)
    {
        if (!$authorized) {
            throw new CentreonCustomViewException('You are not allowed to delete the view');
        }
        //owner
        if ($this->checkOwnership($customViewId)) {
            //if not shared view consumed
            if (!$this->checkOtherShareViewUnlocked($this->userId, $customViewId)) {
                $query = 'DELETE FROM custom_views WHERE custom_view_id = :viewId';
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
                $dbResult = $stmt->execute();
            } else {
                $query = 'DELETE FROM custom_view_user_relation ' .
                    'WHERE custom_view_id = :viewId ' .
                    'AND (is_consumed = 0 OR is_owner = 1)';
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
                $dbResult = $stmt->execute();
            }
            //other
        } else {
            // if owner consumed = 0 -> delete
            if ($this->checkOwnerViewStatus($customViewId) == 0) {
                //if not other shared view consumed, delete all
                if (!$this->checkOtherShareViewUnlocked($customViewId, $this->userId)) {
                    $query = 'DELETE FROM custom_views WHERE custom_view_id = :viewId';
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
                    $stmt->execute();
                    //if shared view consumed, delete for me
                } else {
                    $query = 'DELETE FROM custom_view_user_relation ' .
                        'WHERE user_id = :userId ' .
                        'AND custom_view_id = :viewId';
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
                    $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
                    $stmt->execute();
                }
                //if owner not delete
            } else {
                // reset relation by setting is_consumed flag to 0
                try {
                    $stmt = $this->db->prepare(
                        'UPDATE custom_view_user_relation SET is_consumed = 0 ' .
                        'WHERE custom_view_id = :viewId AND user_id = :userId '
                    );
                    $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
                    $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
                    $stmt->execute();
                } catch (\PDOException $e) {
                    throw new Exception(
                        "Cannot reset widget preferences, " . $e->getMessage() . "\n"
                    );
                }
            }
        }
    }

    /**
     * Update Custom View
     *
     * @param int $viewId
     * @param string $viewName
     * @param string $layout
     * @param int $public
     * @param bool $permission
     * @param bool $authorized
     * @return int $customViewId
     * @throws Exception
     */
    public function updateCustomView(
        int $viewId,
        string $viewName,
        string $layout,
        ?int $public,
        bool $permission,
        bool $authorized
    ): int {
        if (!$authorized || !$permission) {
            throw new CentreonCustomViewException('You are not allowed to edit the custom view');
        }
        $typeView = 0;
        if (!empty($public)) {
            $typeView = $public;
        }
        $query = 'UPDATE custom_views SET `name` = :viewName, `layout` = :layout, `public` = :typeView ' .
            'WHERE `custom_view_id` = :viewId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':viewName', $viewName, PDO::PARAM_STR);
        $stmt->bindParam(':layout', $layout, PDO::PARAM_STR);
        $stmt->bindParam(':typeView', $typeView, PDO::PARAM_INT);
        $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        return $viewId;
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
     * Loads the Custom View attached to the viewLoadId
     * @param int $viewLoad
     * @param bool $authorized
     * @return int $viewLoad
     * @throws Exception
     */
    public function loadCustomView(int $viewLoadId, bool $authorized): int
    {
        if (!$authorized) {
            throw new CentreonCustomViewException('You are not allowed to add a custom view');
        }
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
        $stmt->bindParam(':viewLoad', $viewLoadId, PDO::PARAM_INT);
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
            $stmt->bindParam(':viewLoad', $viewLoadId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        } else {
            $query = 'INSERT INTO custom_view_user_relation (custom_view_id,user_id,is_owner,locked,is_share) ' .
                'VALUES (:viewLoad, :userId, 0, :isLocked, 1)';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewLoad', $viewLoadId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
            $stmt->bindParam(':isLocked', $isLocked, PDO::PARAM_INT);
        }
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        //if the view is being added for the first time, we make sure that the widget parameters are going to be set
        if (!$update) {
            $this->addPublicViewWidgetParams($viewLoadId, $this->userId);
        }

        return $viewLoadId;
    }

    /**
    * @param $viewId
    * @param $userId
    * @throws Exception
    */
    public function addPublicViewWidgetParams($viewId, $userId)
    {
        //get all widget parameters from the view that is being added
        if (!empty($userId)) {
            $stmt = $this->db->prepare(
                'SELECT * FROM widget_views wv ' .
                'LEFT JOIN widget_preferences wp ON wp.widget_view_id = wv.widget_view_id ' .
                'LEFT JOIN custom_view_user_relation cvur ON cvur.custom_view_id = wv.custom_view_id ' .
                'WHERE cvur.custom_view_id = :viewId AND cvur.is_owner = 1 AND cvur.user_id = wp.user_id'
            );
            $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception(
                    "An error occurred when retrieving user's Id : " . $userId .
                    " parameters of the widgets from the view: Id = " . $viewId
                );
            }

            //add every widget parameters for the current user
            while ($row = $stmt->fetch()) {
                $stmt2 = $this->db->prepare(
                    'INSERT INTO widget_preferences ' .
                    'VALUES (:widgetViewId, :parameterId, :preferenceValue, :userId)'
                );
                $stmt2->bindParam(':widgetViewId', $row['widget_view_id'], PDO::PARAM_INT);
                $stmt2->bindParam(':parameterId', $row['parameter_id'], PDO::PARAM_INT);
                $stmt2->bindParam(':preferenceValue', $row['preference_value'], PDO::PARAM_STR);
                $stmt2->bindParam(':userId', $userId, PDO::PARAM_INT);

                $dbResult2 = $stmt2->execute();
                if (!$dbResult2) {
                    throw new \Exception(
                        "An error occurred when adding user's Id : " . $userId .
                        " parameters to the widgets from the view: Id = " . $viewId
                    );
                }
            }
        }
    }

    /**
     * @param int $customViewId
     * @param int[] $lockedUsers
     * @param int[] $unlockedUsers
     * @param int[] $lockedUsergroups
     * @param int[] $unlockedUsergroups
     * @param bool $permission
     * @param bool $authorized
     * @param $userId
     * @throws Exception
     * @throws CentreonCustomViewExpection
     */
    public function shareCustomView(
        int $customViewId,
        array $lockedUsers,
        array $unlockedUsers,
        array $lockedUsergroups,
        array $unlockedUsergroups,
        int $userId,
        bool $permission,
        bool $authorized
    ) {
        if (!$authorized || !$permission) {
            throw new CentreonCustomViewException('You are not allowed to share the view');
        }
        global $centreon;
        $queryValue = array();

        if ($this->checkPermission($customViewId)) {
            //////////////////////
            // share with users //
            //////////////////////
            $sharedUsers = array();
            $alwaysSharedUsers = array();

            if (!empty($lockedUsers)) {
                foreach ($lockedUsers as $lockedUser) {
                    if ($lockedUser != $centreon->user->user_id) {
                        $sharedUsers[$lockedUser] = 1;
                    }
                }
            }
            if (!empty($unlockedUsers)) {
                foreach ($unlockedUsers as $unlockedUser) {
                    if ($unlockedUser != $centreon->user->user_id) {
                        $sharedUsers[$unlockedUser] = 0;
                    }
                }
            }

            // select user already share
            $stmt = $this->db->prepare(
                'SELECT user_id FROM custom_view_user_relation ' .
                'WHERE custom_view_id = :viewId ' .
                'AND user_id <> :userId ' .
                'AND usergroup_id IS NULL '
            );
            $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            $oldSharedUsers = array();
            while ($row = $stmt->fetch()) {
                $oldSharedUsers[$row['user_id']] = 1;
            }

            // check if the view is share at a new user
            foreach ($sharedUsers as $sharedUserId => $locked) {
                if (isset($oldSharedUsers[$sharedUserId])) {
                    $stmt = $this->db->prepare(
                        'UPDATE custom_view_user_relation SET is_share = 1, locked = :isLocked ' .
                        'WHERE user_id = :userId ' .
                        'AND custom_view_id = :viewId'
                    );
                    $stmt->bindParam(':isLocked', $locked, PDO::PARAM_INT);
                    $stmt->bindParam(':userId', $sharedUserId, PDO::PARAM_INT);
                    $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                    unset($oldSharedUsers[$sharedUserId]);
                } else {
                    $stmt = $this->db->prepare(
                        'INSERT INTO custom_view_user_relation ' .
                        '(custom_view_id, user_id, locked, is_consumed, is_share ) ' .
                        'VALUES ( :viewId, :sharedUser, :isLocked, 0, 1) '
                    );
                    $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
                    $stmt->bindParam(':sharedUser', $sharedUserId, PDO::PARAM_INT);
                    $stmt->bindParam(':isLocked', $locked, PDO::PARAM_INT);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                }
                $alwaysSharedUsers[] = $sharedUserId;
                $this->copyPreferences($customViewId, $sharedUserId);
            }
            $queryValue[] = (int)$customViewId;
            $userIdKey = '';

            //prepare old user entries
            if (!empty($oldSharedUsers)) {
                foreach ($oldSharedUsers as $k => $v) {
                    $userIdKey .= '?,';
                    $queryValue[] = (int)$k;
                }
                $userIdKey = rtrim($userIdKey, ',');
            } else {
                $userIdKey .= '""';
            }

            // delete widget preferences for old user
            $stmt = $this->db->prepare(
                'DELETE FROM widget_preferences ' .
                'WHERE widget_view_id IN (SELECT wv.widget_view_id FROM widget_views wv ' .
                'WHERE wv.custom_view_id = ? ) ' .
                'AND user_id IN (' . $userIdKey . ') '
            );
            $dbResult = $stmt->execute($queryValue);
            if (!$dbResult) {
                throw new \Exception($stmt->errorInfo());
            }

            // delete view / user relation
            $stmt = $this->db->prepare(
                'DELETE FROM custom_view_user_relation ' .
                'WHERE custom_view_id = ? ' .
                'AND user_id IN (' . $userIdKey . ') '
            );
            $dbResult = $stmt->execute($queryValue);
            if (!$dbResult) {
                throw new \Exception("An error occurred");
            }

            ////////////////////////////
            // share with user groups //
            ////////////////////////////
            $sharedUsergroups = array();
            if (!empty($lockedUsergroups)) {
                foreach ($lockedUsergroups as $lockedUsergroup) {
                    $sharedUsergroups[$lockedUsergroup] = 1;
                }
            }
            if (!empty($unlockedUsergroups)) {
                foreach ($unlockedUsergroups as $unlockedUsergroup) {
                    $sharedUsergroups[$unlockedUsergroup] = 0;
                }
            }

            $query = 'SELECT usergroup_id FROM custom_view_user_relation ' .
                'WHERE custom_view_id = :viewId ' .
                'AND user_id IS NULL ';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
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
                    $query = 'UPDATE custom_view_user_relation SET is_share = 1, locked = :isLocked ' .
                        'WHERE usergroup_id = :sharedUser ' .
                        'AND custom_view_id = :viewId';

                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':isLocked', $locked, PDO::PARAM_INT);
                    $stmt->bindParam(':sharedUser', $sharedUsergroupId, PDO::PARAM_INT);
                    $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
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
                    $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
                    $stmt->bindParam(':sharedUser', $sharedUsergroupId, PDO::PARAM_INT);
                    $stmt->bindParam(':isLocked', $locked, PDO::PARAM_INT);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                }
                $this->copyPreferences($customViewId, null, $sharedUsergroupId);
            }

            $queryValue2 = array();
            $queryCgId = array();
            $queryValue2[] = (int)$customViewId;
            $userGroupIdKey = '';
            if (!empty($oldSharedUsergroups)) {
                foreach ($oldSharedUsergroups as $k => $v) {
                    $userGroupIdKey .= '?,';
                    $queryValue2[] = (int)$k;
                    $queryCgId[] = (int)$k;
                }
                $userGroupIdKey = rtrim($userGroupIdKey, ',');
            } else {
                $userGroupIdKey .= '""';
            }

            // select users of old usergroups
            $query = 'SELECT contact_contact_id FROM contactgroup_contact_relation ' .
                'WHERE contactgroup_cg_id IN (' . $userGroupIdKey . ') ';
            $stmt = $this->db->prepare($query);
            $dbResult = $stmt->execute($queryCgId);
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            $queryValueWidgetPref = array();
            $tmpValueWidgetPref = array();
            $queryValueWidgetPref[] = (int)$customViewId;
            $oldSharedUserOfUsergroups = '';
            while ($row = $stmt->fetch()) {
                $tmpValueWidgetPref[] = (int)$row['contact_contact_id'];
            }

            // compare user and user of user group
            $oldUserOfUsergroups = array_diff($tmpValueWidgetPref, $alwaysSharedUsers);
            foreach ($oldUserOfUsergroups as $user) {
                $oldSharedUserOfUsergroups .= '?,';
                $queryValueWidgetPref[] = $user;
            }

            // check user of old user group
            if ($oldSharedUserOfUsergroups !== '') {
                $oldSharedUserOfUsergroups = rtrim($oldSharedUserOfUsergroups, ',');
                // delete widget preferences for user of old user group
                $query = 'DELETE FROM widget_preferences ' .
                    'WHERE widget_view_id IN (SELECT wv.widget_view_id FROM widget_views wv ' .
                    'WHERE wv.custom_view_id = ? ) ' .
                    'AND user_id IN (' . $oldSharedUserOfUsergroups . ') ';

                $stmt = $this->db->prepare($query);
                $dbResult = $stmt->execute($queryValueWidgetPref);
                if (!$dbResult) {
                    throw new \Exception($stmt->errorInfo());
                }
            }

            // delete view / usergroup relation
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
     * @param int $viewId
     * @return array
     * @throws Exception
     */
    public function getUsersFromViewId(int $viewId): array
    {
        static $userList;
        global $centreon;

        if (!isset($userList)) {
            /**
             * Get user's ACL
             */
            $aclListOfContactIds = $centreon->user->access->getContactAclConf(
                [
                    'fields' => [
                        'contact_id'
                    ],
                    'keys' => ['contact_id'],
                    'order' => ['contact_id']
                ]
            );

            $allowedContactIds = '';
            foreach (array_keys($aclListOfContactIds) as $contactId) {
                // result concatenation
                if (false !== filter_var($contactId, FILTER_VALIDATE_INT)) {
                    if ('' !== $allowedContactIds) {
                        $allowedContactIds .= ', ';
                    }
                    $allowedContactIds .= $contactId;
                }
            }

            /**
             * Find users linked to the view
             */
            $userList = [];
            if (empty($allowedContactIds)) {
                return $userList;
            }
            $stmt = $this->db->prepare(
                'SELECT `contact_name`, `user_id`, `locked`
                FROM contact c
                INNER JOIN custom_view_user_relation cvur ON cvur.user_id = c.contact_id
                WHERE cvur.custom_view_id = :viewId
                AND cvur.is_share = 1
                AND c.contact_id IN (' . $allowedContactIds . ')
                ORDER BY contact_name'
            );
            $stmt->bindValue(':viewId', $viewId, \PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception(_("An error occurred while retrieving users linked to ViewId on database"));
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
     * @param int $viewId
     * @return array
     * @throws Exception
     */
    public function getUsergroupsFromViewId(int $viewId): array
    {
        static $usergroupList;
        global $centreon;

        if (!isset($usergroupList)) {
            /**
             * Get user's ACL
             */
            $aclListOfGroupIds = $centreon->user->access->getContactGroupAclConf(
                [
                    'fields' => [
                        'cg_id'
                    ],
                    'keys' => ['cg_id'],
                    'order' => ['cg_id']
                ],
                false
            );

            $allowedGroupIds = '';
            foreach (array_keys($aclListOfGroupIds) as $groupId) {
                // result's concatenation
                if (false !== filter_var($groupId, FILTER_VALIDATE_INT)) {
                    if ('' !== $allowedGroupIds) {
                        $allowedGroupIds .= ', ';
                    }
                    $allowedGroupIds .= $groupId;
                }
            }

            /**
             * Find groups linked to the view
             */
            $usergroupList = [];
            if (empty($allowedGroupIds)) {
                return $usergroupList;
            }

            $stmt = $this->db->prepare(
                'SELECT `cg_name`, `usergroup_id`, `locked`
                FROM contactgroup cg
                INNER JOIN custom_view_user_relation cvur ON cvur.usergroup_id = cg.cg_id
                WHERE cvur.custom_view_id = :viewId
                AND cvur.is_share = 1
                AND cg.cg_id IN (' . $allowedGroupIds . ')
                ORDER BY cg_name'
            );
            $stmt->bindValue(':viewId', $viewId, \PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception(_("An error occurred while retrieving groups linked to ViewId on database"));
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
     * Remove user from view shared viewer
     *
     * @param int $userId
     * @param int $customViewId
     * @param bool $permission
     * @throws Exception
     */
    public function removeUserFromView(int $userId, int $customViewId, bool $permission)
    {
        if (!$permission) {
            throw new CentreonCustomViewException('You are not allowed to remove user from view');
        }
        $query = 'SELECT is_public ' .
            'FROM custom_view_user_relation ' .
            'WHERE user_id = :userId ' .
            'AND custom_view_id = :viewId';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
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
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        } else {
            $query = 'UPDATE custom_view_user_relation SET is_share = 0, locked = 1 ' .
                'WHERE user_id = :userId ' .
                'AND custom_view_id = :viewId';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        }
    }

    /**
     * Remove usergroup from view shared viewers
     *
     * @param int $customViewId
     * @param int $userGroupId
     * @throws Exception
     */
    public function removeUsergroupFromView(int $customViewId, int $userGroupId)
    {
        $query = 'DELETE FROM custom_view_user_relation ' .
            'WHERE usergroup_id = :groupId ' .
            'AND is_public <> 1 ' .
            'AND custom_view_id = :viewId';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':groupId', $userGroupId, PDO::PARAM_INT);
        $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
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

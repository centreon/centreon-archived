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

require_once _CENTREON_PATH_ . "www/class/centreonUtils.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonCustomView.class.php";

/**
 * Class CentreonWidgetException
 */
class CentreonWidgetException extends Exception
{
}

/**
 * Class for managing widgets
 */
class CentreonWidget
{
    protected $userId;
    protected $db;
    protected $widgets;
    protected $userGroups;

    /**
     * CentreonWidget constructor.
     * @param $centreon
     * @param $db
     * @throws Exception
     */
    public function __construct($centreon, $db)
    {
        $this->userId = (int)$centreon->user->user_id;
        $this->db = $db;
        $this->widgets = array();
        $this->userGroups = array();
        $query = 'SELECT contactgroup_cg_id ' .
            'FROM contactgroup_contact_relation ' .
            'WHERE contact_contact_id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $this->userId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        while ($row = $stmt->fetch()) {
            $this->userGroups[$row['contactgroup_cg_id']] = $row['contactgroup_cg_id'];
        }
        $this->customView = new CentreonCustomView($centreon, $db);
    }

    /**
     * @param $widgetModelId
     * @return array
     * @throws Exception
     */
    protected function getParamsFromWidgetModelId($widgetModelId)
    {
        static $tab;

        if (!isset($tab)) {
            $query = 'SELECT parameter_code_name ' .
                'FROM widget_parameters ' .
                'WHERE widget_model_id = :modelId';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':modelId', $widgetModelId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            $tab = array();
            while ($row = $stmt->fetch()) {
                $tab[$row['parameter_code_name']] = $row['parameter_code_name'];
            }
        }
        return $tab;
    }


    /**
     * @param $widgetId
     * @return null
     * @throws Exception
     */
    public function getWidgetType($widgetId)
    {
        $query = 'SELECT widget_model_id, widget_id FROM widgets WHERE widget_id = :widgetId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':widgetId', $widgetId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        while ($row = $stmt->fetch()) {
            return $row['widget_model_id'];
        }
        return null;
    }

    /**
     * @param $widgetId
     * @return null
     * @throws Exception
     */
    public function getWidgetTitle($widgetId)
    {
        $query = 'SELECT title, widget_id FROM widgets WHERE widget_id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $widgetId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        while ($row = $stmt->fetch()) {
            return htmlentities($row['title'], ENT_QUOTES);
        }
        return null;
    }

    /**
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function getWidgetDirectory($id)
    {
        $query = 'SELECT directory FROM widget_models WHERE widget_model_id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        while ($row = $stmt->fetch()) {
            return $row["directory"];
        }
    }

    /**
     * @param $widgetModelId
     * @param $name
     * @return int
     * @throws Exception
     */
    public function getParameterIdByName($widgetModelId, $name)
    {
        $tab = array();
        if (!isset($tab[$widgetModelId])) {
            $query = 'SELECT parameter_id, parameter_code_name ' .
                'FROM widget_parameters ' .
                'WHERE widget_model_id = :id';
            $tab[$widgetModelId] = array();
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $widgetModelId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            while ($row = $stmt->fetch()) {
                $tab[$widgetModelId][$row['parameter_code_name']] = $row['parameter_id'];
            }
        }
        if (isset($tab[$widgetModelId][$name]) && $tab[$widgetModelId][$name]) {
            return $tab[$widgetModelId][$name];
        }
        return 0;
    }

    /**
     * @param string $type
     * @param $param
     * @return null
     */
    public function getWidgetInfo($type = "id", $param = '')
    {
        static $tabDir;
        static $tabId;

        if (!isset($tabId) || !isset($tabDir)) {
            $query = 'SELECT description, directory, title, widget_model_id, url, version, author, ' .
                'email, website, keywords, screenshot, thumbnail, autoRefresh ' .
                'FROM widget_models';
            $res = $this->db->query($query);
            while ($row = $res->fetch()) {
                $tabDir[$row['directory']] = array();
                $tabId[$row['widget_model_id']] = array();
                foreach ($row as $key => $value) {
                    $tabDir[$row['directory']][$key] = $value;
                    $tabId[$row['widget_model_id']][$key] = $value;
                }
            }
        }
        if ($type == "directory" && isset($tabDir[$param])) {
            return $tabDir[$param];
        }
        if ($type == "id" && isset($tabId[$param])) {
            return $tabId[$param];
        }
        return null;
    }


    /**
     * @param int $customViewId
     * @param int $widgetModelId
     * @param string $widgetTitle
     * @param bool $permission
     * @param bool $authorized
     * @throws CentreonWidgetException
     * @throws Exception
     */
    public function addWidget(
        int $customViewId,
        int $widgetModelId,
        string $widgetTitle,
        bool $permission,
        bool $authorized
    ) {
        if (!$authorized || !$permission) {
            throw new CentreonWidgetException('You are not allowed to add a widget.');
        }
        if (empty($customViewId) || empty($widgetModelId)) {
            throw new CentreonWidgetException('No custom view or no widget selected');
        }
        $query = 'INSERT INTO widgets (title, widget_model_id) VALUES (:title, :id)';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $widgetTitle, PDO::PARAM_STR);
        $stmt->bindParam(':id', $widgetModelId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        /* Get view layout */
        $query = 'SELECT layout ' .
            'FROM custom_views ' .
            'WHERE custom_view_id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $customViewId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new CentreonWidgetException('No view found');
        }
        $row = $stmt->fetch();
        if (is_null($row)) {
            throw new CentreonWidgetException('No view found');
        }
        $layout = str_replace('column_', '', $row['layout']);
        /* Default position */
        $newPosition = null;
        /* Prepare first position */
        $matrix = array();
        $query = 'SELECT widget_order ' .
            'FROM widget_views ' .
            'WHERE custom_view_id = :id';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $customViewId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new CentreonWidgetException('No view found');
        }

        while ($position = $stmt->fetch()) {
            list($col, $row) = explode('_', $position['widget_order']);
            if (false == isset($matrix[$row])) {
                $matrix[$row] = array();
            }
            $matrix[$row][] = $col;
        }
        ksort($matrix);
        $rowNb = 0; //current row in the foreach
        foreach ($matrix as $row => $cols) {
            if ($rowNb != $row) {
                break;
            }
            if (count($cols) < $layout) {
                sort($cols); // number of used columns in this row
                for ($i = 0; $i < $layout; $i++) {
                    if (!isset($cols[$i]) || $cols[$i] != $i) {
                        $newPosition = $i . '_' . $rowNb;
                        break;
                    }
                }
                break;
            }
            $rowNb++;
        }
        if (is_null($newPosition)) {
            $newPosition = '0_' . $rowNb;
        }

        $lastId = $this->getLastInsertedWidgetId($widgetTitle);
        $query = 'INSERT INTO widget_views (custom_view_id, widget_id, widget_order) VALUES (:id, :lastId, :neworder)';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $customViewId, PDO::PARAM_INT);
        $stmt->bindParam(':lastId', $lastId, PDO::PARAM_INT);
        $stmt->bindParam(':neworder', $newPosition, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
    }

    /**
     * Get Wiget Info By Id
     *
     * @param int $widgetModelId
     * @return mixed
     */
    public function getWidgetInfoById($widgetModelId)
    {
        return $this->getWidgetInfo("id", $widgetModelId);
    }

    /**
     * Get Widget Info By Directory
     *
     * @param string $directory
     * @return mixed
     */
    public function getWidgetInfoByDirectory($directory)
    {
        return $this->getWidgetInfo("directory", $directory);
    }

    /**
     * @param $widgetId
     * @return mixed
     * @throws CentreonWidgetException
     * @throws Exception
     */
    public function getUrl($widgetId)
    {
        $query = 'SELECT url FROM widget_models wm, widgets w ' .
            'WHERE wm.widget_model_id = w.widget_model_id ' .
            'AND w.widget_id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $widgetId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        if ($stmt->rowCount()) {
            $row = $stmt->fetch();
            return $row['url'];
        } else {
            throw new CentreonWidgetException('No URL found for Widget #' . $widgetId);
        }
    }

    /**
     * @param $widgetId
     * @return mixed
     * @throws CentreonWidgetException
     * @throws Exception
     */
    public function getRefreshInterval($widgetId)
    {
        $query = 'SELECT autoRefresh FROM widget_models wm, widgets w ' .
            'WHERE wm.widget_model_id = w.widget_model_id ' .
            'AND w.widget_id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $widgetId, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        if ($stmt->rowCount()) {
            $row = $stmt->fetch();
            return $row['autoRefresh'];
        } else {
            throw new CentreonWidgetException('No autoRefresh found for Widget #' . $widgetId);
        }
    }

    /**
     * @param int $viewId
     * @return array
     * @throws Exception
     */
    public function getWidgetsFromViewId(int $viewId): array
    {
        if (!isset($this->widgets[$viewId])) {
            $this->widgets[$viewId] = array();
            $query = "SELECT w.widget_id, w.title, wm.url, widget_order
            		  FROM widget_views wv, widgets w, widget_models wm
            		  WHERE w.widget_id = wv.widget_id
            		  AND wv.custom_view_id = :id
            		  AND w.widget_model_id = wm.widget_model_id
                      ORDER BY 
                      CAST(SUBSTRING_INDEX(widget_order, '_', 1) AS SIGNED INTEGER), 
                      CAST(SUBSTRING_INDEX(widget_order, '_', -1) AS SIGNED INTEGER)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $viewId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            while ($row = $stmt->fetch()) {
                $this->widgets[$viewId][$row['widget_id']]['title'] = htmlentities($row['title'], ENT_QUOTES);
                $this->widgets[$viewId][$row['widget_id']]['url'] = $row['url'];
                $this->widgets[$viewId][$row['widget_id']]['widget_order'] = $row['widget_order'];
                $this->widgets[$viewId][$row['widget_id']]['widget_id'] = $row['widget_id'];
            }
        }
        return $this->widgets[$viewId];
    }

    /**
     * @param string $search
     * @param array $range
     * @return array
     * @throws Exception
     */
    public function getWidgetModels($search = '', $range = array())
    {
        $queryValues = array();
        $query = 'SELECT SQL_CALC_FOUND_ROWS widget_model_id, title FROM widget_models ';
        if ($search != '') {
            $query .= 'WHERE title like :search ';
            $queryValues['search'] = '%' . $search . '%';
        }
        $query .= 'ORDER BY title ';
        if (!empty($range)) {
            $query .= 'LIMIT :offset, :limit ';
            $queryValues['offset'] = (int)$range[0];
            $queryValues['limit'] = (int)$range[1];
        }
        $stmt = $this->db->prepare($query);
        if (isset($queryValues['search'])) {
            $stmt->bindParam(':search', $queryValues['search'], PDO::PARAM_STR);
        }
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $widgets = array();
        while ($data = $stmt->fetch()) {
            $widgets[] = array('id' => $data['widget_model_id'], 'text' => $data['title']);
        }
        return array(
            'items' => $widgets,
            'total' => (int) $this->db->numberRows()
        );
    }

    /**
     * @param int $viewId
     * @param array $widgetList
     * @throws Exception
     */
    public function updateViewWidgetRelations($viewId, array $widgetList = [])
    {
        $query = 'DELETE FROM widget_views WHERE custom_view_id = :viewId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $str = '';
        $queryValues = array();
        foreach ($widgetList as $widgetId) {
            if ($str != '') {
                $str .= ',';
            }
            $str .= '(:viewId, :widgetId' . $widgetId . ')';
            $queryValues['widgetId' . $widgetId] = (int)$widgetId;
        }

        if ($str != '') {
            $query = 'INSERT INTO widget_views (custom_view_id, widget_id) VALUES ' . $str;
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':viewId', $viewId, PDO::PARAM_INT);
            foreach ($queryValues as $widgetId) {
                $stmt->bindValue(':widgetId' . $widgetId, $widgetId, PDO::PARAM_INT);
            }
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        }
    }

    /**
     * @param $widgetId
     * @param bool $hasPermission
     * @return array
     * @throws Exception
     */
    public function getParamsFromWidgetId($widgetId, $hasPermission = false)
    {
        static $params;

        if (!isset($params)) {
            $params = array();
            $query = 'SELECT ft.is_connector, ft.ft_typename, p.parameter_id, p.parameter_name, p.default_value, ' .
                'p.header_title, p.require_permission ' .
                'FROM widget_parameters_field_type ft, widget_parameters p, widgets w ' .
                'WHERE ft.field_type_id = p.field_type_id ' .
                'AND p.widget_model_id = w.widget_model_id ' .
                'AND w.widget_id = :widgetId ' .
                'ORDER BY parameter_order ASC';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':widgetId', $widgetId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }

            while ($row = $stmt->fetch()) {
                if ($row['require_permission'] && $hasPermission == false) {
                    continue;
                }
                $params[$row['parameter_id']]['parameter_id'] = $row['parameter_id'];
                $params[$row['parameter_id']]['ft_typename'] = $row['ft_typename'];
                $params[$row['parameter_id']]['parameter_name'] = $row['parameter_name'];
                $params[$row['parameter_id']]['default_value'] = $row['default_value'];
                $params[$row['parameter_id']]['is_connector'] = $row['is_connector'];
                $params[$row['parameter_id']]['header_title'] = $row['header_title'];
            }
        }
        return $params;
    }

    /**
     * @param $params
     * @param bool $permission
     * @param bool $authorized
     * @throws CentreonWidgetException
     * @throws Exception
     */
    public function updateUserWidgetPreferences(array $params, bool $permission, bool $authorized)
    {
        if (!$authorized || !$permission) {
            throw new CentreonWidgetException('You are not allowed to set preferences on the widget');
        }
        $queryValues = array();
        $query = 'SELECT wv.widget_view_id ' .
            'FROM widget_views wv, custom_view_user_relation cvur ' .
            'WHERE cvur.custom_view_id = wv.custom_view_id ' .
            'AND wv.widget_id = :widgetId ' .
            'AND (cvur.user_id = :userId';
        $explodedValues = '';
        if (count($this->userGroups)) {
            foreach ($this->userGroups as $k => $v) {
                $explodedValues .= ':userGroupId' . $v . ',';
                $queryValues['userGroupId' . $v] = $v;
            }
            $explodedValues = rtrim($explodedValues, ',');

            $query .= " OR cvur.usergroup_id IN ($explodedValues) ";
        }
        $query .= ") AND wv.custom_view_id = :viewId";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':widgetId', $params['widget_id'], PDO::PARAM_INT);
        $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        $stmt->bindParam(':viewId', $params['custom_view_id'], PDO::PARAM_INT);
        if (count($this->userGroups)) {
            foreach ($queryValues as $key => $userGroupId) {
                $stmt->bindValue(':' . $key, $userGroupId, PDO::PARAM_INT);
            }
        }
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        if ($stmt->rowCount()) {
            $row = $stmt->fetch();
            $widgetViewId = $row['widget_view_id'];
        } else {
            throw new CentreonWidgetException('No widget_view_id found for user');
        }

        if ($permission == false) {
            $query = 'DELETE FROM widget_preferences ' .
                'WHERE widget_view_id = :widgetViewId ' .
                'AND user_id = :userId ' .
                'AND parameter_id NOT IN (' .
                'SELECT parameter_id FROM widget_parameters WHERE require_permission = "1")';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':widgetViewId', $widgetViewId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        } else {
            $query = 'DELETE FROM widget_preferences ' .
                'WHERE widget_view_id = :widgetViewId ' .
                'AND user_id = :userId';

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':widgetViewId', $widgetViewId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        }

        $queryValues = array();
        $str = "";
        foreach ($params as $key => $val) {
            if (preg_match("/param_(\d+)/", $key, $matches)) {
                if (is_array($val)) {
                    if (isset($val['op_' . $matches[1]]) && isset($val['cmp_' . $matches[1]])) {
                        $val = $val['op_' . $matches[1]] . ' ' . $val['cmp_' . $matches[1]];
                    } elseif (isset($val['order_' . $matches[1]]) && isset($val['column_' . $matches[1]])) {
                        $val = $val['column_' . $matches[1]] . ' ' . $val['order_' . $matches[1]];
                    } elseif (isset($val['from_' . $matches[1]]) && isset($val['to_' . $matches[1]])) {
                        $val = $val['from_' . $matches[1]] . ',' . $val['to_' . $matches[1]];
                    } else {
                        $val = implode(',', $val);
                    }
                    $val = trim($val);
                }
                if ($str != "") {
                    $str .= ", ";
                }
                $str .= '(:widgetViewId, :parameter' . $matches[1] . ', :preference' . $matches[1] . ', :userId)';
                $queryValues['parameter'][':parameter' . $matches[1]] = $matches[1];
                $queryValues['preference'][':preference' . $matches[1]] = $val;
            }
        }
        if ($str != "") {
            $query = 'INSERT INTO widget_preferences (widget_view_id, parameter_id, preference_value, user_id) ' .
                'VALUES ' . $str;
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':widgetViewId', $widgetViewId, PDO::PARAM_INT);
            $stmt->bindValue(':userId', $this->userId, PDO::PARAM_INT);
            if (isset($queryValues['parameter'])) {
                foreach ($queryValues['parameter'] as $k => $v) {
                    $stmt->bindValue($k, $v, PDO::PARAM_INT);
                }
            }
            if (isset($queryValues['preference'])) {
                foreach ($queryValues['preference'] as $k => $v) {
                    $stmt->bindValue($k, $v, PDO::PARAM_STR);
                }
            }
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        }
        $this->customView->syncCustomView($params['custom_view_id']);
    }

    /**
     * @param int $customViewId
     * @param int $widgetId
     * @param bool $authorized
     * @param bool $permission
     * @throws Exception
     */
    public function deleteWidgetFromView(int $customViewId, int $widgetId, bool $authorized, bool $permission)
    {
        if (!$authorized || !$permission) {
            throw new CentreonWidgetException('You are not allowed to delete the widget');
        }
        $query = 'DELETE FROM widget_views ' .
            'WHERE custom_view_id = :viewId ' .
            'AND widget_id = :widgetId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
        $stmt->bindParam(':widgetId', $widgetId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
    }

    /**
     * Updates a widget position on a customview
     *
     * @param int $customViewId
     * @param string[] $position
     * @param bool $permission
     * @throws CentreonWidgetException
     * @throws Exception
     */
    public function updateWidgetPositions(int $customViewId, bool $permission, array $positions = [])
    {
        if (!$permission) {
            throw new CentreonWidgetException('You are not allowed to change widget position');
        }
        if (empty($customViewId)) {
            throw new CentreonWidgetException('No custom view id provided');
        }
        if (!empty($positions) && is_array($positions)) {
            foreach ($positions as $rawData) {
                if (preg_match('/([0-9]+)_([0-9]+)_([0-9]+)/', $rawData, $matches)) {
                    $widgetOrder = "{$matches[1]}_{$matches[2]}";
                    $widgetId = $matches[3];

                    $query = 'UPDATE widget_views SET widget_order = :widgetOrder ' .
                        'WHERE custom_view_id = :viewId ' .
                        'AND widget_id = :widgetId';
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':widgetOrder', $widgetOrder, PDO::PARAM_STR);
                    $stmt->bindParam(':viewId', $customViewId, PDO::PARAM_INT);
                    $stmt->bindParam(':widgetId', $widgetId, PDO::PARAM_INT);
                    $dbResult = $stmt->execute();

                }
            }
        }
    }
    /**
     * Read Configuration File
     *
     * @param string $filename
     * @return array
     */
    public function readConfigFile($filename)
    {
        $xmlString = file_get_contents($filename);
        $xmlObj = simplexml_load_string($xmlString);
        return CentreonUtils::objectIntoArray($xmlObj);
    }

    /**
     * @param $title
     * @return mixed
     * @throws Exception
     */
    protected function getLastInsertedWidgetId($title)
    {
        $query = 'SELECT MAX(widget_id) as lastId FROM widgets WHERE title = :title';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $row = $stmt->fetch();
        return $row['lastId'];
    }

    /**
     * @param $directory
     * @return mixed
     * @throws Exception
     */
    protected function getLastInsertedWidgetModelId($directory)
    {
        $query = 'SELECT MAX(widget_model_id) as lastId ' .
            'FROM widget_models ' .
            'WHERE directory = :directory';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':directory', $directory, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $row = $stmt->fetch();
        return $row['lastId'];
    }

    /**
     * @param $label
     * @return mixed
     * @throws Exception
     */
    protected function getLastInsertedParameterId($label)
    {
        $query = 'SELECT MAX(parameter_id) as lastId ' .
            'FROM widget_parameters ' .
            'WHERE parameter_name = :name';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $label, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $row = $stmt->fetch();
        return $row['lastId'];
    }

    /**
     * Get Parameter Type IDs
     *
     * @return array
     */
    protected function getParameterTypeIds()
    {
        static $types;

        if (!isset($types)) {
            $types = array();
            $query = 'SELECT ft_typename, field_type_id FROM  widget_parameters_field_type';
            $res = $this->db->query($query);
            while ($row = $res->fetch()) {
                $types[$row['ft_typename']] = $row['field_type_id'];
            }
        }
        return $types;
    }

    /**
     * @param $lastId
     * @param $config
     * @throws CentreonWidgetException
     * @throws Exception
     */
    protected function insertWidgetPreferences($lastId, $config)
    {
        if (isset($config['preferences'])) {
            $types = $this->getParameterTypeIds();
            foreach ($config['preferences'] as $preference) {
                $order = 1;
                if (isset($preference['@attributes'])) {
                    $pref = $preference;
                    $attr = $pref['@attributes'];
                    if (!isset($types[$attr['type']])) {
                        throw new CentreonWidgetException(
                            'Unknown type : ' . $attr['type'] . ' found in configuration file'
                        );
                    }
                    if (!isset($attr['requirePermission'])) {
                        $attr['requirePermission'] = 0;
                    }
                    if (!isset($attr['defaultValue'])) {
                        $attr['defaultValue'] = '';
                    }
                    $str = "(:lastId, :type, :label, :name, :defaultValue, :order, :requirePermission, ";
                    if (isset($attr['header']) && $attr['header'] != "") {
                        $str .= ":header";
                    } else {
                        $str .= "NULL";
                    }
                    $str .= ")";
                    $query = 'INSERT INTO widget_parameters ' .
                        '(widget_model_id, field_type_id, parameter_name, parameter_code_name, default_value, ' .
                        'parameter_order, require_permission, header_title) ' .
                        'VALUES ' . $str;
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':lastId', $lastId, PDO::PARAM_INT);
                    $stmt->bindParam(':type', $types[$attr['type']], PDO::PARAM_INT);
                    $stmt->bindParam(':label', $attr['label'], PDO::PARAM_STR);
                    $stmt->bindParam(':name', $attr['name'], PDO::PARAM_STR);
                    $stmt->bindParam(':defaultValue', $attr['defaultValue'], PDO::PARAM_STR);
                    $stmt->bindParam(':order', $order, PDO::PARAM_INT);
                    $stmt->bindParam(':requirePermission', $attr['requirePermission'], PDO::PARAM_STR);
                    if (isset($attr['header']) && $attr['header'] != "") {
                        $stmt->bindParam(':header', $attr['header'], PDO::PARAM_STR);
                    }
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                    $lastParamId = $this->getLastInsertedParameterId($attr['label']);
                    $this->insertParameterOptions($lastParamId, $attr, $pref);
                    $order++;
                } else {
                    foreach ($preference as $pref) {
                        $attr = $pref['@attributes'];
                        if (!isset($types[$attr['type']])) {
                            throw new CentreonWidgetException(
                                'Unknown type : ' . $attr['type'] . ' found in configuration file'
                            );
                        }
                        if (!isset($attr['requirePermission'])) {
                            $attr['requirePermission'] = 0;
                        }
                        if (!isset($attr['defaultValue'])) {
                            $attr['defaultValue'] = '';
                        }

                        $str = "(:lastId, :type, :label, :name, :defaultValue, :order, :requirePermission, ";
                        if (isset($attr['header']) && $attr['header'] != "") {
                            $str .= ":header";
                        } else {
                            $str .= "NULL";
                        }
                        $str .= ")";
                        $query = 'INSERT INTO widget_parameters ' .
                            '(widget_model_id, field_type_id, parameter_name, parameter_code_name, ' .
                            'default_value, parameter_order, require_permission, header_title) ' .
                            'VALUES ' . $str;
                        $stmt = $this->db->prepare($query);
                        $stmt->bindParam(':lastId', $lastId, PDO::PARAM_INT);
                        $stmt->bindParam(':type', $types[$attr['type']], PDO::PARAM_INT);
                        $stmt->bindParam(':label', $attr['label'], PDO::PARAM_STR);
                        $stmt->bindParam(':name', $attr['name'], PDO::PARAM_STR);
                        $stmt->bindParam(':defaultValue', $attr['defaultValue'], PDO::PARAM_STR);
                        $stmt->bindParam(':order', $order, PDO::PARAM_INT);
                        $stmt->bindParam(':requirePermission', $attr['requirePermission'], PDO::PARAM_STR);
                        if (isset($attr['header']) && $attr['header'] != "") {
                            $stmt->bindParam(':header', $attr['header'], PDO::PARAM_STR);
                        }
                        $dbResult = $stmt->execute();
                        if (!$dbResult) {
                            throw new \Exception("An error occured");
                        }

                        $lastParamId = $this->getLastInsertedParameterId($attr['label']);
                        $this->insertParameterOptions($lastParamId, $attr, $pref);
                        $order++;
                    }
                }
            }
        }
    }

    /**
     * @param $widgetPath
     * @param $directory
     * @throws Exception
     */
    public function install($widgetPath, $directory)
    {
        $config = $this->readConfigFile($widgetPath . "/" . $directory . "/configs.xml");
        if (!$config['autoRefresh']) {
            $config['autoRefresh'] = 0;
        }
        $queryValues = array();
        $query = 'INSERT INTO widget_models (title, description, url, version, directory, author, email, ' .
            'website, keywords, screenshot, thumbnail, autoRefresh) ' .
            'VALUES (:title, :description, :url, :version, :directory, :author, :email, ' .
            ':website, :keywords, :screenshot, :thumbnail, :autoRefresh)';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $config['title'], PDO::PARAM_STR);
        $stmt->bindParam(':description', $config['description'], PDO::PARAM_STR);
        $stmt->bindParam(':url', $config['url'], PDO::PARAM_STR);
        $stmt->bindParam(':version', $config['version'], PDO::PARAM_STR);
        $stmt->bindParam(':directory', $directory, PDO::PARAM_STR);
        $stmt->bindParam(':author', $config['author'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $config['email'], PDO::PARAM_STR);
        $stmt->bindParam(':website', $config['website'], PDO::PARAM_STR);
        $stmt->bindParam(':keywords', $config['keywords'], PDO::PARAM_STR);
        $stmt->bindParam(':screenshot', $config['screenshot'], PDO::PARAM_STR);
        $stmt->bindParam(':thumbnail', $config['thumbnail'], PDO::PARAM_STR);
        $stmt->bindParam(':autoRefresh', $config['autoRefresh'], PDO::PARAM_INT);

        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $lastId = $this->getLastInsertedWidgetModelId($directory);
        $this->insertWidgetPreferences($lastId, $config);
    }


    /**
     * @param $paramId
     * @param $attr
     * @param $pref
     * @throws Exception
     */
    protected function insertParameterOptions($paramId, $attr, $pref)
    {
        if ($attr['type'] == "list" || $attr['type'] == "sort") {
            if (isset($pref['option'])) {
                $queryValues2 = array();
                $str2 = "";
                foreach ($pref['option'] as $option) {
                    if (isset($option['@attributes'])) {
                        $opt = $option['@attributes'];
                    } else {
                        $opt = $option;
                    }
                    if ($str2 != "") {
                        $str2 .= ", ";
                    }
                    $str2 .= "(:id, :label, :value)";
                }
                if ($str2 != "") {
                    $query2 = 'INSERT INTO widget_parameters_multiple_options ' .
                        '(parameter_id, option_name, option_value) ' .
                        'VALUES ' . $str2;
                    $stmt = $this->db->prepare($query2);
                    $stmt->bindParam(':id', $paramId, PDO::PARAM_INT);
                    $stmt->bindParam(':label', $opt['label'], PDO::PARAM_STR);
                    $stmt->bindParam(':value', $opt['value'], PDO::PARAM_STR);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }
                }
            }
        } elseif ($attr['type'] == "range") {
            $query = 'INSERT INTO widget_parameters_range (parameter_id, min_range, max_range, step) ' .
                'VALUES (:id, :mini, :maxi, :step)';

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $paramId, PDO::PARAM_INT);
            $stmt->bindParam(':mini', $attr['min'], PDO::PARAM_INT);
            $stmt->bindParam(':maxi', $attr['max'], PDO::PARAM_INT);
            $stmt->bindParam(':step', $attr['step'], PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        }
    }

    /**
     * @param $widgetModelId
     * @param $config
     * @throws CentreonWidgetException
     * @throws Exception
     */
    protected function upgradePreferences($widgetModelId, $config)
    {
        $existingParams = $this->getParamsFromWidgetModelId($widgetModelId);
        $currentParameterTab = array();
        if (isset($config['preferences'])) {
            $types = $this->getParameterTypeIds();
            foreach ($config['preferences'] as $preference) {
                $order = 1;
                if (isset($preference['@attributes'])) {
                    $pref = $preference;
                    $attr = $pref['@attributes'];
                    if (!isset($types[$attr['type']])) {
                        throw new CentreonWidgetException(
                            'Unknown type : ' . $attr['type'] . ' found in configuration file'
                        );
                    }
                    if (!isset($existingParams[$attr['name']])) {
                        if (!isset($attr['requirePermission'])) {
                            $attr['requirePermission'] = 0;
                        }
                        $queryValues = array();
                        $str = "(?, ?, ?, ?, ?, ?, ?, ";
                        $queryValues[] = (int)$widgetModelId;
                        $queryValues[] = (int)$types[$attr['type']];
                        $queryValues[] = (string)$attr['label'];
                        $queryValues[] = (string)$attr['name'];
                        $queryValues[] = (string)$attr['defaultValue'];
                        $queryValues[] = (int)$order;
                        $queryValues[] = (string)$attr['requirePermission'];
                        if (isset($attr['header']) && $attr['header'] != "") {
                            $str .= "?";
                            $queryValues[] = (string)$attr['header'];
                        } else {
                            $str .= "NULL";
                        }
                        $str .= ")";
                        $query = 'INSERT INTO widget_parameters (widget_model_id, field_type_id, parameter_name, ' .
                            'parameter_code_name, default_value, parameter_order, require_permission, header_title) ' .
                            'VALUES ' . $str;
                    } else {
                        $queryValues = array();
                        $str = ' field_type_id = ?, parameter_name = ?,  default_value = ?, parameter_order = ?, ';
                        $queryValues[] = (int)$types[$attr['type']];
                        $queryValues[] = (string)$attr['label'];
                        $queryValues[] = (string)$attr['defaultValue'];
                        $queryValues[] = (int)$order;

                        if (!isset($attr['requirePermission'])) {
                            $attr['requirePermission'] = 0;
                        }
                        $str .= ' require_permission = ?, header_title = ';
                        $queryValues[] = (string)$attr['requirePermission'];

                        if (isset($attr['header']) && $attr['header'] != "") {
                            $str .= "? ";
                            $queryValues[] = (string)$attr['header'];
                        } else {
                            $str .= "NULL ";
                        }
                        $query = 'UPDATE widget_parameters ' .
                            'SET ' . $str . ' ' .
                            'WHERE parameter_code_name = ? ' .
                            'AND widget_model_id = ?';
                        $queryValues[] = (string)$attr['name'];
                        $queryValues[] = (int)$widgetModelId;
                    }
                    $stmt = $this->db->prepare($query);
                    $dbResult = $stmt->execute($queryValues);
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }


                    $parameterId = $this->getParameterIdByName($widgetModelId, $attr['name']);
                    $currentParameterTab[$attr['name']] = 1;
                    $query = 'DELETE FROM widget_parameters_multiple_options WHERE parameter_id = :paramId';
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':paramId', $parameterId, PDO::PARAM_INT);
                    $dbResult = $stmt->execute();
                    if (!$dbResult) {
                        throw new \Exception("An error occured");
                    }

                    $this->insertParameterOptions($parameterId, $attr, $pref);
                    $order++;
                } else {
                    foreach ($preference as $pref) {
                        $attr = $pref['@attributes'];
                        if (!isset($types[$attr['type']])) {
                            throw new CentreonWidgetException(
                                'Unknown type : ' . $attr['type'] . ' found in configuration file'
                            );
                        }
                        if (!isset($existingParams[$attr['name']])) {
                            if (!isset($attr['requirePermission'])) {
                                $attr['requirePermission'] = 0;
                            }

                            $queryValues = array();
                            $str = '(?, ?, ?, ?, ?, ?, ?, ';
                            $queryValues[] = (int)$widgetModelId;
                            $queryValues[] = (int)$types[$attr['type']];
                            $queryValues[] = (string)$attr['label'];
                            $queryValues[] = (string)$attr['name'];
                            $queryValues[] = (string)$attr['defaultValue'];
                            $queryValues[] = (int)$order;
                            $queryValues[] = (string)$attr['requirePermission'];

                            if (!isset($attr['header'])) {
                                $str .= "NULL";
                            } else {
                                $str .= "?";
                                $queryValues[] = (string)$attr['header'];
                            }
                            $str .= ")";
                            $query = 'INSERT INTO widget_parameters (widget_model_id, field_type_id, parameter_name, ' .
                                'parameter_code_name, default_value, parameter_order, require_permission, ' .
                                'header_title) VALUES ' . $str;
                        } else {
                            $queryValues = array();
                            $str = ' field_type_id = ?, parameter_name = ?, ';
                            $queryValues[] = (int)$types[$attr['type']];
                            $queryValues[] = (string)$attr['label'];

                            if (isset($attr['defaultValue'])) {
                                $str .= ' default_value = ?, ';
                                $queryValues[] = (string)$attr['defaultValue'];
                            }
                            $str .= ' parameter_order = ?, ';
                            $queryValues[] = (int)$order;

                            if (!isset($attr['requirePermission'])) {
                                $attr['requirePermission'] = 0;
                            }
                            $str .= " require_permission = ?, header_title = ";
                            $queryValues[] = (string)$attr['requirePermission'];

                            if (isset($attr['header']) && $attr['header'] != "") {
                                $str .= "? ";
                                $queryValues[] = (string)$attr['header'];
                            } else {
                                $str .= "NULL ";
                            }
                            $query = 'UPDATE widget_parameters SET ' . $str . ' ' .
                                'WHERE parameter_code_name = ? ' .
                                'AND widget_model_id = ?';
                            $queryValues[] = (string)$attr['name'];
                            $queryValues[] = (int)$widgetModelId;
                        }
                        $stmt = $this->db->prepare($query);
                        $dbResult = $stmt->execute($queryValues);
                        if (!$dbResult) {
                            throw new \Exception("An error occured");
                        }

                        $parameterId = $this->getParameterIdByName($widgetModelId, $attr['name']);
                        $currentParameterTab[$attr['name']] = 1;
                        $query = 'DELETE FROM widget_parameters_multiple_options WHERE parameter_id = :paramId';
                        $stmt = $this->db->prepare($query);
                        $stmt->bindParam(':paramId', $parameterId, PDO::PARAM_INT);
                        $dbResult = $stmt->execute();
                        if (!$dbResult) {
                            throw new \Exception("An error occured");
                        }

                        $this->insertParameterOptions($parameterId, $attr, $pref);
                        $order++;
                    }
                }
            }
        }
        $deleteStr = "";
        $deleteQueryValues = array();
        foreach ($existingParams as $codeName) {
            if (!isset($currentParameterTab[$codeName])) {
                if ($deleteStr != "") {
                    $deleteStr .= ', ';
                }
                $deleteStr .= '?';
                $codeName = array_map(
                    function ($var) {
                        return (string)$var;
                    },
                    $codeName
                );
                $deleteQueryValues[] = $codeName;
            }
        }
        if ($deleteStr) {
            $query = 'DELETE FROM widget_parameters ' .
                'WHERE parameter_code_name ' .
                'IN (' . $deleteStr . ') ' .
                'AND widget_model_id = ?';
            $deleteQueryValues[] = (int)$widgetModelId;
            $stmt = $this->db->prepare($query);
            $dbResult = $stmt->execute($deleteQueryValues);
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
        }
    }

    /**
     * @param $widgetPath
     * @param $directory
     * @throws Exception
     */
    public function upgrade($widgetPath, $directory)
    {
        $config = $this->readConfigFile($widgetPath . "/" . $directory . "/configs.xml");
        if (!$config['autoRefresh']) {
            $config['autoRefresh'] = 0;
        }
        $queryValues = array();
        $query = 'UPDATE widget_models SET ' .
            'title = ?, ' .
            'description = ?, ' .
            'url = ?, ' .
            'version = ?, ' .
            'author = ?, ' .
            'email = ?, ' .
            'website = ?, ' .
            'keywords = ?, ' .
            'screenshot = ?, ' .
            'thumbnail = ?, ' .
            'autoRefresh = ? ' .
            'WHERE directory = ?';

        $queryValues[] = (string)$config['title'];
        $queryValues[] = (string)$config['description'];
        $queryValues[] = (string)$config['url'];
        $queryValues[] = (string)$config['version'];
        $queryValues[] = (string)$config['author'];
        $queryValues[] = (string)$config['email'];
        $queryValues[] = (string)$config['website'];
        $queryValues[] = (string)$config['keywords'];
        $queryValues[] = (string)$config['screenshot'];
        $queryValues[] = (string)$config['thumbnail'];
        $queryValues[] = (int)$config['autoRefresh'];
        $queryValues[] = (string)$directory;

        $stmt = $this->db->prepare($query);
        $dbResult = $stmt->execute($queryValues);
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $info = $this->getWidgetInfoByDirectory($directory);
        $this->upgradePreferences($info['widget_model_id'], $config);
    }

    /**
     * @param $directory
     * @throws Exception
     */
    public function uninstall($directory)
    {
        $query = 'DELETE FROM widget_models WHERE directory = :directory';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':directory', $directory, PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
    }

    /**
     * @param $widgetId
     * @return array
     * @throws Exception
     */
    public function getWidgetPreferences($widgetId)
    {
        $query = 'SELECT default_value, parameter_code_name ' .
            'FROM widget_parameters param, widgets w ' .
            'WHERE w.widget_model_id = param.widget_model_id ' .
            'AND w.widget_id = :widgetId';

        // Prevent SQL injection with widget id
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':widgetId', $widgetId, PDO::PARAM_INT);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $tab = array();
        while ($row = $stmt->fetch()) {
            $tab[$row['parameter_code_name']] = $row['default_value'];
        }

        try {
            $query = 'SELECT pref.preference_value, param.parameter_code_name ' .
                'FROM widget_preferences pref, widget_parameters param, widget_views wv ' .
                'WHERE param.parameter_id = pref.parameter_id ' .
                'AND pref.widget_view_id = wv.widget_view_id ' .
                'AND wv.widget_id = :widgetId ' .
                'AND pref.user_id = :userId';

            // Prevent SQL injection with widget id
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':widgetId', $widgetId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
            $dbResult = $stmt->execute();
        } catch (\PDOException $e) {
            throw new Exception(
                "Error: cannot get preference parameter by user for widget , " . $e->getMessage() . "\n"
            );
        }

        //if user has no preferences take parent preferences
        if ($this->db->numberRows() === 0) {
            try {
                $query = 'SELECT pref.preference_value, param.parameter_code_name ' .
                    'FROM widget_preferences pref, widget_parameters param, widget_views wv ' .
                    'WHERE param.parameter_id = pref.parameter_id ' .
                    'AND pref.widget_view_id = wv.widget_view_id ' .
                    'AND wv.widget_id = :widgetId ';
                // Prevent SQL injection with widget id
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':widgetId', $widgetId, \PDO::PARAM_INT);
                $dbResult = $stmt->execute();
            } catch (\PDOException $e) {
                throw new Exception(
                    "Error: cannot get preference parameter for widget , " . $e->getMessage() . "\n"
                );
            }
        }

        while ($row = $stmt->fetch()) {
            $tab[$row['parameter_code_name']] = $row['preference_value'];
        }
        return $tab;
    }

    /**
     * Rename widget
     *
     * @param int $elementId widget id
     * @param string $newName widget new name
     * @return string
     */
    public function rename(int $widgetId, string $newName)
    {
        $query = 'UPDATE widgets ' .
            'SET title = :title ' .
            'WHERE widget_id = :widgetId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $newName, PDO::PARAM_STR);
        $stmt->bindParam(':widgetId', $widgetId, PDO::PARAM_INT);
        $stmt->execute();

        return $newName;
    }
}

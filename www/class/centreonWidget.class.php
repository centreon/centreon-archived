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

require_once _CENTREON_PATH_ . "www/class/centreonUtils.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonCustomView.class.php";

/**
 * Centreon Widget Exception
 */
class CentreonWidgetException extends Exception
{
}

;

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
     * Constructor
     *
     * @param Centreon $centreon
     * @param CentreonDB $db
     * @return void
     */
    public function __construct($centreon, $db)
    {
        $this->userId = $centreon->user->user_id;
        $this->db = $db;
        $this->widgets = array();
        $this->userGroups = array();
        $query = 'SELECT contactgroup_cg_id ' .
            'FROM contactgroup_contact_relation ' .
            'WHERE contact_contact_id = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$this->userId));

        while ($row = $res->fetchRow()) {
            $this->userGroups[$row['contactgroup_cg_id']] = $row['contactgroup_cg_id'];
        }
        $this->customView = new CentreonCustomView($centreon, $db);
    }

    /**
     * Get Params From Widget Model Id
     *
     * @param int $widgetModelId
     * @return array
     */
    protected function getParamsFromWidgetModelId($widgetModelId)
    {
        static $tab;

        if (!isset($tab)) {
            $query = 'SELECT parameter_code_name ' .
                'FROM widget_parameters ' .
                'WHERE widget_model_id = ?';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$widgetModelId));

            $tab = array();
            while ($row = $res->fetchRow()) {
                $tab[$row['parameter_code_name']] = $row['parameter_code_name'];
            }
        }
        return $tab;
    }


    /**
     * Get Widget Title
     *
     * @param int $widgetId
     * @return string
     */
    public function getWidgetType($widgetId)
    {
        $query = 'SELECT widget_model_id, widget_id FROM widgets WHERE widget_id = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$widgetId));

        while ($row = $res->fetchRow()) {
            return $row['widget_model_id'];
        }
        return null;
    }

    /**
     * Get Widget Type
     *
     * @param int $widgetId
     * @return string
     */
    public function getWidgetTitle($widgetId)
    {
        $query = 'SELECT title, widget_id FROM widgets WHERE widget_id = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$widgetId));

        while ($row = $res->fetchRow()) {
            return $row['title'];
        }
        return null;
    }

    /**
     * Get Widget Model Name
     *
     * @param int id
     * @return mixed
     */
    public function getWidgetDirectory($id)
    {
        $query = 'SELECT directory FROM widget_models WHERE widget_model_id = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$id));
        while ($row = $res->fetchRow()) {
            return $row["directory"];
        }
    }

    /**
     * Get Parameter Id By Name
     *
     * @param int $widgetModelId
     * @param string $name
     * @return int
     */
    public function getParameterIdByName($widgetModelId, $name)
    {
        $tab = array();
        if (!isset($tab[$widgetModelId])) {
            $query = 'SELECT parameter_id, parameter_code_name ' .
                'FROM widget_parameters ' .
                'WHERE widget_model_id = ?';
            $tab[$widgetModelId] = array();
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$widgetModelId));
            while ($row = $res->fetchRow()) {
                $tab[$widgetModelId][$row['parameter_code_name']] = $row['parameter_id'];
            }
        }
        if (isset($tab[$widgetModelId][$name]) && $tab[$widgetModelId][$name]) {
            return $tab[$widgetModelId][$name];
        }
        return 0;
    }

    /**
     * Get Widget Info
     *
     * @param string $type
     * @param mixed $param
     * @return mixed
     */
    public function getWidgetInfo($type = "id", $param)
    {
        static $tabDir;
        static $tabId;

        if (!isset($tabId) || !isset($tabDir)) {
            $query = 'SELECT description, directory, title, widget_model_id, url, version, author, ' .
                'email, website, keywords, screenshot, thumbnail, autoRefresh ' .
                'FROM widget_models';
            $res = $this->db->query($query);
            while ($row = $res->fetchRow()) {
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
     * Add widget to view
     *
     * @param array $params
     * @throws CentreonWidgetException
     */
    public function addWidget($params)
    {
        if (!isset($params['custom_view_id'])
            || !isset($params['widget_model_id'])
            || !isset($params['widget_title'])
        ) {
            throw new CentreonWidgetException('No custom view or no widget selected');
        }
        $queryValues = array();
        $query = 'INSERT INTO widgets (title, widget_model_id) VALUES (?, ?)';
        $queryValues[] = (string)$params['widget_title'];
        $queryValues[] = (int)$params['widget_model_id'];
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, $queryValues);
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        /* Get view layout */
        $query = 'SELECT layout ' .
            'FROM custom_views ' .
            'WHERE custom_view_id = ?';

        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$params['custom_view_id']));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        if (PEAR::isError($res)) {
            throw new CentreonWidgetException('No view found');
        }
        $row = $res->fetchRow();
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
            'WHERE custom_view_id = ?';

        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$params['custom_view_id']));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        if (PEAR::isError($res)) {
            throw new CentreonWidgetException('No view found');
        }
        while ($position = $res->fetchRow()) {
            list($col, $row) = explode('_', $position['widget_order']);
            if (false == isset($matrix[$row])) {
                $matrix[$row] = array();
            }
            $matrix[$row][] = $col;
        }
        ksort($matrix);
        $rowNb = 0;
        foreach ($matrix as $row => $cols) {
            if ($rowNb != $row) {
                break;
            }
            file_put_contents('/tmp/debug-layout', "Row " . $row);
            if (count($cols) < $layout) {
                sort($cols);
                for ($i = 0; $i < $layout; $i++) {
                    file_put_contents('/tmp/debug-layout', "Col " . $i, FILE_APPEND);
                    if ($cols[$i] != $i) {
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

        $lastId = $this->getLastInsertedWidgetId($params['widget_title']);
        $queryValues = array();

        $query = 'INSERT INTO widget_views (custom_view_id, widget_id, widget_order) VALUES (?, ?, ?)';
        $queryValues[] = (int)$params['custom_view_id'];
        $queryValues[] = (int)$lastId;
        $queryValues[] = (string)$newPosition;
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, $queryValues);
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
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
     * Get URL
     *
     * @param int $widgetId
     * @return string
     */
    public function getUrl($widgetId)
    {
        $query = 'SELECT url FROM widget_models wm, widgets w ' .
            'WHERE wm.widget_model_id = w.widget_model_id ' .
            'AND w.widget_id = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$widgetId));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        if ($res->numRows()) {
            $row = $res->fetchRow();
            return $row['url'];
        } else {
            throw new CentreonWidgetException('No URL found for Widget #' . $widgetId);
        }
    }

    /**
     * Get Refresh Interval
     *
     * @param int $widgetId
     * @return int
     */
    public function getRefreshInterval($widgetId)
    {
        $query = 'SELECT autoRefresh FROM widget_models wm, widgets w ' .
            'WHERE wm.widget_model_id = w.widget_model_id ' .
            'AND w.widget_id = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$widgetId));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        if ($res->numRows()) {
            $row = $res->fetchRow();
            return $row['autoRefresh'];
        } else {
            throw new CentreonWidgetException('No autoRefresh found for Widget #' . $widgetId);
        }
    }

    /**
     * Get Widgets From View Id
     *
     * @param int $viewId
     * @return array
     */
    public function getWidgetsFromViewId($viewId)
    {
        if (!isset($this->widgets[$viewId])) {
            $this->widgets[$viewId] = array();
            $query = "SELECT w.widget_id, w.title, wm.url, widget_order
            		  FROM widget_views wv, widgets w, widget_models wm
            		  WHERE w.widget_id = wv.widget_id
            		  AND wv.custom_view_id = ?
            		  AND w.widget_model_id = wm.widget_model_id
                      ORDER BY 
                      CAST(SUBSTRING_INDEX(widget_order, '_', 1) AS SIGNED INTEGER), 
                      CAST(SUBSTRING_INDEX(widget_order, '_', -1) AS SIGNED INTEGER)";
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$viewId));
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }

            while ($row = $res->fetchRow()) {
                $this->widgets[$viewId][$row['widget_id']]['title'] = $row['title'];
                $this->widgets[$viewId][$row['widget_id']]['url'] = $row['url'];
                $this->widgets[$viewId][$row['widget_id']]['widget_order'] = $row['widget_order'];
                $this->widgets[$viewId][$row['widget_id']]['widget_id'] = $row['widget_id'];
            }
        }
        return $this->widgets[$viewId];
    }

    /**
     * Get Widget Models
     *
     * @return array
     */
    public function getWidgetModels($search = '', $range = array())
    {
        $queryValues = array();
        $query = 'SELECT SQL_CALC_FOUND_ROWS widget_model_id, title FROM widget_models ';
        if ($search != '') {
            $query .= 'WHERE title like ? ';
            $queryValues[] = (string)'%' . $search . '%';
        }
        $query .= 'ORDER BY title ';

        if (!empty($range)) {
            $query .= 'LIMIT ?, ? ';
            $queryValues[] = (int)$range[0];
            $queryValues[] = (int)$range[1];
        }

        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, $queryValues);
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        $total = $this->db->numberRows();
        $widgets = array();
        while ($data = $res->fetchRow()) {
            $widgets[] = array('id' => $data['widget_model_id'], 'text' => $data['title']);
        }

        return array(
            'items' => $widgets,
            'total' => $total
        );
    }

    /**
     * Update View Widget Relations
     *
     * @param int $viewId
     * @param array $widgetList
     */
    public function udpateViewWidgetRelations($viewId, $widgetList)
    {
        $query = 'DELETE FROM widget_views WHERE custom_view_id = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$viewId));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        $str = '';
        $queryValues = array();
        foreach ($widgetList as $widgetId) {
            if ($str != '') {
                $str .= ',';
            }
            $str .= '(?,?)';
            $queryValues[] = (int)$viewId;
            $queryValues[] = (int)$widgetId;
        }
        if ($str != '') {
            $query = 'INSERT INTO widget_views (custom_view_id, widget_id) VALUES ' . $str;
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, $queryValues);
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }
        }
    }

    /**
     * Get Params From Widget Id
     *
     * @param int $widgetId
     * @return array
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
                'AND w.widget_id = ? ' .
                'ORDER BY parameter_order ASC';
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, array((int)$widgetId));
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }

            while ($row = $res->fetchRow()) {
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
     * Update User Widget Preferences
     *
     * @param array $params
     * @return void
     * @throws CentreonWidgetException
     */
    public function updateUserWidgetPreferences($params, $hasPermission = false)
    {
        $queryValues = array();
        $query = 'SELECT wv.widget_view_id ' .
            'FROM widget_views wv, custom_view_user_relation cvur ' .
            'WHERE cvur.custom_view_id = wv.custom_view_id ' .
            'AND wv.widget_id = ? ' .
            'AND (cvur.user_id = ?';
        $queryValues[] = (int)$params['widget_id'];
        $queryValues[] = (int)$this->userId;

        $explodedValues = '';
        if (count($this->userGroups)) {
            foreach ($this->userGroups as $k => $v) {
                $explodedValues .= '?,';
                $queryValues[] = (int)$v;
            }
            $explodedValues = rtrim($explodedValues, ',');

            $query .= " OR cvur.usergroup_id IN ($explodedValues) ";
        }
        $query .= ") AND wv.custom_view_id = ?";
        $queryValues[] = (int)$params['custom_view_id'];

        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, $queryValues);
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        if ($res->numRows()) {
            $row = $res->fetchRow();
            $widgetViewId = $row['widget_view_id'];
        } else {
            throw new CentreonWidgetException('No widget_view_id found for user');
        }

        $deleteQueryValues = array();
        if ($hasPermission == false) {
            $query = 'DELETE FROM widget_preferences ' .
                'WHERE widget_view_id = ? ' .
                'AND user_id = ? ' .
                'AND parameter_id NOT IN (' .
                'SELECT parameter_id FROM widget_parameters WHERE require_permission = "1")';
            $deleteQueryValues[] = (int)$widgetViewId;
            $deleteQueryValues[] = (int)$this->userId;
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, $deleteQueryValues);
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }
        } else {
            $query = 'DELETE FROM widget_preferences ' .
                'WHERE widget_view_id = ? ' .
                'AND user_id = ?';
            $deleteQueryValues[] = (int)$widgetViewId;
            $deleteQueryValues[] = (int)$this->userId;
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, $deleteQueryValues);
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
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
                }
                if ($str != "") {
                    $str .= ", ";
                }
                $str .= "(?, ?, ?, ?)";
                $queryValues[] = (int)$widgetViewId;
                $queryValues[] = (int)$matches[1];
                $queryValues[] = (string)$val;
                $queryValues[] = (int)$this->userId;
            }
        }
        if ($str != "") {
            $query = 'INSERT INTO widget_preferences (widget_view_id, parameter_id, preference_value, user_id) ' .
                'VALUES ' . $str;
            $stmt = $this->db->prepare($query);
            $res = $this->db->execute($stmt, $queryValues);
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }
        }
        $this->customView->syncCustomView($params['custom_view_id']);
    }

    /**
     * Delete Widget From View
     *
     * @param array $params
     * @return void
     */
    public function deleteWidgetFromView($params)
    {
        $queryValues = array();
        $query = 'DELETE FROM widget_views ' .
            'WHERE custom_view_id = ? ' .
            'AND widget_id = ?';
        $queryValues[] = (int)$params['custom_view_id'];
        $queryValues[] = (int)$params['widget_id'];

        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, $queryValues);
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }
    }

    /**
     * Update Widget Positions
     *
     * @param array $params
     * @return void
     * @throws CentreonWidgetException
     */
    public function updateWidgetPositions($params)
    {
        if (!isset($params['custom_view_id'])) {
            throw new CentreonWidgetException('No custom view id');
        }
        $viewId = $params['custom_view_id'];
        if (isset($params['positions']) && is_array($params['positions'])) {
            foreach ($params['positions'] as $rawData) {
                $tmp = explode("_", $rawData);
                if (count($tmp) != 3) {
                    throw new CentreonWidgetException('incorrect position data');
                }
                $column = $tmp[0];
                $row = $tmp[1];
                $widgetId = $tmp[2];
                $queryValues = array();
                $query = 'UPDATE widget_views SET widget_order = ? ' .
                    'WHERE custom_view_id =? ' .
                    'AND widget_id = ?';
                $queryValues[] = (string)$column . "_" . $row;
                $queryValues[] = (int)$viewId;
                $queryValues[] = (int)$widgetId;

                $stmt = $this->db->prepare($query);
                $res = $this->db->execute($stmt, $queryValues);
                if (PEAR::isError($res)) {
                    throw new Exception('Bad Request');
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
     * Get Last Inserted Widget id
     *
     * @param string $title
     * @return int
     */
    protected function getLastInsertedWidgetId($title)
    {
        $query = 'SELECT MAX(widget_id) as lastId FROM widgets WHERE title = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((string)$title));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        $row = $res->fetchRow();
        return $row['lastId'];
    }

    /**
     * Get Last Inserted Widget Model id
     *
     * @param string $directory
     * @return int
     */
    protected function getLastInsertedWidgetModelId($directory)
    {
        $query = 'SELECT MAX(widget_model_id) as lastId ' .
            'FROM widget_models ' .
            'WHERE directory = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((string)$directory));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        $row = $res->fetchRow();
        return $row['lastId'];
    }

    /**
     * Get Last Inserted Parameter id
     *
     * @param string $label
     * @return int
     */
    protected function getLastInsertedParameterId($label)
    {
        $query = 'SELECT MAX(parameter_id) as lastId ' .
            'FROM widget_parameters ' .
            'WHERE parameter_name = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((string)$label));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        $row = $res->fetchRow();
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
            while ($row = $res->fetchRow()) {
                $types[$row['ft_typename']] = $row['field_type_id'];
            }
        }
        return $types;
    }

    /**
     * Insert Widget Preferences
     *
     * @param int $lastId
     * @param array $config
     * @throws CentreonWidgetException
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
                    $queryValues = array();
                    $str = "(?, ?, ?, ?, ?, ?, ?, ";
                    $queryValues[] = (int)$lastId;
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
                    $query = 'INSERT INTO widget_parameters ' .
                        '(widget_model_id, field_type_id, parameter_name, parameter_code_name, default_value, ' .
                        'parameter_order, require_permission, header_title) ' .
                        'VALUES ' . $str;
                    $stmt = $this->db->prepare($query);
                    $res = $this->db->execute($stmt, $queryValues);
                    if (PEAR::isError($res)) {
                        throw new Exception('Bad Request');
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
                        $queryValues = array();
                        $str = "(?, ?, ?, ?, ?, ?, ?, ";
                        $queryValues[] = (int)$lastId;
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
                        $query = 'INSERT INTO widget_parameters ' .
                            '(widget_model_id, field_type_id, parameter_name, parameter_code_name, ' .
                            'default_value, parameter_order, require_permission, header_title) ' .
                            'VALUES ' . $str;
                        $stmt = $this->db->prepare($query);
                        $res = $this->db->execute($stmt, $queryValues);
                        if (PEAR::isError($res)) {
                            throw new Exception('Bad Request');
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
     * Install
     *
     * @param string $widgetPath
     * @param string $directory
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
            'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $queryValues[] = (string)$config['title'];
        $queryValues[] = (string)$config['description'];
        $queryValues[] = (string)$config['url'];
        $queryValues[] = (string)$config['version'];
        $queryValues[] = (string)$directory;
        $queryValues[] = (string)$config['author'];
        $queryValues[] = (string)$config['email'];
        $queryValues[] = (string)$config['website'];
        $queryValues[] = (string)$config['keywords'];
        $queryValues[] = (string)$config['screenshot'];
        $queryValues[] = (string)$config['thumbnail'];
        $queryValues[] = (int)$config['autoRefresh'];

        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, $queryValues);
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        $lastId = $this->getLastInsertedWidgetModelId($directory);
        $this->insertWidgetPreferences($lastId, $config);
    }


    /**
     * Insert Parameter Options
     *
     * @param int $paramId
     * @param array $attr
     * @param array $pref
     * @return void
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
                    $str2 .= "(?, ?, ?)";
                    $queryValues2[] = (int)$paramId;
                    $queryValues2[] = (string)$opt['label'];
                    $queryValues2[] = (string)$opt['value'];
                }
                if ($str2 != "") {
                    $query2 = 'INSERT INTO widget_parameters_multiple_options ' .
                        '(parameter_id, option_name, option_value) ' .
                        'VALUES ' . $str2;
                    $stmt = $this->db->prepare($query2);
                    $res = $this->db->execute($stmt, $queryValues2);
                    if (PEAR::isError($res)) {
                        throw new Exception('Bad Request');
                    }
                }
            }
        } elseif ($attr['type'] == "range") {
            $queryValues = array();
            $query = 'INSERT INTO widget_parameters_range (parameter_id, min_range, max_range, step) ' .
                'VALUES (?, ?, ?, ?)';
            $queryValues[] = (int)$paramId;
            $queryValues[] = (int)$attr['min'];
            $queryValues[] = (int)$attr['max'];
            $queryValues[] = (int)$attr['step'];
            $stmt = $this->db->prepare($query);
            $this->db->execute($stmt, $queryValues);
        }
    }

    /**
     * Upgrade preferences
     *
     * @param int $widgetModelId
     * @param array $config
     * @return void
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
                    $this->db->execute($stmt, $queryValues);

                    $parameterId = $this->getParameterIdByName($widgetModelId, $attr['name']);
                    $currentParameterTab[$attr['name']] = 1;
                    $query = 'DELETE FROM widget_parameters_multiple_options WHERE parameter_id = ?';
                    $stmt = $this->db->prepare($query);
                    $this->db->execute($stmt, array((int)$parameterId));

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
                        $this->db->execute($stmt, $queryValues);
                        $parameterId = $this->getParameterIdByName($widgetModelId, $attr['name']);
                        $currentParameterTab[$attr['name']] = 1;
                        $query = 'DELETE FROM widget_parameters_multiple_options WHERE parameter_id = ?';
                        $stmt = $this->db->prepare($query);
                        $this->db->execute($stmt, array((int)$parameterId));
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
            $res = $this->db->execute($stmt, $deleteQueryValues);
            if (PEAR::isError($res)) {
                throw new Exception('Bad Request');
            }
        }
    }

    /**
     * Upgrade
     *
     * @param string $widgetPath
     * @param string $directory
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
        $res = $this->db->execute($stmt, $queryValues);
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }
        $info = $this->getWidgetInfoByDirectory($directory);
        $this->upgradePreferences($info['widget_model_id'], $config);
    }

    /**
     * Uninstall
     *
     * @param string $directory
     */
    public function uninstall($directory)
    {
        $query = 'DELETE FROM widget_models WHERE directory = ?';
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((string)$directory));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }
    }

    /**
     * Get widget Preferences
     *
     * @param int $widgetId
     * @return array
     */
    public function getWidgetPreferences($widgetId)
    {
        $query = 'SELECT default_value, parameter_code_name ' .
            'FROM widget_parameters param, widgets w ' .
            'WHERE w.widget_model_id = param.widget_model_id ' .
            'AND w.widget_id = ?';

        // Prevent SQL injection with widget id
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$widgetId));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        $tab = array();
        while ($row = $res->fetchRow()) {
            $tab[$row['parameter_code_name']] = $row['default_value'];
        }

        $query = 'SELECT pref.preference_value, param.parameter_code_name ' .
            'FROM widget_preferences pref, widget_parameters param, widget_views wv ' .
            'WHERE param.parameter_id = pref.parameter_id ' .
            'AND pref.widget_view_id = wv.widget_view_id ' .
            'AND wv.widget_id = ?';

        // Prevent SQL injection with widget id
        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, array((int)$widgetId));
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }

        while ($row = $res->fetchRow()) {
            $tab[$row['parameter_code_name']] = $row['preference_value'];
        }
        return $tab;
    }

    /**
     * Rename widget
     *
     * @param array $params
     * @return string
     */
    public function rename($params)
    {
        if (!isset($params['elementId']) || !isset($params['newName'])) {
            throw new CentreonWidgetException('Missing mandatory parameters elementId or newName');
        }
        if (preg_match("/title_(\d+)/", $params['elementId'], $matches)) {
            if (isset($matches[1])) {
                $widgetId = $matches[1];
            }
        }
        if (!isset($widgetId)) {
            throw new CentreonWidgetException('Missing widget id');
        }
        $queryValues = array();
        $query = 'UPDATE widgets ' .
            'SET title = ? ' .
            'WHERE widget_id = ?';
        $queryValues[] = (string)$params['newName'];
        $queryValues[] = (int)$widgetId;

        $stmt = $this->db->prepare($query);
        $res = $this->db->execute($stmt, $queryValues);
        if (PEAR::isError($res)) {
            throw new Exception('Bad Request');
        }
        return $params['newName'];
    }
}

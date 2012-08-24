<?php
/**
 * Copyright 2005-2011 MERETHIS
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

require_once $centreon_path . "www/class/centreonUtils.class.php";

/**
 * Centreon Widget Exception
 */
class CentreonWidgetException extends Exception {};

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
        $query = "SELECT contactgroup_cg_id
        		  FROM contactgroup_contact_relation
        		  WHERE contact_contact_id = " . $this->db->escape($this->userId);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow()) {
            $this->userGroups[$row['contactgroup_cg_id']] = $row['contactgroup_cg_id'];
        }
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
            $query = "SELECT parameter_code_name
            		  FROM widget_parameters
            		  WHERE widget_model_id = " . $this->db->escape($widgetModelId);
            $res = $this->db->query($query);
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
    public function getWidgetTitle($widgetId)
    {
        static $tab;

        if (!isset($tab)) {
            $tab = array();
            $res = $this->db->query("SELECT title, widget_id FROM widgets");
            while ($row = $res->fetchRow()) {
                $tab[$row['widget_id']] = $row['title'];
            }
        }
        if (isset($tab[$widgetId])) {
            return $tab[$widgetId];
        }
        return null;
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
            $query = "SELECT parameter_id, parameter_code_name
            		  FROM widget_parameters
            		  WHERE widget_model_id = " . $this->db->escape($widgetModelId);
            $tab[$widgetModelId] = array();
            $res = $this->db->query($query);
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
    protected function getWidgetInfo($type = "id", $param)
    {
        static $tabDir;
        static $tabId;

        if (!isset($tabId) || !isset($tabDir)) {
            $query = "SELECT description, directory, title, widget_model_id, url, version, author, email, website, keywords, screenshot, thumbnail, autoRefresh
            		  FROM widget_models";
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
        if (!isset($params['custom_view_id']) || !isset($params['widget_model_id']) || !isset($params['widget_title'])) {
            throw new CentreonWidgetException('No custom view or no widget selected');
        }
        $query = "INSERT INTO widgets (title, widget_model_id)
        		  VALUES ('".$this->db->escape($params['widget_title'])."', ".$this->db->escape($params['widget_model_id']).")";
        $this->db->query($query);
        $lastId = $this->getLastInsertedWidgetId($params['widget_title']);
        $query = "INSERT INTO widget_views (custom_view_id, widget_id, widget_order)
        		  VALUES (".$this->db->escape($params['custom_view_id']).", ".$this->db->escape($lastId).", 0)";
        $this->db->query($query);
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
        $query = "SELECT url FROM widget_models wm, widgets w
        		  WHERE wm.widget_model_id = w.widget_model_id
        		  AND w.widget_id = " . $this->db->escape($widgetId);
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            return $row['url'];
        } else {
            throw new CentreonWidgetException('No URL found for Widget #'.$widgetId);
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
        $query = "SELECT autoRefresh FROM widget_models wm, widgets w
        		  WHERE wm.widget_model_id = w.widget_model_id
        		  AND w.widget_id = " . $this->db->escape($widgetId);
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            return $row['autoRefresh'];
        } else {
            throw new CentreonWidgetException('No autoRefresh found for Widget #'.$widgetId);
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
            		  AND wv.custom_view_id = " .$this->db->escape($viewId) . "
            		  AND w.widget_model_id = wm.widget_model_id
            		  ORDER BY widget_order";
            $res = $this->db->query($query);
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
    public function getWidgetModels()
    {
         $query = "SELECT widget_model_id, title
         		   FROM widget_models
         		   ORDER BY title";
         $res = $this->db->query($query);
         $widgets = array();
         while ($row = $res->fetchRow()) {
             $widgets[$row['widget_model_id']] = $row['title'];
         }
         return $widgets;
    }

    /**
     * Update View Widget Relations
     *
     * @param int $viewId
     * @param array $widgetList
     */
    public function udpateViewWidgetRelations($viewId, $widgetList)
    {
        $query = "DELETE FROM widget_views WHERE custom_view_id = " . $this->db->escape($viewId);
        $this->db->query($query);
        $str = "";
        foreach ($widgetList as $widgetId) {
            if ($str != "") {
                $str .= ",";
            }
            $str .= "(".$this->db->escape($viewId).",".$this->db->escape($widgetId).")";
        }
        if ($str != "") {
            $this->db->query("INSERT INTO widget_views (custom_view_id, widget_id) VALUES " . $str);
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
            $query = "SELECT ft.is_connector, ft.ft_typename, p.parameter_id, p.parameter_name, p.default_value, p.header_title, p.require_permission
            		  FROM widget_parameters_field_type ft, widget_parameters p, widgets w
            		  WHERE ft.field_type_id = p.field_type_id
            		  AND p.widget_model_id = w.widget_model_id
            		  AND w.widget_id = " . $this->db->escape($widgetId) . "
            		  ORDER BY parameter_order ASC";
            $res = $this->db->query($query);
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
        $query = "SELECT wv.widget_view_id
        		  FROM widget_views wv, custom_view_user_relation cvur
        		  WHERE cvur.custom_view_id = wv.custom_view_id
        		  AND wv.widget_id = " . $this->db->escape($params['widget_id']) . "
        		  AND (cvur.user_id = ".$this->db->escape($this->userId);
        if (count($this->userGroups)) {
            $cglist = implode(",", $this->userGroups);
            $query .= " OR cvur.usergroup_id IN ($cglist) ";
        }
        $query .= ") AND wv.custom_view_id = " .$this->db->escape($params['custom_view_id']);
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            $widgetViewId = $row['widget_view_id'];
        } else {
            throw new CentreonWidgetException('No widget_view_id found for user');
        }
        $str = "";
        foreach ($params as $key => $val) {
            if (preg_match("/param_(\d+)/", $key, $matches)) {
                if (is_array($val)) {
                    if (isset($val['op_'.$matches[1]]) && isset($val['cmp_'.$matches[1]])) {
                        $val = $val['op_'.$matches[1]]. ' ' .$val['cmp_'.$matches[1]];
                    } elseif (isset($val['order_'.$matches[1]]) && isset($val['column_'.$matches[1]])) {
                        $val = $val['column_'.$matches[1]]. ' ' .$val['order_'.$matches[1]];
                    } elseif (isset($val['from_'.$matches[1]]) && isset($val['to_'.$matches[1]])) {
                        $val = $val['from_'.$matches[1]].','.$val['to_'.$matches[1]];
                    }
                }
                if ($str != "") {
                    $str .= ", ";
                }
                $str .= "(".$widgetViewId.",".$matches[1].",'".$val."', ".$this->userId.")";
            }
        }
        if ($hasPermission == false) {
            $this->db->query("DELETE FROM widget_preferences
        				  WHERE widget_view_id = " . $this->db->escape($widgetViewId) . "
        				  AND user_id = " . $this->db->escape($this->userId) . "
        				  AND parameter_id NOT IN (SELECT parameter_id FROM widget_parameters WHERE require_permission = '1')");
        } else {
            $this->db->query("DELETE FROM widget_preferences
        				  WHERE widget_view_id = " . $this->db->escape($widgetViewId) . "
        				  AND user_id = " . $this->db->escape($this->userId));
        }
        if ($str != "") {
            $query = "INSERT INTO widget_preferences (widget_view_id, parameter_id, preference_value, user_id) VALUES $str";
        }
        $this->db->query($query);
    }

    /**
     * Delete Widget From View
     *
     * @param array $params
     * @return void
     */
    public function deleteWidgetFromView($params)
    {
        $query = "DELETE FROM widget_views
        		  WHERE custom_view_id = " .$this->db->escape($params['custom_view_id']) . "
        		  AND widget_id = " . $this->db->escape($params['widget_id']);
        $this->db->query($query);
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
                $query = "UPDATE widget_views SET widget_order = '".$column."_".$row."'
                		  WHERE custom_view_id = " . $this->db->escape($viewId) . "
                		  AND widget_id = " . $this->db->escape($widgetId);
                $this->db->query($query);
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
        $query = "SELECT MAX(widget_id) as lastId FROM widgets WHERE title = '".$this->db->escape($title)."'";
        $res = $this->db->query($query);
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
        $query = "SELECT MAX(widget_model_id) as lastId FROM widget_models WHERE directory = '".$this->db->escape($directory)."'";
        $res = $this->db->query($query);
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
        $query = "SELECT MAX(parameter_id) as lastId FROM widget_parameters WHERE parameter_name = '".$this->db->escape($label)."'";
        $res = $this->db->query($query);
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
            $query = "SELECT ft_typename, field_type_id FROM  widget_parameters_field_type";
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
                foreach ($preference as $pref) {
                    $attr = $pref['@attributes'];
                    if (!isset($types[$attr['type']])) {
                        throw new CentreonWidgetException('Unknown type : ' . $attr['type'] . ' found in configuration file');
                    }
                    if (!isset($attr['requirePermission'])) {
                        $attr['requirePermission'] = 0;
                    }
                    if (!isset($attr['defaultValue'])) {
                        $attr['defaultValue'] = '';
                    }
                    $str = "(".$lastId.", ".$types[$attr['type']].", '".$this->db->escape($attr['label'])."', '".$this->db->escape($attr['name'])."', '".$this->db->escape($attr['defaultValue'])."', $order, '".$this->db->escape($attr['requirePermission'])."', ";
                    if (isset($attr['header']) && $attr['header'] != "") {
                        $str .= "'".$this->db->escape($attr['header'])."'";
                    } else {
                        $str .= "NULL";
                    }
                    $str .= ")";
                    $query = "INSERT INTO widget_parameters
                    		  (widget_model_id, field_type_id, parameter_name, parameter_code_name, default_value, parameter_order, require_permission, header_title)
                              VALUES $str";
                    $this->db->query($query);
                    $lastParamId  = $this->getLastInsertedParameterId($attr['label']);
                    $this->insertParameterOptions($lastParamId, $attr, $pref);
                    $order++;
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
        $config = $this->readConfigFile($widgetPath."/".$directory."/configs.xml");
        if (!$config['autoRefresh']) {
            $config['autoRefresh'] = 0;
        }
        $query = "INSERT INTO widget_models (title, description, url, version, directory, author, email, website, keywords, screenshot, thumbnail, autoRefresh)
        		  VALUES (".
                          "'".$this->db->escape($config['title']) ."',".
                          "'".$this->db->escape($config['description']) ."',".
        				  "'".$this->db->escape($config['url']) ."',".
        				  "'".$this->db->escape($config['version']) ."',".
        				  "'".$this->db->escape($directory) ."',".
                          "'".$this->db->escape($config['author']) ."',".
                          "'".$this->db->escape($config['email']) ."',".
                          "'".$this->db->escape($config['website']) ."',".
                          "'".$this->db->escape($config['keywords']) ."',".
                          "'".$this->db->escape($config['screenshot']) ."',".
                          "'".$this->db->escape($config['thumbnail']) ."',".
                          "" .$config['autoRefresh']."".
        		  ")";
        $this->db->query($query);
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
                    $str2 .= "(".$paramId.", '".$this->db->escape($opt['label'])."', '".$this->db->escape($opt['value'])."')";
                }
                if ($str2 != "") {
                    $query2 = "INSERT INTO widget_parameters_multiple_options (parameter_id, option_name, option_value) VALUES $str2";
                    $this->db->query($query2);
                }
            }
        } elseif ($attr['type'] == "range") {
            $query = "INSERT INTO widget_parameters_range (parameter_id, min_range, max_range, step)
               		  VALUES (".$paramId.", ".$attr['min'].", ".$attr['max'].", ".$attr['step'].")";
            $this->db->query($query);
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
                foreach ($preference as $pref) {
                    $attr = $pref['@attributes'];
                    if (!isset($types[$attr['type']])) {
                        throw new CentreonWidgetException('Unknown type : ' . $attr['type'] . ' found in configuration file');
                    }
                    if (!isset($existingParams[$attr['name']])) {
                        if (!isset($attr['requirePermission'])) {
                            $attr['requirePermission'] = 0;
                        }
                        if (!isset($attr['header'])) {
                            $attr['header'] = "NULL ";
                        } else {
                            $attr['header'] = "'".$attr['header']."'";
                        }
                        $str = "(".$widgetModelId.", ".$types[$attr['type']].", '".$this->db->escape($attr['label'])."', '".$this->db->escape($attr['name'])."', '".$this->db->escape($attr['defaultValue'])."', $order, '".$this->db->escape($attr['requirePermission'])."', ".$attr['header'].")";
                        $query = "INSERT INTO widget_parameters (widget_model_id, field_type_id, parameter_name, parameter_code_name, default_value, parameter_order, require_permission, header_title) VALUES $str";
                    } else {
                        $str  = " field_type_id = " . $types[$attr['type']] . ", ";
                        $str .= " parameter_name = '" . $this->db->escape($attr['label']) . "', ";
                        $str .= " default_value = '" . $this->db->escape($attr['defaultValue']) . "', ";
                        $str .= " parameter_order = " . $order . ", ";
                        if (!isset($attr['requirePermission'])) {
                            $attr['requirePermission'] = 0;
                        }
                        $str .= " require_permission = '" . $this->db->escape($attr['requirePermission']) . "', ";
                        $str .= " header_title = ";
                        if (isset($attr['header']) && $attr['header'] != "") {
                            $str .= "'".$this->db->escape($attr['header'])."' ";
                        } else {
                            $str .= "NULL ";
                        }
                        $query = "UPDATE widget_parameters SET $str
                        		  WHERE parameter_code_name = '".$this->db->escape($attr['name'])."'
                        		  AND widget_model_id = " . $this->db->escape($widgetModelId);
                    }
                    $this->db->query($query);
                    $parameterId = $this->getParameterIdByName($widgetModelId, $attr['name']);
                    $currentParameterTab[$attr['name']] = 1;
                    $query = "DELETE FROM widget_parameters_multiple_options WHERE parameter_id = ".$this->db->escape($parameterId);
                    $this->db->query($query);
                    $this->insertParameterOptions($parameterId, $attr, $pref);
                    $order++;
                }
            }
        }
        $deleteStr = "";
        foreach ($existingParams as $codeName) {
            if (!isset($currentParameterTab[$codeName])) {
                if ($deleteStr != "") {
                    $deleteStr .= ", ";
                }
                $deleteStr .= "'".$this->db->escape($codeName)."'";
            }
        }
        if ($deleteStr) {
            $query = "DELETE FROM widget_parameters WHERE parameter_code_name IN ($deleteStr) AND widget_model_id = ". $this->db->escape($widgetModelId);
            $this->db->query($query);
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
        $config = $this->readConfigFile($widgetPath."/".$directory."/configs.xml");
        if (!$config['autoRefresh']) {
            $config['autoRefresh'] = 0;
        }
        $query = "UPDATE widget_models SET ".
                          "title = '".$this->db->escape($config['title']) ."',".
                          "description = '".$this->db->escape($config['description']) ."',".
        				  "url = '".$this->db->escape($config['url']) ."',".
        				  "version = '".$this->db->escape($config['version']) ."',".
        				  "author = '".$this->db->escape($config['author']) ."',".
                          "email = '".$this->db->escape($config['email']) ."',".
                          "website = '".$this->db->escape($config['website']) ."',".
                          "keywords = '".$this->db->escape($config['keywords']) ."',".
                          "screenshot = '".$this->db->escape($config['screenshot']) ."',".
                          "thumbnail = '".$this->db->escape($config['thumbnail']) ."',".
                          "autoRefresh = " .$config['autoRefresh']." ".
        		  "WHERE directory = '".$this->db->escape($directory)."'";
        $this->db->query($query);
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
        $query = "DELETE FROM widget_models WHERE directory = '" . $this->db->escape($directory) . "'";
        $this->db->query($query);
    }

    /**
     * Get widget Preferences
     *
     * @param int $widgetId
     * @return array
     */
    public function getWidgetPreferences($widgetId)
    {
        $query = "SELECT default_value, parameter_code_name
        		  FROM widget_parameters param, widgets w
        		  WHERE w.widget_model_id = param.widget_model_id
        		  AND w.widget_id = " . $this->db->escape($widgetId);
        $res = $this->db->query($query);
        $tab = array();
        while ($row = $res->fetchRow()) {
            $tab[$row['parameter_code_name']] = $row['default_value'];
        }

        $query = "SELECT pref.preference_value, param.parameter_code_name
           	      FROM widget_preferences pref, widget_parameters param, widget_views wv
           	      WHERE param.parameter_id = pref.parameter_id
           	      AND pref.widget_view_id = wv.widget_view_id
           	      AND wv.widget_id = ".$this->db->escape($widgetId) . "
           	      AND pref.user_id = " . $this->userId;
        $res = $this->db->query($query);
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
        $query = "UPDATE widgets
        		  SET title = '".$this->db->escape($params['newName'])."'
        		  WHERE widget_id = " . $this->db->escape($widgetId);
        $this->db->query($query);
        return $params['newName'];
    }
}
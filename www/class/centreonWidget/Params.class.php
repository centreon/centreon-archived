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

require_once "class/centreonWidget/Params/Interface.class.php";

class CentreonWidgetParamsException extends Exception {}

abstract class CentreonWidgetParams implements CentreonWidgetParamsInterface
{
    protected static $instances;
    protected $db;
    protected $quickform;
    protected $params;
    protected $userGroups;
    protected $trigger;


    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @param HTML_Quickform $quickform
     * @param int $userId
     * @return void
     */
    public function __construct($db, $quickform, $userId)
    {
        $this->trigger = false;
        $this->db = $db;
        $this->quickform = $quickform;
        $this->userId = $userId;
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
     * Get User Preferences
     *
     * @param array $params
     * @return mixed
     */
    protected function getUserPreferences($params)
    {
        $query = "SELECT preference_value
            	  FROM widget_preferences wp, widget_views wv, custom_view_user_relation cvur
            	  WHERE wp.parameter_id = " .$this->db->escape($params['parameter_id']) . "
            	  AND wp.widget_view_id = wv.widget_view_id
            	  AND wv.widget_id = ".$this->db->escape($params['widget_id'])."
            	  AND wv.custom_view_id = cvur.custom_view_id
            	  AND wp.user_id = ".$this->db->escape($this->userId) . "
            	  AND (cvur.user_id = wp.user_id";
        if (count($this->userGroups)) {
            $cglist = implode(",", $this->userGroups);
            $query .= " OR cvur.usergroup_id IN ($cglist) ";
        }
        $query .= ") AND cvur.custom_view_id = ". $this->db->escape($params['custom_view_id']) . "
				  LIMIT 1";
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            return $row['preference_value'];
        }
        return null;
    }

    /**
     * Factory
     *
     * @param CentreonDB $db
     * @param HTML_Quickform $quickform
     * @param string $typeName
     * @param int $userId
     * @return mixed
     */
    public static function factory($db, $quickform, $className, $userId)
    {
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className($db, $quickform, $userId);
        }
        return self::$instances[$className];
    }

    /**
     * Init
     *
     * @param array $params
     * @return void
     */
    public function init($params)
    {
        $this->params = $params;
    }

    /**
     * Set Value
     *
     * @param array $params
     * @return void
     */
    public function setValue($params)
    {
        $userPref = $this->getUserPreferences($params);
        if (isset($userPref)) {
            $this->quickform->setDefaults(array('param_' . $params['parameter_id'] => $userPref));
        } elseif (isset($params['default_value']) && $params['default_value'] != "") {
            $this->quickform->setDefaults(array('param_' . $params['parameter_id'] => $params['default_value']));
        }
    }

    /**
     * Get Element
     *
     * @return HTML_Quickform
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Get List Values
     *
     * @param int $paramId
     * @return array
     */
    public function getListValues($paramId)
    {
        $query = "SELECT option_name, option_value
           		  FROM widget_parameters_multiple_options
           		  WHERE parameter_id = " . $this->db->escape($paramId);
        $res = $this->db->query($query);
        $tab = array(null => null);
        while ($row = $res->fetchRow()) {
            $tab[$row['option_value']] = $row['option_name'];
        }
        return $tab;
    }

    public function getTrigger()
    {
        return $this->trigger;
    }
}
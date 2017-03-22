<?php
/**
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once "centreonInstance.class.php";
require_once "Centreon/Object/Broker/Broker.php";

require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonConfigCentreonBroker.php";

/**
 *
 * @author sylvestre
 */
class CentreonCentbrokerCfg extends CentreonObject
{
    const ORDER_UNIQUENAME        = 0;
    const ORDER_INSTANCE          = 1;
    const UNKNOWNCOMBO            = "Unknown combination";
    const INVALIDFIELD            = "Invalid field";
    const NOENTRYFOUND            = "No entry found";
    protected $instanceObj;
    protected $brokerObj;

    public static $aDepends = array(
        'INSTANCE'
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->instanceObj = new CentreonInstance();
        $this->brokerObj = new \CentreonConfigCentreonBroker((new \CentreonDB()));
        $this->object = new \Centreon_Object_Broker();
        $this->params = array(
            'config_filename' => 'central-broker.xml',
            'config_activate' => '1'
        );
        $this->insertParams = array('name', 'ns_nagios_server');
        $this->action = "CENTBROKERCFG";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->activateField = "config_activate";
    }

    /**
     * Add action
     *
     * @param string $parameters
     * @return void
     */
    public function add($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $params[self::ORDER_UNIQUENAME];
        $addParams['ns_nagios_server'] = $this->instanceObj->getInstanceId($params[self::ORDER_INSTANCE]);
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
        parent::add();
    }

    /**
     * Set Parameters
     *
     * @param string $parameters
     * @return void
     * @throws Exception
     */
    public function setparam($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            if ($params[1] == "instance" || $params[1] == "ns_nagios_server") {
                $params[1] = "ns_nagios_server";
                $params[2] = $this->instanceObj->getInstanceId($params[2]);
            } elseif (!preg_match('/^config_/', $params[1])) {
                $parametersWithoutPrefix = array(
                    "event_queue_max_size",
                    "cache_directory",
                    "stats_activate",
                    "correlation_activate",
                    "daemon"
                );
                if (!in_array($params[1], $parametersWithoutPrefix)) {
                    $params[1] = 'config_'.$params[1];
                }
            }
            $updateParams = array($params[1] => $params[2]);
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Show
     * @param string $parameters
     */
    public function show($parameters = null)
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%".$parameters."%");
        }
        $params = array("config_id", "config_name", "ns_nagios_server");
        $paramString = str_replace("_", " ", implode($this->delim, $params));
        $paramString = str_replace("ns nagios server", "instance", $paramString);
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            $str = "";
            foreach ($tab as $key => $value) {
                if ($key == "ns_nagios_server") {
                    $value = $this->instanceObj->getInstanceName($value);
                }
                $str .= $value . $this->delim;
            }
            $str = trim($str, $this->delim) . "\n";
            echo $str;
        }
    }

    /**
     * get list of multi select fields
     *
     * @return array
     */
    protected function getMultiselect()
    {
        $sql = "SELECT f.cb_fieldgroup_id, fieldname, groupname
            FROM cb_field f, cb_fieldgroup fg
            WHERE f.cb_fieldgroup_id = fg.cb_fieldgroup_id
            AND f.fieldtype = 'multiselect'";
        $res = $this->db->query($sql);
        $arr = array();
        while ($row = $res->fetch()) {
            $arr[$row['fieldname']]['groupid'] = $row['cb_fieldgroup_id'];
            $arr[$row['fieldname']]['groupname'] = $row['groupname'];
        }
        return $arr;
    }

    /**
     * Magic method
     *
     * @param $name
     * @param $arg
     * @throws CentreonClapiException
     */
    public function __call($name, $arg)
    {
        /* Get the method name */
        $name = strtolower($name);

        /* Get the action and the object */
        if (preg_match("/^(list|get|set|add|del)(input|output|logger)/", $name, $matches)) {
            $tagName = $matches[2];

            /* Parse arguments */
            if (!isset($arg[0])) {
                throw new CentreonClapiException(self::MISSINGPARAMETER);
            }
            $args = explode($this->delim, $arg[0]);
            $configIds = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($args[0]));
            if (!count($configIds)) {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND .":".$args[0]);
            }
            $configId = $configIds[0];

            switch ($matches[1]) {
                case "list":
                    $this->listFlow($configId, $tagName, $args);
                    break;
                case "get":
                    $this->getFlow($configId, $tagName, $args);
                    break;
                case "set":
                    $this->setFlow($configId, $tagName, $args);
                    break;
                case "add":
                    $this->addFlow($configId, $tagName, $args);
                    break;
                case "del":
                    $this->delFlow($configId, $tagName, $args);
                    break;
            }
        } else {
            throw new CentreonClapiException(self::UNKNOWN_METHOD);
        }
    }

    /**
     * List flows
     *
     * @param $configId
     * @param $tagName
     * @param $args
     */
    private function listFlow($configId, $tagName, $args)
    {
        $query = "SELECT config_group_id as id, config_value as name "
            . "FROM cfg_centreonbroker_info "
            . "WHERE config_id = ? "
            . "AND config_group = ? "
            . "AND config_key = 'name' "
            . "ORDER BY config_group_id ";
        $res = $this->db->query($query, array($configId, $tagName));

        echo "id;name\n";
        while ($row = $res->fetch()) {
            echo $row['id'] . $this->delim . $row['name'] . "\n";
        }
    }

    /**
     * Get flow parameters
     *
     * @param $configId
     * @param $tagName
     * @param $args
     * @throws CentreonClapiException
     */
    private function getFlow($configId, $tagName, $args)
    {
        if (!isset($args[1]) || !$args[1]) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $query = "SELECT config_key, config_value "
            . "FROM cfg_centreonbroker_info "
            . "WHERE config_id = ? "
            . "AND config_group_id = ? "
            . "AND config_group = ? "
            . "ORDER BY config_key ";
        $res = $this->db->query($query, array($configId, $args[1], $tagName));

        echo "parameter key;parameter value\n";
        while ($row = $res->fetch()) {
            if ($row['config_key'] != 'blockId') {
                echo $row['config_key'] . $this->delim . $row['config_value'] . "\n";
            }
        }
    }

    /**
     * Set flow parameter
     *
     * @param $configId
     * @param $tagName
     * @param $args
     * @throws CentreonClapiException
     */
    private function setFlow($configId, $tagName, $args)
    {
        if (!isset($args[3])) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        if ($this->fieldIsValid($configId, $tagName, $args) == false) {
            throw new CentreonClapiException(self::INVALIDFIELD);
        }

        $multiselect = $this->getMultiselect();

        $query = "DELETE FROM cfg_centreonbroker_info "
            . "WHERE config_id = :config_id "
            . "AND config_group_id = :config_group_id "
            . "AND config_key = :config_key "
            . "AND config_group = :config_group ";
        $this->db->query(
            $query,
            array(
                ':config_id' => $configId,
                ':config_group_id' => $args[1],
                ':config_key' => $args[2],
                ':config_group' => $tagName
            )
        );
        $sql = "INSERT INTO cfg_centreonbroker_info "
            . "(config_id, config_group_id, config_key, "
            . "config_value, config_group, grp_level, "
            . "parent_grp_id, subgrp_id) "
            . "VALUES (?,?,?,?,?,?,?,?)";

        $grplvl = 0;
        $parentgrpid = null;
        if (isset($multiselect[$args[2]])) {
            $this->db->query(
                $sql,
                array(
                    $configId,
                    $args[1],
                    $multiselect[$args[2]]['groupname'],
                    '',
                    $tagName,
                    0,
                    null,
                    1
                )
            );
            $grplvl = 1;
            $parentgrpid = $multiselect[$args[2]]['groupid'];
        }

        $values = explode(',', $args[3]);
        foreach ($values as $value) {
            $this->db->query(
                $sql,
                array(
                    $configId,
                    $args[1],
                    $args[2],
                    $value,
                    $tagName,
                    $grplvl,
                    $parentgrpid,
                    null
                )
            );
        }
    }

    /**
     * Add flow
     *
     * @param $configId
     * @param $tagName
     * @param $args
     * @throws CentreonClapiException
     */
    private function addFlow($configId, $tagName, $args)
    {
        if (!isset($args[2])) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        
        $cbTypeId = $this->brokerObj->getTypeId($args[2]);
        if (is_null($cbTypeId)) {
            throw new CentreonClapiException(self::UNKNOWNPARAMETER);
        }

        $fields = $this->brokerObj->getBlockInfos($cbTypeId);

        $defaultValues = array();
        foreach ($fields as $field) {
            if (is_null($field['value'])) {
                $field['value'] = $this->brokerObj->getDefaults($field['id']);
            }
            if (is_null($field['value'])) {
                $field['value'] = '';
            }

            $defaultValues[$field['fieldname']] = $field['value'];
        }

        $blockId = $this->getBlockId($tagName, $args[2]);
        $sql = "SELECT MAX(config_group_id) as max_id "
            . "FROM cfg_centreonbroker_info "
            . "WHERE config_id = ? "
            . "AND config_group = ? ";
        $res = $this->db->query($sql, array($configId, $tagName));
        $row = $res->fetch();
        $i = isset($row['max_id']) ? $row['max_id'] + 1 : 1;
        unset($res);

        $sql = "INSERT INTO cfg_centreonbroker_info "
            . "(config_id, config_key, config_value, "
            . "config_group, config_group_id) "
            . "VALUES (:config_id, :config_key, :config_value, "
            . ":config_group, :config_group_id)";

        $sqlParams = array(
            ':config_id' => $configId,
            ':config_key' => 'blockId',
            ':config_value' => $blockId,
            ':config_group' => $tagName,
            ':config_group_id' => $i
        );
        $this->db->query($sql, $sqlParams);

        $values = explode(',', $args[1]);
        foreach ($values as $value) {
            $sqlParams[':config_key'] = 'type';
            $sqlParams[':config_value'] = $args[2];
            $this->db->query($sql, $sqlParams);

            $sqlParams[':config_key'] = 'name';
            $sqlParams[':config_value'] = $value;
            $this->db->query($sql, $sqlParams);
        }

        unset($defaultValues['name']);
        foreach ($defaultValues as $key => $value) {
            $sqlParams[':config_key'] = $key;
            $sqlParams[':config_value'] = $value;
            $this->db->query($sql, $sqlParams);
        }
    }

    /**
     * Remove flow
     *
     * @param $configId
     * @param $tagName
     * @param $args
     * @throws CentreonClapiException
     */
    private function delFlow($configId, $tagName, $args)
    {
        if (!isset($args[1]) || !$args[1]) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $sql = "DELETE FROM cfg_centreonbroker_info "
            . "WHERE config_id = ? "
            . "AND config_group_id = ? "
            . "AND config_group = ? ";
        $this->db->query($sql, array($configId, $args[1], $tagName));
    }

    /**
     * Get list from tag
     *
     * @param string $tagName
     * @throws CentreonClapiException
     */
    public function getTypeList($tagName = "")
    {
        if ($tagName == "") {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $sql = "SELECT ct.cb_type_id, ct.type_shortname, ct.type_name
        		FROM cb_tag_type_relation cttr, cb_type ct, cb_tag ca
        		WHERE ct.cb_type_id = cttr.cb_type_id
        		AND cttr.cb_tag_id = ca.cb_tag_id
        		AND ca.tagname = ?
        		ORDER BY ct.type_name";
        $res = $this->db->query($sql, array($tagName));
        $rows = $res->fetchAll();
        if (!count($rows)) {
            throw new CentreonClapiException(self::NOENTRYFOUND." for ".$tagName);
        }
        echo "type id".$this->delim."short name".$this->delim."name\n";
        foreach ($rows as $row) {
            echo $row['cb_type_id'].$this->delim.$row['type_shortname'].$this->delim.$row['type_name']."\n";
        }
    }
    
    /**
     * User help method
     * Get Field list from Type
     *
     * @return void
     */
    public function getFieldList($typeName)
    {
        if ($typeName == "") {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $sql = "SELECT f.cb_field_id, f.fieldname, f.displayname, f.fieldtype
        		FROM cb_type_field_relation tfr, cb_field f, cb_type ct
        		WHERE ct.cb_type_id = tfr.cb_type_id
        		AND tfr.cb_field_id = f.cb_field_id
        		AND ct.type_shortname = ?
        		ORDER BY f.fieldname";
        $res = $this->db->query($sql, array($typeName));
        $rows = $res->fetchAll();
        if (!count($rows)) {
            throw new CentreonClapiException(self::NOENTRYFOUND." for ".$typeName);
        }
        echo "field id".$this->delim."short name".$this->delim."name\n";
        foreach ($rows as $row) {
            echo $row['cb_field_id'].$this->delim.$row['fieldname'];
            if ($row['fieldtype'] == 'select' || $row['fieldtype'] == 'multiselect') {
                echo "*";
            }
            echo $this->delim.$row['displayname'].$this->delim.$row['fieldtype']."\n";
        }
    }

    /**
     * User help method
     * Get Value list from Selectbox name
     *
     * @return void
     */
    public function getValueList($selectName)
    {
        if ($selectName == "") {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $sql = "SELECT value_value
        		FROM cb_list_values lv, cb_list l, cb_field f
        		WHERE lv.cb_list_id = l.cb_list_id
        		AND l.cb_field_id = f.cb_field_id
        		AND f.fieldname = ?
        		ORDER BY lv.value_value";
        $res = $this->db->query($sql, array($selectName));
        $rows = $res->fetchAll();
        if (!count($rows)) {
            throw new CentreonClapiException(self::NOENTRYFOUND." for ".$selectName);
        }
        echo "possible values\n";
        foreach ($rows as $row) {
            echo $row['value_value']."\n";
        }
    }

    /**
     * Get block id
     *
     * @param string $tagName
     * @param string $typeName
     * @return string
     * @throws CentreonClapiException
     */
    protected function getBlockId($tagName, $typeName)
    {
        $sql = "SELECT cttr.cb_tag_id, cttr.cb_type_id
        		FROM cb_tag, cb_type, cb_tag_type_relation cttr
        		WHERE cb_tag.cb_tag_id = cttr.cb_tag_id
        		AND cttr.cb_type_id = cb_type.cb_type_id
        		AND cb_tag.tagname = ?
        		AND cb_type.type_shortname = ?";
        $res = $this->db->query($sql, array($tagName, $typeName));
        $row = $res->fetch();
        if (!isset($row['cb_type_id']) || !isset($row['cb_tag_id'])) {
            throw new CentreonClapiException(self::UNKNOWNCOMBO.': '.$tagName.'/'.$typeName);
        }
        return $row['cb_tag_id']."_".$row['cb_type_id'];
    }

    /**
     * Checks if field is valid
     *
     * @param int $configId
     * @param string $tagName
     * @param array $args | index 1 => config group id, 2 => config_key, 3 => config_value
     * @return bool
     */
    protected function fieldIsValid($configId, $tagName, $args)
    {
        if ($args[2] == 'type') {
            return true;
        }
        $sql = "SELECT config_value
        		FROM cfg_centreonbroker_info
        		WHERE config_key = 'blockId'
        		AND config_id = ?
        		AND config_group_id = ?
        		AND config_group = ?";
        $res = $this->db->query($sql, array($configId, $args[1], $tagName));
        $row = $res->fetch();
        unset($res);
        if (!isset($row['config_value'])) {
            return false;
        }
        list($tagId, $typeId) = explode('_', $row['config_value']);
        $sql = "SELECT fieldtype, cf.cb_field_id, ct.cb_module_id
        		FROM cb_type_field_relation ctfr, cb_field cf, cb_type ct
        		WHERE ctfr.cb_field_id = cf.cb_field_id
        		AND ctfr.cb_type_id = ct.cb_type_id
        		AND cf.fieldname = ?
        		AND ctfr.cb_type_id = ?";
        $res = $this->db->query($sql, array($args[2], $typeId));
        $row = $res->fetch();
        unset($res);
        if (!isset($row['fieldtype'])) {
            $sql = "SELECT fieldtype, cf.cb_field_id, ct.cb_module_id
        			FROM cb_type_field_relation ctfr, cb_field cf, cb_type ct
        			WHERE ctfr.cb_field_id = cf.cb_field_id
        			AND ctfr.cb_type_id = ct.cb_type_id
        			AND ctfr.cb_type_id = ?";
            $res = $this->db->query($sql, array($typeId));
            $rows = $res->fetchAll();
            unset($res);
            $found = false;
            foreach ($rows as $row) {
                $sql = "SELECT fieldtype, cf.cb_field_id
    					FROM cb_module_relation cmr, cb_type ct, cb_type_field_relation ctfr, cb_field cf
                        WHERE cmr.cb_module_id = ?
                        AND cf.fieldname = ?
                        AND cmr.inherit_config = 1
                        AND cmr.module_depend_id = ct.cb_module_id
                        AND ct.cb_type_id = ctfr.cb_type_id
                        AND ctfr.cb_field_id = cf.cb_field_id
                        ORDER BY fieldname";
                $res = $this->db->query($sql, array($row['cb_module_id'], $args[2]));
                $row = $res->fetch();
                if (isset($row['fieldtype'])) {
                    $found = true;
                    break;
                }
                unset($res);
            }
            if ($found == false) {
                return false;
            }
        }
        if ($row['fieldtype'] != 'select' && $row['fieldtype'] != 'multiselect') {
            return true;
        }
        if ($row['fieldtype'] == 'select') {
            $sql = "SELECT value_value
        	    FROM cb_list cl, cb_list_values clv, cb_field cf
        	    WHERE cl.cb_list_id = clv.cb_list_id
        		AND cl.cb_field_id = cf.cb_field_id
            	AND cf.cb_field_id = ?
            	AND cf.fieldname = ?
            	AND clv.value_value = ?";
            $res = $this->db->query($sql, array($row['cb_field_id'], $args[2], $args[3]));
            $row = $res->fetch();
            if (!isset($row['value_value'])) {
                return false;
            }
        } else {
            $vals = explode(',', $args[3]);
            $sql = "SELECT value_value
        	    FROM cb_list cl, cb_list_values clv, cb_field cf
        	    WHERE cl.cb_list_id = clv.cb_list_id
        		AND cl.cb_field_id = cf.cb_field_id
            	AND cf.cb_field_id = ?
            	AND cf.fieldname = ?";
            $res = $this->db->query($sql, array($row['cb_field_id'], $args[2]));
            $allowedValues = array();
            while ($row = $res->fetch()) {
                $allowedValues[] = $row['value_value'];
            }
            foreach ($vals as $v) {
                if (!in_array($v, $allowedValues)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Export
     *
     * @return void
     */
    public function export()
    {
        $elements = $this->object->getList("*", -1, 0);
        foreach ($elements as $element) {
            $addStr = $this->action.$this->delim."ADD".
                      $this->delim.$element['config_name'].
                      $this->delim.$this->instanceObj->getInstanceName($element['ns_nagios_server']);
            echo $addStr."\n";
            echo $this->action . $this->delim
                . "SETPARAM" . $this->delim
                . $element['config_name'] . $this->delim
                . "filename" . $this->delim
                . $element['config_filename'] . "\n";
            echo $this->action . $this->delim
                . "SETPARAM" . $this->delim
                . $element['config_name'] . $this->delim
                . "cache_directory" . $this->delim
                . $element['cache_directory']."\n";
            echo $this->action . $this->delim
                . "SETPARAM" . $this->delim
                . $element['config_name'] . $this->delim
                . "stats_activate" . $this->delim
                . $element['stats_activate'] . "\n";
            echo $this->action . $this->delim
                . "SETPARAM" . $this->delim
                . $element['config_name'] . $this->delim
                . "correlation_activate" . $this->delim
                . $element['correlation_activate'] . "\n";
            echo $this->action . $this->delim
                . "SETPARAM" . $this->delim
                . $element['config_name'] . $this->delim
                . "daemon" . $this->delim
                . $element['daemon'] . "\n";
            $sql = "SELECT config_key, config_value, config_group, config_group_id
            		FROM cfg_centreonbroker_info
            		WHERE config_id = ?
            		ORDER BY config_group_id";
            $res = $this->db->query($sql, array($element['config_id']));
            $blockId = array();
            $categories = array();
            $addParamStr = array();
            $setParamStr = array();
            $resultSet = $res->fetchAll();
            unset($res);
            foreach ($resultSet as $row) {
                if ($row['config_key'] != 'name'
                    && $row['config_key'] != 'blockId'
                    && $row['config_key'] != 'filters'
                    && $row['config_key'] != 'category') {
                    if (!isset($setParamStr[$row['config_group'].'_'.$row['config_group_id']])) {
                        $setParamStr[$row['config_group'].'_'.$row['config_group_id']] = "";
                    }
                    $row['config_value'] = CentreonUtils::convertLineBreak($row['config_value']);
                    $setParamStr[$row['config_group'].'_'.$row['config_group_id']] .=
                        $this->action.$this->delim."SET".strtoupper($row['config_group']).
                        $this->delim.$element['config_name'].
                        $this->delim.$row['config_group_id'].
                        $this->delim.$row['config_key'].
                        $this->delim.$row['config_value']."\n";
                } elseif ($row['config_key'] == 'name') {
                    $addParamStr[$row['config_group'].'_'.$row['config_group_id']] =
                        $this->action.$this->delim."ADD".strtoupper($row['config_group']).
                        $this->delim.$element['config_name'].
                        $this->delim.$row['config_value'];
                } elseif ($row['config_key'] == 'blockId') {
                    $blockId[$row['config_group'].'_'.$row['config_group_id']] = $row['config_value'];
                } elseif ($row['config_key'] == 'category') {
                    $categories[$row['config_group'].'_'.$row['config_group_id']][] = $row['config_value'];
                }
            }
            foreach ($addParamStr as $id => $add) {
                if (isset($blockId[$id]) && isset($setParamStr[$id])) {
                    list($tag, $type) = explode('_', $blockId[$id]);
                    $resType = $this->db->query(
                        "SELECT type_shortname FROM cb_type WHERE cb_type_id = ?",
                        array($type)
                    );
                    $rowType = $resType->fetch();
                    if (isset($rowType['type_shortname'])) {
                        echo $add.$this->delim.$rowType['type_shortname']."\n";
                        echo $setParamStr[$id];
                    }
                    unset($resType);
                }
                if (isset($categories[$id])) {
                    list($configGroup, $configGroupId) = explode('_', $id);
                    echo $this->action.$this->delim."SET".strtoupper($configGroup)
                        .$this->delim.$element['config_name']
                        .$this->delim.$configGroupId
                        .$this->delim.'category'
                        .$this->delim.implode(',', $categories[$id])."\n";
                }
            }
        }
    }
}

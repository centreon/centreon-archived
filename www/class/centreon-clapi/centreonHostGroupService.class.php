<?php
/*
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
require_once "centreonUtils.class.php";
require_once "centreonTimePeriod.class.php";
require_once "centreonACL.class.php";
require_once "centreonCommand.class.php";
require_once "Centreon/Object/Instance/Instance.php";
require_once "Centreon/Object/Command/Command.php";
require_once "Centreon/Object/Timeperiod/Timeperiod.php";
require_once "Centreon/Object/Graph/Template/Template.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Host/Extended.php";
require_once "Centreon/Object/Host/Group.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Host/Macro/Custom.php";
require_once "Centreon/Object/Service/Service.php";
require_once "Centreon/Object/Service/Macro/Custom.php";
require_once "Centreon/Object/Service/Extended.php";
require_once "Centreon/Object/Contact/Contact.php";
require_once "Centreon/Object/Contact/Group.php";
require_once "Centreon/Object/Relation/Host/Template/Host.php";
require_once "Centreon/Object/Relation/Contact/Service.php";
require_once "Centreon/Object/Relation/Contact/Group/Service.php";
require_once "Centreon/Object/Relation/Host/Service.php";
require_once "Centreon/Object/Relation/Host/Group/Service/Service.php";
require_once "Centreon/Object/Relation/Service/Category/Service.php";

/**
 * Centreon Service objects
 *
 * @author sylvestre
 */
class CentreonHostGroupService extends CentreonObject
{
    const ORDER_HOSTNAME = 0;
    const ORDER_SVCDESC  = 1;
    const ORDER_SVCTPL   = 2;
    const NB_UPDATE_PARAMS = 4;

    public static $aDepends = array(
        'HOST',
        'SERVICE'
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Service();
        $this->params = array('service_is_volatile'                    => '2',
                              'service_active_checks_enabled'          => '2',
                              'service_passive_checks_enabled'         => '2',
                              'service_parallelize_check'              => '2',
                              'service_obsess_over_service'            => '2',
                              'service_check_freshness'                => '2',
                              'service_event_handler_enabled'          => '2',
                              'service_flap_detection_enabled'         => '2',
                              'service_process_perf_data'               => '2',
                              'service_retain_status_information'       => '2',
                              'service_retain_nonstatus_information'   => '2',
                              'service_notifications_enabled'           => '2',
                              'service_register'                       => '1',
                              'service_activate'                       => '1'
                              );
        $this->insertParams = array('hg_name', 'service_description', 'service_template_model_stm_id');
        $this->exportExcludedParams = array_merge($this->insertParams, array($this->object->getPrimaryKey()));
        $this->action = "HGSERVICE";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->register = 1;
        $this->activateField = 'service_activate';
    }

    /**
     * Returns type of host service relation
     *
     * @param int $serviceId
     * @return int
     */
    public function hostTypeLink($serviceId)
    {
        $sql = "SELECT host_host_id, hostgroup_hg_id FROM host_service_relation WHERE service_service_id = ?";
        $res = $this->db->query($sql, array($serviceId));
        $rows = $res->fetch();
        if (count($rows)) {
            if (isset($rows['host_host_id']) && $rows['host_host_id']) {
                return 1;
            } elseif (isset($rows['hostgroup_hg_id']) && $rows['hostgroup_hg_id']) {
                return 2;
            }
        }
        return 0;
    }

    /**
     * Check parameters
     *
     * @param string $hgName
     * @param string $serviceDescription
     * @return bool
     */
    protected function serviceExists($hgName, $serviceDescription)
    {
        $relObj = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $relObj->getMergedParameters(
            array('hg_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                'hg_name' => $hgName,
                'service_description' => $serviceDescription
            ),
            "AND"
        );
        if (count($elements)) {
            return true;
        }
        return false;
    }

    /**
     * Display all services
     *
     * @param string $parameters
     * @return void
     */
    public function show($parameters = null)
    {
        $filters = array('service_register' => $this->register);
        if (isset($parameters)) {
            $filters["service_description"] = "%".$parameters."%";
        }
        $commandObject = new \Centreon_Object_Command();
        $paramsHost = array('hg_id', 'hg_name');
        $paramsSvc = array(
            'service_id',
            'service_description',
            'command_command_id',
            'command_command_id_arg',
            'service_normal_check_interval',
            'service_retry_check_interval',
            'service_max_check_attempts',
            'service_active_checks_enabled',
            'service_passive_checks_enabled'
        );
        $relObject = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $relObject->getMergedParameters(
            $paramsHost,
            $paramsSvc,
            -1,
            0,
            "hg_name,service_description",
            "ASC",
            $filters,
            "AND"
        );
        $paramHostString = str_replace("_", " ", implode($this->delim, $paramsHost));
        echo $paramHostString . $this->delim;
        $paramSvcString = str_replace("service_", "", implode($this->delim, $paramsSvc));
        $paramSvcString = str_replace("command_command_id", "check command", $paramSvcString);
        $paramSvcString = str_replace("command_command_id_arg", "check command arguments", $paramSvcString);
        $paramSvcString = str_replace("_", " ", $paramSvcString);
        echo $paramSvcString."\n";
        foreach ($elements as $tab) {
            if (isset($tab['command_command_id']) && $tab['command_command_id']) {
                $tmp = $commandObject->getParameters(
                    $tab['command_command_id'],
                    array($commandObject->getUniqueLabelField())
                );
                if (isset($tmp[$commandObject->getUniqueLabelField()])) {
                    $tab['command_command_id'] = $tmp[$commandObject->getUniqueLabelField()];
                }
            }
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * Delete service
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function del($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $hgName = $params[0];
        $serviceDesc = $params[1];
        $relObject = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $relObject->getMergedParameters(
            array("hg_id"),
            array("service_id"),
            -1,
            0,
            null,
            null,
            array(
                "hg_name" => $hgName,
                "service_description" => $serviceDesc
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$hgName."/".$serviceDesc);
        }
        $this->object->delete($elements[0]['service_id']);
    }

    /**
     * Get clapi action name from db column name
     *
     * @param string $columnName
     * @return string
     */
    protected function getClapiActionName($columnName)
    {
        static $table;

        if (!isset($table)) {
            $table = array("command_command_id"      => "check_command",
                           "command_command_id2"     => "event_handler",
                           "timeperiod_tp_id"        => "check_period",
                           "timeperiod_tp_id2"       => "notification_period",
                           "command_command_id_arg"  => "check_command_arguments",
                           "command_command_id_arg2" => "event_handler_arguments");
        }
        if (preg_match("/^esi_/", $columnName)) {
            return ltrim($columnName, "esi_");
        }
        if (isset($table[$columnName])) {
            return $table[$columnName];
        }
        return $columnName;
    }

    /**
     * Add a service
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function add($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if ($this->serviceExists($params[self::ORDER_HOSTNAME], $params[self::ORDER_SVCDESC]) == true) {
            throw new CentreonClapiException(self::OBJECTALREADYEXISTS);
        }
        $hgObject = new \Centreon_Object_Host_Group();
        $tmp = $hgObject->getIdByParameter($hgObject->getUniqueLabelField(), $params[self::ORDER_HOSTNAME]);
        if (!count($tmp)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_HOSTNAME]);
        }
        $hgId = $tmp[0];
        $addParams = array();
        $addParams['service_description'] = $params[self::ORDER_SVCDESC];
        $template = $params[self::ORDER_SVCTPL];
        $tmp = $this->object->getList(
            $this->object->getPrimaryKey(),
            -1,
            0,
            null,
            null,
            array('service_description' => $template, 'service_register' => '0'),
            "AND"
        );
        if (!count($tmp)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $template);
        }
        $addParams['service_template_model_stm_id'] = $tmp[0][$this->object->getPrimaryKey()];
        $this->params = array_merge($this->params, $addParams);
        $serviceId = parent::add();

        $relObject = new \Centreon_Object_Relation_Host_Group_Service();
        $relObject->insert($hgId, $serviceId);

        $extended = new \Centreon_Object_Service_Extended();
        $extended->insert(array($extended->getUniqueLabelField() => $serviceId));
    }

    /**
     * Set parameters
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function setparam($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $hgName = $params[0];
        $serviceDesc = $params[1];
        $relObject = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $relObject->getMergedParameters(
            array("hg_id"),
            array("service_id"),
            -1,
            0,
            null,
            null,
            array(
                "hg_name" => $hgName,
                "service_description" => $serviceDesc
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$hgName."/".$serviceDesc);
        }
        $objectId = $elements[0]['service_id'];
        $extended = false;
        $commandObject = new CentreonCommand();
        switch ($params[2]) {
            case "contact_additive_inheritance":
                break;
            case "cg_additive_inheritance":
                break;
            case "check_command":
                $params[2] = "command_command_id";
                $params[3] = $commandObject->getId($params[3]);
                break;
            case "check_command_arguments":
                $params[2] = "command_command_id_arg";
                break;
            case "event_handler":
                $params[2] = "command_command_id2";
                $params[3] = $commandObject->getId($params[3]);
                break;
            case "event_handler_arguments":
                $params[2] = "command_command_id_arg2";
                break;
            case "check_period":
                $params[2] = "timeperiod_tp_id";
                $tpObj = new CentreonTimePeriod();
                $params[3] = $tpObj->getTimeperiodId($params[3]);
                break;
            case "notification_period":
                $params[2] = "timeperiod_tp_id2";
                $tpObj = new CentreonTimePeriod();
                $params[3] = $tpObj->getTimeperiodId($params[3]);
                break;
            case "flap_detection_options":
                break;
            case "template":
                $params[2] = "service_template_model_stm_id";
                $tmp = $this->object->getList(
                    $this->object->getPrimaryKey(),
                    -1,
                    0,
                    null,
                    null,
                    array('service_description' => $params[3], 'service_register' => '0'),
                    "AND"
                );
                if (!count($tmp)) {
                    throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[3]);
                }
                $params[3] = $tmp[0][$this->object->getPrimaryKey()];
                break;
            case "graphtemplate":
                $extended = true;
                $graphObj = new \Centreon_Object_Graph_Template();
                $tmp = $graphObj->getIdByParameter($graphObj->getUniqueLabelField(), $params[3]);
                if (!count($tmp)) {
                    throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[3]);
                }
                $params[2] = "graph_id";
                $params[3] = $tmp[0];
                break;
            case "notes":
                $extended = true;
                break;
            case "notes_url":
                $extended = true;
                break;
            case "action_url":
                $extended = true;
                break;
            case "icon_image":
                $extended = true;
                break;
            case "icon_image_alt":
                $extended = true;
                break;
            default:
                if (!preg_match("/^service_/", $params[2])) {
                    $params[2] = "service_".$params[2];
                }
                break;
        }
        if ($extended == false) {
            $updateParams = array($params[2] => $params[3]);
            parent::setparam($objectId, $updateParams);
        } else {
            if ($params[2] != "graph_id") {
                $params[2] = "esi_".$params[2];
                if ($params[2] == "esi_icon_image") {
                    if ($params[3]) {
                        $id = CentreonUtils::getImageId($params[3]);
                        if (is_null($id)) {
                            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[3]);
                        }
                        $params[3] = $id;
                    } else {
                        $params[3] = null;
                    }
                }
            }
            $extended = new \Centreon_Object_Service_Extended();
            $extended->update($objectId, array($params[2] => $params[3]));
        }
    }

    /**
     * Strip macro
     *
     * @param string $macroName
     * @return string
     */
    protected function stripMacro($macroName)
    {
        $strippedMacro = ltrim($macroName, "\$_SERVICE");
        $strippedMacro = rtrim($strippedMacro, "\$");
        return strtolower($strippedMacro);
    }

    /**
     * Wrap macro
     *
     * @param string $macroName
     * @return string
     */
    protected function wrapMacro($macroName)
    {
        $wrappedMacro = "\$_SERVICE".strtoupper($macroName)."\$";
        return $wrappedMacro;
    }

    /**
     * Get macro list of a service
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function getmacro($parameters)
    {
        $tmp = explode($this->delim, $parameters);
        if (count($tmp) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $hgName = $tmp[0];
        $serviceDescription = $tmp[1];
        $relObject = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $relObject->getMergedParameters(
            array('hg_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                "hg_name" => $hgName,
                "service_description" => $serviceDescription
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$hgName."/".$serviceDescription);
        }
        $macroObj = new \Centreon_Object_Service_Macro_Custom();
        $macroList = $macroObj->getList(
            array("svc_macro_name", "svc_macro_value"),
            -1,
            0,
            null,
            null,
            array("svc_svc_id" => $elements[0]['service_id'])
        );
        echo "macro name;macro value\n";
        foreach ($macroList as $macro) {
            echo $macro['svc_macro_name'] . $this->delim . $macro['svc_macro_value'] . "\n";
        }
    }

    /**
     * Inserts/updates custom macro
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function setmacro($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 4) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $hgName = $params[0];
        $serviceDescription = $params[1];
        $relObject = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $relObject->getMergedParameters(
            array('hg_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                "hg_name" => $hgName,
                "service_description" => $serviceDescription
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$hgName."/".$serviceDescription);
        }
        $macroObj = new \Centreon_Object_Service_Macro_Custom();
        $macroList = $macroObj->getList(
            $macroObj->getPrimaryKey(),
            -1,
            0,
            null,
            null,
            array(
                "svc_svc_id"      => $elements[0]['service_id'],
                "svc_macro_name" => $this->wrapMacro($params[2])
            ),
            "AND"
        );
        if (count($macroList)) {
            $macroObj->update($macroList[0][$macroObj->getPrimaryKey()], array('svc_macro_value' => $params[3]));
        } else {
            $macroObj->insert(array('svc_svc_id'       => $elements[0]['service_id'],
                                    'svc_macro_name'  => $this->wrapMacro($params[2]),
                                    'svc_macro_value' => $params[3]));
        }
    }

    /**
     * Delete custom macro
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function delmacro($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 3) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $hgName = $params[0];
        $serviceDescription = $params[1];
        $relObject = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $relObject->getMergedParameters(
            array('hg_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                "hg_name" => $hgName,
                "service_description" => $serviceDescription
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$hgName."/".$serviceDescription);
        }
        $macroObj = new \Centreon_Object_Service_Macro_Custom();
        $macroList = $macroObj->getList(
            $macroObj->getPrimaryKey(),
            -1,
            0,
            null,
            null,
            array(
                "svc_svc_id" => $elements[0]['service_id'],
                "svc_macro_name" => $this->wrapMacro($params[2])
            ),
            "AND"
        );
        if (count($macroList)) {
            $macroObj->delete($macroList[0][$macroObj->getPrimaryKey()]);
        }
    }

    /**
     * @param $parameters
     * @throws CentreonClapiException
     */
    public function setseverity($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 3) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $hgName = $params[0];
        $serviceDescription = $params[1];
        $rel = new \Centreon_Object_Relation_Service_Category_Service();
        $relObject = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $relObject->getMergedParameters(
            array('hg_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                "hg_name" => $hgName,
                "service_description" => $serviceDescription
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[0] . "/" . $params[1]);
        }
        $serviceId = $elements[0]['service_id'];
        $severityObj = new \Centreon_Object_Service_Category();
        $severity = $severityObj->getIdByParameter(
            $severityObj->getUniqueLabelField(),
            $params[2]
        );
        if (!isset($severity[0])) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[2]);
        }
        $severityId = $severity[0];
        $severity = $severityObj->getParameters(
            $severityId,
            array('level')
        );
        if ($severity['level']) {
            // can't delete with generic method
            $this->db->query(
                "DELETE FROM service_categories_relation
                WHERE service_service_id = ?
                AND sc_id IN (SELECT sc_id FROM service_categories WHERE level > 0)",
                $serviceId
            );
            $rel->insert($severityId, $serviceId);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[2]);
        }
    }

    /**
     * @param $parameters
     * @throws CentreonClapiException
     */
    public function unsetseverity($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $hgName = $params[0];
        $serviceDescription = $params[1];
        $hostServiceRel = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $hostServiceRel->getMergedParameters(
            array('hg_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                "hg_name" => $hgName,
                "service_description" => $serviceDescription
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[0] . "/" . $params[1]);
        }
        $serviceId = $elements[0]['service_id'];

        // can't delete with generic method
        $this->db->query(
            "DELETE FROM service_categories_relation
             WHERE service_service_id = ?
             AND sc_id IN (SELECT sc_id FROM service_categories WHERE level > 0)",
            $serviceId
        );
    }

    /**
     * Get Object Name
     *
     * @param int $id
     * @return string
     */
    public function getObjectName($id)
    {
        $tmp = $this->object->getParameters($id, array('service_description'));
        if (isset($tmp['service_description'])) {
            return $tmp['service_description'];
        }
        return "";
    }

    /**
     * Set the activate field
     *
     * @param string $objectName
     * @param int $value
     * @throws CentreonClapiException
     */
    protected function activate($objectName, $value)
    {
        if (!isset($objectName) || !$objectName) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $tmp = explode($this->delim, $objectName);
        if (count($tmp) != 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $relObject = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $relObject->getMergedParameters(
            array('hg_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                "hg_name" => $tmp[0],
                "service_description" => $tmp[1]
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$tmp[0]."/".$tmp[1]);
        }
        if (isset($this->activateField)) {
            $this->object->update($elements[0]['service_id'], array($this->activateField => $value));
        }
    }

    /**
     * Magic method
     *
     * @param string $name
     * @param array $args
     * @return void
     * @throws CentreonClapiException
     */
    public function __call($name, $arg)
    {
        /* Get the method name */
        $name = strtolower($name);
        /* Get the action and the object */
        if (preg_match("/^(get|set|add|del)([a-zA-Z_]+)/", $name, $matches)) {
            switch ($matches[2]) {
                case "hostgroup":
                    $class = "Centreon_Object_Host_Group";
                    $relclass = "Centreon_Object_Relation_Host_Group_Service";
                    break;
                case "contact":
                    $class = "Centreon_Object_Contact";
                    $relclass = "Centreon_Object_Relation_Contact_Service";
                    break;
                case "contactgroup":
                    $class = "Centreon_Object_Contact_Group";
                    $relclass = "Centreon_Object_Relation_Contact_Group_Service";
                    break;
                default:
                    throw new CentreonClapiException(self::UNKNOWN_METHOD);
                    break;
            }
            if (class_exists($relclass) && class_exists($class)) {
                /* Parse arguments */
                if (!isset($arg[0]) || !$arg[0]) {
                    throw new CentreonClapiException(self::MISSINGPARAMETER);
                }
                $args = explode($this->delim, $arg[0]);
                $relObject = new \Centreon_Object_Relation_Host_Group_Service();
                $elements = $relObject->getMergedParameters(
                    array('hg_id'),
                    array('service_id'),
                    -1,
                    0,
                    null,
                    null,
                    array(
                        "hg_name" => $args[0],
                        "service_description" => $args[1]
                    ),
                    "AND"
                );
                if (!count($elements)) {
                    throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$args[0]."/".$args[1]);
                }
                $serviceId = $elements[0]['service_id'];

                $relobj = new $relclass();
                $obj = new $class();
                if ($matches[1] == "get") {
                    $tab = $relobj->getTargetIdFromSourceId(
                        $relobj->getFirstKey(),
                        $relobj->getSecondKey(),
                        $serviceId
                    );
                    echo "id".$this->delim."name"."\n";
                    foreach ($tab as $value) {
                        if ($value) {
                            $tmp = $obj->getParameters($value, array($obj->getUniqueLabelField()));
                            echo $value . $this->delim . $tmp[$obj->getUniqueLabelField()] . "\n";
                        }
                    }
                } else {
                    if (!isset($args[1]) || !isset($args[2])) {
                        throw new CentreonClapiException(self::MISSINGPARAMETER);
                    }
                    if ($matches[2] == "contact") {
                        $args[2] = str_replace(" ", "_", $args[2]);
                    }
                    $relation = $args[2];
                    $relations = explode("|", $relation);
                    $relationTable = array();
                    foreach ($relations as $rel) {
                        if ($matches[1] != "del"
                            && $matches[2] == "hostgroup"
                            && $this->serviceExists($rel, $args[1])) {
                            throw new CentreonClapiException(self::OBJECTALREADYEXISTS);
                        }
                        $tab = $obj->getIdByParameter($obj->getUniqueLabelField(), array($rel));
                        if (!count($tab)) {
                            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":".$rel);
                        }
                        $relationTable[] = $tab[0];
                    }
                    $existingRelationIds = $relobj->getTargetIdFromSourceId(
                        $relobj->getFirstKey(),
                        $relobj->getSecondKey(),
                        $serviceId
                    );
                    if ($matches[1] == "set") {
                        $relobj->delete(null, $serviceId);
                    }
                    foreach ($relationTable as $relationId) {
                        if ($matches[1] == "del") {
                            $relobj->delete($relationId, $serviceId);
                        } elseif ($matches[1] == "set" || $matches[1] == "add") {
                            if (!in_array($relationId, $existingRelationIds)) {
                                $relobj->insert($relationId, $serviceId);
                            }
                        }
                    }
                }
            } else {
                throw new CentreonClapiException(self::UNKNOWN_METHOD);
            }
        } else {
            throw new CentreonClapiException(self::UNKNOWN_METHOD);
        }
    }

    /**
     * Export
     *
     * @return void
     */
    public function export($filter_name)
    {
        if (!$this->canBeExported($filter_name)) {
            return 0;
        }

        $labelField = $this->object->getUniqueLabelField();
        $filters = array("service_register" => $this->register);
        if (!is_null($filter_name)) {
            $filters[$labelField] = $filter_name;
        }
        $hostRel = new \Centreon_Object_Relation_Host_Group_Service();
        $elements = $hostRel->getMergedParameters(
            array("hg_name"),
            array('*'),
            -1,
            0,
            null,
            null,
            $filters,
            "AND"
        );
        $extendedObj = new \Centreon_Object_Service_Extended();
        $commandObj = new \Centreon_Object_Command();
        $tpObj = new \Centreon_Object_Timeperiod();
        $macroObj = new \Centreon_Object_Service_Macro_Custom();
        foreach ($elements as $element) {
            $addStr = $this->action.$this->delim."ADD";
            foreach ($this->insertParams as $param) {
                $addStr .= $this->delim;
                if ($param == "service_template_model_stm_id") {
                    $tmp_id = $element[$param];
                    $tmp = $this->object->getParameters($element[$param], 'service_description');
                    if (isset($tmp) && isset($tmp['service_description']) && $tmp['service_description']) {
                        $element[$param] = $tmp['service_description'];
                        CentreonServiceTemplate::getInstance()->export($tmp['service_description']);
                    }
                    if (!$element[$param]) {
                        $element[$param] = "";
                    }
                }
                $addStr .= $element[$param];
            }
            $addStr .= "\n";
            echo $addStr;
            foreach ($element as $parameter => $value) {
                if (!in_array($parameter, $this->exportExcludedParams) && !is_null($value) && $value != "") {
                    $action_tmp = null;
                    if ($parameter == "timeperiod_tp_id" || $parameter == "timeperiod_tp_id2") {
                        $action_tmp = 'TP';
                        $tmpObj = $tpObj;
                    } elseif ($parameter == "command_command_id" || $parameter == "command_command_id2") {
                        $action_tmp = 'CMD';
                        $tmpObj = $commandObj;
                    }
                    if (isset($tmpObj)) {
                        $tmp = $tmpObj->getParameters($value, $tmpObj->getUniqueLabelField());
                        if (isset($tmp) && isset($tmp[$tmpObj->getUniqueLabelField()])) {
                            $tmp_id = $value;
                            $value = $tmp[$tmpObj->getUniqueLabelField()];
                            $tmpObj::getInstance()->export($value);
                        }
                        unset($tmpObj);
                    }
                    echo $this->action . $this->delim
                        . "setparam" . $this->delim
                        . $element['hg_name'] . $this->delim
                        . $element['service_description'] . $this->delim
                        . $this->getClapiActionName($parameter) . $this->delim
                        . $value . "\n";
                }
            }
            $params = $extendedObj->getParameters(
                $element[$this->object->getPrimaryKey()],
                array(
                    "esi_notes",
                    "esi_notes_url",
                    "esi_action_url",
                    "esi_icon_image",
                    "esi_icon_image_alt"
                )
            );
            if (isset($params) && is_array($params)) {
                foreach ($params as $k => $v) {
                    if (!is_null($v) && $v != "") {
                        echo $this->action . $this->delim
                            . "setparam" . $this->delim
                            . $element['hg_name'] . $this->delim
                            . $element['service_description'] . $this->delim
                            . $this->getClapiActionName($k) . $this->delim
                            . $v . "\n";
                    }
                }
            }
            $macros = $macroObj->getList(
                "*",
                -1,
                0,
                null,
                null,
                array($macroObj->getPrimaryKey() => $element[$this->object->getPrimaryKey()]),
                "AND"
            );
            foreach ($macros as $macro) {
                echo $this->action . $this->delim
                    . "setmacro" . $this->delim
                    . $element['hg_name'] . $this->delim
                    . $element['service_description'] . $this->delim
                    . $this->stripMacro($macro['svc_macro_name']) . $this->delim
                    . $macro['svc_macro_value'] . "\n";
            }
            $cgRel = new \Centreon_Object_Relation_Contact_Group_Service();
            $cgelements = $cgRel->getMergedParameters(
                array("cg_name", "cg_id"),
                array('service_description'),
                -1,
                0,
                null,
                null,
                array(
                    "service_register" => $this->register,
                    "service_id" => $element['service_id']
                ),
                "AND"
            );
            foreach ($cgelements as $cgelement) {
                CentreonContactGroup::getInstance()->export($element['cg_name']);
                echo $this->action . $this->delim
                    . "addcontactgroup" . $this->delim
                    . $element['hg_name'] . $this->delim
                    . $cgelement['service_description'] . $this->delim
                    . $cgelement['cg_name'] . "\n";
            }
            $contactRel = new \Centreon_Object_Relation_Contact_Service();
            $celements = $contactRel->getMergedParameters(
                array("contact_name", "contact_id"),
                array('service_description'),
                -1,
                0,
                null,
                null,
                array(
                    "service_register" => $this->register,
                    "service_id" => $element['service_id']
                ),
                "AND"
            );
            foreach ($celements as $celement) {
                CentreonContact::getInstance()->export($element['contact_name']);
                echo $this->action . $this->delim
                    . "addcontact" . $this->delim
                    . $element['hg_name'] . $this->delim
                    . $celement['service_description'] . $this->delim
                    . $celement['contact_name'] . "\n";
            }
        }
    }
}

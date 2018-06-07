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
require_once "Centreon/Object/Trap/Trap.php";
require_once "Centreon/Object/Relation/Host/Template/Host.php";
require_once "Centreon/Object/Relation/Contact/Service.php";
require_once "Centreon/Object/Relation/Contact/Group/Service.php";
require_once "Centreon/Object/Relation/Host/Service.php";
require_once "Centreon/Object/Relation/Host/Group/Service/Service.php";
require_once "Centreon/Object/Relation/Trap/Service.php";
require_once "Centreon/Object/Relation/Service/Category/Service.php";

/**
 * Centreon Service objects
 *
 * @author sylvestre
 */
class CentreonService extends CentreonObject
{

    const ORDER_HOSTNAME = 0;
    const ORDER_SVCDESC = 1;
    const ORDER_SVCTPL = 2;
    const NB_UPDATE_PARAMS = 4;
    const UNKNOWN_NOTIFICATION_OPTIONS = "Invalid notifications options";

    public static $aDepends = array(
        'CMD',
        'TP',
        'TRAP',
        'HOST',
        'STPL'
    );

    /**
     *
     * @var array
     * Contains : list of authorized notifications_options for this objects
     */
    public static $aAuthorizedNotificationsOptions = array(
        'w' => 'Warning',
        'u' => 'Unreachable',
        'c' => 'Critical',
        'r' => 'Recovery',
        'f' => 'Flapping',
        's' => 'Downtime Scheduled'
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
        $this->params = array('service_is_volatile' => '2',
            'service_active_checks_enabled' => '2',
            'service_passive_checks_enabled' => '2',
            'service_parallelize_check' => '2',
            'service_obsess_over_service' => '2',
            'service_check_freshness' => '2',
            'service_event_handler_enabled' => '2',
            'service_flap_detection_enabled' => '2',
            'service_process_perf_data' => '2',
            'service_retain_status_information' => '2',
            'service_retain_nonstatus_information' => '2',
            'service_notifications_enabled' => '2',
            'service_register' => '1',
            'service_activate' => '1'
        );
        $this->insertParams = array('host_name', 'service_description', 'service_template_model_stm_id');
        $this->exportExcludedParams = array_merge($this->insertParams, array($this->object->getPrimaryKey()));
        $this->action = "SERVICE";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->register = 1;
        $this->activateField = 'service_activate';
    }

    /**
     * Get Object Id
     *
     * @param string $name
     * @return int
     */
    public function getObjectId($name)
    {
        if (isset($this->objectIds[$name])) {
            return $this->objectIds[$name];
        }

        if (preg_match('/^.+;.+$/', $name, $matches)) {
            $ids = $this->getHostAndServiceId($matches[1], $matches[2]);
            if (isset($ids[1])) {
                $this->objectIds[$name] = $ids[1];
                return $this->objectIds[$name];
            }
        } else {
            return parent::getObjectId($name);
        }

        return 0;
    }

    /**
     * Return the host id and service id if the combination does exist
     *
     * @param string $host
     * @param string $service
     * @return array | array($hostId, $serviceId)
     */
    public function getHostAndServiceId($host, $service)
    {
        /* Regular services */
        $sql = "SELECT h.host_id, s.service_id
            FROM host h, service s, host_service_relation hsr
            WHERE h.host_id = hsr.host_host_id
            AND hsr.service_service_id = s.service_id
            AND h.host_name = ?
            AND s.service_description = ?";
        $res = $this->db->query($sql, array($host, $service));
        $row = $res->fetchAll();
        if (count($row)) {
            return array($row[0]['host_id'], $row[0]['service_id']);
        }

        /* Service by hostgroup */
        $sql = "SELECT h.host_id, s.service_id
            FROM host h, service s, host_service_relation hsr, hostgroup_relation hgr
            WHERE h.host_id = hgr.host_host_id
            AND hgr.hostgroup_hg_id = hsr.hostgroup_hg_id
            AND hsr.service_service_id = s.service_id
            AND h.host_name = ?
            AND s.service_description = ?";
        $res = $this->db->query($sql, array($host, $service));
        $row = $res->fetchAll();
        if (count($row)) {
            return array($row[0]['host_id'], $row[0]['service_id']);
        }

        /* nothing found, return empty array */
        return array();
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
     * @param string $hostName
     * @param string $serviceDescription
     * @return bool
     */
    public function serviceExists($hostName, $serviceDescription)
    {
        $relObj = new \Centreon_Object_Relation_Host_Service();
        $elements = $relObj->getMergedParameters(
            array('host_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                'host_name' => $hostName,
                'service_description' => $serviceDescription),
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
            $filters["service_description"] = "%" . $parameters . "%";
        }
        $commandObject = new \Centreon_Object_Command();
        $paramsHost = array('host_id', 'host_name');
        $paramsSvc = array('service_id', 'service_description', 'command_command_id', 'command_command_id_arg',
            'service_normal_check_interval', 'service_retry_check_interval', 'service_max_check_attempts',
            'service_active_checks_enabled', 'service_passive_checks_enabled', 'service_activate');
        $relObject = new \Centreon_Object_Relation_Host_Service();
        $elements = $relObject->getMergedParameters(
            $paramsHost,
            $paramsSvc,
            -1,
            0,
            "host_name,service_description",
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
        echo $paramSvcString . "\n";
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
        $hostName = $params[0];
        $serviceDesc = $params[1];
        $relObject = new \Centreon_Object_Relation_Host_Service();
        $elements = $relObject->getMergedParameters(
            array("host_id"),
            array("service_id"),
            -1,
            0,
            null,
            null,
            array(
                "host_name" => $hostName,
                "service_description" => $serviceDesc
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $hostName . "/" . $serviceDesc);
        }
        $this->object->delete($elements[0]['service_id']);
        $this->addAuditLog('d', $elements[0]['service_id'], $hostName . " - " . $serviceDesc);
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
            $table = array("command_command_id" => "check_command",
                "command_command_id2" => "event_handler",
                "timeperiod_tp_id" => "check_period",
                "timeperiod_tp_id2" => "notification_period",
                "command_command_id_arg" => "check_command_arguments",
                "command_command_id_arg2" => "event_handler_arguments");
        }
        if (preg_match("/^esi_/", $columnName)) {
            return substr($columnName, strlen("esi_"));
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
        $hostObject = new \Centreon_Object_Host();
        $tmp = $hostObject->getIdByParameter($hostObject->getUniqueLabelField(), $params[self::ORDER_HOSTNAME]);
        if (!count($tmp)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_HOSTNAME]);
        }
        $hostId = $tmp[0];
        $addParams = array();
        $addParams['service_description'] = $this->checkIllegalChar($params[self::ORDER_SVCDESC]);
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

        $relObject = new \Centreon_Object_Relation_Host_Service();
        $relObject->insert($hostId, $serviceId);

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
        $hostName = $params[0];
        $serviceDesc = $params[1];
        $relObject = new \Centreon_Object_Relation_Host_Service();
        $elements = $relObject->getMergedParameters(
            array("host_id"),
            array("service_id"),
            -1,
            0,
            null,
            null,
            array(
                "host_name" => $hostName,
                "service_description" => $serviceDesc
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $hostName . "/" . $serviceDesc);
        }
        $objectId = $elements[0]['service_id'];
        $extended = false;
        $commandObject = new CentreonCommand();
        switch ($params[2]) {
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
            case "contact_additive_inheritance":
                break;
            case "cg_additive_inheritance":
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
            case "service_notification_options":
                $aNotifs = explode(",", $params[3]);
                foreach ($aNotifs as $notif) {
                    if (!array_key_exists($notif, self::$aAuthorizedNotificationsOptions)) {
                        throw new CentreonClapiException(self::UNKNOWN_NOTIFICATION_OPTIONS);
                    }
                }
                break;
            default:
                if (!preg_match("/^service_/", $params[2])) {
                    $params[2] = "service_" . $params[2];
                }
                break;
        }
        if ($extended == false) {
            $updateParams = array($params[2] => $params[3]);
            if ($params[2] == 'service_description' && $this->serviceExists($hostName, $params[3])) {
                throw new CentreonClapiException(self::OBJECTALREADYEXISTS);
            }
            $this->object->update($objectId, $updateParams);
            $this->addAuditLog('c', $objectId, $hostName . ' - ' . $serviceDesc, $updateParams);
        } else {
            if ($params[2] != "graph_id") {
                $params[2] = "esi_" . $params[2];
                if ($params[2] == "esi_icon_image") {
                    if ($params[3]) {
                        $id = CentreonUtils::getImageId($params[3]);
                        if (is_null($id)) {
                            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[3]);
                        }
                        $params[3] = $id;
                    } else {
                        $params[3] = null;
                    }
                }
            }
            $extended = new \Centreon_Object_Service_Extended();
            $extended->update($objectId, array($params[2] => $params[3]));
            $this->addAuditLog('c', $objectId, $hostName . ' - ' . $serviceDesc, array($params[2] => $params[3]));
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
        $strippedMacro = $macroName;
        if (preg_match('/\$_SERVICE([a-zA-Z0-9_-]+)\$/', $strippedMacro, $matches)) {
            $strippedMacro = $matches[1];
        }
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
        $wrappedMacro = "\$_SERVICE" . strtoupper($macroName) . "\$";
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
        $hostName = $tmp[0];
        $serviceDescription = $tmp[1];
        $relObject = new \Centreon_Object_Relation_Host_Service();
        $elements = $relObject->getMergedParameters(
            array('host_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                "host_name" => $hostName,
                "service_description" => $serviceDescription
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $hostName . "/" . $serviceDescription);
        }
        $macroObj = new \Centreon_Object_Service_Macro_Custom();
        $macroList = $macroObj->getList(
            array(
                "svc_macro_name",
                "svc_macro_value",
                "is_password",
                "description"
            ),
            -1,
            0,
            null,
            null,
            array("svc_svc_id" => $elements[0]['service_id'])
        );
        $aListTemplate = $this->getListTemplates($this->db, $elements[0]['service_id']);

        if (!isset($cmdId)) {
            $cmdId = "";
        }
        $macroList = $this->getMacros($elements[0]['service_id'], $aListTemplate, $cmdId);
        echo "macro name;macro value;is_password;description;source\n";
        foreach ($macroList as $macro) {
            $source = "direct";
            if ($macro["source"] == "fromTpl") {
                $source = $macro["macroTpl"];
            }
            echo $macro['svc_macro_name'] . $this->delim
                . $macro['svc_macro_value'] . $this->delim
                . $macro['is_password'] . $this->delim
                . $macro['description'] . $this->delim
                . $source . "\n";
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
        if (count($params) == 4) {
            $params[4] = 0;
        }
        if (count($params) < 4) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $hostName = $params[0];
        $serviceDescription = $params[1];
        $relObject = new \Centreon_Object_Relation_Host_Service();
        $elements = $relObject->getMergedParameters(
            array('host_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                "host_name" => $hostName,
                "service_description" => $serviceDescription
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $hostName . "/" . $serviceDescription);
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

        $maxOrder = $macroObj->getList(
            'max(macro_order)',
            -1,
            0,
            null,
            null,
            array("svc_svc_id" => $elements[0]['service_id'])
        );
        if (empty($maxOrder)) {
            $macroOrder = 0;
        } else {
            $macroOrder = $maxOrder[0]["max(macro_order)"] + 1;
        }
        // disable the check if the macro added is already in service template with same value
        //if($this->hasMacroFromServiceChanged($this->db, $elements[0]['service_id'], $params[2], $params[3])){
        if (count($macroList)) {
            $macroObj->update(
                $macroList[0][$macroObj->getPrimaryKey()],
                array(
                    'svc_macro_value' => $params[3],
                    'is_password' => $params[4],
                    'description' => $params[5]
                )
            );
        } else {
            $macroObj->insert(
                array(
                    'svc_svc_id' => $elements[0]['service_id'],
                    'svc_macro_name' => $this->wrapMacro($params[2]),
                    'svc_macro_value' => $params[3],
                    'is_password' => $params[4],
                    'description' => $params[5],
                    'macro_order' => $macroOrder
                )
            );
        }
        $this->addAuditLog(
            'c',
            $elements[0]['service_id'],
            $hostName . ' - ' . $serviceDescription,
            array($params[2] => $params[3])
        );
        //}
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
        $hostName = $params[0];
        $serviceDescription = $params[1];
        $relObject = new \Centreon_Object_Relation_Host_Service();
        $elements = $relObject->getMergedParameters(
            array('host_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array("host_name" => $hostName,
            "service_description" => $serviceDescription),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $hostName . "/" . $serviceDescription);
        }
        $macroObj = new \Centreon_Object_Service_Macro_Custom();
        $macroList = $macroObj->getList(
            $macroObj->getPrimaryKey(),
            -1,
            0,
            null,
            null,
            array("svc_svc_id" => $elements[0]['service_id'],
            "svc_macro_name" => $this->wrapMacro($params[2])),
            "AND"
        );
        if (count($macroList)) {
            $macroObj->delete($macroList[0][$macroObj->getPrimaryKey()]);
        }
        $this->addAuditLog(
            'c',
            $elements[0]['service_id'],
            $hostName . ' - ' . $serviceDescription,
            array($params[2] => '')
        );
    }

    /**
     * Set severity
     *
     * @param string $parameters
     */
    public function setseverity($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 3) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $rel = new \Centreon_Object_Relation_Service_Category_Service();
        $hostServiceRel = new \Centreon_Object_Relation_Host_Service();
        $elements = $hostServiceRel->getMergedParameters(
            array('host_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                "host_name" => $params[0],
                "service_description" => $params[1]
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
     * Unset severity
     *
     * @param string $parameters
     */
    public function unsetseverity($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $rel = new \Centreon_Object_Relation_Service_Category_Service();
        $hostServiceRel = new \Centreon_Object_Relation_Host_Service();
        $elements = $hostServiceRel->getMergedParameters(
            array('host_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array(
                "host_name" => $params[0],
                "service_description" => $params[1]
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
        $relObject = new \Centreon_Object_Relation_Host_Service();
        $elements = $relObject->getMergedParameters(
            array('host_id'),
            array('service_id'),
            -1,
            0,
            null,
            null,
            array("host_name" => $tmp[0],
            "service_description" => $tmp[1]),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $tmp[0] . "/" . $tmp[1]);
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
                case "host":
                    $class = "Centreon_Object_Host";
                    $relclass = "Centreon_Object_Relation_Host_Service";
                    break;
                case "contact":
                    $class = "Centreon_Object_Contact";
                    $relclass = "Centreon_Object_Relation_Contact_Service";
                    break;
                case "contactgroup":
                    $class = "Centreon_Object_Contact_Group";
                    $relclass = "Centreon_Object_Relation_Contact_Group_Service";
                    break;
                case "trap":
                    $class = "Centreon_Object_Trap";
                    $relclass = "Centreon_Object_Relation_Trap_Service";
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
                $relObject = new \Centreon_Object_Relation_Host_Service();
                $elements = $relObject->getMergedParameters(
                    array('host_id'),
                    array('service_id'),
                    -1,
                    0,
                    null,
                    null,
                    array("host_name" => $args[0],
                    "service_description" => $args[1]),
                    "AND"
                );
                if (!count($elements)) {
                    throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $args[0] . "/" . $args[1]);
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
                    echo "id" . $this->delim . "name" . "\n";
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
                    $relation = $args[2];
                    $relations = explode("|", $relation);
                    $relationTable = array();
                    foreach ($relations as $rel) {
                        if ($matches[1] != "del" && $matches[2] == "host" && $this->serviceExists($rel, $args[1])) {
                            throw new CentreonClapiException(self::OBJECTALREADYEXISTS);
                        }
                        if ($matches[2] == "contact") {
                            $tab = $obj->getIdByParameter("contact_alias", array($rel));
                        } else {
                            $tab = $obj->getIdByParameter($obj->getUniqueLabelField(), array($rel));
                        }
                        if (!count($tab)) {
                            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $rel);
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
            return false;
        }

        $labelField = $this->object->getUniqueLabelField();
        $filters = array("service_register" => $this->register);
        $filterId = null;
        if (!is_null($filter_name)) {
            $filterId = $this->getObjectId($filter_name);
            $filters['service_id'] = $filterId;
        }

        $hostRel = new \Centreon_Object_Relation_Host_Service();
        $elements = $hostRel->getMergedParameters(
            array("host_name", "host_id"),
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
            $addStr = $this->action . $this->delim . "ADD";
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

            # Host Filter
            CentreonHost::getInstance()->export($element['host_name']);

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
                    $value = CentreonUtils::convertLineBreak($value);

                    if ($this->getClapiActionName($parameter) != "host_id") {
                        echo $this->action . $this->delim . "setparam" . $this->delim
                            . $element['host_name'] . $this->delim
                            . $element['service_description'] . $this->delim
                            . $this->getClapiActionName($parameter) . $this->delim
                            . $value . "\n";
                    }
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
                        $v = CentreonUtils::convertLineBreak($v);
                        echo $this->action . $this->delim . "setparam" . $this->delim
                            . $element['host_name'] . $this->delim
                            . $element['service_description'] . $this->delim
                            . $this->getClapiActionName($k) . $this->delim . $v . "\n";
                    }
                }
            }
            $macros = $macroObj->getList(
                "*",
                -1,
                0,
                null,
                null,
                array('svc_svc_id' => $element[$this->object->getPrimaryKey()]),
                "AND"
            );
            foreach ($macros as $macro) {
                echo $this->action . $this->delim . "setmacro" . $this->delim
                    . $element['host_name'] . $this->delim
                    . $element['service_description'] . $this->delim
                    . $this->stripMacro($macro['svc_macro_name']) . $this->delim
                    . $macro['svc_macro_value'] . $this->delim
                    . $macro['is_password'] . $this->delim
                    . "'" .$macro['description'] ."'" . "\n";
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
                echo $this->action . $this->delim . "addcontactgroup" . $this->delim
                    . $element['host_name'] . $this->delim
                    . $cgelement['service_description'] . $this->delim
                    . $cgelement['cg_name'] . "\n";
            }
            $contactRel = new \Centreon_Object_Relation_Contact_Service();
            $celements = $contactRel->getMergedParameters(
                array("contact_alias", "contact_id"),
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
                echo $this->action . $this->delim . "addcontact" . $this->delim
                    . $element['host_name'] . $this->delim
                    . $celement['service_description'] . $this->delim
                    . $celement['contact_alias'] . "\n";
            }
            $trapRel = new \Centreon_Object_Relation_Trap_Service();
            $telements = $trapRel->getMergedParameters(
                array("traps_name", "traps_id"),
                array('service_description'),
                -1,
                0,
                null,
                null,
                array(
                    "service_register" => $this->register,
                    "service.service_id" => $element['service_id']
                ),
                "AND"
            );
            foreach ($telements as $telement) {
                CentreonTrap::getInstance()->export($element['traps_name']);
                echo $this->action . $this->delim . "addtrap" . $this->delim
                    . $element['host_name'] . $this->delim
                    . $telement['service_description'] . $this->delim
                    . $telement['traps_name'] . "\n";
            }
        }
    }

    /**
     * @param $pearDB
     * @param $service_id
     * @param $macroInput
     * @param $macroValue
     * @param bool $cmdId
     * @return bool
     */
    public function hasMacroFromServiceChanged($pearDB, $service_id, &$macroInput, &$macroValue, $cmdId = false)
    {
        $aListTemplate = $this->getListTemplates($pearDB, $service_id);

        if (!isset($cmdId)) {
            $cmdId = "";
        }
        $aMacros = $this->getMacros($service_id, $aListTemplate, $cmdId);
        foreach ($aMacros as $macro) {
            if ($macroInput == $macro['svc_macro_name'] && $macroValue == $macro["svc_macro_value"]) {
                return false;
            }
        }
        return true;
    }

        /**
     * This method get the macro attached to the service
     *
     * @param int $iServiceId
     * @param array $aListTemplate
     * @param int $iIdCommande
     *
     * @return array
     */
    public function getMacros($iServiceId, $aListTemplate, $iIdCommande)
    {
        $aMacro = array();
        $macroArray = array();
        $aMacroInService = array();

        //Get macro attached to the service
        $macroArray = $this->getCustomMacroInDb($iServiceId);
        $iNb = count($macroArray);

        //Get macro attached to the template
        $aMacroTemplate = array();

        // clear current template/service from the list.
        unset($aListTemplate[count($aListTemplate) - 1]);
        foreach ($aListTemplate as $template) {
            if (!empty($template)) {
                $aMacroTemplate[] = $this->getCustomMacroInDb($template['service_id'], $template);
            }
        }

        if (empty($iIdCommande)) {
            foreach ($aListTemplate as $template) {
                if (!empty($template['command_command_id'])) {
                    $iIdCommande = $template['command_command_id'];
                }
            }
        }


        // Get macro attached to the command
        if (!empty($iIdCommande)) {
            $oCommand = new CentreonCommand($this->db);
            $aMacroInService[] = $oCommand->getMacroByIdAndType($iIdCommande, 'service');
        }



        //filter a macro
        $aTempMacro = array();
        $serv = current($aMacroInService);
        if (count($aMacroInService) > 0) {
            for ($i = 0; $i < count($serv); $i++) {
                $serv[$i]['macroOldValue_#index#'] = $serv[$i]["svc_macro_value"];
                $serv[$i]['macroFrom_#index#'] = 'fromService';
                $serv[$i]['source'] = 'fromService';
                $aTempMacro[] = $serv[$i];
            }
        }

        if (count($aMacroTemplate) > 0) {
            foreach ($aMacroTemplate as $key => $macr) {
                foreach ($macr as $mm) {
                    $mm['macroOldValue_#index#'] = $mm["svc_macro_value"];
                    $mm['macroFrom_#index#'] = 'fromTpl';
                    $mm['source'] = 'fromTpl';
                    $aTempMacro[] = $mm;
                }
            }
        }

        if (count($macroArray) > 0) {
            foreach ($macroArray as $directMacro) {
                $directMacro['macroOldValue_#index#'] = $directMacro["svc_macro_value"];
                $directMacro['macroFrom_#index#'] = 'direct';
                $directMacro['source'] = 'direct';
                $aTempMacro[] = $directMacro;
            }
        }



        $aFinalMacro = $this->macro_unique($aTempMacro);

        return $aFinalMacro;
    }



    /**
     *
     * @param integer $serviceId
     * @param array $template
     * @return array
     */
    public function getCustomMacroInDb($serviceId = null, $template = null)
    {
        $arr = array();
        $i = 0;
        if ($serviceId) {
            $res = $this->db->query("SELECT svc_macro_name, svc_macro_value, is_password, description
                                FROM on_demand_macro_service
                                WHERE svc_svc_id = " .
                    $serviceId . "
                                ORDER BY macro_order ASC");
            while ($row = $res->fetch()) {
                if (preg_match('/\$_SERVICE(.*)\$$/', $row['svc_macro_name'], $matches)) {
                    $arr[$i]['svc_macro_name'] = $matches[1];
                    $arr[$i]['svc_macro_value'] = $row['svc_macro_value'];
                    $arr[$i]['macroPassword_#index#'] = $row['is_password'] ? 1 : null;
                    $arr[$i]['description'] = $row['description'];
                    $arr[$i]['macroDescription'] = $row['description'];
                    if (!is_null($template)) {
                        $arr[$i]['macroTpl'] = $template['service_description'];
                    }
                    $i++;
                }
            }
        }
        return $arr;
    }

    public function macro_unique($aTempMacro)
    {
        $storedMacros = array();
        foreach ($aTempMacro as $TempMacro) {
            $sInput = $TempMacro['svc_macro_name'];
            $storedMacros[$sInput][] = $TempMacro;
        }

        $finalMacros = array();
        foreach ($storedMacros as $key => $macros) {
            $choosedMacro = array();
            foreach ($macros as $macro) {
                if (empty($choosedMacro)) {
                    $choosedMacro = $macro;
                } else {
                    $choosedMacro = $this->comparaPriority($macro, $choosedMacro, false);
                }
            }
            if (!empty($choosedMacro)) {
                $finalMacros[] = $choosedMacro;
            }
        }
        $this->addInfosToMacro($storedMacros, $finalMacros);
        return $finalMacros;
    }

    private function addInfosToMacro($storedMacros, &$finalMacros)
    {
        foreach ($finalMacros as &$finalMacro) {
            $sInput = $finalMacro['svc_macro_name'];
            $this->setInheritedDescription(
                $finalMacro,
                $this->getInheritedDescription($storedMacros[$sInput], $finalMacro)
            );
            switch ($finalMacro['source']) {
                case 'direct':
                    $this->setTplValue($this->findTplValue($storedMacros[$sInput]), $finalMacro);
                    break;
                case 'fromTpl':
                    break;
                case 'fromService':
                    break;
                default:
                    break;
            }
        }
    }

    private function getInheritedDescription($storedMacros, $finalMacro)
    {
        $description = "";
        if (empty($finalMacro['macroDescription'])) {
            $choosedMacro = array();
            foreach ($storedMacros as $storedMacro) {
                if (!empty($storedMacro['macroDescription'])) {
                    if (empty($choosedMacro)) {
                        $choosedMacro = $storedMacro;
                    } else {
                        $choosedMacro = $this->comparaPriority($storedMacro, $choosedMacro, false);
                    }
                    $description = $choosedMacro['macroDescription'];
                }
            }
        } else {
            $description = $finalMacro['macroDescription'];
        }
        return $description;
    }

    private function setInheritedDescription(&$finalMacro, $description)
    {
        $finalMacro['description'] = $description;
        $finalMacro['macroDescription'] = $description;
    }

    private function setTplValue($tplValue, &$finalMacro)
    {
        if ($tplValue) {
            $finalMacro['macroTplValue_#index#'] = $tplValue;
            $finalMacro['macroTplValToDisplay_#index#'] = 1;
        } else {
            $finalMacro['macroTplValue_#index#'] = "";
            $finalMacro['macroTplValToDisplay_#index#'] = 0;
        }
    }

    private function findTplValue($storedMacro, $getFirst = false)
    {
        if ($getFirst) {
            foreach ($storedMacro as $macros) {
                if ($macros['source'] == 'fromTpl') {
                    return $macros['svc_macro_value'];
                }
            }
        } else {
            $macroReturn = false;
            foreach ($storedMacro as $macros) {
                if ($macros['source'] == 'fromTpl') {
                    $macroReturn = $macros['svc_macro_value'];
                }
            }
            return $macroReturn;
        }
        return false;
    }


        /**
     * Return the list of template
     *
     * @param int $svcId The service ID
     * @return array
     */
    public function getListTemplates($pearDB, $svcId, $alreadyProcessed = array())
    {
        $svcTmpl = array();
        if (in_array($svcId, $alreadyProcessed)) {
            return $svcTmpl;
        } else {
            $alreadyProcessed[] = $svcId;

            $query = "SELECT * FROM service WHERE service_id = ".  intval($svcId);
            $stmt = $pearDB->query($query);
            $row = $stmt->fetch();
            if (!empty($row)) {
                if ($row['service_template_model_stm_id'] !== null) {
                    $svcTmpl = array_merge(
                        $svcTmpl,
                        $this->getListTemplates(
                            $pearDB,
                            $row['service_template_model_stm_id'],
                            $alreadyProcessed
                        )
                    );
                    $svcTmpl[] = $row;
                }
            }
            return $svcTmpl;
        }
    }

    private function comparaPriority($macroA, $macroB, $getFirst = true)
    {
        $arrayPrio = array('direct' => 3,'fromTpl' => 2,'fromService' => 1);
        if ($getFirst) {
            if ($arrayPrio[$macroA['source']] > $arrayPrio[$macroB['source']]) {
                return $macroA;
            } else {
                return $macroB;
            }
        } else {
            if ($arrayPrio[$macroA['source']] >= $arrayPrio[$macroB['source']]) {
                return $macroA;
            } else {
                return $macroB;
            }
        }
    }
}

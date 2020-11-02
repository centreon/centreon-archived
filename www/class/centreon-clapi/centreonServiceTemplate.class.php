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

require_once "centreonService.class.php";
require_once "centreonCommand.class.php";
require_once "Centreon/Object/Relation/Service/Template/Host.php";
require_once "Centreon/Object/Host/Template.php";
require_once "Centreon/Object/Service/Template.php";

/**
 * Class for managing service templates
 *
 * @author sylvestre
 */
class CentreonServiceTemplate extends CentreonObject
{

    const ORDER_SVCDESC = 0;
    const ORDER_SVCALIAS = 1;
    const ORDER_SVCTPL = 2;
    const NB_UPDATE_PARAMS = 3;
    const UNKNOWN_NOTIFICATION_OPTIONS = "Invalid notifications options";

    public static $aDepends = array(
        'CMD',
        'TP',
        'TRAP',
        'HTPL'
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
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->object = new \Centreon_Object_Service($dependencyInjector);
        $this->params = array(
            'service_is_volatile' => '2',
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
            'service_register' => '0',
            'service_activate' => '1'
        );
        $this->insertParams = array('service_description', 'service_alias', 'service_template_model_stm_id');
        $this->exportExcludedParams = array_merge(
            $this->insertParams,
            array($this->object->getPrimaryKey(), 'children')
        );
        $this->action = "STPL";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->register = 0;
        $this->activateField = 'service_activate';
    }

    /**
     * Check parameters
     *
     * @param string $serviceDescription
     * @return bool
     */
    protected function serviceExists($serviceDescription)
    {
        $elements = $this->object->getList(
            "service_description",
            -1,
            0,
            null,
            null,
            array(
                'service_description' => $serviceDescription,
                'service_register' => 0
            ),
            "AND"
        );
        if (count($elements)) {
            return true;
        }
        return false;
    }

    /**
     * Display all service templates
     *
     * @param null $parameters
     * @param array $filters
     */
    public function show($parameters = null, $filters = array())
    {
        $filters = array('service_register' => $this->register);
        if (isset($parameters)) {
            $filters["service_description"] = "%" . $parameters . "%";
        }
        $commandObject = new \Centreon_Object_Command($this->dependencyInjector);
        $paramsSvc = array(
            'service_id',
            'service_description',
            'service_alias',
            'command_command_id',
            'command_command_id_arg',
            'service_normal_check_interval',
            'service_retry_check_interval',
            'service_max_check_attempts',
            'service_active_checks_enabled',
            'service_passive_checks_enabled'
        );
        $elements = $this->object->getList(
            $paramsSvc,
            -1,
            0,
            null,
            null,
            $filters,
            "AND"
        );
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
     * Get a parameter
     *
     * @param null $parameters
     * @throws CentreonClapiException
     */
    public function getparam($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        
        $authorizeParam = array(
            'activate',
            'description',
            'alias',
            'template',
            'is_volatile',
            'check_period',
            'check_command',
            'check_command_arguments',
            'max_check_attempts',
            'normal_check_interval',
            'retry_check_interval',
            'active_checks_enabled',
            'passive_checks_enabled',
            'notifications_enabled',
            'contact_additive_inheritance',
            'cg_additive_inheritance',
            'notification_interval',
            'notification_period',
            'notification_options',
            'first_notification_delay',
            'obsess_over_service',
            'check_freshness',
            'freshness_threshold',
            'event_handler_enabled',
            'flap_detection_enabled',
            'retain_status_information',
            'retain_nonstatus_information',
            'event_handler',
            'event_handler_arguments',
            'notes',
            'notes_url',
            'action_url',
            'icon_image',
            'icon_image_alt',
            'comment'
        );
        $unknownParam = array();

        if (($objectId = $this->getObjectId($params[self::ORDER_SVCDESC])) != 0) {
            $listParam = explode('|', $params[1]);
            $exportedFields = [];
            $resultString = "";
            foreach ($listParam as $paramSearch) {
                if (!$paramString) {
                    $paramString = $paramSearch;
                } else {
                    $paramString = $paramString . $this->delim . $paramSearch;
                }
                $field = $paramSearch;
                if (!in_array($field, $authorizeParam)) {
                    $unknownParam[] = $field;
                } else {
                    $extended = false;
                    switch ($paramSearch) {
                        case "check_command":
                            $field = "command_command_id";
                            break;
                        case "check_command_arguments":
                            $field = "command_command_id_arg";
                            break;
                        case "event_handler":
                            $field = "command_command_id2";
                            break;
                        case "event_handler_arguments":
                            $field = "command_command_id_arg2";
                            break;
                        case "check_period":
                            $field = "timeperiod_tp_id";
                            break;
                        case "notification_period":
                            $field = "timeperiod_tp_id2";
                            break;
                        case "template":
                            $field = "service_template_model_stm_id";
                            break;
                        case "contact_additive_inheritance":
                        case "cg_additive_inheritance":
                        case "geo_coords":
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
                            if (!preg_match("/^service_/", $paramSearch)) {
                                $field = "service_" . $paramSearch;
                            }
                            break;
                    }

                    if (!$extended) {
                        $ret = $this->object->getParameters($objectId, $field);
                        $ret = $ret[$field];
                    } else {
                        $field = "esi_" . $field;
                        $extended = new \Centreon_Object_Service_Extended($this->dependencyInjector);
                        $ret = $extended->getParameters($objectId, $field);
                        $ret = $ret[$field];
                    }

                    switch ($paramSearch) {
                        case "check_command":
                        case "event_handler":
                            $commandObject = new CentreonCommand($this->dependencyInjector);
                            $field = $commandObject->object->getUniqueLabelField();
                            $ret = $commandObject->object->getParameters($ret, $field);
                            $ret = $ret[$field];
                            break;
                        case "check_period":
                        case "notification_period":
                            $tpObj = new CentreonTimePeriod($this->dependencyInjector);
                            $field = $tpObj->object->getUniqueLabelField();
                            $ret = $tpObj->object->getParameters($ret, $field);
                            $ret = $ret[$field];
                            break;
                        case "template":
                            $tplObj = new CentreonServiceTemplate($this->dependencyInjector);
                            $field = $tplObj->object->getUniqueLabelField();
                            $ret = $tplObj->object->getParameters($ret, $field);
                            $ret = $ret[$field];
                            break;
                    }
                
                    if (!isset($exportedFields[$paramSearch])) {
                        $resultString .= $ret . $this->delim;
                        $exportedFields[$paramSearch] = 1;
                    }
                }
            }
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }

        if (!empty($unknownParam)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . implode('|', $unknownParam));
        }
        echo implode(';', array_unique(explode(';', $paramString))) . "\n";
        echo substr($resultString, 0, -1) . "\n";
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
            $table = array(
                "command_command_id" => "check_command",
                "command_command_id2" => "event_handler",
                "timeperiod_tp_id" => "check_period",
                "timeperiod_tp_id2" => "notification_period",
                "command_command_id_arg" => "check_command_arguments",
                "command_command_id_arg2" => "event_handler_arguments"
            );
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
     * @param $parameters
     * @return mixed|void
     * @throws CentreonClapiException
     */
    public function initInsertParameters($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if ($this->serviceExists($params[self::ORDER_SVCDESC]) == true) {
            throw new CentreonClapiException(self::OBJECTALREADYEXISTS);
        }
        $addParams = array();
        $addParams['service_description'] = $this->checkIllegalChar($params[self::ORDER_SVCDESC]);
        $addParams['service_alias'] = $params[self::ORDER_SVCALIAS];
        $template = $params[self::ORDER_SVCTPL];
        if ($template) {
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
        }
        $this->params = array_merge($this->params, $addParams);
    }

    /**
     * @param $serviceId
     */
    function insertRelations($serviceId)
    {
        $extended = new \Centreon_Object_Service_Extended($this->dependencyInjector);
        $extended->insert(array($extended->getUniqueLabelField() => $serviceId));
    }


    /**
     * Delete service template
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function del($parameters)
    {
        $serviceDesc = $parameters;
        $elements = $this->object->getList(
            "service_id",
            -1,
            0,
            null,
            null,
            array(
                'service_description' => $serviceDesc,
                'service_register' => 0
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $serviceDesc);
        }
        $this->object->delete($elements[0]['service_id']);
    }

    /**
     * @param null $parameters
     * @return array
     * @throws CentreonClapiException
     */
    public function initUpdateParameters($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $serviceDesc = $params[0];
        $elements = $this->object->getList(
            "service_id",
            -1,
            0,
            null,
            null,
            array(
                'service_description' => $serviceDesc,
                'service_register' => 0
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $serviceDesc);
        }
        $objectId = $elements[0]['service_id'];
        $extended = false;
        $commandObject = new CentreonCommand($this->dependencyInjector);
        switch ($params[1]) {
            case "check_command":
                $params[1] = "command_command_id";
                $params[2] = $commandObject->getId($params[2]);
                break;
            case "check_command_arguments":
                $params[1] = "command_command_id_arg";
                break;
            case "event_handler":
                $params[1] = "command_command_id2";
                $params[2] = $commandObject->getId($params[2]);
                break;
            case "event_handler_arguments":
                $params[1] = "command_command_id_arg2";
                break;
            case "check_period":
                $params[1] = "timeperiod_tp_id";
                $tpObj = new CentreonTimePeriod($this->dependencyInjector);
                $params[2] = $tpObj->getTimeperiodId($params[2]);
                break;
            case "notification_period":
                $params[1] = "timeperiod_tp_id2";
                $tpObj = new CentreonTimePeriod($this->dependencyInjector);
                $params[2] = $tpObj->getTimeperiodId($params[2]);
                break;
            case "flap_detection_options":
                break;
            case "template":
                $params[1] = "service_template_model_stm_id";
                $tmp = $this->object->getList(
                    $this->object->getPrimaryKey(),
                    -1,
                    0,
                    null,
                    null,
                    array('service_description' => $params[2], 'service_register' => '0'),
                    "AND"
                );
                if (!count($tmp)) {
                    throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[2]);
                }
                $params[2] = $tmp[0][$this->object->getPrimaryKey()];
                break;
            case "graphtemplate":
                $extended = true;
                $graphObj = new \Centreon_Object_Graph_Template($this->dependencyInjector);
                $tmp = $graphObj->getIdByParameter($graphObj->getUniqueLabelField(), $params[2]);
                if (!count($tmp)) {
                    throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[2]);
                }
                $params[1] = "graph_id";
                $params[2] = $tmp[0];
                break;
            case "contact_additive_inheritance":
            case "cg_additive_inheritance":
                break;
            case "notes":
            case "notes_url":
            case "action_url":
            case "icon_image":
            case "icon_image_alt":
                $extended = true;
                break;
            case "service_notification_options":
                $aNotifs = explode(",", $params[2]);
                foreach ($aNotifs as $notif) {
                    if (!array_key_exists($notif, self::$aAuthorizedNotificationsOptions)) {
                        throw new CentreonClapiException(self::UNKNOWN_NOTIFICATION_OPTIONS);
                    }
                }
                break;
            default:
                if (!preg_match("/^service_/", $params[1])) {
                    $params[1] = "service_" . $params[1];
                }
                break;
        }
        if ($extended == false) {
            $updateParams = array($params[1] => $params[2]);
            $updateParams['objectId'] = $objectId;
            return $updateParams;
        } else {
            if ($params[1] != "graph_id") {
                $params[1] = "esi_" . $params[1];
                if ($params[1] == "esi_icon_image") {
                    if ($params[2]) {
                        $id = CentreonUtils::getImageId($params[2], $this->db);
                        if (is_null($id)) {
                            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[2]);
                        }
                        $params[2] = $id;
                    } else {
                        $params[2] = null;
                    }
                }
            }
            $extended = new \Centreon_Object_Service_Extended($this->dependencyInjector);
            $extended->update($objectId, array($params[1] => $params[2]));
            return array();
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
     * Get macro list of a service template
     *
     * @param string $parameters
     * @return void
     * @throws CentreonClapiException
     */
    public function getmacro($parameters)
    {
        $serviceDesc = $parameters;
        $elements = $this->object->getList(
            "service_id",
            -1,
            0,
            null,
            null,
            array(
                'service_description' => $serviceDesc,
                'service_register' => 0
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $serviceDesc);
        }
        $macroObj = new \Centreon_Object_Service_Macro_Custom($this->dependencyInjector);
        $macroList = $macroObj->getList(
            array("svc_macro_name", "svc_macro_value", "description", "is_password"),
            -1,
            0,
            null,
            null,
            array("svc_svc_id" => $elements[0]['service_id'])
        );
        echo "macro name;macro value;description;is_password\n";
        foreach ($macroList as $macro) {
            $password = !empty($macro['is_password']) ? (int)$macro['is_password'] : 0;
            echo $macro['svc_macro_name'] . $this->delim
            . $macro['svc_macro_value'] . $this->delim
            . $macro['description'] . $this->delim
            . $password . "\n";
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
        if (count($params) < 3) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $serviceDescription = $params[0];
        $macroName = $params[1];
        $macroValue = $params[2];
        $macroDescription = isset($params[3]) ? $params[3] : '';
        $macroPassword = !empty($params[4]) ? (int)$params[4] : 0;

        $elements = $this->object->getList(
            "service_id",
            -1,
            0,
            null,
            null,
            array(
                'service_description' => $serviceDescription,
                'service_register' => 0
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $serviceDescription);
        }
        $macroObj = new \Centreon_Object_Service_Macro_Custom($this->dependencyInjector);
        $macroList = $macroObj->getList(
            $macroObj->getPrimaryKey(),
            -1,
            0,
            null,
            null,
            array(
                "svc_svc_id" => $elements[0]['service_id'],
                "svc_macro_name" => $this->wrapMacro($macroName)
            ),
            "AND"
        );
        if (count($macroList)) {
            $macroObj->update(
                $macroList[0][$macroObj->getPrimaryKey()],
                array(
                    'svc_macro_value' => $macroValue,
                    'is_password' => $macroPassword,
                    'description' => $macroDescription
                )
            );
        } else {
            $macroObj->insert(
                array(
                    'svc_svc_id' => $elements[0]['service_id'],
                    'svc_macro_name' => $this->wrapMacro($macroName),
                    'is_password' => $macroPassword,
                    'svc_macro_value' => $macroValue,
                    'description' => $macroDescription
                )
            );
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
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $elements = $this->object->getList(
            "service_id",
            -1,
            0,
            null,
            null,
            array(
                'service_description' => $params[0],
                'service_register' => 0
            ),
            "AND"
        );
        if (!count($elements)) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[0]);
        }
        $macroObj = new \Centreon_Object_Service_Macro_Custom($this->dependencyInjector);
        $macroList = $macroObj->getList(
            $macroObj->getPrimaryKey(),
            -1,
            0,
            null,
            null,
            array(
                "svc_svc_id" => $elements[0]['service_id'],
                "svc_macro_name" => $this->wrapMacro($params[1])
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
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        if (($serviceId = $this->getObjectId($params[self::ORDER_SVCDESC])) == 0) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_SVCDESC]);
        }

        $severityObj = new \Centreon_Object_Service_Category($this->dependencyInjector);
        $severity = $severityObj->getIdByParameter(
            $severityObj->getUniqueLabelField(),
            $params[1]
        );
        if (!isset($severity[0])) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[1]);
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
            $rel = new \Centreon_Object_Relation_Service_Category_Service($this->dependencyInjector);
            $rel->insert($severityId, $serviceId);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[1]);
        }
    }

    /**
     * @param $parameters
     * @throws CentreonClapiException
     */
    public function unsetseverity($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 1) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        if (($serviceId = $this->getObjectId($params[self::ORDER_SVCDESC])) == 0) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_SVCDESC]);
        }

        // can't delete with generic method
        $this->db->query(
            "DELETE FROM service_categories_relation
             WHERE service_service_id = ?
             AND sc_id IN (SELECT sc_id FROM service_categories WHERE level > 0)",
            $serviceId
        );
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
                case "hosttemplate":
                    $class = "Centreon_Object_Host_Template";
                    $relclass = "Centreon_Object_Relation_Service_Template_Host";
                    break;
                case "category":
                    $class = "Centreon_Object_Service_Category";
                    $relclass = "Centreon_Object_Relation_Service_Category_Service";
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
                $elements = $this->object->getList(
                    "service_id",
                    -1,
                    0,
                    null,
                    null,
                    array(
                        'service_description' => $args[0],
                        'service_register' => 0
                    ),
                    "AND"
                );
                if (!count($elements)) {
                    throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $args[0]);
                }
                $serviceId = $elements[0]['service_id'];

                $relobj = new $relclass($this->dependencyInjector);
                $obj = new $class($this->dependencyInjector);
                if ($matches[1] == "get") {
                    $tab = $relobj->getTargetIdFromSourceId(
                        $relobj->getFirstKey(),
                        $relobj->getSecondKey(),
                        $serviceId
                    );
                    echo "id" . $this->delim . "name" . "\n";
                    foreach ($tab as $value) {
                        $tmp = $obj->getParameters($value, array($obj->getUniqueLabelField()));
                        echo $value . $this->delim . $tmp[$obj->getUniqueLabelField()] . "\n";
                    }
                } else {
                    if (!isset($args[1])) {
                        throw new CentreonClapiException(self::MISSINGPARAMETER);
                    }
                    $relation = $args[1];
                    $relations = explode("|", $relation);
                    $relationTable = array();
                    foreach ($relations as $rel) {
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
                    if ($matches[1] == "set") {
                        $relobj->delete(null, $serviceId);
                    }

                    if ((strtolower($matches[2]) === 'hosttemplate') && (strtolower($matches[1]) === 'add')) {
                        $existingRelationIds = $relobj->getTargetIdFromSourceId(
                            $relobj->getSecondKey(),
                            $relobj->getFirstKey(),
                            $serviceId
                        );
                    } else {
                        $existingRelationIds = $relobj->getTargetIdFromSourceId(
                            $relobj->getFirstKey(),
                            $relobj->getSecondKey(),
                            $serviceId
                        );
                    }

                    foreach ($relationTable as $relationId) {
                        if ($matches[1] == "del") {
                            $relobj->delete($relationId, $serviceId);
                        } elseif ($matches[1] == "set" || $matches[1] == "add") {
                            if (!in_array($relationId, $existingRelationIds)) {
                                $relobj->insert($relationId, $serviceId);
                            } else {
                                throw new CentreonClapiException(self::OBJECTALREADYEXISTS);
                            }
                        }
                    }
                }
            } else {
                throw new CentreonClapiException(self::UNKNOWN_METHOD . "PHP >> " . __LINE__);
            }
        } else {
            throw new CentreonClapiException(self::UNKNOWN_METHOD . "PHP >> " . __LINE__);
        }
    }

    /**
     * Sort templates so that import can be processed without failure
     *
     * @param array $arr
     * @param int $parentId
     * @return array
     */
    protected function sortTemplates($arr, $parentId = null)
    {
        $branch = array();
        foreach ($arr as $data) {
            if ($data['service_template_model_stm_id'] == $parentId) {
                $children = $this->sortTemplates($arr, $data['service_id']);
                $data['children'] = count($children) ? $children : array();
                $branch[] = $data;
            }
        }
        return $branch;
    }

    /**
     * Parse template tree
     *
     * @param array $tree
     * @param Centreon_Object_Service_Extended $extendedObj
     */
    protected function parseTemplateTree($tree, $filter_id = null)
    {
        $commandObj = CentreonCommand::getInstance();
        $tpObj = CentreonTimePeriod::getInstance();
        $extendedObj = new \Centreon_Object_Service_Extended($this->dependencyInjector);
        $macroObj = new \Centreon_Object_Service_Macro_Custom($this->dependencyInjector);
        foreach ($tree as $element) {
            $addStr = $this->action . $this->delim . "ADD";
            foreach ($this->insertParams as $param) {
                $addStr .= $this->delim;
                if ($param == "service_template_model_stm_id") {
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
                        $tmpObj = $tpObj;
                    } elseif ($parameter == "command_command_id" || $parameter == "command_command_id2") {
                        $tmpObj = $commandObj;
                    }
                    if (isset($tmpObj)) {
                        $labelField = $tmpObj->getObject()->getUniqueLabelField();
                        $tmp = $tmpObj->getObject()->getParameters($value, $labelField);
                        if (isset($tmp) && isset($tmp[$labelField])) {
                            $value = $tmp[$labelField];
                            $tmpObj::getInstance()->export($value);
                        }
                        unset($tmpObj);
                    }
                    $value = CentreonUtils::convertLineBreak($value);
                    echo $this->action . $this->delim
                        . "setparam" . $this->delim
                        . $element['service_description']
                        . $this->delim . $this->getClapiActionName($parameter) . $this->delim
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
                        $v = CentreonUtils::convertLineBreak($v);
                        echo $this->action . $this->delim
                            . "setparam" . $this->delim
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
                array('svc_svc_id' => $element[$this->object->getPrimaryKey()]),
                "AND"
            );
            foreach ($macros as $macro) {
                echo $this->action . $this->delim
                    . "setmacro" . $this->delim
                    . $element['service_description'] . $this->delim
                    . $this->stripMacro($macro['svc_macro_name']) . $this->delim
                    . $macro['svc_macro_value'] . "\n";
            }
            if (isset($element['children']) && count($element['children'])) {
                $this->parseTemplateTree($element['children'], $extendedObj);
            }
        }
    }

    /**
     * @param null $filterName
     * @return bool|void
     */
    public function export($filterName = null)
    {
        if (!$this->canBeExported($filterName)) {
            return false;
        }
        if (!is_null($filterName)) {
            $filterId = $this->getObjectId($filterName);
        }

        $labelField = $this->object->getUniqueLabelField();
        $filters = array("service_register" => $this->register);
        if (!is_null($filterName)) {
            $filters[$labelField] = $filterName;
        }
        $elements = $this->object->getList(
            "*",
            -1,
            0,
            $labelField,
            'ASC',
            $filters,
            "AND"
        );


        # No need to sort all service templates. We only export the current
        if (is_null($filterId)) {
            $templateTree = $this->sortTemplates($elements);
            $this->parseTemplateTree($templateTree);
        } else {
            $this->parseTemplateTree($elements, $filterId);
        }

        // contact groups
        $cgRel = new \Centreon_Object_Relation_Contact_Group_Service($this->dependencyInjector);
        $filters_cgRel = array("service_register" => $this->register);
        if (!is_null($filterId)) {
            $filters_cgRel['service_id'] = $filterId;
        }
        $elements = $cgRel->getMergedParameters(
            array("cg_name", "cg_id"),
            array('service_description'),
            -1,
            0,
            null,
            null,
            $filters_cgRel,
            "AND"
        );
        foreach ($elements as $element) {
            CentreonContactGroup::getInstance()->export($element['cg_name']);
            echo $this->action . $this->delim
                . "addcontactgroup" . $this->delim
                . $element['service_description'] . $this->delim
                . $element['cg_name'] . "\n";
        }

        // contacts
        $contactRel = new \Centreon_Object_Relation_Contact_Service($this->dependencyInjector);
        $filters_contactRel = array("service_register" => $this->register);
        if (!is_null($filterId)) {
            $filters_contactRel['service_id'] = $filterId;
        }
        $elements = $contactRel->getMergedParameters(
            array("contact_alias", "contact_id"),
            array('service_description'),
            -1,
            0,
            null,
            null,
            $filters_contactRel,
            "AND"
        );
        foreach ($elements as $element) {
            CentreonContact::getInstance()->export($element['contact_alias']);
            echo $this->action . $this->delim
                . "addcontact" . $this->delim
                . $element['service_description'] . $this->delim
                . $element['contact_alias'] . "\n";
        }

        // macros
        $macroObj = new \Centreon_Object_Service_Macro_Custom($this->dependencyInjector);
        $macros = $macroObj->getList(
            "*",
            -1,
            0,
            null,
            null,
            array('svc_svc_id' => isset($element[$this->object->getPrimaryKey()]) ? $element[$this->object->getPrimaryKey()] : null),
            "AND"
        );
        foreach ($macros as $macro) {
            echo $this->action . $this->delim
                . "setmacro" . $this->delim
                . $element['service_description'] . $this->delim
                . $this->stripMacro($macro['svc_macro_name']) . $this->delim
                . $macro['svc_macro_value'] . $this->delim
                . "'" . $macro['description'] . "'" . "\n";
        }

        // traps
        $trapRel = new \Centreon_Object_Relation_Trap_Service($this->dependencyInjector);
        $filters_trapRel = array("service_register" => $this->register);
        if (!is_null($filterId)) {
            $filters_trapRel['traps_service_relation.service_id'] = $filterId;
        }
        $telements = $trapRel->getMergedParameters(
            array("traps_name", "traps_id"),
            array('service_description'),
            -1,
            0,
            null,
            null,
            $filters_trapRel,
            "AND"
        );
        foreach ($telements as $telement) {
            CentreonTrap::getInstance()->export($telement['traps_name']);
            echo $this->action . $this->delim
                . "addtrap" . $this->delim
                . $telement['service_description'] . $this->delim
                . $telement['traps_name'] . "\n";
        }

        // hosts
        $hostRel = new \Centreon_Object_Relation_Host_Service($this->dependencyInjector);
        $filters_hostRel = array("service_register" => $this->register);
        if (!is_null($filterId)) {
            $filters_hostRel['service_id'] = $filterId;
        }
        $helements = $hostRel->getMergedParameters(
            array("host_name", "host_id"),
            array('service_description'),
            -1,
            0,
            null,
            null,
            $filters_hostRel,
            "AND"
        );
        foreach ($helements as $helement) {
            echo $this->action . $this->delim
                . "addhosttemplate" . $this->delim
                . $helement['service_description'] . $this->delim
                . $helement['host_name'] . "\n";
        }
    }
}

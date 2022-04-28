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
require_once "centreonInstance.class.php";
require_once "Centreon/Object/Engine/Engine.php";
require_once "Centreon/Object/Engine/Engine_Broker_Module.php";
require_once "Centreon/Object/Command/Command.php";

/**
 *
 * @author sylvestre
 */
class CentreonEngineCfg extends CentreonObject
{
    const ORDER_UNIQUENAME = 0;
    const ORDER_INSTANCE = 1;
    const ORDER_COMMENT = 2;
    protected $instanceObj;

    public static $aDepends = array(
        'INSTANCE'
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->instanceObj = new CentreonInstance($dependencyInjector);
        $this->commandObj = new \Centreon_Object_Command($dependencyInjector);
        $this->object = new \Centreon_Object_Engine($dependencyInjector);
        $this->brokerModuleObj = new \Centreon_Object_Engine_Broker_Module($dependencyInjector);
        $this->params = array(
            'log_file' => '/var/log/centreon-engine/centengine.log',
            'cfg_dir' => '/etc/centreon-engine/',
            'enable_notifications' => '0',
            'execute_service_checks' => '1',
            'accept_passive_service_checks' => '1',
            'execute_host_checks' => '2',
            'accept_passive_host_checks' => '2',
            'enable_event_handlers' => '1',
            'check_external_commands' => '1',
            'command_check_interval' => '1s',
            'command_file' => '/var/log/centreon-engine/rw/nagios.cmd',
            'retain_state_information' => '1',
            'state_retention_file' => '/var/log/centreon-engine/status.sav',
            'retention_update_interval' => '60',
            'use_retained_program_state' => '1',
            'use_retained_scheduling_info' => '1',
            'use_syslog' => '0',
            'log_notifications' => '1',
            'log_service_retries' => '1',
            'log_host_retries' => '1',
            'log_event_handlers' => '1',
            'log_external_commands' => '1',
            'log_passive_checks' => '2',
            'sleep_time' => '0.2',
            'service_inter_check_delay_method' => 's',
            'service_interleave_factor' => 's',
            'max_concurrent_checks' => '400',
            'max_service_check_spread' => '5',
            'check_result_reaper_frequency' => '5',
            'interval_length' => '60',
            'auto_reschedule_checks' => '2',
            'enable_flap_detection' => '0',
            'low_service_flap_threshold' => '25.0',
            'high_service_flap_threshold' => '50.0',
            'low_host_flap_threshold' => '25.0',
            'high_host_flap_threshold' => '50.0',
            'soft_state_dependencies' => '0',
            'service_check_timeout' => '60',
            'host_check_timeout' => '10',
            'event_handler_timeout' => '30',
            'notification_timeout' => '30',
            'ocsp_timeout' => '5',
            'ochp_timeout' => '5',
            'perfdata_timeout' => '5',
            'obsess_over_services' => '0',
            'obsess_over_hosts' => '2',
            'process_performance_data' => '0',
            'host_perfdata_file_mode' => '2',
            'service_perfdata_file_mode' => '2',
            'check_for_orphaned_services' => '0',
            'check_for_orphaned_hosts' => '0',
            'check_service_freshness' => '2',
            'check_host_freshness' => '2',
            'date_format' => 'euro',
            'illegal_object_name_chars' => "~!$%^&*\"|'<>?,()=",
            'illegal_macro_output_chars' => "`~$^&\"|'<>",
            'use_regexp_matching' => '2',
            'use_true_regexp_matching' => '2',
            'admin_email' => 'admin@localhost',
            'admin_pager' => 'admin',
            'nagios_activate' => '1',
            'event_broker_options' => '-1',
            'enable_predictive_host_dependency_checks' => '2',
            'enable_predictive_service_dependency_checks' => '2',
            'use_large_installation_tweaks' => '2',
            'enable_environment_macros' => '2',
            'debug_level' => '0',
            'debug_level_opt' => '0',
            'debug_verbosity' => '2',
            'cached_host_check_horizon' => '60',
            'logger_version' => 'log_v2_enabled',
        );
        $this->nbOfCompulsoryParams = 3;
        $this->activateField = "nagios_activate";
        $this->action = 'ENGINECFG';
        $this->insertParams = array($this->object->getUniqueLabelField(), 'nagios_server_id', 'nagios_comment');
        $this->exportExcludedParams = array_merge($this->insertParams, array($this->object->getPrimaryKey()));
    }

    /**
     * Set Broker Module
     *
     * @param int $objectId
     * @param string $brokerModule
     * @return void
     * @todo we should implement this object in the centreon api so that we don't have to write our own query
     */
    protected function setBrokerModule($objectId, $brokerModule)
    {
        $query = "DELETE FROM cfg_nagios_broker_module WHERE cfg_nagios_id = ?";
        $this->db->query($query, array($objectId));
        $brokerModuleArray = explode("|", $brokerModule);
        foreach ($brokerModuleArray as $bkModule) {
            $this->db->query(
                "INSERT INTO cfg_nagios_broker_module (cfg_nagios_id, broker_module) VALUES (?, ?)",
                array($objectId, $bkModule)
            );
        }
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
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $params[self::ORDER_UNIQUENAME];
        $addParams['nagios_server_id'] = $this->instanceObj->getInstanceId($params[self::ORDER_INSTANCE]);
        $addParams['nagios_comment'] = $params[self::ORDER_COMMENT];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
    }

    /**
     * @param $parameters
     * @return array
     * @throws CentreonClapiException
     */
    public function initUpdateParameters($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME]);
        if ($objectId != 0) {
            $commandColumns = array(
                'global_host_event_handler',
                'global_service_event_handler',
                'host_perfdata_command',
                'service_perfdata_command',
                'host_perfdata_file_processing_command',
                'service_perfdata_file_processing_command',
                'ocsp_command',
                'ochp_command'
            );
            $loggerColumns = [
                'log_level_functions',
                'log_level_config',
                'log_level_events',
                'log_level_checks',
                'log_level_notifications',
                'log_level_eventbroker',
                'log_level_external_command',
                'log_level_commands',
                'log_level_downtimes',
                'log_level_comments',
                'log_level_macros',
                'log_level_process',
                'log_level_runtime',
            ];
            $setUpdateParams = true;
            if ($params[1] == "instance" || $params[1] == "nagios_server_id") {
                $params[1] = "nagios_server_id";
                $params[2] = $this->instanceObj->getInstanceId($params[2]);
            } elseif ($params[1] == "broker_module") {
                $this->setBrokerModule($objectId, $params[2]);
                $setUpdateParams = false;
            } elseif (preg_match('/(' . implode('|', $commandColumns) . ')/', $params[1], $matches)) {
                if ($params[2]) {
                    $commandObj = new \Centreon_Object_Command($this->dependencyInjector);
                    $res = $commandObj->getIdByParameter($commandObj->getUniqueLabelField(), $params[2]);
                    if (count($res)) {
                        $params[2] = $res[0];
                    } else {
                        throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[2]);
                    }
                } else {
                    $params[2] = null;
                }
            } elseif ($params[1] === 'logger_version' && $params['2'] === 'log_v2_enabled') {
                $this->createLoggerV2Cfg($objectId);
            } elseif (preg_match('/(' . implode('|', $loggerColumns) . ')/', $params[1], $matches)) {
                $this->updateLoggerV2Param($objectId, $params);
                $setUpdateParams = false;
            }
            if ($setUpdateParams) {
                $p = strtolower($params[1]);
                if ($params[2] == "") {
                    if (isset($this->params[$p]) && $this->params[$p] == 2) {
                        $params[2] = $this->params[$p];
                    } else {
                        $params[2] = null;
                    }
                }
                $updateParams = array($params[1] => $params[2]);
                $updateParams['objectId'] = $objectId;
                return $updateParams;
            }
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * @param null $parameters
     * @param array $filters
     */
    public function show($parameters = null, $filters = array())
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%" . $parameters . "%");
        }
        $params = array("nagios_id", "nagios_name", "nagios_server_id", "nagios_comment");
        $paramString = str_replace("_", " ", implode($this->delim, $params));
        $paramString = str_replace("nagios server id", "instance", $paramString);
        echo $paramString . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            $str = "";
            foreach ($tab as $key => $value) {
                if ($key == "nagios_server_id") {
                    $value = $this->instanceObj->getInstanceName($value);
                }
                $str .= $value . $this->delim;
            }
            $str = trim($str, $this->delim) . "\n";
            echo $str;
        }
    }

    /**
     * Export
     *
     * @param null $filterName
     * @return bool|void
     */
    public function export($filterName = null)
    {
        if (!$this->canBeExported($filterName)) {
            return false;
        }

        $labelField = $this->object->getUniqueLabelField();
        $filters = array();
        if (!is_null($filterName)) {
            $filters[$labelField] = $filterName;
        }

        $elements = $this->object->getList(
            '*',
            -1,
            0,
            $labelField,
            'ASC',
            $filters,
            "AND"
        );

        foreach ($elements as $element) {
            $element = array_merge($element, $this->getLoggerV2Cfg($element['nagios_id']));

            /* ADD action */
            $addStr = $this->action . $this->delim . "ADD";
            foreach ($this->insertParams as $param) {
                if ($param == 'nagios_server_id') {
                    $element[$param] = $this->instanceObj->getInstanceName($element[$param]);
                }
                $addStr .= $this->delim . $element[$param];
            }
            $addStr .= "\n";
            echo $addStr;

            /* SETPARAM action */
            foreach ($element as $parameter => $value) {
                if (!in_array($parameter, $this->exportExcludedParams) && !is_null($value) && $value != "") {
                    if ($parameter == 'global_host_event_handler'
                        || $parameter == 'global_service_event_handler'
                        || $parameter == 'host_perfdata_command'
                        || $parameter == 'service_perfdata_command'
                        || $parameter == 'host_perfdata_file_processing_command'
                        || $parameter == 'service_perfdata_file_processing_command'
                        || $parameter == 'ochp_command'
                        || $parameter == 'ocsp_command'
                    ) {
                        $tmp = $this->commandObj->getParameters($value, $this->commandObj->getUniqueLabelField());
                        $value = $tmp[$this->commandObj->getUniqueLabelField()];
                    } elseif ($parameter == 'illegal_object_name_chars'
                        || $parameter == 'illegal_macro_output_chars'
                    ) {
                        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML401);
                    }

                    $value = str_replace("\n", "<br/>", $value);
                    $value = CentreonUtils::convertLineBreak($value);
                    echo $this->action . $this->delim
                        . "setparam" . $this->delim
                        . $element[$this->object->getUniqueLabelField()] . $this->delim
                        . $parameter . $this->delim
                        . $value . "\n";
                }
            }
            $modules = $this->brokerModuleObj->getList(
                "broker_module",
                -1,
                0,
                null,
                "ASC",
                array('cfg_nagios_id' => $element[$this->object->getPrimaryKey()]),
                "AND"
            );
            $moduleList = array();
            foreach ($modules as $module) {
                array_push($moduleList, $module['broker_module']);
            }
            echo $this->action . $this->delim
                . "setparam" . $this->delim
                . $element[$this->object->getUniqueLabelField()] . $this->delim
                . 'broker_module' . $this->delim
                . implode('|', $moduleList) . "\n";
        }
    }

    /**
     * @param $parameters
     * @throws CentreonClapiException
     */
    public function addbrokermodule($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            $this->addBkModule($objectId, $params[1]);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Set Broker Module
     *
     * @param int $objectId
     * @param string $brokerModule
     * @return void
     * @todo we should implement this object in the centreon api so that we don't have to write our own query
     */
    protected function addBkModule($objectId, $brokerModule)
    {
        $brokerModuleArray = explode("|", $brokerModule);
        foreach ($brokerModuleArray as $bkModule) {
            $res = $this->db->query(
                'SELECT COUNT(*) as nbBroker FROM cfg_nagios_broker_module ' .
                'WHERE cfg_nagios_id = ? AND broker_module = ?',
                array($objectId, $bkModule)
            );
            $row = $res->fetch();
            if ($row['nbBroker'] > 0) {
                throw new CentreonClapiException(self::OBJECTALREADYEXISTS . ":" . $bkModule);
            } else {
                $this->db->query(
                    "INSERT INTO cfg_nagios_broker_module (cfg_nagios_id, broker_module) VALUES (?, ?)",
                    array($objectId, $bkModule)
                );
            }
        }
    }

    /**
     * @param $parameters
     * @throws CentreonClapiException
     */
    public function delbrokermodule($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            $this->delBkModule($objectId, $params[1]);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Set Broker Module
     *
     * @param int $objectId
     * @param string $brokerModule
     * @return void
     * @todo we should implement this object in the centreon api so that we don't have to write our own query
     */
    protected function delBkModule($objectId, $brokerModule)
    {
        $brokerModuleArray = explode("|", $brokerModule);

        foreach ($brokerModuleArray as $bkModule) {
            $tab = $this->brokerModuleObj->getIdByParameter('broker_module', array($bkModule));

            if (count($tab)) {
                $this->db->query(
                    "DELETE FROM cfg_nagios_broker_module WHERE cfg_nagios_id = ? and broker_module = ?",
                    array($objectId, $bkModule)
                );
            } else {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $bkModule);
            }
        }
    }

    /**
     * @param int $nagiosId
     */
    public function insertRelations(int $nagiosId): void
    {
        $this->createLoggerV2Cfg($nagiosId);
    }

    /**
     * Create logger V2 config if it doesn't already exist
     *
     * @param int $nagiosId
     */
    private function createLoggerV2Cfg(int $nagiosId): void
    {
        $statement = $this->db->prepare('SELECT id FROM cfg_nagios_logger WHERE cfg_nagios_id = :cfg_nagios_id');
        $statement->bindValue(':cfg_nagios_id', $nagiosId, \PDO::PARAM_INT);
        $statement->execute();

        if (! ($statement->fetch())) {
            $statement = $this->db->prepare('INSERT INTO cfg_nagios_logger (cfg_nagios_id) VALUES (:cfg_nagios_id)');
            $statement->bindValue(':cfg_nagios_id', $nagiosId, \PDO::PARAM_INT);
            $statement->execute();
        }
    }

    /**
     * @param int $nagiosId
     * @return array<mixed>
     */
    private function getLoggerV2Cfg(int $nagiosId): array
    {
        $statement = $this->db->prepare('SELECT * FROM cfg_nagios_logger WHERE cfg_nagios_id = :cfg_nagios_id');
        $statement->bindValue(':cfg_nagios_id', $nagiosId, \PDO::PARAM_INT);
        $statement->execute();

        if ($result = $statement->fetch()) {
            unset($result['cfg_nagios_id']);
            unset($result['id']);
        }

        return empty($result) ? [] : $result;
    }

    /**
     * Update loggerV2 config
     *
     * @param int $nagiosId
     * @param string[] $params
     * @throws CentreonClapiException if config isn't found in cfg_nagios_logger table
     */
    private function updateLoggerV2Param(int $nagiosId, array $params): void
    {
        $statement = $this->db->prepare('SELECT id FROM cfg_nagios_logger WHERE cfg_nagios_id = :cfg_nagios_id');
        $statement->bindValue(':cfg_nagios_id', $nagiosId, \PDO::PARAM_INT);
        $statement->execute();

        if ($statement->fetch()) {
            $statement = $this->db->prepare(
                "UPDATE cfg_nagios_logger SET {$params[1]} = :paramValue WHERE cfg_nagios_id = :cfg_nagios_id"
            );
            $statement->bindValue(':paramValue', $params[2], \PDO::PARAM_STR);
            $statement->bindValue(':cfg_nagios_id', $nagiosId, \PDO::PARAM_INT);
            $statement->execute();
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }
}

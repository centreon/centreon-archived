<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonEngine\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Repository\CommandRepository as CommandConfigurationRepository;
use CentreonConfiguration\Repository\TimePeriodRepository as TimeperiodConfigurationRepository;
use CentreonConfiguration\Repository\HostTemplateRepository as HostTemplateConfigurationRepository;
use CentreonConfiguration\Repository\HostRepository as HostConfigurationRepository;
use CentreonConfiguration\Repository\CustomMacroRepository;
use CentreonConfiguration\Internal\Poller\WriteConfigFile;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class HostTemplateRepository
{
    /**
     * @var int
     */
    protected static $register = 0;

    /**
     * 
     * @return int
     */
    public static function getTripleChoice()
    {
        $content = array();
        $content["host_active_checks_enabled"] = 1;
        $content["host_passive_checks_enabled"] = 1;
        $content["host_obsess_over_host"] = 1;
        $content["host_check_freshness"] = 1;
        $content["host_event_handler_enabled"] = 1;
        $content["host_flap_detection_enabled"] = 1;
        return $content;
    }

    /**
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     * @param CentreonEngine\Events\GetMacroHost $hostMacroEvent
     */
    public static function generate(& $filesList, $poller_id, $path, $filename, $hostMacroEvent)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Get disfield */
        $disableFields = static::getTripleChoice();

        /* Init Content Array */
        $content = array();

        /* Get information into the database. */
        $fields = static::getFields();

        $query = "SELECT $fields
            FROM cfg_hosts 
            WHERE host_activate = '1' 
            AND host_register = ? 
            ORDER BY host_name"; 

        $stmt = $dbconn->prepare($query);
        $stmt->execute(array(static::$register));
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "host");
            $tmpData = array();
            $args = "";
            $host_id = null;

            foreach ($row as $key => $value) {
                if ($key == "host_id") {
                    $host_id = $row["host_id"];
                } elseif (($key == "host_snmp_community") && ($value != "")) {
                    $tmpData["_SNMPCOMMUNITY"] = $value;
                } elseif (($key == "host_snmp_version") && ($value != "")) {
                    $tmpData["_SNMPVERSION"] = $value;
                } elseif (isset($disableField[$key]) && $value != 2 && $value != "") {
                    $key = str_replace("host_", "", $key);
                    $tmpData[$key] = $value;
                } elseif ((!isset($disableField[$key]) && $value != "")) {
                    $key = str_replace("host_", "", $key);
                    if ($key == 'command_command_id_arg1' || $key == 'command_command_id_arg2') {
                        $args = $value;
                    } else if ($key == 'check_command' || $key == 'event_handler') {
                        $value = CommandConfigurationRepository::getCommandName($value).$args;
                        $args = "";
                    } else if ($key == 'check_period') {
                        $value = TimePeriodConfigurationRepository::getPeriodName($value);
                    }
                    $tmpData[$key] = $value;
                }
            }

            if (!is_null($host_id)) {
                $templates = HostTemplateConfigurationRepository::getTemplates($host_id); 
                if ($templates != "") {
                    $tmpData['use'] = $templates;
                }
            }

            /* Generate macro */
            $macros = CustomMacroRepository::loadHostCustomMacro($host_id);
            if (is_array($macros) && count($macros)) {
                foreach ($macros as $macro) {
                    if (preg_match('/^\$_HOST(.+)\$$/', $macro['macro_name'], $m)) {
                        $name = "_{$m[1]}";
                        $tmpData[$name] = $macro['macro_value'];
                    }
                }
            }

            /* Macros that can be generated from other modules */
            $extraMacros = $hostMacroEvent->getMacro($host_id);
            foreach ($extraMacros as $macroName => $macroValue) {
                $macroName = "_{$macroName}";
                $tmpData[$macroName] = $macroValue;
            }

            $tmpData['register'] = 0;
            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }
        /* Write Check-Command configuration file */
        WriteConfigFile::writeObjectFile($content, $path . $poller_id . "/objects.d/" . $filename, $filesList, "API");
        unset($content);
    }

    /**
     * Return query to retrive host list
     *
     * @return string
     */
    protected static function getFields()
    {
        $fields = "host_id, host_name, host_alias, host_address, host_snmp_version, host_snmp_community, "
            . "host_max_check_attempts, host_check_interval, host_active_checks_enabled, host_passive_checks_enabled, "
            . "command_command_id_arg1, command_command_id AS check_command, timeperiod_tp_id AS check_period, "
            . "host_obsess_over_host, host_check_freshness, host_freshness_threshold, host_event_handler_enabled, "
            . "command_command_id_arg2, command_command_id2 AS event_handler, host_flap_detection_enabled, "
            . "host_low_flap_threshold, host_high_flap_threshold, flap_detection_options, "
            . "host_register, timezone_id, host_check_timeout ";

        return $fields;
    }
}

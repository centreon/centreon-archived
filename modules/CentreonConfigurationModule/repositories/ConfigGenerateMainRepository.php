<?php
/*
 * Copyright 2005-2014 MERETHIS
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

namespace  CentreonConfiguration\Repository;

/**
 * Factory for ConfigGenerate Engine For centengine.cfg
 *
 * @author Julien Mathis <jmathis@merethis.com>
 * @version 3.0.0
 */

class ConfigGenerateMainRepository
{
    /**
     * Method for generating Main configuration file
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     * @param int $testing
     */
    public static function generateMainFile(& $filesList, $poller_id, $path, $filename, $testing = 0)
    {
        /* Get Content */
        $content = static::getContent($poller_id, $filesList, $testing);
        
        /* Write Check-Command configuration file */
        WriteConfigFileRepository::writeParamsFile($content, $path.$poller_id."/".$filename, $filesList, $user = "API");
        unset($content);
    }

    /**
     * 
     * @param array $filesList
     * @param array $content
     * @param int $testing
     * @return array
     */
    private static function getFilesList(& $filesList, $content, $testing)
    {
        foreach ($filesList as $category => $data) {
            if ($category != 'main_file') {
                foreach ($data as $path) {
                    if (!isset($content[$category])) {
                        $content[$category] = array();
                    }
                    if (!$testing) {
                        $path = str_replace("/var/lib/centreon/tmp/1/", "/etc/centreon-engine/", $path);
                    }
                    $content[$category][] = $path;
                }
            }
        }
        return $content;
    }

    /**
     * 
     * @param int $poller_id
     * @param array $filesList
     * @param int $testing
     * @return array
     */
    private static function getContent($poller_id, & $filesList, $testing)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Init Content Array */
        $content = array();

        $disabledField = static::getDisabledField();
        $defaultValue = static::getDefaultValue();
        $command_id = static::getCommandIdField();
        $getCmd = static::getCommandIdField();
        
        /* get configuration files */
        $content = static::getFilesList($filesList, $content, $testing);

        /* Get information into the database. */
        $query = "SELECT * FROM cfg_nagios WHERE nagios_server_id = '$poller_id'";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            foreach ($row as $key => $value) {
                if ($key != "cfg_dir") {
                    if (isset($disabledField[$key]) || (isset($defaultValue[$key]) && $value == 2)) {
                        ;
                    } elseif (isset($commandId[$key]) && isset($value)) {
                        $content[$key] = CommandRepository::getCommandName($value);
                    } elseif ($key == "event_broker_options") {
                        /* Get Brokers List */
                        $content["broker_module"] = static::getBrokerConf($poller_id);

                        /* Write param */
                        $content[$key] = $value;
                    } else {
                        if ($value != "") {
                            $content[$key] = html_entity_decode($value);
                        }
                    }
                }
            }
        }
        return $content;
    }

    /**
     * 
     * @param int $poller_id
     * @return array
     */
    private static function getConfigFiles($poller_id)
    {
        $pathList = array();
        $resList = array();
        $dirList = array();
        
        /* TODO : Change hardcoded path */
        $path = "/var/lib/centreon/tmp/";
        
        /* Check that that basic path exists */
        if (!file_exists($path)) {
            if (!is_dir($path)) {
                mkdir($path);
            }
        }

        /* Check that poller directory exists */
        if (!file_exists($path.$poller_id)) {
            if (!is_dir($path.$poller_id)) {
                mkdir($path.$poller_id);
            }
        }

        /* Check that Object directory exists */
        if (!file_exists($path.$poller_id."/objects/")) {
            if (!is_dir($path.$poller_id."/objects/")) {
                mkdir($path.$poller_id."/objects/");
            }
        }

        /* Check that Ressources directory exists */
        if (!file_exists($path.$poller_id."/resources/")) {
            if (!is_dir($path.$poller_id."/resources/")) {
                mkdir($path.$poller_id."/resources/");
            }
        }

        /* Add fixed path files */
        $resList[] = $path."$poller_id/resources.cfg";
        $pathList[] = $path."$poller_id/misc-command.cfg";
        $pathList[] = $path."$poller_id/check-command.cfg";
        $pathList[] = $path."$poller_id/timeperiods.cfg";
        $pathList[] = $path."$poller_id/connectors.cfg";
        
        $dirList[] = $path."$poller_id/objects/";
        $dirList[] = $path."$poller_id/resources/";

        return array("cfg_file" => $pathList, "resource_file" => $resList, "cfg_dir" => $dirList);
    }

    /**
     * 
     * @param int $poller_id
     * @return array
     */
    private static function getBrokerConf($poller_id)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        /* Init Broker list */
        $broker = array();

        /* Get broker in DB */
        $stmt = $dbconn->prepare(
            "SELECT broker_module FROM `cfg_nagios_broker_module` WHERE `cfg_nagios_id` = '".$poller_id."'"
        );
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $broker[] = $row["broker_module"];
        }
        return $broker;
    }

    /**
     * 
     * @return int
     */
    private static function getDefaultValue()
    {
        /* Field that we don't write if value = 2 */
        $defaultValue = array();
        $defaultValue["enable_notification"] = 1;
        $defaultValue["execute_service_checks"] = 1;
        $defaultValue["accept_passive_service_checks"] = 1;
        $defaultValue["execute_host_checks"] = 1;
        $defaultValue["accept_passive_host_checks"] = 1;
        $defaultValue["enable_event_handlers"] = 1;
        $defaultValue["check_external_commands"] = 1;
        $defaultValue["retain_state_information"] = 1;
        $defaultValue["use_retained_program_state"] = 1;
        $defaultValue["use_retained_scheduling_info"] = 1;
        $defaultValue["use_syslog"] = 1;
        $defaultValue["log_notifications"] = 1;
        $defaultValue["log_service_retries"] = 1;
        $defaultValue["log_host_retries"] = 1;
        $defaultValue["log_event_handlers"] = 1;
        $defaultValue["log_initial_states"] = 1;
        $defaultValue["log_external_commands"] = 1;
        $defaultValue["log_passive_checks"] = 1;
        $defaultValue["auto_reschedule_checks"] = 1;
        $defaultValue["use_aggressive_host_checking"] = 1;
        $defaultValue["enable_flap_detection"] = 1;
        $defaultValue["soft_state_dependencies"] = 1;
        $defaultValue["obsess_over_services"] = 1;
        $defaultValue["obsess_over_hosts"] = 1;
        $defaultValue["process_performance_data"] = 1;
        $defaultValue["check_for_orphaned_hosts"] = 1;
        $defaultValue["check_for_orphaned_services"] = 1;
        $defaultValue["check_service_freshness"] = 1;
        $defaultValue["check_host_freshness"] = 1;
        $defaultValue["use_regexp_matching"] = 1;
        $defaultValue["use_true_regexp_matching"] = 1;
        $defaultValue["service_inter_check_delay_method"] = 1;
        $defaultValue["host_inter_check_delay_method"] = 1;
        $defaultValue["enable_predictive_host_dependency_checks"] = 1;
        $defaultValue["enable_predictive_service_dependency_checks"] = 1;
        $defaultValue["use_large_installation_tweaks"] = 1;
        $defaultValue["free_child_process_memory"] = 1;
        $defaultValue["child_processes_fork_twice"] = 1;
        $defaultValue["enable_environment_macros"] = 1;
        $defaultValue["use_setpgid"] = 1;
        $defaultValue["enable_embedded_perl"] = 1;
        $defaultValue["use_embedded_perl_implicitly"] = 1;
        $defaultValue["host_perfdata_file_mode"] = 1;
        $defaultValue["translate_passive_host_checks"] = 1;
        return $defaultValue;
    }

    /**
     * 
     * @return int
     */
    private static function getCommandIdField()
    {
        $commandId = array();
        $commandId["global_host_event_handler"] = 1;
        $commandId["global_service_event_handler"] = 1;
        $commandId["ocsp_command"] = 1;
        $commandId["ochp_command"] = 1;
        $commandId["host_perfdata_command"] = 1;
        $commandId["service_perfdata_command"] = 1;
        $commandId["host_perfdata_file_processing_command"] = 1;
        $commandId["service_perfdata_file_processing_command"] = 1;
        return $commandId;
    }

    /**
     * 
     * @return int
     */
    private static function getDisabledField()
    {
        /* Field that we don't want into the config file */
        $disabledField = array();
        $disabledField["nagios_id"] = 1;
        $disabledField["nagios_name"] = 1;
        $disabledField["nagios_server_id"] = 1;
        $disabledField["nagios_comment"] = 1;
        $disabledField["nagios_activate"] = 1;
        $disabledField["debug_level_opt"] = 1;
        $disabledField["cfg_file"] = 1;
        
        /* Todo : Remove */
        $disabledField["use_check_result_path"] = 1;
        $disabledField["temp_file"] = 1;
        $disabledField["nagios_user"] = 1;
        $disabledField["nagios_group"] = 1;
        $disabledField["log_rotation_method"] = 1;
        $disabledField["log_archive_path"] = 1;
        $disabledField["lock_file"] = 1;
        return $disabledField;
    }
}

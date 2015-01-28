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

namespace CentreonEngine\Repository;

use CentreonConfiguration\Repository\CommandRepository as CommandConfigurationRepository;
use CentreonConfiguration\Repository\TimePeriodRepository as TimePeriodConfigurationRepository;
use CentreonConfiguration\Repository\ServiceRepository as ServiceConfigurationRepository;
use CentreonConfiguration\Repository\ServicetemplateRepository as ServicetemplateConfigurationRepository;
use CentreonConfiguration\Repository\CustomMacroRepository;
use Centreon\Internal\Di;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class ServicetemplateRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     * 
     * @return int
     */
    public static function getTripleChoice()
    {
        $content = array();
        $content["service_active_checks_enabled"] = 1;
        $content["service_passive_checks_enabled"] = 1;
        $content["service_obsess_over_host"] = 1;
        $content["service_check_freshness"] = 1;
        $content["service_event_handler_enabled"] = 1;
        $content["service_flap_detection_enabled"] = 1;
        $content["service_process_perf_data"] = 1;
        $content["service_retain_status_information"] = 1;
        $content["service_retain_nonstatus_information"] = 1;
        $content["service_notifications_enabled"] = 1;
        $content["service_stalking_options"] = 1;
        $content["service_is_volatile"] = 1;
        $content["service_parallelize_check"] = 1;
        $content["service_obsess_over_service"] = 1;
        return $content;
    }

    /**
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     * @param CentreonEngine\Events\GetMacroService $serviceMacroEvent
     */
    public static function generate(& $filesList, $poller_id, $path, $filename, $serviceMacroEvent)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Field to not display */
        $disableField = static::getTripleChoice();
        $field = "service_id, service_description, service_alias, service_template_model_stm_id, "
            . "command_command_id_arg, command_command_id AS check_command, timeperiod_tp_id AS check_period, "
            . "command_command_id_arg2, command_command_id2 AS event_handler, "
            . "timeperiod_tp_id2 AS notification_period, display_name, service_is_volatile, "
            . "service_max_check_attempts, service_normal_check_interval, service_retry_check_interval, "
            . "service_active_checks_enabled, service_passive_checks_enabled, initial_state, "
            . "service_parallelize_check, service_obsess_over_service, service_check_freshness, "
            . "service_freshness_threshold, service_event_handler_enabled, service_low_flap_threshold, "
            . "service_high_flap_threshold, service_flap_detection_enabled, service_process_perf_data, "
            . "service_retain_status_information, service_retain_nonstatus_information, service_notification_interval, "
            . "service_notification_options, service_notifications_enabled, service_first_notification_delay, "
            . "service_stalking_options ";
        
        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $query = "SELECT $field "
            . "FROM cfg_services "
            . "WHERE service_activate = '1' "
            . "AND service_register = '0' "
            . "ORDER BY service_description";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "service");
            $tmpData = array();
            $args = "";
            foreach ($row as $key => $value) {
                if ($key == "service_id") {
                    $service_id = $row["service_id"];
                } elseif ((!isset($disableField[$key]) && $value != "")) {
                    $writeParam = 1;
                    if (isset($disableField[$key]) && $value != 2) {
                        ;
                    } else {
                        $key = str_replace("service_", "", $key);
                        if ($key == 'description') {
                            $key = "name";
                        }
                        if ($key == 'alias') {
                            $key = "service_description";
                        }
                        if ($key == 'normal_check_interval') {
                            $key = "check_interval";
                        }
                        if ($key == 'retry_check_interval') {
                            $key = "retry_interval";
                        }
                        if ($key == 'command_command_id_arg' || $key == 'command_command_id_arg2') {
                            $args = $value;
                            $writeParam = 0;
                        }
                        if ($key == 'check_command' || $key == 'event_handler') {
                            $value = CommandConfigurationRepository::getCommandName($value).html_entity_decode($args);
                            $args = "";
                        }
                        if ($key == 'check_period' || $key == 'notification_period') {
                            $value = TimePeriodConfigurationRepository::getPeriodName($value);
                        }
                        if ($key == "template_model_stm_id") {
                            $key = "use";
                            $value = ServicetemplateConfigurationRepository::getTemplateName($value);
                        }
                        if ($writeParam == 1) {
                            $tmpData[$key] = $value;
                        }
                    }
                }
            }
            
            /* Generate macro */
            $macros = CustomMacroRepository::loadServiceCustomMacro($service_id);
            if (is_array($macros) && count($macros)) {
                foreach ($macros as $macro) {
                    if (preg_match('/^\$_SERVICE(.+)\$$/', $macro['macro_name'], $m)) {
                        $name = "_{$m[1]}";
                        $tmpData[$name] = $macro['macro_value'];
                    }
                }
            }

            /* Macros that can be generated from other modules */
            $extraMacros = $serviceMacroEvent->getMacro($service_id);
            foreach ($extraMacros as $macroName => $macroValue) {
                $macroName = "_{$macroName}";
                $tmpData[$macroName] = $macroValue;
            }

            $tmpData['register'] = 0;
            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }
        
        /* Write Check-Command configuration file */
        WriteConfigFileRepository::writeObjectFile($content, $path.$poller_id."/".$filename, $filesList, $user = "API");
        unset($content);
    }
}

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
use CentreonConfiguration\Repository\TimePeriodRepository as TimePeriodConfigurationRepository;
use CentreonConfiguration\Repository\ServiceRepository as ServiceConfigurationRepository;
use CentreonConfiguration\Repository\ServicetemplateRepository as ServicetemplateConfigurationRepository;
use CentreonConfiguration\Repository\CustomMacroRepository;
use CentreonEngine\Events\AddService as AddServiceEvent;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class ServiceRepository extends ServicetemplateRepository 
{
    /**
     * 
     * @param int $host_id
     * @param CentreonEngine\Events\GetMacroService $serviceMacroEvent
     * @return int
     */
    public static function generate($host_id, $serviceMacroEvent)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Field to not display */
        $disableField = static::getTripleChoice();
        $field = "host_id, h.host_name, service_id, "
            . "service_description, service_alias, service_template_model_stm_id, command_command_id_arg, "
            . "s.command_command_id AS check_command, s.timeperiod_tp_id AS check_period, "
            . "s.command_command_id_arg2, s.command_command_id2 AS event_handler, "
            . "service_is_volatile, service_max_check_attempts, service_normal_check_interval, "
            . "service_retry_check_interval, service_active_checks_enabled, "
            . "s.initial_state, service_obsess_over_service, service_check_freshness, "
            . "service_freshness_threshold, service_event_handler_enabled, service_low_flap_threshold, "
            . "service_high_flap_threshold, service_flap_detection_enabled, service_check_timeout ";

        
        /* Init Content Array */
        $content = array();
 
        /* Get information into the database. */
        $query = "SELECT $field "
            . "FROM cfg_hosts h, cfg_services s, cfg_hosts_services_relations r "
            . "WHERE h.host_id = $host_id "
            . "AND h.host_id = r.host_host_id "
            . "AND s.service_id = r.service_service_id "
            . "AND service_activate = '1' "
            . "AND (service_register = '1' "
            . "OR service_register = '2') "
            . "ORDER BY host_name, service_description";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();

        $serviceList = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $event = $di->get('events');
        $addServiceEvent = new AddServiceEvent($host_id, $serviceList);
        $event->emit('centreon-engine.add.service', array($addServiceEvent));
        $serviceList = $addServiceEvent->getServiceList();

        foreach ($serviceList as $service) {
            $tmp = array("type" => "service");
            $tmpData = array();
            $args = "";
            foreach ($service as $key => $value) {
                if ($key == "service_id" || $key == "host_id") {
                    $host_id = $service["host_id"];
                    $service_id = $service["service_id"];

                    /* Add service_id macro for broker - This is mandatory*/
                    $tmpData["_SERVICE_ID"] = $service_id;
                } elseif (isset($disableField[$key]) && $value != 2 && $value != "") {
                    $key = str_replace("service_", "", $key);
                    $tmpData[$key] = $value;
                } elseif ((!isset($disableField[$key]) && $value != "")) {
                    if ($key != 'service_description') {
                        $key = str_replace("service_", "", $key);
                    }

                    if ($key == 'normal_check_interval') {
                        $key = "check_interval";
                    } else if ($key == 'retry_check_interval') {
                        $key = "retry_interval";
                    } else if ($key == 'command_command_id_arg' || $key == 'command_command_id_arg2') {
                        $args = $value;
                        $writeParam = 0;
                    } else if ($key == 'check_command' || $key == 'event_handler') {
                        if (is_numeric($value)) {
                            $value = CommandConfigurationRepository::getCommandName($value).html_entity_decode($args);
                        }
                        $args = "";
                    } else if ($key == 'check_period') {
                        $value = TimePeriodConfigurationRepository::getPeriodName($value);
                    } else if ($key == "template_model_stm_id") {
                        $key = "use";
                        $value = ServicetemplateConfigurationRepository::getTemplateName($value);
                    }
                    $tmpData[$key] = $value;
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

            $tmpData["register"] = 1;
            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }
        return $content;
    }
}

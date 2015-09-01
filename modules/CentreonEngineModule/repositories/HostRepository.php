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
use CentreonConfiguration\Repository\HostTemplateRepository as HostTemplateConfigurationRepository;
use CentreonConfiguration\Repository\HostRepository as HostConfigurationRepository;
use CentreonConfiguration\Repository\CustomMacroRepository;
use CentreonEngine\Events\AddHost as AddHostEvent;
use CentreonEngine\Events\AddService as AddServiceEvent;
use CentreonConfiguration\Internal\Poller\WriteConfigFile;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class HostRepository extends HostTemplateRepository
{
    /**
     * @var int
     */
    protected static $register = 1;

    /**
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     * @param CentreonEngine\Events\GetMacroHost $hostMacroEvent
     * @param CentreonEngine\Events\GetMacroService $serviceMacroEvent
     */
    public static function generate(& $filesList, $poller_id, $path, $filename, $hostMacroEvent, $serviceMacroEvent)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Get disabled fields */
        $disableField = static::getTripleChoice();
        
        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $fields = static::getFields();

        $query = "SELECT $fields 
            FROM cfg_hosts 
            WHERE host_activate = '1' 
            AND (host_register = '1' 
            OR host_register = '2') 
            AND poller_id = ?
            ORDER BY host_name";

        $stmt = $dbconn->prepare($query);
        $stmt->execute(array(
            $poller_id
        ));

        $hostList = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $event = $di->get('events');
        $addHostEvent = new AddHostEvent($poller_id, $hostList);
        $event->emit('centreon-engine.add.host', array($addHostEvent));
        $hostList = $addHostEvent->getHostList();

        foreach ($hostList as $host) {
            $content = array();
            $tmp = array("type" => "host");
            $tmpData = array();
            $args = "";
            $host_id = null;

            /* Write Host Properties */
            foreach ($host as $key => $value) {
                if ($key == "host_id") {
                    $host_id = $host["host_id"];
                    
                    /* Add host_id macro for broker - This is mandatory*/
                    $tmpData["_HOST_ID"] = $host_id;
                    $host_name = "";
                } elseif (($key == "host_snmp_community") && ($value != "")) {
                    $tmpData["_SNMPCOMMUNITY"] = $value;
                } elseif (($key == "host_snmp_version") && ($value != "")) {
                    $tmpData["_SNMPVERSION"] = $value;
                } elseif (isset($disableField[$key]) && $value != 2 && $value != "") {
                    $key = str_replace("host_", "", $key);
                    $tmpData[$key] = $value;
                } elseif ((!isset($disableField[$key]) && $value != "")) {
                    if ($key != 'host_name') {
                        $key = str_replace("host_", "", $key);
                    } else {
                        $host_name = $value;
                    }

                    if ($key == 'command_command_id_arg1' || $key == 'command_command_id_arg2') {
                        $args = $value;
                    } else if ($key == 'check_command' || $key == 'event_handler') {
                        $value = CommandConfigurationRepository::getCommandName($value).$args;
                        $args = "";
                    } else if ($key == 'check_period') {
                        $value = TimeperiodConfigurationRepository::getPeriodName($value);
                    } else if ($key == 'timezone_id') {
                        $key = 'timezone';
                        if ($value != 'NULL') {
                            $tName = \CentreonAdministration\Models\Timezone::getParameters($value, array('name'));
                            $value = ':'.$tName['name'];
                        }
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

            $tmpData['register'] = 1;
            $tmp["content"] = $tmpData;
            $content[] = $tmp;

            /* Write Service Properties */
            $services = ServiceRepository::generate($host_id, $serviceMacroEvent);
            foreach ($services as $contentService) {
                $content[] = $contentService;
            }
            
            WriteConfigFile::writeObjectFile(
                $content,
                $path . $poller_id . "/objects.d/" . $filename . $host_name . "-" . $host_id . ".cfg",
                $filesList,
                "API"
            );
           
        }
        unset($content);
    }
}

<?php
/*
 * Copyright 2005-2014 CENTREON
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

namespace CentreonEngine\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Repository\CommandRepository as CommandConfigurationRepository;
use CentreonConfiguration\Repository\TimePeriodRepository as TimePeriodConfigurationRepository;
use CentreonConfiguration\Repository\HostTemplateRepository as HostTemplateConfigurationRepository;
use CentreonConfiguration\Repository\HostRepository as HostConfigurationRepository;
use CentreonConfiguration\Repository\CustomMacroRepository;
use CentreonEngine\Events\AddHost as AddHostEvent;
use CentreonEngine\Events\AddService as AddServiceEvent;

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
                } elseif ((!isset($disableField[$key]) && $value != "")) {
                    if (isset($disableField[$key]) && $value != 2) {
                        ;
                    } else {
                        if ($key != 'host_name') {
                            $key = str_replace("host_", "", $key);
                        } else {
                            $host_name = $value;
                        }
                        if ($key == 'command_command_id_arg1' || $key == 'command_command_id_arg2') {
                            $args = $value;
                        }
                        if ($key == 'check_command' || $key == 'event_handler') {
                            $value = CommandConfigurationRepository::getCommandName($value).$args;
                            $args = "";
                        }
                        if ($key == 'check_period' || $key == 'notification_period') {
                            $value = TimeperiodConfigurationRepository::getPeriodName($value);
                        }
                        if ($key == 'timezone_id') {
                            $key = 'timezone';
                            if ($value != 'NULL') {
                                $tName = \CentreonAdministration\Models\Timezone::getParameters($value, array('name'));
                                $value = ':'.$tName['name'];
                            }
                        }
                        $tmpData[$key] = $value;
                    }
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
            
            /* Write Check-Command configuration file */
            //print "Write : " . $path . $poller_id . "/".$filename . $host_name . "-" . $host_id . ".cfg \n<br>";

            WriteConfigFileRepository::writeObjectFile(
                $content,
                $path.$poller_id."/".$filename.$host_name."-".$host_id.".cfg",
                $filesList,
                "API"
            );
           
        }
        unset($content);
    }
}

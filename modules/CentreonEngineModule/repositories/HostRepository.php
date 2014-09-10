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

use \Centreon\Internal\Di;
use \CentreonConfiguration\Repository\CommandRepository as CommandConfigurationRepository;
use \CentreonConfiguration\Repository\TimePeriodRepository as TimePeriodConfigurationRepository;
use \CentreonConfiguration\Repository\HostTemplateRepository as HostTemplateConfigurationRepository;
use \CentreonConfiguration\Repository\HostRepository as HostConfigurationRepository;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class HostRepository extends HostTemplateRepository
{
    /**
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     */
    public static function generate(& $filesList, $poller_id, $path, $filename)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Field to not display */
        $disableField = static::getTripleChoice();
        $field = "host_id, host_name, host_alias, host_address, display_name, host_max_check_attempts, "
            . "host_check_interval, host_active_checks_enabled, host_passive_checks_enabled, "
            . "command_command_id_arg1, command_command_id AS check_command, timeperiod_tp_id AS check_period, "
            . "host_obsess_over_host, host_check_freshness, host_freshness_threshold, host_event_handler_enabled, "
            . "command_command_id_arg2, command_command_id2 AS event_handler, host_flap_detection_enabled, "
            . "host_low_flap_threshold, host_high_flap_threshold, flap_detection_options, host_process_perf_data, "
            . "host_retain_status_information, host_retain_nonstatus_information, host_notifications_enabled, "
            . "host_notification_interval, cg_additive_inheritance, contact_additive_inheritance, "
            . "host_notification_options, timeperiod_tp_id2 AS notification_period, host_stalking_options, "
            . "host_register ";
        
        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $query = "SELECT $field FROM cfg_hosts WHERE host_activate = '1' AND host_register = '1' ORDER BY host_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $content = array();
            $tmp = array("type" => "host");
            $tmpData = array();
            $args = "";

            /* Write Host Properties */
            foreach ($row as $key => $value) {
                if ($key == "host_id") {
                    $host_id = $row["host_id"];
                    
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
                        if ($key == "contact_additive_inheritance") {
                            $tmpContact = HostConfigurationRepository::getContacts($host_id);
                            if ($tmpContact != "") {
                                if ($value = 1) {
                                    $tmpData["contacts"] = "+";
                                }
                                $tmpData["contacts"] .= $tmpContact;
                            }
                        } elseif ($key == "cg_additive_inheritance") {
                            $tmpContact = HostConfigurationRepository::getContactGroups($host_id);
                            if ($tmpContact != "") {
                                if ($value = 1) {
                                    $tmpData["contact_groups"] = "+";
                                }
                                $tmpData["contact_groups"] .= $tmpContact;
                            }
                        } elseif ($key == "name") {
                            $tmpData[$key] = $value;
                            $template = HostTemplateConfigurationRepository::getTemplates($host_id);
                            if ($template != "") {
                                $tmpData["use"] = $template;
                            }
                        } else {
                            $tmpData[$key] = $value;
                            if ($key == "host_name") {
                                /* Get Template List */
                                $tmpData["use"] = "generic-host";
                            }
                        }
                    }
                }
            }
            $tmp["content"] = $tmpData;
            $content[] = $tmp;
           
            /* Write Service Properties */
            $services = ServiceRepository::generate($host_id);
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

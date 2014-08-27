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

namespace CentreonConfiguration\Repository;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class ServiceRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'service';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Service';
    
    /**
     * 
     * @param int $interval
     * @return string
     */
    public static function formatNotificationOptions($interval)
    {
        // Initializing connection
        $intervalLength = \Centreon\Internal\Di::getDefault()->get('config')->get('default', 'interval_length');
        $interval *= $intervalLength;
        
        if ($interval % 60 == 0) {
            $units = "min";
            $interval /= 60;
        } else {
            $units = "sec";
        }
        
        $scheduling = $interval.' '.$units;
        
        return $scheduling;
    }
    
    /**
     * 
     * @param int $service_id
     * @param string $field
     * @return type
     */
    public static function getMyServiceField($service_id, $field)
    {
        
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $tab = array();
        while (1) {
            $stmt = $dbconn->query(
                "SELECT "
                . "`".$field."`, "
                . "service_template_model_stm_id "
                . "FROM service "
                . "WHERE "
                . "service_id = '".$service_id."' LIMIT 1"
            );
            $row = $stmt->fetchAll();
            if ($row[0][$field]) {
                return $row[0][$field];
            } elseif ($row[0]['service_template_model_stm_id']) {
                if (isset($tab[$row[0]['service_template_model_stm_id']])) {
                    break;
                }
                $service_id = $row[0]["service_template_model_stm_id"];
                $tab[$service_id] = 1;
            } else {
                break;
            }
        }
    }

    /**
     * 
     * @param int $service_id
     * @return type
     */
    public function getNotificicationsStatus($service_id)
    {
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        while (1) {
            $stmt = $dbconn->query(
                "SELECT "
                . "service_notifications_enabled, "
                . "service_template_model_stm_id "
                . "FROM service "
                . "WHERE "
                . "service_id = '".$service_id."' LIMIT 1"
            );
            $row = $stmt->fetchAll();
            
            if (($row[0]['service_notifications_enabled'] != 2) || (!$row[0]['service_template_model_stm_id'])) {
                return $row[0]['service_notifications_enabled'];
            }
            
            $service_id = $row[0]['service_template_model_stm_id'];
        }
        
    }
    
    /**
     * 
     * @param int $service_template_id
     * @return array
     */
    public static function getMyServiceTemplateModels($service_template_id)
    {
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $stmt = $dbconn->query(
            "SELECT service_description FROM service WHERE service_id = '".$service_template_id."' LIMIT 1"
        );
        $row = $stmt->fetchAll();
        $tplArr = array(
            'id' => $service_template_id,
            'description' => \html_entity_decode(self::db2str($row[0]["service_description"]), ENT_QUOTES, "UTF-8")
        );
        return $tplArr;
    }
    
    /**
     * 
     * @param string $string
     * @return string
     */
    public static function db2str($string)
    {
        $string = str_replace('#BR#', "\\n", $string);
        $string = str_replace('#T#', "\\t", $string);
        $string = str_replace('#R#', "\\r", $string);
        $string = str_replace('#S#', "/", $string);
        $string = str_replace('#BS#', "\\", $string);
        return $string;
    }
    
    /**
     * 
     * @param int $service_id
     * @return type
     */
    public static function getMyServiceAlias($service_id)
    {
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $tab = array();
        while (1) {
            $stmt = $dbconn->query(
                "SELECT "
                . "service_alias, service_template_model_stm_id "
                . "FROM service "
                . "WHERE "
                . "service_id = '".$service_id."' LIMIT 1"
            );
            $row = $stmt->fetchRow();
            if ($row["service_alias"]) {
                return html_entity_decode(db2str($row["service_alias"]), ENT_QUOTES, "UTF-8");
            } elseif ($row["service_template_model_stm_id"]) {
                if (isset($tab[$row['service_template_model_stm_id']])) {
                    break;
                }
                $service_id = $row["service_template_model_stm_id"];
                $tab[$service_id] = 1;
            } else {
                break;
            }
        }
    }
    
    /**
     * 
     * @param int $service_id
     * @return string
     */
    public static function getIconImage($service_id)
    {
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $router = $di->get('router');
        
        $finalRoute = "";
        
        while (1) {
            $stmt = $dbconn->query(
                "SELECT b.filename, s.service_template_model_stm_id "
                . "FROM service s, service_image_relation sir, binaries b "
                . "WHERE s.service_id = '$service_id' "
                . "AND s.service_id = sir.service_id"
            );
            $esiResult = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!is_null($esiResult['filename'])) {
                $filenameExploded = explode('.', $esiResult['filename']);
                $nbOfOccurence = count($filenameExploded);
                $fileFormat = $filenameExploded[$nbOfOccurence-1];
                $filenameLength = strlen($esiResult['filename']);
                $routeAttr = array(
                    'image' => substr($esiResult['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                    'format' => '.'.$fileFormat
                );
                $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
                $finalRoute .= '<img src="'.$imgSrc.'" style="width:20px;height:20px;">';
                break;
            } elseif (is_null($esiResult['filename']) && is_null($esiResult['service_template_model_stm_id'])) {
                $finalRoute .= "<i class='fa fa-gear'></i>";
                break;
            }
            
            $service_id = $esiResult['service_template_model_stm_id'];
        }
        
        return $finalRoute;
    }

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
     * @param int $host_id
     * @return int
     */
    public static function generate($host_id)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Field to not display */
        $disableField = static::getTripleChoice();
        $field = "host_id, h.host_name, service_id, "
            . "service_description, service_alias, service_template_model_stm_id, command_command_id_arg, "
            . "s.command_command_id AS check_command, s.timeperiod_tp_id AS check_period, "
            . "s.command_command_id_arg2, s.command_command_id2 AS event_handler, "
            . "s.timeperiod_tp_id2 AS notification_period, s.display_name, "
            . "service_is_volatile, service_max_check_attempts, service_normal_check_interval, "
            . "service_retry_check_interval, service_active_checks_enabled, service_passive_checks_enabled, "
            . "s.initial_state, service_parallelize_check, service_obsess_over_service, service_check_freshness, "
            . "service_freshness_threshold, service_event_handler_enabled, service_low_flap_threshold, "
            . "service_high_flap_threshold, service_flap_detection_enabled, service_process_perf_data, "
            . "service_retain_status_information, service_retain_nonstatus_information, service_notification_interval, "
            . "service_notification_options, service_notifications_enabled, service_first_notification_delay, "
            . "service_stalking_options ";

        
        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $query = "SELECT $field "
            . "FROM host h, service s, cfg_hosts_services_relations r "
            . "WHERE h.host_id = $host_id "
            . "AND h.host_id = r.host_host_id "
            . "AND s.service_id = r.service_service_id "
            . "AND service_activate = '1' "
            . "AND service_register = '1' "
            . "ORDER BY host_name, service_description";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "service");
            $tmpData = array();
            $args = "";
            foreach ($row as $key => $value) {
                if ($key == "service_id" || $key == "host_id") {
                    $host_id = $row["host_id"];
                    $service_id = $row["service_id"];

                    /* Add service_id macro for broker - This is mandatory*/
                    $tmpData["_SERVICE_ID"] = $service_id;
                } elseif ((!isset($disableField[$key]) && $value != "")) {
                    if (isset($disableField[$key]) && $value != 2) {
                        ;
                    } else {
                        if ($key != 'service_description') {
                            $key = str_replace("service_", "", $key);
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
                            $value = CommandRepository::getCommandName($value).html_entity_decode($args);
                            $args = "";
                        }
                        if ($key == 'check_period' || $key == 'notification_period') {
                            $value = TimeperiodRepository::getPeriodName($value);
                        }
                        if ($key == "template_model_stm_id") {
                            $key = "use";
                            $value = ServicetemplateRepository::getTemplateName($value);
                        }
                        if ($key == "contact_additive_inheritance") {
                            $tmpContact = static::getContacts($service_id);
                            if ($tmpContact != "") {
                                if ($value = 1) {
                                    $tmpData["contacts"] = "+";
                                }
                                $tmpData["contacts"] .= $tmpContact;
                            }
                        }
                        if ($key == "cg_additive_inheritance") {
                            $tmpContact = static::getContactGroups($service_id);
                            if ($tmpContact != "") {
                                if ($value = 1) {
                                    $tmpData["contactgroups"] = "+";
                                }
                                $tmpData["contactgroups"] .= $tmpContact;
                            }
                        }
                        $tmpData[$key] = $value;
                    }
                }
            }
            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }
        return $content;
    }
    
    /**
     * 
     * @param int $service_id
     * @return array
     */
    public static function getContacts($service_id)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        $contactList = "";

        $query = "SELECT contact_name "
            . "FROM contact c, contact_service_relation cs "
            . "WHERE service_service_id = '$service_id' "
            . "AND c.contact_id = cs.contact_id "
            . "ORDER BY contact_alias";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($contactList != "") {
                $contactList .= ",";
            }
            $contactList .= $row["contact_name"];
        }
        return $contactList;
    }

    /**
     * 
     * @param int $service_id
     * @return array
     */
    public static function getContactGroups($service_id)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        $contactgroupList = "";

        $query = "SELECT cg_name "
            . "FROM contactgroup cg, contactgroup_service_relation cgs "
            . "WHERE service_service_id = '$service_id' "
            . "AND cg.cg_id = cgs.contactgroup_cg_id "
            . "ORDER BY cg_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($contactgroupList != "") {
                $contactgroupList .= ",";
            }
            $contactgroupList .= $row["cg_name"];
        }
        return $contactgroupList;
    }
}

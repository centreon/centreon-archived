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
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        '<input id="allService" class="allService" type="checkbox">' => 'service_id',
        'Host Name' => 'host_name',
        'Name' => 'service_description',
        'Scheduling' => array(
            'Interval' => 'service_normal_check_interval',
            'Retry Interval' => 'service_retry_check_interval',
            'Max Atp' => 'service_max_check_attempts'
        ),
        'Notifications' => '[SPECFIELD]',
        'Parent Template' => "[SPECFIELD]service_template_model_stm_id IN (SELECT service_id FROM service WHERE service_description LIKE '::search_value::')",
        'Status' => 'service_activate'
    );
    
    /**
     *
     * @var type 
     */
    public static $additionalColumn = array(
        'host_id',
        'service_template_model_stm_id'
    );
    
    /**
     *
     * @var array 
     */
    public static $researchIndex = array(
        'service_id',
        'host_name',
        'service_description',
        'service_normal_check_interval',
        'service_retry_check_interval',
        'service_max_check_attempts',
        '[SPECFIELD]',
        "[SPECFIELD]service_template_model_stm_id IN (SELECT service_id FROM service WHERE service_description LIKE '::search_value::')",
        'service_activate'
    );
    
    /**
     *
     * @var string 
     */
    public static $specificConditions = "h.host_id = hsr.host_host_id AND service_id=hsr.service_service_id AND service_register = '1' ";
    
    /**
     *
     * @var string 
     */
    public static $linkedTables = "host h, host_service_relation hsr";
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'text',
        'text',
        'none',
        'none',
        'none',
        'none',
        'text',
        array('select' => array(
                'Enabled' => '1',
                'Disabled' => '0',
                'Trash' => '2'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $columnCast = array(
        'service_activate' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>',
                '2' => '<span class="label label-warning">Trash</span>',
            )
        ),
        'service_notifications' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>',
                '2' => '<span class="label label-info">Default</span>',
            )
        ),
        'service_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::service_description::'
            )
        ),
        'service_description' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/service/[i:id]',
                'routeParams' => array(
                    'id' => '::service_id::',
                    'advanced' => '0'
                ),
                'linkName' => '::service_description::'
            )
        ),
        'host_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/host/[i:id]',
                'routeParams' => array(
                    'id' => '::host_id::'
                ),
                'linkName' => '::host_name::'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array(
        'none',
        'text',
        'text',
        'none',
        'none',
        'none',
        'text',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0',
                'Trash' => '2'
            )
        )
    );
    
    /**
     * 
     * @param array $resultSet
     */
    public static function formatDatas(&$resultSet)
    {
        $previousHost = '';
        foreach ($resultSet as &$myServiceSet) {
            
            // Keep up
            $save = $myServiceSet['service_activate'];
            unset($myServiceSet['service_activate']);
            
            // Set host_name
            if ($myServiceSet['host_name'] === $previousHost) {
                $myServiceSet['host_name'] = '';
            } else {
                $previousHost = $myServiceSet['host_name'];
                $myServiceSet['host_name'] = \CentreonConfiguration\Repository\HostRepository::getIconImage(
                    $myServiceSet['host_name']
                ).'&nbsp;'.$myServiceSet['host_name'];
            }
            
            // Set Scheduling
            $myServiceSet['service_normal_check_interval'] = self::formatNotificationOptions(
                self::getMyServiceField($myServiceSet['service_id'], 'service_normal_check_interval')
            );
            $myServiceSet['service_retry_check_interval'] = self::formatNotificationOptions(
                self::getMyServiceField($myServiceSet['service_id'], 'service_normal_check_interval')
            );
            $myServiceSet['service_max_check_attempts'] = self::getMyServiceField(
                $myServiceSet['service_id'],
                'service_max_check_attempts'
            );
            $myServiceSet['service_notifications'] = self::getNotificicationsStatus($myServiceSet['service_id']);
            
            // Get Real Service Description
            if (!$myServiceSet["service_description"]) {
                $myServiceSet["service_description"] = self::getMyServiceAlias(
                    $myServiceSet['service_template_model_stm_id']
                );
            } else {
                $myServiceSet["service_description"] = str_replace(
                    '#S#',
                    "/",
                    $myServiceSet["service_description"]
                );
                $myServiceSet["service_description"] = str_replace(
                    '#BS#',
                    "\\",
                    $myServiceSet["service_description"]
                );
            }
            
            // Set Tpl Chain
            $tplStr = null;
            $tplArr = self::getMyServiceTemplateModels($myServiceSet["service_template_model_stm_id"]);
            $tplArr['description'] = str_replace('#S#', "/", $tplArr['description']);
            $tplArr['description'] = str_replace('#BS#', "\\", $tplArr['description']);
            $tplRoute = str_replace(
                "//",
                "/",
                \Centreon\Internal\Di::getDefault()
                    ->get('router')
                    ->getPathFor(
                        '/configuration/servicetemplate/[i:id]',
                        array('id' => $tplArr['id'])
                    )
            );
            
            $tplStr .= "<a href='".$tplRoute."'>".$tplArr['description']."</a>";
            $myServiceSet['parent_template'] = $tplStr;
            
            $myServiceSet['service_description'] = self::getIconImage($myServiceSet['service_id']).
                '&nbsp;'.$myServiceSet['service_description'];
            
            $myServiceSet['service_activate'] = $save;
        }
    }
    
    /**
     * 
     * @param integer $interval
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
     * @param integer $service_id
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
     * @param integer $service_id
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
     * @param type $service_template_id
     * @return type
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
     * @param integer $service_id
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
     * @param integer $service_id
     * @return string
     */
    public static function getIconImage($service_id)
    {
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $config = \Centreon\Internal\Di::getDefault()->get('config');
        $finalRoute = rtrim($config->get('global', 'base_url'), '/');
        
        while (1) {
            $stmt = $dbconn->query(
                "SELECT esi_icon_image, service_template_model_stm_id "
                . "FROM service, extended_service_information "
                . "WHERE service_service_id = '$service_id' "
                . "AND service_id = service_service_id"
            );
            $esiResult = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!is_null($esiResult['esi_icon_image'])) {
                $finalRoute .= "<img src='".$finalRoute.$esiResult['esi_icon_image'].">";
                break;
            } elseif (is_null($esiResult['esi_icon_image']) && !is_null($esiResult['service_template_model_stm_id'])) {
                $finalRoute = "<i class='fa fa-gear'></i>";
                break;
            }
            
            $service_id = $esiResult['service_template_model_stm_id'];
        }
        
        return $finalRoute;
    }

    public static function getTripleChoice() {
        $content = array();
        $content["service_max_check_attempts"] = 1;
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

    public static function generateServices($host_id) 
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Field to not display */
        $disableField = static::getTripleChoice();
        $field = "s.service_id, h.host_id, h.host_name, s.service_description, s.service_alias, s.service_template_model_stm_id, s.command_command_id, s.command_command_id_arg, s.timeperiod_tp_id, s.command_command_id2, s.command_command_id_arg2, s.timeperiod_tp_id2, s.display_name, s.service_is_volatile, s.service_max_check_attempts, s.service_normal_check_interval, s.service_retry_check_interval, s.service_active_checks_enabled, s.service_passive_checks_enabled, s.initial_state, s.service_parallelize_check, s.service_obsess_over_service, s.service_check_freshness, s.service_freshness_threshold, s.service_event_handler_enabled, s.service_low_flap_threshold, s.service_high_flap_threshold, s.service_flap_detection_enabled, s.service_process_perf_data, s.service_retain_status_information, s.service_retain_nonstatus_information, s.service_notification_interval, s.service_notification_options, s.service_notifications_enabled, s.service_first_notification_delay, s.service_stalking_options ";
        
        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $query = "SELECT $field FROM host h, service s, host_service_relation r WHERE h.host_id = $host_id AND h.host_id = r.host_host_id AND s.service_id = r.service_service_id AND service_activate = '1' AND service_register = '1' ORDER BY host_name, service_description";
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
                } else if ((!isset($disableField[$key]) && $value != "")) {
                    if (isset($disableField[$key]) && $value != 2) {
                        ;
                    } else {
                        if ($key != 'service_description') {
                            $key = str_replace("service_", "", $key);
                        }
                        if ($key == 'command_command_id_arg1' || $key == 'command_command_id_arg2') {
                            $args = $value;
                        }
                        if ($key == 'check_command' || $key == 'event_handler') {
                            $value = CommandRepository::getCommandName($value).$args;
                            $args = "";
                        } 
                        if ($key == "template_model_stm_id") {
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

        unset($content);
    }
    
    public static function getContacts($service_id) 
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        $contactList = "";

        $query = "SELECT contact_alias FROM contact c, contact_service_relation cs WHERE service_service_id = '$service_id' AND c.contact_id = cs.contact_id ORDER BY contact_alias";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($contactList != "") {
                $contactList .= ","; 
            }
            $contactList .= $row["contact_alias"];
        }
        return $contactList;
    }

    public static function getContactGroups($service_id) 
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        $contactgroupList = "";

        $query = "SELECT cg_name FROM contactgroup cg, contactgroup_service_relation cgs WHERE service_service_id = '$service_id' AND cg.cg_id = cgs.contactgroup_cg_id ORDER BY cg_name";
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


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
class HostRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'host';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Host';
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        '<input id="allHost" class="allHost" type="checkbox">' => 'host_id',
        'Name' => 'host_name',
        'Description' => 'host_alias',
        'IP Address / DNS' => 'host_address',
        'Interval' => 'host_check_interval', 
        'Retry' => 'host_max_check_attempts',
        'Status' => 'host_activate'
    );
    
    /**
     *
     * @var array 
     */
    public static $researchIndex = array(
        'host_id',
        'host_name',
        'host_alias',
        'host_address',
        'host_check_interval',
        'host_max_check_attempts',
        'host_activate'
    );
    
    public static $specificConditions = "host_register = '1' ";
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'search_name',
        'search_description',
        'search_address',
        'interval', 
        'retry',
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
        'host_activate' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>',
                '2' => 'Trash',
            )
        ),
        'host_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::host_name::'
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
        'search_name',
        'search_description',
        'search_address',
        'interval', 
        'retry',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0',
                'Trash' => '2'
            )
        )
    );

    /**
     * @see \Centreon\Repository\Repository::$hasCategory
     */
    public static $hasCategory = true;

    /**
     * @see \Centreon\Repository\Repository:$groupname
     */
    public static $groupname = 'Hostgroup';
    
    /**
     * 
     * @param array $resultSet
     */
    public static function formatDatas(&$resultSet)
    {
        foreach ($resultSet as &$myHostSet) {
            $myHostSet['host_name'] = self::getIconImage($myHostSet['host_name']).
                '&nbsp;'.$myHostSet['host_name'];
        }
    }
    
    /**
     * 
     * @param string $host_name
     * @return string
     */
    public static function getIconImage($host_name)
    {
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $config = \Centreon\Internal\Di::getDefault()->get('config');
        $finalRoute = rtrim($config->get('global', 'base_url'), '/');
        
        while (1) {
            $stmt = $dbconn->query(
                "SELECT ehi_icon_image, host_id "
                . "FROM host, extended_host_information "
                . "WHERE host_name = '$host_name' "
                . "AND host_id = host_host_id"
            );
            $ehiResult = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $stmtTpl = $dbconn->query(
                "SELECT host_tpl_id, host_name "
                . "FROM host, host_template_relation "
                . "WHERE host_host_id = '$ehiResult[host_id]' "
                . "AND host_id = host_host_id "
                . "LIMIT 1"
            );
            $tplResult = $stmtTpl->fetch(\PDO::FETCH_ASSOC);

            if (!is_null($ehiResult['ehi_icon_image'])) {
                $finalRoute .= "<img src='".$finalRoute.$ehiResult['ehi_icon_image']."'>";
                break;
            } elseif (is_null($ehiResult['ehi_icon_image'])/* && !is_null($tplResult['host_tpl_id'])*/) {
                $finalRoute = "<i class='fa fa-hdd-o'></i>";
                break;
            }
            
            $host_name = $tplResult['host_name'];
        }
        
        return $finalRoute;
    }

    public static function getTripleChoice() {
        $content = array();
        $content["host_max_check_attempts"] = 1;
        $content["host_active_checks_enabled"] = 1;
        $content["host_passive_checks_enabled"] = 1;
        $content["host_obsess_over_host"] = 1;
        $content["host_check_freshness"] = 1;
        $content["host_event_handler_enabled"] = 1;
        $content["host_flap_detection_enabled"] = 1;
        $content["host_process_perf_data"] = 1;
        $content["host_retain_status_information"] = 1;
        $content["host_retain_nonstatus_information"] = 1;
        $content["host_notifications_enabled"] = 1;
        $content["host_stalking_options"] = 1;
        return $content;
    }
    
    public static function generateHosts(& $filesList, $poller_id, $path, $filename) 
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Field to not display */
        $disableField = static::getTripleChoice();
        $field = "host_id, host_name, host_alias, host_address, display_name, host_max_check_attempts, host_check_interval, host_active_checks_enabled, host_passive_checks_enabled, command_command_id_arg1, command_command_id AS check_command, timeperiod_tp_id AS check_period, host_obsess_over_host, host_check_freshness, host_freshness_threshold, host_event_handler_enabled, command_command_id_arg2, command_command_id2 AS event_handler, host_flap_detection_enabled, host_low_flap_threshold, host_high_flap_threshold, flap_detection_options, host_process_perf_data, host_retain_status_information, host_retain_nonstatus_information, host_notifications_enabled, host_notification_interval, host_notification_options, timeperiod_tp_id2 AS notification_period, host_stalking_options, host_register ";
        
        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $query = "SELECT $field FROM host WHERE host_activate = '1' AND host_register = '1' ORDER BY host_name";
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
                    $host_name = "";
                } else if ((!isset($disableField[$key]) && $value != "")) {
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
                            $value = CommandRepository::getCommandName($value).$args;
                            $args = "";
                        }

                        if ($key == "contact_additive_inheritance") {
                            $tmpContact = static::getContacts($host_id);
                            if ($tmpContact != "") {
                                if ($value = 1) {
                                    $tmpData["contacts"] = "+";
                                }
                                $tmpData["contacts"] .= $tmpContact; 
                            }
                        } else if ($key == "cg_additive_inheritance") {
                            $tmpContact = static::getContactGroups($host_id);
                            if ($tmpContact != "") {
                                if ($value = 1) {
                                    $tmpData["contactgroups"] = "+";
                                }
                                $tmpData["contactgroups"] .= $tmpContact; 
                            }
                        } else if ($key == "name") {
                            $tmpData[$key] = $value;
                            $template = HosttemplateRepository::getTemplates($host_id);
                            if ($template != "") {
                                $tmpData["use"] = $template; 
                            }
                        } else {
                            $tmpData[$key] = $value;
                        }
                    }
                }                
            }
            $tmp["content"] = $tmpData;
            $content[] = $tmp;
            
            /* Write Service Properties */
            $services = ServiceRepository::generateServices($host_id);
            foreach ($services as $contentService) {
                $content[] = $contentService;
            }
            
            /* Write Check-Command configuration file */
            print "Write : ".$path.$poller_id."/".$filename.$host_name."-".$host_id.".cfg \n<br>";
            WriteConfigFileRepository::writeObjectFile($content, $path.$poller_id."/".$filename.$host_name."-".$host_id.".cfg", $filesList, $user = "API");
           
        }


        unset($content);
    }

    public static function getContacts($host_id) 
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        $contactList = "";

        $query = "SELECT contact_alias FROM contact c, contact_host_relation ch WHERE host_host_id = '$host_id' AND c.contact_id = ch.contact_id ORDER BY contact_alias";
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

    public static function getContactGroups($host_id) 
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        $contactgroupList = "";

        $query = "SELECT cg_name FROM contactgroup cg, contactgroup_host_relation cgh WHERE host_host_id = '$host_id' AND cg.cg_id = cgh.contactgroup_cg_id ORDER BY cg_name";
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

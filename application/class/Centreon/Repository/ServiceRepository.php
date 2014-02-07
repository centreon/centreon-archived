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

namespace Centreon\Repository;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class ServiceRepository extends \Centreon\Repository\Repository
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
        '<input id="allService" type="checkbox">' => 'service_id',
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
     * @var type 
     */
    public static $specificConditions = "h.host_id = hsr.host_host_id AND service_id=hsr.service_service_id AND service_register = '1' ";
    
    /**
     *
     * @var type 
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
     * @var type 
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
            'parameters' => array()
        ),
        'service_description' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/service/[i:id]',
                'routeParams' => array(
                    'id' => '::service_id::'
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
                $myServiceSet['host_name'] = '<img src="'.
                    \Centreon\Repository\HostRepository::getIconImage($myServiceSet['host_name']).
                    '" />&nbsp;'.$myServiceSet['host_name'];
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
                $myServiceSet["service_description"] = self::getMyServiceAlias($myServiceSet['service_template_model_stm_id']);
            } else {
                $myServiceSet["service_description"] = str_replace('#S#', "/", $myServiceSet["service_description"]);
                $myServiceSet["service_description"] = str_replace('#BS#', "\\", $myServiceSet["service_description"]);
            }
            
            // Set Tpl Chain
            $tplStr = null;
            $tplArr = self::getMyServiceTemplateModels($myServiceSet["service_template_model_stm_id"]);
            $tplArr['description'] = str_replace('#S#', "/", $tplArr['description']);
            $tplArr['description'] = str_replace('#BS#', "\\", $tplArr['description']);
            $tplRoute = str_replace(
                "//",
                "/",
                \Centreon\Core\Di::getDefault()
                    ->get('router')
                    ->getPathFor('/configuration/servicetemplate/[i:id]', array('id' => $tplArr['id']))
            );
            
            $tplStr .= "<a href='".$tplRoute."'>".$tplArr['description']."</a>";
            $myServiceSet['parent_template'] = $tplStr;
            
            $myServiceSet['service_description'] = '<img src="'.self::getIconImage($myServiceSet['service_id']).
                '" />&nbsp;'.$myServiceSet['service_description'];
            
            $myServiceSet['service_activate'] = $save;
        }
    }
    
    public static function formatNotificationOptions($interval)
    {
        // Initializing connection
        $intervalLength = \Centreon\Core\Di::getDefault()->get('config')->get('default', 'interval_length');
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
    
    public static function getMyServiceField($service_id = null, $field)
    {
		if (!$service_id){
            return;
        }
        
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
		$tab = array();
		while (1) {
			$stmt = $dbconn->query("SELECT `".$field."`, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
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
    
    public function getNotificicationsStatus($service_id)
    {
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        while(1) {
			$stmt = $dbconn->query("SELECT service_notifications_enabled, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row = $stmt->fetchAll();
            
            if (($row[0]['service_notifications_enabled'] != 2) || (!$row[0]['service_template_model_stm_id'])) {
                return $row[0]['service_notifications_enabled'];
            }
            
            $service_id = $row[0]['service_template_model_stm_id'];
		}
        
    }
    
    public static function getMyServiceTemplateModels($service_template_id = null)
    {
		if (!$service_template_id) {
            return;
        }
        
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $stmt = $dbconn->query("SELECT service_description FROM service WHERE service_id = '".$service_template_id."' LIMIT 1");
        $row = $stmt->fetchAll();
        $tplArr = array(
            'id' => $service_template_id,
            'description' => \html_entity_decode(self::db2str($row[0]["service_description"]), ENT_QUOTES, "UTF-8")
        );
		return $tplArr;
	}
    
    public static function db2str($string)
    {
		$string = str_replace('#BR#', "\\n", $string);
		$string = str_replace('#T#', "\\t", $string);
		$string = str_replace('#R#', "\\r", $string);
		$string = str_replace('#S#', "/", $string);
		$string = str_replace('#BS#', "\\", $string);
		return $string;
	}
    
    public static function getMyServiceAlias($service_id)
    {
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');

		$tab = array();
		while(1) {
			$stmt = $dbconn->query("SELECT service_alias, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			$row = $stmt->fetchRow();
			if ($row["service_alias"])	{
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
    
    public static function getIconImage($service_id)
    {
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $config = \Centreon\Core\Di::getDefault()->get('config');
        $finalRoute = rtrim($config->get('global','base_url'), '/');
        
        while (1) {
            $stmt = $dbconn->query("SELECT esi_icon_image, service_template_model_stm_id "
                . "FROM service, extended_service_information "
                . "WHERE service_service_id = '$service_id' "
                . "AND service_id = service_service_id");
            $esiResult = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!is_null($esiResult['esi_icon_image'])) {
                $finalRoute .= $esiResult['esi_icon_image'];
                break;
            } elseif (is_null($esiResult['esi_icon_image']) && !is_null($esiResult['service_template_model_stm_id'])) {
                $finalRoute .= '/static/centreon/img/icons/16x16/gear.gif';
                break;
            }
            
            $service_id = $esiResult['service_template_model_stm_id'];
        }
        
        return $finalRoute;
    }
}

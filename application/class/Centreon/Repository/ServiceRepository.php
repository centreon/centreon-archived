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
            'Normal Check Interval' => 'service_normal_check_interval',
            'Retry Check Interval' => 'service_retry_check_interval',
            'Max Check Attempts' => 'service_max_check_attempts'
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
                '0' => 'Disabled',
                '1' => 'Enabled',
                '2' => 'Trash',
            )
        ),
        'service_notifications' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => 'Disabled',
                '1' => 'Enabled',
                '2' => 'Default',
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
    
    /**
     * 
     * @param array $params
     * @return array
     */
    public static function getDatasForDatatable($params)
    {
        // Init vars
        $additionalTables = '';
        $conditions = '';
        $limitations = '';
        $sort = '';
        
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        // Getting selected field(s)
        $field_list = '';
        foreach (static::$datatableColumn as $field) {
            if (!is_array($field)) {
                if (substr($field, 0, 11) !== '[SPECFIELD]') {
                    $field_list .= $field.',';
                }
            }
        }
        
        foreach (static::$additionalColumn as $field) {
            $field_list .= $field.',';
        }
        
        $field_list = trim($field_list, ',');
        
        // Getting table column
        $c = array_values(static::$researchIndex);
        
        if (!empty(static::$specificConditions)) {
            $conditions = "WHERE ".static::$specificConditions;
        }
        
        if (!empty(static::$aclConditions)) {
            if (empty($conditions)) {
                $conditions = "WHERE ".static::$aclConditions;
            } else {
                $conditions = "AND ".static::$aclConditions;
            }
        }
        
        if (!empty(static::$linkedTables)) {
            $additionalTables = ', '.static::$linkedTables;
        }
        
        // Conditions (Recherche)
        foreach ($params as $paramName=>$paramValue) {
            if (strpos($paramName, 'sSearch_') !== false) {
                if (!empty($paramValue) || $paramValue === "0") {
                    $colNumber = substr($paramName, strlen('sSearch_'));
                    
                    if (substr($c[$colNumber], 0, 11) === '[SPECFIELD]') {
                        $research = str_replace('::search_value::', '%'.$paramValue.'%', substr($c[$colNumber], 11));
                    } else {
                        $research = $c[$colNumber]." like '%".$paramValue."%' ";
                    }
                    
                    if (empty($conditions)) {
                        $conditions = "WHERE ".$research;
                    } else {
                        $conditions .= "AND ".$research;
                    }
                }
            }
        }
        
        // Sort
        if (substr($c[$params['iSortCol_0']], 0, 11) !== '[SPECFIELD]') {
            $sort = 'ORDER BY '.$c[$params['iSortCol_0']].' '.$params['sSortDir_0'];
        }
        
        // Processing the limit
        $limitations = 'LIMIT '.$params['iDisplayStart'].','.$params['iDisplayLength'];
        
        // Building the final request
        $finalRequest = "SELECT $field_list FROM ".static::$tableName."$additionalTables $conditions "
            . "$sort $limitations";
        
        try {
            // Executing the request
            $stmt = $dbconn->query($finalRequest);
        } catch (Exception $e) {
            
        }
        
        // Returning the result
        $resultSet = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $countTab = count($resultSet);
        $objectTab = array();
        for($i=0; $i<$countTab; $i++) {
            $objectTab[] = static::$objectName;
        }
        
        self::formatDatas($resultSet);
        
        return self::array_values_recursive(
            \array_values(
                \Centreon\Core\Datatable::removeUnwantedFields(
                    static::$objectName,
                    \array_map(
                        "\\Centreon\\Core\\Datatable::castResult",
                        $resultSet,
                        $objectTab
                    )
                )
            )
        );
    }
    
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
            }
            
            // Set Scheduling
            $myServiceSet['service_normal_check_interval'] = self::formatNotificationOptions(
                self::getMyServiceField($myServiceSet['service_id'], 'service_normal_check_interval')
            );
            $myServiceSet['service_retry_check_interval'] = self::formatNotificationOptions(
                self::getMyServiceField($myServiceSet['service_id'], 'service_normal_check_interval')
            );
            $myServiceSet['service_max_check_attempts'] = self::formatNotificationOptions(
                self::getMyServiceField($myServiceSet['service_id'], 'service_max_check_attempts')
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
            
            $tplStr .= "&nbsp;->&nbsp;<a href='".$tplRoute."'>".$tplArr['description']."</a>";
            $myServiceSet['parent_template'] = $tplStr;
            
            $myServiceSet['service_activate'] = $save;
        }
    }
    
    public static function formatNotificationOptions($interval)
    {
        // Initializing connection
        $intervalLength = \Centreon\Core\Di::getDefault()->get('config')->get('global', 'interval_length');
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
    
    public static function getMyServiceAlias($service_id = null)
    {
		if (!$service_id) {
            return;
        }
        
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
}

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
class PollerRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $objectName = 'Poller';
    
    /**
     *
     * @var array Main database field to get
     */
    public static $datatableColumn = array(
        '<input id="allPoller" class="allPoller" type="checkbox">' => 'id',
        'Name' => 'name',
        'Ip Address' => 'ns_ip_address',
        'Localhost' => 'last_restart',
        'Is Running' => 'is_currently_running',
        'Has changed' => 'hasChanged',
        'Start time' => 'start_time',
        'Last Update' => 'last_restart',
        'Version' => 'version',
        'Default' => 'ns_activate',
        'Status' => 'ns_activate'
    );
    
    /**
     *
     * @var array Column name for the search index
     */
    public static $researchIndex = array(
        'id',
        'name',
        'ns_ip_address',
        'localhost',
        'is_currently_running',
        'has_changed',
        'program_start_time',
        'last_update',
        'version',
        'is_default',
        'ns_activate'
    );
    
    /**
     * @inherit doc
     * @var array 
     */
    public static $columnCast = array(
        'id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::name::'
            )
        ),
        'tp_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/poller/[i:id]',
                'routeParams' => array(
                    'id' => '::id::'
                ),
                'linkName' => '::name::'
            )
        ),
        'program_start_time' => array(
            'type' => 'date',
            'parameters' => array(
                'date' => 'd/m/Y H:i:s'
            )
        ),
        'last_alive' => array(
            'type' => 'date',
            'parameters' => array(
                'date' => 'd/m/Y H:i:s'
            )
        ),
        'localhost' => array(
            'type' => 'select',
            'parameters' => array(
                '0' => 'No',
                '1' => 'Yes'
            )
        ),
        'hasChanged' => array(
            'type' => 'select',
            'parameters' => array(
                '0' => 'No',
                '1' => '<span class="label label-warning">Yes</span>'
            )
        ),
        'is_currently_running' => array(
            'type' => 'select',
            'parameters' => array(
                '0' => '<span class="label label-danger">No</span>',
                '1' => '<span class="label label-success">Yes</span>'
            )
        ),
        'is_default' => array(
            'type' => 'select',
            'parameters' => array(
                '0' => 'No',
                '1' => 'Yes'
            )
        ),
        'ns_activate' => array(
            'type' => 'select',
            'parameters' => array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>'
            )
        ),
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'text',
        'text',
        array(
            'select' => array(
                'Yes' => '1',
                'No' => '0'
            )
        ),
        array(
            'select' => array(
                'Yes' => '1',
                'No' => '0'
            )
        ),
        array(
            'select' => array(
                'Yes' => '1',
                'No' => '0'
            )
        ),
        'none',
        'none',
        'text',
        array(
            'select' => array(
                'Yes' => '1',
                'No' => '0'
            )
        ),
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        ),
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
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        ),
    );
    
    /**
     * 
     * @param array $params
     * @return array
     */
    public static function getDatasForDatatable($params)
    {
        // Get centreon DB and centreon storage DB connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $dbconnStorage = $di->get('db_storage');
        $conditions = '';
        $limitations = '';
        
        // Processing the limit
        if ($params['iDisplayLength'] > 0) {
            $limitations = 'LIMIT '.$params['iDisplayStart'].','.$params['iDisplayLength'];
        }
        
        // Conditions (Recherche)
        foreach ($params as $paramName => $paramValue) {
            if (strpos($paramName, 'sSearch_') !== false) {
                if (!empty($paramValue) || $paramValue === "0") {
                    $colNumber = substr($paramName, strlen('sSearch_'));
                    if (substr($c[$colNumber], 0, 11) !== '[SPECFIELD]') {
                        $searchString = $c[$colNumber]." like '%".$paramValue."%' ";
                    } else {
                        $customSearchString = substr($c[$colNumber], 11);
                        $searchString = str_replace('::search_value::', '%'.$paramValue.'%', $customSearchString);
                    }
                    
                    if (empty($conditions)) {
                        $conditions = "WHERE ".$searchString;
                    } else {
                        $conditions .= "AND ".$searchString;
                    }
                }
            }
        }
        
        // Get List of Nagios Servers
        $sqlNagiosServer = "SELECT SQL_CALC_FOUND_ROWS id, name, localhost, is_default, last_restart, ns_ip_address, ns_activate FROM nagios_server "
            . " $conditions"
            . " ORDER BY name"
            . " $limitations";
        $stmtNagiosServer = $dbconn->query($sqlNagiosServer);
        $resultNagiosServer = $stmtNagiosServer->fetchAll(\PDO::FETCH_ASSOC);
        
        
        $sqlBroker = "SELECT start_time AS program_start_time, running AS is_currently_running, instance_id, name AS instance_name , last_alive FROM instances";
        $stmtBroker = $dbconnStorage->query($sqlBroker);
        $resultBroker = $stmtBroker->fetchAll(\PDO::FETCH_ASSOC);
        
        
        $pollerNumber = count($resultNagiosServer);
        $sqlBroker2 = "SELECT DISTINCT instance_id, version AS program_version, engine AS program_name, name AS instance_name FROM instances LIMIT $pollerNumber";
        $stmtBroker2 = $dbconnStorage->query($sqlBroker2);
        $resultBroker2 = $stmtBroker2->fetchAll(\PDO::FETCH_ASSOC);
        
        
        // Build Up the line
        foreach ($resultNagiosServer as &$nagiosServer) {
            foreach ($resultBroker as $key => $broker) {
                if ($broker['instance_name'] == $nagiosServer['name']) {
                    $nagiosServer = array_merge($nagiosServer, $broker);
                    unset($resultBroker[$key]);
                }
            }
            foreach ($resultBroker2 as $key => $broker2) {
                if ($broker2['instance_name'] == $nagiosServer['name']) {
                    $nagiosServer = array_merge($nagiosServer, $broker2);
                    unset($resultBroker2[$key]);
                }
            }
            $nagiosServer['hasChanged'] = self::checkChangeState($nagiosServer['id'], $nagiosServer['last_restart']);
        }
        
        $countTab = count($resultNagiosServer);
        $objectTab = array();
        for ($i=0; $i<$countTab; $i++) {
            $objectTab[] = array(
                static::$objectName,
                static::$moduleName
            );
        }
        
        static::formatDatas($resultNagiosServer);
        
        return self::arrayValuesRecursive(
            \array_values(
                \Centreon\Internal\Datatable::removeUnwantedFields(
                    static::$moduleName,
                    static::$objectName,
                    \array_map(
                        "\\Centreon\\Internal\\Datatable::castResult",
                        $resultNagiosServer,
                        $objectTab
                    )
                )
            )
        );
    }
    
    /**
     * 
     * @param array $resultSet
     */
    public static function formatDatas(&$resultSet)
    {
        $cache = $resultSet;
        $resultSet = array();
        
        $nbPoller = count($cache);
        for ($i=0; $i < $nbPoller; $i++) {
            $resultSet[$i]['id'] = $cache[$i]['id'];
            $resultSet[$i]['name'] = $cache[$i]['name'];
            $resultSet[$i]['ns_ip_address'] = $cache[$i]['ns_ip_address'];
            $resultSet[$i]['localhost'] = $cache[$i]['localhost'];
            $resultSet[$i]['is_currently_running'] = $cache[$i]['is_currently_running'];
            $resultSet[$i]['hasChanged'] = $cache[$i]['hasChanged'];
            $resultSet[$i]['program_start_time'] = $cache[$i]['program_start_time'];
            $resultSet[$i]['last_alive'] = $cache[$i]['last_alive'];
            $resultSet[$i]['version'] = $cache[$i]['program_version'];
            $resultSet[$i]['is_default'] = $cache[$i]['is_default'];
            $resultSet[$i]['ns_activate'] = $cache[$i]['ns_activate'];
        }
    }
    
    /**
	 *
	 * Check if a service or an host has been
	 * changed for a specific poller.
	 * @param unknown_type $poller_id
	 * @param unknown_type $last_restart
	 * @return number
	 */
	public static function checkChangeState($poller_id, $last_restart)
    {
        if (!isset($last_restart) || $last_restart == "") {
			return 0;
		}
        
        // Get centreon DB and centreon storage DB connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconnStorage = $di->get('db_storage');

		$request = "SELECT *
						FROM log_action
						WHERE
							action_log_date > $last_restart AND
							((object_type = 'host' AND
							object_id IN (
								SELECT host_host_id
								FROM centreon.ns_host_relation
								WHERE nagios_server_id = '$poller_id'
							)) OR
							(object_type = 'service') AND
							object_id IN (
								SELECT service_service_id
								FROM centreon.ns_host_relation nhr, centreon.host_service_relation hsr
								WHERE nagios_server_id = '$poller_id' AND hsr.host_host_id = nhr.host_host_id
						))";
		$DBRESULT = $dbconnStorage->query($request);
		if ($DBRESULT->rowCount()) {
			return 1;
		}
		return 0;
	}
    
    /**
     * 
     * @param array $params
     * @return integer
     */
    public static function getTotalRecordsForDatatable($params)
    {
        // Get centreon DB and centreon storage DB connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        // 
        $sqlCalNagiosServer = "SELECT COUNT(`id`) as nb_poller FROM `nagios_server`";
        $stmtCalNagiosServer = $dbconn->query($sqlCalNagiosServer);
        $resultCalNagiosServer = $stmtCalNagiosServer->fetchAll(\PDO::FETCH_ASSOC);
        
        return $resultCalNagiosServer[0]['nb_poller'];
    }
}

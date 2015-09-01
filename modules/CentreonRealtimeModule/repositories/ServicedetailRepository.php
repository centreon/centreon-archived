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

namespace CentreonRealtime\Repository;

use Centreon\Internal\Di;
use Centreon\Internal\Exception;

/**
 * Repository for service data
 *
 * @author Sylvestre Ho <sho@centreon.com>
 * @version 3.0.0
 */
class ServicedetailRepository extends ObjectdetailRepository
{
    const SCHEDULE_CHECK = 1;

    /* deprecated ? */
    const SCHEDULE_FORCED_CHECK = 2;

    const ACKNOWLEDGE = 3;

    const REMOVE_ACKNOWLEDGE = 4;

    const DOWNTIME = 5;

    const REMOVE_DOWNTIME = 6;

    const ENABLE_CHECK = 7;

    const DISABLE_CHECK = 8;

    /**
     * Get real time data of a service
     * 
     * @param int $serviceId
     * @return array
     */
    public static function getRealtimeData($serviceId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = 'SELECT h.name as host_name, s.acknowledged, s.scheduled_downtime_depth, s.output, s.latency,
            s.last_check, s.next_check, s.check_period, i.name as instance_name, s.state, s.command_line,
            s.description as service_description, s.state_type, s.perfdata, s.retry_interval, s.active_checks, 
            s.passive_checks, i.last_command_check, s.check_interval, s.max_check_attempts
            FROM rt_hosts h, rt_services s, rt_instances i
            WHERE i.instance_id = h.instance_id
            AND h.host_id = s.host_id
            AND s.enabled = 1
            AND s.service_id = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute(array($serviceId));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get list of monitoring actions for services
     *
     * @param array
     */
    public static function getMonitoringActions()
    {
        $actions = array();
        $actions[self::SCHEDULE_CHECK] = _('Schedule check');
        $actions[self::ACKNOWLEDGE] = _('Acknowledge');
        //$actions[self::REMOVE_ACKNOWLEDGE] = _('Remove acknowledgement');
        $actions[self::DOWNTIME] = _('Set downtime');
        //$actions[self::REMOVE_DOWNTIME] = _('Remove downtime');
        $actions[self::ENABLE_CHECK] = _('Enable check');
        $actions[self::DISABLE_CHECK] = _('Disable check');
        return $actions;
    }

    /**
     * Process command
     * 
     * @param int $cmdId
     * @param array $serviceIds
     * @param array $additionalParams
     */
    public static function processCommand($cmdId, $serviceIds, $additionalParams = array())
    {
        if (count($serviceIds)) {
            $list = implode(',', $serviceIds);
            $sql = "SELECT h.name, s.description, h.instance_id
                FROM rt_services s, rt_hosts h 
                WHERE h.host_id = s.host_id 
                AND s.service_id IN ($list)";
            self::doCommand($cmdId, $sql, $additionalParams);
        }
    }

    /**
     * Get string of a given command
     *
     * @param int $cmdId
     * @throws \Centreon\Internal\Exception
     */
    public static function getCommandString($cmdId)
    {
        static $commands = null;

        if (is_null($commands)) {
            $commands = array();
            $commands[self::SCHEDULE_CHECK] = "SCHEDULE_SVC_CHECK";
            $commands[self::ACKNOWLEDGE] = "ACKNOWLEDGE_SVC_PROBLEM";
            $commands[self::REMOVE_ACKNOWLEDGE] = "REMOVE_SVC_ACKNOWLEDGEMENT";
            $commands[self::DOWNTIME] = "SCHEDULE_SVC_DOWNTIME";
            $commands[self::REMOVE_DOWNTIME] = "DEL_SVC_DOWNTIME";
            $commands[self::ENABLE_CHECK] = "ENABLE_SVC_CHECK";
            $commands[self::DISABLE_CHECK] = "DISABLE_SVC_CHECK";
        }
        if (isset($commands[$cmdId])) {
            return $commands[$cmdId];
        }
        throw new Exception('Unknown command');
    }

    /**
     * Get type of a given command
     *
     * @param int $cmdId
     * @throws \Centreon\Internal\Exception
     */
    public static function getCommandType($cmdId)
    {
        static $commands = null;

        if (is_null($commands)) {
            $commands = array();
            $commands[self::SCHEDULE_CHECK] = "engine";
            $commands[self::ACKNOWLEDGE] = "broker";
            $commands[self::REMOVE_ACKNOWLEDGE] = "broker";
            $commands[self::DOWNTIME] = "broker";
            $commands[self::REMOVE_DOWNTIME] = "broker";
            $commands[self::ENABLE_CHECK] = "engine";
            $commands[self::DISABLE_CHECK] = "engine";
        }
        if (isset($commands[$cmdId])) {
            return $commands[$cmdId];
        }
        throw new Exception('Unknown command');
    }

    /**
     * Get array of host ids from an array of service ids
     *
     * @param array $serviceIds
     * @return array
     */
    public static function getHostIdFromServiceId($serviceIds)
    {
        $db = Di::getDefault()->get('db_centreon');
        $arr = array();
        if (count($serviceIds)) {
            $sql = "SELECT DISTINCT host_id 
                FROM rt_services 
                WHERE service_id IN (".implode(",", $serviceIds).")";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $arr[] = $row['host_id'];
            }
        }
        return $arr;
    }
}

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

/**
 * Repository for host data
 *
 * @author Sylvestre Ho <sho@centreon.com>
 * @version 3.0.0
 */
class HostdetailRepository extends ObjectdetailRepository
{
    const SCHEDULE_CHECK = 101;

    /* deprecated ? */
    const SCHEDULE_FORCED_CHECK = 102;

    const ACKNOWLEDGE = 103;

    const REMOVE_ACKNOWLEDGE = 104;

    const DOWNTIME = 105;

    const REMOVE_DOWNTIME = 106;

    const ENABLE_CHECK = 107;

    const DISABLE_CHECK = 108;

    /**
     * Get real time data of a host
     * 
     * @param int $hostId
     * @return array
     */
    public static function getRealtimeData($hostId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = 'SELECT h.name as host_name, acknowledged, scheduled_downtime_depth, output, latency, command_line, h.alias as host_alias,
            last_check, next_check, check_period, i.name as instance_name, state, h.address as host_address,
            h.state_type
            FROM rt_hosts h, rt_instances i
            WHERE h.instance_id = i.instance_id
            AND h.enabled = 1
            AND h.host_id = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute(array($hostId));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Process command
     * 
     * @param int $cmdId
     * @param array $serviceIds
     * @param array $additionalParams
     */
    public static function processCommand($cmdId, $hostIds, $additionalParams = array())
    {
        if (count($hostIds)) {
            $list = implode(',', $hostIds);
            $sql = "SELECT h.name, h.instance_id
                FROM rt_hosts h 
                WHERE h.host_id IN ($list)";
            self::doCommand($cmdId, $sql, $additionalParams);
        }
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
            $commands[self::SCHEDULE_CHECK] = "SCHEDULE_HOST_CHECK";
            $commands[self::ACKNOWLEDGE] = "ACKNOWLEDGE_HOST_PROBLEM";
            $commands[self::REMOVE_ACKNOWLEDGE] = "REMOVE_HOST_ACKNOWLEDGEMENT";
            $commands[self::DOWNTIME] = "SCHEDULE_HOST_DOWNTIME";
            $commands[self::REMOVE_DOWNTIME] = "DEL_HOST_DOWNTIME";
            $commands[self::ENABLE_CHECK] = "ENABLE_HOST_CHECK";
            $commands[self::DISABLE_CHECK] = "DISABLE_HOST_CHECK";
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
}

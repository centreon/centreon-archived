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
            $sql = "SELECT h.name
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
}

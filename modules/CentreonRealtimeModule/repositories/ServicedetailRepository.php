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
    public static function getRealtimeData($hostId, $serviceId)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = 'SELECT h.name as host_name, s.acknowledged, s.scheduled_downtime_depth, s.output, s.latency,
            s.last_check, s.next_check, s.check_period, i.name as instance_name, s.state, s.command_line,
            s.description as service_description, s.state_type, s.perfdata
            FROM rt_hosts h, rt_services s, rt_instances i
            WHERE i.instance_id = h.instance_id
            AND h.host_id = s.host_id
            AND s.enabled = 1
            AND s.service_id = ?
            AND s.host_id = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute(array($serviceId, $hostId));
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
            $sql = "SELECT h.name, s.description 
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

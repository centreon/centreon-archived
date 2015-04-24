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
use CentreonRealtime\Events\ExternalCommand;

/**
 * Repository for host and object
 *
 * @author Sylvestre Ho <sho@centreon.com>
 * @version 3.0.0
 */
class ObjectdetailRepository
{
    /**
     * Send command to service
     *
     * @param int $cmdId
     * @param int $pollerId The poller ID
     * @param array $params
     * @todo retrieve centcorecmd path
     */
    public static function sendCommand($cmdId, $pollerId, $params)
    {
        $prefix = sprintf("[%u] ", time());
        $command = $prefix . static::getCommandString($cmdId) . ";" .implode(';', $params) . "\n";
        $eventObj = new ExternalCommand($pollerId, $command);
        Di::getDefault()->get('events')->emit('centreon-realtime.command.send', array($eventObj));
    }

    /**
     * Send command for each object returned by the sql query.
     *
     * @param int $cmdId
     * @param str $sql
     * @param array $additionalParams
     */
    public static function doCommand($cmdId, $sql, $additionalParams)
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            switch ($cmdId) {
                case static::SCHEDULE_CHECK:
                    $options = array(time());
                    break;
                case static::ACKNOWLEDGE:
                    $options = array(
                        isset($additionalParams['sticky']) ? 1 : 0,
                        isset($additionalParams['notify']) ? 1 : 0,
                        isset($additionalParams['persistent']) ? 1 : 0,
                        $additionalParams['author'],
                        $additionalParams['comment']
                    );
                    break;
                case static::DOWNTIME:
                    $options = array(
                        $additionalParams['start_time'],
                        $additionalParams['end_time'],
                        isset($additionalParams['fixed']) ? 1 : 0,
                        0,
                        $additionalParams['duration'],
                        $additionalParams['author'],
                        $additionalParams['comment']
                    );
                    break;
                case static::REMOVE_ACKNOWLEDGE:
                    break;
                case static::REMOVE_DOWNTIME:
                    break;
                default:
                    $options = array();
                    break;
            }
            $instanceId = $row['instance_id'];
            unset($row['instance_id']);
            self::sendCommand(
                $cmdId,
                $instanceId,
                array_merge($row, $options)
            );
        }
    }
}

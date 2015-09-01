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
        $commandType = static::getCommandType($cmdId);

        if ($commandType == 'engine') {
            $timestamp = sprintf("[%u] ", time());
            $command = $timestamp . static::getCommandString($cmdId) . ";" .implode(';', $params) . "\n";
            $eventObj = new ExternalCommand($pollerId, $command, $commandType);
            Di::getDefault()->get('events')->emit('centreon-realtime.command.send', array($eventObj));
        } else if ($commandType == 'broker') {
            $command = static::getCommandString($cmdId) . ";" .implode(';', $params) . "\n";
            $eventObj = new ExternalCommand($pollerId, $command, $commandType);
            Di::getDefault()->get('events')->emit('centreon-realtime.command.send', array($eventObj));            
        }
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
                        isset($additionalParams['notify']) ? $additionalParams['notify'] : 0,
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
                        isset($additionalParams['fixed']) ? 0 : $additionalParams['duration'],
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

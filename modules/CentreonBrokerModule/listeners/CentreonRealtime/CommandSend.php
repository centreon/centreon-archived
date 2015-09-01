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
namespace CentreonBroker\Listeners\CentreonRealtime;

use Centreon\Internal\Di;
use CentreonRealtime\Events\ExternalCommand;
use CentreonBroker\Repository\BrokerRepository;

/**
 * Listeners for send a command
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonBroker
 */
class CommandSend
{
    public static function execute(ExternalCommand $command)
    {
        // @todo found poller where I am
        $dbconn = Di::getDefault()->get('db_centreon');

        $query = 'SELECT config_id'
            . ' FROM cfg_centreonbroker'
            . ' WHERE poller_id = :poller_id'
            . ' AND config_name = "central-broker"';
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':poller_id', $command->getPollerId(), \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row === false) {
            throw new \Exception ("Can't get config id");
        }
        $brokerId = $row['config_id'];

        $nodeEvents = BrokerRepository::getBrokerEndpointFromBrokerId($brokerId, 'node_events');
        $nodeEvent = "";
        if (isset($nodeEvents[0]) && isset($nodeEvents[0]['name'])) {
            $nodeEvent = $nodeEvents[0]['name'];
        }

        if ($command->getType() === 'broker') {
            BrokerRepository::sendCommand($command->getPollerId(), $nodeEvent . ';' . $command->getCommand());
        }
    }
}

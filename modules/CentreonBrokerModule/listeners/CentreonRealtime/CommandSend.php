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

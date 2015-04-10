<?php
/*
 * Copyright 2005-2014 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonBam\Listeners\CentreonEngine;

use Centreon\Internal\Di;
use CentreonEngine\Events\AddHost as AddHostEvent;
use CentreonConfiguration\Repository\HostRepository;

class AddHost
{
    /**
     * @param CentreonEngine\Events\AddHost $event
     */
    public static function execute(AddHostEvent $event)
    {
        //var_dump($event->getPollerId());
        //var_dump($event->getHostList());
        $pollerId = $event->getPollerId();
        $hostList = $event->getHostList();
        //echo "$pollerId\n";
 
        $dbconn = Di::getDefault()->get('db_centreon');

        $selectRequest = "SELECT COUNT(poller_id) as poller"
            . " FROM cfg_centreonbroker cb, cfg_centreonbroker_info cbi"
            . " WHERE cb.poller_id=:id"
            . " AND cb.config_id=cbi.config_id"
            . " AND cbi.config_value='bam'";
        $stmtSelect = $dbconn->prepare($selectRequest);
        $stmtSelect->bindParam(':id', $pollerId, \PDO::PARAM_INT);
        $stmtSelect->execute();
        $result = $stmtSelect->fetchAll(\PDO::FETCH_ASSOC);

        if ($result[0]['poller'] > 0) {
            $addBamHost = true;
            foreach ($hostList as &$host) {
                if ($host['host_name'] === '_Module_BAM') {
                    //$host['host_register'] = '1';
                    $addBamHost = false;
                }
            }
            if ($addBamHost) {
                $insertRequest = "INSERT INTO cfg_hosts(host_name, host_address, host_max_check_attempts, poller_id, organization_id, host_register)"
                    . " VALUES('_Module_BAM', '127.0.0.1', '3', :id, 1, '1')";
                $stmtInsert = $dbconn->prepare($insertRequest);
                $stmtInsert->bindParam(':id', $pollerId, \PDO::PARAM_INT);
                $stmtInsert->execute();
                $count = count($hostList);
                $hostList[$count]['host_name'] = '_Module_BAM';
                $hostList[$count]['host_address'] = '127.0.0.1';
                $hostList[$count]['host_register'] = '1';
            }
        }
    }
}

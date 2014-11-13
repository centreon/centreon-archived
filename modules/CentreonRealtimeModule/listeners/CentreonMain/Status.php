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

namespace CentreonRealtime\Listeners\CentreonMain;

use CentreonMain\Events\Status as StatusEvent;
use Centreon\Internal\Di;

/**
 * Event to top counter for host and service
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonMain
 */
class Status
{
    /**
     * Execute the event
     *
     * @param \CentreonMain\Events\Status $event The event object
     */
    public static function execute(StatusEvent $event)
    {
        $values = array(
            'services' => array(
                'unknown' => 0,
                'warning' => 0,
                'critical' => 0
            ),
            'hosts' => array(
                'unknown' => 0,
                'warning' => 0,
                'critical' => 0
            ),
            'pollers' => array(
                'activity' => 0,
                'stopped' => 0,
                'latency' => 0
            )
        );
        $db = Di::getDefault()->get('db_centreon');
        /* Get service critical and warning */
        $query = "SELECT COUNT(service_id) as nb, state
            FROM rt_services
            WHERE state_type = 1
                AND state IN (1, 2, 3)
                AND acknowledged = 0
            GROUP BY state";
        $stmt = $db->query($query);
        while ($row = $stmt->fetch()) {
            if ($row['state'] == 1) {
                $values['services']['warning'] = $row['nb'];
            } elseif ($row['state'] == 2) {
                $values['services']['critical'] = $row['nb'];
            } elseif ($row['state'] == 3) {
                $values['services']['unknown'] = $row['nb'];
            }
        }
        $event->addStatus('service', $values['services']);
        /* Get host critical and warning */
        $query = "SELECT COUNT(host_id) as nb, state
            FROM rt_hosts
            WHERE state_type = 1
                AND state IN (1, 2, 3)
                AND acknowledged = 0
            GROUP BY state";
        $stmt = $db->query($query);
        while ($row = $stmt->fetch()) {
            if ($row['state'] == 1) {
                $values['host']['warning'] = $row['nb'];
            } elseif ($row['state'] == 2) {
                $values['host']['down'] = $row['nb'];
            } elseif ($row['state'] == 3) {
                $values['host']['unknown'] = $row['nb'];
            }
        }
        $event->addStatus('host', $values['hosts']);
        /* Get poller information */
        $query = "SELECT last_alive, running
            FROM rt_instances
            WHERE deleted != 1";
        $stmt = $db->query($query);
        $now = time();
        while ($row = $stmt->fetch()) {
            if ($row['running'] == 0) {
                $values['pollers']['stopped']++;
            } elseif ($row['last_alive'] - $now > 60) {
                $values['pollers']['activity']++;
            }
        }
        $event->addStatus('poller', $values['pollers']);
    }
}

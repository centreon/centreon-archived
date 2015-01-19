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

namespace CentreonRealtime\Controllers;

use Centreon\Internal\Di;
use Centreon\Internal\Controller;

/**
 * Display service monitoring states
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @package Centreon
 * @subpackage CentreonRealtime
 */
class PollerController extends Controller
{

    /**
     * Get the list of poller status in json
     *
     * @method get
     * @route /poller/status
     */
    public function pollerStatusAction()
    {
        $router = Di::getDefault()->get('router');
        $orgId = Di::getDefault()->get('organization');
        $dbconn = Di::getDefault()->get('db_centreon');
        $query = 'SELECT c.name, r.last_alive, r.running
            FROM cfg_pollers c
            LEFT OUTER JOIN rt_instances r
                ON r.instance_id = c.poller_id
            WHERE c.organization_id = :org_id';
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':org_id', $orgId, \PDO::PARAM_INT);
        $stmt->execute();
        $now = time();
        $pollers = array();
        while ($row = $stmt->fetch()) {
            $row['latency'] = 0;
            if (is_null($row['last_alive']) || $row['last_alive'] - $now > 60) {
                $row['disconnect'] = 1;
            } else {
                $row['disconnect'] = 0;
            }
            $pollers[] = $row;
        }
        $router->response()->json(array(
            'success' => true,
            'values' => $pollers
        ));
    }
}

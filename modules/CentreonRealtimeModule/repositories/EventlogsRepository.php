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
use CentreonRealtime\Repository\EventlogsRepository\Database as EventLogDatabase;
use CentreonRealtime\Repository\EventlogsRepository\ElasticSearch;

/**
 * Factory for Eventlogs
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 */
class EventlogsRepository
{
    /**
     * Return the list of events
     *
     * @param $fromTime string The start time in date format Y-m-d H:i:s
     * @param $order string The order for getting events : DESC or ASC
     * @param $limit int The number of event to get
     * @param $filters array The list of fitlers for event
     * @return array
     */
    public static function getEventLogs($fromTime = null, $order = 'DESC', $limit = null, $filters = array())
    {
        $di = Di::getDefault();

        /* If time is a timestamp, convert it */
        if (!is_null($fromTime) && is_numeric($fromTime)) {
            $fromTime = date('Y-m-d H:i:s', $fromTime);
        }

        /* Get configuration */
        $storageType = 'db';
        if ($storageType == 'db') {
            return EventLogDatabase::getEventLogs(
                $fromTime,
                $order,
                $limit,
                $filters
            );
        } elseif ($storageType == 'es') {
            return ElasticSearch::getEventLogs(
                $fromTime,
                $order,
                $limit,
                $filters
            );
        }
        throw new \Exception("The eventlogs storage does not exists");
    }
}

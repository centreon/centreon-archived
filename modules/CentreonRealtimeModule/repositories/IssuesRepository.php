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

/**
 * Repository for Issues
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 */
class IssuesRepository
{
    /**
     * Return the list of issues
     *
     * @param $fromTime string The start time in date format Y-m-d H:i:s
     * @param $order string The order for getting events : DESC or ASC
     * @param $limit int The number of event to get
     * @param $filters array The list of fitlers for event
     * @return array
     */
    public static function getIssues($fromTime = null, $order = 'DESC', $limit = null, $filters = array())
    {
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_storage');
        $globalWheres = array();
        if (false === is_null($fromTime)) {
            $clause = 'i.start_time';
            if ($order == 'DESC') {
                $clause .= ' <= ';
            } else {
                $clause .= ' > ';
            }
            $clause .= ':start_time';
            $globalWheres[] = $clause;
        }

        /* Subquery for hosts */
        $queryHosts = "SELECT i.issue_id, i.host_id, h.name, i.service_id, NULL as description, FROM_UNIXTIME(i.start_time), he.state as state
            FROM issues i, hosts h, hoststateevents he"
        $wheres = array();
        $wheres[] = "i.host_id = h.host_id";
        $wheres[] = "i.host_id = se.host_id";
        $wheres[] = "i.service_id IS NULL";
        $wheres = array_merge($wheres, $globalWheres);
        if (count($wheres) > 0) {
            $queryHosts .= ' WHERE ' . join(' AND ', $wheres);
        }
        
        /* Subquery for services */
        $queryServices = "SELECT i.issue_id, i.host_id, h.name, i.service_id, s.description, FROM_UNIXTIME(i.start_time), se.state as state
            FROM issues i, hosts h, services s, servicestateevents se";
        $wheres = array();
        $wheres[] = "i.host_id = h.host_id";
        $wheres[] = "s.host_id = i.host_id";
        $wheres[] = "s.service_id = i.service_id";
        $wheres[] = "se.host_id = i.host_id";
        $wheres[] = "se.service_id = i.service_id";
        $wheres[] = "i.service_id IS NOT NULL";
        $wheres = array_merge($wheres, $globalWheres);
        if (count($wheres) > 0) {
            $queryServices .= ' WHERE ' . join(' AND ', $wheres);
        }

        $query = $queryHosts . " UNION " . $queryServices;
        $query .= ' ORDER BY i.start_time DESC';
        if (false === is_null($limit)) {
            $query .= ' LIMIT ' . $limit;
        }

        $stmt = $dbconn->prepare($query);
        if (false === is_null($fromTime)) {
            $stmt->bindValue(':start_time', $fromTime, \PDO::PARAM_INT);
        }

        $stmt->execute();

        /* Data */
        $data = array();
        /* Get number events for last time for remove duplicate */
        $lastDateCount = 0;
        $lastDate = null;
        $firstDate = null;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($lastDate != $row['start_time']) {
                $lastDate = $row['start_time'];
                $lastDateCount = 0;
            }
            if (is_null($firstDate)) {
                $firstDate = $row['start_time'];
            }
            $lastDateCount++;
            $data[] = array(
                'instance_id' => $row['instance_id'],
                'host_id' => $row['host_id'],
                'host_name' => $row['host_name'],
                'service_id' => $row['service_description'],
                'service_desc' => $row['service_description'],
                'start_time' => date('Y-m-d H:i:s', $row['start_time']),
                'end_time' => date('Y-m-d H:i:s', $row['end_time']),
                'ticket' => ''
            );
        }
        return  array(
            'data' => $data,
            'lastTimeEntry' => $lastDate,
            'nbEntryForLastTime' => $lastDateCount,
            'recentTime' => $firstDate
        );
    }
}


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
        $router = $di->get('router');
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

        /* Add filters to global */
        /* @todo make better */
        foreach ($filters as $key => $value) {
            $globalWheres[] = $key . ' = "' . $value . '"';
        }

        /* Subquery for hosts */
        $queryHosts = "SELECT i.issue_id, i.host_id, h.name, i.service_id, NULL as description, FROM_UNIXTIME(i.start_time) as start_time, FROM_UNIXTIME(i.end_time) as end_time, he.state as state, h.instance_id
            FROM issues i, hosts h, hoststateevents he";
        $wheres = array();
        $wheres[] = "i.host_id = h.host_id";
        $wheres[] = "i.host_id = he.host_id";
        $wheres[] = "i.service_id IS NULL";
        $wheres[] = "i.end_time IS NULL";
        $wheres[] = "he.end_time IS NULL";
        $wheres = array_merge($wheres, $globalWheres);
        if (count($wheres) > 0) {
            $queryHosts .= ' WHERE ' . join(' AND ', $wheres);
        }
        
        /* Subquery for services */
        $queryServices = "SELECT i.issue_id, i.host_id, h.name, i.service_id, s.description, FROM_UNIXTIME(i.start_time) as start_time, FROM_UNIXTIME(i.end_time) as end_time, se.state as state, h.instance_id
            FROM issues i, hosts h, services s, servicestateevents se";
        $wheres = array();
        $wheres[] = "i.host_id = h.host_id";
        $wheres[] = "s.host_id = i.host_id";
        $wheres[] = "s.service_id = i.service_id";
        $wheres[] = "se.host_id = i.host_id";
        $wheres[] = "se.service_id = i.service_id";
        $wheres[] = "i.service_id IS NOT NULL";
        $wheres[] = "i.end_time IS NULL";
        $wheres[] = "se.end_time IS NULL";
        $wheres = array_merge($wheres, $globalWheres);
        if (count($wheres) > 0) {
            $queryServices .= ' WHERE ' . join(' AND ', $wheres);
        }

        $query = $queryHosts . " UNION " . $queryServices;
        $query .= ' ORDER BY start_time DESC';
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
                'issue_id' => $row['issue_id'],
                'instance_id' => $row['instance_id'],
                'host_id' => $row['host_id'],
                'host_name' => $row['name'],
                'service_id' => $row['service_id'],
                'service_desc' => $row['description'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'url_graph' => $router->getPathFor('/realtime/issueGraph/[i:id]', array('id' => $row['issue_id'])),
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

    /**
     * Get a issue information
     *
     * @param int $issueId The issue id
     * @return array
     */
    public static function getIssue($issueId)
    {
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_storage');

        /* Query for host */
        $queryHosts = "SELECT i.issue_id, i.host_id, h.name, i.service_id, NULL as description, FROM_UNIXTIME(i.start_time) as start_time, FROM_UNIXTIME(i.end_time) as end_time, he.state as state, h.instance_id,
                (SELECT COUNT(iip.child_id) FROM issues_issues_parents iip WHERE iip.parent_id = i.issue_id) as nb_children,
                (SELECT COUNT(iip.parent_id) FROM issues_issues_parents iip WHERE iip.child_id = i.issue_id) as nb_parents
            FROM issues i, hosts h, hoststateevents he";
        $wheres = array();
        $wheres[] = "i.host_id = h.host_id";
        $wheres[] = "i.host_id = he.host_id";
        $wheres[] = "i.service_id IS NULL";
        $wheres[] = "i.end_time IS NULL";
        $wheres[] = "he.end_time IS NULL";
        $wheres[] = "i.issue_id = :issue_id";
        if (count($wheres) > 0) {
            $queryHosts .= ' WHERE ' . join(' AND ', $wheres);
        }

        /* Query for service */
        $queryServices = "SELECT i.issue_id, i.host_id, h.name, i.service_id, s.description, FROM_UNIXTIME(i.start_time) as start_time, FROM_UNIXTIME(i.end_time) as end_time, se.state as state, h.instance_id,
                (SELECT COUNT(iip.child_id) FROM issues_issues_parents iip WHERE iip.parent_id = i.issue_id) as nb_children,
                (SELECT COUNT(iip.parent_id) FROM issues_issues_parents iip WHERE iip.child_id = i.issue_id) as nb_parents
            FROM issues i, hosts h, services s, servicestateevents se";
        $wheres = array();
        $wheres[] = "i.host_id = h.host_id";
        $wheres[] = "s.host_id = i.host_id";
        $wheres[] = "s.service_id = i.service_id";
        $wheres[] = "se.host_id = i.host_id";
        $wheres[] = "se.service_id = i.service_id";
        $wheres[] = "i.service_id IS NOT NULL";
        $wheres[] = "i.end_time IS NULL";
        $wheres[] = "se.end_time IS NULL";
        $wheres[] = "i.issue_id = :issue_id";
        if (count($wheres) > 0) {
            $queryServices .= ' WHERE ' . join(' AND ', $wheres);
        }

        $query = $queryHosts . " UNION " . $queryServices;
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':issue_id', $issueId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $issue = $row;
        $stmt->closeCursor();

        /* Get Parents */
        $query = "SELECT i.issue_id, h.name, NULL as description
            FROM issues i, issues_issues_parents iip, hosts h
            WHERE i.issue_id = iip.parent_id
                AND i.service_id IS NULL
                AND i.host_id = h.host_id
                AND i.end_time IS NULL
                AND iip.child_id = :issue_id
            UNION
            SELECT i.issue_id, h.name, s.description
            FROM issues i, issues_issues_parents iip, hosts h, services s
            WHERE i.issue_id = iip.parent_id
                AND i.service_id IS NOT NULL
                AND i.host_id = h.host_id
                AND i.host_id = s.host_id
                AND i.service_id = s.service_id
                AND i.end_time IS NULL
                AND iip.child_id = :issue_id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':issue_id', $issueId, \PDO::PARAM_INT);
        $stmt->execute();

        $issue['parents'] = array();
        while ($row = $stmt->fetch()) {
            $issue['parents'][] = $row;
        }

        return $issue;
    }

    /**
     * Get the list of children for a issue
     *
     * @param int $issueId The issue ID
     * @return array
     */
    public static function getChildren($issueId)
    {
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_storage');

        /* Query for host */
        $queryHosts = "SELECT i.issue_id, i.host_id, h.name, i.service_id, NULL as description, FROM_UNIXTIME(i.start_time) as start_time, FROM_UNIXTIME(i.end_time) as end_time, h.instance_id, he.state,
                (SELECT COUNT(iip.child_id) FROM issues_issues_parents iip WHERE iip.parent_id = i.issue_id) as nb_children,
                (SELECT COUNT(iip.parent_id) FROM issues_issues_parents iip WHERE iip.child_id = i.issue_id) as nb_parents
            FROM issues_issues_parents iip, issues i, hosts h, hoststateevents he";
        $wheres = array();
        $wheres[] = "i.host_id = h.host_id";
        $wheres[] = "i.service_id IS NULL";
        $wheres[] = "i.host_id = he.host_id";
        $wheres[] = "he.end_time IS NULL";
        $wheres[] = "i.issue_id = iip.child_id";
        $wheres[] = "iip.parent_id = :issue_id";
        if (count($wheres) > 0) {
            $queryHosts .= ' WHERE ' . join(' AND ', $wheres);
        }

        /* Query for service */
        $queryServices = "SELECT i.issue_id, i.host_id, h.name, i.service_id, s.description, FROM_UNIXTIME(i.start_time) as start_time, FROM_UNIXTIME(i.end_time) as end_time, h.instance_id, se.state,
                (SELECT COUNT(iip.child_id) FROM issues_issues_parents iip WHERE iip.parent_id = i.issue_id) as nb_children,
                (SELECT COUNT(iip.parent_id) FROM issues_issues_parents iip WHERE iip.child_id = i.issue_id) as nb_parents
            FROM issues_issues_parents iip, issues i, hosts h, services s, servicestateevents se";
        $wheres = array();
        $wheres[] = "i.host_id = h.host_id";
        $wheres[] = "s.host_id = i.host_id";
        $wheres[] = "s.service_id = i.service_id";
        $wheres[] = "i.service_id IS NOT NULL";
        $wheres[] = "i.host_id = se.host_id";
        $wheres[] = "se.service_id = i.service_id";
        $wheres[] = "se.end_time IS NULL";
        $wheres[] = "i.issue_id = iip.child_id";
        $wheres[] = "iip.parent_id = :issue_id";
        if (count($wheres) > 0) {
            $queryServices .= ' WHERE ' . join(' AND ', $wheres);
        }

        $query = $queryHosts . " UNION " . $queryServices;
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':issue_id', $issueId, \PDO::PARAM_INT);
        $stmt->execute();

        $list = array();
        while ($row = $stmt->fetch()) {
            $list[] = $row;
        }
        return $list;
    }
}

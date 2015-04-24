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

namespace CentreonRealtime\Repository\EventlogsRepository;

use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Di;

/**
 * Factory for Eventlogs
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 */
class Database extends Storage
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
        $listFullsearch = array('output', 'host', 'service');
        $timeField = array('period');
        $types = array(
            0 => array(0, 1),
            2 => array(2, 3),
            4 => array(4),
            5 => array(5),
            6 => array(6, 7),
            8 => array(8, 9),
            10 => array(10, 11)
        );

        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $query = "SELECT ctime, host_id, host_name, instance_name, output, "
            . "service_description, service_id, status, msg_type, `type` "
            . "FROM log_logs";
        $wheres = array();
        if (false === is_null($fromTime)) {
            $clause = 'ctime';
            if ($order == 'DESC') {
                $clause .= ' <= ';
            } else {
                $clause .= ' > ';
            }
            $clause .= ':ctime';
            $wheres[] = $clause;
        }
        $values = array();
        foreach ($filters as $key => $value) {
            if ($value !== trim('')) { 
                if (in_array($key, $listFullsearch)) {
                    if ($key == 'host') {
                        $dbkey = 'host_name';
                    } elseif ($key == 'service') {
                        $dbkey = 'service_description';
                    } else {
                        $dbkey = $key;
                    }
                    $clause = $dbkey . ' LIKE :' . $key;
                    $values[$key] = $value;
                } elseif (in_array($key, $timeField)) {
                    list($timeStart, $timeEnd) = explode(' - ', $value);
                    $clause = 'ctime >= :timeStart AND ctime <= :timeEnd';
                    $values['timeStart'] = strtotime($timeStart);
                    $values['timeEnd'] = strtotime($timeEnd);
                } elseif ($key == 'status') {
                    /*$concatClause = "CONCAT(IF(ISNULL(service_id), 'h_', 's_'), status)";
                    if (is_array($value)) {
                        $clause = "{$concatClause} IN ('" . implode("','", $value) . "')";*/
                    $clause = "status = :status";
                    $values['status'] = $value;
                } elseif ($key == 'eventtype') {
                    if (isset($types[$value])) {
                        if (count($types[$value]) == 2) {
                            $clause = "type = :type1 OR type = :type2";
                            $values['type1'] = $types[$value][0];
                            $values['type2'] = $types[$value][1];
                        } else {
                            $clause = "type = :type";
                            $values['type'] = $types[$value][0];
                        }
                    }
                }/* else {
                    if (is_array($value)) {
                        $clause = $key . ' IN (' . join(',', $value) . ')';
                    } else {
                        $clause = $key . ' = :' . $key;
                        $values[$key] = $value;
                    }
                }*/
                if (isset($clause)) {
                    $wheres[] = $clause;
                }
            }
        }
        if (count($wheres) > 0) {
            $query .= ' WHERE ' . join(' AND ', $wheres);
        }
        $query .= ' ORDER BY ctime DESC';

        if (false === is_null($limit)) {
            $query .= ' LIMIT ' . $limit;
        }
        $stmt = $dbconn->prepare($query);
        if (false === is_null($fromTime)) {
            $stmt->bindValue(':ctime', strtotime($fromTime), \PDO::PARAM_INT);
        }
        foreach ($values as $key => $value) {
            if (in_array($key, $listFullsearch)) {
                $value = '%' . $value . '%';
            }
            $stmt->bindValue(':' . $key, $value); // @TODO Better param type
        }
        $stmt->execute();

        /* Data */
        $data = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = array(
                //'datetime' => Datetime::format($row['ctime']),
                'datetime' => $row['ctime'],
                'host_id' => $row['host_id'],
                'host' => $row['host_name'],
                'service_id' => $row['service_id'],
                'service' => $row['service_description'],
                'instance' => $row['instance_name'],
                'output' => $row['output'],
                'status' => $row['status'],
                'type' => $row['type'],
                'msg_type' => $row['msg_type']
            );
        }
        return $data;
    }
}

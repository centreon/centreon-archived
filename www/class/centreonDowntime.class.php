<?php
/**
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

/**
 * Class for cycle downtime management
 *
 */
class CentreonDowntime
{
    protected $db;
    protected $search = '';
    protected $nbRows = null;
    protected $localCommands;
    protected $localCmdFile = '';
    protected $remoteCommands;
    protected $remoteCmdFile = '';
    protected $varlib;
    protected $periods = null;
    protected $downtimes = null;

    /**
     * Construtor
     *
     * @param CentreonDB $pearDB The connection to database centreon
     * @param string $varlib Centreon dynamic dir
     */
    public function __construct($pearDB, $varlib = null)
    {
        $this->db = $pearDB;
        $this->localCommands = array();
        $this->remoteCommands = array();
        if (!is_null($varlib)) {
            $this->remoteCmdFile = $varlib . '/centcore.cmd';
        }
    }

    public function initPeriods()
    {
        if (!is_null($this->periods)) {
            return $this->periods;
        }

        $this->periods = array();

        $query = 'SELECT dt_id, dtp_start_time, dtp_end_time, '
            . 'dtp_day_of_week, dtp_month_cycle, dtp_day_of_month, '
            . 'dtp_fixed, dtp_duration '
            . 'FROM downtime_period ';

        $res = $this->db->query($query);
        while ($row = $res->fetchRow()) {
            $this->periods[$row['dt_id']][] = $row;
        }
    }

    /**
     * Set the string for filter the display
     *
     * The string search is set for filter
     * In SQL, the the string is "%$search%"
     *
     * @param string $search The string for filter
     */
    public function setSearch($search = '')
    {
        if ('' !== $search) {
            $this->search = " dt_name LIKE '%" . htmlentities($search, ENT_QUOTES, "UTF-8") . "%'";
        }
    }

    /**
     * Get the number of rows for display, with applied search filter
     *
     * @return int The number of rows
     */
    public function getNbRows()
    {
        /* Get the number of rows if getList is call before*/
        if (false === is_null($this->nbRows)) {
            return $this->nbRows;
        }
        /* Get the number of rows with a COUNT(*) */
        $query = "SELECT COUNT(*) FROM downtime" . $this->search;
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return 0;
        }
        $row = $res->fetchRow();
        $res->free();
        return $row["COUNT(*)"];
    }

    /**
     * Get the list of downtime, with applied search filter
     *
     * <code>
     * $return_array =
     *   array(
     *      array(
     *          'dt_id' => int, // The downtime id
     *          'dt_name' => string, // The downtime name
     *          'dt_description' => string, // The downtime description
     *          'dt_activate' => int // 0 Downtime is deactivated, 1 Downtime is activated
     *      ),...
     *   )
     * </code>
     *
     * @param int $num The page number
     * @param int $limit The limit by page for pagination
     * @return array The list of downtime
     */
    public function getList($num, $limit, $type = null)
    {
        if ($type == "h") {
            $query = "SELECT SQL_CALC_FOUND_ROWS downtime.dt_id, dt_name, dt_description, dt_activate FROM downtime
                WHERE (downtime.dt_id IN(SELECT dt_id FROM downtime_host_relation)
                    OR downtime.dt_id IN (SELECT dt_id FROM downtime_hostgroup_relation)) " .
                    ($this->search == '' ? "" : " AND ") . $this->search .
                " ORDER BY dt_name LIMIT " . $num * $limit . ", " . $limit;
        } elseif ($type == "s") {
            $query = "SELECT SQL_CALC_FOUND_ROWS downtime.dt_id, dt_name, dt_description, dt_activate FROM downtime
                WHERE (downtime.dt_id IN (SELECT dt_id FROM downtime_service_relation)
                    OR downtime.dt_id IN (SELECT dt_id FROM downtime_servicegroup_relation)) " .
                    ($this->search == '' ? "" : " AND ") . $this->search .
                " ORDER BY dt_name LIMIT " . $num * $limit . ", " . $limit;
        } else {
            $query = "SELECT SQL_CALC_FOUND_ROWS downtime.dt_id, dt_name, dt_description, dt_activate
                FROM downtime " . ($this->search == '' ? "" : "WHERE ") . $this->search .
                " ORDER BY dt_name LIMIT " . $num * $limit . ", " . $limit;
        }
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return array();
        }
        $list = array();
        while ($row = $res->fetchRow()) {
            $list[] = $row;
        }
        $res->free();
        $this->nbRows = $this->db->numberRows();
        return $list;
    }

    public function getPeriods($id)
    {
        $this->initPeriods();

        $periods = array();
        if (!isset($this->periods[$id])) {
            return $periods;
        }

        foreach ($this->periods[$id] as $period) {
            $days = $period['dtp_day_of_week'];
            /* Make a array if the cycle is all */
            if ($period['dtp_month_cycle'] == 'all') {
                $days = preg_split('/\,/', $days);
            }
            /* Convert HH:mm:ss to HH:mm */
            $start_time = substr($period['dtp_start_time'], 0, strrpos($period['dtp_start_time'], ':'));
            $end_time = substr($period['dtp_end_time'], 0, strrpos($period['dtp_end_time'], ':'));

            $periods[] = array(
                'start_time' => $start_time,
                'end_time' => $end_time,
                'day_of_week' => $days,
                'month_cycle' => $period['dtp_month_cycle'],
                'day_of_month' => preg_split('/\,/', $period['dtp_day_of_month']),
                'fixed' => $period['dtp_fixed'],
                'duration' => $period['dtp_duration']
            );
        }

        return $periods;
    }

    /**
     * Get informations for a downtime
     *
     * <code>
     * $return_array =
     * array(
     *      'name' => string, // The downtime name
     *      'description' => string, // The downtime description
     *      'activate' => int // 0 Downtime is deactivated, 1 Downtime is activated
     * )
     * </code
     *
     * @param int $id The downtime id
     * @return array The informations for a downtime
     */
    public function getInfos($id)
    {
        $query = "SELECT dt_name, dt_description, dt_activate FROM downtime WHERE dt_id=" . $id;
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return array('name' => '', 'description' => '', 'activate' => '');
        }
        $row = $res->fetchRow();
        return array(
            'name' => $row['dt_name'],
            'description' => $row['dt_description'],
            'activate' => $row['dt_activate'],
        );
    }

    /**
     * Get the list of relations for a downtime
     *
     * <code>
     * $return_array =
     *  array(
     *      'host' => array, // The list of host id
     *      'hostgrp' => array, // The list of hostgroup id
     *      'svc' => array, // The list of service id
     *      'svcgrp' => array, // The list of servicegroup id
     *  )
     * </code>
     *
     * @param int $id The downtime id
     * @return array The list of relations
     */
    public function getRelations($id)
    {
        $list = array(
            "host" => array(),
            "hostgrp" => array(),
            "svc" => array(),
            "svcgrp" => array()
        );
        foreach (array_keys($list) as $type) {
            switch ($type) {
                case 'host':
                    $query = "SELECT host_host_id as obj_id FROM downtime_host_relation WHERE dt_id = ";
                    break;
                case 'hostgrp':
                    $query = "SELECT hg_hg_id as obj_id FROM downtime_hostgroup_relation WHERE dt_id = ";
                    break;
                case 'svc':
                    $query = "SELECT CONCAT(host_host_id, CONCAT('-', service_service_id)) as obj_id
                        FROM downtime_service_relation WHERE dt_id = ";
                    break;
                case 'svcgrp':
                    $query = "SELECT sg_sg_id as obj_id FROM downtime_servicegroup_relation WHERE dt_id = ";
                    break;
            }
            $res = $this->db->query($query . $id);
            while ($row = $res->fetchRow()) {
                $list[$type][] = $row['obj_id'];
            }
            $res->free();
        }

        return $list;
    }

    public function getHostDowntimes()
    {
        $hostDowntimes = array();

        $query = 'SELECT dt.dt_id, dt.dt_activate, dtp.dtp_start_time, dtp.dtp_end_time, dtp.dtp_day_of_week, '
            . 'dtp.dtp_month_cycle, dtp.dtp_day_of_month, dtp.dtp_fixed, dtp.dtp_duration, '
            . 'h.host_id, h.host_name, NULL as service_id, NULL as service_description '
            . 'FROM downtime_period dtp, downtime dt, '
            . 'downtime_host_relation dtr, host h '
            . 'WHERE dtp.dt_id = dtr.dt_id AND dtp.dt_id = dt.dt_id '
            . 'AND dtr.host_host_id = h.host_id' ;

        $res = $this->db->query($query);
        if (false === PEAR::isError($res)) {
            while ($row = $res->fetchRow()) {
                $hostDowntimes[] = $row;
            }
        }

        return $hostDowntimes;
    }

    public function getServiceDowntimes()
    {
        $serviceDowntimes = array();

        $query = 'SELECT dt.dt_id, dt.dt_activate, dtp.dtp_start_time, dtp.dtp_end_time, dtp.dtp_day_of_week, '
            . 'dtp.dtp_month_cycle, dtp.dtp_day_of_month, dtp.dtp_fixed, dtp.dtp_duration, '
            . 'h.host_id, h.host_name, s.service_id, s.service_description '
            . 'FROM downtime_period dtp, downtime dt, downtime_service_relation dtr, '
            . 'service s, host h, host_service_relation hsr '
            . 'WHERE dtp.dt_id = dtr.dt_id '
            . 'AND dtp.dt_id = dt.dt_id '
            . 'AND dtr.service_service_id = s.service_id '
            . 'AND hsr.service_service_id = s.service_id '
            . 'AND hsr.host_host_id = h.host_id '
            . 'AND h.host_id = dtr.host_host_id '
            . 'UNION '
            . 'SELECT dt.dt_id, dt.dt_activate, dtp.dtp_start_time, dtp.dtp_end_time, '
            . 'dtp.dtp_day_of_week, dtp.dtp_month_cycle, dtp.dtp_day_of_month, dtp.dtp_fixed, '
            . 'dtp.dtp_duration, s.service_description as obj_name, '
            . 'dtr.service_service_id as obj_id, h.host_name as host_name, h.host_id '
            . 'FROM downtime_period dtp, downtime dt, downtime_service_relation dtr, service s, '
            . 'host h, hostgroup_relation hgr, host_service_relation hsr '
            . 'WHERE '
            . 'dtp.dt_id = dtr.dt_id '
            . 'AND dtp.dt_id = dt.dt_id '
            . 'AND dtr.host_host_id = h.host_id '
            . 'AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id '
            . 'AND hgr.host_host_id = h.host_id '
            . 'AND s.service_id = hsr.service_service_id '
            . 'AND dtr.service_service_id = s.service_id';

        $res = $this->db->query($query);
        if (false === PEAR::isError($res)) {
            while ($row = $res->fetchRow()) {
                $serviceDowntimes[] = $row;
            }
        }

        return $serviceDowntimes;
    }

    public function getHostgroupDowntimes()
    {
        $hostgroupDowntimes = array();

        $query = 'SELECT dt.dt_id, dt.dt_activate, dtp.dtp_start_time, dtp.dtp_end_time, dtp.dtp_day_of_week, '
            . 'dtp.dtp_month_cycle, dtp.dtp_day_of_month, dtp.dtp_fixed, dtp.dtp_duration, '
            . 'h.host_id, h.host_name, NULL as service_id, NULL as service_description '
            . 'FROM downtime_period dtp, downtime dt, '
            . 'downtime_hostgroup_relation dhr, servicegroup sg, '
            . 'host h, hostgroup_relation hgr '
            . 'WHERE dtp.dt_id = dhr.dt_id '
            . 'AND dtp.dt_id = dt.dt_id '
            . 'AND dhr.hg_hg_id = hgr.hostgroup_hg_id '
            . 'AND hgr.host_host_id = h.host_id ';

        $res = $this->db->query($query);
        if (false === PEAR::isError($res)) {
            while ($row = $res->fetchRow()) {
                $hostgroupDowntimes[] = $row;
            }
        }

        return $hostgroupDowntimes;
    }

    public function getServicegroupDowntimes()
    {
        $servicegroupDowntimes = array();

        $query = 'SELECT dt.dt_id, dt.dt_activate, dtp.dtp_start_time, dtp.dtp_end_time, dtp.dtp_day_of_week, '
            . 'dtp.dtp_month_cycle, dtp.dtp_day_of_month, dtp.dtp_fixed, dtp.dtp_duration, '
            . 'h.host_id, h.host_name, s.service_id, s.service_description '
            . 'FROM downtime_period dtp, downtime dt, '
            . 'downtime_servicegroup_relation dtr, servicegroup_relation sgr, '
            . 'service s, host h '
            . 'WHERE dtp.dt_id = dtr.dt_id '
            . 'AND dtp.dt_id = dt.dt_id '
            . 'AND dtr.sg_sg_id = sgr.servicegroup_sg_id '
            . 'AND sgr.host_host_id = h.host_id '
            . 'AND sgr.service_service_id = s.service_id '
            . 'UNION DISTINCT '
            . 'SELECT dt.dt_id, dt.dt_activate, dtp.dtp_start_time, dtp.dtp_end_time, dtp.dtp_day_of_week, '
            . 'dtp.dtp_month_cycle, dtp.dtp_day_of_month, dtp.dtp_fixed, dtp.dtp_duration, '
            . 'h.host_id, h.host_name, s.service_id, s.service_description '
            . 'FROM downtime_period dtp, downtime dt, '
            . 'downtime_servicegroup_relation dtr, '
            . 'host_service_relation hsr, hostgroup_relation hgr, '
            . 'service s, host h, servicegroup_relation sgr '
            . 'WHERE dtp.dt_id = dtr.dt_id '
            . 'AND dtp.dt_id = dt.dt_id '
            . 'AND dtr.sg_sg_id = sgr.servicegroup_sg_id '
            . 'AND sgr.hostgroup_hg_id IS NOT NULL '
            . 'AND sgr.hostgroup_hg_id = hsr.hostgroup_hg_id '
            . 'AND hsr.service_service_id = s.service_id '
            . 'AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id '
            . 'AND hgr.host_host_id = h.host_id ';

        $res = $this->db->query($query);
        if (false === PEAR::isError($res)) {
            while ($row = $res->fetchRow()) {
                $servicegroupDowntimes[] = $row;
            }
        }

        return $servicegroupDowntimes;
    }

    /**
     * Get the list of all downtimes
     *
     * @return array All downtimes
     */
    public function getDowntime()
    {
        if (!is_null($this->downtimes)) {
            return $this->downtimes;
        }

        $downtimes = array_merge(
            $this->getHostDowntimes(),
            $this->getServiceDowntimes(),
            $this->getHostgroupDowntimes(),
            $this->getServicegroupDowntimes()
        );

        /* Remove duplicate downtimes */
        $downtimes = array_intersect_key($downtimes, array_unique(array_map('serialize', $downtimes)));
        sort($downtimes);

        $this->downtimes = $downtimes;

        return $this->downtimes;
    }

    /**
     * The duplicate one or many downtime, with periods
     *
     * @param array $ids The list of downtime id to replicate
     * @param array $nb The list of number of duplicate by downtime id
     */
    public function duplicate($ids, $nb)
    {
        if (false === is_array($ids)) {
            $ids = array($ids);
        } else {
            $ids = $this->normalizeArray($ids);
        }
        foreach ($ids as $id) {
            if (isset($nb[$id])) {
                $query = "SELECT dt_name, dt_description, dt_activate FROM downtime WHERE dt_id = " . $id;
                $res = $this->db->query($query);
                if (PEAR::isError($res)) {
                    return;
                }
                $row = $res->fetchRow();
                $dt_name = $row['dt_name'];
                $dt_desc = $row['dt_description'];
                $dt_activate = $row['dt_activate'];
                $index = $i = 1;
                while ($i <= $nb[$id]) {
                    /* Find the index for duplicate name */
                    $query = "SELECT COUNT(*) as nb FROM downtime WHERE dt_name = '" . $dt_name . "_" . $index . "'";
                    $res = $this->db->query($query);
                    $row = $res->fetchRow();
                    if ($row["nb"] == 0) {
                        /* Insert the new downtime */
                        $rq = "INSERT INTO downtime (dt_name, dt_description, dt_activate)
								VALUES ('" . $dt_name . "_" . $index . "', '" . $dt_desc . "', '" . $dt_activate . "')";
                        $res = $this->db->query($rq);
                        if (PEAR::isError($res)) {
                            return;
                        } else {
                            /* Get the new downtime id */
                            $query = "SELECT dt_id FROM downtime WHERE dt_name = '" . $dt_name . "_" . $index . "'";
                            $res = $this->db->query($query);
                            $row = $res->fetchRow();
                            $res->free();
                            $id_new = $row['dt_id'];
                            /* Copy the periods for new downtime */
                            $query = "INSERT INTO downtime_period (dt_id, dtp_start_time, dtp_end_time,
                                dtp_day_of_week, dtp_month_cycle, dtp_day_of_month, dtp_fixed, dtp_duration,
                                dtp_activate)
								SELECT " . $id_new . ", dtp_start_time, dtp_end_time, dtp_day_of_week, dtp_month_cycle,
                                dtp_day_of_month, dtp_fixed, dtp_duration, dtp_activate
								FROM downtime_period WHERE dt_id = " . $id;
                            $res = $this->db->query($query);

                            /*
        					 * Duplicate Relations for hosts
        					 */
                            $this->db->query("INSERT INTO downtime_host_relation (dt_id, host_host_id)
                                SELECT $id_new, host_host_id FROM downtime_host_relation WHERE dt_id = '$id'");

                            /*
        					 * Duplicate Relations for hostgroups
        					 */
                            $this->db->query("INSERT INTO downtime_hostgroup_relation (dt_id, hg_hg_id)
                                SELECT $id_new, hg_hg_id FROM downtime_hostgroup_relation WHERE dt_id = '$id'");

                            /*
        					 * Duplicate Relations for services
        					 */
                            $this->db->query("INSERT INTO downtime_service_relation
                                (dt_id, host_host_id, service_service_id)
                                SELECT $id_new, host_host_id, service_service_id
                                    FROM downtime_service_relation WHERE dt_id = '$id'");

                            /*
        					 * Duplicate Relations for servicegroups
        					 */
                            $this->db->query("INSERT INTO downtime_servicegroup_relation (dt_id, sg_sg_id)
                                SELECT $id_new, sg_sg_id FROM downtime_servicegroup_relation WHERE dt_id = '$id'");

                            $i++;
                        }
                    }
                    $index++;
                }
            }
        }
    }

    /**
     * Add a downtime
     *
     * @param string $name The downtime name
     * @param string $desc The downtime description
     * @param int $activate If the downtime is activated (0 Downtime is deactivated, 1 Downtime is activated)
     * @return int The id of downtime or false if in error
     */
    public function add($name, $desc, $activate)
    {
        if ($desc == "") {
            $desc = $name;
        }

        $query = "INSERT INTO downtime (dt_name, dt_description, dt_activate)
            VALUES ('" . CentreonDB::escape($name) . "', '" . CentreonDB::escape($desc) . "', '" . $activate . "')";
        if (PEAR::isError($this->db->query($query))) {
            return false;
        }
        $query = "SELECT dt_id FROM downtime WHERE dt_name = '" . CentreonDB::escape($name) . "'";
        $res = $this->db->query($query);
        if (PEAR::isError($res) || $res->numRows() == 0) {
            return false;
        }
        $row = $res->fetchRow();
        return $row['dt_id'];
    }

    /**
     * Modify a downtime
     *
     * @param $id The downtime id
     * @param string $name The downtime name
     * @param string $desc The downtime description
     * @param int $activate If the downtime is activated (0 Downtime is deactivated, 1 Downtime is activated)
     */
    public function modify($id, $name, $desc, $activate)
    {

        if ($desc == "") {
            $desc = $name;
        }

        $query = "UPDATE downtime SET
			dt_name = '" . CentreonDB::escape($name) . "',
			dt_description = '" . CentreonDB::escape($desc) . "',
			dt_activate = '" . $activate . "'
			WHERE dt_id = " . $id;
        $this->db->query($query);
    }

    /**
     * Add a period to a downtime
     *
     * <code>
     * $infos =
     *  array(
     *      'start_period' => string, // The start time of the period (HH:mm)
     *      'end_period' => string, // The end time of the period (HH:mm)
     *      'days' => array, // The days in week, it is a array with the day number in the week (1 to 7)
     *                       // if month_cycle is all, first or last
     *                       // The days of month if month_cycle is none
     *      'month_cycle' => string, // The cycle method (all: all in month, first: first in month, last: last in month
     *                               // , none: only the day of the month)
     *      'fixed' => int, // If the downtime is fixed (0: flexible, 1: fixed)
     *      'duration' => int, // If the downtime is fexible, the duration of the downtime
     *  )
     * </code>
     *
     * @param $id The downtime id
     * @param array $infos The information for a downtime period
     */
    public function addPeriod($id, $infos)
    {
        if (trim($infos['duration']) == '') {
            $infos['duration'] = 'NULL';
        } else {
            if (trim($infos['scale']) == '') {
                $scale = 's';
            } else {
                $scale = trim($infos['scale']);
            }
            
            switch ($scale) {
                default:
                case 's':
                    $infos['duration'] = $infos['duration'];
                    break;
                
                case 'm':
                    $infos['duration'] = $infos['duration'] * 60;
                    break;
                
                case 'h':
                    $infos['duration'] = $infos['duration'] * 60 * 60;
                    break;
                
                case 'd':
                    $infos['duration'] = $infos['duration'] * 60 * 60 * 24;
                    break;
            }
            $infos['duration'] = "'" . $infos['duration'] . "'";
        }
        if (!isset($infos['days'])) {
            $infos['days'] = array();
        }
        
        
        switch ($infos['period_type']) {
            case 'weekly_basis':
                $query = "INSERT INTO downtime_period (dt_id, dtp_day_of_week, dtp_month_cycle, dtp_start_time,
                    dtp_end_time, dtp_fixed, dtp_duration)
					VALUES (" . $id . ", '" . join(',', $infos['days']) . "', 'all', '" .
                        $infos['start_period'] . "', '" . $infos['end_period'] . "', '" . $infos['fixed'] . "', " .
                        $infos['duration'] . ")";
                break;
            case 'monthly_basis':
                $query = "INSERT INTO downtime_period (dt_id, dtp_day_of_month, dtp_month_cycle, dtp_start_time,
                    dtp_end_time, dtp_fixed, dtp_duration)
					VALUES (" . $id . ", '" . join(',', $infos['days']) . "', 'none', '" .
                        $infos['start_period'] . "', '" . $infos['end_period'] . "', '" . $infos['fixed'] . "', " .
                        $infos['duration'] . ")";
                break;
            case 'specific_date':
                $query = "INSERT INTO downtime_period (dt_id, dtp_day_of_week, dtp_month_cycle, dtp_start_time,
                    dtp_end_time, dtp_fixed, dtp_duration)
					VALUES (" . $id . ", '" . $infos['days'] . "', '" . $infos['month_cycle'] . "', '" .
                        $infos['start_period'] . "', '" . $infos['end_period'] . "', '" . $infos['fixed'] . "', " .
                        $infos['duration'] . ")";
                break;
        }
        $res = $this->db->query($query);
    }

    /**
     * Delete all periods for a downtime
     *
     * @param int $id The downtime id
     */
    public function deletePeriods($id)
    {
        $query = "DELETE FROM downtime_period WHERE dt_id = " .$id;
        $this->db->query($query);
    }

    /**
     * Add relations for downtime
     *
     * @param int $id The downtime id
     * @param array $obj_ids The list of object id
     * @param string $obj_type The object type (host, hostgrp, service, servicegrp)
     */
    public function addRelations($id, $obj_ids, $obj_type)
    {
        switch ($obj_type) {
            case 'host':
                $query = "INSERT INTO downtime_host_relation (dt_id, host_host_id) VALUES (" . $id  . ", %obj_id%)";
                break;
            case 'hostgrp':
                $query = "INSERT INTO downtime_hostgroup_relation (dt_id, hg_hg_id) VALUES (" . $id  . ", %obj_id%)";
                break;
            case 'svc':
                $query = "INSERT INTO downtime_service_relation (dt_id, host_host_id, service_service_id)
                    VALUES (" . $id  . ", %obj_id%)";
                break;
            case 'svcgrp':
                $query = "INSERT INTO downtime_servicegroup_relation (dt_id, sg_sg_id) VALUES (" . $id  . ", %obj_id%)";
                break;
        }
        foreach ($obj_ids as $obj_id) {
            if ($obj_type == 'svc') {
                $obj_id = str_replace('-', ', ', $obj_id);
            }
            $queryInsert = str_replace('%obj_id%', $obj_id, $query);
            $this->db->query($queryInsert);
        }
    }

    /**
     * Delete all relations for a downtime
     *
     * @param int $id The downtime id
     */
    public function deteleRelations($id)
    {
        $query = "DELETE FROM downtime_host_relation WHERE dt_id = " .$id;
        $this->db->query($query);
        $query = "DELETE FROM downtime_hostgroup_relation WHERE dt_id = " .$id;
        $this->db->query($query);
        $query = "DELETE FROM downtime_service_relation WHERE dt_id = " .$id;
        $this->db->query($query);
        $query = "DELETE FROM downtime_servicegroup_relation WHERE dt_id = " .$id;
        $this->db->query($query);
    }

    /**
     * Activate a downtime
     *
     * @param int $id The downtime id
     * @see CentreonDowntime::setActivate
     */
    public function enable($id)
    {
        $this->setActivate($id, '1');
    }

    /**
     * Activate downtimes
     *
     * @param array $id The list of downtimes id
     * @see CentreonDowntime::setActivate
     */
    public function multiEnable($ids)
    {
        $this->setActivate($ids, '1');
    }

    /**
     * Deactivate a downtime
     *
     * @param int $id The downtime id
     * @see CentreonDowntime::setActivate
     */
    public function disable($id)
    {
        $this->setActivate($id, '0');
    }

    /**
     * Deactivate downtimes
     *
     * @param array $id The list of downtimes id
     * @see CentreonDowntime::setActivate
     */
    public function multiDisable($ids)
    {
        $this->setActivate($ids, '0');
    }

    /**
     * Delete a downtime
     *
     * @param int $id The downtime id
     * @see CentreonDowntime::multiDelete
     */
    public function delete($id)
    {
        $this->multiDelete($id);
    }

    /**
     * Delete downtimes
     *
     * @param array $id The list of downtimes id
     */
    public function multiDelete($ids)
    {
        if (false === is_array($ids)) {
            $ids = array($ids);
        } else {
            $ids = $this->normalizeArray($ids);
        }
        if (0 !== count($ids)) {
            $query = "DELETE FROM downtime WHERE dt_id IN (" . join(', ', $ids) . ")";
            $this->db->query($query);
        }
    }

    /**
     * Activate or deactivate a downtime
     *
     * @param array $ids The list of downtimes id
     * @param int $status 0 Downtime is deactivated, 1 Downtime is activated
     */
    private function setActivate($ids, $status)
    {
        if (false === is_array($ids)) {
            $ids = array($ids);
        } else {
            $ids = $this->normalizeArray($ids);
        }
        if (0 !== count($ids)) {
            $query = "UPDATE downtime SET dt_activate = '" . $status . "' WHERE dt_id IN (" . join(', ', $ids) . ")";
            $this->db->query($query);
        }
    }

    /**
     * Normalize a array from post from $key => $value to list of $key
     *
     * @param array $arr The array
     * @return array
     */
    private function normalizeArray($arr)
    {
        $list = array();
        foreach ($arr as $key => $value) {
            $list[] = $key;
        }
        return $list;
    }
    
    /**
     *
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'downtime';
        $parameters['currentObject']['id'] = 'dt_id';
        $parameters['currentObject']['name'] = 'dt_name';
        $parameters['currentObject']['comparator'] = 'dt_id';

        switch ($field) {
            case 'host_relation':
                $parameters['type'] = 'relation';
                $parameters['object'] = 'centreonHost';
                $parameters['externalObject']['table'] = 'host';
                $parameters['externalObject']['id'] = 'host_id';
                $parameters['externalObject']['name'] = 'host_name';
                $parameters['externalObject']['comparator'] = 'host_id';
                $parameters['relationObject']['table'] = 'downtime_host_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['comparator'] = 'dt_id';
                break;
            case 'hostgroup_relation':
                $parameters['type'] = 'relation';
                $parameters['object'] = 'centreonHostgroups';
                $parameters['externalObject']['table'] = 'hostgroup';
                $parameters['externalObject']['id'] = 'hg_id';
                $parameters['externalObject']['name'] = 'hg_name';
                $parameters['externalObject']['comparator'] = 'hg_id';
                $parameters['relationObject']['table'] = 'downtime_hostgroup_relation';
                $parameters['relationObject']['field'] = 'hg_hg_id';
                $parameters['relationObject']['comparator'] = 'dt_id';
                break;
            case 'svc_relation':
                $parameters['type'] = 'relation';
                $parameters['object'] = 'centreonService';
                $parameters['externalObject']['table'] = 'service';
                $parameters['externalObject']['id'] = 'service_id';
                $parameters['externalObject']['name'] = 'service_description';
                $parameters['externalObject']['comparator'] = 'service_id';
                $parameters['relationObject']['table'] = 'downtime_service_relation';
                $parameters['relationObject']['field'] = 'service_service_id';
                $parameters['relationObject']['comparator'] = 'dt_id';
                break;
            case 'svcgroup_relation':
                $parameters['type'] = 'relation';
                $parameters['object'] = 'centreonServicegroups';
                $parameters['externalObject']['table'] = 'servicegroup';
                $parameters['externalObject']['id'] = 'sg_id';
                $parameters['externalObject']['name'] = 'sg_name';
                $parameters['externalObject']['comparator'] = 'sg_id';
                $parameters['relationObject']['table'] = 'downtime_servicegroup_relation';
                $parameters['relationObject']['field'] = 'sg_sg_id';
                $parameters['relationObject']['comparator'] = 'dt_id';
                break;
        }
        
        return $parameters;
    }
}

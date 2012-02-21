<?php
/**
 * Copyright 2005-2011 MERETHIS
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


	/**
	 * Construtor
	 *
	 * @param CentreonDB $pearDB The connection to database centreon
	 */
	public function __construct($pearDB)
	{
		$this->db = $pearDB;
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
	 *   	array(
	 *   		'dt_id' => int, // The downtime id
	 *   		'dt_name' => string, // The downtime name
	 *   		'dt_description' => string, // The downtime description
	 *   		'dt_activate' => int // 0 Downtime is deactivated, 1 Downtime is activated
	 *  	),...
	 * 	 )
	 * </code>
	 *
	 * @param int $num The page number
	 * @param int $limit The limit by page for pagination
	 * @return array The list of downtime
	 */
	public function getList($num, $limit, $type = NULL)
	{
		if ($type == "h") {
			$query = "SELECT SQL_CALC_FOUND_ROWS downtime.dt_id, dt_name, dt_description, dt_activate FROM downtime WHERE downtime.dt_id IN(SELECT dt_id FROM downtime_host_relation) OR downtime.dt_id IN (SELECT dt_id FROM downtime_hostgroup_relation) " . ($this->search == '' ? "" : " AND ") . $this->search . " ORDER BY dt_name LIMIT " . $num * $limit . ", " . $limit;
		} else if ($type == "s") {
			$query = "SELECT SQL_CALC_FOUND_ROWS downtime.dt_id, dt_name, dt_description, dt_activate FROM downtime WHERE downtime.dt_id IN (SELECT dt_id FROM downtime_service_relation) OR downtime.dt_id IN (SELECT dt_id FROM downtime_servicegroup_relation) " . ($this->search == '' ? "" : " AND ") . $this->search . " ORDER BY dt_name LIMIT " . $num * $limit . ", " . $limit;
		} else {
			$query = "SELECT SQL_CALC_FOUND_ROWS downtime.dt_id, dt_name, dt_description, dt_activate FROM downtime " . ($this->search == '' ? "" : " WHERE ") . $this->search . " ORDER BY dt_name LIMIT " . $num * $limit . ", " . $limit;
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

	/**
	 * Get informations for a downtime
	 *
	 * <code>
	 * $return_array =
	 * array(
	 *   	'name' => string, // The downtime name
	 *   	'description' => string, // The downtime description
	 *   	'activate' => int // 0 Downtime is deactivated, 1 Downtime is activated
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
	 * Get the list of periods for a downtime
	 *
	 * <code>
	 * $return_array =
	 *   array(
	 *   	array(
	 *   		'start_time' => string, // The start time of the period (HH:mm)
	 *   		'end_time' => string, // The end time of the period (HH:mm)
	 *   		'day_of_week' => array, // The days in week, it is a array with the day number in the week (1 to 7)
	 *   		'month_cycle' => string, // The cycle method (all: all in month, first: first in month, last: last in month, none: only the day of the month)
	 *   		'day_of_month' => array, // The days of month
	 *   		'fixed' => int, // If the downtime is fixed (0: flexible, 1: fixed)
	 *   		'duration' => int // If the downtime is fexible, the duration of the downtime
	 *  	),...
	 * 	 )
	 * </code>
	 *
	 * @param int $id The downtime id
	 * @return array The list of periods
	 */
	public function getPeriods($id)
	{
		$query = "SELECT dtp_start_time, dtp_end_time, dtp_day_of_week, dtp_month_cycle, dtp_day_of_month, dtp_fixed, dtp_duration
			FROM downtime_period
			WHERE dt_id = " . $id;
		$res = $this->db->query($query);
		if (PEAR::isError($res)) {
			return array();
		}
		$list = array();
		while ($row = $res->fetchRow()) {
			$days = $row['dtp_day_of_week'];
			/* Make a array if the cycle is all */
			if ($row['dtp_month_cycle'] == 'all') {
				$days = preg_split('/\,/', $days);
			}
			$start_time = substr($row['dtp_start_time'], 0, strrpos($row['dtp_start_time'], ':'));
			$end_time = substr($row['dtp_end_time'], 0, strrpos($row['dtp_end_time'], ':'));
			$list[] = array(
				'start_time' => $start_time,
				'end_time' => $end_time,
				'day_of_week' => $days,
				'month_cycle' => $row['dtp_month_cycle'],
				'day_of_month' => preg_split('/\,/', $row['dtp_day_of_month']),
				'fixed' => $row['dtp_fixed'],
				'duration' => $row['dtp_duration']
			);
		}
		return $list;
	}

	/**
	 * Get the list of relations for a downtime
	 *
	 * <code>
	 * $return_array =
	 * 	array(
	 * 		'host' => array, // The list of host id
	 * 		'hostgrp' => array, // The list of hostgroup id
	 * 		'svc' => array, // The list of service id
	 * 		'svcgrp' => array, // The list of servicegroup id
	 *	)
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
					$query = "SELECT CONCAT(host_host_id, CONCAT('-', service_service_id)) as obj_id FROM downtime_service_relation WHERE dt_id = ";
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

	/**
	 * Get the list of all downtimes
	 *
	 * <code>
	 * $return_array =
	 * 	array(
	 * 		'host' => array, // The list of downtime by host type (see the array after)
	 * 		'hostgrp' => array, // The list of downtime by hostgroup type
	 * 		'svc' => array, // The list of downtime by service type
	 * 		'svcgrp' => array, // The list of downtime by servicegroup type
	 * 	)
	 *
	 *	downtime_array(
	 *		'dt_id'' => int, // The downtime id
	 *		'dt_activate' => int, // 0 Downtime is deactivated, 1 Downtime is activated
	 *		'dtp_start_time' => string, // The start time of the period (HH:mm)
	 *   	'dtp_end_time' => string, // The end time of the period (HH:mm)
	 *   	'dtp_day_of_week' => array, // The days in week, it is a array with the day number in the week (1 to 7)
	 *   	'dtp_month_cycle' => string, // The cycle method (all: all in month, first: first in month, last: last in month, none: only the day of the month)
	 *   	'dtp_day_of_month' => array, // The days of month
	 *   	'dtp_fixed' => int, // If the downtime is fixed (0: flexible, 1: fixed)
	 *   	'dtp_duration' => int, // If the downtime is fexible, the duration of the downtime
	 *   	'obj_name' => string, // The name of object (host_name, hg_name, service_description or sg_name)
	 *   	'obj_id' => int, // The object id
	 *   	'host_name' => string // The hostname for a service only for type service
	 *	)
	 * </code>
	 *
	 * @return array All downtimes
	 */
	public function getDowntime()
	{
		$list = array('host' => array(), 'hostgrp' => array(), 'svc' => array(), 'svcgrp' => array());
		foreach (array_keys($list) as $type) {
			switch ($type) {
				case 'host':
					$name = ', h.host_name as obj_name, dtr.host_host_id as obj_id';
					$table = ', downtime_host_relation dtr, host h';
					$clause = ' AND dtr.host_host_id = h.host_id';
					break;
				case 'hostgrp':
					$name = ', hg.hg_name as obj_name, dtr.hg_hg_id as obj_id';
					$table = ', downtime_hostgroup_relation dtr, hostgroup hg';
					$clause = ' AND dtr.hg_hg_id = hg.hg_id';
					break;
				case 'svc':
					$name = ', s.service_description as obj_name, dtr.service_service_id as obj_id, h.host_name as host_name';
					$table = ', downtime_service_relation dtr, service s, host h, host_service_relation hsr';
					$clause = ' AND dtr.service_service_id = s.service_id AND hsr.service_service_id = s.service_id AND hsr.host_host_id = h.host_id AND h.host_id = dtr.host_host_id';
					break;
				case 'svcgrp':
					$name = ', sg.sg_name as obj_name, dtr.sg_sg_id as obj_id';
					$table = ', downtime_servicegroup_relation dtr, servicegroup sg';
					$clause = ' AND dtr.sg_sg_id = sg.sg_id';
					break;
				default:
					$name = '';
					$table = '';
					$clause = '';
			}
			$query = "SELECT dt.dt_id, dt.dt_activate, dtp.dtp_start_time, dtp.dtp_end_time, dtp.dtp_day_of_week, dtp.dtp_month_cycle, dtp.dtp_day_of_month, dtp.dtp_fixed, dtp.dtp_duration" . $name . "
				FROM downtime_period dtp, downtime dt" . $table . "
				WHERE  dtp.dt_id = dtr.dt_id AND dtp.dt_id = dt.dt_id" . $clause;
			$res = $this->db->query($query);
			if (false === PEAR::isError($res)) {
				while ($row = $res->fetchRow()) {
					$list[$type][] = $row;
				}
			}
		}
		return $list;
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
							$query = "INSERT INTO downtime_period (dt_id, dtp_start_time, dtp_end_time, dtp_day_of_week, dtp_month_cycle, dtp_day_of_month, dtp_fixed, dtp_duration, dtp_activate)
								SELECT " . $id_new . ", dtp_start_time, dtp_end_time, dtp_day_of_week, dtp_month_cycle, dtp_day_of_month, dtp_fixed, dtp_duration, dtp_activate
								FROM downtime_period WHERE dt_id = " . $id;
							$res = $this->db->query($query);

							/*
        					 * Duplicate Relations for hosts
        					 */
                            $this->db->query("INSERT INTO downtime_host_relation (dt_id, host_host_id) SELECT $id_new, host_host_id FROM downtime_host_relation WHERE dt_id = '$id'");

                            /*
        					 * Duplicate Relations for hostgroups
        					 */
                            $this->db->query("INSERT INTO downtime_hostgroup_relation (dt_id, hg_hg_id) SELECT $id_new, hg_hg_id FROM downtime_hostgroup_relation WHERE dt_id = '$id'");

                            /*
        					 * Duplicate Relations for services
        					 */
                            $this->db->query("INSERT INTO downtime_service_relation (dt_id, host_host_id, service_service_id) SELECT $id_new, host_host_id, service_service_id FROM downtime_service_relation WHERE dt_id = '$id'");

                            /*
        					 * Duplicate Relations for servicegroups
        					 */
                            $this->db->query("INSERT INTO downtime_servicegroup_relation (dt_id, sg_sg_id) SELECT $id_new, sg_sg_id FROM downtime_servicegroup_relation WHERE dt_id = '$id'");

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

		$query = "INSERT INTO downtime (dt_name, dt_description, dt_activate) VALUES ('" . CentreonDB::escape($name) . "', '" . CentreonDB::escape($desc) . "', '" . $activate . "')";
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
	public function modify($id, $name, $desc, $activate) {

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
	 * 	array(
	 * 		'start_period' => string, // The start time of the period (HH:mm)
	 *   	'end_period' => string, // The end time of the period (HH:mm)
	 *   	'days' => array, // The days in week, it is a array with the day number in the week (1 to 7) if month_cycle is all, first or last
	 *   				     // The days of month if month_cycle is none
	 *   	'month_cycle' => string, // The cycle method (all: all in month, first: first in month, last: last in month, none: only the day of the month)
	 *   	'fixed' => int, // If the downtime is fixed (0: flexible, 1: fixed)
	 *   	'duration' => int, // If the downtime is fexible, the duration of the downtime
	 * 	)
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
			$infos['duration'] = "'" . $infos['duration'] . "'";
		}
		if (!isset($infos['days'])) {
			$infos['days'] = array();
		}
		switch ($infos['period_type']) {
			case 'weekly_basis':
				$query = "INSERT INTO downtime_period (dt_id, dtp_day_of_week, dtp_month_cycle, dtp_start_time, dtp_end_time, dtp_fixed, dtp_duration)
					VALUES (" . $id . ", '" . join(',', $infos['days']) . "', 'all', '" . $infos['start_period'] . "', '" . $infos['end_period'] . "', '" . $infos['fixed'] . "', " . $infos['duration'] . ")";
				break;
			case 'monthly_basis':
				$query = "INSERT INTO downtime_period (dt_id, dtp_day_of_month, dtp_month_cycle, dtp_start_time, dtp_end_time, dtp_fixed, dtp_duration)
					VALUES (" . $id . ", '" . join(',', $infos['days']) . "', 'none', '" . $infos['start_period'] . "', '" . $infos['end_period'] . "', '" . $infos['fixed'] . "', " . $infos['duration'] . ")";
				break;
			case 'specific_date':
				$query = "INSERT INTO downtime_period (dt_id, dtp_day_of_week, dtp_month_cycle, dtp_start_time, dtp_end_time, dtp_fixed, dtp_duration)
					VALUES (" . $id . ", '" . $infos['days'] . "', '" . $infos['month_cycle'] . "', '" . $infos['start_period'] . "', '" . $infos['end_period'] . "', '" . $infos['fixed'] . "', " . $infos['duration'] . ")";
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
				$query = "INSERT INTO downtime_service_relation (dt_id, host_host_id, service_service_id) VALUES (" . $id  . ", %obj_id%)";
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
	 * Get the list of period to schedule for a time
	 *
	 * <code>
	 * $return_array =
	 * 	array(
	 * 		array(
	 * 			int, // The start period time in timestamp
	 * 			int, // The end period time in timestamp
	 * 		)
	 * 	)
	 * </code>
	 *
	 * @param int $id The downtime id
	 * @param int $time The timestamp for scheduling
	 * @param int $delay The delay between the timestamp and the start period
	 * @param string $start The start time for this period
	 * @param string $end The end time for this period
	 * @return array
	 * @see CentreonDowntime::getPeriods
	 */
	public function doSchedule($id, $time, $delay, $start, $end)
	{
		$periods = $this->getPeriods($id);
		$listSchedule = array();
		$start = substr($start, 0, strrpos($start, ':'));
		$end = substr($end, 0, strrpos($end, ':'));
		foreach ($periods as $period) {
		    if ($period['start_time'] != $start || $period['end_time'] != $end) {
		        continue;
		    }
			$add = false;
			if ($period['month_cycle'] == 'none') {
				$dateOfMonth = date('j', $time);
				if (in_array($dateOfMonth, $period['day_of_month'])) {
					$add = true;
				}
			} elseif ($period['month_cycle'] == 'all') {
				$dateOfMonth = date('w', $time);
				if ($dateOfMonth == 0) {
				    $dateOfMonth = 7;
				}
				if (in_array($dateOfMonth, $period['day_of_week'])) {
					$add = true;
				}
			} else {
				$dateOfMonth = date('w', $time);
			    if ($dateOfMonth == 0) {
				    $dateOfMonth = 7;
				}
				if ($dateOfMonth == $period['day_of_week']) {
					$monthName = date('F', $time);
					$year = date('Y', $time);
					$dayShortName = date('D', $time);
					$dayInMonth = date('d', strtotime($period['month_cycle'] . ' ' . $dayShortName . ' ' . $monthName . ' ' . $year));
					if ($dayInMonth == date('d', $time)) {
						$add = true;
					}
				}
			}
			if ($add) {
			    /*
			     * If start time is 00:00 the time is for tomorrow
			     */
			    $tomorrow = false;
			    if ($period['start_time'] == '00:00') {
				    $timestamp_start = strtotime($period['start_time']) + 3600 * 24;
				    $tomorrow = true;
			    } else {
				    $timestamp_start = strtotime($period['start_time']);
			    }
				if ($time < $timestamp_start && ($time + $delay) > $timestamp_start) {
				    if ($period['end_time'] == '24:00') {
				        $timestamp_stop = strtotime('00:00') + 3600 * 24;
				    } else {
					    $timestamp_stop = strtotime($period['end_time']);
				    }
				    if ($tomorrow) {
				        $timestamp_stop = $timestamp_stop + 3600 * 24;
				    }
					$listSchedule[] = array($timestamp_start, $timestamp_stop);
				}
			}
		}
		return $listSchedule;
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
	private function normalizeArray($arr) {
		$list = array();
		foreach ($arr as $key => $value) {
			$list[] = $key;
		}
		return $list;
	}
}
?>
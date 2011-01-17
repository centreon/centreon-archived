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
 * SVN : $URL: $
 * SVN : $Id: $
 *
 */


class CentreonDowntime
{
	protected $db;
	protected $search = '';
	
	public function __construct($pearDB)
	{
		$this->db = $pearDB;
	}
	
	public function setSearch($search = '')
	{
		if ('' !== $search) {
			$this->search = " WHERE dt_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%'";
		}
	}
	
	public function getNbRows()
	{
		$query = "SELECT COUNT(*) FROM downtime" . $this->search;
		$res = $this->db->query($query);
		if (PEAR::isError($res)) { 
			return 0;
		}
		$row = $res->fetchRow();
		$res->free();
		return $row["COUNT(*)"];
	}
	
	public function getList($num, $limit)
	{
		$query = "SELECT dt_id, dt_name, dt_description, dt_activate FROM downtime" . $this->search . " ORDER BY dt_name LIMIT " . $num * $limit . ", " . $limit;
		$res = $this->db->query($query);
		if (PEAR::isError($res)) {
			return array();
		}
		$list = array();
		while ($row = $res->fetchRow()) {
			$list[] = $row;
		}
		$res->free();
		return $list;
	}
	
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
			if ($row['dtp_month_cycle'] == 'all') {
				$days = split(',', $days);
			}
			$start_time = substr($row['dtp_start_time'], 0, strrpos($row['dtp_start_time'], ':'));
			$end_time = substr($row['dtp_end_time'], 0, strrpos($row['dtp_end_time'], ':'));
			$list[] = array(
				'start_time' => $start_time,
				'end_time' => $end_time,
				'day_of_week' => $days,
				'month_cycle' => $row['dtp_month_cycle'], 
				'day_of_month' => split(',', $row['dtp_day_of_month']),
				'fixed' => $row['dtp_fixed'],
				'duration' => $row['dtp_duration']
			);
		}
		return $list;
	}
	
	public function getRelations($id)
	{
		$query = "SELECT obj_id, obj_type FROM downtime_relation WHERE dt_id = " . $id . " ORDER BY obj_type";
		$list = array(
			"host" => array(),
			"hostgrp" => array(),
			"svc" => array(),
			"svcgrp" => array()
		);
		$res = $this->db->query($query);
		while ($row = $res->fetchRow()) {
			$list[$row['obj_type']][] = $row['obj_id'];
		}
		$res->free();
		return $list;
	}
	
	public function getDowntime()
	{
		$list = array('host' => array(), 'hostgrp' => array(), 'svc' => array(), 'svcgrp' => array());
		foreach (array_keys($list) as $type) {
			switch ($type) {
				case 'host':
					$name = ', h.host_name as obj_name, dtr.obj_id as obj_id';
					$table = ', host h';
					$clause = ' AND dtr.obj_id = h.host_id';
					break;
				case 'hostgrp':
					$name = ', hg.hg_name as obj_name, dtr.obj_id as obj_id';
					$table = ', hostgroup hg';
					$clause = ' AND dtr.obj_id = hg.hg_id';
					break;
				case 'svc':
					$name = ', s.service_name as obj_name, dtr.obj_id as obj_id, h.host_name as host_name';
					$table = ', service s, host h, host_service_relation hsr';
					$clause = ' AND dtr.obj_id = s.service_id AND hsr.service_service_id = s.service_id AND hsr.host_host_id = h.host_id';
					break;
				case 'svcgrp':
					$name = ', sg.sg_name as obj_name, dtr.obj_id as obj_id';
					$table = ', servicegroup sg';
					$clause = ' AND dtr.obj_id = sg.sg_id';
					break;
				default:
					$name = '';
					$table = '';
					$clause = '';
			}
			$query = "SELECT dt.dt_id, dt.dt_activate, dtp.dtp_start_time, dtp.dtp_end_time, dtp.dtp_day_of_week, dtp.dtp_month_cycle, dtp.dtp_day_of_month, dtp.dtp_fixed, dtp.dtp_duration" . $name . "
				FROM downtime_period dtp, downtime_relation dtr, downtime dt" . $table . "
				WHERE  dtp.dt_id = dtr.dt_id AND dtp.dt_id = dt.dt_id AND dtr.obj_type = '" . $type . "'" . $clause;
			$res = $this->db->query($query);
			if (false === PEAR::isError($res)) {
				while ($row = $res->fetchRow()) {
					$list[$type][] = $row;
				}
			}
		}
		return $list;
	}
	
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
					$query = "SELECT COUNT(*) as nb FROM downtime WHERE dt_name = '" . $dt_name . "_" . $index . "'";
					$res = $this->db->query($query);
					$row = $res->fetchRow();
					if ($row["nb"] == 0) {
						$rq = "INSERT INTO downtime (dt_name, dt_description, dt_activate) 
								VALUES ('" . $dt_name . "_" . $index . "', '" . $dt_desc . "', '" . $dt_activate . "')";
						$res = $this->db->query($rq);
						if (PEAR::isError($res)) {
							return;
						} else {
							$query = "SELECT dt_id FROM downtime WHERE dt_name = '" . $dt_name . "_" . $index . "'";
							$res = $this->db->query($query);
							$row = $res->fetchRow();
							$res->free();
							$id_new = $row['dt_id'];
							$query = "INSERT INTO downtime_period (dt_id, dtp_start_time, dtp_end_time, dtp_day_of_week, dtp_month_cycle, dtp_day_of_month, dtp_fixed, dtp_duration, dtp_activate) 
								SELECT " . $id_new . ", dtp_start_time, dtp_end_time, dtp_day_of_week, dtp_month_cycle, dtp_day_of_month, dtp_fixed, dtp_duration, dtp_activate
								FROM downtime_period WHERE dt_id = " . $id;
							$res = $this->db->query($query);
							$i++;
						}
					}
					$index++;
				}
			}
		}
	}
	
	public function add($name, $desc, $activate)
	{
		$query = "INSERT INTO downtime (dt_name, dt_description, dt_activate) VALUES ('" . CentreonDB::escape($name) . "', '" . CentreonDB::escape($desc) . "', '" . $activate . "')";
		if(PEAR::isError($this->db->query($query))) {
			return false;
		}
		$query = "SELECT dt_id FROM downtime WHERE dt_name = '" . CentreonDB::escape($name) . "'";
		$res = $this->db->query($query);
		if (PEAR::isError($res)) {
			return false;
		}
		$row = $res->fetchRow();
		return $row['dt_id'];
	}
	
	public function modify($id, $name, $desc, $activate) {
		$query = "UPDATE downtime SET
			dt_name = '" . CentreonDB::escape($name) . "',
			dt_description = '" . CentreonDB::escape($desc) . "',
			dt_activate = '" . $activate . "'
			WHERE dt_id = " . $id;
		$this->db->query($query);
	}
	
	public function addPeriod($id, $infos)
	{
		if (trim($infos['duration']) == '') {
			$infos['duration'] = 'NULL';
		} else {
			$infos['duration'] = "'" . $infos['duration'] . "'";
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
	
	public function deletePeriods($id)
	{
		$query = "DELETE FROM downtime_period WHERE dt_id = " .$id;
		$this->db->query($query);
	}
	
	public function addRelations($id, $obj_ids, $obj_type)
	{
		$query = "INSERT INTO downtime_relation (dt_id, obj_id, obj_type) VALUES (" . $id  . ", ?, ?)";
		foreach ($obj_ids as $obj_id) {
			$query = "INSERT INTO downtime_relation (dt_id, obj_id, obj_type) VALUES (" . $id  . ", " . $obj_id . ", '" . $obj_type . "')";
			$this->db->query($query);
		}
	}
	
	public function deteleRelations($id)
	{
		$query = "DELETE FROM downtime_relation WHERE dt_id = " .$id;
		$this->db->query($query);
	}
	
	public function enable($id)
	{
		$this->setActivate($id, '1');
	}
	
	public function multiEnable($ids)
	{
		$this->setActivate($ids, '1');
	}
	
	public function disable($id)
	{
		$this->setActivate($id, '0');
	}
	
	public function multiDisable($ids)
	{
		$this->setActivate($ids, '0');
	}
	
	public function delete($id)
	{
		$this->multiDelete($id);	
	}
	
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
	
	public function doSchedule($id, $time, $delay)
	{
		$periods = $this->getPeriods($id);
		$listSchedule = array();
		foreach ($periods as $period) {
			$add = false;
			if ($period['month_cycle'] == 'none') {
				$dateOfMonth = date('j', $time);
				if (in_array($dateOfMonth, $period['day_of_month'])) {
					$add = true;
				}
			} elseif ($period['month_cycle'] == 'all') {
				$dateOfMonth = date('w', $time);
				if (in_array($dateOfMonth, $period['day_of_week'])) {
					$add = true;
				}
			} else {
				$dateOfMonth = date('w', $time);
				if ($dateOfMonth == $period['day_of_week']) {
					$monthName = date('F', $time);
					$dayShortName = date('D', $time);
					$dayInMonth = date('d', strtotime($period['month_cycle'] . ' ' . $dayShortName . ' of ' . $monthName));
					if ($dayInMonth == date('d', $time)) {
						$add = true;
					}
				}
			}
			if ($add) {
				$timestamp_start = strtotime($period['start_time']);
				if ($time < $timestamp_start && ($time + $delay) > $timestamp_start) {
					$timestamp_stop = strtotime($period['end_time']);
					$listSchedule[] = array($timestamp_start, $timestamp_stop);
				}
			}
		}
		return $listSchedule;
	}
	
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
	
	private function normalizeArray($arr) {
		$list = array();
		foreach ($arr as $key => $value) {
			$list[] = $key;
		}
		return $list;
	}
}

/*
 * dtp.dtp_start_time > CURRENT_TIME AND TIME_TO_SEC(dtp.dtp_start_time) < (TIME_TO_SEC(CURRENT_TIME) + " . $delay . ")
					AND
 */
?>
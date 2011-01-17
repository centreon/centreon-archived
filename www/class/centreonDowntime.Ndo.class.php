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

/**
 * Class for management downtime with ndo broker
 * 
 * @see CentreonDowntime
 */
class CentreonDowntimeNdo extends CentreonDowntime
{
	private $dbndo;
	private $ndoPrefix;
	
	/**
	 * Constructor
	 * 
	 * @param CentreonDb $pearDB
	 */
	public function __construct($pearDB)
	{
		$this->db = $pearDB;
		$this->dbndo = new CentreonDB('ndo');
		$res = $this->db->query("SELECT db_prefix FROM cfg_ndo2db LIMIT 1;");
		$row = $res->fetchRow();
		$res->free();
		$this->ndoPrefix = $row['db_prefix'];
	}
	
	/**
	 * Get the list of reccurrent downtime after now
	 * 
	 * Return array
	 *   array(
	 *   	'services' => array(
	 *   		0 => array('Host 1', 'Service 1')
	 *   	),
	 *   	'hosts' => array(
	 *		   0 => array('Host 1')
	 *   	)
	 * 	)
	 * 
	 * @return array A array with host and services for downtime, or false if in error
	 */
	public function getSchedDowntime()
	{
		$list = array('hosts' => array(), 'services' => array());
		$query = "SELECT dth.internal_downtime_id, o.name1, o.name2, o.objecttype_id
			FROM " . $this->ndoPrefix . "objects o, " . $this->ndoPrefix . "downtimehistory dth
			WHERE dth.object_id = o.object_id AND dth.scheduled_start_time > NOW() AND dth.comment_data LIKE '[Downtime cycle%'";
		$res = $this->dbndo->query($query);
		if (PEAR::isError($res)) {
			return false;
		}
		while ($row = $res->fetchRow()) {
			if ($row['objecttype_id'] == 2) {
				$list['services'] = array('host_name' => $row['name1'], 'service_name' => $row['name2']);
			} elseif ($row['objecttype_id'] == 1) {
				$list['hosts'] = array('host_name' => $row['name1']);
			}
		}
		return $list; 
	}
	
	/**
	 * Get the NDO internal ID
	 * 
	 * @param string $oname1 The first object name (host_name)
	 * @param int $start_time The timestamp for starting downtime
	 * @param int $dt_id The downtime id
	 * @param string $oname2 The second object name (service_name), is null if search a host 
	 */
	public function getDowntimeInternalId($oname1, $start_time, $dt_id, $oname2 = null)
	{
		$query = "SELECT dth.internal_downtime_id
			FROM " . $this->ndoPrefix . "objects o, " . $this->ndoPrefix . "downtimehistory dth
			WHERE dth.scheduled_start_time = " . $start_time . " AND dth.object_id = o.object_id
				dth.comment_data = '[Downtime cycle #" . $dt_id . "]' AND o.name1 = '" . $oname1 . "' AND o.name2 ";
		if (is_null($oname2)) {
			$query .= "IS NULL";
		} else {
			$query .= " = '" . $oname2 . "'";
		}
		$res = $this->dbndo->query($query);
		if (PEAR::isError($res)) {
			return false;
		}
		$row = $res->fetchRow();
		return $row['internal_downtime_id'];
	}    
	
	/**
	 * Check if the downtime is scheduled
	 * 
	 * @param int $dt_id The downtime id
	 * @param string $oname1 The first object name (host_name)
	 * @param string $oname2 The second object name (service_name), is null if search a host
	 */
	public function isScheduled($dt_id, $oname1, $oname2 = null)
	{
		$query = "SELECT dth.internal_downtime_id, dth.downtime_type
			FROM " . $this->ndoPrefix . "objects o, " . $this->ndoPrefix . "downtimehistory dth
			WHERE dth.object_id = o.object_id AND dth.scheduled_start_time > NOW() AND dth.comment_data = '[Downtime cycle #" . $dt_id . "]'
				AND o.name1 = '" . $oname1 . "'";
		if (!is_null($oname2)) {
			$query .= " o.name2 = '" . $oname2 . "'";
		}
		$res = $this->dbndo->query($query);
		if (PEAR::isError($res)) {
			return array();
		}
		$listObj = array();
		while ($row = $res->fetchRow()) {
			$listObj[] = $row;
		}
		return $listObj;
	}
}
?>
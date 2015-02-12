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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/class/centreonDowntime.Ndo.class.php $
 * SVN : $Id: centreonDowntime.Ndo.class.php 11678 2011-02-14 15:34:15Z jmathis $
 *
 */

/**
 * Class for management downtime with ndo broker
 *
 * @see CentreonDowntime
 */
class CentreonDowntimeBroker extends CentreonDowntime
{
	private $dbb;

	/**
	 * Constructor
	 *
	 * @param CentreonDb $pearDB
	 * @param string $varlib
	 */
	public function __construct($pearDB, $varlib = null)
	{
		parent::__construct($pearDB, $varlib);
		$this->dbb = new CentreonDB('centstorage');
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
		$query = "SELECT d.internal_id as internal_downtime_id,
						 h.name as name1,
						 s.description as name2
			FROM downtimes d, hosts h
			LEFT JOIN services s ON s.host_id = h.host_id
			WHERE d.host_id = h.host_id AND d.start_time > NOW() AND d.comment_data LIKE '[Downtime cycle%'";
		$res = $this->dbb->query($query);
		if (PEAR::isError($res)) {
			return false;
		}
		while ($row = $res->fetchRow()) {
			if (isset($row['name2']) && $row['name2'] != "") {
				$list['services'] = array('host_name' => $row['name1'], 'service_name' => $row['name2']);
			} elseif (isset($row['name1']) && $row['name1'] != "") {
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
	 * @return int
	 */
	public function getDowntimeInternalId($oname1, $start_time, $dt_id, $oname2 = null)
	{
        $query = "SELECT d.internal_id as internal_downtime_id
        		  FROM downtimes d, hosts h ";
        if (isset($oname2) && $oname2 != "") {
            $query .= ", services s ";
        }
        $query .= "WHERE d.host_id = h.host_id
        		  AND d.start_time = " .$this->dbb->escape($start_time). "
        		  AND d.comment_data = '[Downtime cycle #".$dt_id."]'
        		  AND h.name = '".$this->dbb->escape($oname1)."' ";
        if (isset($oname2) && $oname2 != "") {
            $query .= " AND h.host_id = s.host_id ";
            $query .= " AND s.description = '".$this->dbb->escape($oname2)."' ";
        }
		$res = $this->dbb->query($query);
		if (PEAR::isError($res)) {
			return false;
		}
		$row = $res->fetchRow();
		return $row['internal_downtime_id'];
	}

	/**
	 * Check if the downtime is scheduled
	 *
	 * Return array
	 *   array(
	 *   	0 => array(
	 *          internal_id => 1,
	 *          downtime_type => 1
	 *      )
	 * 	)
	 *
	 * @param int $dt_id The downtime id
	 * @param int $hostId The first object id (host_id)
	 * @param int $serviceId The second object id (service_id), is null if search a host
	 * @return array
	 */
	public function isScheduled($dt_id, $hostId, $serviceId = null)
	{
		static $downtimeHosts = array();
		static $downtimeServices = array();

		if (!isset($downtimeHosts[$dt_id])) {
			$downtimeHosts[$dt_id] = array();
			$downtimeServices[$dt_id] = array();

			$query = "SELECT internal_id as internal_downtime_id, type as downtime_type, host_id, service_id
				FROM downtimes
				WHERE start_time > UNIX_TIMESTAMP()
				AND comment_data = '[Downtime cycle #" . $dt_id . "]'";
			$res = $this->dbb->query($query);
			while ($row = $res->fetchRow()) {
				if (!isset($downtimes[$dt_id][$row['host_id']]) && is_null($row['service_id'])) {
					$downtimeHosts[$dt_id][$row['host_id']] = $row;
				}
				if (!is_null($row['service_id'])) {
					$downtimeServices[$dt_id][$row['host_id']][$row['service_id']] = $row;
				}
			}
		}
		
		$arr = array();
		if (!is_null($serviceId)) {
			if (isset($downtimeServices[$dt_id]) 
				&& isset($downtimeServices[$dt_id][$hostId]) && isset($downtimeServices[$dt_id][$hostId][$serviceId])) {
					$arr = $downtimeServices[$dt_id][$hostId][$serviceId];
				}
		} elseif (isset($downtimeHosts[$dt_id]) && isset($downtimeHosts[$dt_id][$hostId])) {
			$arr = $downtimeHosts[$dt_id][$hostId];
		}

		$listObj = array();
		foreach ($arr as $row) {
			$listObj[] = $row;
		}
		return $listObj;
	}
}
?>

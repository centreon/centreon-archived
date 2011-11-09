<?php
/*
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
  *
  * Servicegroups objects
  *
  */
 class CentreonServicegroups {
 	private $DB;
 	private $relationCache;
 	private $dataTree;

 	/**
 	 *
 	 * Constructor
 	 * @param $pearDB
 	 */
 	function __construct($pearDB) {
 		$this->DB = $pearDB;
 	}

 	/**
 	 *
 	 * Enter description here ...
 	 * @param unknown_type $sg_id
 	 */
 	public function getServiceGroupServices($sg_id = NULL)	{
		if (!$sg_id) {
			return;
		}

		$services = array();
		$query = "SELECT host_host_id, service_service_id
				  FROM servicegroup_relation
				  WHERE servicegroup_sg_id = " . $sg_id . "
				  AND host_host_id IS NOT NULL
				  UNION
				  SELECT hgr.host_host_id, hsr.service_service_id
				  FROM servicegroup_relation sgr, host_service_relation hsr, hostgroup_relation hgr
				  WHERE sgr.servicegroup_sg_id = " . $sg_id . "
				  AND sgr.hostgroup_hg_id = hsr.hostgroup_hg_id
				  AND hsr.service_service_id = sgr.service_service_id
				  AND sgr.hostgroup_hg_id = hgr.hostgroup_hg_id";
		$res = $this->DB->query($query);
		while ($row = $res->fetchRow()) {
			$services[] = array($row['host_host_id'], $row['service_service_id']);
		}
		$res->free();
		return $services;
	}
}

?>
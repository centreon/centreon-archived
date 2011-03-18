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
 * Enter description here ...
 * @author jmathis
 *
 */
class CentreonMonitoring {

	var $poller;
	var $DB;
	var $objBroker;

	/**
	 *
	 * Enter description here ...
	 */
	public function __construct($DB) {
		$this->DB = $DB;
		$this->objBroker = new CentreonBroker($DB);
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $pollerId
	 */
	public function setPoller($pollerId) {
		$this->poller = $pollerId;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function getPoller() {
		return $this->poller;
	}

	/**
	 *
	 * Enter description here ...
	 * @param $host_name
	 * @param $objXMLBG
	 * @param $o
	 * @param $status
	 * @param $obj
	 */
	public function getServiceStatusCountNDO($host_name, $objXMLBG, $o, $status, $obj) {

		$rq = 	" SELECT DISTINCT no.name1, no.name2, count( nss.service_object_id ) AS nb".
				" FROM " .$objXMLBG->ndoPrefix."servicestatus nss".
				" WHERE nss.current_state = '".$status."'";

		if ($o == "svcSum_ack_0") {
			$rq .= " AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0";
		} else if ($o == "svcSum_ack_1") {
			$rq .= " AND nss.problem_has_been_acknowledged = 1 AND nss.current_state != 0";
		}

		$rq .= 	" AND nss.service_object_id".
				" IN (".
				" SELECT nno.object_id".
				" FROM " .$objXMLBG->ndoPrefix."objects nno";

		if (!$objXMLBG->is_admin) {
			$rq	.=	", centreon_acl";
		}
		$rq	.=	" WHERE nno.objecttype_id = 2 " .
				" AND nno.name1 LIKE '".$host_name."'";
		if (!$objXMLBG->is_admin) {
			$rq .= 	" AND nno.name1 = centreon_acl.host_name AND nno.name2 = centreon_acl.service_description AND centreon_acl.group_id IN (". $obj->access->getAccessGroupsString().")";
		}
		$rq .=  ")";

		$DBRESULT = $objXMLBG->DBNdo->query($rq);
		$tab = $DBRESULT->fetchRow();
		return ($tab["nb"]);
	}
	
	/**
	 *
	 * Enter description here ...
	 * @param $host_name
	 * @param $objXMLBG
	 * @param $o
	 * @param $status
	 * @param $obj
	 */
	public function getServiceStatusCountBroker($host_name, $objXMLBG, $o, $status, $obj) {

		$rq = 	" SELECT DISTINCT s.host_id, s.service_id, s.state ".
				" FROM services s, `hosts` h".(!$objXMLBG->is_admin ? ", centreon_acl " : "").
				" WHERE s.state = '".$status."'".
				" AND s.host_id = h.host_id ".
				" AND h.name = '$host_name' ";

		if ($o == "svcSum_ack_0") {
			$rq .= " AND s.acknowledged = 0 AND s.state != 0";
		} else if ($o == "svcSum_ack_1") {
			$rq .= " AND s.acknowledged = 1 AND s.state != 0";
		}
		if (!$objXMLBG->is_admin) {
			$rq .= 	" AND h.host_id = centreon_acl.host_id ". 
					" AND s.service_id = centreon_acl.service_id ". 
					" AND centreon_acl.group_id IN (". $obj->access->getAccessGroupsString().")";
		}
		$DBRESULT = $objXMLBG->DBC->query($rq);
		$cpt = 0;
		while ($tab = $DBRESULT->fetchRow()) {
			$cpt++;
		}
		$DBRESULT->free();
		return ($cpt);
	}

	/**
	 *
	 * Proxy function
	 * @param unknown_type $hostList
	 * @param unknown_type $objXMLBG
	 * @param unknown_type $o
	 * @param unknown_type $instance
	 * @param unknown_type $hostgroups
	 */
	public function getServiceStatusCount($host_name, $objXMLBG, $o, $status, $obj) {
		if ($this->objBroker->getBroker() == "broker") {
			return $this->getServiceStatusCountBroker($host_name, $objXMLBG, $o, $status, $obj);
		} else if ($this->objBroker->getBroker() == "ndo") {
			return $this->getServiceStatusCountNDO($host_name, $objXMLBG, $o, $status, $obj);
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param $host_name
	 * @param $objXMLBG
	 * @param $o
	 * @param $status
	 * @param $obj
	 */
	public function getServiceAllStatusCountBroker($host_name, $objXMLBG, $o, $obj) {

		$rq = 	" SELECT count(s.service_id) AS nb".
				" FROM services s".
				" WHERE s.state = '".$status."'";

		if ($o == "svcSum_ack_0") {
			$rq .= " AND s.acknowledged = 0 AND s.state != 0";
		} else if ($o == "svcSum_ack_1") {
			$rq .= " AND s.acknowledged = 1 AND s.state != 0";
		}

		$rq .= 	" AND nss.service_object_id".
				" IN (".
				" SELECT nno.object_id".
				" FROM " .$objXMLBG->ndoPrefix."objects nno";

		if (!$objXMLBG->is_admin) {
			$rq	.=	", centreon_acl";
		}
		$rq	.=	" WHERE nno.objecttype_id = 2 " .
				" AND nno.name1 LIKE '".$host_name."'";
		if (!$objXMLBG->is_admin) {
			$rq .= 	" AND nno.name1 = centreon_acl.host_name AND nno.name2 = centreon_acl.service_description AND centreon_acl.group_id IN (". $obj->access->getAccessGroupsString().")";
		}
		$rq .=  ")";

		$DBRESULT = $objXMLBG->DBNdo->query($rq);
		$tab = $DBRESULT->fetchRow();
		return ($tab["nb"]);
	}

	/**
	 *
	 * Enter description here ...
	 * @param $host_name
	 * @param $objXMLBG
	 * @param $o
	 * @param $status
	 * @param $obj
	 */
	public function getServiceAllStatusCountNDO($host_name, $objXMLBG, $o, $obj) {

		$rq = 	" SELECT count( nss.service_object_id ) AS nb".
				" FROM " .$objXMLBG->ndoPrefix."servicestatus nss".
				" WHERE nss.current_state = '".$status."'";

		if ($o == "svcSum_ack_0") {
			$rq .= " AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0";
		} else if ($o == "svcSum_ack_1") {
			$rq .= " AND nss.problem_has_been_acknowledged = 1 AND nss.current_state != 0";
		}

		$rq .= 	" AND nss.service_object_id".
				" IN (".
				" SELECT nno.object_id".
				" FROM " .$objXMLBG->ndoPrefix."objects nno";

		if (!$objXMLBG->is_admin) {
			$rq	.=	", centreon_acl";
		}
		$rq	.=	" WHERE nno.objecttype_id = 2 " .
				" AND nno.name1 LIKE '".$host_name."'";
		if (!$objXMLBG->is_admin) {
			$rq .= 	" AND nno.name1 = centreon_acl.host_name AND nno.name2 = centreon_acl.service_description AND centreon_acl.group_id IN (". $obj->access->getAccessGroupsString().")";
		}
		$rq .=  ")";

		$DBRESULT = $objXMLBG->DBNdo->query($rq);
		$tab = $DBRESULT->fetchRow();
		return ($tab["nb"]);
	}


	/**
	 *
	 * Proxy function
	 * @param unknown_type $hostList
	 * @param unknown_type $objXMLBG
	 * @param unknown_type $o
	 * @param unknown_type $instance
	 * @param unknown_type $hostgroups
	 */
	public function getServiceStatus($hostList, $objXMLBG, $o, $instance, $hostgroups) {
		if ($this->objBroker->getBroker() == "broker") {
			return $this->getServiceStatusBroker($hostList, $objXMLBG, $o, $instance, $hostgroups);
		} else if ($this->objBroker->getBroker() == "ndo") {
			return $this->getServiceStatusNDO($hostList, $objXMLBG, $o, $instance, $hostgroups);
		}
	}


	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $hostList
	 * @param unknown_type $objXMLBG
	 * @param unknown_type $o
	 * @param unknown_type $instance
	 * @param unknown_type $hostgroups
	 */
	private function getServiceStatusBroker($hostList, $objXMLBG, $o, $instance, $hostgroups) {
		if ($hostList == "") {
           return array();
        }

		$rq = 		" SELECT h.name, s.description as service_name, s.state, s.service_id " .
					" FROM hosts h, services s";

		if (!$objXMLBG->is_admin) {
			$rq .= ", centreon_acl ";
		}
		$rq .= 		" WHERE h.host_id = s.host_id " .
					" AND s.enabled = '1' " .
					" AND h.name NOT LIKE '_Module_%'";

		if ($o == "svcgrid_pb" || $o == "svcOV_pb") {
			$rq .= 	" AND s.state != 0" ;
		} else if ($o == "svcgrid_ack_0" || $o == "svcOV_ack_0") {
			$rq .= 	" AND s.acknowledged = 0 AND s.state != 0" ;
		} else if ($o == "svcgrid_ack_1" || $o == "svcOV_ack_1") {
			$rq .= 	" AND s.acknowledged = 1" ;
		}

		$rq .=  	" AND h.name IN (".$hostList.")";

		if ($instance != -1) {
			$rq .= 	" AND h.instance_id = ".$instance;
		}
		$grouplistStr = $objXMLBG->access->getAccessGroupsString();
		if (!$objXMLBG->is_admin) {
			$rq .= "AND h.host_id = centreon_acl.host_id AND s.service_id = centreon_acl.service_id ".$objXMLBG->access->queryBuilder("AND", "centreon_acl.group_id", $grouplistStr);
		}
		$tab = array();
		$DBRESULT = $objXMLBG->DBC->query($rq);
		while ($svc = $DBRESULT->fetchRow()) {
			if (!isset($tab[$svc["name"]])) {
				$tab[$svc["name"]] = array();
			}
			$tab[$svc["name"]][$svc["service_name"]] = $svc["state"];
		}
		$DBRESULT->free();
		return $tab;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $hostList
	 * @param unknown_type $objXMLBG
	 * @param unknown_type $o
	 * @param unknown_type $instance
	 * @param unknown_type $hostgroups
	 */
	private function getServiceStatusNDO($hostList, $objXMLBG, $o, $instance, $hostgroups) {
		if ($hostList == "") {
           return array();
        }

		$rq = 		" SELECT no.name1, no.name2 as service_name, nss.current_state, service_object_id " .
					" FROM `".$objXMLBG->ndoPrefix."servicestatus` nss, `".$objXMLBG->ndoPrefix."objects` no";

		if (!$objXMLBG->is_admin)
			$rq .= ", centreon_acl ";

		$rq .= 		" WHERE no.object_id = nss.service_object_id".
					" AND no.name1 NOT LIKE '_Module_%'";

		if ($o == "svcgrid_pb" || $o == "svcOV_pb") {
			$rq .= 	" AND nss.current_state != 0" ;
		} else if ($o == "svcgrid_ack_0" || $o == "svcOV_ack_0") {
			$rq .= 	" AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" ;
		} else if ($o == "svcgrid_ack_1" || $o == "svcOV_ack_1") {
			$rq .= 	" AND nss.problem_has_been_acknowledged = 1" ;
		}

		$rq .=  	" AND no.object_id IN (" .
					" SELECT nno.object_id FROM " .$objXMLBG->ndoPrefix."objects nno " .
					" WHERE nno.objecttype_id =2 AND nno.name1 IN (".$hostList."))";

		if ($instance != -1) {
			$rq .= 	" AND no.instance_id = ".$instance;
		}
		$grouplistStr = $objXMLBG->access->getAccessGroupsString();
		$rq .= 	$objXMLBG->access->queryBuilder("AND", "no.name1", "centreon_acl.host_name") . $objXMLBG->access->queryBuilder("AND", "no.name2", "centreon_acl.service_description").$objXMLBG->access->queryBuilder("AND", "centreon_acl.group_id", $grouplistStr);

		$tab = array();
		$DBRESULT = $objXMLBG->DBNdo->query($rq);
		while ($svc = $DBRESULT->fetchRow()) {
			if (!isset($tab[$svc["name1"]])) {
				$tab[$svc["name1"]] = array();
			}
			$tab[$svc["name1"]][$svc["service_name"]] = $svc["current_state"];
		}
		$DBRESULT->free();
		return $tab;
	}

}
?>
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
 * SVN : $URL
 * SVN : $Id
 *
 */

	ini_set("display_errors", "Off");

	include_once "@CENTREON_ETC@/centreon.conf.php";

	include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";
	include_once $centreon_path . "www/include/monitoring/status/Common/common-Func.php";
	include_once $centreon_path . "www/include/common/common-Func.php";
	include_once $centreon_path . "www/class/centreonService.class.php";

	/*
	 * Create XML Request Objects
	 */
	$obj = new CentreonXMLBGRequest($_GET["sid"], 1, 1, 0, 1);
	$svcObj = new CentreonService($obj->DB);
	CentreonSession::start();

	if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
		;
	} else {
		print "Bad Session ID";
		exit();
	}

	/*
	 * Set Default Poller
	 */
	$obj->getDefaultFilters();

	/* **************************************************
	 * Check Arguments From GET tab
	 */
	$o 			= $obj->checkArgument("o", $_GET, "h");
	$p 			= $obj->checkArgument("p", $_GET, "2");
	$nc 		= $obj->checkArgument("nc", $_GET, "0");
	$num 		= $obj->checkArgument("num", $_GET, 0);
	$limit 		= $obj->checkArgument("limit", $_GET, 20);
	$instance 	= $obj->checkArgument("instance", $_GET, $obj->defaultPoller);
	$hostgroups = $obj->checkArgument("hostgroups", $_GET, $obj->defaultHostgroups);
	$search 	= $obj->checkArgument("search", $_GET, "");
	$sort_type 	= $obj->checkArgument("sort_type", $_GET, "host_name");
	$order 		= $obj->checkArgument("order", $_GET, "ASC");
	$dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "d/m/Y H:i:s");

	/*
	 * Backup poller selection
	 */
	$obj->setInstanceHistory($instance);

	/** **********************************************
	 * Prepare pagination
	 */
	$rq1 = "SELECT DISTINCT h.name as host_name, sg.alias, sg.name AS sg_name".
		" FROM servicegroups sg, services_servicegroups sgm, services s, hosts h ".
		" WHERE s.service_id = sgm.service_id".
		" AND s.host_id = sgm.host_id" .
		" AND s.host_id = h.host_id" .
		" AND sgm.servicegroup_id = sg.servicegroup_id" .
		" AND s.enabled = 1 ";

	$rq1 .= $obj->access->queryBuilder("AND", "sg.alias", $obj->access->getServiceGroupsString("ALIAS"));

	if ($instance != -1) {
		$rq1 .= " AND h.instance_id = ".$instance;
	}
	if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb") {
		$rq1 .= " AND s.state != 0" ;
	}
	if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0") {
		$rq1 .= " AND s.state != 0 AND s.acknowledged = 0" ;
	}
	if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1") {
		$rq1 .= " AND s.acknowledged = '1'";
	}
	if ($search != ""){
		$rq1 .= " AND h.name like '%" . $search . "%' ";
	}
	$DBRESULT = $obj->DBC->query($rq1);
	$numRows = 0;
	$tabString = "";
	while ($row = $DBRESULT->fetchRow()) {
		$numRows++;
		if ($tabString != "") {
			$tabString .= ",";
		}
		$tabString .= "'".$row['host_name']."'";
	}
	unset($row);
	$DBRESULT->free();

	if ($numRows) {

		/** ******************************************
		 * Get all informations
		 */
		$rq1 = 	"SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.alias, sg.name as sg_name, h.name as host_name".
				" FROM servicegroups sg, services_servicegroups sgm, services s, hosts h ".
				" WHERE s.service_id = sgm.service_id" .
				" AND h.host_id = s.host_id" .
				" AND sgm.host_id = s.host_id" .
				" AND sgm.servicegroup_id = sg.servicegroup_id" .
				" AND s.enabled = '1' ";
		$rq1 .= $obj->access->queryBuilder("AND", "sg.alias", $obj->access->getServiceGroupsString("ALIAS"));

		if ($instance != -1) {
			$rq1 .= " AND h.instance_id = ".$instance;
		}
		if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb") {
			$rq1 .= " AND s.state != 0" ;
		}
		if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0") {
			$rq1 .= " AND s.state != 0 AND s.acknowledged = 0" ;
		}
		if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1") {
			$rq1 .= " AND s.service_id IN (" .
				" SELECT s.service_id FROM services s " .
				" WHERE s.acknowledged = 1" .
				")";
		}
		if ($search != ""){
			$rq1 .= " AND h.name like '%" . $search . "%' ";
		}
		$rq1 .= " ORDER BY sg.name, host_name " . $order;
		$rq1 .= " LIMIT ".($num * $limit).",".$limit;

		$DBRESULT_PAGINATION = $obj->DBC->query($rq1);
		$host_table = array();
		$sg_table = array();
		while ($row = $DBRESULT_PAGINATION->fetchRow()) {
		    $host_table[$row["host_name"]] = $row["host_name"];
			if (!isset($sg_table[$row["sg_name"]])) {
            	$sg_table[$row["sg_name"]] = array();
			}
        	$sg_table[$row["sg_name"]][$row["host_name"]] = $row["host_name"];
		}
		$DBRESULT_PAGINATION->free();

		/** *****************************************
		 * Create Host list string
		 */
		$hostList = "";
		foreach ($host_table as $host_name) {
			if ($hostList != "")
			   $hostList .= ",";
			$hostList .= "'".$host_name."'";
		}
		if ($hostList == "") {
		    $hostList = "''";
		}

		/** *****************************************
		 * Prepare Finale Request
		 */
		$rq1 =	"SELECT sg.alias, sg.name as sg_name, h.name as host_name, s.description as service_description, sgm.servicegroup_id, sgm.service_id, " .
				"s.state, h.icon_image, h.host_id, h.state AS host_state ".
				" FROM servicegroups sg, services_servicegroups sgm, services s, hosts h".
				" WHERE sgm.servicegroup_id = sg.servicegroup_id " .
				" AND s.service_id = sgm.service_id" .
				" AND s.host_id = sgm.host_id" .
				" AND s.host_id = h.host_id" .
				" AND h.name IN ($hostList)" .
				" AND s.enabled = '1'" .
				$obj->access->queryBuilder("AND", "sg.alias", $obj->access->getServiceGroupsString("ALIAS"));
		if ($instance != -1) {
			$rq1 .= " AND h.instance_id = ".$instance;
		}
		if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb") {
			$rq1 .= " AND s.state != 0" ;
		}
		if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0") {
			$rq1 .= " AND s.state != 0 AND s.acknowledged = 0" ;
		}
		if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1") {
			$rq1 .= " AND s.service_id IN (" .
				" SELECT s.service_id FROM services s " .
				" WHERE s.acknowledged = '1'" .
				")";
		}
		if ($search != "") {
			$rq1 .= " AND h.name like '%" . $search . "%' ";
		}
		if ($sort_type == "host_state") {
			$rq1 .= " ORDER BY sg.name, host_state $order, host_name, service_description ";
		} else {
			$rq1 .= " ORDER BY sg.name, host_name $order, service_description ";
		}
	}

	/** ***************************************************
	 * Create XML Flow
	 */
	$obj->XML = new CentreonXML();
	$obj->XML->startElement("reponse");
	$obj->XML->startElement("i");
	$obj->XML->writeElement("numrows", $numRows);
	$obj->XML->writeElement("num", $num);
	$obj->XML->writeElement("limit", $limit);
	$obj->XML->writeElement("host_name", _("Hosts"), 0);
	$obj->XML->writeElement("services", _("Services"), 0);
	$obj->XML->writeElement("p", $p);
	($o == "svcOVSG") ? $obj->XML->writeElement("s", "1")  : $obj->XML->writeElement("s", "0");
	$obj->XML->endElement();

	$ct = 0;
	$flag = 0;

	$sg = "";
	$h = "";
	$flag = 0;
	$count = 0;
	$DBRESULT = $obj->DBC->query($rq1);
	while ($tab = $DBRESULT->fetchRow()) {
		if (isset($sg_table[$tab["sg_name"]]) && isset($sg_table[$tab["sg_name"]][$tab["host_name"]]) && isset($host_table[$tab["host_name"]])) {
			if ($sg != $tab["sg_name"]) {
				$flag = 0;
				if ($sg != "") {
					$obj->XML->endElement();
					$obj->XML->endElement();
				}
				$sg = $tab["sg_name"];
				$h = "";
				$obj->XML->startElement("sg");
				$obj->XML->writeElement("sgn", $tab["sg_name"]);
				$obj->XML->writeElement("o", $ct);
			}
			$ct++;

			if ($h != $tab["host_name"]) {
				if ($h != "" && $flag) {
					$obj->XML->endElement();
				}
				$flag = 1;
				$h = $tab["host_name"];
				$hs = $tab["host_state"];
				$obj->XML->startElement("h");
				$obj->XML->writeAttribute("class", $obj->getNextLineClass());
				$obj->XML->writeElement("hn", $tab["host_name"], false);
				if ($tab["icon_image"]) {
					$obj->XML->writeElement("hico", $tab["icon_image"]);
				} else {
					$obj->XML->writeElement("hico", "none");
				}
				$obj->XML->writeElement("hnl", urlencode($tab["host_name"]));
				$obj->XML->writeElement("hid", $tab["host_id"]);
				$obj->XML->writeElement("hcount", $count);
				$obj->XML->writeElement("hs", _($obj->statusHost[$tab["host_state"]]));
				$obj->XML->writeElement("hc", $obj->colorHost[$tab["host_state"]]);
				$count++;
			}
			$obj->XML->startElement("svc");
			$obj->XML->writeElement("sn", $tab["service_description"]);
			$obj->XML->writeElement("snl", urlencode($tab["service_description"]));
			$obj->XML->writeElement("sc", $obj->colorService[$tab["state"]]);
			$obj->XML->writeElement("svc_id", $svcObj->getServiceId($tab['service_description'], $tab['host_name']));
			$obj->XML->endElement();
		}
	}
	$DBRESULT->free();
	if ($sg != "") {
		$obj->XML->endElement();
		$obj->XML->endElement();
	}
	$obj->XML->endElement();

	/*
	 * Send Header
	 */
	$obj->header();

	/*
	 * Send XML
	 */
	$obj->XML->output();
?>
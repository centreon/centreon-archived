<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 * SVN : $URL:
 * SVN : $Id:
 *
 */

	ini_set("display_errors", "Off");

	include_once "@CENTREON_ETC@/centreon.conf.php";
    //include_once "@CENTREON_ETC@/centreon.conf.php";

	include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";
	include_once $centreon_path . "www/include/monitoring/status/Common/common-Func.php";
	include_once $centreon_path . "www/include/common/common-Func.php";

	/*
	 * Create XML Request Objects
	 */
	$obj = new CentreonXMLBGRequest($_GET["sid"], 1, 1, 0, 1);
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

	/** **************************************************
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

	/** **************************************************
	 * Backup poller selection
	 */
	$obj->setInstanceHistory($instance);

	/** **************************************************
	 * Get Icone list
	 */
	$hostIcones = array();
	$tabHost = array();
	$query = "SELECT no.name1, h.icon_image, h.host_object_id FROM ".$obj->ndoPrefix."objects no, ".$obj->ndoPrefix."hosts h WHERE no.object_id = h.host_object_id";
	$DBRESULT = $obj->DBNdo->query($query);
	while ($data = $DBRESULT->fetchRow()) {
		$hostIcones[$data['name1']] = $data['icon_image'];
		$tabHost[$data['name1']] = $data['host_object_id'];
	}
	$DBRESULT->free();

	/** **************************************************
	 * Get Host status
	 */
	$rq1 = "SELECT DISTINCT no.name1 as host_name, sg.alias".
			" FROM " .$obj->ndoPrefix."servicegroups sg," .$obj->ndoPrefix."servicegroup_members sgm, " .$obj->ndoPrefix."servicestatus ss, " .$obj->ndoPrefix."objects no".
			" WHERE ss.service_object_id = sgm.service_object_id".
			" AND no.object_id = sgm.service_object_id" .
			" AND sgm.servicegroup_id = sg.servicegroup_id".
			" AND no.is_active = 1 ";

	$rq1 .= $obj->access->queryBuilder("AND", "sg.alias", $obj->access->getServiceGroupsString("ALIAS"));
	if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb" || $o == "svcSumSG_pb" || $o == "svcSumSG_ack_0") {
		$rq1 .= " AND ss.current_state != 0 AND no.name1 IN (" .
			" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
			" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0)";
	}
	if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0" || $o == "svcSumSG_ack_0") {
		$rq1 .= " AND no.name1 IN (" .
			" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
			" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" .
			")";
	}
	if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1" || $o == "svcSumSG_ack_1") {
		$rq1 .= " AND ss.problem_has_been_acknowledged = 1 AND no.name1 IN (" .
			" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
			" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1" .
			")";
	}
	/**  **************************************************
	 * Search condition
	 */
	if ($search != "") {
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
	}

	$DBRESULT = $obj->DBNdo->query($rq1);
	$numRows = 0;
	while ($row = $DBRESULT->fetchRow()) {
		$numRows++;
    }
	$DBRESULT->free();

	if ($numRows) {
		/*
		 * Check ndo version
		 */
		$request = "SELECT count(*) FROM " .$obj->ndoPrefix."servicegroups WHERE config_type = '1' LIMIT 1";
		$DBRESULT = $obj->DBNdo->query($request);
		while ($row = $DBRESULT->fetchRow()) {
			if ($row["count(*)"] > 0) {
				$custom_ndo = 0;
				break;
			} else {
				$request = "SELECT count(*) FROM " .$obj->ndoPrefix."servicegroups LIMIT 1";
				$DBRESULT2 = $obj->DBNdo->query($request);
				while ($row2 = $DBRESULT2->fetchRow()) {
					if ($row2["count(*)"] > 0) {
						$custom_ndo = 1;
						break;
					} else {
						$custom_ndo = 0;
						break;
					}
				}
				$DBRESULT2->free();
			}
		}
		$DBRESULT->free();

		/**  **************************************************
		 * Host List
		 */
		$rq1 = "SELECT DISTINCT sg.alias, no.name1 as host_name".
				" FROM " .$obj->ndoPrefix."servicegroups sg," .$obj->ndoPrefix."servicegroup_members sgm, " .$obj->ndoPrefix."servicestatus ss, " .$obj->ndoPrefix."objects no".
				" WHERE ss.service_object_id = sgm.service_object_id";
		if ($custom_ndo == 0) {
			$rq1 .= " AND sg.config_type = 1";
		}
		$rq1 .= " AND no.object_id = sgm.service_object_id" .
				" AND sgm.servicegroup_id = sg.servicegroup_id".
				" AND no.is_active = 1 ";
		$rq1 .= $obj->access->queryBuilder("AND", "sg.alias", $obj->access->getServiceGroupsString("ALIAS"));
		if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb" || $o == "svcSumSG_pb" || $o == "svcSumSG_ack_0") {
			$rq1 .= " AND ss.current_state != 0 AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0)";
		}
		if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0" || $o == "svcSumSG_ack_0") {
			$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" .
				")";
		}
		if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1" || $o == "svcSumSG_ack_1") {
			$rq1 .= " AND ss.problem_has_been_acknowledged = 1 AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1" .
				")";
		}

		/** **************************************************
		 * Search condition
		 */
		if ($search != "")
			$rq1 .= " AND no.name1 like '%" . $search . "%' ";
		$rq1 .= " ORDER BY sg.alias ASC, no.name1 ".$order." ";
		$rq1 .= " LIMIT ".($num * $limit).",".$limit;

		$DBRESULT = $obj->DBNdo->query($rq1);
		$host_table = array();
		$sg_table = array();
		while ($row = $DBRESULT->fetchRow()) {
		    $host_table[$row["host_name"]] = $row["host_name"];
			if (!isset($sg_table[$row["alias"]]))
            	$sg_table[$row["alias"]] = array();
        	$sg_table[$row["alias"]][$row["host_name"]] = $row["host_name"];
		}
		$DBRESULT->free();

		/** **************************************************
		 * Create Host list string
		 */
		$hostList = "";
		foreach ($host_table as $host_name) {
			if ($hostList != "") {
			     $hostList .= ",";
			}
			$hostList .= "'".$host_name."'";
		}

		/** **************************************************
		 * Display all services
		 */
		$rq1 = "SELECT sg.alias, no.name1 as host_name, no.name2 as service_description, sgm.servicegroup_id, sgm.service_object_id, ss.current_state".
				" FROM " .$obj->ndoPrefix."servicegroups sg," .$obj->ndoPrefix."servicegroup_members sgm, " .$obj->ndoPrefix."servicestatus ss, " .$obj->ndoPrefix."objects no".
				" WHERE ss.service_object_id = sgm.service_object_id";
		if ($custom_ndo == 0)
			$rq1 .= " AND sg.config_type = 1";

		$rq1 .= " AND no.object_id = sgm.service_object_id" .
				" AND sgm.servicegroup_id = sg.servicegroup_id".
				" AND no.name1 IN ($hostList)" .
				" AND no.is_active = 1 ";
		$rq1 .= $obj->access->queryBuilder("AND", "sg.alias", $obj->access->getServiceGroupsString("ALIAS"));
		if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb" || $o == "svcSumSG_pb" || $o == "svcSumSG_ack_0") {
			$rq1 .= " AND ss.current_state != 0 AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0)";
		}
		if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0" || $o == "svcSumSG_ack_0") {
			$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" .
				")";
		}
		if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1" || $o == "svcSumSG_ack_1") {
			$rq1 .= " AND ss.problem_has_been_acknowledged = 1 AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1" .
				")";
		}

		/*
		 * Search condition
		 */
		if ($search != "") {
			$rq1 .= " AND no.name1 like '%" . $search . "%' ";
		}
		$rq1 .= " ORDER BY sg.alias, host_name " . $order;
	}

	$obj->XML = new CentreonXML();
	$obj->XML->startElement("reponse");
	$obj->XML->startElement("i");
	$obj->XML->writeElement("numrows", $numRows);
	$obj->XML->writeElement("num", $num);
	$obj->XML->writeElement("limit", $limit);
	$obj->XML->writeElement("p", $p);
	$obj->XML->writeElement("sk", $obj->colorService[0]);
	$obj->XML->writeElement("sw", $obj->colorService[1]);
	$obj->XML->writeElement("sc", $obj->colorService[2]);
	$obj->XML->writeElement("su", $obj->colorService[3]);
	$obj->XML->writeElement("sp", $obj->colorService[4]);
	($o == "svcOVSG") ? $obj->XML->writeElement("s", "1") : $obj->XML->writeElement("s", "0");
	$obj->XML->endElement();

	$DBRESULT_NDO1 = $obj->DBNdo->query($rq1);

	$general_opt = getStatusColor($obj->DB);

	$flag = 0;
	$sg = "";
	$h = "";
	$flag = 0;
	$ct = 0;
	$count = 0;
	$nb_service = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
	while ($numRows && $tab = $DBRESULT_NDO1->fetchRow()){
		if (isset($sg_table[$tab["alias"]]) && isset($sg_table[$tab["alias"]][$tab["host_name"]]) && isset($host_table[$tab["host_name"]])) {
			if (($h != "" && $h != $tab["host_name"]) || ($sg != $tab["alias"] && $sg != "")) {
				$obj->XML->startElement("h");
				$obj->XML->writeAttribute("class", $obj->getNextLineClass());
				$obj->XML->writeElement("hn", $h, false);
				if (isset($hostIcones[$host_name]) && $hostIcones[$host_name]) {
					$obj->XML->writeElement("hico", $hostIcones[$h]);
				} else {
					$obj->XML->writeElement("hico", "none");
				}
				$obj->XML->writeElement("hnl", urlencode($h));
				$obj->XML->writeElement("hs", _($obj->statusHost[$hs]));
				$obj->XML->writeElement("hcount", $count);
				$obj->XML->writeElement("hid", $tabHost[$h]);
				$obj->XML->writeElement("hc", $obj->colorHost[$hs]);
				$obj->XML->writeElement("sk", $nb_service[0]);
				$obj->XML->writeElement("sw", $nb_service[1]);
				$obj->XML->writeElement("sc", $nb_service[2]);
				$obj->XML->writeElement("su", $nb_service[3]);
				$obj->XML->writeElement("sp", $nb_service[4]);
				$obj->XML->endElement();
				$count++;
			}
			if ($sg != $tab["alias"]){
				$nb_service = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
				if ($flag)
					$obj->XML->endElement();
				$sg = $tab["alias"];
				$obj->XML->startElement("sg");
				$obj->XML->writeElement("sgn", $tab["alias"]);
				$obj->XML->writeElement("o", $ct);
				$flag = 1;
			}
			$ct++;
			$hs = get_Host_Status($tab["host_name"], $obj->DBNdo, $general_opt);

			if ($h != $tab["host_name"] || $h == "") {
				$nb_service = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
				$h = $tab["host_name"];
			}

			$nb_service[$tab["current_state"]]++;
			$sg = $tab["alias"];
		}
	}
	if (isset($hs)) {
		$obj->XML->startElement("h");
		$obj->XML->writeAttribute("class", $obj->getNextLineClass());
		$obj->XML->writeElement("hn", $h);
		if (isset($hostIcones[$host_name])) {
			$obj->XML->writeElement("hico", $hostIcones[$h]);
		} else {
			$obj->XML->writeElement("hico", "none");
		}
		$obj->XML->writeElement("hs", _($obj->statusHost[$hs]));
		$obj->XML->writeElement("hc", $obj->colorHost[$hs]);
		$obj->XML->writeElement("sk", $nb_service[0]);
		$obj->XML->writeElement("sw", $nb_service[1]);
		$obj->XML->writeElement("sc", $nb_service[2]);
		$obj->XML->writeElement("su", $nb_service[3]);
		$obj->XML->writeElement("sp", $nb_service[4]);
		$obj->XML->endElement();
		$obj->XML->endElement();
		$obj->XML->endElement();
	}

	/*
	 * Send Header
	 */
	$obj->header();

	/*
	 * Send XML
	 */
	$obj->XML->output();
?>
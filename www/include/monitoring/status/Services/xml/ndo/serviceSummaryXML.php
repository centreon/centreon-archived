<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	include_once "@CENTREON_ETC@/centreon.conf.php";

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

	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();
	$tabIcone = array();

	/* **********************************************
	 * Get Host status
	 */
	$rq1 = 		" SELECT " .
				" DISTINCT no.name1 as host_name, nhs.current_state, icon_image, nh.host_object_id " .
				" FROM " .$obj->ndoPrefix."objects no, " .$obj->ndoPrefix."hoststatus nhs, " .$obj->ndoPrefix."hosts nh ";

	if (!$obj->is_admin)	{
		$rq1 .= ", centreon_acl ";
	}
	$rq1 .=	" WHERE no.objecttype_id = 1 AND nhs.host_object_id = no.object_id AND nh.host_object_id = no.object_id ".
				" AND no.name1 NOT LIKE '_Module_%'";

	if ($o == "svcSum_pb") {
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0)";
	}
	if ($o == "svcSum_ack_0") {
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" .
				")";
	}
	if ($o == "svcSum_ack_1") {
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1 AND nss.current_state != 0" .
				")";
	}
	if ($search != "") {
		$rq1 .= " AND no.name1 LIKE '%" . $search . "%' ";
	}
	if ($instance != -1) {
		$rq1 .= " AND no.instance_id = ".$instance."";
	}
	if ($hostgroups) {
        $rq1 .= " AND EXISTS(SELECT 1 FROM " . $obj->ndoPrefix . "objects as nohg, " . $obj->ndoPrefix . "hostgroup_members as hm, " . $obj->ndoPrefix . "hostgroups as nhg WHERE nohg.objecttype_id = 3 AND nohg.name1 = '" . $hostgroups . "' AND nohg.is_active = '1' AND nohg.object_id = nhg.hostgroup_object_id AND nhg.hostgroup_id = hm.hostgroup_id AND hm.host_object_id = nh.host_object_id) ";
	}
	$rq1 .= $obj->access->queryBuilder("AND", "no.name1", "centreon_acl.host_name").$obj->access->queryBuilder("AND", "group_id", $obj->grouplistStr);

	switch ($sort_type) {
		case 'current_state' : $rq1 .= " order by nhs.current_state ". $order.",no.name1 "; break;
		default : $rq1 .= " order by no.name1 ". $order; break;
	}

	$rq_pagination = $rq1;

	/* ***********************************************
	 * Get Pagination Rows
	 */
	$DBRESULT_PAGINATION = $obj->DBNdo->query($rq_pagination);
	$numRows = $DBRESULT_PAGINATION->numRows();


	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	/*
	 * Info / Pagination
	 */
	$obj->XML->startElement("reponse");
	$obj->XML->startElement("i");
	$obj->XML->writeElement("numrows", $numRows);
	$obj->XML->writeElement("num", $num);
	$obj->XML->writeElement("limit", $limit);
	$obj->XML->writeElement("p", $p);
	$obj->XML->endElement();

	$ct = 0;
	$tab_final = array();
	$DBRESULT_NDO1 = $obj->DBNdo->query($rq1);
	while ($ndo = $DBRESULT_NDO1->fetchRow()){
		$tab_final[$ndo["host_name"]]["nb_service_k"] = 0;
		if ($o != "svcSum_pb" && $o != "svcSum_ack_1"  && $o !=  "svcSum_ack_0") {
			$tab_final[$ndo["host_name"]]["nb_service_k"] = $obj->monObj->getServiceStatusCount($ndo["host_name"], $obj, $o, 0, $obj);
		}
		$tab_final[$ndo["host_name"]]["nb_service_w"] = 0 + $obj->monObj->getServiceStatusCount($ndo["host_name"], $obj, $o, 1, $obj);
		$tab_final[$ndo["host_name"]]["nb_service_c"] = 0 + $obj->monObj->getServiceStatusCount($ndo["host_name"], $obj, $o, 2, $obj);
		$tab_final[$ndo["host_name"]]["nb_service_u"] = 0 + $obj->monObj->getServiceStatusCount($ndo["host_name"], $obj, $o, 3, $obj);
		$tab_final[$ndo["host_name"]]["nb_service_p"] = 0 + $obj->monObj->getServiceStatusCount($ndo["host_name"], $obj, $o, 4, $obj);
		$tab_final[$ndo["host_name"]]["cs"] = $ndo["current_state"];
		$tab_final[$ndo["host_name"]]["host_id"] = $ndo["host_object_id"];
		if ($ndo["icon_image"] != "") {
			$tabIcone[$ndo["host_name"]] = $ndo["icon_image"];
		} else {
			$tabIcone[$ndo["host_name"]] = "none";
		}
	}

	foreach ($tab_final as $host_name => $tab) {
		$obj->XML->startElement("l");
		$obj->XML->writeAttribute("class", $obj->getNextLineClass());
		$obj->XML->writeElement("o", $ct++);
		$obj->XML->writeElement("hn", $host_name, false);
		$obj->XML->writeElement("hnl", urlencode($host_name));
		$obj->XML->writeElement("hid", $tab["host_id"], false);
		$obj->XML->writeElement("ico", $tabIcone[$host_name]);
		$obj->XML->writeElement("hs", _($obj->statusHost[$tab["cs"]]), false);
		$obj->XML->writeElement("hc", $obj->colorHost[$tab["cs"]]);
		$obj->XML->writeElement("sk", $tab["nb_service_k"]);
		$obj->XML->writeElement("skc", $obj->colorService[0]);
		$obj->XML->writeElement("sw", $tab["nb_service_w"]);
		$obj->XML->writeElement("swc", $obj->colorService[1]);
		$obj->XML->writeElement("sc", $tab["nb_service_c"]);
		$obj->XML->writeElement("scc", $obj->colorService[2]);
		$obj->XML->writeElement("su", $tab["nb_service_u"]);
		$obj->XML->writeElement("suc", $obj->colorService[3]);
		$obj->XML->writeElement("sp", $tab["nb_service_p"]);
		$obj->XML->writeElement("spc", $obj->colorService[4]);
		$obj->XML->endElement();
	}

	if (!$ct) {
		$obj->XML->writeElement("infos", "none");
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
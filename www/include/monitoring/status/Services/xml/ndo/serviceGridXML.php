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

	ini_set("display_errors", "Off");

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

	/** *********************************************
	 * Get Host status
	 */
	$rq1 =	  	" SELECT SQL_CALC_FOUND_ROWS DISTINCT no.name1 as host_name, nhs.current_state, icon_image, nh.host_object_id " .
				" FROM " .$obj->ndoPrefix."objects no, " .$obj->ndoPrefix."hoststatus nhs, " .$obj->ndoPrefix."hosts nh ";
	if (!$obj->is_admin) {
		$rq1 	.= ", centreon_acl ";
	}
	$rq1 .=		" WHERE no.objecttype_id = 1 AND nhs.host_object_id = no.object_id AND nh.host_object_id = no.object_id ".
				" AND no.name1 NOT LIKE '_Module_%'";
	$rq1 .= $obj->access->queryBuilder("AND", "no.name1", "centreon_acl.host_name").$obj->access->queryBuilder("AND", "group_id", $obj->grouplistStr);
	if ($o == "svcgrid_pb" || $o == "svcOV_pb" || $o == "svcgrid_ack_0" || $o == "svcOV_ack_0") {
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0)";
	}
	if ($o == "svcgrid_ack_1" || $o == "svcOV_ack_1") {
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1)";
	}
	if ($search != "") {
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
	}
	if ($instance != -1) {
		$rq1 .= " AND no.instance_id = ".$instance."";
	}
	if ($hostgroups) {
	    $rq1 .= " AND EXISTS(SELECT 1 FROM " . $obj->ndoPrefix . "objects as nohg, " . $obj->ndoPrefix . "hostgroup_members as hm, " . $obj->ndoPrefix . "hostgroups as nhg WHERE nohg.objecttype_id = 3 AND nohg.name1 = '" . $hostgroups . "' AND nohg.is_active = '1' AND nohg.object_id = nhg.hostgroup_object_id AND nhg.hostgroup_id = hm.hostgroup_id AND hm.host_object_id = nh.host_object_id) ";
	}
	switch ($sort_type) {
		case 'current_state' : $rq1 .= " order by nhs.current_state ". $order.",no.name1 "; break;
		default : $rq1 .= " order by no.name1 ". $order; break;
	}
	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	/*
	 * Execute request
	 */
	$DBRESULT = $obj->DBNdo->query($rq1);
	$numRows = $obj->DBNdo->numberRows();

	$obj->XML->startElement("reponse");
	$obj->XML->startElement("i");
	$obj->XML->writeElement("numrows", $numRows);
	$obj->XML->writeElement("num", $num);
	$obj->XML->writeElement("limit", $limit);
	$obj->XML->writeElement("p", $p);

	preg_match("/svcOV/",$_GET["o"], $matches) ? $obj->XML->writeElement("s", "1") : $obj->XML->writeElement("s", "0");
	$obj->XML->endElement();

	$tab_final = array();
	$str = "";
	while ($ndo = $DBRESULT->fetchRow()) {
		if ($str != "") {
			$str .= ",";
		}
		$str .= "'".$ndo["host_name"]."'";
		$tab_final[$ndo["host_name"]] = array("cs" => $ndo["current_state"], "hid" => $ndo["host_object_id"]);
		if ($ndo["icon_image"] != "") {
			$tabIcone[$ndo["host_name"]] = $ndo["icon_image"];
		} else {
			$tabIcone[$ndo["host_name"]] = "none";
		}
	}
	$DBRESULT->free();

	/*
	 * Get Service status
	 */
	$tab_svc = $obj->monObj->getServiceStatus($str, $obj, $o, $instance, $hostgroups);
	foreach ($tab_svc as $host_name => $tab) {
		if (count($tab)) {
			$tab_final[$host_name]["tab_svc"] = $tab;
		}
	}

	$ct = 0;
	foreach ($tab_final as $host_name => $tab){
		$obj->XML->startElement("l");
		$obj->XML->writeAttribute("class", $obj->getNextLineClass());
		foreach ($tab["tab_svc"] as $svc => $state) {
			$obj->XML->startElement("svc");
			$obj->XML->writeElement("sn", $svc, false);
			$obj->XML->writeElement("snl", urlencode($svc));
			$obj->XML->writeElement("sc", $obj->colorService[$state]);
			$obj->XML->writeElement("svc_id", getServiceObjectId($svc, $host_name, $obj));
			$obj->XML->endElement();
		}
		$obj->XML->writeElement("o", $ct++);
		$obj->XML->writeElement("ico", $tabIcone[$host_name]);
		$obj->XML->writeElement("hn", $host_name, false);
		$obj->XML->writeElement("hid", $tab["hid"], false);
		$obj->XML->writeElement("hnl", urlencode($host_name));
		$obj->XML->writeElement("hs", _($obj->statusHost[$tab["cs"]]), false);
		$obj->XML->writeElement("hc", $obj->colorHost[$tab["cs"]]);
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
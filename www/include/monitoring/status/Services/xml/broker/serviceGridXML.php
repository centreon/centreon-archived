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

	/** *********************************************
	 * Get Host status
	 */
	$rq1 =	  	" SELECT SQL_CALC_FOUND_ROWS DISTINCT hosts.name, hosts.state, hosts.icon_image, hosts.host_id " .
				" FROM hosts ";
	if ($hostgroups) {
		$rq1 .= ", hosts_hostgroups hg, hostgroups hg2 ";
	}
	if (!$obj->is_admin) {
		$rq1 	.= ", centreon_acl ";
	}
	$rq1 .=		" WHERE hosts.name NOT LIKE '_Module_%' ";
	if (!$obj->is_admin) {
		$rq1 .=		" AND hosts.host_id = centreon_acl.host_id ";
		$rq1 .= $obj->access->queryBuilder("AND", "group_id", $obj->grouplistStr);
	}
	if ($o == "svcgrid_pb" || $o == "svcOV_pb" || $o == "svcgrid_ack_0" || $o == "svcOV_ack_0") {
		$rq1 .= " AND hosts.host_id IN (" .
				" SELECT s.host_id FROM services s " .
				" WHERE s.state != 0 AND s.state != 4 AND s.enabled = 1)";
	}
	if ($o == "svcgrid_ack_1" || $o == "svcOV_ack_1") {
		$rq1 .= " AND hosts.host_id IN (" .
				" SELECT s.host_id FROM services s " .
				" WHERE s.acknowledged = '1' AND s.enabled = 1)";
	}
	if ($search != "") {
		$rq1 .= " AND hosts.name like '%" . $search . "%' ";
	}
	if ($instance != -1) {
		$rq1 .= " AND hosts.instance_id = ".$instance."";
	}
	if ($hostgroups) {
	    $rq1 .= " AND hosts.host_id = hg.host_id ";
	    $rq1 .= " AND hg.hostgroup_id IN (".$hostgroups.") ";
	    $rq1 .= " AND hg.hostgroup_id = hg2.hostgroup_id ";
	    $rq1 .= " AND hg2.enabled = 1 ";
	}
	$rq1 .= " AND hosts.enabled = 1 ";

	switch ($sort_type) {
		case 'current_state' : $rq1 .= " ORDER BY hosts.state ". $order.",hosts.name "; break;
		default : $rq1 .= " ORDER BY hosts.name ". $order; break;
	}
	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	/*
	 * Execute request
	 */
	$DBRESULT = $obj->DBC->query($rq1);
	$numRows = $obj->DBC->numberRows();

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
		$str .= "'".$ndo["name"]."'";
		$tab_final[$ndo["name"]] = array("cs" => $ndo["state"], "hid" => $ndo["host_id"]);
		if ($ndo["icon_image"] != "") {
			$tabIcone[$ndo["name"]] = $ndo["icon_image"];
		} else {
			$tabIcone[$ndo["name"]] = "none";
		}
	}
	$DBRESULT->free();

	/*
	 * Get Service status
	 */
	$tab_svc = $obj->monObj->getServiceStatus($str, $obj, $o, $instance, $hostgroups);
	if (isset($tab_svc)) {
		foreach ($tab_svc as $host_name => $tab) {
			if (count($tab)) {
				$tab_final[$host_name]["tab_svc"] = $tab;
			}
		}
	}

	$ct = 0;
	if (isset($tab_svc)) {
		foreach ($tab_final as $host_name => $tab){
			$obj->XML->startElement("l");
			$obj->XML->writeAttribute("class", $obj->getNextLineClass());
			if (isset($tab["tab_svc"])) {
				foreach ($tab["tab_svc"] as $svc => $state) {
					$obj->XML->startElement("svc");
					$obj->XML->writeElement("sn", $svc, false);
					$obj->XML->writeElement("snl", urlencode($svc));
					$obj->XML->writeElement("sc", $obj->colorService[$state]);
					$obj->XML->writeElement("svc_id", $svcObj->getServiceId($svc, $host_name));
					$obj->XML->endElement();
				}
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
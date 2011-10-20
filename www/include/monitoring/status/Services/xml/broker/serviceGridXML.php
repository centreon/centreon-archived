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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/monitoring/status/Services/xml/ndo/serviceGridXML.php $
 * SVN : $Id: serviceGridXML.php 11683 2011-02-14 16:10:44Z jmathis $
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
		$rq1 .= ", hosts_hostgroups hg ";
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
				" WHERE s.state != 0 AND s.enabled = 1)";
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
	    $rq1 .= " AND hg.hostgroup_id = '".$hostgroups."' ";
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
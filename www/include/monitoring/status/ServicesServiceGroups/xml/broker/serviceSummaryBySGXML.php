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

	/** **********************************************
	 * Prepare pagination
	 */

	# We can'use 'group_concat' because of size lim (and we need two field in services: state and description)
	
	$rq1_select1 = "SELECT SQL_CALC_FOUND_ROWS h.host_id ";
	$rq1_from1 = " FROM servicegroups sg, services_servicegroups sgm, services s, hosts h WHERE ";
	$rq1_search1 = $obj->access->queryBuilder("", "h.host_id", $obj->access->getHostsString("ID", $obj->DBC));
    if ($rq1_search1 != "") {
        $rq1_search1 .= " AND ";
    }
	$rq1 = "";
	$rq1 .= " h.host_id = s.host_id ";
	if ($search != ""){
		$rq1 .= " AND h.name like '%" . $search . "%' ";
	}
        $rq1 .= $obj->access->queryBuilder("AND", "s.service_id", $obj->access->getServicesString("ID", $obj->DBC));

	$rq1 .= " AND s.enabled = 1 AND s.service_id = sgm.service_id AND sgm.servicegroup_id = sg.servicegroup_id";

	if ($instance != -1) {
		$rq1 .= " AND h.instance_id = ".$instance;
	}
	if ($o == "svcSumSG_pb" || $o == "svcOVSG_pb") {
		$rq1 .= " AND s.state != 0 AND s.state != 4 " ;
	}
	if ($o == "svcSumSG_ack_0" || $o == "svcOVSG_ack_0") {
		$rq1 .= " AND s.state != 0 AND s.state != 4 AND s.acknowledged = 0" ;
	}
	if ($o == "svcSumSG_ack_1" || $o == "svcOVSG_ack_1") {
		$rq1 .= " AND s.acknowledged = '1'";
	}

	$rq1_group = " GROUP BY h.name";
	
	if ($sort_type == "host_state") {
    	    $rq1_order = " ORDER BY sg.name, h.state $order, h.name";
        } else {
	    $rq1_order = " ORDER BY sg.name, h.name $order";
        }
	$rq1_limit = " LIMIT ".($num * $limit).",".$limit;


	$DBRESULT = $obj->DBC->query($rq1_select1 . $rq1_from1 . $rq1_search1 . $rq1 . $rq1_group . $rq1_order . $rq1_limit);
	$numRows = $obj->DBC->numberRows();

	if ($numRows > 0) {
		$rq1_search2 = " h.host_id IN (";
		$rq1_append = "";
		while ($tab = $DBRESULT->fetchRow()) {
			$rq1_search2 .= $rq1_append . $tab['host_id'];
			$rq1_append = ", ";
		}
		$rq1_search2 .= ") AND ";
	} else {
		$rq1_search2 = "";
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
	$obj->XML->writeElement("p", $p);
	$obj->XML->writeElement("sk", $obj->colorService[0]);
	$obj->XML->writeElement("sw", $obj->colorService[1]);
	$obj->XML->writeElement("sc", $obj->colorService[2]);
	$obj->XML->writeElement("su", $obj->colorService[3]);
	$obj->XML->writeElement("sp", $obj->colorService[4]);
	($o == "svcOVSG") ? $obj->XML->writeElement("s", "1") : $obj->XML->writeElement("s", "0");
	$obj->XML->endElement();

	$rq1_select2 = "SELECT SQL_CALC_FOUND_ROWS h.name as host_name, h.state as host_state, h.icon_image, h.host_id, sg.alias, sg.name AS sg_name, s.state ";
	$DBRESULT = $obj->DBC->query($rq1_select2 . $rq1_from1 . $rq1_search2 . $rq1 . $rq1_order);

	$flag = 0;
	$sg = "";
	$h = "";
	$hid = "";
	$hic = "none";
	$flag = 0;
	$ct = 0;
	$count = 0;
	$nb_service = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
	while ($tab = $DBRESULT->fetchRow()){
		$hs = $tab["host_state"];
		if (($h != "" && $h != $tab["host_name"])) {
			$obj->XML->startElement("h");
			$obj->XML->writeAttribute("class", $obj->getNextLineClass());
			$obj->XML->writeElement("hn", $h, false);
			if ($hic) {
				$obj->XML->writeElement("hico", $hic);
			} else {
				$obj->XML->writeElement("hico", "none");
			}
			$obj->XML->writeElement("hnl", urlencode($h));
			$obj->XML->writeElement("hs", _($obj->statusHost[$hs]));
			$obj->XML->writeElement("hcount", $count);
			$obj->XML->writeElement("hid", $hid);
			$obj->XML->writeElement("hc", $obj->colorHost[$hs]);
			$obj->XML->writeElement("sk", $nb_service[0]);
			$obj->XML->writeElement("sw", $nb_service[1]);
			$obj->XML->writeElement("sc", $nb_service[2]);
			$obj->XML->writeElement("su", $nb_service[3]);
			$obj->XML->writeElement("sp", $nb_service[4]);
			$obj->XML->endElement();
			$host_id = $tab["host_id"];
			$count++;
		}
		if ($sg != $tab["sg_name"]){
			$nb_service = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
			if ($flag) {
				$obj->XML->endElement();
			}
			$sg = $tab["sg_name"];
			$obj->XML->startElement("sg");
			$obj->XML->writeElement("sgn", $tab["sg_name"]);
			$obj->XML->writeElement("o", $ct);
			$flag = 1;
		}
		$ct++;
		if ($h != $tab["host_name"] || $h == "") {
			$nb_service = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
			$h = $tab["host_name"];
			$hid = $tab["host_id"];
			$hic = $tab["icon_image"];
		}
		$nb_service[$tab["state"]]++;
		$sg = $tab["sg_name"];
	}
	if (isset($hs)) {
		$obj->XML->startElement("h");
		$obj->XML->writeAttribute("class", $obj->getNextLineClass());
		$obj->XML->writeElement("hn", $h);
		$obj->XML->writeElement("hid", $hid);
		if ($hic) {
			$obj->XML->writeElement("hico", $hic);
		} else {
			$obj->XML->writeElement("hico", "none");
		}
		$obj->XML->writeElement("hnl", urlencode($h));
		$obj->XML->writeElement("hs", _($obj->statusHost[$hs]));
		$obj->XML->writeElement("hid", $hid);
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

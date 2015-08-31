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

	/*
	 * Alias / Name convertion table
	 */
	$convertTable = array();
    $convertID = array();
    $DBRESULT = $pearDB->query("SELECT hg_id, hg_alias, hg_name FROM hostgroup");
    while ($hg = $DBRESULT->fetchRow()){
		$convertTable[$hg["hg_name"]] = $hg["hg_alias"];
	    $convertID[$hg["hg_alias"]] = $hg["hg_id"];
    }
    $DBRESULT->free();

	/*
	 *  Check Arguments from GET
	 */
	$o 			= $obj->checkArgument("o", $_GET, "h");
	$p			= $obj->checkArgument("p", $_GET, "2");
	$num 		= $obj->checkArgument("num", $_GET, 0);
	$limit 		= $obj->checkArgument("limit", $_GET, 20);
	$instance 	= $obj->checkArgument("instance", $_GET, $obj->defaultPoller);
	$hostgroups = $obj->checkArgument("hostgroups", $_GET, $obj->defaultHostgroups);
	$search 	= $obj->checkArgument("search", $_GET, "");
	$sort_type 	= $obj->checkArgument("sort_type", $_GET, "host_name");
	$order 		= $obj->checkArgument("order", $_GET, "ASC");
	$dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "d/m/Y H:i:s");

	$groupStr = $obj->access->getAccessGroupsString();
	/*
	 * Backup poller selection
	 */
	$obj->setInstanceHistory($instance);
	$obj->setHostGroupsHistory($hostgroups);

	/*
	 * Search string
	 */
	$searchStr = "";
	if ($search != "") {
		$searchStr = " AND nhg.alias LIKE '%$search%' ";
	}

	/*
	 * Host state
	 */
	if ($obj->is_admin) {
		$rq1 = 	"SELECT hgo.name1 as alias, nhs.current_state, count(nhs.host_object_id) AS nb " .
				"FROM ".$obj->ndoPrefix."objects hgo, ".$obj->ndoPrefix."hostgroup_members nhgm " .
						"INNER JOIN ".$obj->ndoPrefix."objects noo ON (noo.object_id = nhgm.host_object_id) " .
						"INNER JOIN ".$obj->ndoPrefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id ";
		if (isset($instance) && $instance > 0) {
		    $rq1 .= " AND nhg.instance_id = " .$obj->DBNdo->escape($instance);
		}
		$rq1 .= ") ";
    	$rq1 .= "INNER JOIN ".$obj->ndoPrefix."objects no ON (noo.name1 = no.name1) " .
						"INNER JOIN ".$obj->ndoPrefix."hoststatus nhs ON (nhs.host_object_id = no.object_id) " .
				"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 1 $searchStr" .
		        "AND nhg.hostgroup_object_id = hgo.object_id " .
				"GROUP BY hgo.name1, nhs.current_state";
	} else {
		$rq1 = 	"SELECT hgo.name1 as alias, nhs.current_state, count(nhs.host_object_id) AS nb " .
				"FROM ".$obj->ndoPrefix."objects hgo, ".$obj->ndoPrefix."hostgroup_members nhgm " .
						"INNER JOIN ".$obj->ndoPrefix."objects noo ON (noo.object_id = nhgm.host_object_id) " .
						"INNER JOIN ".$obj->ndoPrefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id ";
		if (isset($instance) && $instance > 0) {
		    $rq1 .= " AND nhg.instance_id = " .$obj->DBNdo->escape($instance);
		}
		$rq1 .= ") ";
		$rq1 .=	"INNER JOIN ".$obj->ndoPrefix."objects no ON (noo.name1 = no.name1) " .
						"INNER JOIN ".$obj->ndoPrefix."hoststatus nhs ON (nhs.host_object_id = no.object_id) " .
				"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 1 " .
					"AND noo.name1 IN (SELECT host_name FROM centreon_acl WHERE group_id IN (" . $groupStr . ")) " .
					"AND noo.name2 IS NULL $searchStr" .
		        $obj->access->queryBuilder("AND", "hgo.name1", $obj->access->getHostGroupsString("NAME")).
				"AND nhg.hostgroup_object_id = hgo.object_id " .
				"GROUP BY hgo.name1, nhs.current_state";
	}
	$DBRESULT = $obj->DBNdo->query($rq1);
	while ($ndo = $DBRESULT->fetchRow()) {
		if (!isset($stats[$ndo["alias"]]))
			$stats[$ndo["alias"]] = array("h" => array(0=>0,1=>0,2=>0,3=>0), "s" => array(0=>0,1=>0,2=>0,3=>0,3=>0,4=>0));
		$stats[$ndo["alias"]]["h"][$ndo["current_state"]] = $ndo["nb"];
	}
	$DBRESULT->free();

	/*
	 * Get Services request
	 */
	if ($obj->is_admin) {
			$rq2 = 	"SELECT hgo.name1 as alias, nss.current_state, count( nss.service_object_id ) AS nb " .
				"FROM ".$obj->ndoPrefix."objects hgo, ".$obj->ndoPrefix."hostgroup_members nhgm " .
				"INNER JOIN ".$obj->ndoPrefix."objects noo ON ( noo.object_id = nhgm.host_object_id ) " .
				"INNER JOIN ".$obj->ndoPrefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id ";
	        if (isset($instance) && $instance > 0) {
		        $rq2 .= " AND nhg.instance_id = " .$obj->DBNdo->escape($instance);
		    }
		    $rq2 .= ") ";
			$rq2 .= "INNER JOIN ".$obj->ndoPrefix."objects no ON ( noo.name1 = no.name1 ) " .
					"INNER JOIN ".$obj->ndoPrefix."servicestatus nss ON ( nss.service_object_id = no.object_id ) " .
					"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 2 $searchStr " .
					"AND nhg.hostgroup_object_id = hgo.object_id " .
					"GROUP BY hgo.name1, nss.current_state";
	} else {
		$rq2 = 	"SELECT hgo.name1 as alias, nss.current_state, count( nss.service_object_id ) AS nb " .
				"FROM centreon_acl acl, ".$obj->ndoPrefix."objects hgo, ".$obj->ndoPrefix."hostgroup_members nhgm " .
				"INNER JOIN ".$obj->ndoPrefix."objects noo ON ( noo.object_id = nhgm.host_object_id ) " .
				"INNER JOIN ".$obj->ndoPrefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id ";
	    if (isset($instance) && $instance > 0) {
            $rq2 .= " AND nhg.instance_id = " .$obj->DBNdo->escape($instance);
		}
		$rq2 .= ") ";
		$rq2 .= "INNER JOIN ".$obj->ndoPrefix."objects no ON ( noo.name1 = no.name1 ) " .
				"INNER JOIN ".$obj->ndoPrefix."servicestatus nss ON ( nss.service_object_id = no.object_id ) " .
				"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 2 ".
				"AND acl.group_id IN (".$groupStr.") " .
				"AND CONCAT_WS(',', no.name1, no.name2) = CONCAT_WS(',', acl.host_name, acl.service_description) ".
				$searchStr .
		        $obj->access->queryBuilder("AND", "hgo.name1", $obj->access->getHostGroupsString("NAME")).
				"AND nhg.hostgroup_object_id = hgo.object_id " .
				"GROUP BY hgo.name1, nss.current_state";
	}
	$DBRESULT = $obj->DBNdo->query($rq2);
	while ($ndo = $DBRESULT->fetchRow()) {
		if (!isset($stats[$ndo["alias"]])) {
			$stats[$ndo["alias"]] = array("h" => array(0=>0,1=>0,2=>0,3=>0), "s" => array(0=>0,1=>0,2=>0,3=>0,3=>0,4=>0));
		}
		if ($stats[$ndo["alias"]]) {
			$stats[$ndo["alias"]]["s"][$ndo["current_state"]] = $ndo["nb"];
		}
	}

	if ($order == "DESC") {
		ksort($stats);
	} else {
		krsort($stats);
	}

	/*
	 * Get Pagination Rows
	 */
	$numRows = count($stats);

	$obj->XML->startElement("reponse");
	$obj->XML->startElement("i");
	$obj->XML->writeElement("numrows", $numRows);
	$obj->XML->writeElement("num", $num);
	$obj->XML->writeElement("limit", $limit);
	$obj->XML->writeElement("p", $p);
	$obj->XML->endElement();

	$i = 0;
	$ct = 0;
	foreach ($stats as $name => $stat) {
		if (($i < (($num + 1) * $limit) && $i >= (($num) * $limit)) && ((isset($convertTable[$name]) && isset($acl[$convertTable[$name]])) || (!isset($acl))) && $name != "meta_hostgroup") {
			$class = $obj->getNextLineClass();
			if (isset($stat["h"]) && count($stat["h"])) {
				$obj->XML->startElement("l");
				$obj->XML->writeAttribute("class", $class);
				$obj->XML->writeElement("o", $ct++);
				$obj->XML->writeElement("hn", $name . " (".$convertTable[$name].")", false);
				$obj->XML->writeElement("hu", $stat["h"][0]);
				$obj->XML->writeElement("huc", $obj->colorHost[0]);
				$obj->XML->writeElement("hd", $stat["h"][1]);
				$obj->XML->writeElement("hdc", $obj->colorHost[1]);
				$obj->XML->writeElement("hur", $stat["h"][2]);
				$obj->XML->writeElement("hurc", $obj->colorHost[2]);
				$obj->XML->writeElement("sk", $stat["s"][0]);
				$obj->XML->writeElement("skc", $obj->colorService[0]);
				$obj->XML->writeElement("sw", $stat["s"][1]);
				$obj->XML->writeElement("swc", $obj->colorService[1]);
				$obj->XML->writeElement("sc", $stat["s"][2]);
				$obj->XML->writeElement("scc", $obj->colorService[2]);
				$obj->XML->writeElement("su", $stat["s"][3]);
				$obj->XML->writeElement("suc", $obj->colorService[3]);
				$obj->XML->writeElement("sp", $stat["s"][4]);
				$obj->XML->writeElement("spc", $obj->colorService[4]);
				$obj->XML->writeElement("hgurl", "main.php?p=20201&o=svc&hg=".$convertTable[$name]);
				$obj->XML->endElement();
			}
		}
		$i++;
	}

	if (!$ct) {
		$obj->XML->writeElement("infos", "none");
	}
	$obj->XML->endElement();

	$obj->header();
	$obj->XML->output();
?>

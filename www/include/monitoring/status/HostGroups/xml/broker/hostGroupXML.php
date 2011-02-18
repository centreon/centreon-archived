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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/monitoring/status/HostGroups/xml/hostGroupXML.php $
 * SVN : $Id: hostGroupXML.php 11683 2011-02-14 16:10:44Z jmathis $
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
    while ($hg = $DBRESULT->fetchRow()) {
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
		$rq1 = 	"SELECT nhg.alias, nhs.current_state, count(nhs.host_object_id) AS nb " .
				"FROM ".$obj->ndoPrefix."hostgroup_members nhgm " .
						"INNER JOIN ".$obj->ndoPrefix."objects noo ON (noo.object_id = nhgm.host_object_id) " .
						"INNER JOIN ".$obj->ndoPrefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id) " .
						"INNER JOIN ".$obj->ndoPrefix."objects no ON (noo.name1 = no.name1) " .
						"INNER JOIN ".$obj->ndoPrefix."hoststatus nhs ON (nhs.host_object_id = no.object_id) " .
				"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 1 $searchStr" .
				"GROUP BY nhg.alias, nhs.current_state";
	} else {
		$rq1 = 	"SELECT nhg.alias, nhs.current_state, count(nhs.host_object_id) AS nb " .
				"FROM ".$obj->ndoPrefix."hostgroup_members nhgm " .
						"INNER JOIN ".$obj->ndoPrefix."objects noo ON (noo.object_id = nhgm.host_object_id) " .
						"INNER JOIN ".$obj->ndoPrefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id) " .
						"INNER JOIN ".$obj->ndoPrefix."objects no ON (noo.name1 = no.name1) " .
						"INNER JOIN ".$obj->ndoPrefix."hoststatus nhs ON (nhs.host_object_id = no.object_id) " .
				"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 1 " .
					"AND noo.name1 IN (SELECT host_name FROM centreon_acl WHERE group_id IN (" . $groupStr . ")) " .
					"AND noo.name2 IS NULL $searchStr" .
				"GROUP BY nhg.alias, nhs.current_state";
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
			$rq2 = 	"SELECT nhg.alias, nss.current_state, count( nss.service_object_id ) AS nb " .
			"FROM ".$obj->ndoPrefix."hostgroup_members nhgm " .
				"INNER JOIN ".$obj->ndoPrefix."objects noo ON ( noo.object_id = nhgm.host_object_id ) " .
				"INNER JOIN ".$obj->ndoPrefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id) " .
				"INNER JOIN ".$obj->ndoPrefix."objects no ON ( noo.name1 = no.name1 ) " .
				"INNER JOIN ".$obj->ndoPrefix."servicestatus nss ON ( nss.service_object_id = no.object_id ) " .
			"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 2 $searchStr " .
			"GROUP BY nhg.alias, nss.current_state";
	} else {
		$hostStr = $obj->access->getHostsString("NAME", $obj->DBNdo);
		$svcStr = $obj->access->getServicesString("NAME", $obj->DBNdo);
		$rq2 = 	"SELECT nhg.alias, nss.current_state, count( nss.service_object_id ) AS nb " .
				"FROM ".$obj->ndoPrefix."hostgroup_members nhgm " .
				"INNER JOIN ".$obj->ndoPrefix."objects noo ON ( noo.object_id = nhgm.host_object_id ) " .
				"INNER JOIN ".$obj->ndoPrefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id) " .
				"INNER JOIN ".$obj->ndoPrefix."objects no ON ( noo.name1 = no.name1 ) " .
				"INNER JOIN ".$obj->ndoPrefix."servicestatus nss ON ( nss.service_object_id = no.object_id ) " .
				"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 2
				AND no.name1 IN (".$hostStr.") AND no.name2 IN (".$svcStr. ") ". $searchStr .
				"GROUP BY nhg.alias, nss.current_state";
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
		if (($i < (($num + 1) * $limit) && $i >= (($num) * $limit)) && ((isset($convertID[$name]) && isset($acl[$convertID[$name]])) || (!isset($acl))) && $name != "meta_hostgroup") {
			$class = $obj->getNextLineClass();
			if (isset($stat["h"]) && count($stat["h"])) {
				$obj->XML->startElement("l");
				$obj->XML->writeAttribute("class", $class);
				$obj->XML->writeElement("o", $ct++);
				$obj->XML->writeElement("hn", $name, false);
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
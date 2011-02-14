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

	/* **************************************************
	 * Check Arguments From GET tab
	 */
	$o 			= $obj->checkArgument("o", $_GET, "h");
	$p 			= $obj->checkArgument("p", $_GET, "2");
	$hg 		= $obj->checkArgument("hg", $_GET, "");
	$num 		= $obj->checkArgument("num", $_GET, 0);
	$limit 		= $obj->checkArgument("limit", $_GET, 20);
	$instance 	= $obj->checkArgument("instance", $_GET, $obj->defaultPoller);
	$hostgroups = $obj->checkArgument("hostgroups", $_GET, $obj->defaultHostgroups);
	$search 	= $obj->checkArgument("search", $_GET, "");
	$sort_type 	= $obj->checkArgument("sort_type", $_GET, "host_name");
	$order 		= $obj->checkArgument("order", $_GET, "ASC");
	$dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "d/m/Y H:i:s");
	$grouplistStr = $obj->access->getAccessGroupsString();

	/** **************************************
	 * Get Icone list
	 *
	 */
	$hostIcones = array();
	$query = "SELECT no.name1, h.icon_image FROM ".$obj->ndoPrefix."objects no, ".$obj->ndoPrefix."hosts h WHERE no.object_id = h.host_object_id";
	$DBRESULT = $obj->DBNdo->query($query);
	while ($data = $DBRESULT->fetchRow()) {
		$hostIcones[$data['name1']] = $data['icon_image'];
	}
	$DBRESULT->free();

	/** **************************************
	 * Get Host status
	 *
	 */
	$rq1 =	" SELECT SQL_CALC_FOUND_ROWS DISTINCT hg.alias, no.object_id id, no.name1 as host_name, hgm.hostgroup_id, hgm.host_object_id, hs.current_state hs ".
			" FROM " .$obj->ndoPrefix."hostgroups hg," .$obj->ndoPrefix."hostgroup_members hgm, " .$obj->ndoPrefix."hoststatus hs, " .$obj->ndoPrefix."objects no";
	if (!$obj->is_admin) {
		$rq1 .= ", centreon_acl ";
	}
	$rq1 .= " WHERE hs.host_object_id = hgm.host_object_id".
			" AND no.object_id = hgm.host_object_id" .
			" AND hgm.hostgroup_id = hg.hostgroup_id".
			" AND no.name1 not like '_Module_%'";
	if (!$obj->is_admin) {
		$rq1 .= $obj->access->queryBuilder("AND", "no.name1", "centreon_acl.host_name") . $obj->access->queryBuilder("AND", "group_id", $grouplistStr) . " " . $obj->access->queryBuilder("AND", "hg.alias", $obj->access->getHostGroupsString("ALIAS"));
	}
	if ($instance != -1) {
		$rq1 .= 	" AND no.instance_id = ".$instance;
	}
	if ($o == "svcgrid_pb" || $o == "svcOVHG_pb") {
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0)";
	}
	if ($o == "svcOVHG_ack_0") {
		$rq1 .= 	" AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0)";
	}
	if ($o == "svcOVHG_ack_1"){
		$rq1 .= 	" AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1 AND nss.current_state != 0)";
	}
	if ($search != "") {
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
	}
	if ($hg != "") {
		$rq1 .= " AND hg.alias = '" . $hg . "'";
	}
	$rq1 .= " ORDER BY $sort_type, host_name $order ";
	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	$tabH = array();
	$tabHG = array();
	$tab_finalH = array();

	$DBRESULT_NDO1 = $obj->DBNdo->query($rq1);
	$numRows = $obj->DBNdo->numberRows();
	while ($ndo = $DBRESULT_NDO1->fetchRow())	{
		if (!isset($tab_finalH[$ndo["alias"]])) {
			$tab_finalH[$ndo["alias"]] = array($ndo["host_name"] => array());
		}
		$tab_finalH[$ndo["alias"]][$ndo["host_name"]]["cs"] = $ndo["hs"];
		$tab_finalH[$ndo["alias"]][$ndo["host_name"]]["tab_svc"] = array();
		$tabH[$ndo["host_name"]] = $ndo["id"];
		$tabHG[$ndo["alias"]] = $ndo["hostgroup_id"];
	}
	$DBRESULT_NDO1->free();


	/** **************************************
	 * Get Services status
	 *
	 */
	$rq1 = 	" SELECT DISTINCT no.object_id id, no.name1 as host_name, no.name2 svc_name, nss.current_state svcs " .
		 	" FROM ".$obj->ndoPrefix."objects no, ".$obj->ndoPrefix."servicestatus nss ";
	if (!$obj->is_admin) {
		$rq1 .= ", centreon_acl ";
	}
	$rq1 .=  " WHERE no.objecttype_id = '2' AND nss.service_object_id = no.object_id ".
			 " AND no.name1 NOT LIKE '_Module_%' ".
			 " AND no.is_active = 1 ";
	$rq1 .= $obj->access->queryBuilder("AND", "no.name1", "centreon_acl.host_name") . $obj->access->queryBuilder("AND","no.name2", "centreon_acl.service_description") . $obj->access->queryBuilder("AND", "group_id", $grouplistStr);
	if ($o == "svcgrid_pb" || $o == "svcOVHG_pb" || $o == "svcgrid_ack_0" || $o == "svcOVHG_ack_0") {
		$rq1 .= " AND nss.current_state != 0 ";
	}
	if ($o == "svcgrid_ack_1" || $o == "svcOVHG_ack_1") {
		$rq1 .= "AND nss.problem_has_been_acknowledged = 1";
	}
	if ($o == "svcgrid_ack_0" || $o == "svcOVHG_ack_0") {
		$rq1 .= "AND nss.problem_has_been_acknowledged = 0";
	}
	if ($search != "") {
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
	}
	if ($instance != -1) {
		$rq1 .= " AND no.instance_id = ".$instance;
	}
	$rq1 .= " ORDER BY svc_name";

	$tabService = array();
	$tabHost = array();
	$DBRESULT_NDO1 = $obj->DBNdo->query($rq1);
	while ($ndo = $DBRESULT_NDO1->fetchRow())	{
		if (!isset($tabService[$ndo["host_name"]])) {
			$tabService[$ndo["host_name"]] = array();
		}
		if (!isset($tabService[$ndo["host_name"]])) {
			$tabService[$ndo["host_name"]] = array("tab_svc" => array());
		}
		$tabService[$ndo["host_name"]]["tab_svc"][$ndo["svc_name"]] = $ndo["svcs"];
		$tabHost[$ndo["host_name"]] = $ndo["id"];
	}
	$DBRESULT_NDO1->free();

	/*
	 * Begin XML Generation
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
	$o == "svcOVHG" ? $obj->XML->writeElement("s", "1") : $obj->XML->writeElement("s", "0");
	$obj->XML->endElement();

	$ct = 0;
	$hg = "";
	$count = 0;
	if (isset($tab_finalH)) {
		foreach ($tab_finalH as $hg_name => $tab_host) {
			foreach ($tab_host as $host_name => $tab) {
				if (isset($tabService[$host_name]["tab_svc"]) && count($tabService[$host_name]["tab_svc"])) {
					if (isset($hg_name) && $hg != $hg_name){
						if ($hg != "") {
							$obj->XML->endElement();
						}
						$hg = $hg_name;
						$obj->XML->startElement("hg");
						$obj->XML->writeElement("hgn", $hg_name);
						$obj->XML->writeElement("hgid", $tabHG[$hg_name]);
					}
					$obj->XML->startElement("l");
					$obj->XML->writeAttribute("class", $obj->getNextLineClass());
					if (isset($tabService[$host_name]["tab_svc"])) {
						foreach ($tabService[$host_name]["tab_svc"] as $svc => $state) {
							$obj->XML->startElement("svc");
							$obj->XML->writeElement("sn", $svc);
							$obj->XML->writeElement("snl", urlencode($svc));
							$obj->XML->writeElement("sc", $obj->colorService[$state]);
							$obj->XML->endElement();
						}
					}
					$obj->XML->writeElement("o", $ct);
					$obj->XML->writeElement("hn", $host_name, false);
					if (isset($hostIcones[$host_name]) && $hostIcones[$host_name]) {
						$obj->XML->writeElement("hico", $hostIcones[$host_name]);
					} else {
						$obj->XML->writeElement("hico", "none");
					}
					$obj->XML->writeElement("hnl", urlencode($host_name));
					$obj->XML->writeElement("hid", $tabH[$host_name]);
					$obj->XML->writeElement("hs", $obj->statusHost[$tab["cs"]]);
					$obj->XML->writeElement("hc", $obj->colorHost[$tab["cs"]]);
					$obj->XML->writeElement("hcount", $count);
					$obj->XML->endElement();
					$count++;
				}
			}
			$ct++;
		}
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
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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/monitoring/status/Hosts/xml/hostXML.php $
 * SVN : $Id: hostXML.php 9401 2009-11-20 09:18:02Z shotamchay $
 *
 */

	//include_once "@CENTREON_ETC@/centreon.conf.php";
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

	/*
	 * Backup poller selection
	 */
	$obj->setInstanceHistory($instance);
	$obj->setHostGroupsHistory($hostgroups);

	/*
	 * Get Host status
	 */
	$rq1 = 	" SELECT SQL_CALC_FOUND_ROWS DISTINCT nhs.current_state," .
			" nhs.problem_has_been_acknowledged, " .
			" nhs.passive_checks_enabled," .
			" nhs.active_checks_enabled," .
			" nhs.notifications_enabled," .
			" unix_timestamp(nhs.last_state_change) as last_state_change," .
			" unix_timestamp(nhs.last_hard_state_change) as last_hard_state_change," .
			" nhs.output," .
			" unix_timestamp(nhs.last_check) as last_check," .
			" nh.address," .
			" no.name1 as host_name," .
			" nh.action_url," .
			" nh.notes_url," .
			" nh.notes," .
			" nh.icon_image," .
			" nh.icon_image_alt," .
			" nhs.max_check_attempts," .
			" nhs.state_type," .
			" nhs.current_check_attempt, " .
			" nhs.scheduled_downtime_depth, " .
			" nh.host_object_id " .
			" FROM ".$obj->ndoPrefix."hoststatus nhs, ".$obj->ndoPrefix."objects no, ".$obj->ndoPrefix."hosts nh";
	if (!$obj->is_admin) {
		$rq1 .= ", centreon_acl ";
	}
	if ($hostgroups) {
		$rq1 .= ", ".$obj->ndoPrefix."hostgroup_members hm ";
	}
	$rq1 .= " WHERE no.object_id = nhs.host_object_id AND nh.host_object_id = no.object_id " .
			" AND no.is_active = 1 AND no.objecttype_id = 1 " .
			" AND no.name1 NOT LIKE '_Module_%'";

	if (!$obj->is_admin) {
		$rq1 .= $obj->access->queryBuilder("AND", "no.name1", "centreon_acl.host_name") . $obj->access->queryBuilder("AND", "centreon_acl.group_id", $obj->grouplistStr);
	}
	if ($search != "") {
		$rq1 .= " AND (no.name1 LIKE '%" . $search . "%' OR nh.alias LIKE '%" . $search . "%') ";
	}
	if ($o == "hpb") {
		$rq1 .= " AND nhs.current_state != 0 ";
	}
	if ($o == "h_unhandled") {
		$rq1 .= " AND nhs.current_state != 0 ";
		$rq1 .= " AND nhs.state_type = '1'";
		$rq1 .= " AND nhs.problem_has_been_acknowledged = 0";
		$rq1 .= " AND nhs.scheduled_downtime_depth = 0";
	}
	if ($hostgroups) {
		$rq1 .= " AND nh.host_object_id = hm.host_object_id AND hm.hostgroup_id IN
				(SELECT hostgroup_id FROM ".$obj->ndoPrefix."hostgroups WHERE alias LIKE '".$hostgroups."') ";
	}

	if ($instance != -1) {
		$rq1 .= " AND no.instance_id = ".$instance;
	}
	switch ($sort_type) {
		case 'host_name' :
			$rq1 .= " order by no.name1 ". $order;
			break;
		case 'current_state' :
			$rq1 .= " order by nhs.current_state ". $order.",no.name1 ";
			break;
		case 'last_state_change' :
			$rq1 .= " order by nhs.last_state_change ". $order.",no.name1 ";
			break;
		case 'last_hard_state_change' :
			$rq1 .= " order by nhs.last_hard_state_change ". $order.",no.name1 ";
			break;
		case 'last_check' :
			$rq1 .= " order by nhs.last_check ". $order.",no.name1 ";
			break;
		case 'current_check_attempt' :
			$rq1 .= " order by nhs.current_check_attempt ". $order.",no.name1 ";
			break;
		case 'ip' :
			$rq1 .= " order by nh.address ". $order.",no.name1 ";
			break;
		case 'plugin_output' :
			$rq1 .= " order by nhs.output ". $order.",no.name1 ";
			break;
		default :
			$rq1 .= " order by no.name1 ";
			break;
	}
	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	$ct = 0;
	$flag = 0;
	$DBRESULT =& $obj->DBNdo->query($rq1);
	$numRows = $obj->DBNdo->numberRows();

	$obj->XML->startElement("reponse");
	$obj->XML->startElement("i");
	$obj->XML->writeElement("numrows", $numRows);
	$obj->XML->writeElement("num", $num);
	$obj->XML->writeElement("limit", $limit);
	$obj->XML->writeElement("p", $p);
	$obj->XML->writeElement("o", $o);
	$obj->XML->writeElement("hard_state_label", _("Hard State Duration"));
	$obj->XML->endElement();

	while ($ndo =& $DBRESULT->fetchRow()) {

		if ($ndo["last_state_change"] > 0 && time() > $ndo["last_state_change"]) {
			$duration = CentreonDuration::toString(time() - $ndo["last_state_change"]);
		} else {
			$duration = "N/A";
		}

		if (($ndo["last_hard_state_change"] > 0) && ($ndo["last_hard_state_change"] >= $ndo["last_state_change"])) {
			$hard_duration = CentreonDuration::toString(time() - $ndo["last_hard_state_change"]);
		} else if ($ndo["last_hard_state_change"] > 0) {
			$hard_duration = " N/A ";
		} else {
			$hard_duration = "N/A";
		}

		$obj->XML->startElement("l");
		$obj->XML->writeAttribute("class", $obj->getNextLineClass());
		$obj->XML->writeElement("o", 	$ct++);
		$obj->XML->writeElement("hc", 	$obj->colorHost[$ndo["current_state"]]);
		$obj->XML->writeElement("f", 	$flag);
		$obj->XML->writeElement("hid",	$ndo["host_object_id"]);
		$obj->XML->writeElement("hn",	$ndo["host_name"], false);
		$obj->XML->writeElement("hnl",	urlencode($ndo["host_name"]));
		$obj->XML->writeElement("a", 	($ndo["address"] ? $ndo["address"] : "N/A"));
		$obj->XML->writeElement("ou", 	($ndo["output"] ? $ndo["output"] : "N/A"));
		$obj->XML->writeElement("lc", 	($ndo["last_check"] != 0 ? $obj->GMT->getDate($dateFormat, $ndo["last_check"]) : "N/A"));
		$obj->XML->writeElement("cs", 	_($obj->statusHost[$ndo["current_state"]]), false);
		$obj->XML->writeElement("pha", 	$ndo["problem_has_been_acknowledged"]);
        $obj->XML->writeElement("pce", 	$ndo["passive_checks_enabled"]);
        $obj->XML->writeElement("ace", 	$ndo["active_checks_enabled"]);
        $obj->XML->writeElement("lsc", 	($duration ? $duration : "N/A"));
        $obj->XML->writeElement("lhs", 	($hard_duration ? $hard_duration : "N/A"));
        $obj->XML->writeElement("ha", 	$ndo["problem_has_been_acknowledged"]);
        $obj->XML->writeElement("hdtm", $ndo["scheduled_downtime_depth"]);
        $obj->XML->writeElement("hae", 	$ndo["active_checks_enabled"]);
        $obj->XML->writeElement("hpe", 	$ndo["passive_checks_enabled"]);
        $obj->XML->writeElement("ne", 	$ndo["notifications_enabled"]);
        $obj->XML->writeElement("tr", 	$ndo["current_check_attempt"]."/".$ndo["max_check_attempts"]." (".$obj->stateType[$ndo["state_type"]].")");
        $obj->XML->writeElement("ico", 	$ndo["icon_image"]);

        $hostObj = new CentreonHost($obj->DB);
		if ($ndo["notes"] != "") {
			$obj->XML->writeElement("hnn", $hostObj->replaceMacroInString($ndo["name1"], str_replace("\$HOSTNAME\$", $ndo["host_name"], str_replace("\$HOSTADDRESS\$", $ndo["address"], $ndo["notes"]))));
		} else {
			$obj->XML->writeElement("hnn", "none");
		}

		if ($ndo["notes_url"] != "") {
			$obj->XML->writeElement("hnu", $hostObj->replaceMacroInString($ndo["name1"], str_replace("\$HOSTNAME\$", $ndo["host_name"], str_replace("\$HOSTADDRESS\$", $ndo["address"], str_replace("\$HOSTNOTES\$", $ndo["notes"], $ndo["notes_url"])))));
		} else {
			$obj->XML->writeElement("hnu", "none");
		}
		$obj->XML->endElement();
	}
	$DBRESULT->free();

	if (!$ct) {
		$obj->XML->writeElement("infos", "none");
	}
	$obj->XML->endElement();

	$obj->header();
	$obj->XML->output();
?>
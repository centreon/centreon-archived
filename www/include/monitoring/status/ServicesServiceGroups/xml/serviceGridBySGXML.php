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
 * SVN : $URL
 * SVN : $Id
 *
 */

	/*
	 * if debug == 0 => Normal,
	 * debug == 1 => get use,
	 * debug == 2 => log in file (log.xml)
	 */
	$debugXML = 0;
	$buffer = '';

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path."www/class/centreonDuration.class.php");
	include_once($centreon_path."www/class/centreonACL.class.php");
	include_once($centreon_path."www/class/centreonXML.class.php");
	include_once($centreon_path."www/class/centreonDB.class.php");
	include_once($centreon_path."www/include/monitoring/status/Common/common-Func.php");
	include_once($centreon_path."www/include/common/common-Func.php");

	$pearDB = new CentreonDB();
	$pearDBndo = new CentreonDB("ndo");

	$ndo_base_prefix = getNDOPrefix();
	$general_opt = getStatusColor($pearDB);

	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid, ENT_QUOTES);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session =& $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');


	/* requisit */
	(isset($_GET["instance"]) && !check_injection($_GET["instance"])) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["num"]) && !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) && !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');

	/*
	 * options
	 */

	(isset($_GET["search"]) && !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) && !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "host_name";
	(isset($_GET["order"]) && !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $oreder = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) && !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) && !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";

	// check is admin
	$is_admin = isUserAdmin($sid);

	$user_id = getUserIdFromSID($sid);
	$access = new CentreonACL($user_id, $is_admin);
	$grouplist = $access->getAccessGroups();
	$grouplistStr = $access->getAccessGroupsString();
	$groupnumber = count($grouplist);

	function get_services($host_name){
		global $pearDBndo,$ndo_base_prefix, $lcaSGStr, $instance, $general_opt, $o;

		$rq = "SELECT no.name1, no.name2 as service_name, nss.current_state" .
				" FROM `" .$ndo_base_prefix."servicestatus` nss, `" .$ndo_base_prefix."objects` no" .
				" WHERE no.object_id = nss.service_object_id " ;

		if ($instance != "ALL")
			$rq .= " AND no.instance_id = ".$instance;

		if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb" || $o == "svcOVSG_ack_0")
			$rq .= " AND nss.current_state != 0 " ;

		if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0")
			$rq .= " AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0 " ;

		if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1")
			$rq .= " AND nss.problem_has_been_acknowledged = 1 " ;

		$rq .= " AND no.object_id" .
				" IN (" .
				" SELECT nno.object_id" .
				" FROM ".$ndo_base_prefix."objects nno" .
				" WHERE nno.objecttype_id =2" .
				" AND nno.name1 = '".$host_name."')";

		$DBRESULT =& $pearDBndo->query($rq);
		$tab = array();
		while ($svc =& $DBRESULT->fetchRow()) {
			$tab[$svc["service_name"]] = $svc["current_state"];
		}
		return($tab);
	}

	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();

	$tab_color_service = array();
	$tab_color_service[0] = $general_opt["color_ok"];
	$tab_color_service[1] = $general_opt["color_warning"];
	$tab_color_service[2] = $general_opt["color_critical"];
	$tab_color_service[3] = $general_opt["color_unknown"];
	$tab_color_service[4] = $general_opt["color_pending"];

	$tab_color_host = array();
	$tab_color_host[0] = $general_opt["color_up"];
	$tab_color_host[1] = $general_opt["color_down"];
	$tab_color_host[2] = $general_opt["color_unreachable"];
	$tab_color_host[3] = $general_opt["color_unreachable"];

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");

	/*
	 * Prepare pagination
	 */
	$rq1 = "SELECT DISTINCT no.name1 as host_name, sg.alias".
		" FROM " .$ndo_base_prefix."servicegroups sg," .$ndo_base_prefix."servicegroup_members sgm, " .$ndo_base_prefix."servicestatus ss, " .$ndo_base_prefix."objects no".
		" WHERE ss.service_object_id = sgm.service_object_id".
		" AND no.object_id = sgm.service_object_id" .
		" AND sgm.servicegroup_id = sg.servicegroup_id" .
		" AND no.is_active = 1 AND no.objecttype_id = 2";

	$rq1 .= $access->queryBuilder("AND", "sg.alias", $access->getServiceGroupsString("ALIAS"));

	if ($instance != "ALL")
		$rq1 .= " AND no.instance_id = ".$instance;

	if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb")
		$rq1 .= " AND ss.current_state != 0" ;

	if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0")
		$rq1 .= " AND ss.current_state != 0 AND ss.problem_has_been_acknowledged = 0" ;

	if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1")
		$rq1 .= " AND no.name1 IN (" .
			" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
			" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1" .
			")";
	if ($search != ""){
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
	}

	$DBRESULT_PAGINATION =& $pearDBndo->query($rq1);
	$numRows = 0;
	$tabString = "";
	while ($row =& $DBRESULT_PAGINATION->fetchRow()) {
		$numRows++;
		if ($tabString != "")
			$tabString .= ",";
		$tabString .= "'".$row['host_name']."'";
	}
	unset($row);
	$DBRESULT_PAGINATION->free();

	if ($numRows) {

		/*
		 * Check ndo version
		 */
		$request = "SELECT count(*) FROM " .$ndo_base_prefix."servicegroups WHERE config_type = '1' LIMIT 1";
		$DBRESULT =& $pearDBndo->query($request);
		while ($row =& $DBRESULT->fetchRow()) {
			if ($row["count(*)"] > 0) {
				$custom_ndo = 0;
				break;
			} else {
				$request = "SELECT count(*) FROM " .$ndo_base_prefix."servicegroups LIMIT 1";
				$DBRESULT2 =& $pearDBndo->query($request);
				while ($row2 =& $DBRESULT2->fetchRow()) {
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

		/*
		 * Create Buffer for host status
		 */
		$rq = "SELECT nhs.current_state, nh.display_name FROM `" .$ndo_base_prefix."hoststatus` nhs, `" .$ndo_base_prefix."hosts` nh " .
 	            "WHERE nh.display_name IN ($tabString) " .
 	            "AND nh.host_object_id = nhs.host_object_id" ;
		$DBRESULT =& $pearDBndo->query($rq);
		$tabHostStatus = array();
		while ($row =& $DBRESULT->fetchRow()) {
			$tabHostStatus[$row["display_name"]] = $row["current_state"];
		}
		unset($row);
		$DBRESULT->free();

		$rq1 = "SELECT DISTINCT sg.alias, no.name1 as host_name".
		" FROM " .$ndo_base_prefix."servicegroups sg," .$ndo_base_prefix."servicegroup_members sgm, " .$ndo_base_prefix."servicestatus ss, " .$ndo_base_prefix."objects no".
		" WHERE ss.service_object_id = sgm.service_object_id";
		if ($custom_ndo == 0)
			$rq1 .= " AND sg.config_type = 1";

		$rq1 .= " AND no.object_id = sgm.service_object_id" .
		" AND sgm.servicegroup_id = sg.servicegroup_id" .
		" AND no.is_active = 1 AND no.objecttype_id = 2";

		$rq1 .= $access->queryBuilder("AND", "sg.alias", $access->getServiceGroupsString("ALIAS"));

		if ($instance != "ALL")
			$rq1 .= " AND no.instance_id = ".$instance;

		if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb")
			$rq1 .= " AND ss.current_state != 0" ;

		if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0")
			$rq1 .= " AND ss.current_state != 0 AND ss.problem_has_been_acknowledged = 0" ;

		if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1")
			$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1" .
				")";
		if ($search != ""){
			$rq1 .= " AND no.name1 like '%" . $search . "%' ";
		}
		$rq1 .= " ORDER BY sg.alias, host_name " . $order;
		$rq1 .= " LIMIT ".($num * $limit).",".$limit;

		$DBRESULT_PAGINATION =& $pearDBndo->query($rq1);
		$host_table = array();
		$sg_table = array();
		while ($row =& $DBRESULT_PAGINATION->fetchRow()) {
		    $host_table[$row["host_name"]] = $row["host_name"];
			if (!isset($sg_table[$row["alias"]]))
            	$sg_table[$row["alias"]] = array();
        	$sg_table[$row["alias"]][$row["host_name"]] = $row["host_name"];
		}
		$DBRESULT_PAGINATION->free();

		/*
		 * Create Host list string
		 */
		$hostList = "";
		foreach ($host_table as $host_name) {
			if ($hostList != "")
			   $hostList .= ",";
			$hostList .= "'".$host_name."'";
		}

		/*
		 * Prepare Finale Request
		 */
		$rq1 =	"SELECT sg.alias, no.name1 as host_name, no.name2 as service_description, sgm.servicegroup_id, sgm.service_object_id, ss.current_state".
			" FROM " .$ndo_base_prefix."servicegroups sg," .$ndo_base_prefix."servicegroup_members sgm, " .$ndo_base_prefix."servicestatus ss, " .$ndo_base_prefix."objects no".
			" WHERE ss.service_object_id = sgm.service_object_id";
			if ($custom_ndo == 0)
				$rq1 .= " AND sg.config_type = 1";

			$rq1 .= " AND no.object_id = sgm.service_object_id" .
			" AND sgm.servicegroup_id = sg.servicegroup_id" .
			" AND no.name1 IN ($hostList)" .
			" AND no.is_active = 1 AND no.objecttype_id = 2";

		$rq1 .= $access->queryBuilder("AND", "sg.alias", $access->getServiceGroupsString("ALIAS"));

		if ($instance != "ALL")
			$rq1 .= " AND no.instance_id = ".$instance;

		if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb")
			$rq1 .= " AND ss.current_state != 0" ;

		if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0")
			$rq1 .= " AND ss.current_state != 0 AND ss.problem_has_been_acknowledged = 0" ;

		if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1")
			$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1" .
					")";
		if ($search != ""){
			$rq1 .= " AND no.name1 like '%" . $search . "%' ";
		}
		$rq1 .= " ORDER BY sg.alias, host_name, service_description " . $order;
	}
	/*
	 * Init Buffer
	 */

	$buffer = new CentreonXML();
	$buffer->startElement("reponse");
	$buffer->startElement("i");
	$buffer->writeElement("numrows", $numRows);
	$buffer->writeElement("num", $num);
	$buffer->writeElement("limit", $limit);
	$buffer->writeElement("host_name", _("Hosts"), 0);
	$buffer->writeElement("services", _("Services"), 0);
	$buffer->writeElement("p", $p);
	($o == "svcOVSG") ? $buffer->writeElement("s", "1")  : $buffer->writeElement("s", "0");
	$buffer->endElement();

	$class = "list_one";
	$ct = 0;
	$flag = 0;

	$sg = "";
	$h = "";
	$flag = 0;
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	while ($tab =& $DBRESULT_NDO1->fetchRow() && $numRows){
		if (isset($sg_table[$tab["alias"]]) && isset($sg_table[$tab["alias"]][$tab["host_name"]]) && isset($host_table[$tab["host_name"]])) {
			$class == "list_one" ? $class = "list_two" : $class = "list_one";
			if ($sg != $tab["alias"]){
				$flag = 0;
				if ($sg != "") {
					$buffer->endElement();
					$buffer->endElement();
				}
				$sg = $tab["alias"];
				$h = "";
				$buffer->startElement("sg");
				$buffer->writeElement("sgn", $tab["alias"]);
				$buffer->writeElement("o", $ct);
			}
			$ct++;

			if ($h != $tab["host_name"]){
				if ($h != "" && $flag)
					$buffer->endElement();
				$flag = 1;
				$h = $tab["host_name"];
				$hs = $tabHostStatus[$tab["host_name"]];
				$buffer->startElement("h");
				$buffer->writeAttribute("class", $class);
				$buffer->writeElement("hn", $tab["host_name"]);
				$buffer->writeElement("hnl", urlencode($tab["host_name"]));
				$buffer->writeElement("hs", $tab_status_host[$hs]);
				$buffer->writeElement("hc", $tab_color_host[$hs]);
			}
			$buffer->startElement("svc");
			$buffer->writeElement("sn", $tab["service_description"]);
			$buffer->writeElement("snl", urlencode($tab["service_description"]));
			$buffer->writeElement("sc", $tab_color_service[$tab["current_state"]]);
			$buffer->endElement();
		}
	}
	if ($sg != "") {
		$buffer->endElement();
		$buffer->endElement();
	}
	$buffer->endElement();

	header('Content-Type: text/xml');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate');
	$buffer->output();
?>
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	/*
	 * if debug == 0 => Normal,
	 * debug == 1 => get use,
	 * debug == 2 => log in file (log.xml)
	 */
	$debugXML = 0;
	$buffer = '';

	//include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once("/etc/centreon/centreon.conf.php");

	include_once($centreon_path."www/class/centreonDuration.class.php");
	include_once($centreon_path."www/class/centreonACL.class.php");
	include_once($centreon_path."www/class/centreonXML.class.php");
	include_once($centreon_path."www/class/centreonDB.class.php");
	include_once $centreon_path."www/include/monitoring/status/Common/common-Func.php";
	include_once($centreon_path."www/include/common/common-Func.php");

	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");
	$pearDBndo 	= new CentreonDB("ndo");

	$ndo_base_prefix = getNDOPrefix();
	$general_opt = getStatusColor($pearDB);

	/*
	 * security check 2/2
	 */

	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])) {
		$sid = $_GET["sid"];
		$sid = htmlentities($sid, ENT_QUOTES, "UTF-8");
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session =& $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	/*
	 * Get Acl Group list
	 */
	$is_admin = isUserAdmin($sid);
	$user_id = getUserIdFromSID($sid);
	$access = new CentreonACL($user_id, $is_admin);

	(isset($_GET["num"]) 		&& !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) 		&& !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	(isset($_GET["instance"])/* && !check_injection($_GET["instance"])*/) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["search"]) 	&& !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) 	&& !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "service_description";
	(isset($_GET["search_type_host"]) 		&& !check_injection($_GET["search_type_host"])) ? $search_type_host = htmlentities($_GET["search_type_host"]) : $search_type_host = 1;
	(isset($_GET["search_type_service"])	&& !check_injection($_GET["search_type_service"])) ? $search_type_service = htmlentities($_GET["search_type_service"]) : $search_type_service = 1;
	(isset($_GET["order"]) 		&& !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $oreder = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) 			&& !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) 			&& !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";
	(isset($_GET["nc"]) 		&& !check_injection($_GET["nc"])) ? $nc = htmlentities($_GET["nc"]) : $nc = "0";

	if (!$is_admin)
		$_POST["sid"] = $sid;

	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();

	$tab_color_service = array(0 => $general_opt["color_ok"], 1 => $general_opt["color_warning"], 2 => $general_opt["color_critical"], 3 => $general_opt["color_unknown"], 4 => $general_opt["color_pending"]);
	$tab_color_host = array(0 => "normal", 1 => "#FD8B46", /* $general_opt["color_down"];*/ 2 => "normal");

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");

	/* Get Service status */
	$rq =		" SELECT " .
				" DISTINCT no.name1 as host_name," .
				" nss.process_performance_data," .
				" nss.current_state," .
				" nss.output as plugin_output," .
				" nss.current_check_attempt as current_attempt," .
				" nss.status_update_time as status_update_time," .
				" unix_timestamp(nss.last_state_change) as last_state_change," .
				" unix_timestamp(nss.last_check) as last_check," .
				" unix_timestamp(nss.next_check) as next_check," .
				" nss.notifications_enabled," .
				" nss.problem_has_been_acknowledged," .
				" nss.passive_checks_enabled," .
				" nss.active_checks_enabled," .
				" nss.event_handler_enabled," .
				" nss.is_flapping," .
				" nss.flap_detection_enabled," .
				" no.object_id," .
				" no.name2 as service_description" .
				" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no";

	$rq .= 	" WHERE no.object_id = nss.service_object_id".
			" AND no.name1 LIKE '_Module_Meta'" .
			" AND no.is_active = 1" .
		  	" AND objecttype_id = 2";

	if (!$is_admin) {
		$ACLString = "";
		foreach ($access->getMetaServices() as $key => $empty) {
			if ($ACLString != "")
				$ACLString .= ",";
			$ACLString .= "'meta_".$key."'";
		}
		if ($ACLString == "")
			$ACLString = "''";
		$rq .= " AND no.name2 IN (".$ACLString.") AND no.name1 LIKE '_Module_Meta' ";
	}
	if ($search_type_host && $search_type_service && $search){
		$rq .= " AND ( no.name1 like '%" . $search . "%' OR no.name2 like '%" . $search . "%' OR nss.output like '%" . $search . "%') ";
	} else if (!$search_type_service && $search_type_host && $search){
		$rq .= " AND no.name1 like '%" . $search . "%'";
	} else if ($search_type_service && !$search_type_host && $search){
		$rq .= " AND no.name2 like '%" . $search . "%'";
	}

	if ($o == "svcpb")
		$rq .= " AND nss.current_state != 0";
	if ($o == "svc_ok")
		$rq .= " AND nss.current_state = 0 ";
	if ($o == "svc_warning")
		$rq .= " AND nss.current_state = 1 ";
	if ($o == "svc_critical")
		$rq .= " AND nss.current_state = 2 ";
	if ($o == "svc_unknown")
		$rq .= " AND nss.current_state = 3 ";
	if ($o == "svc_unhandled") {
		$rq .= " AND nss.current_state != 0";
		$rq .= " AND nss.problem_has_been_acknowledged = 0";
		$rq .= " AND nss.scheduled_downtime_depth = 0";
	}

	$rq_pagination = $rq;

	switch ($sort_type){
		case 'host_name' : $rq .= " order by no.name1 ". $order.",no.name2 "; break;
		case 'service_description' : $rq .= " order by no.name2 ". $order.",no.name1 "; break;
		case 'current_state' : $rq .= " order by nss.current_state ". $order.",no.name1,no.name2 "; break;
		case 'last_state_change' : $rq .= " order by nss.last_state_change ". $order.",no.name1,no.name2 "; break;
		case 'last_check' : $rq .= " order by nss.last_check ". $order.",no.name1,no.name2 "; break;
		case 'current_attempt' : $rq .= " order by nss.current_check_attempt ". $order.",no.name1,no.name2 "; break;
		default : $rq .= " order by no.name1 ". $order; break;
	}

	$rq .= " LIMIT ".($num * $limit).",".$limit;

	$ct = 0;
	$flag = 0;

	$DBRESULT_NDO2 =& $pearDBndo->query($rq_pagination);

	/*
	 * Get Pagination Rows
	 */

	$numRows = $DBRESULT_NDO2->numRows();

	/*
	 * Create Buffer
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("reponse");
	$buffer->startElement("i");
	$buffer->writeElement("numrows", $numRows);
	$buffer->writeElement("num", $num);
	$buffer->writeElement("limit", $limit);
	$buffer->writeElement("p", $p);
	$buffer->writeElement("nc", $nc);
	$buffer->writeElement("o", $o);
	$buffer->endElement();

	$host_prev = "";
	$class = "list_one";

	$DBRESULT_NDO2 =& $pearDBndo->query($rq);

	while ($ndo =& $DBRESULT_NDO2->fetchRow()) {

		$color_service = $tab_color_service[$ndo["current_state"]];
		$passive = 0;
		$active = 1;
		$last_check = " ";
		$duration = " ";

		if ($ndo["last_state_change"] > 0 && time() > $ndo["last_state_change"])
			$duration = CentreonDuration::toString(time() - $ndo["last_state_change"]);
		else if ($ndo["last_state_change"] > 0)
			$duration = " - ";

		$class == "list_one" ? $class = "list_two" : $class = "list_one";

		if ($tab_status_svc[$ndo["current_state"]] == "CRITICAL"){
			$ndo["problem_has_been_acknowledged"] == 1 ? $class = "list_four" : $class = "list_down";
		} else {
			if ($ndo["problem_has_been_acknowledged"] == 1)
				$class = "list_four";
		}

		$tabID = split("_", $ndo["service_description"]);
		$id = $tabID[1];

		$DBRESULT=& $pearDB->query("SELECT `meta_name` FROM  `meta_service` WHERE `meta_id` = '$id'");
		$dataMeta =& $DBRESULT->fetchRow();
		$DBRESULT->free();

		$buffer->startElement("l");
		$buffer->writeAttribute("class", $class);
		$buffer->writeElement("o", $ct++);
		$buffer->writeElement("f", $flag);
		$buffer->writeElement("ppd", $ndo["process_performance_data"]);
		$buffer->writeElement("sd", $dataMeta['meta_name'], false);
		$buffer->writeElement("svc_id", $ndo["object_id"]);

		$ndo["service_description"] = str_replace("/", "#S#", $ndo["service_description"]);
		$ndo["service_description"] = str_replace("\\", "#BS#", $ndo["service_description"]);

		$buffer->writeElement("svc_index", getMyIndexGraph4Service($ndo["host_name"],$ndo["service_description"], $pearDBO));
		$buffer->writeElement("sc", $color_service);
		$buffer->writeElement("cs", _($tab_status_svc[$ndo["current_state"]]));
		$buffer->writeElement("po", $ndo["plugin_output"], false);
		$buffer->writeElement("ca", $ndo["current_attempt"]);
		$buffer->writeElement("ne", $ndo["notifications_enabled"]);
		$buffer->writeElement("pa", $ndo["problem_has_been_acknowledged"]);
		$buffer->writeElement("pc", $ndo["passive_checks_enabled"]);
		$buffer->writeElement("ac", $ndo["active_checks_enabled"]);
		$buffer->writeElement("eh", $ndo["event_handler_enabled"]);
		$buffer->writeElement("is", $ndo["is_flapping"]);
		$buffer->writeElement("fd", $ndo["flap_detection_enabled"]);
		$buffer->writeElement("ha", $ndo["problem_has_been_acknowledged"]);
		$buffer->writeElement("hae", $ndo["active_checks_enabled"]);
        $buffer->writeElement("hpe", $ndo["passive_checks_enabled"]);
        $buffer->writeElement("nc", date($date_time_format_status, $ndo["next_check"]));
        $buffer->writeElement("lc", date($date_time_format_status, $ndo["last_check"]));
		$buffer->writeElement("d", $duration);
		$buffer->endElement();
	}

	if (!$ct) {
		$buffer->writeElement("infos", "none");
	}

	$buffer->writeElement("sid", $sid);
	$buffer->endElement();
	header('Content-Type: text/xml');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate');
	$buffer->output();
?>
<?php
/*
 * Copyright 2005-2009 MERETHIS
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
	include_once $centreon_path . "www/class/centreonDB.class.php";
	include_once $centreon_path . "www/class/other.class.php";
	include_once $centreon_path . "www/class/centreonXML.class.php";
	include_once $centreon_path . "www/class/centreonACL.class.php";
	include_once $centreon_path . "www/class/centreonGMT.class.php";	
	include_once $centreon_path . "www/include/common/common-Func.php";
	
	
	$pearDB 	= new CentreonDB();
	$pearDBndo 	= new CentreonDB("ndo");

	$ndo_base_prefix = getNDOPrefix();
	$general_opt = getStatusColor($pearDB);
	
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM `session` WHERE `session_id` = '".$sid."'");
		if (!$session =& $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	/* 
	 * requisit 
	 */
	(isset($_GET["instance"]) && !check_injection($_GET["instance"])) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["num"]) && !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) && !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	
	/*
	 *  options 
	 */
	(isset($_GET["search"]) && !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) && !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]):$sort_type = "host_name";
	(isset($_GET["order"]) && !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $oreder = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) && !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) && !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";


	/* 
	 * LCA 
	 */
	
	$is_admin =  isUserAdmin($sid);
	$user_id = getUserIdFromSID($sid); 	
	$access = new CentreonACL($user_id, $is_admin);	
	$grouplistStr = $access->getAccessGroupsString();
		

	/*
	 * Init GMT class
	 */
	
	$centreonGMT = new CentreonGMT();
	$centreonGMT->getMyGMTFromSession($sid);
	

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

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
	$state_type = array("1" => "H", "0" => "S");

	/* Get Host status */
	$rq1 = 	" SELECT DISTINCT no.name1, nhs.current_state," .
			" nhs.problem_has_been_acknowledged, " .
			" nhs.passive_checks_enabled," .
			" nhs.active_checks_enabled," .
			" nhs.notifications_enabled," .
			" unix_timestamp(nhs.last_state_change) as last_state_change," .
			" nhs.output," .
			" unix_timestamp(nhs.last_check) as last_check," .
			" nh.address," .
			" no.name1 as host_name," .
			" nh.action_url," .
			" nh.notes_url," .
			" nh.icon_image," .
			" nh.icon_image_alt," .
			" nhs.max_check_attempts," .
			" nhs.state_typeZ," .
			" nhs.current_check_attempt" .
			" FROM ".$ndo_base_prefix."hoststatus nhs, ".$ndo_base_prefix."objects no, ".$ndo_base_prefix."hosts nh";
	if (!$is_admin)	
		$rq1 .= ", centreon_acl ";
		 
	$rq1 .= " WHERE no.object_id = nhs.host_object_id AND nh.host_object_id = no.object_id " .
			" AND no.is_active = 1 AND no.objecttype_id = 1 " .
			" AND no.name1 NOT LIKE '_Module_%'";

	if (!$is_admin)	
		$rq1 .= $access->queryBuilder("AND", "no.name1", "centreon_acl.host_name") . $access->queryBuilder("AND", "centreon_acl.group_id", $grouplistStr);

	if ($search != "")
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";

	if ($o == "hpb")
		$rq1 .= " AND nhs.current_state != 0 ";
	
	if ($o == "h_unhandled") {
		$rq1 .= " AND nhs.current_state != 0 ";
		$rq1 .= " AND nhs.state_type = '1'";
		$rq1 .= " AND nhs.problem_has_been_acknowledged = 0";
		$rq1 .= " AND nhs.scheduled_downtime_depth = 0";		
	}

	if ($instance != "ALL")
		$rq1 .= " AND no.instance_id = ".$instance;


	switch ($sort_type) {
		case 'host_name' : $rq1 .= " order by no.name1 ". $order;  break;
		case 'current_state' : $rq1 .= " order by nhs.current_state ". $order.",no.name1 ";  break;
		case 'last_state_change' : $rq1 .= " order by nhs.last_state_change ". $order.",no.name1 ";  break;
		case 'last_check' : $rq1 .= " order by nhs.last_check ". $order.",no.name1 ";  break;
		case 'current_check_attempt' : $rq1 .= " order by nhs.current_check_attempt ". $order.",no.name1 ";  break;
		case 'ip' : $rq1 .= " order by nh.address ". $order.",no.name1 ";  break;
		case 'plugin_output' : $rq1 .= " order by nhs.output ". $order.",no.name1 ";  break;
		default : $rq1 .= " order by no.name1 ";  break;
	}

	$rq_pagination = $rq1;

	/* 
	 * Get Pagination Rows 
	 */
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	$numRows = $DBRESULT_PAGINATION->numRows();

	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	$buffer = new CentreonXML();
	$buffer->startElement("reponse");
	$buffer->startElement("i");
	$buffer->writeElement("numrows", $numRows);
	$buffer->writeElement("num", $num);
	$buffer->writeElement("limit", $limit);
	$buffer->writeElement("p", $p);
	$buffer->endElement();	
	
	$class = "list_one";
	$ct = 0;
	$flag = 0;
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	while ($ndo =& $DBRESULT_NDO1->fetchRow()){
		$color_host = $tab_color_host[$ndo["current_state"]];
		$passive = 0;
		$active = 1;
		$last_check = " ";
		$duration = " ";
		
		if ($ndo["last_state_change"] > 0 && time() > $ndo["last_state_change"])
			$duration = Duration::toString(time() - $ndo["last_state_change"]);
		else
			$duration = "N/A";
			
		$class == "list_one" ? $class = "list_two" : $class = "list_one";
			
		$host_status[$ndo["host_name"]] = $ndo;
		$buffer->startElement("l");
		$buffer->writeAttribute("class", $class);
		$buffer->writeElement("o", $ct++);
		$buffer->writeElement("hc", $color_host);
		$buffer->writeElement("f", $flag);
		$buffer->writeElement("hn", $ndo["host_name"]);
		$buffer->writeElement("a", ($ndo["address"] ? $ndo["address"] : "N/A"));
		$buffer->writeElement("ou", ($ndo["output"] ? $ndo["output"] : "N/A"));
		$buffer->writeElement("lc", (($ndo["last_check"] != 0) ? $centreonGMT->getDate($date_time_format_status, $ndo["last_check"]) : "N/A"));
		$buffer->writeElement("cs", $tab_status_host[$ndo["current_state"]]);		
		$buffer->writeElement("pha", $ndo["problem_has_been_acknowledged"]);
        $buffer->writeElement("pce", $ndo["passive_checks_enabled"]);
        $buffer->writeElement("ace", $ndo["active_checks_enabled"]);
        $buffer->writeElement("lsc", ($duration ? $duration : "N/A"));      
        $buffer->writeElement("ha", $ndo["problem_has_been_acknowledged"]);
        $buffer->writeElement("hae", $ndo["active_checks_enabled"]);       
        $buffer->writeElement("hpe", $ndo["passive_checks_enabled"]);
        $buffer->writeElement("ne", $ndo["notifications_enabled"]);
        $buffer->writeElement("tr", $ndo["current_check_attempt"]."/".$ndo["max_check_attempts"]." (".$state_type[$ndo["state_type"]].")");
        $buffer->writeElement("ico", $ndo["icon_image"]);
		$buffer->endElement();		
	}

	if (!$ct)
		$buffer->writeElement("infos", "none");

	$buffer->endElement();
	header('Content-Type: text/xml');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate'); 
	
	$buffer->output();
?>
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
	include_once $centreon_path."www/class/other.class.php";
	include_once $centreon_path."www/class/centreonGMT.class.php";
	include_once $centreon_path."www/class/centreonACL.class.php";
	include_once $centreon_path."www/class/centreonXML.class.php";
	include_once $centreon_path."www/class/centreonDB.class.php";
	include_once $centreon_path."www/include/monitoring/status/Common/common-Func.php";	
	include_once $centreon_path."www/include/common/common-Func.php";

	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");
	$pearDBndo 	= new CentreonDB("ndo");

	$ndo_base_prefix = getNDOPrefix();
	$general_opt = getStatusColor($pearDB);
	
	/* 
	 * security check 2/2 
	 */
	 
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid, ENT_QUOTES);
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
	$grouplist = $access->getAccessGroups();
	$grouplistStr = $access->getAccessGroupsString();
	$groupnumber = count($grouplist);	

	$default_poller = "ALL";
	$rq_default_poller = "SELECT cp_value FROM contact_param WHERE cp_key = 'monitoring_default_poller' AND cp_contact_id = '".$user_id."' LIMIT 1";
	$DBRES_POLLER = $pearDB->query($rq_default_poller);
	if ($DBRES_POLLER->numRows()) {
		$tmpRow = $DBRES_POLLER->fetchRow();
		$default_poller = $tmpRow['cp_value'];
	}
	
	(isset($_GET["num"]) 		&& !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) 		&& !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	(isset($_GET["instance"])/* && !check_injection($_GET["instance"])*/) ? $instance = htmlentities($_GET["instance"]) : $instance = $default_poller;
	(isset($_GET["search"]) 	&& !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) 	&& !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "host_name";
	(isset($_GET["search_type_host"]) 		&& !check_injection($_GET["search_type_host"])) ? $search_type_host = htmlentities($_GET["search_type_host"]) : $search_type_host = 1;
	(isset($_GET["search_type_service"])	&& !check_injection($_GET["search_type_service"])) ? $search_type_service = htmlentities($_GET["search_type_service"]) : $search_type_service = 1;
	(isset($_GET["order"]) 		&& !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $order = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) 			&& !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) 			&& !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";
	(isset($_GET["nc"]) 		&& !check_injection($_GET["nc"])) ? $nc = htmlentities($_GET["nc"]) : $nc = "0";

	if (!$instance) {
		$instance = "ALL";
	}
	
	/*
	 * Init GMT class
	 */
	$centreonGMT = new CentreonGMT();
	$centreonGMT->getMyGMTFromSession($sid);
	
	/*
	 * Backup poller selection
	 */
	$pearDB->query("DELETE FROM `contact_param` WHERE `cp_key` = 'MONITORING_POLLER_ID' AND `cp_contact_id` = '$user_id'");	
	$pearDB->query("INSERT INTO `contact_param` SET `cp_key` = 'MONITORING_POLLER_ID', `cp_contact_id` = '$user_id', `cp_value` = '$instance'");	
	
	
	/*
	 * check is admin
	 */	

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
	$state_type = array("1" => "H", "0" => "S");

	/* 
	 * Get Host status 
	 */
	
	$rq1 = "SELECT " .
			" DISTINCT no.name1 as host_name," .
			" nhs.current_state, nhs.problem_has_been_acknowledged, nhs.scheduled_downtime_depth, " .
			" nhs.passive_checks_enabled, nhs.active_checks_enabled, no.object_id, nh.action_url," .
			" nh.notes_url, nh.notes, nh.address, nh.icon_image, nh.icon_image_alt " .
			" FROM ".$ndo_base_prefix."hoststatus nhs, ".$ndo_base_prefix."objects no, ".$ndo_base_prefix."hosts nh " .
			" WHERE no.object_id = nhs.host_object_id " .
			" AND nh.host_object_id = no.object_id " .
			" AND no.objecttype_id = 1 " .
			" AND no.object_id = nh.host_object_id";

	if ($o == "svc_unhandled") {
		$rq1 .= " AND nhs.problem_has_been_acknowledged = 0";
		$rq1 .= " AND nhs.scheduled_downtime_depth = 0";
	}

	if ($instance != "ALL")
		$rq1 .= " AND no.instance_id = ".$instance;

	if ($o == "meta")
		$rq1 .= " AND no.name1 = '_Module_Meta'";
	else
		$rq1 .= " AND no.name1 != '_Module_Meta'";

	$str = "";
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	while ($ndo =& $DBRESULT_NDO1->fetchRow()) {
		$host_status[$ndo["host_name"]] = $ndo;
	}
	$DBRESULT_NDO1->free();

	/* 
	 * Get Service status
	 */
	switch ($sort_type){
		case 'host_name' : 
			$rq_sorte = " ORDER BY host_name ". $order.", service_description"; 
			break;
		case 'service_description' : 
			$rq_sorte = " ORDER BY service_description ". $order.", host_name "; 
			break;
		case 'current_state' : 
			$rq_sorte = " ORDER BY nss.current_state ". $order.", host_name, service_description ";
			break;
		case 'last_state_change' : 
			$rq_sorte = " ORDER BY nss.last_state_change ". $order.", host_name, service_description "; 
			break;
		case 'last_check' : 
			$rq_sorte = " ORDER BY nss.last_check ". $order.", host_name, service_description "; 
			break;
		case 'current_attempt' : 
			$rq_sorte = " ORDER BY nss.current_check_attempt ". $order.", host_name, service_description "; 
			break;
		default : 
			$rq_sorte = " ORDER BY host_name ". $order; 
			break;
	}

	$rq_limit = " LIMIT ".($num * $limit).",".$limit;

	$ArgNeeded = "A.*, nss.process_performance_data, nss.current_state, nss.output as plugin_output, nss.state_type as state_type, nss.current_check_attempt as current_attempt, nss.status_update_time as status_update_time, unix_timestamp(nss.last_state_change) as last_state_change, unix_timestamp(nss.last_check) as last_check, unix_timestamp(nss.next_check) as next_check, nss.notifications_enabled, nss.problem_has_been_acknowledged, nss.passive_checks_enabled, nss.active_checks_enabled, nss.event_handler_enabled, nss.is_flapping, nss.scheduled_downtime_depth, nss.flap_detection_enabled";

	$ACLDBName = "";
	if (!$is_admin)
		$ACLDBName = ", centreon_acl ";

	$ACLCondition = "";
	if (!$is_admin)
		$ACLCondition = " AND no.name1 = centreon_acl.host_name AND no.name2 = centreon_acl.service_description AND group_id IN ($grouplistStr)";

	/*
	 * Prepare current_state condition
	 */
	$rq_state = "";
	
	$instance_filter = "";
	if ($instance != "ALL") {
		$instance_filter = " AND no.instance_id = '".$instance."' ";
	}
	
	if ($o == "svcpb")
		$rq_state = " AND nss.current_state != 0";
	if ($o == "svc_ok")
		$rq_state = " AND nss.current_state = 0 ";
	if ($o == "svc_warning")
		$rq_state = " AND nss.current_state = 1 ";
	if ($o == "svc_critical")
		$rq_state = " AND nss.current_state = 2 ";
	if ($o == "svc_unknown")
		$rq_state = " AND nss.current_state = 3 ";
	
	if ($o == "svc_unhandled") {
		$rq_state .= " AND nss.current_state != 0";
		$rq_state .= " AND nss.state_type = 1";
		$rq_state .= " AND nss.problem_has_been_acknowledged = 0";
		$rq_state .= " AND nss.scheduled_downtime_depth = 0";
	}

	$searchHost = "";
	if ($search_type_host && $search) {
		if ($search_type_service && $search)
			$searchHost .= " AND (";
		else
			$searchHost .= " AND ";
		$searchHost .= "no.name1 LIKE '%$search%' ";
	}
	
	$searchService = "";
	if ($search_type_service && $search) {
		if ($search_type_host && $search) {
			$searchService .= " OR ";
		} else {
			$searchService .= " AND ";
		}
		$searchService .= " no.name2 LIKE '%$search%' ";
		if ($search_type_host && $search)
			$searchService .= ")";
	}
	
	$rq3 = 	"SELECT 1 FROM nagios_servicestatus WHERE no.object_id = ns.service_object_id  ";
	
	$rq1 = 	"SELECT $ArgNeeded " .
		 	"FROM (";

	$rq2 = 	"SELECT DISTINCT no.name1 as host_name, no.object_id, no.name2 as service_description, " .
			"ns.notes, ns.notes_url, ns.action_url, ns.max_check_attempts FROM  nagios_objects no, nagios_services ns $ACLDBName" .
			"WHERE no.object_id = ns.service_object_id " .
			"	AND no.name1 NOT LIKE '_Module_%' " .
			"	$searchHost $searchService $instance_filter $ACLCondition " .
			"	AND objecttype_id = 2 " .
			"	AND EXISTS ($rq3)" .
			"	) A, " .
		 	"nagios_servicestatus nss WHERE A.object_id = nss.service_object_id $rq_state $rq_sorte $rq_limit ";

	$rq = $rq1 . $rq2;

	/* 
	 * Get Pagination Rows 
	 */
	if ($is_admin) {
		$rq_pagination ="SELECT count(DISTINCT UPPER(CONCAT(no.name1,';', no.name2))) " .
						"FROM nagios_objects no ,  nagios_servicestatus nss " .
						"WHERE no.object_id = nss.service_object_id $rq_state $instance_filter AND no.name1 NOT LIKE '_Module_%' $searchHost $searchService";
	} else {
		$rq_pagination ="SELECT count(DISTINCT UPPER(CONCAT(no.name1,';', no.name2))) " .
						"FROM nagios_objects no, nagios_servicestatus nss, centreon_acl " .
						"WHERE no.object_id = nss.service_object_id $rq_state $instance_filter AND no.name1 NOT LIKE '_Module_%' $searchHost $searchService $ACLCondition";
	}
	$DBRESULT =& $pearDBndo->query($rq_pagination);
	$data =& $DBRESULT->fetchRow();
	$numRows =& $data["count(DISTINCT UPPER(CONCAT(no.name1,';', no.name2)))"];
	$DBRESULT->free();
	unset($data);
	
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
	
	$ct = 0;
	$flag = 0;
	$DBRESULT_NDO2 =& $pearDBndo->query($rq);
	while ($ndo =& $DBRESULT_NDO2->fetchRow()) {
		if (isset($host_status[$ndo["host_name"]])){

			$color_host = $tab_color_host[$host_status[$ndo["host_name"]]["current_state"]];
			$color_service = $tab_color_service[$ndo["current_state"]];
			$passive = 0;
			$active = 1;
			$last_check = " ";
			$duration = " ";

			if ($ndo["last_state_change"] > 0 && time() > $ndo["last_state_change"])
				$duration = Duration::toString(time() - $ndo["last_state_change"]);
			else if ($ndo["last_state_change"] > 0)
				$duration = " - ";

			$class == "list_one" ? $class = "list_two" : $class = "list_one";
			
			if ($ndo["scheduled_downtime_depth"] == 1) {
				$class = "line_downtime";
			} else if ($tab_status_svc[$ndo["current_state"]] == "CRITICAL"){
				$ndo["problem_has_been_acknowledged"] == 1 ? $class = "line_ack" : $class = "list_down";
			} else {
				if ($ndo["problem_has_been_acknowledged"] == 1)
					$class = "line_ack";
			} 
			
			$buffer->startElement("l");
			$buffer->writeAttribute("class", $class);
			$buffer->writeElement("o", $ct++);
			$buffer->writeElement("f", $flag);
			
			if ($host_prev == $ndo["host_name"]){
				$buffer->writeElement("hc", "transparent");				
				$buffer->startElement("hn");
				$buffer->writeAttribute("none", "1");
				$buffer->text($ndo["host_name"]);
				$buffer->endElement();				
			} else {				
				$host_prev = $ndo["host_name"];
				$buffer->writeElement("hc", $color_host);
				$buffer->startElement("hn");
				$buffer->writeAttribute("none", "0");
				$buffer->text($ndo["host_name"]);
				$buffer->endElement();
				$buffer->writeElement("hau", $host_status[$ndo["host_name"]]["action_url"]);								

				if ($host_status[$ndo["host_name"]]["notes_url"])
					$buffer->writeElement("hnu", $host_status[$ndo["host_name"]]["notes_url"]);
				else
					$buffer->writeElement("hnu", "none");
					
				$buffer->writeElement("hnn", $host_status[$ndo["host_name"]]["notes"]);
				$buffer->writeElement("hico", $host_status[$ndo["host_name"]]["icon_image"]);
				$buffer->writeElement("hip", $host_status[$ndo["host_name"]]["address"]);
				$buffer->writeElement("hdtm", $host_status[$ndo["host_name"]]["scheduled_downtime_depth"]);
				$buffer->writeElement("hid", $host_status[$ndo["host_name"]]["object_id"]);
			}

			$buffer->writeElement("ppd", $ndo["process_performance_data"]);
			$buffer->writeElement("hs", $host_status[$ndo["host_name"]]["current_state"]);			
			$buffer->writeElement("sd", $ndo["service_description"]);
			$buffer->writeElement("svc_id", $ndo["object_id"]);			
						
			$ndo["service_description"] = str_replace("/", "#S#", $ndo["service_description"]);
			$ndo["service_description"] = str_replace("\\", "#BS#", $ndo["service_description"]);
			
			$buffer->writeElement("svc_index", getMyIndexGraph4Service($ndo["host_name"],$ndo["service_description"], $pearDBO));
			$buffer->writeElement("sc", $color_service);
			$buffer->writeElement("cs", $tab_status_svc[$ndo["current_state"]]);
			$buffer->writeElement("po", $ndo["plugin_output"], 0);
			$buffer->writeElement("ca", $ndo["current_attempt"]."/".$ndo["max_check_attempts"]." (".$state_type[$ndo["state_type"]].")");
			$buffer->writeElement("ne", $ndo["notifications_enabled"]);
			$buffer->writeElement("pa", $ndo["problem_has_been_acknowledged"]);
			$buffer->writeElement("pc", $ndo["passive_checks_enabled"]);			
			$buffer->writeElement("ac", $ndo["active_checks_enabled"]);
			$buffer->writeElement("eh", $ndo["event_handler_enabled"]);
			$buffer->writeElement("is", $ndo["is_flapping"]);
			$buffer->writeElement("dtm", $ndo["scheduled_downtime_depth"]);
			
			if (!isset($ndo["notes"]) || $ndo["notes"] == "")
				$ndo["notes"] = $ndo["notes_url"];
			$buffer->writeElement("sn", $ndo["notes"]);
			
			if ($ndo["notes_url"] != "") {
				$buffer->writeElement("snu", $ndo["notes_url"]);
			} else {
				$buffer->writeElement("snu", 'none');
			}
			
			$buffer->writeElement("fd", $ndo["flap_detection_enabled"]);			
			$buffer->writeElement("ha", $host_status[$ndo["host_name"]]["problem_has_been_acknowledged"]);
			$buffer->writeElement("hae", $host_status[$ndo["host_name"]]["active_checks_enabled"]);
	        $buffer->writeElement("hpe", $host_status[$ndo["host_name"]]["passive_checks_enabled"]);
	        $buffer->writeElement("nc", $centreonGMT->getDate($date_time_format_status, $ndo["next_check"]));	        	        
			$buffer->writeElement("lc", $centreonGMT->getDate($date_time_format_status, $ndo["last_check"]));
			$buffer->writeElement("d", $duration);
			$buffer->endElement();			
		}
	}

	if (!$ct)
		$buffer->writeElement("infos", "none");
	$buffer->writeElement("sid", $sid);			
	$buffer->endElement();
	header('Content-Type: text/xml');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate');
	$buffer->output();
?>
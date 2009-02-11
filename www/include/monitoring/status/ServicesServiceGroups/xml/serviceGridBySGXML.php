<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	# if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
	$debugXML = 0;
	$buffer = '';

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path."www/class/other.class.php");
	include_once($centreon_path."www/class/centreonACL.class.php");
	include_once($centreon_path."www/class/centreonXML.class.php");
	include_once($centreon_path."www/DBconnect.php");
	include_once($centreon_path."www/DBNDOConnect.php");
	include_once($centreon_path."www/include/monitoring/status/Common/common-Func.php");
	include_once($centreon_path."www/include/common/common-Func.php");

	$ndo_base_prefix = getNDOPrefix();
	$general_opt = getStatusColor($pearDB);
	
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
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
		global $pearDBndo,$ndo_base_prefix, $lcaSGStr, $instance;
		global $general_opt, $o;

		$rq = "SELECT no.name1, no.name2 as service_name, nss.current_state" .
				" FROM `" .$ndo_base_prefix."servicestatus` nss, `" .$ndo_base_prefix."objects` no" .
				" WHERE no.object_id = nss.service_object_id" ;

		if ($instance != "ALL")
			$rq .= " AND no.instance_id = ".$instance;

		if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb")
			$rq .= " AND nss.current_state != 0" ;

		if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0")
			$rq .= " AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" ;

		if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1")
			$rq .= " AND nss.problem_has_been_acknowledged = 1" ;

		$rq .= " AND no.object_id" .
				" IN (" .
				" SELECT nno.object_id" .
				" FROM ".$ndo_base_prefix."objects nno" .
				" WHERE nno.objecttype_id =2" .
				" AND nno.name1 = '".$host_name."')";

		$DBRESULT =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
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

	$rq1 = "SELECT sg.alias, no.name1 as host_name, no.name2 as service_description, sgm.servicegroup_id, sgm.service_object_id, ss.current_state".
			" FROM " .$ndo_base_prefix."servicegroups sg," .$ndo_base_prefix."servicegroup_members sgm, " .$ndo_base_prefix."servicestatus ss, " .$ndo_base_prefix."objects no".
			" WHERE sg.config_type = 1 " .
			" AND ss.service_object_id = sgm.service_object_id".
			" AND no.object_id = sgm.service_object_id" .
			" AND sgm.servicegroup_id = sg.servicegroup_id" .
			" AND no.is_active = 1 AND no.objecttype_id = 2";
	
	$rq1 .= $access->queryBuilder("AND", "sg.alias", $access->getServiceGroupsString("NAME"));

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


	$rq_pagination = $rq1;
	/* Get Pagination Rows */
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	if (PEAR::isError($DBRESULT_PAGINATION))
		print "DB Error : ".$DBRESULT_PAGINATION->getDebugInfo()."<br />";
	$numRows = $DBRESULT_PAGINATION->numRows();
	/* End Pagination Rows */


	$rq1 .= " ORDER BY sg.alias, host_name " . $order;

	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	/*
	 * Init Buffer
	 */
	 
	$buffer = new CentreonXML();
	$buffer->startElement("reponse");
	$buffer->startElement("i");
	$buffer->writeElement("numrows", $numRows);
	$buffer->writeElement("num", $num);
	$buffer->writeElement("limit", $limit);
	$buffer->writeElement("host_name", _("Hosts"));
	$buffer->writeElement("services", _("Services"));
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
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	while ($tab =& $DBRESULT_NDO1->fetchRow()){
		$class == "list_one" ? $class = "list_two" : $class = "list_one";
		if ($sg != $tab["alias"]){
			$flag = 0;
			if ($sg != "") {
				$buffer->endElement();
				$buffer->endElement();				
			}
			$sg = $tab["alias"];
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
			$hs = get_Host_Status($tab["host_name"], $pearDBndo, $general_opt);
			$buffer->startElement("h");
			$buffer->writeAttribute("class", $class);
			$buffer->writeElement("hn", $tab["host_name"]);
			$buffer->writeElement("hs", $tab_status_host[$hs]);
			$buffer->writeElement("hc", $tab_color_host[$hs]);			
		}
		$buffer->startElement("svc");
		$buffer->writeElement("sn", $tab["service_description"]);
		$buffer->writeElement("sc", $tab_color_service[$tab["current_state"]]);
		$buffer->endElement();		

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
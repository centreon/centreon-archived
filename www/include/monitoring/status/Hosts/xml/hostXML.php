<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon 
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

	/*
	 * if debug == 0 => Normal, 
	 * debug == 1 => get use, 
	 * debug == 2 => log in file (log.xml)
	 */
	$debugXML = 0;
	$buffer = '';

	include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once $centreon_path . "www/DBconnect.php";
	include_once $centreon_path . "www/DBNDOConnect.php";
	include_once $centreon_path . "www/class/other.class.php";
	include_once $centreon_path . "www/class/centreonGMT.class.php";
	include_once $centreon_path . "www/include/common/common-Func-ACL.php";
	include_once $centreon_path . "www/include/common/common-Func.php";
	
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

	// if is admin -> lca
	if (!$is_admin){
		$_POST["sid"] = $sid;
		
		$grouplist = getGroupListofUser($pearDB); 
		$groupnumber = count($grouplist);
		$grouplistStr = groupsListStr($grouplist);
		
	}

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
			" nh.icon_image_alt" .
			" FROM ".$ndo_base_prefix."hoststatus nhs, ".$ndo_base_prefix."objects no, ".$ndo_base_prefix."hosts nh";
	if (!$is_admin)	
		$rq1 .= ", centreon_acl ";
		 
	$rq1 .= " WHERE no.object_id = nhs.host_object_id and nh.host_object_id = no.object_id " .
			" AND no.name1 not like 'OSL_Module'".
			" AND no.is_active = 1 AND no.objecttype_id = 1 AND nh.config_type = 1" .
			" AND no.name1 NOT LIKE 'Meta_Module'";

	if (!$is_admin)
		$rq1 .= " AND no.name1 = centreon_acl.host_name" .
				" AND centreon_acl.group_id IN (".$grouplistStr.")";


	if ($search != "")
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";

	if ($o == "hpb")
		$rq1 .= " AND nhs.current_state != 0 ";
	
	if ($o == "h_unhandled") {
		$rq1 .= " AND nhs.current_state != 0 ";
		$rq1 .= " AND nhs.problem_has_been_acknowledged = 0";
		$rq1 .= " AND nhs.scheduled_downtime_depth = 0";		
	}

	if ($instance != "ALL")
		$rq1 .= " AND no.instance_id = ".$instance;


	switch($sort_type){
		case 'host_name' : $rq1 .= " order by no.name1 ". $order;  break;
		case 'current_state' : $rq1 .= " order by nhs.current_state ". $order.",no.name1 ";  break;
		case 'last_state_change' : $rq1 .= " order by nhs.last_state_change ". $order.",no.name1 ";  break;
		case 'last_check' : $rq1 .= " order by nhs.last_check ". $order.",no.name1 ";  break;
		case 'current_attempt' : $rq1 .= " order by nhs.current_check_attempt ". $order.",no.name1 ";  break;
		case 'ip' : $rq1 .= " order by nh.address ". $order.",no.name1 ";  break;
		case 'plugin_output' : $rq1 .= " order by nhs.output ". $order.",no.name1 ";  break;
		default : $rq1 .= " order by no.name1 ";  break;
	}

	$rq_pagination = $rq1;

	/* 
	 * Get Pagination Rows 
	 */
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	if (PEAR::isError($DBRESULT_PAGINATION))
		print "DB Error : ".$DBRESULT_PAGINATION->getDebugInfo()."<br />";
	$numRows = $DBRESULT_PAGINATION->numRows();

	$rq1 .= " LIMIT ".($num * $limit).",".$limit;


	$buffer .= '<reponse>';
	$buffer .= '<i>';
	$buffer .= '<numrows>'.$numRows.'</numrows>';
	$buffer .= '<num>'.$num.'</num>';
	$buffer .= '<limit>'.$limit.'</limit>';
	$buffer .= '<p>'.$p.'</p>';
	$buffer .= '</i>';
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	$class = "list_one";
	$ct = 0;
	$flag = 0;
	while ($ndo =& $DBRESULT_NDO1->fetchRow()){
		$color_host = $tab_color_host[$ndo["current_state"]]; //"#FF0000";
		$passive = 0;
		$active = 1;
		$last_check = " ";
		$duration = " ";
		
		if ($ndo["last_state_change"] > 0)
			$duration = Duration::toString(time() - $ndo["last_state_change"]);
		
		$class == "list_one" ? $class = "list_two" : $class = "list_one";
			
		$host_status[$ndo["host_name"]] = $ndo;
		$buffer .= '<l class="'.$class.'">';
		$buffer .= '<o>'. $ct++ . '</o>';
		$buffer .= '<hc><![CDATA['. $color_host . ']]></hc>';
		$buffer .= '<f><![CDATA['. $flag . ']]></f>';
		$buffer .= '<hn><![CDATA['. $ndo["host_name"]  . ']]></hn>';
		$buffer .= '<a><![CDATA['. $ndo["address"]  . ']]></a>';
		$buffer .= '<ou><![CDATA['. $ndo["output"]  . ']]></ou>';
		$buffer .= '<lc>'. (($ndo["last_check"] != 0) ? $centreonGMT->getDate($date_time_format_status, $ndo["last_check"]) : "N/A") . '</lc>';
		$buffer .= '<cs>'. $tab_status_host[$ndo["current_state"]] . '</cs>';
        $buffer .= '<pha>'. $ndo["problem_has_been_acknowledged"] .'</pha>';
        $buffer .= '<pce>'.$ndo["passive_checks_enabled"] .'</pce>';
        $buffer .= '<ace>'.$ndo["active_checks_enabled"] .'</ace>';
        $buffer .= '<lsc>'.($duration ? $duration : "N/A") .'</lsc>';
        $buffer .= '<ha>'.$ndo["problem_has_been_acknowledged"]  .'</ha>';///
        $buffer .= '<hae>'.$ndo["active_checks_enabled"] .'</hae>';///
        $buffer .= '<hpe>'.$ndo["passive_checks_enabled"]  .'</hpe>';///
		$buffer .= '<ne>'. $ndo["notifications_enabled"] . '</ne>';
		$buffer .= '</l>';
	}

	if (!$ct)
		$buffer .= '<infos>none</infos>';

	$buffer .= '</reponse>';
	header('Content-Type: text/xml');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate'); 
	
	echo $buffer;
?>
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

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path . "www/class/other.class.php");
	include_once($centreon_path . "www/DBconnect.php");
	include_once($centreon_path . "www/DBNDOConnect.php");
	include_once($centreon_path . "www/include/common/common-Func-ACL.php");
	include_once($centreon_path . "www/include/common/common-Func.php");

	$ndo_base_prefix = getNDOPrefix();
	$general_opt = getStatusColor($pearDB);
	
	/* 
	 * security check 2/2
	 */
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session =& $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');
	
	/*
	 * Get Acl Group list
	 */
	
	$grouplist = getGroupListofUser($pearDB); 
	$groupnumber = count($grouplist);
	
	/* 
	 * requisit 
	 */
	(isset($_GET["num"]) 		&& !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) 		&& !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	(isset($_GET["instance"])	/* && !check_injection($_GET["instance"])*/) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["search"]) 	&& !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) 	&& !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "host_name";
	(isset($_GET["order"]) 		&& !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $oreder = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) 			&& !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) 			&& !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";

	// check is admin
	$is_admin = isUserAdmin($sid);

	/*
	 * if is admin -> lca
	 */
	if (!$is_admin){
		$_POST["sid"] = $sid;
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

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");

	/* 
	 * Get Host status 
	 */
	 
	$rq1 =	  	" SELECT DISTINCT no.object_id id, no.name1 as host_name, nhs.current_state hs, no.name2 svc_name, nss.current_state svcs" .
				" FROM " .$ndo_base_prefix."objects no, " .$ndo_base_prefix."hoststatus nhs, " .$ndo_base_prefix."services ns, " .$ndo_base_prefix."servicestatus nss  ";
	if (!$is_admin)
		$rq1 	.= ", centreon_acl ";
	
	$rq1 .=		" WHERE ((no.objecttype_id = '1' AND nhs.host_object_id = no.object_id) OR (no.objecttype_id = '2' AND nss.service_object_id = no.object_id))".
				" AND no.name1 NOT LIKE 'OSL_Module'".
				" AND no.name1 NOT LIKE 'Meta_Module'";
	
	if (!$is_admin && $groupnumber)
		$rq1 .= " AND no.name1 = centreon_acl.host_name AND no.name2 = centreon_acl.service_description AND group_id IN (".groupsListStr($grouplist).")";
	if ($o == "svcgrid_pb" || $o == "svcOV_pb" || $o == "svcgrid_ack_0" || $o == "svcOV_ack_0")
		$rq1 .= " AND nss.current_state != 0 ";
	if ($o == "svcgrid_ack_1" || $o == "svcOV_ack_1")
		$rq1 .= "AND nss.problem_has_been_acknowledged = 1";
	if ($o == "svcgrid_ack_0" || $o == "svcOV_ack_0")
		$rq1 .= "AND nss.problem_has_been_acknowledged = 0";
	if ($search != "")
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
	if ($instance != "ALL")
		$rq1 .= " AND no.instance_id = ".$instance;
	/*
	 * Sort
	 */
	switch ($sort_type){
			case 'current_state' : $rq1 .= " order by nhs.current_state ". $order.",no.name1 "; break;
			default : $rq1 .= " order by no.name1, no.name2 ". $order; break;
	}

	$rq_pagination = $rq1;

	/*
	 * Pagination computing
	 */
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	if (PEAR::isError($DBRESULT_PAGINATION))
		print "DB Error : ".$DBRESULT_PAGINATION->getDebugInfo()."<br />";
	$numRows = $DBRESULT_PAGINATION->numRows();

	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	$buffer .= '<reponse><i><numrows>'.$numRows.'</numrows><num>'.$num.'</num><limit>'.$limit.'</limit><p>'.$p.'</p>';

	preg_match("/svcOV/",$_GET["o"], $matches) ? $buffer .= '<s>1</s>' : $buffer .= '<s>0</s>';
	$buffer .= '</i>';
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	$class = "list_one";
	$ct = 0;
	$flag = 0;

	$tabH = array();
	$tab_final = array();
	while ($ndo =& $DBRESULT_NDO1->fetchRow())	{
		if (!$ndo["svc_name"] && !isset($tabH[$ndo["id"]])) {
			$tab_final[$ndo["host_name"]]["cs"] = $ndo["hs"];			
			$tab_final[$ndo["host_name"]]["tab_svc"] = array();
			$tabH[$ndo["id"]] = 1;
		} 
		if ($ndo["svc_name"]) {
			if (!isset($tab_final[$ndo["host_name"]]))
				$tab_final[$ndo["host_name"]] = array("tab_svc" => array());
			$tab_final[$ndo["host_name"]]["tab_svc"][$ndo["svc_name"]] = $ndo["svcs"];
		}
	}

	foreach ($tab_final as $host_name => $tab){
		if (count($tab["tab_svc"])) {
			$class == "list_one" ? $class = "list_two" : $class = "list_one";
			$buffer .= '<l class="'.$class.'">';
			foreach ($tab["tab_svc"] as $svc => $state) {
				$buffer .= '<svc>';
				$buffer .= '<sn><![CDATA['. $svc . ']]></sn>';
				$buffer .= '<sc><![CDATA['. $tab_color_service[$state] . ']]></sc>';
				$buffer .= '</svc>';
			}
			$buffer .= '<o>'. $ct++ . '</o>';
			$buffer .= '<hn><![CDATA['. $host_name  . ']]></hn>';
			$buffer .= '<hs><![CDATA['. $tab_status_host[$tab["cs"]]  . ']]></hs>';
			$buffer .= '<hc><![CDATA['. $tab_color_host[$tab["cs"]]  . ']]></hc>';
			$buffer .= '</l>';		
		}
	}

	if (!$ct) {
		$buffer .= '<infos>';
		$buffer .= 'none';
		$buffer .= '</infos>';
	}

	$buffer .= '</reponse>';
	header('Content-Type: text/xml');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate'); 
	echo $buffer;
?>
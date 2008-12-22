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
 * SVN : $URL
 * SVN : $Id: 
 * 
 */

	/*
	 * if debug == 0 => Normal, 
	 * debug == 1 => get use, 
	 * debug == 2 => log in file (log.xml)
	 * 
	 */
	$debugXML = 0;
	$buffer = '';

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path . "www/class/other.class.php");
	include_once $centreon_path . "www/class/centreonGMT.class.php";
	include_once($centreon_path . "www/DBconnect.php");
	include_once($centreon_path . "www/DBNDOConnect.php");
	include_once($centreon_path . "www/include/common/common-Func-ACL.php");
	include_once($centreon_path . "www/include/common/common-Func.php");

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
	 * Init GMT class
	 */
	
	$centreonGMT = new CentreonGMT();
	$centreonGMT->getMyGMTFromSession($sid);
	
	(isset($_GET["enable"]) && !check_injection($_GET["enable"])) ? $enable = urldecode($_GET["enable"]) : $enable = "enable";
	(isset($_GET["disable"]) && !check_injection($_GET["disable"])) ? $disable = urldecode($_GET["disable"]) : $disable = "disable";
	
	$enable = str_replace('&eacute', '&#233', $enable);
	$disable = str_replace('&eacute', '&#233', $disable);

	(isset($_GET["num"]) 		&& !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) 		&& !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	(isset($_GET["instance"])/* && !check_injection($_GET["instance"])*/) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["search"]) 	&& !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	
	(isset($_GET["sort_type"]) 	&& !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "last_check";
	(isset($_GET["search_type_host"]) 		&& !check_injection($_GET["search_type_host"])) ? $search_type_host = htmlentities($_GET["search_type_host"]) : $search_type_host = 1;
	(isset($_GET["search_type_service"])	&& !check_injection($_GET["search_type_service"])) ? $search_type_service = htmlentities($_GET["search_type_service"]) : $search_type_service = 1;
		
	(isset($_GET["order"]) 		&& !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $oreder = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) 			&& !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) 			&& !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";
	(isset($_GET["nc"]) 		&& !check_injection($_GET["nc"])) ? $nc = htmlentities($_GET["nc"]) : $nc = "0";

	/* LCA */
	// check is admin
	$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$sid."'");
	$user =& $res1->fetchRow();
	$user_id = $user["user_id"];
	$res2 =& $pearDB->query("SELECT contact_admin FROM contact WHERE contact_id = '".$user_id."'");
	$admin =& $res2->fetchRow();
	$is_admin = 0;
	$is_admin = $admin["contact_admin"];

	if (!$is_admin){
		$_POST["sid"] = $sid;
		$lca =  getLCAHostByName($pearDB);
		$lcaSTR = getLCAHostStr($lca["LcaHost"]);
	}
	
	/* security end*/

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
	$tab_color_host[0] = "normal";
	$tab_color_host[1] = "#FD8B46";//$general_opt["color_down"];
	$tab_color_host[2] = "normal";

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");


	/* Get Host status */
	$rq1 = "SELECT nhs.current_state," .
			" nhs.problem_has_been_acknowledged, " .
			" nhs.passive_checks_enabled," .
			" nhs.active_checks_enabled," .
			" no.name1 as host_name" .
			" FROM ".$ndo_base_prefix."hoststatus nhs, ".$ndo_base_prefix."objects no" .
			" WHERE no.object_id = nhs.host_object_id AND no.objecttype_id = 1";

	if (!$is_admin)
		$rq1 .= " AND no.name1 IN (".$lcaSTR." )";

	if ($instance != "ALL")
		$rq1 .= " AND no.instance_id = ".$instance;

	($o == "meta") ? $rq1 .= " AND no.name1 = 'Meta_Module'" : $rq1 .= " AND no.name1 != 'Meta_Module'";


	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	while($ndo =& $DBRESULT_NDO1->fetchRow())
		$host_status[$ndo["host_name"]] = $ndo;
	
	/* 
	 * Get Service status 
	 */
	$rq =	" SELECT " .
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
			" no.name1 as host_name," .
			" no.name2 as service_description" .
			" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
			" WHERE no.object_id = nss.service_object_id".
			" AND no.name1 not like 'qos_Module'".
			" AND no.is_active = '1' AND objecttype_id = '2' AND `nss`.`active_checks_enabled` = '1'";

	if (!$is_admin)
		$rq .= " AND no.name1 IN (".$lcaSTR." )";
	
	if ($o == "meta")
		$rq .= " AND no.name1 = 'Meta_Module'";
	else
		$rq .= " AND no.name1 != 'Meta_Module'";


	if ($instance != "ALL")
		$rq .= " AND no.instance_id = ".$instance;


	if (isset($host_name) &&$host_name != "")
		$rq .= " AND no.name1 like '%" . $host_name . "%'  ";

	if ($search_type_host && $search_type_service && $search){
		$rq .= " AND ( no.name1 like '%" . $search . "%' OR no.name2 like '%" . $search . "%' OR nss.output like '%" . $search . "%') ";
	} else if (!$search_type_service && $search_type_host && $search){
		$rq .= " AND no.name1 like '%" . $search . "%'";
	} else if ($search_type_service && !$search_type_host && $search){
		$rq .= " AND no.name2 like '%" . $search . "%'";
	} 
	
	if ($o == "svcpb")
		$rq .= " AND nss.current_state != 0 ";
	if ($o == "svc_ok")
		$rq .= " AND nss.current_state = 0 ";
	if ($o == "svc_warning")
		$rq .= " AND nss.current_state = 1 ";
	if ($o == "svc_critical")
		$rq .= " AND nss.current_state = 2 ";
	if ($o == "svc_unknown")
		$rq .= " AND nss.current_state = 3 ";
	$rq_pagination = $rq;

	switch($sort_type){
			case 'host_name' : 				$rq .= " order by no.name1,no.name2 ". $order; break;
			case 'service_description' : 	$rq .= " order by no.name2,no.name1 ". $order; break;
			case 'last_check' : 			$rq .= " order by nss.last_check,no.name1,no.name2 ". $order; break;
			case 'next_check' : 			$rq .= " order by nss.next_check,no.name1,no.name2 ". $order; break;
			case 'active_check' : 			$rq .= " order by nss.active_check,no.name1,no.name2 ". $order; break;
			default : 						$rq .= " order by no.name1 ". $order; break;
	}

	$rq .= " LIMIT ".($num * $limit).",".$limit;

	$DBRESULT_NDO =& $pearDBndo->query($rq);
	if (PEAR::isError($DBRESULT_NDO))
		print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br />";
	$buffer .= '<reponse>';
	$ct = 0;
	$flag = 0;

	/* Get Pagination Rows */
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	if (PEAR::isError($DBRESULT_PAGINATION))
		print "DB Error : ".$DBRESULT_PAGINATION->getDebugInfo()."<br />";
	$numRows = $DBRESULT_PAGINATION->numRows();
	$buffer .= '<i>';
	$buffer .= '<numrows>'.$numRows.'</numrows>';
	$buffer .= '<num>'.$num.'</num>';
	$buffer .= '<limit>'.$limit.'</limit>';
	$buffer .= '<p>'.$p.'</p>';
	$buffer .= '<nc>'.$nc.'</nc>';
	$buffer .= '<o>'.$o.'</o>';
	$buffer .= '</i>';
	/* End Pagination Rows */

	$host_prev = "";
	$class = "list_one";

	$color_en = array("1" => "#00ff00", "0" => "#ff0000");
	$color_en_label = array("1" => $enable, "0" => $disable);

	while($ndo =& $DBRESULT_NDO->fetchRow()){
		if (isset($host_status[$ndo["host_name"]]) ){
			$color_host = $tab_color_host[$host_status[$ndo["host_name"]]["current_state"]]; //"#FF0000";
			$color_service = $tab_color_service[$ndo["current_state"]];
			$passive = 0;
			$active = 1;
			$last_check = " ";
			$duration = " ";
			if ($ndo["last_state_change"] > 0)
				$duration = Duration::toString(time() - $ndo["last_state_change"]);

			if ($class == "list_one")
				$class = "list_two";
			else
				$class = "list_one";

			if ($tab_status_svc[$ndo["current_state"]] == "CRITICAL"){
				if ($ndo["problem_has_been_acknowledged"] == 1)
					$class = "list_four";
				else
					$class = "list_down";
			} else {
				if( $ndo["problem_has_been_acknowledged"] == 1)
					$class = "list_four";
			}

			$buffer .= '<l class="'.$class.'">';
			$buffer .= '<o>'. $ct++ . '</o>';
			$buffer .= '<f>'. $flag . '</f>';

			if ($host_prev == $ndo["host_name"]){
				$buffer .= '<hc>transparent</hc>';
				$buffer .= '<hn none="1">'. $ndo["host_name"] . '</hn>';
			} else {
				$host_prev = $ndo["host_name"];
				$buffer .= '<hc>'.$color_host.'</hc>';
				$buffer .= '<hn none="0">'. $ndo["host_name"] . '</hn>';
			}

			$buffer .= '<hs><![CDATA['. $host_status[$ndo["host_name"]]["current_state"]  . ']]></hs>';
			$buffer .= '<sd><![CDATA['. $ndo["service_description"] . ']]></sd>';
			$buffer .= '<ac>'. $color_en_label[$ndo["active_checks_enabled"]] . '</ac>';
			$buffer .= '<sc>'.$color_service.'</sc>';
			$buffer .= '<cs>'. $tab_status_svc[$ndo["current_state"]].'</cs>';
			$buffer .= '<po><![CDATA['. $ndo["plugin_output"].']]></po>';
			$buffer .= '<ca>'. $ndo["current_attempt"] . '</ca>';
			$buffer .= '<ne>'. $ndo["notifications_enabled"] . '</ne>';
			$buffer .= '<pa>'. $ndo["problem_has_been_acknowledged"] . '</pa>';
			$buffer .= '<pc>'. $passive . '</pc>';
			$buffer .= '<ac>'. $active . '</ac>';
			$buffer .= '<eh>'. $ndo["event_handler_enabled"] . '</eh>';
			$buffer .= '<is>'. $ndo["is_flapping"] . '</is>';
			$buffer .= '<fd>'. $ndo["flap_detection_enabled"] . '</fd>';
	        $buffer .= '<ha>'.$host_status[$ndo["host_name"]]["problem_has_been_acknowledged"]  .'</ha>';
	        $buffer .= '<hae>'.$host_status[$ndo["host_name"]]["active_checks_enabled"] .'</hae>';
	        $buffer .= '<hpe>'.$host_status[$ndo["host_name"]]["passive_checks_enabled"]  .'</hpe>';
			$buffer .= '<nc>'. $centreonGMT->getDate($date_time_format_status, $ndo["next_check"]) . '</nc>';
			$buffer .= '<lc>'. $centreonGMT->getDate($date_time_format_status, $ndo["last_check"]) . '</lc>';
			$buffer .= '<d>'. $duration . '</d>';
			$buffer .= '</l>';
		}
	}
	/* end */

	if (!$ct){
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
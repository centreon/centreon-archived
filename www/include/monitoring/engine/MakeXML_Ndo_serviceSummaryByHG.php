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

	# if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
	$debugXML = 0;
	$buffer = '';

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path . "www/class/other.class.php");
	include_once($centreon_path . "www/DBconnect.php");
	include_once($centreon_path . "www/DBNDOConnect.php");	
	include_once($centreon_path . "www/include/monitoring/engine/common-Func.php");
	include_once($centreon_path . "www/include/common/common-Func-ACL.php");
	include_once($centreon_path . "www/include/common/common-Func.php");

	$ndo_base_prefix = getNDOPrefix();
	$general_opt = getStatusColor($pearDB);
	
	/* security check 2/2*/
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$res->fetchInto($session))
			get_error('bad session id');
	} else
		get_error('need session identifiant !');
	/* security end 2/2 */

	(isset($_GET["instance"])/* && !check_injection($_GET["instance"])*/) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["num"]) && !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) && !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	(isset($_GET["search"]) && !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) && !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "host_name";
	(isset($_GET["order"]) && !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $order = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) && !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) && !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";

	// check is admin
	$is_admin = isUserAdmin($sid);

	if (!$is_admin)	{
		$_POST["sid"] = $sid;
		$lca =  getLCAHostByName($pearDB);
		$lcaSTR = getLCAHostStr($lca["LcaHost"]);
		$lcaSTR_HG = getLCAHostStr($lca["LcaHostGroup"]);
	}

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

	/* Get Host status */

	$rq1 = 			" SELECT hg.alias, no.name1 as host_name, hgm.hostgroup_id, hgm.host_object_id, hs.current_state".
					" FROM " .$ndo_base_prefix."hostgroups hg," .$ndo_base_prefix."hostgroup_members hgm, " .$ndo_base_prefix."hoststatus hs, " .$ndo_base_prefix."objects no";
	if (!$is_admin)
		$rq1 .= ", centreon_acl";
			$rq1 .= " WHERE hs.host_object_id = hgm.host_object_id".
					" AND no.object_id = hgm.host_object_id" .
					" AND hgm.hostgroup_id = hg.hostgroup_id".
					" AND no.name1 not like 'OSL_Module'";
					" AND no.name1 not like 'Meta_Module'";

	if ($instance != "ALL")
		$rq1 .= 	" AND no.instance_id = ".$instance;
	
	/*if ($o == "svcSumHG"){
		$rq1 .= 	" AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0)";
	}*/
	if ($o == "svcSumHG_ack_0") {
		$rq1 .= 	" AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0)";
	}
	if ($o == "svcSumHG_ack_1"){
		$rq1 .= 	" AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1 AND nss.current_state != 0)";
	}
		
	if ($search != "")
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
		
	if (!$is_admin)
		$rq1 .= " AND no.name1 = centreon_acl.host_name AND group_id IN (".groupsListStr(getGroupListofUser($pearDB)).")";
	
	//print $rq1;
		
	$rq_pagination = $rq1;
	
	/* Get Pagination Rows */
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	if (PEAR::isError($DBRESULT_PAGINATION))
		print "DB Error : ".$DBRESULT_PAGINATION->getDebugInfo()."<br />";
	$numRows = $DBRESULT_PAGINATION->numRows();

	$rq1 .= " ORDER BY hg.alias";
	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	$buffer .= '<reponse>';
	$buffer .= '<i>';
	$buffer .= '<numrows>'.$numRows.'</numrows>';
	$buffer .= '<num>'.$num.'</num>';
	$buffer .= '<limit>'.$limit.'</limit>';
	$buffer .= '<p>'.$p.'</p>';
	$o == "svcOVHG" ? $buffer .= '<s>1</s>' : $buffer .= '<s>0</s>';
	$buffer .= '</i>';

	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";

	$class = "list_one";
	$ct = 0;
	$flag = 0;

	$tab_final = array();
	while($DBRESULT_NDO1->fetchInto($ndo))	{
		if (isset($lca["LcaHostGroup"][$ndo["alias"]]) || !isset($lca["LcaHostGroup"])){
			
			if ($o != "svcSum_pb" && $o != "svcSum_ack_1"  && $o !=  "svcSum_ack_0")
				$tab_final[$ndo["host_name"]]["nb_service_k"] = 0 + get_services_status($ndo["host_name"], 0);
			else
				$tab_final[$ndo["host_name"]]["nb_service_k"] = 0;
				
			$tab_final[$ndo["host_name"]]["nb_service_w"] = 0 + get_services_status($ndo["host_name"], 1);
			$tab_final[$ndo["host_name"]]["nb_service_c"] = 0 + get_services_status($ndo["host_name"], 2);
			$tab_final[$ndo["host_name"]]["nb_service_u"] = 0 + get_services_status($ndo["host_name"], 3);
			$tab_final[$ndo["host_name"]]["nb_service_p"] = 0 + get_services_status($ndo["host_name"], 4);
			
			$tab_final[$ndo["host_name"]]["cs"] = $ndo["current_state"];
			$tab_final[$ndo["host_name"]]["hg_name"] = $ndo["alias"];
			if ($tab_final[$ndo["host_name"]]["nb_service_w"] == 0 && $tab_final[$ndo["host_name"]]["nb_service_k"] == 0 && $tab_final[$ndo["host_name"]]["nb_service_c"] == 0 && $tab_final[$ndo["host_name"]]["nb_service_u"] == 0 && $tab_final[$ndo["host_name"]]["nb_service_p"] == 0)
				unset($tab_final[$ndo["host_name"]]);
		}
	}

	$hg = "";
	foreach ($tab_final as $host_name => $tab){
		$class == "list_one" ? $class = "list_two" : $class = "list_one";
		if (isset($tab["hg_name"]) && $hg != $tab["hg_name"]){
			if ($hg != "")
				$buffer .= '</hg>';
			$hg = $tab["hg_name"];
			$buffer .= '<hg>';
			$buffer .= '<hgn><![CDATA['. $tab["hg_name"]  .']]></hgn>';
		}
		$buffer .= '<l class="'.$class.'">';
		$buffer .= '<sk><![CDATA['. $tab["nb_service_k"]  . ']]></sk>';
		$buffer .= '<skc><![CDATA['. $tab_color_service[0]  . ']]></skc>';
		$buffer .= '<sw><![CDATA['. $tab["nb_service_w"]  . ']]></sw>';
		$buffer .= '<swc><![CDATA['. $tab_color_service[1]  . ']]></swc>';
		$buffer .= '<sc><![CDATA['. $tab["nb_service_c"]  . ']]></sc>';
		$buffer .= '<scc><![CDATA['. $tab_color_service[2]  . ']]></scc>';
		$buffer .= '<su><![CDATA['. $tab["nb_service_u"]  . ']]></su>';
		$buffer .= '<suc><![CDATA['. $tab_color_service[3]  . ']]></suc>';
		$buffer .= '<sp><![CDATA['. $tab["nb_service_p"]  . ']]></sp>';
		$buffer .= '<spc><![CDATA['. $tab_color_service[4]  . ']]></spc>';
		$buffer .= '<o>'. $ct++ . '</o>';
		$buffer .= '<hn><![CDATA['. $host_name  . ']]></hn>';
		$buffer .= '<hs><![CDATA['. $tab_status_host[$tab["cs"]]  . ']]></hs>';
		$buffer .= '<hc><![CDATA['. $tab_color_host[$tab["cs"]]  . ']]></hc>';
		$buffer .= '</l>';
	}
	$buffer .= '</hg>';

	if (!$ct){
		$buffer .= '<infos>';
		$buffer .= 'none';
		$buffer .= '</infos>';
	}
	$buffer .= '</reponse>';
	header('Content-Type: text/xml');
	echo $buffer;
?>
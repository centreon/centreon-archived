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
	 * if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
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
	(isset($_GET["instance"]) && !check_injection($_GET["instance"])) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["num"]) && !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) && !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	(isset($_GET["search"]) && !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) && !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "host_name";
	(isset($_GET["order"]) && !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $oreder = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) && !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) && !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";

	/*
	 * Get Status Colors
	 */
	$general_opt = getStatusColor($pearDB);

	function get_hosts_status($host_group_id, $status){
		global $pearDB, $pearDBndo, $ndo_base_prefix, $general_opt, $is_admin, $grouplist, $groupnumber;

		$tab_acl = "";
		$condition_acl = "";
		if (!$is_admin && $groupnumber){
			$tab_acl = ", centreon_acl";
			$condition_acl = " AND nh.display_name = centreon_acl.host_name AND centreon_acl.group_id IN (".groupsListStr($grouplist).") ";
		}
		$rq = 	" SELECT count( nhs.host_object_id ) AS nb".
				" FROM " .$ndo_base_prefix."hoststatus nhs".
				" WHERE nhs.current_state = '".$status."'".
				" AND nhs.host_object_id IN (SELECT nhgm.host_object_id FROM ".$ndo_base_prefix."hostgroup_members nhgm, ".$ndo_base_prefix."hosts nh $tab_acl WHERE nhgm.hostgroup_id =".$host_group_id." AND nh.host_object_id = nhgm.host_object_id $condition_acl)";

		$DBRESULT =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$tab =& $DBRESULT->fetchRow();
		return($tab["nb"]);
	}

	function get_services_status($host_group_id, $status){
		global $pearDB, $pearDBndo, $ndo_base_prefix, $general_opt, $instance,$lcaSTR, $is_admin, $grouplist, $groupnumber;

		$rq = 			" SELECT count( nss.service_object_id ) AS nb".
						" FROM " .$ndo_base_prefix."servicestatus nss ".
						" WHERE nss.current_state = '".$status."'".
						" AND nss.service_object_id".
						" IN (SELECT nno.object_id FROM " .$ndo_base_prefix."objects nno, centreon_acl WHERE nno.objecttype_id = '2' ";
						
		if (!$is_admin && $groupnumber)
			$rq .= 	" AND nno.name1 = centreon_acl.host_name AND nno.name2 = centreon_acl.service_description AND centreon_acl.group_id IN (".groupsListStr($grouplist).")";

		if ($instance != "ALL")
			$rq .= 	" AND nno.instance_id = ".$instance;

		$rq .= 		" AND nno.name1 IN (".
						" SELECT no.name1".
						" FROM " .$ndo_base_prefix."objects no, " .$ndo_base_prefix."hostgroup_members nhgm".
						" WHERE nhgm.hostgroup_id =".$host_group_id.
						" AND no.object_id = nhgm.host_object_id".")".
					" )";
		$DBRESULT =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$tab =& $DBRESULT->fetchRow();
		return($tab["nb"]);
	}

	// check is admin
	$is_admin = isUserAdmin($sid);

	if (!$is_admin)
		$_POST["sid"] = $sid;


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
			$rq1 = 	" SELECT " .
					" no.name1 as hostgroup_name," .
					" hg.hostgroup_id" .
					" FROM " .$ndo_base_prefix."hostgroups hg, ".$ndo_base_prefix."objects no, centreon_acl ".
					" WHERE no.object_id = hg.hostgroup_object_id AND hg.alias != 'meta_hostgroup'";

	if ($search != "")
		$rq1 .= 	" AND no.name1 like '%" . $search . "%' ";
	
	if ($instance != "ALL")
		$rq1 .= 	" AND no.instance_id = ".$instance;
	$rq1 .= 		" group by no.name1 ";
	$rq1 .= 		" order by no.name1 ". $order;

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

	while ($ndo =& $DBRESULT_NDO1->fetchRow()) 
	{
		if (isset($lca["LcaHostGroup"][$ndo["hostgroup_name"]]) || !isset($lca)) {
			$nb_host_up = 0 + get_hosts_status($ndo["hostgroup_id"], 0);
			$nb_host_down = 0 + get_hosts_status($ndo["hostgroup_id"], 1);
			$nb_host_unreachable = 0 + get_hosts_status($ndo["hostgroup_id"], 2);
	
			$nb_service_k = 0 + get_services_status($ndo["hostgroup_id"], 0);
			$nb_service_w = 0 + get_services_status($ndo["hostgroup_id"], 1);
			$nb_service_c = 0 + get_services_status($ndo["hostgroup_id"], 2);
			$nb_service_u = 0 + get_services_status($ndo["hostgroup_id"], 3);
			$nb_service_p = 0 + get_services_status($ndo["hostgroup_id"], 4);
	
			$passive = 0;
			$active = 1;
			$last_check = " ";
			$duration = " ";
	
			$class == "list_one" ? $class = "list_two" : $class = "list_one";
			if ($nb_host_up || $nb_host_down || $nb_host_unreachable || $nb_service_k || $nb_service_w || $nb_service_c || $nb_service_u || $nb_service_p){
				$buffer .= '<l class="'.$class.'">';
				$buffer .= '<o>'. $ct++ . '</o>';
				$buffer .= '<hn><![CDATA['. $ndo["hostgroup_name"]  . ']]></hn>';
				$buffer .= '<hu>'. $nb_host_up  . '</hu>';
				$buffer .= '<huc>'. $tab_color_host[0]  . '</huc>';
				$buffer .= '<hd>'.  $nb_host_down . '</hd>';
				$buffer .= '<hdc>'. $tab_color_host[1]  . '</hdc>';
				$buffer .= '<hur>'. $nb_host_unreachable  . '</hur>';
				$buffer .= '<hurc>'. $tab_color_host[2]  . '</hurc>';
				$buffer .= '<sk>'. $nb_service_k  . '</sk>';
				$buffer .= '<skc>'. $tab_color_service[0]  . '</skc>';
				$buffer .= '<sw>'. $nb_service_w  . '</sw>';
				$buffer .= '<swc>'. $tab_color_service[1]  . '</swc>';
				$buffer .= '<sc>'. $nb_service_c  . '</sc>';
				$buffer .= '<scc>'. $tab_color_service[2]  . '</scc>';
				$buffer .= '<su>'. $nb_service_u  . '</su>';
				$buffer .= '<suc>'. $tab_color_service[3]  . '</suc>';
				$buffer .= '<sp>'. $nb_service_p  . '</sp>';
				$buffer .= '<spc>'. $tab_color_service[4]  . '</spc>';
				$buffer .= '</l>';
			}
		}
	}

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
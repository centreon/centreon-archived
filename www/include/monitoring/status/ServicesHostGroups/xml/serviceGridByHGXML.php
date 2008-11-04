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
	include_once($centreon_path . "www/include/monitoring/status/Common/common-Func.php");
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


	/* requisit */
	(isset($_GET["num"]) 			&& !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) 			&& !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	(isset($_GET["instance"])		/* && !check_injection($_GET["instance"])*/) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["search"]) 		&& !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) 		&& !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "host_name";
	(isset($_GET["order"]) 			&& !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $oreder = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) 				&& !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) 				&& !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";

	// check is admin
	$is_admin = isUserAdmin($sid);

	if (!$is_admin)	{
		$_POST["sid"] = $sid;
		$lca =  getLCAHostByAlias($pearDB);
		$lcaSTR = getLCAHostStr($lca["LcaHost"]);
		$lcaSTR_HG = getLCAHostStr($lca["LcaHostGroup"]);
	}
	
	//print_r($lca);
	
	/*
	 * Get Acl Group list
	 */
	
	$grouplist = getGroupListofUser($pearDB); 
	$groupnumber = count($grouplist);

	function get_services($host_name){
		
		global $pearDBndo, $pearDB, $ndo_base_prefix, $general_opt, $o, $instance, $is_admin, $groupnumber, $grouplist;

		$rq = 		" SELECT no.name1, no.name2 as service_name, nss.current_state" .
					" FROM `" .$ndo_base_prefix."servicestatus` nss, `" .$ndo_base_prefix."objects` no";
				
		if (!$is_admin && $groupnumber)
			$rq .= ", centreon_acl ";
					
		$rq .= 		" WHERE no.object_id = nss.service_object_id" .
					" AND no.name1 NOT LIKE 'OSL_Module'" .
					" AND no.name1 NOT LIKE 'Meta_Module'";
		
		if (!$is_admin && $groupnumber)
			$rq .= 	" AND no.name1 = centreon_acl.host_name AND no.name2 = centreon_acl.service_description AND centreon_acl.group_id IN (".groupsListStr($grouplist).")";

		if	($o == "svcgridHG_pb" || $o == "svcOVHG_pb")
			$rq .= 	" AND nss.current_state != 0" ;

		if ($o == "svcgridHG_ack_0" || $o == "svcOVHG_ack_0")
			$rq .= 	" AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" ;

		if ($o == "svcgridHG_ack_1" || $o == "svcOVHG_ack_1")
			$rq .= 	" AND nss.problem_has_been_acknowledged = 1" ;

		$rq .= 		" AND no.object_id" .
					" IN (" .
					" SELECT nno.object_id" .
					" FROM ".$ndo_base_prefix."objects nno" .
					" WHERE nno.objecttype_id =2" .
					" AND nno.name1 = '".$host_name."'" .
					" AND nno.name1 not like 'OSL_Module'".
					" )";
		
		if($instance != "ALL")
			$rq .= " AND no.instance_id = ".$instance;

		$DBRESULT =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$tab = array();
		while ($svc =& $DBRESULT->fetchRow()){
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

	$tab_color_service = array(0 => $general_opt["color_ok"], 1 => $general_opt["color_warning"], 2 => $general_opt["color_critical"], 3 => $general_opt["color_unknown"], 4 => $general_opt["color_pending"]);
    $tab_color_host = array(0 => $general_opt["color_up"], 1 => $general_opt["color_down"], 2 => $general_opt["color_unreachable"]);

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");

	/* Get Host status */

	$rq1 =  	" SELECT DISTINCT no.name1 as host_name, hg.alias, hgm.hostgroup_id, hgm.host_object_id, hs.current_state".
				" FROM " .$ndo_base_prefix."hostgroups hg," .$ndo_base_prefix."hostgroup_members hgm, " .$ndo_base_prefix."hoststatus hs, " .$ndo_base_prefix."objects no ";
	
	if (!$is_admin && $groupnumber)
		$rq1 .= ", centreon_acl ";
	
	$rq1 .=  	" WHERE hs.host_object_id = hgm.host_object_id".
				" AND no.object_id = hgm.host_object_id" .
				" AND hgm.hostgroup_id = hg.hostgroup_id".
				" AND no.name1 not like 'OSL_Module'".
				" AND no.name1 not like 'Meta_Module'".
				" AND no.is_active = 1";
	
	if (!$is_admin && $groupnumber)
		$rq1 .= " AND no.name1 = centreon_acl.host_name AND group_id IN (".groupsListStr($grouplist).") AND hg.alias IN ($lcaSTR_HG)";
	
	if ($o == "svcgridHG_pb" || $o == "svcOVHG_pb")
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0" .
				" AND nno.name1 not like 'OSL_Module')";

	if ($o == "svcgridHG_ack_0" || $o == "svcOVHG_ack_0")
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" .
				" AND nno.name1 not like 'OSL_Module')";

	if ($o == "svcgridHG_ack_1" || $o == "svcOVHG_ack_1")
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1" .
				" AND nno.name1 not like 'OSL_Module')";
	
	if ($search != "")
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";

	if ($instance != "ALL")
		$rq1 .= " AND no.instance_id = ".$instance;

	$rq_pagination = $rq1;

	
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	if (PEAR::isError($DBRESULT_PAGINATION))
		print "DB Error : ".$DBRESULT_PAGINATION->getDebugInfo()."<br />";
	$numRows = $DBRESULT_PAGINATION->numRows();

	switch ($sort_type){
			case 'current_state' : $rq1 .= " order by hg.alias, hs.current_state ". $order.",no.name1 "; break;
			default : $rq1 .= " order by hg.alias, no.name1 ". $order; break;
	}
	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	$buffer .= '<reponse>';
	$buffer .= '<i>';
	$buffer .= '<numrows>'.$numRows.'</numrows>';
	$buffer .= '<num>'.$num.'</num>';
	$buffer .= '<limit>'.$limit.'</limit>';
	$buffer .= '<host_name>'._("Hosts").'</host_name>';
	$buffer .= '<services>'._("Services").'</services>';
	$buffer .= '<p>'.$p.'</p>';
	$o == "svcOVHG" ? $buffer .= '<s>1</s>' : $buffer .= '<s>0</s>';
	$buffer .= '</i>';
	
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	$class = "list_one";
	$ct = 0;
	$flag = 0;
	$tab_final =  array();
	while ($ndo =& $DBRESULT_NDO1->fetchRow()) {
			
		$tab_svc = get_services($ndo["host_name"]);
		if (count($tab_svc)) {
			if (!isset($tab_final[$ndo["alias"]]))
				$tab_final[$ndo["alias"]] = array();
			if (!isset($tab_final[$ndo["alias"]][$ndo["host_name"]]))
				$tab_final[$ndo["alias"]][$ndo["host_name"]] = array("0"=>0,"1"=>0,"2"=>0,"3"=>0,"4"=>0);
			
			$tab_final[$ndo["alias"]][$ndo["host_name"]]["tab_svc"] = $tab_svc;
			$tab_final[$ndo["alias"]][$ndo["host_name"]]["cs"] = $ndo["current_state"];
		}
	}
	
	$hg = "";
	if (isset($tab_final))
		foreach ($tab_final as $hg_name => $tab_host) {
			foreach ($tab_host as $host_name => $tab) {
				$class == "list_one" ? $class = "list_two" : $class = "list_one";
				if (isset($hg_name) && $hg != $hg_name){
					if ($hg != "")
						$buffer .= '</hg>';
					$hg = $hg_name;
					$buffer .= '<hg>';
					$buffer .= '<hgn><![CDATA['. $hg_name .']]></hgn>';
				}
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
	$buffer .= '</hg>';
	/* end */

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
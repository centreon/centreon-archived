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

	$debug = 0;
	$flag_reset = 0;
	
	foreach ($_GET as $key => $value){
		$value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
		$value = filter_var($value, INPUT_GET);	
		$_GET[$key] = $value;
	}
	
	include_once "@CENTREON_ETC@/centreon.conf.php";	
	include_once $centreon_path . "www/include/common/common-Func.php";
	include_once $centreon_path . "www/class/centreonACL.class.php";
	include_once $centreon_path . "www/class/centreonDB.class.php";
	
	/* 
	 * Connect to oreon DB 
	 */

	$pearDB = new CentreonDB();
		
	$ndo_base_prefix = getNDOPrefix();
	$general_opt = getStatusColor($pearDB);
	
	/*
	 * Session...
	 */
	$debug_session = 'KO';

	/*
	 * sessionID check and refresh
	 */
	$sid = isset($_POST["sid"]) ? $_POST["sid"] : 0;
	$sid = isset($_GET["sid"]) ? $_GET["sid"] : $sid;

	/* 
	 * Security Check
	 */
	if (!check_injection($sid)){
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if ($session =& $res->fetchRow())
			$DBRESULT2 =& $pearDB->query("UPDATE `session` SET `last_reload` = '".time()."', `ip_address` = '".$_SERVER["REMOTE_ADDR"]."' WHERE CONVERT( `session_id` USING utf8 ) = '".$sid."' LIMIT 1");
		else
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	/*
	 * LCA
	 */
	$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$sid."'");
	$user =& $res1->fetchRow();
	$user_id = $user["user_id"];

	global $is_admin;
	
	$is_admin =  isUserAdmin($sid);	
		
	/*
	 * Get Acl Group list
	 */
	$access = new CentreonACL($user_id, $is_admin);
	$grouplist = $access->getAccessGroups(); 
	$groupnumber = count($grouplist);
	$grouplistStr = $access->getAccessGroupsString(); 
	
	function restore_session($statistic_service = 'null', $statistic_host = 'null'){
		global $pearDB;
		if (isset($statistic_service) && !is_null($statistic_service)){
			$sql = 	" UPDATE session SET " .
					" s_nbHostsUp = '".$statistic_host["UP"]."'," .
					" s_nbHostsDown = '".$statistic_host["DOWN"]."'," .
					" s_nbHostsUnreachable = '".$statistic_host["UNREACHABLE"]."'," .
					" s_nbHostsPending = '".$statistic_host["PENDING"]."'," .
					" s_nbServicesOk = '".$statistic_service["OK"]."'," .
					" s_nbServicesWarning = '".$statistic_service["WARNING"]."'," .
					" s_nbServicesCritical = '".$statistic_service["CRITICAL"]."'," .
					" s_nbServicesUnknown = '".$statistic_service["UNKNOWN"]."'," .
					" s_nbServicesPending = '".$statistic_service["PENDING"]."'" .
					" WHERE session_id = '".$_POST["sid"]."'";
			$DBRESULT =& $pearDB->query($sql);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}

	function read($sid){
		global $pearDB, $flag,$centreon_path, $ndo_base_prefix, $is_admin, $groupnumber, $grouplistStr, $access;
		
		$oreon = "";
		$search = "";
		$search_type_service = 0;
		$search_type_host = 0;

		/*
		 * Connect to NDO
		 */		
		include_once($centreon_path . "www/class/centreonDB.class.php");
		
		$pearDBndo = new CentreonDB("ndo");
				
		if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
			print "<data>Can't connect to ndo Database</data>";
			exit();
		} 

		/*
		 * Init stat for resume
		 */
		$statistic_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => 0);
		$statistic_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);

		$DBRESULT_OPT =& $pearDB->query("SELECT color_ok,color_warning,color_critical,color_unknown,color_pending,color_up,color_down,color_unreachable FROM general_opt");
		if (PEAR::isError($DBRESULT_OPT))
			print "DB Error : ".$DBRESULT_OPT->getDebugInfo()."<br />";
		$general_opt =& $DBRESULT_OPT->fetchRow();
	
		/* 
		 * Get Host NDO status 
		 */		
		$rq1 = 	" SELECT count(DISTINCT ".$ndo_base_prefix."objects.name1), ".$ndo_base_prefix."hoststatus.current_state" .
				" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects, centreon_acl " .
				" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id " .
				" AND ".$ndo_base_prefix."objects.is_active = 1 " .
				" AND ".$ndo_base_prefix."objects.name1 = centreon_acl.host_name " .
				$access->queryBuilder("AND", "centreon_acl.group_id", $grouplistStr) .				
				" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
				" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
		
		$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
		if (PEAR::isError($DBRESULT_NDO1))
			print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
		
		$host_stat = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);

		while ($ndo =& $DBRESULT_NDO1->fetchRow())
			$host_stat[$ndo["current_state"]] = $ndo["count(DISTINCT ".$ndo_base_prefix."objects.name1)"];
		$DBRESULT_NDO1->free();
		
		/* 
		 * Get Service NDO status 
		 */
		if (!$is_admin)
			$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
					" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl " .
					" WHERE no.object_id = nss.service_object_id".
					" AND no.name1 NOT LIKE '_Module_%' ".					
					" AND no.name1 = centreon_acl.host_name ".
					" AND no.name2 = centreon_acl.service_description " .
					" AND centreon_acl.group_id IN (".$grouplistStr.") ".
					" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
		else
			$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
					" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
					" WHERE no.object_id = nss.service_object_id".
					" AND no.name1 NOT LIKE '_Module_%' ".
					" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";			
		
		$DBRESULT_NDO2 =& $pearDBndo->query($rq2);
		if (PEAR::isError($DBRESULT_NDO2))
			print "DB Error : ".$DBRESULT_NDO2->getDebugInfo()."<br />";

		$svc_stat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
		while ($ndo =& $DBRESULT_NDO2->fetchRow())
			$svc_stat[$ndo["current_state"]] = $ndo["count(nss.current_state)"];
		$DBRESULT_NDO2->free();
		
		restore_session($statistic_service, $statistic_host);

		/*
		 * Create Buffer
		 */
		$buffer  = '<?phpxml version="1.0"?>';
		$buffer .= '<reponse>';
		$buffer .= '<infos>';
		$buffer .= '<filetime>'.time().'</filetime>';
		$buffer .= '</infos>';
		$buffer .= '<s>';
		$buffer .= '<o>'.$svc_stat["0"].'</o>';
		$buffer .= '<w>'.$svc_stat["1"].'</w>';
		$buffer .= '<c>'.$svc_stat["2"].'</c>';
		$buffer .= '<un1>'.$svc_stat["3"].'</un1>';
		$buffer .= '<p1>'.$svc_stat["4"].'</p1>';
		$buffer .= '<up>'.$host_stat["0"].'</up>';
		$buffer .= '<d>'.$host_stat["1"].'</d>';
		$buffer .= '<un2>'.$host_stat["2"].'</un2>';
		$buffer .= '<p2>'.$host_stat["3"].'</p2>';
		$buffer .= '</s>';
		$buffer .= '</reponse>';
		
		/*
		 * send Header
		 */
		header('Content-Type: text/xml');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: no-cache, must-revalidate'); 
		
		/*
		 * Display Buffer
		 */
		echo $buffer;
	}
	
	read($sid);
?>
<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Cedrick Facon

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	$debug = 0;
	$flag_reset = 0;
	$oreonPath = '/srv/oreon/';

	if ($oreonPath == '@INSTALL_DIR_OREON@'){
		$buffer = null;
		$buffer .= '<reponse>';
		$buffer .= 'none';
		$buffer .= '</reponse>';
		header('Content-Type: text/xml');
		echo $buffer;
		exit(0);
	}

	foreach ($_GET as $key => $value){
		$value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
		$value = filter_var($value, INPUT_GET);	
		$_GET[$key] = $value;
	}

	## pearDB init
	require_once 'DB.php';

	include_once($oreonPath . "etc/centreon.conf.php");
	include_once($oreonPath . "www/include/common/common-Func-ACL.php");
	include_once($oreonPath . "www/include/common/common-Func.php");

	/* Connect to oreon DB */

	$dsn = array('phptype'  => 'mysql',
			     'username' => $conf_oreon['user'],
			     'password' => $conf_oreon['password'],
			     'hostspec' => $conf_oreon['host'],
			     'database' => $conf_oreon['db'],);
	$options = array('debug'=> 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,);
	$pearDB =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDB)) die("Connecting problems with oreon database : " . $pearDB->getMessage());
	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);

	$ndo_base_prefix = getNDOPrefix();
	
	# Session...
	$debug_session = 'KO';

	# sessionID check and refresh
	$sid = isset($_POST["sid"]) ? $_POST["sid"] : 0;
	$sid = isset($_GET["sid"]) ? $_GET["sid"] : $sid;

	/* security check 2/2*/
	if(!check_injection($sid)){
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if($res->fetchInto($session)){
			/* update session */
			$DBRESULT2 =& $pearDB->query("UPDATE `session` SET `last_reload` = '".time()."', `ip_address` = '".$_SERVER["REMOTE_ADDR"]."' WHERE CONVERT( `session_id` USING utf8 ) = '".$sid."' LIMIT 1");
		} else
			get_error('bad session id');
	} else
		get_error('need session identifiant !');
	/* security end 2/2 */

	/*
	 * LCA
	 */
	$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$sid."'");
	$user = $res1->fetchRow();
	$user_id = $user["user_id"];

	$res2 =& $pearDB->query("SELECT contact_admin FROM contact WHERE contact_id = '".$user_id."'");
	$admin = $res2->fetchrow();
	
	global $is_admin;
	
	$is_admin = 0;
	$is_admin = $admin["contact_admin"];
	
	if (!$is_admin){
		$_POST["sid"] = $sid;
		$lca =  getLCAHostByName($pearDB);
		$lcaSTR = getLCAHostStr($lca["LcaHost"]);
	}
	 

	function restore_session($statistic_service = 'null', $statistic_host = 'null'){
		global $pearDB;
		if (isset($statistic_service) && !is_null($statistic_service)){
			$sql = "UPDATE session SET " .
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

	function read($sid, $lcaSTR){
		global $pearDB, $flag,$oreonPath, $ndo_base_prefix, $is_admin;
		$oreon = "";
		$search = "";
		$search_type_service = 0;
		$search_type_host = 0;

		## calcul stat for resume
		$statistic_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => 0);
		$statistic_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);


		$DBRESULT_OPT =& $pearDB->query("SELECT color_ok,color_warning,color_critical,color_unknown,color_pending,color_up,color_down,color_unreachable FROM general_opt");
		if (PEAR::isError($DBRESULT_OPT))
			print "DB Error : ".$DBRESULT_OPT->getDebugInfo()."<br />";
		$DBRESULT_OPT->fetchInto($general_opt);

		include_once($oreonPath . "www/DBNDOConnect.php");

		/* Get HostNDO status */
		if (!$is_admin)
			$rq1 = 	" SELECT count(".$ndo_base_prefix."hoststatus.current_state) , ".$ndo_base_prefix."hoststatus.current_state" .
					" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects " .
					" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id AND ".$ndo_base_prefix."objects.is_active = 1 " .
					" AND ".$ndo_base_prefix."objects.name1 IN (".$lcaSTR." )" .
					" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
					" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
		else
			$rq1 = 	" SELECT count(".$ndo_base_prefix."hoststatus.current_state) , ".$ndo_base_prefix."hoststatus.current_state" .
					" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects " .
					" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id AND ".$ndo_base_prefix."objects.is_active = 1 " .
					" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
					" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
		$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
		if (PEAR::isError($DBRESULT_NDO1))
			print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
		
		$host_stat = array();
		$host_stat[0] = 0;
		$host_stat[1] = 0;
		$host_stat[2] = 0;
		$host_stat[3] = 0;
		while($DBRESULT_NDO1->fetchInto($ndo))
			$host_stat[$ndo["current_state"]] = $ndo["count(nagios_hoststatus.current_state)"];
		/* end */

		/* Get ServiceNDO status */
		if (!$is_admin)
			$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
					" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
					" WHERE no.object_id = nss.service_object_id".
					" AND no.name1 not like 'OSL_Module' ".
					" AND no.name1 not like 'Meta_Module' ".
					" AND no.name1 IN (".$lcaSTR." ) ".
					" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
		else
			$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
					" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
					" WHERE no.object_id = nss.service_object_id".
					" AND no.name1 not like 'OSL_Module' ".
					" AND no.name1 not like 'Meta_Module' ".
					" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
			
		$DBRESULT_NDO2 =& $pearDBndo->query($rq2);
		if (PEAR::isError($DBRESULT_NDO2))
			print "DB Error : ".$DBRESULT_NDO2->getDebugInfo()."<br />";

		$svc_stat = array();
		$svc_stat[0] = 0;
		$svc_stat[1] = 0;
		$svc_stat[2] = 0;
		$svc_stat[3] = 0;
		$svc_stat[4] = 0;
		while($DBRESULT_NDO2->fetchInto($ndo))
			$svc_stat[$ndo["current_state"]] = $ndo["count(nss.current_state)"];
		/* end */

		$statistic_service["OK"] = $svc_stat[0];
		$statistic_service["WARNING"] = $svc_stat[1];
		$statistic_service["CRITICAL"] = $svc_stat[2];
		$statistic_service["UNKNOWN"] = $svc_stat[3];
		$statistic_service["PENDING"] = $svc_stat[4];
		$statistic_host["UP"] = $host_stat[0];
		$statistic_host["DOWN"] = $host_stat[1];
		$statistic_host["UNREACHABLE"] = $host_stat[2];
		$statistic_host["PENDING"] = $host_stat[3];

		restore_session($statistic_service, $statistic_host);

		$buffer = null;
		$buffer  = '<?phpxml version="1.0"?>';
		$buffer .= '<reponse>';
		$buffer .= '<infos>';
		$buffer .= '<filetime>'.time().'</filetime>';
		$buffer .= '</infos>';
		$buffer .= '<stats>';
		$buffer .= '<statistic_service_ok>'.$svc_stat["0"].'</statistic_service_ok>';
		$buffer .= '<statistic_service_warning>'.$svc_stat["1"].'</statistic_service_warning>';
		$buffer .= '<statistic_service_critical>'.$svc_stat["2"].'</statistic_service_critical>';
		$buffer .= '<statistic_service_unknown>'.$svc_stat["3"].'</statistic_service_unknown>';
		$buffer .= '<statistic_service_pending>'.$svc_stat["4"].'</statistic_service_pending>';
		$buffer .= '<statistic_host_up>'.$host_stat["0"].'</statistic_host_up>';
		$buffer .= '<statistic_host_down>'.$host_stat["1"].'</statistic_host_down>';
		$buffer .= '<statistic_host_unreachable>'.$host_stat["2"].'</statistic_host_unreachable>';
		$buffer .= '<statistic_host_pending>'.$host_stat["3"].'</statistic_host_pending>';
		$buffer .= '</stats>';
		$buffer .= '</reponse>';
		header('Content-Type: text/xml');
		echo $buffer;
	}
	if (!isset($lcaSTR))
		$lcaSTR = array();
	read($sid, $lcaSTR);
?>
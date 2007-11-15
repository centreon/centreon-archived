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

/*
	if($oreonPath == '@INSTALL_DIR_OREON@'){
		$buffer = null;
		$buffer .= '<reponse>';
		$buffer .= 'none';
		$buffer .= '</reponse>';
		header('Content-Type: text/xml');
		echo $buffer;
		exit(0);
	}
*/



	## pearDB init
	require_once 'DB.php';

	include_once($oreonPath . "etc/centreon.conf.php");
	include_once($oreonPath . "www/include/common/common-Func-ACL.php");

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


	# Session...
	$debug_session = 'KO';

	# sessionID check and refresh
	$sid = isset($_POST["sid"]) ? $_POST["sid"] : 0;
	$sid = isset($_GET["sid"]) ? $_GET["sid"] : $sid;


	function get_error($motif){
		$buffer = null;
		$buffer .= '<reponse>';
		$buffer .= $motif;
		$buffer .= '</reponse>';
		header('Content-Type: text/xml');
		echo $buffer;
		exit(0);
	}
	function check_injection($sid){
		if ( eregi("(<|>|;|UNION|ALL|OR|AND|ORDER|SELECT|WHERE)", $sid)) {
			get_error('sql injection detected');
			return 1;
		}
		return 0;
	}

	/* security check 2/2*/
	if(!check_injection($sid)){

		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if($res->fetchInto($session)){
			/* update session */
			$DBRESULT2 =& $pearDB->query("UPDATE `session` SET `last_reload` = '".time()."', `ip_address` = '".$_SERVER["REMOTE_ADDR"]."' WHERE CONVERT( `session_id` USING utf8 ) = '".$sid."' LIMIT 1");
		}else
			get_error('bad session id');
	}
	else
		get_error('need session identifiant !');
	/* security end 2/2 */



	function restore_session($statistic_service = 'null', $statistic_host = 'null'){
		global $pearDB;
		if(isset($statistic_service) && !is_null($statistic_service)){
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
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}


	function read($sid){

		global $pearDB, $flag,$oreonPath;
		$oreon = "";
		$search = "";
		$search_type_service = 0;
		$search_type_host = 0;

		## calcul stat for resume
		$statistic_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => 0);
		$statistic_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);


		$DBRESULT_OPT =& $pearDB->query("SELECT ndo_base_prefix,color_ok,color_warning,color_critical,color_unknown,color_pending,color_up,color_down,color_unreachable FROM general_opt");
		if (PEAR::isError($DBRESULT_OPT))
			print "DB Error : ".$DBRESULT_OPT->getDebugInfo()."<br>";
		$DBRESULT_OPT->fetchInto($general_opt);

		include_once($oreonPath . "www/DBndoConnect.php");

		/* Get HostNDO status */
		$rq1 = "SELECT count(nhs.current_state) as cnt, nhs.current_state" .
				" FROM ".$general_opt["ndo_base_prefix"]."_hoststatus nhs, ".$general_opt["ndo_base_prefix"]."_objects no" .
				" WHERE no.object_id = nhs.host_object_id AND no.is_active = 1 GROUP BY nhs.current_state ORDER by nhs.current_state";
		$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
		if (PEAR::isError($DBRESULT_NDO1))
			print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br>";
		$host_stat = array();
		$host_stat[0] = 0;
		$host_stat[1] = 0;
		$host_stat[2] = 0;
		$host_stat[3] = 0;
		while($DBRESULT_NDO1->fetchInto($ndo))
			$host_stat[$ndo["current_state"]] = $ndo["cnt"];
		/* end */

		/* Get ServiceNDO status */
		$rq2 = "SELECT count(nss.current_state) as cnt, nss.current_state" .
				" FROM ".$general_opt["ndo_base_prefix"]."_servicestatus nss, ".$general_opt["ndo_base_prefix"]."_objects no" .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 not like 'OSL_Module'".
				" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
	//			" AND no.instance_id = 1";

		$DBRESULT_NDO2 =& $pearDBndo->query($rq2);
		if (PEAR::isError($DBRESULT_NDO2))
			print "DB Error : ".$DBRESULT_NDO2->getDebugInfo()."<br>";

		$svc_stat = array();
		$svc_stat[0] = 0;
		$svc_stat[1] = 0;
		$svc_stat[2] = 0;
		$svc_stat[3] = 0;
		$svc_stat[4] = 0;
		while($DBRESULT_NDO2->fetchInto($ndo))
			$svc_stat[$ndo["current_state"]] = $ndo["cnt"];
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





	read($sid);
?>
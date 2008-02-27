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
	
	/* Connect to ods DB */	
	$dsn = array('phptype'  => 'mysql',
			     'username' => $conf_oreon['user'],
			     'password' => $conf_oreon['password'],
			     'hostspec' => $conf_oreon['host'],
			     'database' => $conf_oreon['ods'],);
	$options = array('debug'=> 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,);
	$pearDBO =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDBO)) die("Connecting problems with oreon database : " . $pearDBO->getMessage());
	$pearDBO->setFetchMode(DB_FETCHMODE_ASSOC);

	# Session...	
	$debug_session = 'KO';

	# sessionID check and refresh
	$sid = isset($_POST["sid"]) ? $_POST["sid"] : 0;
	$sid = isset($_GET["sid"]) ? $_GET["sid"] : $sid;
	$session_expire = isset($_POST["session_expire"]) ? $_POST["session_expire"] : 30;
	$session_expire = isset($_GET["session_expire"]) ? $_GET["session_expire"] : $session_expire;
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
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}

	if ($sid){
		$debug_session = $sid;
		$time = time();
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if(($res->fetchInto($session) && (($time - $session["last_reload"]) > $session_expire))  || isset($session["s_nbServicesOk"]) ){
			$flag_reset = 1;
		} else if($res->fetchInto($session) && isset($session["s_nbServicesOk"]) && $session["s_nbServicesOk"] && (($time - $session["last_reload"]) <= $session_expire)){
			$flag_reset = 0;
		} else {
			$flag_reset = 0;
			$session["s_nbServicesOk"]= 0;
			$session["s_nbServicesWarning"]= 0;
			$session["s_nbServicesCritical"]= 0;
			$session["s_nbServicesUnknown"]= 0;
			$session["s_nbServicesPending"]= 0;
			$session["s_nbHostsUp"]= 0;
			$session["s_nbHostsDown"]= 0;
			$session["s_nbHostsUnreachable"]= 0;
			$session["s_nbHostsPending"] = 0;
		}
	}

	function read($version,$sid,$file){
		global $pearDB, $flag;
		$_POST["sid"] = $sid;
		$_GET["sid"] = $sid;
		$oreon = "";
		$search = "";
		$search_type_service = 0;
		$search_type_host = 0;
		include("../load_status_log.php");
		## calcul stat for resume		
		$statistic_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => 0);
		$statistic_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);		
		if (isset($host_status))
			foreach ($host_status as $hs)
				$statistic_host[$hs["current_state"]]++;
		if (isset($service_status))
			foreach ($service_status as $s)
				$statistic_service[$s["current_state"]]++;
		restore_session($statistic_service, $statistic_host);
		$MyLog = date('l dS \of F Y h:i:s A'). "\n";
		$buffer = null;
		$buffer  = '<?xml version="1.0"?>';
		$buffer .= '<reponse>';
		$buffer .= '<infos>';
		$buffer .= '<filetime>'.filectime($file).'</filetime>';
		$buffer .= '</infos>';
		$buffer .= '<stats>';
		$buffer .= '<statistic_service_ok>'.$statistic_service["OK"].'</statistic_service_ok>';
		$buffer .= '<statistic_service_warning>'.$statistic_service["WARNING"].'</statistic_service_warning>';
		$buffer .= '<statistic_service_critical>'.$statistic_service["CRITICAL"].'</statistic_service_critical>';
		$buffer .= '<statistic_service_unknown>'.$statistic_service["UNKNOWN"].'</statistic_service_unknown>';
		$buffer .= '<statistic_service_pending>'.$statistic_service["PENDING"].'</statistic_service_pending>';
		$buffer .= '<statistic_host_up>'.$statistic_host["UP"].'</statistic_host_up>';
		$buffer .= '<statistic_host_down>'.$statistic_host["DOWN"].'</statistic_host_down>';
		$buffer .= '<statistic_host_unreachable>'.$statistic_host["UNREACHABLE"].'</statistic_host_unreachable>';
		$buffer .= '<statistic_host_pending>'.$statistic_host["PENDING"].'</statistic_host_pending>';
		$buffer .= '</stats>';
		$buffer .= '</reponse>';
		header('Content-Type: text/xml');
		echo $buffer;
	}
	
	if (!isset($session["last_reload"]) || !$session["last_reload"])
		$session["last_reload"] = time();
	if(!$flag_reset){
		$buffer = null;
		$buffer  = '<?xml version="1.0"?>';
		$buffer .= '<reponse>';
		$buffer .= '<infos>';
		$buffer .= '<expire>'.$session_expire.'</expire>';
		$buffer .= '<filetime>'.time().'</filetime>';
		$buffer .= '</infos>';
		$buffer .= '<stats>';
		$buffer .= '<statistic_service_ok>'.$session["s_nbServicesOk"].'</statistic_service_ok>';
		$buffer .= '<statistic_service_warning>'.$session["s_nbServicesWarning"].'</statistic_service_warning>';
		$buffer .= '<statistic_service_critical>'.$session["s_nbServicesCritical"].'</statistic_service_critical>';
		$buffer .= '<statistic_service_unknown>'.$session["s_nbServicesUnknown"].'</statistic_service_unknown>';
		$buffer .= '<statistic_service_pending>'.$session["s_nbServicesPending"].'</statistic_service_pending>';
		$buffer .= '<statistic_host_up>'.$session["s_nbHostsUp"].'</statistic_host_up>';
		$buffer .= '<statistic_host_down>'.$session["s_nbHostsDown"].'</statistic_host_down>';
		$buffer .= '<statistic_host_unreachable>'.$session["s_nbHostsUnreachable"].'</statistic_host_unreachable>';
		$buffer .= '<statistic_host_pending>'.$session["s_nbHostsPending"].'</statistic_host_pending>';
		$buffer .= '</stats>';
		$buffer .= '</reponse>';
		header('Content-Type: text/xml');
		echo $buffer;
	} else if (isset($_POST["version"]) && isset($_POST["sid"])&& isset($_POST["fileStatus"])){
		read($_POST["version"],$_POST["sid"],$_POST["fileStatus"]);
	} else if (isset($_GET["version"]) && isset($_GET["sid"])&& isset($_GET["fileStatus"])) {
		read($_GET["version"],$_GET["sid"],$_GET["fileStatus"]);
	} else {
		$buffer = null;
		$buffer .= '<reponse>';	
		$buffer .= 'none';	
		$buffer .= '</reponse>';	
		header('Content-Type: text/xml');
		echo $buffer;
		restore_session();
	}
?>
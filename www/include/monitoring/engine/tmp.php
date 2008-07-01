<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
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
	$oreonPath = '/srv/oreon/';


	function get_error($motif){
		$buffer = null;
		$buffer .= '<reponse>';	
		$buffer .= $motif;
		$buffer .= '</reponse>';
		header('Content-Type: text/xml');
		echo $buffer;
		exit(0);
	}

	function check_injection(){
		if ( eregi("(<|>|;|UNION|ALL|OR|AND|ORDER|SELECT|WHERE)", $_GET["sid"])) {
			get_error('sql injection detected');
			return 1;
		}
		return 0;
	}

	/* security check 1/2*/
	if($oreonPath == '@INSTALL_DIR_OREON@')
		get_error('please set your oreonPath');
	/* security end 1/2 */

	include_once($oreonPath . "etc/centreon.conf.php");
	include_once($oreonPath . "www/DBconnect.php");

	/* security check 2/2*/
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){

		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if ($res->fetchInto($session)){
			;
		}else
			get_error('bad session id');
	} else
		get_error('need session identifiant !');
	/* security end 2/2 */




	/* requisit */
	if(isset($_GET["num"]) && !check_injection($_GET["num"])){
		$num = htmlentities($_GET["num"]);
	}else
		get_error('num unknown');
	if(isset($_GET["limit"]) && !check_injection($_GET["limit"])){
		$limit = htmlentities($_GET["limit"]);
	}else
		get_error('limit unknown');



	/* options */
	if(isset($_GET["search"]) && !check_injection($_GET["search"])){
		$search = htmlentities($_GET["search"]);
	}else
		$search = "";
	if(isset($_GET["host_name"]) && !check_injection($_GET["host_name"])){
		$host_name = htmlentities($_GET["host_name"]);
	}else
		$host_name = "";
	if(isset($_GET["search_type_host"]) && !check_injection($_GET["search_type_host"])){
		$search_type_host = htmlentities($_GET["search_type_host"]);
	}else
		$search_type_host = 1;
	if(isset($_GET["search_type_service"]) && !check_injection($_GET["search_type_service"])){
		$search_type_service = htmlentities($_GET["search_type_service"]);
	}else
		$search_type_service = 1;
	if(isset($_GET["sort_type"]) && !check_injection($_GET["sort_type"])){
		$sort_type = htmlentities($_GET["sort_type"]);
	}else
		$sort_type = "host_name";

	if(isset($_GET["order"]) && !check_injection($_GET["order"])){
		$order = htmlentities($_GET["order"]);
	}else
		$oreder = "ASC";

	if(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])){
		$date_time_format_status = htmlentities($_GET["date_time_format_status"]);
	}else
		$date_time_format_status = "d/m/Y H:i:s";

	if(isset($_GET["o"]) && !check_injection($_GET["o"])){
		$o = htmlentities($_GET["o"]);
	}else
		$o = "svc";
	if(isset($_GET["p"]) && !check_injection($_GET["p"])){
		$p = htmlentities($_GET["p"]);
	}else
		$p = "2";



	/* security end*/

	# class init
	class Duration
	{
		function toString ($duration, $periods = null)
	    {
	        if (!is_array($duration)) {
	            $duration = Duration::int2array($duration, $periods);
	        }
	        return Duration::array2string($duration);
	    }
	    function int2array ($seconds, $periods = null)
	    {
	        // Define time periods
	        if (!is_array($periods)) {
	            $periods = array (
	                    'y'	=> 31556926,
	                    'M' => 2629743,
	                    'w' => 604800,
	                    'd' => 86400,
	                    'h' => 3600,
	                    'm' => 60,
	                    's' => 1
	                    );
	        }
	        // Loop
	        $seconds = (int) $seconds;
	        foreach ($periods as $period => $value) {
	            $count = floor($seconds / $value);
	            if ($count == 0) {
	                continue;
	            }
	            $values[$period] = $count;
	            $seconds = $seconds % $value;
	        }
	        // Return
	        if (empty($values)) {
	            $values = null;
	        }
	        return $values;
	    }

	    function array2string ($duration)
	    {
	        if (!is_array($duration)) {
	            return false;
	        }
	        foreach ($duration as $key => $value) {
	            $segment = $value . '' . $key;
	            $array[] = $segment;
	        }
	        $str = implode(' ', $array);
	        return $str;
	    }
	}


	include_once($oreonPath . "www/DBNDOConnect.php");
	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();

	$DBRESULT_OPT =& $pearDB->query("SELECT color_ok,color_warning,color_critical,color_unknown,color_pending,color_up,color_down,color_unreachable FROM general_opt");
	if (PEAR::isError($DBRESULT_OPT))
		print "DB Error : ".$DBRESULT_OPT->getDebugInfo()."<br />";	
	$DBRESULT_OPT->fetchInto($general_opt);
	
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
	$rq1 = "SELECT count(nhs.current_state) as cnt, nhs.current_state" .
			" FROM ".$ndo_base_prefix."_hoststatus nhs, ".$ndo_base_prefix."_objects no" .
			" WHERE no.object_id = nhs.host_object_id GROUP BY nhs.current_state ORDER by nhs.current_state";
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";	
	$host_stat = array();
	$host_stat[0] = 0;
	$host_stat[1] = 0;
	$host_stat[2] = 0;
	$host_stat[3] = 0;
	while($DBRESULT_NDO1->fetchInto($ndo))
		$host_stat[$ndo["current_state"]] = $ndo["cnt"];
	/* end */

	/* Get Service status */
	$rq2 = "SELECT count(nss.current_state) as cnt, nss.current_state" .
			" FROM ".$ndo_base_prefix."_servicestatus nss, ".$ndo_base_prefix."_objects no" .
			" WHERE no.object_id = nss.service_object_id".
			" AND no.name1 not like 'OSL_Module'".
			" AND no.is_active = 0 GROUP BY nss.current_state ORDER by nss.current_state";
//			" AND no.instance_id = 1";



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
		$svc_stat[$ndo["current_state"]] = $ndo["cnt"];

	/* end */	
		$buffer .= '<reponse>';
		$buffer .= '<infos>';
		$buffer .= '<filetime>'.filectime($file).'</filetime>';
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

	//header('Content-Type: text/xml');
	echo $buffer;
?>
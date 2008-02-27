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

	# if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
	$debugXML = 0;
	$buffer = '';
	$oreonPath = '/srv/oreon/';
	$ndo_base_prefix = "nagios";

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
	include_once($oreonPath . "www/DBndoConnect.php");
	include_once($oreonPath . "www/include/common/common-Func-ACL.php");



	/* security check 2/2*/
	if(isset($_GET["sid"]) && !check_injection($_GET["sid"])){

		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if($res->fetchInto($session)){
			;
		}else
			get_error('bad session id');
	}
	else
		get_error('need session identifiant !');
	/* security end 2/2 */

	if(isset($_GET["host_id"]) && !check_injection($_GET["host_id"])){
		$host_id = htmlentities($_GET["host_id"]);
	}else
		$host_id = "0";

	if(isset($_GET["enable"]) && !check_injection($_GET["enable"])){
		$enable = htmlentities($_GET["enable"]);
	}else
		$enable = "enable";
	if(isset($_GET["disable"]) && !check_injection($_GET["disable"])){
		$disable = htmlentities($_GET["disable"]);
	}else
		$disable = "disable";
	if(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])){
		$date_time_format_status = htmlentities($_GET["date_time_format_status"]);
	}else
		$date_time_format_status = "d/m/Y H:i:s";

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


	function get_centreon_date($date){
		global $date_time_format_status;
		if ($date > 0)
			return date($date_time_format_status,$date);
		else
			return "N/A";
	}


	/* LCA */
	// check is admin
	$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$sid."'");
	$res1->fetchInto($user);
	$user_id = $user["user_id"];
	$res2 =& $pearDB->query("SELECT contact_admin,contact_lang FROM contact WHERE contact_id = '".$user_id."'");
	$res2->fetchInto($admin);
	$is_admin = 0;
	$is_admin = $admin["contact_admin"];

	$lang_ext = $admin["contact_lang"];
	include_once("../lang/$lang_ext.php");


	// if is admin -> lca
	if(!$is_admin){
		$_POST["sid"] = $sid;
		$lca =  getLCAHostByName($pearDB);
		$lcaSTR = getLCAHostStr($lca["LcaHost"]);
	}


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
	$tab_color_host[0] = $general_opt["color_up"];
	$tab_color_host[1] = $general_opt["color_down"];
	$tab_color_host[2] = $general_opt["color_unreachable"];

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");

	$state_type = array("1" => "HARD","0" => "SOFT");

	/* Get Host status */
	$rq1 = "SELECT nhs.current_state," .
			" nh.address," .
			" no.name1 as host_name," .
			" nhs.perfdata," .
			" nhs.current_check_attempt," .
			" nhs.state_type," .
			" unix_timestamp(nhs.last_check) as last_check," .
			" unix_timestamp(nhs.next_check) as next_check," .
			" nhs.latency," .
			" nhs.execution_time," .
			" unix_timestamp(nhs.last_state_change) as last_state_change," .
			" unix_timestamp(nhs.last_notification) as last_notification," .
			" unix_timestamp(nhs.next_notification) as next_notification," .
			" unix_timestamp(nhs.last_hard_state_change) as last_hard_state_change," .
			" nhs.last_hard_state," .
			" unix_timestamp(nhs.last_time_up) as last_time_up," .
			" unix_timestamp(nhs.last_time_down) as last_time_down," .
			" unix_timestamp(nhs.last_time_unreachable) as last_time_unreachable," .
			" nhs.current_notification_number," .
//			" nhs.is_flapping," .
			" nhs.scheduled_downtime_depth," .
			" nhs.output," .
			" ROUND(nhs.percent_state_change) as percent_state_change," .
//			" nh.flap_detection_enabled," .
//			" nh.passive_checks_enabled," .
//			" nh.active_checks_enabled," .
//			" nh.obsess_over_host," .
			" nh.notifications_enabled," .
			" nh.event_handler_enabled," .
			" nh.icon_image_alt" .
			" FROM ".$ndo_base_prefix."_hoststatus nhs, ".$ndo_base_prefix."_objects no, ".$ndo_base_prefix."_hosts nh" .
			" WHERE no.object_id = " . $host_id .
			" AND no.object_id = nhs.host_object_id and nh.host_object_id = no.object_id " .
			" AND no.name1 not like 'OSL_Module'".
			" AND no.is_active = 1 AND no.objecttype_id = 1 AND nh.config_type = 1";
		if(!$is_admin)
			$rq1 .= " AND no.name1 IN (".$lcaSTR." )";
	$buffer .= '<reponse>';

	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	$class = "list_one";
	$ct = 0;
	$flag = 0;
	$c = array("1" => "#00ff00", "0" => "#ff0000");
	$en = array("0" => $lang["no"], "1" => $lang["yes"]);

	
	if($DBRESULT_NDO1->fetchInto($ndo))
	{
		$duration = "";
		if($ndo["last_state_change"] > 0)
			$duration = Duration::toString(time() - $ndo["last_state_change"]);

		$last_notification = "N/A";
		if($ndo["last_notification"] > 0)
			$last_notification = $ndo["last_notification"];
			
		$next_notification = "N/A";
		if($ndo["next_notification"] > 0)
			$next_notification = $ndo["next_notification"];
			
		$buffer .= '<hostname><![CDATA['. $ndo["host_name"]  . ']]></hostname>';
		$buffer .= '<address><![CDATA['. $ndo["address"]  . ']]></address>';

		$buffer .= '<current_state color="'.$tab_color_host[$ndo["current_state"]].'">'. $tab_status_host[$ndo["current_state"]]  . '</current_state>';
		$buffer .= '<current_state_name><![CDATA['. html_entity_decode($lang["m_mon_host_status"]).']]> </current_state_name>';
		$buffer .= '<plugin_output name="'.$lang["m_mon_host_status_info"].'"><![CDATA['. $ndo["output"]  . ']]></plugin_output>';

		$buffer .= '<performance_data>'. $ndo["perfdata"]  . '</performance_data>';
		$buffer .= '<performance_data_name><![CDATA['.html_entity_decode($lang["m_mon_performance_data"]).']]></performance_data_name>';

		$buffer .= '<current_attempt name="'.$lang["m_mon_current_attempt"].'">'. $ndo["current_check_attempt"]  . '</current_attempt>';
		
		$buffer .= '<state_type>'.$state_type[$ndo["state_type"]].'</state_type>';
		$buffer .= '<state_type_name><![CDATA['.html_entity_decode($lang["m_mon_state_type"]).']]> </state_type_name>';

		$buffer .= '<last_check >'. get_centreon_date($ndo["last_check"])  . '</last_check>';
		$buffer .= '<last_check_name><![CDATA['.html_entity_decode($lang["m_mon_host_last_check"]).']]></last_check_name>';

		$buffer .= '<next_check >'. get_centreon_date($ndo["next_check"])  . '</next_check>';
		$buffer .= '<next_check_name><![CDATA['.html_entity_decode($lang["m_mon_next_check"]).']]></next_check_name>';

		$buffer .= '<check_latency>'. $ndo["latency"]  . '</check_latency>';
		$buffer .= '<check_latency_name><![CDATA['.html_entity_decode($lang["m_mon_check_latency"]).']]></check_latency_name>';

		$buffer .= '<check_execution_time>'. $ndo["execution_time"]  . '</check_execution_time>';
		$buffer .= '<check_execution_time_name><![CDATA['.html_entity_decode($lang["m_mon_check_execution_time"]).']]></check_execution_time_name>';
		
		$buffer .= '<last_state_change>'. get_centreon_date($ndo["last_state_change"])  . '</last_state_change>';
		$buffer .= '<last_state_change_name><![CDATA['.$lang["m_mon_last_change"].']]></last_state_change_name>';

		$buffer .= '<duration>'. $duration  . '</duration>';
		$buffer .= '<duration_name><![CDATA['.html_entity_decode($lang["m_mon_current_state_duration"]).']]></duration_name>';
		
		$buffer .= '<last_notification>'.  get_centreon_date($last_notification)  . '</last_notification>';
		$buffer .= '<last_notification_name><![CDATA['.html_entity_decode($lang["m_mon_last_notification"]).']]></last_notification_name>';


		$buffer .= '<next_notification>'.  get_centreon_date($next_notification)  . '</next_notification>';
		$buffer .= '<next_notification_name><![CDATA['.html_entity_decode($lang["m_mon_next_notification"]).']]></next_notification_name>';


		$buffer .= '<current_notification_number>'. $ndo["current_notification_number"]  . '</current_notification_number>';
		$buffer .= '<current_notification_number_name><![CDATA['.$lang["m_mon_notification_nb"].']]></current_notification_number_name>';

/*
		$buffer .= '<is_flapping>'. $ndo["is_flapping"]  . '</is_flapping>';
		$buffer .= '<is_flapping_name><![CDATA['.$lang["m_mon_host_flapping"].']]></is_flapping_name>';
*/

		$buffer .= '<percent_state_change>'. $ndo["percent_state_change"]  . '</percent_state_change>';
		$buffer .= '<percent_state_change_name><![CDATA['.$lang["m_mon_percent_state_change"].']]></percent_state_change_name>';


		$buffer .= '<is_downtime>'. $en[$ndo["scheduled_downtime_depth"]]  . '</is_downtime>';
		$buffer .= '<is_downtime_name><![CDATA['.$lang["m_mon_downtime_sc"].']]></is_downtime_name>';


		$buffer .= '<last_update>'. get_centreon_date( time())  . '</last_update>';
		$buffer .= '<last_update_name><![CDATA['.$lang["m_mon_last_update"].']]></last_update_name>';


		$buffer .= '<last_time_up name="'.html_entity_decode($lang['pop_last_time_up']).'">'. get_centreon_date( $ndo["last_time_up"])  . '</last_time_up>';
		$buffer .= '<last_time_down name="'.html_entity_decode($lang['pop_last_time_down']).'">'. get_centreon_date( $ndo["last_time_down"])  . '</last_time_down>';
		$buffer .= '<last_time_unreachable name="'.html_entity_decode($lang['pop_last_time_unreachable']).'">'. get_centreon_date( $ndo["last_time_unreachable"])  . '</last_time_unreachable>';

		$ct++;
	}
	/* end */

	if(!$ct){
		$buffer .= '<infos>';
		$buffer .= 'none';
		$buffer .= '</infos>';
	}

	$buffer .= '</reponse>';

header('Content-type: text/xml; charset=iso-8859-1');
header('Cache-Control: no-cache, must-revalidate');

echo '<'.'?xml version="1.0" ?'.">\n";

	echo $buffer;






?>

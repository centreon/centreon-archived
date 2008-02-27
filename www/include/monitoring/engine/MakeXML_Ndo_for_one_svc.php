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

	/* security check 1/2*/
	if($oreonPath == '@INSTALL_DIR_OREON@')
		get_error('please set your oreonPath');
	/* security end 1/2 */

	include_once($oreonPath . "etc/centreon.conf.php");
	include_once($oreonPath . "www/DBconnect.php");
	include_once($oreonPath . "www/DBndoConnect.php");
	include_once($oreonPath . "www/include/common/common-Func-ACL.php");
	include_once($oreonPath . "www/include/common/common-Func.php");

	$ndo_base_prefix = getNDOPrefix();

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

	if(isset($_GET["svc_id"]) && !check_injection($_GET["svc_id"])){
		$svc_id = htmlentities($_GET["svc_id"]);
	}else
		$svc_id = "0";

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
//	$DBRESULT_OPT =& $pearDB->query("SELECT color_ok,color_warning,color_critical,color_unknown,color_pending,color_up,color_down,color_unreachable FROM general_opt");
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
	$rq1 = "SELECT nss.current_state," .
			" no.name1 as hostname," .
			" no.name2 as service_description," .
			" unix_timestamp(nss.last_check) as last_check," .
			" unix_timestamp(nss.next_check) as next_check," .
			" unix_timestamp(nss.last_state_change) as last_state_change," .
			" unix_timestamp(nss.last_notification) as last_notification," .
			" unix_timestamp(nss.next_notification) as next_notification," .
			" unix_timestamp(nss.last_hard_state_change) as last_hard_state_change," .
			" nss.last_hard_state," .
			" nss.latency," .
			" unix_timestamp(nss.last_time_ok) as last_time_ok," .
			" unix_timestamp(nss.last_time_critical) as last_time_critical," .
			" unix_timestamp(nss.last_time_unknown) as last_time_unknown," .
			" unix_timestamp(nss.last_time_warning) as last_time_warning," .
			" nss.current_notification_number," .
			" nss.scheduled_downtime_depth," .
			" nss.output," .
			" ROUND(nss.percent_state_change) as percent_state_change," .
			" nss.notifications_enabled," .
			" nss.perfdata," .
			" nss.state_type," .
			" nss.execution_time," .
			" nss.event_handler_enabled" .

			" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
			" WHERE no.object_id = " . $svc_id .
			" AND no.object_id = nss.service_object_id " .
			" AND no.name1 not like 'OSL_Module'".
			" AND no.is_active = 1 AND no.objecttype_id = 2";
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
	$en = array("0" => _("No"), "1" => _("Yes"));

	
	if($DBRESULT_NDO1->fetchInto($ndo))
	{
		$buffer .= '<svc_name><![CDATA['. $ndo["service_description"]  . ']]></svc_name>';


		$duration = "";
		if($ndo["last_state_change"] > 0)
			$duration = Duration::toString(time() - $ndo["last_state_change"]);

		$last_notification = "N/A";
		if($ndo["last_notification"] > 0)
			$last_notification = $ndo["last_notification"];
			
		$next_notification = "N/A";
		if($ndo["next_notification"] > 0)
			$next_notification = $ndo["next_notification"];


		$buffer .= '<service_description><![CDATA['.$ndo["service_description"].']]></service_description>';
		$buffer .= '<hostname><![CDATA['.$ndo["hostname"].']]></hostname>';

		$buffer .= '<current_state color="'.$tab_color_service[$ndo["current_state"]].'">'. $tab_status_svc[$ndo["current_state"]]  . '</current_state>';
		$buffer .= '<current_state_name><![CDATA['. html_entity_decode(_("Host Status")).']]> </current_state_name>';
		$buffer .= '<plugin_output name="'._("Status Information").'"><![CDATA['. $ndo["output"]  . ']]></plugin_output>';


		$buffer .= '<performance_data>'. $ndo["perfdata"]  . '</performance_data>';
		$buffer .= '<performance_data_name><![CDATA['.html_entity_decode(_("Performance Data")).']]></performance_data_name>';

//		$buffer .= '<current_attempt name="'.$lang["m_mon_current_attempt"].'">'. $ndo["current_check_attempt"]  . '</current_attempt>';
		
		$buffer .= '<state_type>'.$state_type[$ndo["state_type"]].'</state_type>';
		$buffer .= '<state_type_name><![CDATA['.html_entity_decode(_("State Type")).']]> </state_type_name>';

		$buffer .= '<last_check >'. get_centreon_date($ndo["last_check"])  . '</last_check>';
		$buffer .= '<last_check_name><![CDATA['.html_entity_decode(_("Last Check")).']]></last_check_name>';

		$buffer .= '<next_check >'. get_centreon_date($ndo["next_check"])  . '</next_check>';
		$buffer .= '<next_check_name><![CDATA['.html_entity_decode(_("Next Check")).']]></next_check_name>';


		$buffer .= '<check_latency>'. $ndo["latency"]  . '</check_latency>';
		$buffer .= '<check_latency_name><![CDATA['.html_entity_decode(_("Latency")).']]></check_latency_name>';

		$buffer .= '<check_execution_time>'. $ndo["execution_time"]  . '</check_execution_time>';
		$buffer .= '<check_execution_time_name><![CDATA['.html_entity_decode(_("Execution Time")).']]></check_execution_time_name>';
		
		$buffer .= '<last_state_change>'. get_centreon_date($ndo["last_state_change"])  . '</last_state_change>';
		$buffer .= '<last_state_change_name><![CDATA['._("Last State Change").']]></last_state_change_name>';

		$buffer .= '<duration>'. $duration  . '</duration>';
		$buffer .= '<duration_name><![CDATA['.html_entity_decode(_("Current State Duration")).']]></duration_name>';
		
		$buffer .= '<last_notification>'.  get_centreon_date($last_notification)  . '</last_notification>';
		$buffer .= '<last_notification_name><![CDATA['.html_entity_decode(_("Last Notification")).']]></last_notification_name>';


		$buffer .= '<next_notification>'.  get_centreon_date($next_notification)  . '</next_notification>';
		$buffer .= '<next_notification_name><![CDATA['.html_entity_decode(_("Next Notification")).']]></next_notification_name>';


		$buffer .= '<current_notification_number>'. $ndo["current_notification_number"]  . '</current_notification_number>';
		$buffer .= '<current_notification_number_name><![CDATA['._("Current Notification Number").']]></current_notification_number_name>';


		$buffer .= '<percent_state_change>'. $ndo["percent_state_change"]  . '</percent_state_change>';
		$buffer .= '<percent_state_change_name><![CDATA['._("Percent State Change").']]></percent_state_change_name>';


		$buffer .= '<is_downtime>'. $en[$ndo["scheduled_downtime_depth"]]  . '</is_downtime>';
		$buffer .= '<is_downtime_name><![CDATA['._("In Scheduled Downtime?").']]></is_downtime_name>';


		$buffer .= '<last_update>'. get_centreon_date( time())  . '</last_update>';
		$buffer .= '<last_update_name><![CDATA['._("Last Update").']]></last_update_name>';

		$buffer .= '<last_time_ok name="'.html_entity_decode(_("Last ok time")).'">'. get_centreon_date( $ndo["last_time_ok"])  . '</last_time_ok>';
		$buffer .= '<last_time_warning name="'.html_entity_decode(_("Last warning time")).'">'. get_centreon_date( $ndo["last_time_warning"])  . '</last_time_warning>';
		$buffer .= '<last_time_unknown name="'.html_entity_decode(_("Last unknown time")).'">'. get_centreon_date( $ndo["last_time_unknown"])  . '</last_time_unknown>';
		$buffer .= '<last_time_critical name="'.html_entity_decode(_("Last critical time")).'">'. get_centreon_date( $ndo["last_time_critical"])  . '</last_time_critical>';

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

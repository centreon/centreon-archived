<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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

	if (!isset($oreon))
		exit();

	$ndo_base_prefix = "nagios";

	if (isset($_GET["host_name"]) && $_GET["host_name"] && isset($_GET["service_description"]) && $_GET["service_description"]){
		$host_name = $_GET["host_name"];
		$svc_description = $_GET["service_description"];
	} else {
		foreach ($_GET["select"] as $key => $value)
			$host_service_key = $key;
		$tab_data = split(";", $host_service_key);
		$host_name = $tab_data[0];
		$svc_description = $tab_data[1];
	}

	$tab_status = array();

	if (isset($ndo) && $ndo){
		include_once("./DBndoConnect.php");

		/* start ndo service info */
		$rq =	"SELECT " .
				" nss.current_state," .
				" nss.output as plugin_output," .
				" nss.current_check_attempt as current_attempt," .
				" nss.status_update_time as status_update_time," .
				" unix_timestamp(nss.last_state_change) as last_state_change," .
				" unix_timestamp(nss.last_check) as last_check," .
				" nss.notifications_enabled," .
				" unix_timestamp(nss.next_check) as next_check," .
				" nss.problem_has_been_acknowledged," .
				" nss.passive_checks_enabled," .
				" nss.active_checks_enabled," .
				" nss.event_handler_enabled," .
				" nss.perfdata as performance_data," .
				" nss.is_flapping," .
				" nss.scheduled_downtime_depth," .
				" nss.percent_state_change," .
				" nss.current_notification_number," .
				" nss.obsess_over_service," .
				" nss.check_type," .
				" nss.state_type," .
				" nss.latency as check_latency," .
				" nss.execution_time as check_execution_time," .
				" nss.flap_detection_enabled," .
				" unix_timestamp(nss.last_notification) as last_notification," .
				" no.name1 as host_name," .
				" no.name2 as service_description" .
				" FROM ".$ndo_base_prefix."_servicestatus nss, ".$ndo_base_prefix."_objects no" .
				" WHERE no.object_id = nss.service_object_id AND no.name1 like '".$host_name."' ";

		$DBRESULT_NDO =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT_NDO))
			print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br />";

		$tab_status_service = array();
		$tab_status_service[0] = "OK";
		$tab_status_service[1] = "WARNING";
		$tab_status_service[2] = "CRITICAL";
		$tab_status_service[3] = "UNKNOWN";
		$tab_status_service[4] = "PENDING";

		while($DBRESULT_NDO->fetchInto($ndo))
		{
			if($ndo["service_description"] == $svc_description)
				$service_status[$host_name."_".$svc_description]= $ndo;

			if (!isset($tab_status[$ndo["current_state"]]))
				$tab_status[$tab_status_service[$ndo["current_state"]]] = 0;
			$tab_status[$tab_status_service[$ndo["current_state"]]]++;
		}

		$service_status[$host_name."_".$svc_description]["current_state"] = $tab_status_service[$service_status[$host_name."_".$svc_description]["current_state"]];
		/* end ndo service info */

		/* start ndo host detail */
		$tab_host_status[0] = "UP";
		$tab_host_status[1] = "DOWN";
		$tab_host_status[2] = "UNREACHABLE";

		$rq2 =	"SELECT nhs.current_state" .
				" FROM ".$ndo_base_prefix."_hoststatus nhs, ".$ndo_base_prefix."_objects no" .
				" WHERE no.object_id = nhs.host_object_id AND no.name1 like '".$host_name."'";
		$DBRESULT_NDO =& $pearDBndo->query($rq2);
		if (PEAR::isError($DBRESULT_NDO))
			print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br />";
		$DBRESULT_NDO->fetchInto($ndo2);
		$host_status[$host_name] = $tab_host_status[$ndo2["current_state"]];
		/* end ndo host detail */
	}

	if (!isset($_GET["service_description"]))
		$_GET["service_description"] = $svc_description;

	$lcaHost = getLcaHostByName($pearDB);

	isset($lcaHost["LcaHost"][$host_name]) || $oreon->user->admin || !$isRestreint ? $key = true : $key = NULL;
	if ($key == NULL){
		include_once("alt_error.php");
	} else {
		$res =& $pearDB->query("SELECT * FROM host WHERE host_name = '".$host_name."'");
		if (PEAR::isError($res))
			print "Mysql Error : ".$res->getMessage();
		$res->fetchInto($host);
		$host_id = getMyHostID($host["host_name"]);
		$service_id = getMyServiceID($_GET["service_description"], $host_id);
		$total_current_attempts = getMyServiceField($service_id, "service_max_check_attempts");

		$path = "./include/monitoring/objectDetails/";

		# Smarty template Init
		$tpl = new Smarty();
		$tpl = initSmartyTpl($path, $tpl, "./");

		if (!file_exists($oreon->Nagioscfg["comment_file"]))
			print ("downtime file not found");
		else	{
			$tab_comments_svc = array();
			$i = 0;
			$log = fopen($oreon->Nagioscfg["comment_file"], "r");
			if ($oreon->user->get_version() == 1){
				while ($str = fgets($log))	{
					print $str . "<br />";
					$res = preg_split("/;/", $str);
					if (preg_match("/^\[([0-9]*)\] SERVICE_COMMENT;/", $str, $matches)){
						if (!strcmp($res[2], $host_name)){
							print $res[6];
							$tab_comments_svc[$i] = array();
							$tab_comments_svc[$i]["id"] = $res[1];
							$tab_comments_svc[$i]["host_name"] = $res[2];
							$tab_comments_svc[$i]["service_descr"] = $res[3];
							$tab_comments_svc[$i]["time"] = date("d-m-Y G:i:s", $matches[1]);
							$tab_comments_svc[$i]["author"] = $res[5];
							$tab_comments_svc[$i]["comment"] = $res[6];
							$tab_comments_svc[$i]["persistent"] = $res[4];
						}
					}
					$i++;
				}
			} else {
				while ($str = fgets($log))	{
                if (preg_match("/^hostcomment/", $str)){
                	$tab_comments_host[$i] = array();
                    $flag_host = 1;
                } else if (preg_match("/^servicecomment /", $str)){
                	$tab_comments_svc[$i] = array();
                    $flag_svc = 1;
                } else {
                    if(isset($flag_svc) && $flag_svc == 1) {
                      	$res = preg_split("/=/", $str);
                      	$res[0] = trim($res[0]);
                      	if (isset($res[1]))
                      		$res[1] = trim($res[1]);
                        if (preg_match('`comment_id$`', $res[0]))
                            $tab_comments_svc[$i]["id"] = $res[1];
                        if (preg_match('`service_description$`', $res[0])){
                          $tab_comments_svc[$i]["service_description"] = $res[1];}
                        if (preg_match('`host_name$`', $res[0]))
                          $tab_comments_svc[$i]["host_name"] = $res[1];
                        if (preg_match('`entry_time$`', $res[0]))
                        	$tab_comments_svc[$i]["time"] = date("d-m-Y G:i:s", $res[1]);
                        if (preg_match('`author$`', $res[0]))
                        	$tab_comments_svc[$i]["author"] = $res[1];
                        if (preg_match('`comment_data$`', $res[0]))
                        	$tab_comments_svc[$i]["comment"] = $res[1];
                        if (preg_match('`persistent$`', $res[0]))
                        	$tab_comments_svc[$i]["persistent"] = $res[1];
                        if (preg_match('`}$`', $str)){
                            $flag_svc = 0;
                        	$i++;
                        }
                    }
                }
			}

			}
		}

		foreach ($tab_comments_svc as $key => $value){
			if ( ($value["host_name"] == $_GET["host_name"]) && ($value["service_description"] == $_GET["service_description"]))
				;
			else
				unset($tab_comments_svc[$key]);
		}

		$en = array("0" => _("No"), "1" => _("Yes"));

		$en_acknowledge_text = array("1" => _("Delete this Acknowledgement"), "0" => _("Acknowledge this service"));
		$en_acknowledge = array("1" => "0", "0" => "1");

		$en_disable = array("1" => _("Enabled"), "0" => _("Disabled"));
		$en_inv = array("1" => "0", "0" => "1");
		$en_inv_text = array("1" => _("Disable"), "0" => _("Enable"));
		$color_onoff = array("1" => "#00ff00", "0" => "#ff0000");
		$color_onoff_inv = array("0" => "#00ff00", "1" => "#ff0000");
		$img_en = array("0" => "<img src='./img/icones/16x16/element_next.gif' border='0'>", "1" => "<img src='./img/icones/16x16/element_previous.gif' border='0'>");

		/*
		 * Ajust data for beeing displayed in template
		 */

		 $service_status[$host_name."_".$svc_description]["status_color"] = $oreon->optGen["color_".strtolower($service_status[$host_name."_".$svc_description]["current_state"])];
		 $service_status[$host_name."_".$svc_description]["last_check"] = date(_("Y/m/d - H:i:s"), $service_status[$host_name."_".$svc_description]["last_check"]);
		 $service_status[$host_name."_".$svc_description]["next_check"] = date(_("Y/m/d - H:i:s"), $service_status[$host_name."_".$svc_description]["next_check"]);
		!$service_status[$host_name."_".$svc_description]["check_latency"] ? $service_status[$host_name."_".$svc_description]["check_latency"] = "< 1 second" : $service_status[$host_name."_".$svc_description]["check_latency"] = $service_status[$host_name."_".$svc_description]["check_latency"] . " seconds";
		!$service_status[$host_name."_".$svc_description]["check_execution_time"] ? $service_status[$host_name."_".$svc_description]["check_execution_time"] = "< 1 second" : $service_status[$host_name."_".$svc_description]["check_execution_time"] = $service_status[$host_name."_".$svc_description]["check_execution_time"] . " seconds";
		!$service_status[$host_name."_".$svc_description]["last_notification"] ? $service_status[$host_name."_".$svc_description]["notification"] = "": $service_status[$host_name."_".$svc_description]["last_notification"] = date(_("Y/m/d - H:i:s"), $service_status[$host_name."_".$svc_description]["last_notification"]);
		!$service_status[$host_name."_".$svc_description]["last_state_change"] ? $service_status[$host_name."_".$svc_description]["duration"] = Duration::toString($service_status[$host_name."_".$svc_description]["last_time_".strtolower($service_status[$host_name."_".$svc_description]["current_state"])]) : $service_status[$host_name."_".$svc_description]["duration"] = Duration::toString(time() - $service_status[$host_name."_".$svc_description]["last_state_change"]);
		!$service_status[$host_name."_".$svc_description]["last_state_change"] ? $service_status[$host_name."_".$svc_description]["last_state_change"] = "": $service_status[$host_name."_".$svc_description]["last_state_change"] = date(_("Y/m/d - H:i:s"),$service_status[$host_name."_".$svc_description]["last_state_change"]);
		 $service_status[$host_name."_".$svc_description]["last_update"] = date(_("Y/m/d - H:i:s"), time());
		!$service_status[$host_name."_".$svc_description]["is_flapping"] ? $service_status[$host_name."_".$svc_description]["is_flapping"] = $en[$service_status[$host_name."_".$svc_description]["is_flapping"]] : $service_status[$host_name."_".$svc_description]["is_flapping"] = date(_("Y/m/d - H:i:s"), $service_status[$host_name."_".$svc_description]["is_flapping"]);

//		$tab_status = array();
		if (isset($ndo) && $ndo)
			foreach ($tab_host_service[$host_name] as $key_name => $s){
				if (!isset($tab_status[$service_status[$host_name."_".$key_name]["current_state"]]))
					$tab_status[$service_status[$host_name."_".$key_name]["current_state"]] = 0;
				$tab_status[$service_status[$host_name."_".$key_name]["current_state"]]++;
			}
		$status = NULL;
		foreach ($tab_status as $key => $value)
			$status .= "&value[".$key."]=".$value;

		$optionsURL = "session_id=".session_id()."&host_name=".$_GET["host_name"]."&service_description=".$_GET["service_description"];

		//$tpl->assign("lang", $lang);
		$tpl->assign("m_mon_services", _("Services"));
		$tpl->assign("m_mon_on_host", _("on host"));
		$tpl->assign("m_mon_services_status", _("Services Status"));
		$tpl->assign("m_mon_host_status_info", _("Status Information"));
		$tpl->assign("m_mon_performance_data", _("Performance Data"));
		$tpl->assign("m_mon_services_attempt", _("Current Attempt"));
		$tpl->assign("m_mon_services_state", _("State Type"));
		$tpl->assign("m_mon_last_check_type", _("Last Check Type"));
		$tpl->assign("m_mon_host_last_check", _("Last Check"));
		$tpl->assign("m_mon_services_active_check", _("Next Scheduled Active Check"));
		$tpl->assign("m_mon_services_latency", _("Latency"));
		$tpl->assign("m_mon_services_duration", _("Check Duration"));
		$tpl->assign("m_mon_last_change", _("Last State Change"));
		$tpl->assign("m_mon_current_state_duration", _("Current State Duration"));
		$tpl->assign("m_mon_last_notification_serv", _("Last Service Notification"));
		$tpl->assign("m_mon_notification_nb", _("Current Notification Number"));
		$tpl->assign("m_mon_services_flapping", _("Is This Service Flapping?"));
		$tpl->assign("m_mon_percent_state_change", _("Percent State Change"));
		$tpl->assign("m_mon_downtime_sc", _("In Scheduled Downtime?"));
		$tpl->assign("m_mon_last_update", _("Last Update"));
		$tpl->assign("m_mon_tips", _("Tips"));
		$tpl->assign("m_mon_tools", _("Tools"));
		$tpl->assign("m_mon_service_command", _("Service Commands"));
		$tpl->assign("m_mon_check_this_service", _("Checks for this service"));
		$tpl->assign("m_mon_schedule", _("Re-schedule the next check for this service"));
		$tpl->assign("m_mon_schedule_force", _("Re-schedule the next check for this service (forced)"));
		$tpl->assign("m_mon_submit_passive", _("Submit result for this service"));
		$tpl->assign("m_mon_accept_passive", _("Accepting passive checks for this service"));
		$tpl->assign("m_mon_notification_service", _("Notifications for this service"));
		$tpl->assign("m_mon_schedule_downtime", _("Schedule downtime for this service"));
		$tpl->assign("m_mon_schedule_comment", _("Add a comment for this service"));
		$tpl->assign("m_mon_event_handler", _("Event Handler"));
		$tpl->assign("m_mon_flap_detection", _("Flap Detection"));
		$tpl->assign("m_mon_services_en_check_active", _("Active Check Enabled :"));
		$tpl->assign("m_mon_services_en_check_passif", _("Passive Check Enabled :"));
		$tpl->assign("m_mon_services_en_notification", _("Notification Enabled :"));
		$tpl->assign("m_mon_services_en_flap", _("Flap Detection Enabled :"));
		$tpl->assign("m_mon_obsessing", _("Obsess"));
		$tpl->assign("m_comment_for_service", _("All Comments of this service"));
		$tpl->assign("cmt_host_name", _("Host Name"));
		$tpl->assign("cmt_service_descr", _("Services"));
		$tpl->assign("cmt_entry_time", _("Entry Time"));
		$tpl->assign("cmt_author", _("Author"));
		$tpl->assign("cmt_comment", _("Comments"));
		$tpl->assign("cmt_persistent", _("Persistent"));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		$tpl->assign("", _(""));
		
		
		
		$tpl->assign("p", $p);
		$tpl->assign("o", $o);
		$tpl->assign("en", $en);
		$tpl->assign("en_inv", $en_inv);
		$tpl->assign("en_inv_text", $en_inv_text);
		$tpl->assign("img_en", $img_en);
		$tpl->assign("color_onoff", $color_onoff);
		$tpl->assign("color_onoff_inv", $color_onoff_inv);
		$tpl->assign("en_disable", $en_disable);
		$tpl->assign("total_current_attempt", $total_current_attempts);
		$tpl->assign("en_acknowledge_text", $en_acknowledge_text);
		$tpl->assign("en_acknowledge", $en_acknowledge);
		$tpl->assign("actpass", array("0"=>_("Active"), "1"=>_("Passive")));
		$tpl->assign("harsof", array("0"=>_("Soft"), "1"=>_("Hard")));
		$tpl->assign("status", $status);
		$tpl->assign("h", $host);
		$tpl->assign("lcaTopo", $oreon->user->lcaTopo);
		$tpl->assign("count_comments_svc", count($tab_comments_svc));
		$tpl->assign("tab_comments_svc", $tab_comments_svc);
		$tpl->assign("service_id", getMyServiceID($svc_description, $host["host_id"]));
		$tpl->assign("host_data", $host_status[$host_name]);
		$tpl->assign("service_data", $service_status[$host_name."_".$svc_description]);
		$tpl->assign("svc_description", $svc_description);

		# Ext informations
		$tpl->assign("sv_ext_notes", getMyServiceExtendedInfoField($service_id, "esi_notes"));
		$tpl->assign("sv_ext_notes_url", getMyServiceExtendedInfoField($service_id, "esi_notes_url"));
		$tpl->assign("sv_ext_action_url_lang", _("Action URL"));
		$tpl->assign("sv_ext_action_url", getMyServiceExtendedInfoField($service_id, "esi_action_url"));
		$tpl->assign("sv_ext_icon_image_alt", getMyServiceExtendedInfoField($service_id, "esi_icon_image_alt"));
		$tpl->assign("options", $optionsURL);
		$tpl->display("serviceDetails.ihtml");
	}
?>
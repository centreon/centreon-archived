<?php
/*
 * Copyright 2005-2010 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */

	if (!isset($oreon))
		exit();

	include_once("./class/centreonDB.class.php");
	include_once("./class/centreonHost.class.php");
	include_once("./class/centreonService.class.php");
	
	$pearDBndo 	= new CentreonDB("ndo");
	
	$hostObj 	= new CentreonHost($pearDB);
	$svcObj 	= new CentreonService($pearDB);

	/*
	 * ACL Actions
	 */
	$GroupListofUser = array();
	$GroupListofUser =  $oreon->user->access->getAccessGroups();
	
	$allActions = false;
	/*
	 * Get list of actions allowed for user
	 */
	if (count($GroupListofUser) > 0 && $is_admin == 0) {
		$authorized_actions = array();
		$authorized_actions = $oreon->user->access->getActions();
	}

	$ndo_base_prefix = getNDOPrefix();

	if (isset($_GET["host_name"]) && $_GET["host_name"] != "" && isset($_GET["service_description"]) && $_GET["service_description"] != ""){
		$host_name = htmlentities($_GET["host_name"], ENT_QUOTES);
		$svc_description = htmlentities(str_replace("\\\\", "\\", $_GET["service_description"]), ENT_QUOTES);
	} else {
		foreach ($_GET["select"] as $key => $value)
			$tab_data = split(";", $key);
		$host_name = htmlentities($tab_data[0], ENT_QUOTES);
		$svc_description = htmlentities($tab_data[1], ENT_QUOTES);
	}

	/*
	 * Host Group List
	 */
	$host_id = getMyHostID($host_name);
	$lcaHost["LcaHost"] = $oreon->user->access->getHostServicesName($pearDBndo);

	if (!$is_admin && !isset($lcaHost["LcaHost"][$host_name])){
		include_once("../errors/alt_error.php");
	} else {

		$DBRESULT =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id = '".$host_id."' " .
					$oreon->user->access->queryBuilder("AND", "host_host_id", $oreon->user->access->getHostsString("ID", $pearDBndo)));
		for ($i = 0; $hg = $DBRESULT->fetchRow(); $i++)
			$hostGroups[] = getMyHostGroupName($hg["hostgroup_hg_id"]);
		$DBRESULT->free();
	
		$service_id = getMyServiceID($svc_description, $host_id);
	
		if (isset($service_id) && $service_id) {
			$proc_warning =  getMyServiceMacro($service_id, "PROC_WARNING");
			$proc_critical =  getMyServiceMacro($service_id, "PROC_CRITICAL");
		}
	
		/*
		 * Get service category
		 */
		
		$tab_sc = getMyServiceCategories($service_id);
        foreach ($tab_sc as $sc_id) {
          	$serviceCategories[] = getMyCategorieName($sc_id);
        }

		$tab_status = array();
		
		/*
		 * start ndo service info
		 */
		$rq =	"SELECT " .
				" nss.current_state," .
				" CONCAT( '<b>', nss.output, '</b><br>', nss.long_output ) as plugin_output," .
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
				" no.name2 as service_description, " .
				" ns.notes_url, " .
				" ns.notes, " .
				" ns.action_url " .
				" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, ".$ndo_base_prefix."services ns " .
				" WHERE no.object_id = nss.service_object_id AND no.name1 like '".$host_name."' AND no.object_id = ns.service_object_id";
	
		$DBRESULT_NDO =& $pearDBndo->query($rq);		
	
		$tab_status_service = array(0 => "OK", 1 => "WARNING", 2 => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	
		while ($ndo =& $DBRESULT_NDO->fetchRow()) {
			if ($ndo["service_description"] == $svc_description)
				$service_status[$host_name."_".$svc_description] = $ndo;
	
			if (!isset($tab_status[$ndo["current_state"]]))
				$tab_status[$tab_status_service[$ndo["current_state"]]] = 0;
			$tab_status[$tab_status_service[$ndo["current_state"]]]++;
		}
	
		$service_status[$host_name."_".$svc_description]["current_state"] = $tab_status_service[$service_status[$host_name."_".$svc_description]["current_state"]];
			
		/* 
		 * start ndo host detail
		 */
		$tab_host_status[0] = "UP";
		$tab_host_status[1] = "DOWN";
		$tab_host_status[2] = "UNREACHABLE";
	
		$rq2 =	"SELECT nhs.current_state" .
				" FROM ".$ndo_base_prefix."hoststatus nhs, ".$ndo_base_prefix."objects no" .
				" WHERE no.object_id = nhs.host_object_id AND no.name1 like '".$host_name."'";
		$DBRESULT_NDO =& $pearDBndo->query($rq2);
		$ndo2 =& $DBRESULT_NDO->fetchRow();
		$host_status[$host_name] = $tab_host_status[$ndo2["current_state"]];
		
		$DBRESULT =& $pearDB->query("SELECT * FROM host WHERE host_name = '".$host_name."'");
		$host =& $DBRESULT->fetchrow();
		$host_id = getMyHostID($host["host_name"]);
		$DBRESULT->free();
		
		$service_id = getMyServiceID($svc_description, $host_id);
		$total_current_attempts = getMyServiceField($service_id, "service_max_check_attempts");

		$path = "./include/monitoring/objectDetails/";

		/*
		 * Smarty template Init
		 */
		$tpl = new Smarty();
		$tpl = initSmartyTpl($path, $tpl, "./template/");

		$en = array("0" => _("No"), "1" => _("Yes"));
		
		/*
		 * Get comments for service
		 */
		$tabCommentServices = array();
		$rq2 =	" SELECT DISTINCT cmt.comment_time as entry_time, cmt.comment_id, cmt.author_name, cmt.comment_data, cmt.is_persistent, obj.name1 host_name, obj.name2 service_description " .
				" FROM ".$ndo_base_prefix."comments cmt, ".$ndo_base_prefix."objects obj " .
				" WHERE obj.name1 = '".$host_name."' AND obj.name2 = '".$svc_description."' AND obj.object_id = cmt.object_id AND cmt.expires = 0 ORDER BY cmt.comment_time";
		$DBRESULT_NDO =& $pearDBndo->query($rq2);
		for ($i = 0; $data =& $DBRESULT_NDO->fetchRow(); $i++){
			$tabCommentServices[$i] = $data;
			$tabCommentServices[$i]["is_persistent"] = $en[$tabCommentServices[$i]["is_persistent"]];
		}
		unset($data);
		
		$en_acknowledge_text= array("1" => _("Delete Problem Acknowledgement"), "0" => _("Acknowledge Service Problem"));
		$en_acknowledge 	= array("1" => "0", "0" => "1");
		$en_disable 		= array("1" => _("Enabled"), "0" => _("Disabled"));
		$en_inv			= array("1" => "1", "0" => "0");
		$en_inv_text 		= array("1" => _("Disable"), "0" => _("Enable"));
		$color_onoff 		= array("1" => "#00ff00", "0" => "#ff0000");
		$color_onoff_inv 	= array("0" => "#00ff00", "1" => "#ff0000");
		$img_en 		= array("0" => "'./img/icones/16x16/element_next.gif'", "1" => "'./img/icones/16x16/element_previous.gif'");

		/*
		 * Ajust data for beeing displayed in template
		 */
		 
		 $service_status[$host_name."_".$svc_description]["status_color"] = $oreon->optGen["color_".strtolower($service_status[$host_name."_".$svc_description]["current_state"])];
		 $service_status[$host_name."_".$svc_description]["last_check"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $service_status[$host_name."_".$svc_description]["last_check"], $oreon->user->getMyGMT());
		 $service_status[$host_name."_".$svc_description]["next_check"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $service_status[$host_name."_".$svc_description]["next_check"], $oreon->user->getMyGMT());
		!$service_status[$host_name."_".$svc_description]["check_latency"] ? $service_status[$host_name."_".$svc_description]["check_latency"] = "< 1 second" : $service_status[$host_name."_".$svc_description]["check_latency"] = $service_status[$host_name."_".$svc_description]["check_latency"] . " seconds";
		!$service_status[$host_name."_".$svc_description]["check_execution_time"] ? $service_status[$host_name."_".$svc_description]["check_execution_time"] = "< 1 second" : $service_status[$host_name."_".$svc_description]["check_execution_time"] = $service_status[$host_name."_".$svc_description]["check_execution_time"] . " seconds";
		
		!$service_status[$host_name."_".$svc_description]["last_notification"] ? $service_status[$host_name."_".$svc_description]["notification"] = "": $service_status[$host_name."_".$svc_description]["last_notification"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $service_status[$host_name."_".$svc_description]["last_notification"], $oreon->user->getMyGMT());
		
		if (isset($service_status[$host_name."_".$svc_description]["next_notification"]) && !$service_status[$host_name."_".$svc_description]["next_notification"]) 
			$service_status[$host_name."_".$svc_description]["next_notification"] = "";
		else if (!isset($service_status[$host_name."_".$svc_description]["next_notification"]))
			$service_status[$host_name."_".$svc_description]["next_notification"] = "N/A";
		else
			$service_status[$host_name."_".$svc_description]["next_notification"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $service_status[$host_name."_".$svc_description]["next_notification"], $oreon->user->getMyGMT());
		
		$service_status[$host_name."_".$svc_description]["plugin_output"] = utf8_encode($service_status[$host_name."_".$svc_description]["plugin_output"]);
		$service_status[$host_name.'_'.$svc_description]["plugin_output"] = str_replace("'", "", $service_status[$host_name.'_'.$svc_description]["plugin_output"]);	
		$service_status[$host_name.'_'.$svc_description]["plugin_output"] = str_replace("\"", "", $service_status[$host_name.'_'.$svc_description]["plugin_output"]);
		$service_status[$host_name."_".$svc_description]["plugin_output"] = str_replace("\\n", "<br>", $service_status[$host_name."_".$svc_description]["plugin_output"]);
		$service_status[$host_name."_".$svc_description]["plugin_output"] = str_replace('\n', "<br>", $service_status[$host_name."_".$svc_description]["plugin_output"]);
		
		$service_status[$host_name.'_'.$svc_description]["notes_url"] = str_replace("\$HOSTNAME\$", $host_name, $service_status[$host_name.'_'.$svc_description]["notes_url"]);
		$service_status[$host_name.'_'.$svc_description]["notes_url"] = str_replace("\$SERVICEDESC\$", $svc_description, $service_status[$host_name.'_'.$svc_description]["notes_url"]);
		
		$service_status[$host_name.'_'.$svc_description]["action_url"] = str_replace("\$HOSTNAME\$", $host_name, $service_status[$host_name.'_'.$svc_description]["action_url"]);
		$service_status[$host_name.'_'.$svc_description]["action_url"] = str_replace("\$SERVICEDESC\$", $svc_description, $service_status[$host_name.'_'.$svc_description]["action_url"]);

		!$service_status[$host_name."_".$svc_description]["last_state_change"] ? $service_status[$host_name."_".$svc_description]["duration"] = CentreonDuration::toString($service_status[$host_name."_".$svc_description]["last_time_".strtolower($service_status[$host_name."_".$svc_description]["current_state"])]) : $service_status[$host_name."_".$svc_description]["duration"] = centreonDuration::toString(time() - $service_status[$host_name."_".$svc_description]["last_state_change"]);
		!$service_status[$host_name."_".$svc_description]["last_state_change"] ? $service_status[$host_name."_".$svc_description]["last_state_change"] = "": $service_status[$host_name."_".$svc_description]["last_state_change"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"),$service_status[$host_name."_".$svc_description]["last_state_change"], $oreon->user->getMyGMT());
		 $service_status[$host_name."_".$svc_description]["last_update"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), time(), $oreon->user->getMyGMT());
		!$service_status[$host_name."_".$svc_description]["is_flapping"] ? $service_status[$host_name."_".$svc_description]["is_flapping"] = $en[$service_status[$host_name."_".$svc_description]["is_flapping"]] : $service_status[$host_name."_".$svc_description]["is_flapping"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $service_status[$host_name."_".$svc_description]["is_flapping"], $oreon->user->getMyGMT());


		if ($service_status[$host_name."_".$svc_description]["problem_has_been_acknowledged"])
			$service_status[$host_name."_".$svc_description]["current_state"] .= "&nbsp;&nbsp;<b>("._("ACKNOWLEDGED").")</b>";

		if (isset($service_status[$host_name."_".$svc_description]["scheduled_downtime_depth"]) && 
		    $service_status[$host_name."_".$svc_description]["scheduled_downtime_depth"]) {
		    $service_status[$host_name."_".$svc_description]["scheduled_downtime_depth"] = 1;
		}
			
		if (isset($ndo) && $ndo) {
			foreach ($tab_host_service[$host_name] as $key_name => $s) {
				if (!isset($tab_status[$service_status[$host_name."_".$key_name]["current_state"]]))
					$tab_status[$service_status[$host_name."_".$key_name]["current_state"]] = 0;
				$tab_status[$service_status[$host_name."_".$key_name]["current_state"]]++;
			}
		}
		
		$status = NULL;
		foreach ($tab_status as $key => $value)
			$status .= "&value[".$key."]=".$value;

		$optionsURL = "session_id=".session_id()."&host_name=".$host_name."&service_description=".$svc_description;


		$DBRES =& $pearDBO->query("SELECT id FROM `index_data` WHERE host_name LIKE '".$host_name."' AND service_description LIKE '".str_replace("/", "#S#",$svc_description)."' LIMIT 1");
		$index_data = 0;
		if ($DBRES->numRows()) {
			$row =& $DBRES->fetchRow();
			$index_data = $row['id'];
		}
		$optionsURL2 = "session_id=".session_id()."&index=".$index_data;

		/*
		 * Assign translations
		 */
		$tpl->assign("m_mon_services", _("Service"));
		$tpl->assign("m_mon_status_info", _("Status Details"));
		$tpl->assign("m_mon_on_host", _("on host"));
		$tpl->assign("m_mon_services_status", _("Service Status"));
		$tpl->assign("m_mon_host_status_info", _("Status information"));
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
		$tpl->assign("m_mon_tools", _("Tools"));
		$tpl->assign("m_mon_service_command", _("Service Commands"));
		$tpl->assign("m_mon_check_this_service", _("Checks for this service"));
		$tpl->assign("m_mon_schedule", _("Re-schedule the next check for this service"));
		$tpl->assign("m_mon_schedule_force", _("Re-schedule the next check for this service (forced)"));
		$tpl->assign("m_mon_submit_passive", _("Submit result for this service"));
		$tpl->assign("m_mon_schedule_downtime", _("Schedule downtime for this service"));
		$tpl->assign("m_mon_schedule_comment", _("Add a comment for this service"));
		$tpl->assign("m_mon_obsessing", _("Obsess Over Service"));
		$tpl->assign("m_comment_for_service", _("All Comments for this service"));
		$tpl->assign("cmt_host_name", _("Host Name"));
		$tpl->assign("cmt_service_descr", _("Services"));
		$tpl->assign("cmt_entry_time", _("Entry Time"));
		$tpl->assign("cmt_author", _("Author"));
		$tpl->assign("cmt_comment", _("Comments"));
		$tpl->assign("cmt_persistent", _("Persistent"));
		$tpl->assign("secondes", _("seconds"));
		$tpl->assign("m_mon_ticket", "Open Ticket");
		$tpl->assign("links", _("Links"));
		
		$tpl->assign("m_mon_services_en_check_active", _("Active Checks"));
		$tpl->assign("m_mon_services_en_check_passif", _("Passive Checks"));
		$tpl->assign("m_mon_accept_passive", _("Passive Checks"));
		$tpl->assign("m_mon_notification_service", _("Service Notifications"));
		$tpl->assign("m_mon_services_en_notification", _("Service Notifications"));
		$tpl->assign("m_mon_services_en_acknowledge", _("Acknowledged"));
		$tpl->assign("m_mon_event_handler", _("Event Handler"));
		$tpl->assign("m_mon_flap_detection", _("Flap Detection"));
		$tpl->assign("m_mon_services_en_flap", _("Flap Detection"));
		$str_check_svc_enable = _("Enable Active Checks");
		$str_check_svc_disable = _("Disable Active Checks");
		$str_passive_svc_enable = _("Enable Passive Checks");
		$str_passive_svc_disable = _("Disable Passive Checks");
		$str_notif_svc_enable = _("Enable Service Notifications");
		$str_notif_svc_disable = _("Disable Service Notifications");
		$str_handler_svc_enable = _("Enable Event Handler");
		$str_handler_svc_disable = _("Disable Event Handler");
		$str_flap_svc_enable = _("Enable Flap Detection");
		$str_flap_svc_disable = _("Disable Flap Detection");
		$str_obsess_svc_enable = _("Enable Obsess Over Service");
		$str_obsess_svc_disable = _("Disable Obsess Over Service");
		
		/*
		 * if user is admin, allActions is true, 
		 * else we introduce all actions allowed for user
		 */
		if (isset($authorized_actions))
			$tpl->assign("aclAct", $authorized_actions);
		
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
		$tpl->assign("harsof", array("0"=>_("SOFT"), "1"=>_("HARD")));
		$tpl->assign("status", $status);
		$tpl->assign("h", $host);
		$tpl->assign("admin", $is_admin);
		$tpl->assign("lcaTopo", $oreon->user->access->topology);
		$tpl->assign("count_comments_svc", count($tabCommentServices));
		$tpl->assign("tab_comments_svc", $tabCommentServices);
		$tpl->assign("flag_graph", service_has_graph($host["host_id"], getMyServiceID($svc_description, $host["host_id"])));
		$tpl->assign("service_id", getMyServiceID($svc_description, $host["host_id"]));
		$tpl->assign("host_data", $host_status[$host_name]);
		$tpl->assign("service_data", $service_status[$host_name."_".$svc_description]);
		$tpl->assign("host_name", $host_name);
		$tpl->assign("svc_description", $svc_description);
		
		$tpl->assign("status_str", _("Status Graph"));
		$tpl->assign("detailed_graph", _("Detailed Graph"));
		
		/*
		 * Hostgroups Display
		 */
		$tpl->assign("hostgroups_label", _("Host Groups"));
		if (isset($hostGroups))
			$tpl->assign("hostgroups", $hostGroups);
	
		/*
		 * Service Categories
		 */
		$tpl->assign("sg_label", _("Service Categories"));
		if (isset($serviceCategories))
			$tpl->assign("service_categories", $serviceCategories);
					
		/*
		 * Macros
		 */
		if (isset($proc_warning) && $proc_warning)
			$tpl->assign("proc_warning", $proc_warning);
		if (isset($proc_critical) && $proc_critical)
			$tpl->assign("proc_critical", $proc_critical);
		
		/*
		 * Tips translations
		 */
		$tpl->assign("host_shortcut", _("Host Shortcuts"));
		$tpl->assign("serv_shortcut", _("Service Shortcuts"));
		$tpl->assign("lnk_host_config", _("Configure host"));
		$tpl->assign("lnk_serv_config", _("Configure service"));
		$tpl->assign("lnk_host_graphs", _("View graphs for host"));
		$tpl->assign("lnk_host_reports", _("View report for host"));
		$tpl->assign("lnk_serv_reports", _("View report for service"));
		$tpl->assign("lnk_host_status", _("View host status page"));
		$tpl->assign("lnk_serv_status", _("View status of all services on host"));
		$tpl->assign("lnk_host_logs", _("View logs for host"));
		$tpl->assign("lnk_serv_logs", _("View logs for service"));

		/*
		 * Ext informations
		 */
		$notesurl = getMyServiceExtendedInfoField($service_id, "esi_notes_url");
		$notesurl = $hostObj->replaceMacroInString($host_id, $notesurl);
		$notesurl =  $svcObj->replaceMacroInString($service_id, $notesurl);
		$actionurl = getMyServiceExtendedInfoField($service_id, "esi_action_url");
		$actionurl = $hostObj->replaceMacroInString($host_id, $actionurl);
		$actionurl =  $svcObj->replaceMacroInString($service_id, $actionurl);
		
		$tpl->assign("sv_ext_notes", getMyServiceExtendedInfoField($service_id, "esi_notes"));
		$tpl->assign("sv_ext_notes_url", $notesurl);
		$tpl->assign("sv_ext_action_url_lang", _("Action URL"));
		$tpl->assign("sv_ext_action_url", $actionurl);
		$tpl->assign("sv_ext_icon_image_alt", getMyServiceExtendedInfoField($service_id, "esi_icon_image_alt"));
		$tpl->assign("options", $optionsURL);
		$tpl->assign("index_data", $index_data);
		$tpl->assign("options2", $optionsURL2);


		/* Dynamics tools */
		/**/
		$tools = array();
		$DBRESULT =& $pearDB->query("SELECT * FROM modules_informations");
		while($module = $DBRESULT->fetchrow())
		{
			if(isset($module['svc_tools']) && $module['svc_tools'] == 1 && file_exists('modules/'.$module['name'].'/svc_tools.php'))
				include('modules/'.$module['name'].'/svc_tools.php');
		}
		$DBRESULT->free();
		if(count($tools) > 0)
			$tpl->assign("tools", $tools);
		/**/
		/* Dynamics tools */

		$tpl->display("serviceDetails.ihtml");
		$host_name = str_replace("/", "#S#", $host_name);
		$host_name = str_replace("\\", "#BS#", $host_name);
		$svc_description = str_replace("/", "#S#", $svc_description);
		$svc_description = str_replace("\\", "#BS#", $svc_description);
	}
?>

<script type="text/javascript">		
	var _sid = '<?php echo session_id();?>';
	var glb_confirm = '<?php  echo _("Submit command?"); ?>';
	var command_sent = '<?php echo _("Command sent"); ?>';
	var command_failure = "<?php echo _("Failed to execute command");?>";
	var host_id = '<?php echo $hostObj->getHostId($host_name);?>';
	var svc_id = '<?php echo $svcObj->getServiceId($svc_description, $host_name);?>';
	var labels = new Array();
	
	labels['service_checks'] = new Array(	
	    "<?php echo $str_check_svc_enable;?>",
	    "<?php echo $str_check_svc_disable;?>",
	    "<?php echo $img_en[0];?>",
	    "<?php echo $img_en[1];?>"
	);
	
	labels['service_notifications'] = new Array(	
	    "<?php echo $str_notif_svc_enable;?>",
	    "<?php echo $str_notif_svc_disable;?>",
	    "<?php echo $img_en[0];?>",
	    "<?php echo $img_en[1];?>"
	);
	
	labels['service_event_handler'] = new Array(	
	    "<?php echo $str_handler_svc_enable;?>",
	    "<?php echo $str_handler_svc_disable;?>",
	    "<?php echo $img_en[0];?>",
	    "<?php echo $img_en[1];?>"
	);
	
	labels['service_flap_detection'] = new Array(	
	    "<?php echo $str_flap_svc_enable;?>",
	    "<?php echo $str_flap_svc_disable;?>",
	    "<?php echo $img_en[0];?>",
	    "<?php echo $img_en[1];?>"
	);
	
	labels['service_passive_checks'] = new Array(	
	    "<?php echo $str_passive_svc_enable;?>",
	    "<?php echo $str_passive_svc_disable;?>",
	    "<?php echo $img_en[0];?>",
	    "<?php echo $img_en[1];?>"
	);
	
	labels['service_obsess'] = new Array(	
	    "<?php echo $str_obsess_svc_enable;?>",
	    "<?php echo $str_obsess_svc_disable;?>",
	    "<?php echo $img_en[0];?>",
	    "<?php echo $img_en[1];?>"
	);
	
	function send_command(cmd, actiontype) {
		if (!confirm(glb_confirm)) {
			return 0;
		}
		if (window.XMLHttpRequest) { 
		    xhr_cmd = new XMLHttpRequest();
		}
		else if (window.ActiveXObject) 
		{
		    xhr_cmd = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xhr_cmd.onreadystatechange = function() { display_result(xhr_cmd, cmd); };
	   	xhr_cmd.open("GET", "./include/monitoring/objectDetails/xml/serviceSendCommand.php?cmd=" + cmd + "&host_id=" + host_id + "&service_id=" + svc_id + "&sid=" + _sid + "&actiontype=" + actiontype, true);
    		xhr_cmd.send(null);
	}
	
	function display_result(xhr_cmd, cmd) {
		if (xhr_cmd.readyState != 4 && xhr_cmd.readyState != "complete")
			return(0);			
		var msg_result;		
		var docXML= xhr_cmd.responseXML;
		var items_state = docXML.getElementsByTagName("result");
		var received_command = docXML.getElementsByTagName("cmd");
		var acttype = docXML.getElementsByTagName("actiontype");
		var state = items_state.item(0).firstChild.data;		
		var actiontype = acttype.item(0).firstChild.data;
		var executed_command = received_command.item(0).firstChild.data;
		var commands = new Array("service_checks", "service_notifications", "service_event_handler", "service_flap_detection", "service_passive_checks", "service_obsess");
    		
		if (state == "0") {
			msg_result = command_sent;
			for each (var mycmd in commands)
			    if (cmd == mycmd) {
				var tmp = atoi(actiontype) + 2;
				img_src= labels[executed_command][tmp];	
				document.getElementById(cmd).innerHTML = "<a href='#' onClick='send_command(\"" + cmd + "\", \""+ actiontype +"\")'>"
				+ "<img src=" + img_src 
				+ " alt=\"'" + labels[executed_command][actiontype] + "\"'"
				+ " onmouseover=\"Tip('" + labels[executed_command][actiontype] + "')\""
				+ " onmouseout='UnTip()'>"
				+ "</img></a>";
			    }
		}
		else {
			 msg_result = command_failure;
		}
		<?php
		require_once "./class/centreonMsg.class.php";
		?>
		_clear("centreonMsg");
		_setTextStyle("centreonMsg", "bold");	
		_setText("centreonMsg", msg_result);
		_nextLine("centreonMsg");
		_setTimeout("centreonMsg", 3);
	}
</script>

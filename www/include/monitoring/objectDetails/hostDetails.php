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
 
	if (!isset($oreon))
		exit();
	
	include_once("./class/centreonDB.class.php");
	
	$pearDBndo = new CentreonDB("ndo");

	/*
	 * ACL Actions
	 */
	$GroupListofUser = array();
	$GroupListofUser =  $oreon->user->access->getAccessGroups();
	
	$allActions = false;
	if (count($GroupListofUser) > 0 && $is_admin == 0) {
		$authorized_actions = array();
		$authorized_actions = $oreon->user->access->getActions();
		
		if (count($authorized_actions) == 0) 
			$allActions = true;
			
	} else {
	 	/*
	 	 * if user is admin, or without ACL, 
	 	 * he cans perform all actions
	 	 */
		$allActions = true;
	}

	/*
	 * ACL
	 */
	$ndo_base_prefix = getNDOPrefix();

	if (isset($_GET["host_name"]) && $_GET["host_name"])
		$host_name = $_GET["host_name"];
	else
		foreach ($_GET["select"] as $key => $value)
			$host_name = $key;

	if (!$is_admin)
		$lcaHost["LcaHost"] = $oreon->user->access->getHostServicesName($pearDBndo);

	$tab_status = array();

	if (!$is_admin && !isset($lcaHost["LcaHost"][$host_name])){
		include_once("alt_error.php");
	} else {
		/*
		 * Host Group List
		 */
	
		$host_id = getMyHostID($host_name);
	
		$DBRESULT =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id = '".$host_id."'");
		for ($i = 0; $hg = $DBRESULT->fetchRow(); $i++)
			$hostGroups[] = getMyHostGroupName($hg["hostgroup_hg_id"]);
		$DBRESULT->free();
	
		if (isset($host_id)) {
			$proc_warning = getMyHostMacro($host_id, "PROC_WARNING");
			$proc_critical = getMyHostMacro($host_id, "PROC_CRITICAL");
		}
				
		
		/*
		 * Init Table status
		 */
		$tab_status_service = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
		$tab_host_status = array(0 => "UP", 1 => "DOWN", 2 => "UNREACHABLE");
		
		/* 
		 * start ndo svc info 
		 */
		$rq ="SELECT nss.current_state," .
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
					" nss.is_flapping," .
					" nss.latency as check_latency," .
					" nss.execution_time as check_execution_time," .
					" nss.flap_detection_enabled," .
					" unix_timestamp(nss.last_notification) as last_notification," .
					" no.name1 as host_name," .
					" no.name2 as service_description" .
					" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
					" WHERE no.object_id = nss.service_object_id AND no.name1 like '".$host_name."' ";
	
		$DBRESULT_NDO =& $pearDBndo->query($rq);
		while ($ndo =& $DBRESULT_NDO->fetchRow())	{
			if (!isset($tab_status[$ndo["current_state"]]))
				$tab_status[$tab_status_service[$ndo["current_state"]]] = 0;
			$tab_status[$tab_status_service[$ndo["current_state"]]]++;
		}
	
		/* 
		 * start ndo host detail
		 */
		$rq2 = "SELECT nhs.current_state," .
			" nhs.problem_has_been_acknowledged, " .
			" nhs.passive_checks_enabled," .
			" nhs.active_checks_enabled," .
			" nhs.notifications_enabled," .
			" nhs.state_type," .
			" nhs.execution_time as check_execution_time," .
			" nhs.latency as check_latency," .
			" nhs.perfdata as performance_data," .
			" nhs.current_check_attempt as current_attempt," .
			" nhs.state_type," .
			" nhs.check_type," .
			" unix_timestamp(nhs.last_notification) as last_notification," .
			" unix_timestamp(nhs.next_notification) as next_notification," .
			" nhs.is_flapping," .
			" nhs.flap_detection_enabled," .
			" nhs.event_handler_enabled," .
			" nhs.obsess_over_host,".
			" nhs.current_notification_number," .
			" nhs.percent_state_change," .
			" nhs.scheduled_downtime_depth," .
			" unix_timestamp(nhs.last_state_change) as last_state_change," .
			" nhs.output as plugin_output," .
			" unix_timestamp(nhs.last_check) as last_check," .
			" unix_timestamp(nhs.last_notification) as last_notification," .
			" unix_timestamp(nhs.next_check) as next_check," .
			" nh.address," .
			" no.name1 as host_name" .
			" FROM ".$ndo_base_prefix."hoststatus nhs, ".$ndo_base_prefix."objects no, ".$ndo_base_prefix."hosts nh " .
			" WHERE no.object_id = nhs.host_object_id AND no.object_id = nh.host_object_id AND no.name1 like '".$host_name."'";
	
		$DBRESULT_NDO =& $pearDBndo->query($rq2);
		$ndo2 =& $DBRESULT_NDO->fetchRow();
	
		$host_status[$host_name] = $ndo2;
		$host_status[$host_name]["current_state"] = $tab_host_status[$ndo2["current_state"]];

		$res =& $pearDB->query("SELECT * FROM host WHERE host_name = '".$host_name."'");
		$hostDB =& $res->fetchRow();
		$current_attempts = getMyHostField($hostDB["host_id"], "host_max_check_attempts");
		
		$url_id = NULL;

		$path = "./include/monitoring/objectDetails/";

		$en = array("0" => _("No"), "1" => _("Yes"));
		
		/*
		 * Smarty template Init
		 */
		$tpl = new Smarty();
		$tpl = initSmartyTpl($path, $tpl, "./template/");

		/*
		 * Get comments for hosts
		 */
		$tabCommentHosts = array();
		$rq2 =	" SELECT cmt.comment_id, cmt.comment_time, cmt.author_name, cmt.comment_data, cmt.is_persistent, obj.name1 host_name" .
				" FROM ".$ndo_base_prefix."comments cmt, ".$ndo_base_prefix."objects obj " .
				" WHERE obj.name1 = '".$host_name."' AND obj.name2 IS NULL AND obj.object_id = cmt.object_id AND cmt.expires = 0 ORDER BY cmt.comment_time";
		$DBRESULT_NDO =& $pearDBndo->query($rq2);
		for ($i = 0; $data =& $DBRESULT_NDO->fetchRow(); $i++){
			$tabCommentHosts[$i] = $data;
			$tabCommentHosts[$i]["is_persistent"] = $en[$tabCommentHosts[$i]["is_persistent"]];
		}
		unset($data);

		$en_acknowledge_text 	= array("1" => _("Delete this Acknowledgement"), "0" => _("Acknowledge this host"));
		$en_acknowledge 		= array("1" => "0", "0" => "1");

		$en_inv 				= array("1" => "0", "0" => "1");
		$en_inv_text 			= array("1" => _("Disable"), "0" => _("Enable"));
		$color_onoff 			= array("1" => "#00ff00", "0" => "#ff0000");
		$color_onoff_inv 		= array("0" => "#00ff00", "1" => "#ff0000");
		$en_disable 			= array("1" => _("Enabled"), "0" => _("Disabled"));

		$img_en = array("0" => "<img src='./img/icones/16x16/element_next.gif' border='0'>", "1" => "<img src='./img/icones/16x16/element_previous.gif' border='0'>");

		$host_status[$host_name]["status_color"] = $oreon->optGen["color_".strtolower($host_status[$host_name]["current_state"])];
		$host_status[$host_name]["last_check"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $host_status[$host_name]["last_check"], $oreon->user->getMyGMT());
		$host_status[$host_name]["next_check"] = $host_status[$host_name]["next_check"] ? $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $host_status[$host_name]["next_check"], $oreon->user->getMyGMT()) : "";
		!$host_status[$host_name]["last_notification"] ? $host_status[$host_name]["last_notification"] = "": $host_status[$host_name]["last_notification"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $host_status[$host_name]["last_notification"], $oreon->user->getMyGMT());
		!$host_status[$host_name]["next_notification"] ? $host_status[$host_name]["next_notification"] = "": $host_status[$host_name]["next_notification"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $host_status[$host_name]["next_notification"], $oreon->user->getMyGMT());
		!$host_status[$host_name]["last_state_change"] ? $host_status[$host_name]["duration"] = "" : $host_status[$host_name]["duration"] = Duration::toString(time() - $host_status[$host_name]["last_state_change"]);
		!$host_status[$host_name]["last_state_change"] ? $host_status[$host_name]["last_state_change"] = "": $host_status[$host_name]["last_state_change"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"),$host_status[$host_name]["last_state_change"], $oreon->user->getMyGMT());
		$host_status[$host_name]["last_update"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), time(), $oreon->user->getMyGMT());

		if ($host_status[$host_name]["problem_has_been_acknowledged"])
			$host_status[$host_name]["current_state"] .= "&nbsp;&nbsp;<b>("._("ACKNOWLEDGED").")</b>";

		$tab_status_type = array("1" => "HARD", "0" => "SOFT");
		$host_status[$host_name]["state_type"] = $tab_status_type[$host_status[$host_name]["state_type"]];

		$host_status[$host_name]["is_flapping"] = $en[$host_status[$host_name]["is_flapping"]];

		if (isset($tab_host_service[$host_name]) && count($tab_host_service[$host_name]))
			foreach ($tab_host_service[$host_name] as $key_name => $s){
				if (!isset($tab_status[$service_status[$host_name."_".$key_name]["current_state"]]))
					$tab_status[$service_status[$host_name."_".$key_name]["current_state"]] = 0;
				$tab_status[$service_status[$host_name."_".$key_name]["current_state"]]++;
			}
		$status = NULL;
		if (isset($tab_status))
			foreach ($tab_status as $key => $value)
				$status .= "&value[".$key."]=".$value;

		$tpl->assign("m_mon_host", _("Host"));
		$tpl->assign("m_mon_host_info", _("Host Information"));
		$tpl->assign("m_mon_host_status", _("Host Status"));
		$tpl->assign("m_mon_host_status_info", _("Status Information"));
		$tpl->assign("m_mon_performance_data", _("Performance Data"));
		$tpl->assign("m_mon_current_attempt", _("Current Attempt"));
		$tpl->assign("m_mon_state_type", _("State Type"));
		$tpl->assign("m_mon_host_last_check", _("Last Check"));
		$tpl->assign("m_mon_state_type", _("State Type"));
		$tpl->assign("m_mon_next_check", _("Next Check"));
		$tpl->assign("m_mon_check_latency", _("Latency"));
		$tpl->assign("m_mon_check_execution_time", _("Execution Time"));
		$tpl->assign("m_mon_last_change", _("Last State Change"));
		$tpl->assign("m_mon_current_state_duration", _("Current State Duration"));
		$tpl->assign("m_mon_last_notification", _("Last Notification"));
		$tpl->assign("m_mon_next_notification", _("Next Notification"));
		$tpl->assign("m_mon_notification_nb", _("Current Notification Number"));
		$tpl->assign("m_mon_host_flapping", _("Is This Host Flapping?"));
		$tpl->assign("m_mon_percent_state_change", _("Percent State Change"));
		$tpl->assign("m_mon_downtime_sc", _("In Scheduled Downtime?"));
		$tpl->assign("m_mon_last_update", _("Last Update"));
		$tpl->assign("m_mon_host_checks_active", _("Active Checks"));
		$tpl->assign("m_mon_host_checks_passive", _("Passive Checks"));
		$tpl->assign("m_mon_host_notification", _("Host Notifications"));
		$tpl->assign("m_mon_obsess_over_host", _("Obsess Over Host"));
		$tpl->assign("m_mon_event_handler", _("Event Handler"));
		$tpl->assign("m_mon_flap_detection", _("Flap Detection"));
		$tpl->assign("m_mon_services_en_acknowledge", _("Acknowledge Enabled :"));
		$tpl->assign("m_mon_tips", _("Tips"));
		$tpl->assign("m_mon_tools", _("Tools"));		
		$tpl->assign("cmt_host_name", _("Host Name"));
		$tpl->assign("cmt_entry_time", _("Entry Time"));
		$tpl->assign("cmt_author", _("Author"));
		$tpl->assign("cmt_comment", _("Comments"));
		$tpl->assign("cmt_persistent", _("Persistent"));
		$tpl->assign("cmt_actions", _("Actions"));
		$tpl->assign("options", _("Options"));
		$tpl->assign("m_mon_configure", _("Manage"));
		$tpl->assign("m_mon_view_identity_file", _("View identity file"));
		$tpl->assign("m_mon_all_services", _("View all services of "));
		$tpl->assign("m_mon_all_graphs", _("View all graphs of "));
		$tpl->assign("m_mon_tools_ping", _("Ping"));
		$tpl->assign("m_mon_tools_tracert", _("Tracert"));
		$tpl->assign("hosts_command", _("Hosts Command"));
		$tpl->assign("m_mon_check_this_host", _("Checks for this host"));
		$tpl->assign("m_mon_notify_this_host", _("Notifications for this host"));
		$tpl->assign("m_mon_SCH_downtime", _("Schedule downtime for this host"));
		$tpl->assign("m_mon_add_comment", _("Add Comment for this host"));
		$tpl->assign("m_mon_disable_not_all_services", _("Disable notifications for all services on this host"));
		$tpl->assign("m_mon_enable_not_all_services", _("Enable notifications for all services on this host"));
		$tpl->assign("m_mon_SCH_immediate_check", _("Schedule an immediate check of all services on this host"));
		$tpl->assign("m_mon_SCH_immediate_check_f", _("Schedule an immediate check of all services on this host (forced)"));
		$tpl->assign("m_mon_diable_check_all_svc", _("Disable checks of all services on this host"));
		$tpl->assign("m_mon_enable_check_all_svc", _("Enable checks of all services on this host"));
		$tpl->assign("m_mon_ed_event_handler", _("Event handler for this host"));
		$tpl->assign("m_mon_ed_flapping_detect", _("Flap detection for this host"));
		$tpl->assign("m_mon_acknowledge", _("Acknowledge this host"));
		$tpl->assign("seconds", _("seconds"));
		$tpl->assign("links", _("Links"));

		/*
		 * if user is admin, allActions is true, 
		 * else we introduce all actions allowed for user
		 */
		$tpl->assign("acl_allActions", $allActions);
		if (isset($authorized_actions))
			$tpl->assign("aclAct", $authorized_actions);
			
		$tpl->assign("p", $p);
		$tpl->assign("en", $en);
		$tpl->assign("en_inv", $en_inv);
		$tpl->assign("current_attempts", $current_attempts);
		$tpl->assign("en_inv_text", $en_inv_text);
		$tpl->assign("img_en", $img_en);
		$tpl->assign("color_onoff", $color_onoff);
		$tpl->assign("color_onoff_inv", $color_onoff_inv);
		$tpl->assign("en_disable", $en_disable);
		$tpl->assign("img_en", $img_en);
		$tpl->assign("status", $status);
		$tpl->assign("en_acknowledge_text", $en_acknowledge_text);
		$tpl->assign("en_acknowledge", $en_acknowledge);
		$tpl->assign("lcaTopo", $oreon->user->lcaTopo);
		$tpl->assign("h", $hostDB);
		$tpl->assign("url_id", $url_id);
		$tpl->assign("m_mon_ticket", "Open Ticket");
		
		/*
		 * Hostgroups Display
		 */
		$tpl->assign("hostgroups_label", _("Hosts Groups"));
		if (isset($hostGroups))
			$tpl->assign("hostgroups", $hostGroups);
		
		/*
		 * Macros
		 */
		if (isset($proc_warning) && $proc_warning)
			$tpl->assign("proc_warning", $proc_warning);
		if (isset($proc_critical) && $proc_critical)
			$tpl->assign("proc_critical", $proc_critical);
		
		
		if (isset($tabCommentHosts))
			$tpl->assign("tab_comments_host", $tabCommentHosts);
		
		$tpl->assign("host_data", $host_status[$host_name]);

		/*
		 * Ext informations
		 */
		$tpl->assign("h_ext_notes", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_notes"));
		$tpl->assign("h_ext_notes_url", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_notes_url"));
		$tpl->assign("h_ext_action_url_lang", _("Action URL"));

		/*
		* This part was added by Kay Roesler to fix the $HOSTMANE$ Thingy
		*/

		$action_url = getMyHostExtendedInfoField($hostDB["host_id"], "ehi_action_url");
		$new_action_url = str_replace("\$HOSTNAME$", $host_name, $action_url);
		$tpl->assign("h_ext_action_url", $new_action_url);
		$tpl->assign("h_ext_icon_image", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_icon_image"));
		$tpl->assign("h_ext_icon_image_alt", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_icon_image_alt"));

		$tpl->display("hostDetails.ihtml");
	}
?>
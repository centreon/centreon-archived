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

	include_once "./class/centreonDB.class.php";
	include_once "./class/centreonHost.class.php";

	$pearDBndo 	= new CentreonDB("ndo");
	$hostObj 	= new CentreonHost($pearDB);

	/*
	 * ACL Actions
	 */
	$GroupListofUser = array();
	$GroupListofUser =  $oreon->user->access->getAccessGroups();

	$allActions = false;
	if (count($GroupListofUser) > 0 && $is_admin == 0) {
		$authorized_actions = array();
		$authorized_actions = $oreon->user->access->getActions();
	}

	/*
	 * ACL
	 */
	$ndo_base_prefix = getNDOPrefix();

	if (isset($_GET["host_name"]) && $_GET["host_name"]) {
		$host_name = $_GET["host_name"];
	} else {
		foreach ($_GET["select"] as $key => $value) {
			$host_name = $key;
		}
	}

	if (!$is_admin) {
		$lcaHost["LcaHost"] = $oreon->user->access->getHostServicesName($pearDBndo);
	}

	$tab_status = array();

	if (!$is_admin && !isset($lcaHost["LcaHost"][$host_name])){
		include_once("../errors/alt_error.php");
	} else {
		/*
		 * Host Group List
		 */

		$host_id = getMyHostID($host_name);

		$DBRESULT = $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id = '".$host_id."'");
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

		$DBRESULT_NDO = $pearDBndo->query($rq);
		while ($ndo = $DBRESULT_NDO->fetchRow())	{
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
			" no.name1 as host_name, " .
			" nh.notes_url, " .
		    " nh.alias, " .
			" nh.action_url " .
			" FROM ".$ndo_base_prefix."hoststatus nhs, ".$ndo_base_prefix."objects no, ".$ndo_base_prefix."hosts nh " .
			" WHERE no.object_id = nhs.host_object_id AND no.object_id = nh.host_object_id AND no.name1 like '".$host_name."'";

		$DBRESULT_NDO = $pearDBndo->query($rq2);
		$ndo2 = $DBRESULT_NDO->fetchRow();

		$host_status[$host_name] = $ndo2;
		$host_status[$host_name]["current_state"] = $tab_host_status[$ndo2["current_state"]];
		if (isset($host_status[$host_name]["notes_url"]) && $host_status[$host_name]["notes_url"]) {
		    $host_status[$host_name]["notes_url"] = str_replace("\$HOSTNAME\$", $ndo2["host_name"], $ndo2["notes_url"]);
		    $host_status[$host_name]["notes_url"] = str_replace("\$HOSTADDRESS\$", $ndo2["address"], $ndo2["notes_url"]);
		    $host_status[$host_name]["notes_url"] = str_replace("\$HOSTALIAS\$", $ndo2["alias"], $ndo2["notes_url"]);
		}
		if (isset($host_status[$host_name]["action_url"]) && $host_status[$host_name]["action_url"]) {
    		$host_status[$host_name]["action_url"] = str_replace("\$HOSTNAME\$", $ndo2["host_name"], $ndo2["action_url"]);
    		$host_status[$host_name]["action_url"] = str_replace("\$HOSTADDRESS\$", $ndo2["address"], $ndo2["action_url"]);
    		$host_status[$host_name]["action_url"] = str_replace("\$HOSTALIAS\$", $ndo2["alias"], $ndo2["action_url"]);
		}

		$res = $pearDB->query("SELECT * FROM host WHERE host_name = '".$host_name."'");
		$hostDB = $res->fetchRow();
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
		$DBRESULT_NDO = $pearDBndo->query($rq2);
		for ($i = 0; $data = $DBRESULT_NDO->fetchRow(); $i++){
			$tabCommentHosts[$i] = $data;
			$tabCommentHosts[$i]["is_persistent"] = $en[$tabCommentHosts[$i]["is_persistent"]];
		}
		unset($data);

		$en_acknowledge_text 	= array("1" => _("Delete Problem Acknowledgement"), "0" => _("Acknowledge Host Problem"));
		$en_acknowledge 		= array("1" => "0", "0" => "1");

		$en_inv 				= array("1" => "1", "0" => "0");
		$en_inv_text 			= array("1" => _("Disable"), "0" => _("Enable"));
		$color_onoff 			= array("1" => "#00ff00", "0" => "#ff0000");
		$color_onoff_inv 		= array("0" => "#00ff00", "1" => "#ff0000");
		$en_disable 			= array("1" => _("Enabled"), "0" => _("Disabled"));

		$img_en = array("0" => "'./img/icones/16x16/element_next.gif'", "1" => "'./img/icones/16x16/element_previous.gif'");

		$host_status[$host_name]["status_color"] = $oreon->optGen["color_".strtolower($host_status[$host_name]["current_state"])];
		$host_status[$host_name]["last_check"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $host_status[$host_name]["last_check"], $oreon->user->getMyGMT());
		$host_status[$host_name]["next_check"] = $host_status[$host_name]["next_check"] ? $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $host_status[$host_name]["next_check"], $oreon->user->getMyGMT()) : "";
		!$host_status[$host_name]["last_notification"] ? $host_status[$host_name]["last_notification"] = "": $host_status[$host_name]["last_notification"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $host_status[$host_name]["last_notification"], $oreon->user->getMyGMT());
		!$host_status[$host_name]["next_notification"] ? $host_status[$host_name]["next_notification"] = "": $host_status[$host_name]["next_notification"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), $host_status[$host_name]["next_notification"], $oreon->user->getMyGMT());
		!$host_status[$host_name]["last_state_change"] ? $host_status[$host_name]["duration"] = "" : $host_status[$host_name]["duration"] = CentreonDuration::toString(time() - $host_status[$host_name]["last_state_change"]);
		!$host_status[$host_name]["last_state_change"] ? $host_status[$host_name]["last_state_change"] = "": $host_status[$host_name]["last_state_change"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"),$host_status[$host_name]["last_state_change"], $oreon->user->getMyGMT());
		$host_status[$host_name]["last_update"] = $oreon->CentreonGMT->getDate(_("Y/m/d - H:i:s"), time(), $oreon->user->getMyGMT());

		if ($host_status[$host_name]["problem_has_been_acknowledged"])
			$host_status[$host_name]["current_state"] .= "&nbsp;&nbsp;<b>("._("ACKNOWLEDGED").")</b>";

		$tab_status_type = array("1" => "HARD", "0" => "SOFT");
		$host_status[$host_name]["state_type"] = $tab_status_type[$host_status[$host_name]["state_type"]];

		$host_status[$host_name]["is_flapping"] = $en[$host_status[$host_name]["is_flapping"]];

		if (isset($host_status[$host_name]["scheduled_downtime_depth"]) &&
		    $host_status[$host_name]["scheduled_downtime_depth"]) {
		    $host_status[$host_name]["scheduled_downtime_depth"] = 1;
		}

		$host_status[$host_name]["comments"] = $hostDB["host_comment"];

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
		$tpl->assign("m_mon_host_info", _("Status Details"));
		$tpl->assign("m_mon_host_status", _("Host Status"));
		$tpl->assign("m_mon_host_status_info", _("Status information"));
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
		$tpl->assign("m_mon_tools", _("Tools"));
		$tpl->assign("cmt_host_name", _("Host Name"));
		$tpl->assign("cmt_entry_time", _("Entry Time"));
		$tpl->assign("cmt_author", _("Author"));
		$tpl->assign("cmt_comment", _("Comments"));
		$tpl->assign("cmt_persistent", _("Persistent"));
		$tpl->assign("cmt_actions", _("Actions"));
		$tpl->assign("options", _("Options"));
		$tpl->assign("m_mon_tools_ping", _("Ping"));
		$tpl->assign("m_mon_tools_tracert", _("Tracert"));
		$tpl->assign("hosts_command", _("Host Commands"));
		$tpl->assign("m_mon_SCH_downtime", _("Schedule downtime for this host"));
		$tpl->assign("m_mon_add_comment", _("Add Comment for this host"));
		$tpl->assign("m_mon_disable_not_all_services", _("Disable all service notifications on this host"));
		$tpl->assign("m_mon_enable_not_all_services", _("Enable all service notifications on this host"));
		$tpl->assign("m_mon_SCH_immediate_check", _("Schedule an immediate check of all services on this host"));
		$tpl->assign("m_mon_SCH_immediate_check_f", _("Schedule an immediate check of all services on this host (forced)"));
		$tpl->assign("m_mon_diable_check_all_svc", _("Disable all service checks on this host"));
		$tpl->assign("m_mon_enable_check_all_svc", _("Enable all service checks on this host"));
		$tpl->assign("m_mon_acknowledge", _("Acknowledge problem"));
		$tpl->assign("seconds", _("seconds"));
		$tpl->assign("links", _("Links"));
		$tpl->assign("m_mon_host_comment", _("Comments"));

		$tpl->assign("m_mon_obsess_over_host", _("Obsess Over Host"));
		$tpl->assign("m_mon_check_this_host", _("Active Checks"));
		$tpl->assign("m_mon_host_checks_active", _("Active Checks"));
		$tpl->assign("m_mon_host_checks_passive", _("Passive Checks"));
		$tpl->assign("m_mon_passive_check_this_host", _("Passive Checks"));
		$tpl->assign("m_mon_host_notification", _("Notifications"));
		$tpl->assign("m_mon_notify_this_host", _("Notifications"));
		$tpl->assign("m_mon_event_handler", _("Event Handler"));
		$tpl->assign("m_mon_ed_event_handler", _("Event Handler"));
		$tpl->assign("m_mon_ed_flapping_detect", _("Flap Detection"));
		$tpl->assign("m_mon_flap_detection", _("Flap Detection"));
		$tpl->assign("m_mon_services_en_acknowledge", _("Acknowledged"));
		$tpl->assign("m_mon_submit_passive", _("Submit result for this host"));

		/*
		 * Strings are used by javascript command handler
		 */
		$str_check_host_enable = _("Enable Active Checks");
		$str_check_host_disable = _("Disable Active Checks");
		$str_passive_check_host_enable = _("Enable Passive Checks");
		$str_passive_check_host_disable = _("Disable Passive Checks");
		$str_notif_host_enable = _("Enable Host Notifications");
		$str_notif_host_disable = _("Disable Host Notifications");
		$str_handler_host_enable = _("Enable Event Handler");
		$str_handler_host_disable = _("Disable Event Handler");
		$str_flap_host_enable = _("Enable Flap Detection");
		$str_flap_host_disable = _("Disable Flap Detection");
		$str_obsess_host_enable = _("Enable Obsess Over Host");
		$str_obsess_host_disable = _("Disable Obsess Over Host");

		/*
		 * Add Tips
		 */
		$tpl->assign("shortcut", _("Host Shortcuts"));
		$tpl->assign("lnk_all_services", sprintf(_("View status of all services on host %s"), $host_name));
		$tpl->assign("lnk_host_graphs", sprintf(_("View graphs for host %s"), $host_name));
		$tpl->assign("lnk_host_config", sprintf(_("Configure host %s"), $host_name));
		$tpl->assign("lnk_host_reports", sprintf(_("View report for host %s"), $host_name));
		$tpl->assign("lnk_host_logs", sprintf(_("View logs for host %s"), $host_name));

		/*
		 * if user is admin, allActions is true,
		 * else we introduce all actions allowed for user
		 */
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
		$tpl->assign("status", $status);
		$tpl->assign("en_acknowledge_text", $en_acknowledge_text);
		$tpl->assign("en_acknowledge", $en_acknowledge);
		$tpl->assign("admin", $is_admin);
		$tpl->assign("lcaTopo", $oreon->user->access->topology);
		$tpl->assign("h", $hostDB);
		$tpl->assign("url_id", $url_id);
		$tpl->assign("m_mon_ticket", "Open Ticket");

		/*
		 * Hostgroups Display
		 */
		$tpl->assign("hostgroups_label", _("Member of Host Groups"));
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
		$notesurl = getMyHostExtendedInfoField($host_id, "ehi_notes_url");
		$notesurl = $hostObj->replaceMacroInString($host_id, $notesurl);
		$tpl->assign("h_ext_notes", getMyHostExtendedInfoField($host_id, "ehi_notes"));
		$tpl->assign("h_ext_notes_url", $notesurl);
		$tpl->assign("h_ext_action_url_lang", _("Action URL"));

		$actionurl = getMyHostExtendedInfoField($host_id, "ehi_action_url");
		$actionurl = $hostObj->replaceMacroInString($host_id, $actionurl);
		$tpl->assign("h_ext_action_url", $actionurl);
		$tpl->assign("h_ext_icon_image", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_icon_image"));
		$tpl->assign("h_ext_icon_image_alt", getMyHostExtendedInfoField($hostDB["host_id"], "ehi_icon_image_alt"));

		/* Dynamics tools */
		/**/
		$tools = array();
		$DBRESULT = $pearDB->query("SELECT * FROM modules_informations");
		while($module = $DBRESULT->fetchrow())
		{
			if(isset($module['host_tools']) && $module['host_tools'] == 1 && file_exists('modules/'.$module['name'].'/host_tools.php'))
				include('modules/'.$module['name'].'/host_tools.php');
		}
		$DBRESULT->free();
		if(count($tools) > 0)
			$tpl->assign("tools", $tools);
		/**/
		/* Dynamics tools */


		$tpl->display("hostDetails.ihtml");
		$host_name = str_replace("/", "#S#", $host_name);
		$host_name = str_replace("\\", "#BS#", $host_name);
	}
?>
<script type="text/javascript">
	var _sid = '<?php echo session_id();?>';
	var glb_confirm = '<?php  echo _("Submit command?"); ?>';
	var command_sent = '<?php echo _("Command sent"); ?>';
	var command_failure = "<?php echo _("Failed to execute command");?>";
	var host_id = '<?php echo $hostObj->getHostId($host_name);?>';
	var labels = new Array();

	labels['host_checks'] = new Array(
	    "<?php echo $str_check_host_enable;?>",
	    "<?php echo $str_check_host_disable;?>",
	    "<?php echo $img_en[0];?>",
	    "<?php echo $img_en[1];?>"
	);

	labels['host_notifications'] = new Array(
	    "<?php echo $str_notif_host_enable;?>",
	    "<?php echo $str_notif_host_disable;?>",
	    "<?php echo $img_en[0];?>",
	    "<?php echo $img_en[1];?>"
	);

	labels['host_event_handler'] = new Array(
	    "<?php echo $str_handler_host_enable;?>",
	    "<?php echo $str_handler_host_disable;?>",
	    "<?php echo $img_en[0];?>",
	    "<?php echo $img_en[1];?>"
	);

	labels['host_flap_detection'] = new Array(
	    "<?php echo $str_flap_host_enable;?>",
	    "<?php echo $str_flap_host_disable;?>",
	    "<?php echo $img_en[0];?>",
	    "<?php echo $img_en[1];?>"
	);

	labels['host_obsess'] = new Array(
	    "<?php echo $str_obsess_host_enable;?>",
	    "<?php echo $str_obsess_host_disable;?>",
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
	    xhr_cmd.open("GET", "./include/monitoring/objectDetails/xml/hostSendCommand.php?cmd=" + cmd + "&host_id=" + host_id + "&sid=" + _sid + "&actiontype=" + actiontype, true);
    	    xhr_cmd.send(null);
	}

	function display_result(xhr_cmd, cmd) {
		if (xhr_cmd.readyState != 4 && xhr_cmd.readyState != "complete")
			return(0);
		var msg_result;
		var docXML= xhr_cmd.responseXML;
		var items_state = docXML.getElementsByTagName("result");
		var acttype = docXML.getElementsByTagName("actiontype");
		var actiontype = acttype.item(0).firstChild.data;
		var received_command = docXML.getElementsByTagName("cmd");
		var executed_command = received_command.item(0).firstChild.data;
		var commands = new Array("host_checks", "host_notifications", "host_event_handler", "host_flap_detection", "host_obsess");

		var state = items_state.item(0).firstChild.data;
		if (state == "0") {
			 msg_result = command_sent;
			 for (var i = 0;i < commands.length; i++)
				 mycmd = commands[i];
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

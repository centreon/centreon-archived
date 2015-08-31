<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	if (!isset($oreon))
		exit();

	$tab = array("1" => "ENABLE", "0" => "DISABLE");

	include_once("./include/monitoring/external_cmd/extcmd.php");

	/*
	 * 	Re-Schedule for all services of an host
	 */
	function schedule_host_svc_checks($arg, $forced){
		global $pearDB, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_checks_for_services");

		if ($actions == true || $is_admin) {
			$tab_forced = array("0" => "", "1" => "_FORCED");
			$flg = send_cmd(" SCHEDULE".$tab_forced[$forced]."_HOST_SVC_CHECKS;" . $arg . ";" . time(), GetMyHostPoller($pearDB, $arg));
			return $flg;
		}
		return NULL;
	}

	/*
	 * SCHEDULE_SVC_CHECK
	 */
	function schedule_svc_checks($arg, $forced){
		global $pearDB, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_schedule_check");

		if ($actions == true || $is_admin) {
			$tab_forced = array("0" => "", "1" => "_FORCED");
			$tab_data = preg_split("/\;/", $arg);
			$flg = send_cmd(" SCHEDULE".$tab_forced[$forced]."_SVC_CHECK;". urldecode($tab_data[0]) . ";" . urldecode($tab_data[1]) . ";" . time(), GetMyHostPoller($pearDB, urldecode($tab_data[0])));
			return $flg;
		}
		return NULL;
	}

	/*
	 * host check
	 */
	function host_check($arg, $type){
		global $tab, $pearDB, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_checks");

		if ($actions == true || $is_admin) {
			$flg = send_cmd(" ". $tab[$type]."_HOST_CHECK;". urldecode($arg), GetMyHostPoller($pearDB, urldecode($arg)));
			return $flg;
		}

		return NULL;
	}

	/*
	 * 	host notification
	 */
	function host_notification($arg, $type){
		global $tab, $pearDB, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_notifications");

		if ($actions == true || $is_admin) {
			$flg = send_cmd(" ".$tab[$type]."_HOST_NOTIFICATIONS;". urldecode($arg), GetMyHostPoller($pearDB, urldecode($arg)));
			return $flg;
		}
		return NULL;
	}

	/*
	 * ENABLE_HOST_SVC_NOTIFICATIONS
	 */
	function host_svc_notifications($arg, $type){
		global $tab, $pearDB, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_notifications_for_services");

		if ($actions == true || $is_admin) {
			$flg = send_cmd(" " . $tab[$type] . "_HOST_SVC_NOTIFICATIONS;". urldecode($arg), GetMyHostPoller($pearDB, urldecode($arg)));
			return $flg;
		}
		return NULL;
	}

	/*
	 * ENABLE_HOST_SVC_CHECKS
	 */
	function host_svc_checks($arg, $type){
		global $tab, $pearDB, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_checks_for_services");

		if ($actions == true || $is_admin) {
			$flg = send_cmd(" " . $tab[$type] . "_HOST_SVC_CHECKS;". urldecode($arg) . ";" . time(), GetMyHostPoller($pearDB, urldecode($arg)));
			return $flg;
		}
		return NULL;
	}

	/*
	 * ENABLE_HOST_SVC_CHECKS
	 */
	function svc_check($arg, $type){
		global $tab, $pearDB, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_checks");

		if ($actions == true || $is_admin) {
			$tab_data = preg_split("/\;/", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_SVC_CHECK;". urldecode($tab_data["0"]) .";".urldecode($tab_data["1"]), GetMyHostPoller($pearDB, urldecode($tab_data["0"])));
			return $flg;
		}
		return NULL;
	}

	/*
	 * PASSIVE_SVC_CHECKS
	 */
	function passive_svc_check($arg, $type){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_passive_checks");

		if ($actions == true || $is_admin) {
			$tab_data = preg_split("/\;/", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_PASSIVE_SVC_CHECKS;". urldecode($tab_data[0]) . ";". urldecode($tab_data[1]), GetMyHostPoller($pearDB, urldecode($tab_data["0"])));
			return $flg;
		}
		return NULL;
	}

	/*
	 * SVC_NOTIFICATIONS
	 */
	function svc_notifications($arg, $type){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_notifications");

		if ($actions == true || $is_admin) {
			$tab_data = preg_split("/\;/", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_SVC_NOTIFICATIONS;". urldecode($tab_data[0]) . ";". urldecode($tab_data[1]), GetMyHostPoller($pearDB, urldecode($tab_data["0"])));
			return $flg;
		}
		return NULL;
	}

	/*
	 * SVC_EVENT_HANDLER
	 */
	function svc_event_handler($arg, $type){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_event_handler");

		if ($actions == true || $is_admin) {
			$tab_data = preg_split("/\;/", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_SVC_EVENT_HANDLER;". urldecode($tab_data[0]) .";".urldecode($tab_data[1]), GetMyHostPoller($pearDB, urldecode($tab_data["0"])));
			return $flg;
		}
		return NULL;
	}

	/*
	 * HOST_EVENT_HANDLER
	 */
	function host_event_handler($arg, $type){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_event_handler");

		if ($actions == true || $is_admin) {
			$tab_data = preg_split("/\;/", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_HOST_EVENT_HANDLER;". urldecode($arg), GetMyHostPoller($pearDB, urldecode($arg)));
			return $flg;
		}
		return NULL;
	}

	/*
	 * Enable or disable Flap detection
	 */
	function svc_flapping_enable($arg, $type){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_flap_detection");

		if ($actions == true || $is_admin) {
			$tab_data = preg_split("/\;/", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_SVC_FLAP_DETECTION;". urldecode($tab_data[0]) .";".urldecode($tab_data[1]), GetMyHostPoller($pearDB, urldecode($tab_data[0])));
			return $flg;
		}
		return NULL;
	}

	/*
	 * HOST_FLAP_DETECTION
	 */
	function host_flapping_enable($arg, $type){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_flap_detection");

		if ($actions == true || $is_admin) {
			$tab_data = preg_split("/\;/", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_HOST_FLAP_DETECTION;". urldecode($arg), GetMyHostPoller($pearDB, urldecode($arg)));
			return $flg;
		}
		return NULL;
	}

	/*
	 * enable or disable notification for a hostgroup
	 */
	function notifi_host_hostgroup($arg, $type){
		global $pearDB, $tab, $is_admin;
		$tab_data = preg_split("/\;/", $arg);
		$flg = send_cmd(" " . $tab[$type] . "_HOST_NOTIFICATIONS;". urldecode($tab_data[0]), GetMyHostPoller($pearDB, urldecode($tab_data[0])));
		return $flg;
	}

	/*
	 * Ack a host
	 */
	function acknowledgeHost($param){
		global $pearDB,$tab, $key, $is_admin, $oreon;

		$actions = false;
		$actions = $oreon->user->access->checkAction("host_acknowledgement");

		if ($actions == true || $is_admin) {
			$key = $param["host_name"];
			isset($param['sticky']) && $param['sticky'] == "1" ? $sticky = "2" : $sticky = "1";
			$host_poller = GetMyHostPoller($pearDB, htmlentities($param["host_name"], ENT_QUOTES, "UTF-8"));
			$flg = write_command(" ACKNOWLEDGE_HOST_PROBLEM;".urldecode($param["host_name"]).";$sticky;".htmlentities($param["notify"], ENT_QUOTES, "UTF-8").";".htmlentities($param["persistent"], ENT_QUOTES, "UTF-8").";".htmlentities($param["author"], ENT_QUOTES, "UTF-8").";".htmlentities($param["comment"], ENT_QUOTES, "UTF-8"), urldecode($host_poller));

			if (isset($param['ackhostservice']) && $param['ackhostservice'] == 1) {
				$svc_tab = getMyHostServices(getMyHostID(htmlentities($param["host_name"], ENT_QUOTES, "UTF-8")));
				if (count($svc_tab)) {
					foreach ($svc_tab as $key2 => $value) {
	            				write_command(" ACKNOWLEDGE_SVC_PROBLEM;".htmlentities(urldecode($param["host_name"]), ENT_QUOTES, "UTF-8").";".$value.";".$sticky.";".htmlentities($param["notify"], ENT_QUOTES, "UTF-8").";".htmlentities($param["persistent"], ENT_QUOTES, "UTF-8").";".htmlentities($param["author"], ENT_QUOTES, "UTF-8").";".htmlentities($param["comment"], ENT_QUOTES, "UTF-8"), urldecode($host_poller));
	                		}
				}
			}
			set_user_param($oreon->user->user_id, $pearDB, "ack_sticky", $param["sticky"]);
			set_user_param($oreon->user->user_id, $pearDB, "ack_notify", $param["notify"]);
			set_user_param($oreon->user->user_id, $pearDB, "ack_services", $param["ackhostservice"]);
			set_user_param($oreon->user->user_id, $pearDB, "ack_persistent", $param["persistent"]);
			return _("Your command has been sent");
		}
		return NULL;
	}

	/*
	 * Remove ack for a host
	 */
	function acknowledgeHostDisable(){
		global $pearDB,$tab, $_GET, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_disacknowledgement");

		if ($actions == true || $is_admin) {
			$flg = send_cmd(" REMOVE_HOST_ACKNOWLEDGEMENT;".urldecode($_GET["host_name"]), GetMyHostPoller($pearDB, urldecode($_GET["host_name"])));
			return $flg;
		}

		return NULL;
	}

	/*
	 * Remove ack for a service
	 */
	function acknowledgeServiceDisable(){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_disacknowledgement");

		if ($actions == true || $is_admin) {
			$flg = send_cmd(" REMOVE_SVC_ACKNOWLEDGEMENT;".urldecode($_GET["host_name"]).";".urldecode($_GET["service_description"]), GetMyHostPoller($pearDB, urldecode($_GET["host_name"])));
			return $flg;
		}
		return NULL;
	}

	/*
	 * Ack a service
	 */
	function acknowledgeService($param){
		global $pearDB, $tab, $is_admin, $oreon;

		$actions = false;
		$actions = $oreon->user->access->checkAction("service_acknowledgement");

		if ($actions == true || $is_admin) {
			$param["comment"] = $param["comment"];
			$param["comment"] = str_replace('\'', ' ', $param["comment"]);
			isset($param['sticky']) && $param['sticky'] == "1" ? $sticky = "2" : $sticky = "1";
			$flg = send_cmd(" ACKNOWLEDGE_SVC_PROBLEM;".urldecode($param["host_name"]).";".urldecode($param["service_description"]).";".$sticky.";".$param["notify"].";".$param["persistent"].";".$param["author"].";".$param["comment"], GetMyHostPoller($pearDB, urldecode($param["host_name"])));
			isset($param['force_check']) && $param['force_check'] ? $force_check = 1 : $force_check = 0;
		    if ($force_check == 1 && $oreon->user->access->checkAction("service_schedule_forced_check") == true) {
				send_cmd(" SCHEDULE_FORCED_SVC_CHECK;".urldecode($param["host_name"]).";".urldecode($param["service_description"]).";".time(), GetMyHostPoller($pearDB, urldecode($param["host_name"])));
			}
			set_user_param($oreon->user->user_id, $pearDB, "ack_sticky", $param["sticky"]);
		    set_user_param($oreon->user->user_id, $pearDB, "ack_notify", $param["notify"]);
		    set_user_param($oreon->user->user_id, $pearDB, "ack_persistent", $param["persistent"]);
		    set_user_param($oreon->user->user_id, $pearDB, "force_check", $force_check);
			return $flg;
		}
		return NULL;
	}

	function submitPassiveCheck() {
		global $pearDB, $key, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_submit_result");

		if ($actions == true || $is_admin) {
			$key = $_GET["host_name"];
			$flg = send_cmd(" PROCESS_SERVICE_CHECK_RESULT;".urldecode($_GET["host_name"]).";".urldecode($_GET["service_description"]).";".$_GET["return_code"].";".$_GET["output"]."|".$_GET["dataPerform"], GetMyHostPoller($pearDB, urldecode($_GET["host_name"])));
			return $flg;
		}
		return NULL;
	}

	function submitHostPassiveCheck() {
		global $pearDB, $key, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_submit_result");

		if ($actions == true || $is_admin) {
			$key = $_GET["host_name"];
			$flg = send_cmd(" PROCESS_HOST_CHECK_RESULT;".urldecode($_GET["host_name"]).";".$_GET["return_code"].";".$_GET["output"]."|".$_GET["dataPerform"], GetMyHostPoller($pearDB, urldecode($_GET["host_name"])));
			return $flg;
		}
		return NULL;
	}

	function notifi_svc_host_hostgroup($arg, $type){
		global $tab, $pearDB, $is_admin;
	/*	$res = $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
		while ($r = $res->fetchRow()){
			$resH = $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$r["host_host_id"]."'");
			$rH = $resH->fetchRow();
			$flg = send_cmd(" " . $tab[$type] . "_HOST_NOTIFICATIONS;". $rH["host_name"]);
		}
	*/
		return $flg;
	}

	function checks_svc_host_hostgroup($arg, $type){
		global $tab, $pearDB, $is_admin;
		/*$res = $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
		$r = $res->fetchRow();
		$flg = send_cmd(" " . $tab[$type] . "_HOST_SVC_CHECKS;". $rH["host_name"]);
		*/
		return $flg;
	}

	#############################################################################
	# Monitoring Quick Actions
	#############################################################################

	/*
	 * Quick Action -> service ack : Stop and start
	 */

	function autoAcknowledgeServiceStart($key){
		global $pearDB,$tab,$oreon, $is_admin;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_acknowledgement");

		if ($actions == true || $is_admin) {
			$comment = "Service Auto Acknowledge by ".$oreon->user->alias."\n";
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" ACKNOWLEDGE_SVC_PROBLEM;".urldecode($ressource[0]).";".urldecode($ressource[1]).";1;1;1;".$oreon->user->alias.";".$comment, GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	function autoAcknowledgeServiceStop($key){
		global $pearDB,$tab,$oreon, $is_admin;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_disacknowledgement");

		if ($actions == true || $is_admin) {
			$comment = "Service Auto Acknowledge by ".$oreon->user->alias."\n";
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" REMOVE_SVC_ACKNOWLEDGEMENT;".urldecode($ressource[0]).";".urldecode($ressource[1]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	/*
	 * Quick Action -> host ack : Stop and start
	 */

	function autoAcknowledgeHostStart($key){
		global $pearDB,$tab,$oreon, $is_admin;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_acknowledgement");

		if ($actions == true || $is_admin) {
			$comment = "Host Auto Acknowledge by ".$oreon->user->alias."\n";
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" ACKNOWLEDGE_HOST_PROBLEM;".urldecode($ressource[0]).";1;1;1;".$oreon->user->alias.";".$comment, GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	function autoAcknowledgeHostStop($key){
		global $pearDB,$tab,$oreon, $is_admin;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_disacknowledgement");

		if ($actions == true || $is_admin) {
			$comment = "Host Auto Acknowledge by ".$oreon->user->alias."\n";
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" REMOVE_HOST_ACKNOWLEDGEMENT;".urldecode($ressource[0]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	/*
	 * Quick Action -> service notification : Stop and start
	 */

	function autoNotificationServiceStart($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_notifications");

		if ($actions == true || $is_admin) {
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" ENABLE_SVC_NOTIFICATIONS;".urldecode($ressource[0]).";".urldecode($ressource[1]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	function autoNotificationServiceStop($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_notifications");

		if ($actions == true || $is_admin) {
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" DISABLE_SVC_NOTIFICATIONS;".urldecode($ressource[0]).";".urldecode($ressource[1]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	/*
	 * Quick Action -> host notification : Stop and start
	 */

	function autoNotificationHostStart($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_notifications");

		if ($actions == true || $is_admin) {
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" ENABLE_HOST_NOTIFICATIONS;".urldecode($ressource[0]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	function autoNotificationHostStop($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_notifications");

		if ($actions == true || $is_admin) {
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" DISABLE_HOST_NOTIFICATIONS;".urldecode($ressource[0]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	/*
	 * Quick Action -> service check : Stop and start
	 */

	function autoCheckServiceStart($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_checks");

		if ($actions == true || $is_admin) {
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" ENABLE_SVC_CHECK;".urldecode($ressource[0]).";".urldecode($ressource[1]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	function autoCheckServiceStop($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_checks");

		if ($actions == true || $is_admin) {
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" DISABLE_SVC_CHECK;".urldecode($ressource[0]).";".urldecode($ressource[1]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	/*
	 * Quick Action -> host check : Stop and start
	 */

	function autoCheckHostStart($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("host_checks");

		if ($actions == true || $is_admin) {
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" ENABLE_HOST_CHECK;".urldecode($ressource[0]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}

	function autoCheckHostStop($key){
		global $oreon;

		$actions = false;
		$actions = $oreon->user->access->checkAction("host_checks");

		if ($actions == true || $is_admin) {
			global $pearDB,$tab, $is_admin;
			$ressource = preg_split("/\;/", $key);
			$flg = send_cmd(" DISABLE_HOST_CHECK;".urldecode($ressource[0]), GetMyHostPoller($pearDB, urldecode($ressource[0])));
			return $flg;
		}
	}
?>

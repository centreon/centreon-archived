<?php
/*
 * Copyright 2005-2009 MERETHIS
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
			$tab_data = split(";", $arg);
			$flg = send_cmd(" SCHEDULE".$tab_forced[$forced]."_SVC_CHECK;". $tab_data[0] . ";" . $tab_data[1] . ";" . time(), GetMyHostPoller($pearDB, $tab_data[0]));
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
			$flg = send_cmd(" ". $tab[$type]."_HOST_CHECK;". $arg, GetMyHostPoller($pearDB, $arg));
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
			$flg = send_cmd(" ".$tab[$type]."_HOST_NOTIFICATIONS;". $arg, GetMyHostPoller($pearDB, $arg));
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
			$flg = send_cmd(" " . $tab[$type] . "_HOST_SVC_NOTIFICATIONS;". $arg, GetMyHostPoller($pearDB, $arg));
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
			$flg = send_cmd(" " . $tab[$type] . "_HOST_SVC_CHECKS;". $arg . ";" . time(), GetMyHostPoller($pearDB, $arg));
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
		$actions = $oreon->user->access->checkAction("host_checks");
		
		if ($actions == true || $is_admin) {
			$tab_data = split(";", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_SVC_CHECK;". $tab_data["0"] .";".$tab_data["1"], GetMyHostPoller($pearDB, $tab_data["0"]));
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
			$tab_data = split(";", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_PASSIVE_SVC_CHECKS;". $tab_data[0] . ";". $tab_data[1], GetMyHostPoller($pearDB, $tab_data["0"]));
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
			$tab_data = split(";", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_SVC_NOTIFICATIONS;". $tab_data[0] . ";". $tab_data[1], GetMyHostPoller($pearDB, $tab_data["0"]));
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
			$tab_data = split(";", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_SVC_EVENT_HANDLER;". $tab_data[0] .";".$tab_data[1], GetMyHostPoller($pearDB, $tab_data["0"]));
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
			$tab_data = split(";", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_HOST_EVENT_HANDLER;". $arg, GetMyHostPoller($pearDB, $arg));
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
			$tab_data = split(";", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_SVC_FLAP_DETECTION;". $tab_data[0] .";".$tab_data[1], GetMyHostPoller($pearDB, $tab_data[0]));
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
			$tab_data = split(";", $arg);
			$flg = send_cmd(" " . $tab[$type] . "_HOST_FLAP_DETECTION;". $arg, GetMyHostPoller($pearDB, $arg));
			return $flg;
		}
		return NULL;
	}
	
	/*
	 * enable or disable notification for a hostgroup 
	 */
	function notifi_host_hostgroup($arg, $type){
		global $pearDB, $tab, $is_admin;
		$tab_data = split(";", $arg);
		$flg = send_cmd(" " . $tab[$type] . "_HOST_NOTIFICATIONS;". $tab_data[0], GetMyHostPoller($pearDB, $tab_data[0]));
		return $flg;
	}

	/*
	 * Ack a host
	 */	
	function acknowledgeHost(){
		global $pearDB,$tab, $_GET, $key, $is_admin, $oreon;
		
		$actions = false;		
		$actions = $oreon->user->access->checkAction("host_acknowledgement");
		
		if ($actions == true || $is_admin) {
			$key = $_GET["host_name"];
			isset($_GET['sticky']) && $_GET['sticky'] == "1" ? $sticky = "2" : $sticky = "1";
			$host_poller = GetMyHostPoller($pearDB, htmlentities($_GET["host_name"], ENT_QUOTES));
			$flg = write_command(" ACKNOWLEDGE_HOST_PROBLEM;".$_GET["host_name"].";$sticky;".htmlentities($_GET["notify"], ENT_QUOTES).";".htmlentities($_GET["persistent"], ENT_QUOTES).";".htmlentities($_GET["author"], ENT_QUOTES).";".htmlentities($_GET["comment"], ENT_QUOTES), $host_poller);
			if (isset($_GET['ackhostservice']) && $_GET['ackhostservice'] == 1) {
				$svc_tab = getMyHostServices(getMyHostID(htmlentities($_GET["host_name"], ENT_QUOTES)));
	            if (count($svc_tab)) {
					foreach ($svc_tab as $key2 => $value) {
	            		write_command(" ACKNOWLEDGE_SVC_PROBLEM;".htmlentities($_GET["host_name"], ENT_QUOTES).";".$value.";".$sticky.";".htmlentities($_GET["notify"], ENT_QUOTES).";".htmlentities($_GET["persistent"], ENT_QUOTES).";".htmlentities($_GET["author"], ENT_QUOTES).";".htmlentities($_GET["comment"], ENT_QUOTES), $host_poller);
	                }
				}
			}
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
		$actions = $oreon->user->access->checkAction("host_acknowledgement");
		
		if ($actions == true || $is_admin) {
			$flg = send_cmd(" REMOVE_HOST_ACKNOWLEDGEMENT;".$_GET["host_name"], GetMyHostPoller($pearDB, $_GET["host_name"]));
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
		$actions = $oreon->user->access->checkAction("service_acknowledgement");
		
		if ($actions == true || $is_admin) {
			$flg = send_cmd(" REMOVE_SVC_ACKNOWLEDGEMENT;".$_GET["host_name"].";".$_GET["service_description"], GetMyHostPoller($pearDB, $_GET["host_name"]));
			return $flg;
		}
		return NULL;
	}

	/*
	 * Ack a service
	 */
	function acknowledgeService(){
		global $pearDB, $tab, $is_admin, $oreon;
		$actions = false;
		$actions = $oreon->user->access->checkAction("service_acknowledgement");
		
		if ($actions == true || $is_admin) {
			$_GET["comment"] = $_GET["comment"];
			$_GET["comment"] = str_replace('\'', ' ', $_GET["comment"]);
			$flg = send_cmd(" ACKNOWLEDGE_SVC_PROBLEM;".$_GET["host_name"].";".$_GET["service_description"].";".$_GET["sticky"]."---;".$_GET["notify"].";".$_GET["persistent"].";".$_GET["author"].";".$_GET["comment"], GetMyHostPoller($pearDB, $_GET["host_name"]));
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
			$flg = send_cmd(" PROCESS_SERVICE_CHECK_RESULT;".$_GET["host_name"].";".$_GET["service_description"].";".$_GET["return_code"].";".$_GET["output"]."|".$_GET["dataPerform"], GetMyHostPoller($pearDB, $_GET["host_name"]));
			return $flg;
		}
		return NULL;
	}
	
	
	function notifi_svc_host_hostgroup($arg, $type){
		global $tab, $pearDB, $is_admin;
	/*	$res =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
		while ($r =& $res->fetchRow()){
			$resH =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$r["host_host_id"]."'");
			$rH =& $resH->fetchRow();
			$flg = send_cmd(" " . $tab[$type] . "_HOST_NOTIFICATIONS;". $rH["host_name"]);
		}
	*/
		return $flg;
	}
	
	function checks_svc_host_hostgroup($arg, $type){
		global $tab, $pearDB, $is_admin;
		/*$res =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
		$r =& $res->fetchRow();
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
			$ressource = split(";", $key);
			$flg = send_cmd(" ACKNOWLEDGE_SVC_PROBLEM;".$ressource[0].";".$ressource[1].";1;1;1;".$oreon->user->alias.";".$comment, GetMyHostPoller($pearDB, $ressource[0]));
			return $flg;
		}
	}
	
	function autoAcknowledgeServiceStop($key){
		global $pearDB,$tab,$oreon, $is_admin;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("service_acknowledgement");
		
		if ($actions == true || $is_admin) {
			$comment = "Service Auto Acknowledge by ".$oreon->user->alias."\n";
			$ressource = split(";", $key);
			$flg = send_cmd(" REMOVE_SVC_ACKNOWLEDGEMENT;".$ressource[0].";".$ressource[1], GetMyHostPoller($pearDB, $ressource[0]));
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
			$ressource = split(";", $key);
			$flg = send_cmd(" ACKNOWLEDGE_HOST_PROBLEM;".$ressource[0].";1;1;1;".$oreon->user->alias.";".$comment, GetMyHostPoller($pearDB, $ressource[0]));
			return $flg;
		}
	}
	
	function autoAcknowledgeHostStop($key){
		global $pearDB,$tab,$oreon, $is_admin;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("host_acknowledgement");
		
		if ($actions == true || $is_admin) {
			$comment = "Host Auto Acknowledge by ".$oreon->user->alias."\n";
			$ressource = split(";", $key);
			$flg = send_cmd(" REMOVE_HOST_ACKNOWLEDGEMENT;".$ressource[0], GetMyHostPoller($pearDB, $ressource[0]));
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
			$ressource = split(";", $key);
			$flg = send_cmd(" ENABLE_SVC_NOTIFICATIONS;".$ressource[0].";".$ressource[1], GetMyHostPoller($pearDB, $ressource[0]));
			return $flg;
		}
	}
	
	function autoNotificationServiceStop($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("service_notifications");
		
		if ($actions == true || $is_admin) {
			$ressource = split(";", $key);
			$flg = send_cmd(" DISABLE_SVC_NOTIFICATIONS;".$ressource[0].";".$ressource[1], GetMyHostPoller($pearDB, $ressource[0]));
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
			$ressource = split(";", $key);
			$flg = send_cmd(" ENABLE_HOST_NOTIFICATIONS;".$ressource[0], GetMyHostPoller($pearDB, $ressource[0]));
			return $flg;
		}
	}
	
	function autoNotificationHostStop($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("host_notifications");
		
		if ($actions == true || $is_admin) {
			$ressource = split(";", $key);
			$flg = send_cmd(" DISABLE_HOST_NOTIFICATIONS;".$ressource[0], GetMyHostPoller($pearDB, $ressource[0]));
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
			$ressource = split(";", $key);
			$flg = send_cmd(" ENABLE_SVC_CHECK;".$ressource[0].";".$ressource[1], GetMyHostPoller($pearDB, $ressource[0]));
			return $flg;
		}
	}
	
	function autoCheckServiceStop($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("service_checks");
		
		if ($actions == true || $is_admin) {
			$ressource = split(";", $key);
			$flg = send_cmd(" DISABLE_SVC_CHECK;".$ressource[0].";".$ressource[1], GetMyHostPoller($pearDB, $ressource[0]));
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
			$ressource = split(";", $key);
			$flg = send_cmd(" ENABLE_HOST_CHECK;".$ressource[0], GetMyHostPoller($pearDB, $ressource[0]));
			return $flg;
		}
	}
	
	function autoCheckHostStop($key){
		global $oreon;
		
		$actions = false;		
		$actions = $oreon->user->access->checkAction("host_checks");
		
		if ($actions == true || $is_admin) {
			global $pearDB,$tab, $is_admin;
			$ressource = split(";", $key);
			$flg = send_cmd(" DISABLE_HOST_CHECK;".$ressource[0], GetMyHostPoller($pearDB, $ressource[0]));
			return $flg;
		}
	}
?>
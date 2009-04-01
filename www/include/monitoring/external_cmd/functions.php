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

	if (!isset($oreon))
		exit();

	$tab = array("1" => "ENABLE", "0" => "DISABLE");

	/*
	 * Write command in nagios pipe or in centcore pipe. 
	 */

	function write_command($cmd, $poller){
		global $oreon, $key, $pearDB;
		$str = NULL;
		$informations = split(";", $key);
		if ($poller && isPollerLocalhost($pearDB, $poller))
			$str = "echo '[" . time() . "]" . $cmd . "\n' >> " . $oreon->Nagioscfg["command_file"];
		else if (isHostLocalhost($pearDB, $informations[0]))
			$str = "echo '[" . time() . "]" . $cmd . "\n' >> " . $oreon->Nagioscfg["command_file"];
		else
			$str = "echo 'EXTERNALCMD:$poller:[" . time() . "]" . $cmd . "\n' >> " . "@CENTREON_VARLIB@/centcore.cmd";
		return passthru($str);
	}

	function send_cmd($arg){
		if (isset($arg))
			$flg = write_command($arg);
		$flg ? $ret = _("Your command has been sent") : $ret = "Problem Execution";
		return $ret;
	}
	
	/*
	 * 	Re-Schedule for all services of an host
	 */
	function schedule_host_svc_checks($arg, $forced){
		global $pearDB, $is_admin, $oreon;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("host_checks_for_services");
		
		if ($actions == true || $is_admin) {
			$tab_forced = array("0" => "", "1" => "_FORCED");
			$flg = write_command(" SCHEDULE".$tab_forced[$forced]."_HOST_SVC_CHECKS;" . $arg . ";" . time(), GetMyHostPoller($pearDB, $arg));
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
			$flg = write_command(" SCHEDULE".$tab_forced[$forced]."_SVC_CHECK;". $tab_data[0] . ";" . $tab_data[1] . ";" . time(), GetMyHostPoller($pearDB, $tab_data[0]));
			return _("Your command has been sent");
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
			$flg = write_command(" ". $tab[$type]."_HOST_CHECK;". $arg, GetMyHostPoller($pearDB, $arg));
			return _("Your command has been sent");
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
			$flg = write_command(" ".$tab[$type]."_HOST_NOTIFICATIONS;". $arg, GetMyHostPoller($pearDB, $arg));
			return _("Your command has been sent");
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
			$flg = write_command(" " . $tab[$type] . "_HOST_SVC_NOTIFICATIONS;". $arg, GetMyHostPoller($pearDB, $arg));
			return _("Your command has been sent");
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
			$flg = write_command(" " . $tab[$type] . "_HOST_SVC_CHECKS;". $arg, GetMyHostPoller($pearDB, $arg));
			return _("Your command has been sent");
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
			$flg = write_command(" " . $tab[$type] . "_SVC_CHECK;". $tab_data["0"] .";".$tab_data["1"], GetMyHostPoller($pearDB, $tab_data["0"]));
			return _("Your command has been sent");
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
			$flg = write_command(" " . $tab[$type] . "_PASSIVE_SVC_CHECKS;". $tab_data[0] . ";". $tab_data[1], GetMyHostPoller($pearDB, $tab_data["0"]));
			return _("Your command has been sent");
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
			$flg = write_command(" " . $tab[$type] . "_SVC_NOTIFICATIONS;". $tab_data[0] . ";". $tab_data[1], GetMyHostPoller($pearDB, $tab_data["0"]));
			return _("Your command has been sent");
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
			$flg = write_command(" " . $tab[$type] . "_SVC_EVENT_HANDLER;". $tab_data[0] .";".$tab_data[1], GetMyHostPoller($pearDB, $tab_data["0"]));
			return _("Your command has been sent");
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
			$flg = write_command(" " . $tab[$type] . "_HOST_EVENT_HANDLER;". $arg, GetMyHostPoller($pearDB, $arg));
			return _("Your command has been sent");
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
			$flg = write_command(" " . $tab[$type] . "_SVC_FLAP_DETECTION;". $tab_data[0] .";".$tab_data[1], GetMyHostPoller($pearDB, $tab_data[0]));
			return _("Your command has been sent");
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
			$flg = write_command(" " . $tab[$type] . "_HOST_FLAP_DETECTION;". $arg, GetMyHostPoller($pearDB, $arg));
			return _("Your command has been sent");
		}
		return NULL;
	}
	
	/*
	 * enable or disable notification for a hostgroup 
	 */
	function notifi_host_hostgroup($arg, $type){
		global $pearDB, $tab, $is_admin;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_HOST_NOTIFICATIONS;". $tab_data[0], GetMyHostPoller($pearDB, $tab_data[0]));
		return _("Your command has been sent");
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
			$flg = write_command(" ACKNOWLEDGE_HOST_PROBLEM;".$_GET["host_name"].";1;".$_GET["notify"].";".$_GET["persistent"].";".$_GET["author"].";".$_GET["comment"], GetMyHostPoller($pearDB, $_GET["host_name"]));
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
			$flg = write_command(" REMOVE_HOST_ACKNOWLEDGEMENT;".$_GET["host_name"], GetMyHostPoller($pearDB, $_GET["host_name"]));
			return _("Your command has been sent");
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
			$flg = write_command(" REMOVE_SVC_ACKNOWLEDGEMENT;".$_GET["host_name"].";".$_GET["service_description"], GetMyHostPoller($pearDB, $_GET["host_name"]));
			return _("Your command has been sent");
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
			$flg = write_command(" ACKNOWLEDGE_SVC_PROBLEM;".$_GET["host_name"].";".$_GET["service_description"].";1;".$_GET["notify"].";".$_GET["persistent"].";".$_GET["author"].";".$_GET["comment"], GetMyHostPoller($pearDB, $_GET["host_name"]));
			return _("Your command has been sent");
		}
		return NULL;
	}

	function submitPassiveCheck() {
		global $pearDB, $key, $is_admin, $oreon;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("service_submit_result");
		
		if ($actions == true || $is_admin) {
			$key = $_GET["host_name"];
			$flg = write_command(" PROCESS_SERVICE_CHECK_RESULT;".$_GET["host_name"].";".$_GET["service_description"].";".$_GET["return_code"].";".$_GET["output"]."|".$_GET["dataPerform"], GetMyHostPoller($pearDB, $_GET["host_name"]));
			return _("Your command has been sent");
		}
		return NULL;
	}
	
	
	function notifi_svc_host_hostgroup($arg, $type){
		global $tab, $pearDB, $is_admin;
	/*	$res =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
		while ($r =& $res->fetchRow()){
			$resH =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$r["host_host_id"]."'");
			$rH =& $resH->fetchRow();
			$flg = write_command(" " . $tab[$type] . "_HOST_NOTIFICATIONS;". $rH["host_name"]);
		}
	*/
		return _("Your command has been sent");
	}
	
	function checks_svc_host_hostgroup($arg, $type){
		global $tab, $pearDB, $is_admin;
		/*$res =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
		$r =& $res->fetchRow();
		$flg = write_command(" " . $tab[$type] . "_HOST_SVC_CHECKS;". $rH["host_name"]);
		*/
		return _("Your command has been sent");
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
			$flg = write_command(" ACKNOWLEDGE_SVC_PROBLEM;".$ressource[0].";".$ressource[1].";1;1;1;".$oreon->user->alias.";".$comment, GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
		}
	}
	
	function autoAcknowledgeServiceStop($key){
		global $pearDB,$tab,$oreon, $is_admin;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("service_acknowledgement");
		
		if ($actions == true || $is_admin) {
			$comment = "Service Auto Acknowledge by ".$oreon->user->alias."\n";
			$ressource = split(";", $key);
			$flg = write_command(" REMOVE_SVC_ACKNOWLEDGEMENT;".$ressource[0].";".$ressource[1], GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
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
			$flg = write_command(" ACKNOWLEDGE_HOST_PROBLEM;".$ressource[0].";1;1;1;".$oreon->user->alias.";".$comment, GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
		}
	}
	
	function autoAcknowledgeHostStop($key){
		global $pearDB,$tab,$oreon, $is_admin;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("host_acknowledgement");
		
		if ($actions == true || $is_admin) {
			$comment = "Host Auto Acknowledge by ".$oreon->user->alias."\n";
			$ressource = split(";", $key);
			$flg = write_command(" REMOVE_HOST_ACKNOWLEDGEMENT;".$ressource[0], GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
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
			$flg = write_command(" ENABLE_SVC_NOTIFICATIONS;".$ressource[0].";".$ressource[1], GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
		}
	}
	
	function autoNotificationServiceStop($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("service_notifications");
		
		if ($actions == true || $is_admin) {
			$ressource = split(";", $key);
			$flg = write_command(" DISABLE_SVC_NOTIFICATIONS;".$ressource[0].";".$ressource[1], GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
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
			$flg = write_command(" ENABLE_HOST_NOTIFICATIONS;".$ressource[0], GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
		}
	}
	
	function autoNotificationHostStop($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("host_notifications");
		
		if ($actions == true || $is_admin) {
			$ressource = split(";", $key);
			$flg = write_command(" DISABLE_HOST_NOTIFICATIONS;".$ressource[0], GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
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
			$flg = write_command(" ENABLE_SVC_CHECK;".$ressource[0].";".$ressource[1], GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
		}
	}
	
	function autoCheckServiceStop($key){
		global $pearDB,$tab, $is_admin, $oreon;
		$actions = false;		
		$actions = $oreon->user->access->checkAction("service_checks");
		
		if ($actions == true || $is_admin) {
			$ressource = split(";", $key);
			$flg = write_command(" DISABLE_SVC_CHECK;".$ressource[0].";".$ressource[1], GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
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
			$flg = write_command(" ENABLE_HOST_CHECK;".$ressource[0], GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
		}
	}
	
	function autoCheckHostStop($key){
		global $oreon;
		
		$actions = false;		
		$actions = $oreon->user->access->checkAction("host_checks");
		
		if ($actions == true || $is_admin) {
			global $pearDB,$tab, $is_admin;
			$ressource = split(";", $key);
			$flg = write_command(" DISABLE_HOST_CHECK;".$ressource[0], GetMyHostPoller($pearDB, $ressource[0]));
			return _("Your command has been sent");
		}
	}
?>
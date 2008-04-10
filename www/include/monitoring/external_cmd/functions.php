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
 * For information : contact@oreon-project.org
 */
	if (!isset($oreon))
		exit();

	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";

	function write_command($cmd){
		global $oreon, $key, $pearDB;
		$str = NULL;
		$cmd = htmlentities($cmd);

		$informations = split(";", $key);
		if (isHostLocalhost($pearDB, $informations[0]))
			$str = "echo '[" . time() . "]" . $cmd . "\n' >> " . $oreon->Nagioscfg["command_file"];
		else
			$str = "echo '[" . time() . "]" . $cmd . "\n' >> " . "/srv/centreon/var/centreon.cmd";
		return passthru($str);
	}

	function send_cmd($arg){
		if (isset($arg))
			$flg = write_command($arg);
		$flg ? $ret = _("Your command has been sent") : $ret = "Problem Execution";
		return $ret;
	}
	
	// Re-Schedule for all service of an host
	
	function schedule_host_svc_checks($arg, $forced){
		global $pearDB;
		$tab_forced = array("0" => "", "1" => "_FORCED");
		$flg = write_command(" SCHEDULE".$tab_forced[$forced]."_HOST_SVC_CHECKS;" . $arg . ";" . time());
		return $flg;
	}
	
	// SCHEDULE_SVC_CHECK
	
	function schedule_svc_checks($arg, $forced){
		global $pearDB;
		$tab_forced = array("0" => "", "1" => "_FORCED");
		$tab_data = split(";", $arg);
		$flg = write_command(" SCHEDULE".$tab_forced[$forced]."_SVC_CHECK;". $tab_data[0] . ";" . $tab_data[1] . ";" . time());
		return _("Your command has been sent");
	}
	
	// host check
	
	function host_check($arg, $type){
		global $tab, $pearDB;
		$flg = write_command(" ". $tab[$type]."_HOST_CHECK;". $arg);
		return _("Your command has been sent");
	}
	
	//  host notification
	
	function host_notification($arg, $type){
		global $tab, $pearDB;
		$flg = write_command(" ".$tab[$type]."_HOST_NOTIFICATIONS;". $arg);
		return _("Your command has been sent");
	}
	
	// ENABLE_HOST_SVC_NOTIFICATIONS
	
	function host_svc_notifications($arg, $type){
		global $tab, $pearDB;
		$flg = write_command(" " . $tab[$type] . "_HOST_SVC_NOTIFICATIONS;". $arg);
		return _("Your command has been sent");
	}
	
	// ENABLE_HOST_SVC_CHECKS
	
	function host_svc_checks($arg, $type){
		global $tab, $pearDB;
		$flg = write_command(" " . $tab[$type] . "_HOST_SVC_CHECKS;". $arg);
		return _("Your command has been sent");
	}
	
	// ENABLE_HOST_SVC_CHECKS
	
	function svc_check($arg, $type){
		global $tab, $pearDB;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_SVC_CHECK;". $tab_data["0"] .";".$tab_data["1"]);
		return _("Your command has been sent");
	}
	
	// PASSIVE_SVC_CHECKS
	
	function passive_svc_check($arg, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_PASSIVE_SVC_CHECKS;". $tab_data[0] . ";". $tab_data[1]);
		return _("Your command has been sent");
	}
	
	// SVC_NOTIFICATIONS
	
	function svc_notifications($arg, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_SVC_NOTIFICATIONS;". $tab_data[0] . ";". $tab_data[1]);
		return _("Your command has been sent");
	}
	
	// _SVC_EVENT_HANDLER
	
	function svc_event_handler($arg, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_SVC_EVENT_HANDLER;". $tab_data[0] .";".$tab_data[1]);
		return _("Your command has been sent");
	}
	
	// _HOST_EVENT_HANDLER
	
	function host_event_handler($arg, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_HOST_EVENT_HANDLER;". $arg);
		return _("Your command has been sent");
	}
	
	//_SVC_FLAP_DETECTION
	
	function svc_flapping_enable($arg, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_SVC_FLAP_DETECTION;". $tab_data[0] .";".$tab_data[1]);
		return _("Your command has been sent");
	}
	
	//_HOST_FLAP_DETECTION
	
	function host_flapping_enable($arg, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_HOST_FLAP_DETECTION;". $arg);
		return _("Your command has been sent");
	}
	
	function notifi_host_hostgroup($arg, $type){
		global $pearDB,$tab;
		$tab_data = split(";", $arg);
		$flg = write_command(" " . $tab[$type] . "_HOST_NOTIFICATIONS;". $tab_data[0]);
		return _("Your command has been sent");
	}
	
	function acknowledgeHost(){
		global $pearDB,$tab, $_GET;
		$flg = write_command(" ACKNOWLEDGE_HOST_PROBLEM;".$_GET["host_name"].";1;".$_GET["notify"].";".$_GET["persistent"].";".$_GET["author"].";".$_GET["comment"]);
		return _("Your command has been sent");
	}
	
	function acknowledgeHostDisable(){
		global $pearDB,$tab, $_GET;
		$flg = write_command(" REMOVE_HOST_ACKNOWLEDGEMENT;".$_GET["host_name"]);
		return _("Your command has been sent");
	}
	
	function acknowledgeServiceDisable(){
		global $pearDB,$tab;
		$flg = write_command(" REMOVE_SVC_ACKNOWLEDGEMENT;".$_GET["host_name"].";".$_GET["service_description"]);
		return _("Your command has been sent");
	}

	function acknowledgeService(){
		global $pearDB,$tab;
		$_GET["comment"] = htmlentities($_GET["comment"]);
		$_GET["comment"] = str_replace('\'', ' ', $_GET["comment"]);
		$flg = write_command(" ACKNOWLEDGE_SVC_PROBLEM;".$_GET["host_name"].";".$_GET["service_description"].";1;".$_GET["notify"].";".$_GET["persistent"].";".$_GET["author"].";".$_GET["comment"]);
		return _("Your command has been sent");
	}

	function submitPassiveCheck(){
		global $pearDB;
		$flg = write_command(" PROCESS_SERVICE_CHECK_RESULT;".$_GET["host_name"].";".$_GET["service_description"].";".$_GET["return_code"].";".$_GET["output"]."|".$_GET["dataPerform"]);
		return _("Your command has been sent");
	}
	
	
	function notifi_svc_host_hostgroup($arg, $type){
		global $tab, $pearDB;
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
		global $tab, $pearDB;
		/*$res =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$arg."'");
		$r =& $res->fetchRow();
		$flg = write_command(" " . $tab[$type] . "_HOST_SVC_CHECKS;". $rH["host_name"]);
		*/
		return _("Your command has been sent");
	}
	
	#############################################################################
	# Monitoring Quick Actions
	#############################################################################
	
	/* Aknowledge */
	
	function autoAcknowledgeServiceStart($key){
		global $pearDB,$tab,$oreon;
		$comment = "Service Auto Aknowledge by ".$oreon->user->alias."\n";
		$ressource = split(";", $key);
		$flg = write_command(" ACKNOWLEDGE_SVC_PROBLEM;".$ressource[0].";".$ressource[1].";1;1;1;".$oreon->user->alias.";".$comment);
		return _("Your command has been sent");
	}
	
	function autoAcknowledgeServiceStop($key){
		global $pearDB,$tab,$oreon;
		$comment = "Service Auto Aknowledge by ".$oreon->user->alias."\n";
		$ressource = split(";", $key);
		$flg = write_command(" REMOVE_SVC_ACKNOWLEDGEMENT;".$ressource[0].";".$ressource[1]);
		return _("Your command has been sent");
	}
	
	function autoAcknowledgeHostStart($key){
		global $pearDB,$tab,$oreon;
		$comment = "Host Auto Aknowledge by ".$oreon->user->alias."\n";
		$ressource = split(";", $key);
		$flg = write_command(" ACKNOWLEDGE_HOST_PROBLEM;".$ressource[0].";1;1;1;".$oreon->user->alias.";".$comment);
		return _("Your command has been sent");
	}
	
	function autoAcknowledgeHostStop($key){
		global $pearDB,$tab,$oreon;
		$comment = "Host Auto Aknowledge by ".$oreon->user->alias."\n";
		$ressource = split(";", $key);
		$flg = write_command(" REMOVE_HOST_ACKNOWLEDGEMENT;".$ressource[0]);
		return _("Your command has been sent");
	}
	
	/* Notification */
	
	function autoNotificationServiceStart($key){
		global $pearDB,$tab;
		$ressource = split(";", $key);
		$flg = write_command(" ENABLE_SVC_NOTIFICATIONS;".$ressource[0].";".$ressource[1]);
		return _("Your command has been sent");
	}
	
	function autoNotificationServiceStop($key){
		global $pearDB,$tab;
		$ressource = split(";", $key);
		$flg = write_command(" DISABLE_SVC_NOTIFICATIONS;".$ressource[0].";".$ressource[1]);
		return _("Your command has been sent");
	}
	
	function autoNotificationHostStart($key){
		global $pearDB,$tab;
		$ressource = split(";", $key);
		$flg = write_command(" ENABLE_HOST_NOTIFICATIONS;".$ressource[0]);
		return _("Your command has been sent");
	}
	
	function autoNotificationHostStop($key){
		global $pearDB,$tab;
		$ressource = split(";", $key);
		$flg = write_command(" DISABLE_HOST_NOTIFICATIONS;".$ressource[0]);
		return _("Your command has been sent");
	}
	
	/* Check */
	
	function autoCheckServiceStart($key){
		global $pearDB,$tab;
		$ressource = split(";", $key);
		$flg = write_command(" ENABLE_SVC_CHECK;".$ressource[0].";".$ressource[1]);
		return _("Your command has been sent");
	}
	
	function autoCheckServiceStop($key){
		global $pearDB,$tab;
		$ressource = split(";", $key);
		$flg = write_command(" DISABLE_SVC_CHECK;".$ressource[0].";".$ressource[1]);
		return _("Your command has been sent");
	}
	
	function autoCheckHostStart($key){
		global $pearDB,$tab;
		$ressource = split(";", $key);
		$flg = write_command(" ENABLE_HOST_CHECK;".$ressource[0]);
		return _("Your command has been sent");
	}
	
	function autoCheckHostStop($key){
		global $pearDB,$tab;
		$ressource = split(";", $key);
		$flg = write_command(" DISABLE_HOST_CHECK;".$ressource[0]);
		return _("Your command has been sent");
	}
	
?>
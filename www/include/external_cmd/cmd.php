<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon.org
*/
	if (!isset($oreon))
		exit();

	include_once("./include/external_cmd/comments.php");
	include_once("./include/external_cmd/downtime.php");
	
function send_cmd($oreon, $arg1, $arg2, $arg3, $arg4, $arg5, $arg6)
{
	if (isset($arg1) && isset($arg2))
		$str = "echo '[" . time() . "] " . $arg1 . ";" . $arg2 . "' >> " . $oreon->Nagioscfg->command_file;
	else if (isset($arg1) && !isset($arg2))
		$str = "echo '[" . time() . "] " . $arg1 . ";' >> " . $oreon->user->nagios_pwd . "var/rw/nagios.cmd";
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}



// Re-Schedule for all service of a host

function schedule_host_svc_checks($oreon, $arg, $lang)
{
	if (!$oreon->is_accessible($arg)){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$str = "echo '[" . time() . "] SCHEDULE_HOST_SVC_CHECKS;". $oreon->hosts[$arg]->get_name() .";" . time() . "' >> " . $oreon->Nagioscfg->command_file;
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang["cmd_send"]."</center></div>";
	system($str);
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}
// host check

function host_check($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($arg)){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] ".$tab[$type]."_HOST_CHECK;". $oreon->hosts[$arg]->get_name() ."' >> " . $oreon->Nagioscfg->command_file;
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang["cmd_send"]."</center></div>";
	system($str);
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

//  host notification

function host_notification($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($arg)){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] ".$tab[$type]."_HOST_NOTIFICATIONS;". $oreon->hosts[$arg]->get_name() ."' >> " . $oreon->Nagioscfg->command_file;
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang["cmd_send"]."</center></div>";
	system($str);
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

// Enable host flap detection

function host_flap_detection($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($arg)){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] " . $tab[$type] . "_HOST_FLAP_DETECTION;". $oreon->hosts[$arg]->get_name() ."' >> " . $oreon->Nagioscfg->command_file;
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

// event handler

function host_event_handler($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($arg)){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] ".$tab[$type] ."_HOST_EVENT_HANDLER;". $oreon->hosts[$arg]->get_name() ."' >> " . $oreon->Nagioscfg->command_file;
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

// ENABLE_HOST_SVC_NOTIFICATIONS

function host_svc_notifications($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($arg)){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] " . $tab[$type] . "_HOST_SVC_NOTIFICATIONS;". $oreon->hosts[$arg]->get_name() ."' >> " . $oreon->Nagioscfg->command_file;
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

// ENABLE_HOST_SVC_CHECKS

function host_svc_checks($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($arg)){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] " . $tab[$type] . "_HOST_SVC_CHECKS;". $oreon->hosts[$arg]->get_name() ."' >> " . $oreon->Nagioscfg->command_file;
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

// ENABLE_HOST_SVC_CHECKS

function svc_check($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($oreon->services[$arg]->get_host())){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] " . $tab[$type] . "_SVC_CHECK;". $oreon->hosts[$oreon->services[$arg]->get_host()]->get_name() .";" . $oreon->services[$arg]->get_description() . "' >> " . $oreon->Nagioscfg->command_file;
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

// SCHEDULE_SVC_CHECK

function schedule_svc_check($oreon, $arg, $lang, $type, $forced)
{
	if (!$oreon->is_accessible($oreon->services[$arg]->get_host())){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	if ($forced == 1)
		$str_forced = "_FORCED";
	else
		$str_forced = "";
	$str = "echo '[" . time() . "] SCHEDULE".$str_forced."_SVC_CHECK;". $oreon->hosts[$oreon->services[$arg]->get_host()]->get_name() .";" . $oreon->services[$arg]->get_description() . ";" . time() . "' >> " . $oreon->Nagioscfg->command_file;
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

// PASSIVE_SVC_CHECKS

function passive_svc_check($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($oreon->services[$arg]->get_host())){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] " . $tab[$type] . "_PASSIVE_SVC_CHECKS;". $oreon->hosts[$oreon->services[$arg]->get_host()]->get_name() .";" . $oreon->services[$arg]->get_description() . "' >> " . $oreon->Nagioscfg->command_file;
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

// SVC_NOTIFICATIONS

function sv_notifications($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($oreon->services[$arg]->get_host())){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] " . $tab[$type] . "_SVC_NOTIFICATIONS;". $oreon->hosts[$oreon->services[$arg]->get_host()]->get_name() .";" . $oreon->services[$arg]->get_description() . "' >> " . $oreon->Nagioscfg->command_file;
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

// _SVC_EVENT_HANDLER

function event_handler($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($oreon->services[$arg]->get_host())){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] " . $tab[$type] . "_SVC_EVENT_HANDLER;". $oreon->hosts[$oreon->services[$arg]->get_host()]->get_name() .";" . $oreon->services[$arg]->get_description() . "' >> " . $oreon->Nagioscfg->command_file;
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

//_SVC_FLAP_DETECTION

function flapping_enable($oreon, $arg, $lang, $type)
{
	if (!$oreon->is_accessible($oreon->services[$arg]->get_host())){
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"window.location='./alt_error.php'\",0)</SCRIPT>";
		return ;
	}
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	$str = "echo '[" . time() . "] " . $tab[$type] . "_SVC_FLAP_DETECTION;". $oreon->hosts[$oreon->services[$arg]->get_host()]->get_name() .";" . $oreon->services[$arg]->get_description() . "' >> " . $oreon->Nagioscfg->command_file;
	system($str);
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

function notifi_host_hostgroup($oreon, $arg, $lang, $type)
{
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	foreach ($oreon->hostGroups[$arg]->hosts as $h){
		$str = "echo '[" . time() . "] " . $tab[$type] . "_HOST_NOTIFICATIONS;". $h->get_name() ."' >> " . $oreon->Nagioscfg->command_file;
		system($str);
		unset($h);
	}
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}


function notifi_svc_host_hostgroup($oreon, $arg, $lang, $type)
{
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	foreach ($oreon->hostGroups[$arg]->hosts as $h){
		$str = "echo '[" . time() . "] " . $tab[$type] . "_HOST_SVC_NOTIFICATIONS;". $h->get_name() ."' >> " . $oreon->Nagioscfg->command_file;
		system($str);
		unset($h);
	}
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

function checks_svc_host_hostgroup($oreon, $arg, $lang, $type)
{
	$tab["1"] = "ENABLE";
	$tab["0"] = "DISABLE";
	foreach ($oreon->hostGroups[$arg]->hosts as $h){
		$str = "echo '[" . time() . "] " . $tab[$type] . "_HOST_SVC_CHECKS;". $h->get_name() ."' >> " . $oreon->Nagioscfg->command_file;
		system($str);
		unset($h);
	}
	print "<div style='padding-top: 50px' class='text11b'><center>".$lang['mon_request_submit_host']."</center></div>";
	print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:history.go(-1)\",2000)</SCRIPT>";
}

///
if (isset($_GET["cmd"]))
	switch ($_GET["cmd"]) {
		case 0: add_host_comment($oreon, $_GET["cmt"], $lang);break;//
		case 1: add_svc_comment($oreon, $_GET["cmt"], $lang);break;//
		case 2: del_host_comment($oreon, $_GET["id"], $lang);break;//
		case 3: del_all_host_comment($oreon, $_GET["host"], $lang);break;//
		case 4: del_svc_comment($oreon, $_GET["id"], $lang);break;//
		case 5: send_cmd($oreon, $_GET["host"], $_GET["svc"], $lang);break;
		case 6: send_cmd($oreon, "DELAY_HOST_NOTIFICATION", "", "", "", "", "");break;
		case 7: send_cmd($oreon, "DELAY_SVC_NOTIFICATION", "", "", "", "", "");break;
		case 8: schedule_svc_check($oreon, $_GET["id"], $lang, 1, 0);break;//
		case 9: schedule_host_svc_checks($oreon, $_GET["id"], $lang);break;//
		case 10: svc_check($oreon, $_GET["id"], $lang, 1);break;//
		case 11: svc_check($oreon, $_GET["id"], $lang, 0);break;//
		case 12: sv_notifications($oreon, $_GET["id"], $lang, 1);break;//
		case 13: sv_notifications($oreon, $_GET["id"], $lang, 0);break;//
		case 14: host_svc_notifications($oreon, $_GET["id"], $lang, 1);break;//
		case 15: host_svc_notifications($oreon, $_GET["id"], $lang, 0);break;//
		case 16: host_svc_checks($oreon, $_GET["id"], $lang, 1);break;
		case 17: host_svc_checks($oreon, $_GET["id"], $lang, 0);break;
		case 18: host_notification($oreon, $_GET["id"], $lang, 1);break;//
		case 19: host_notification($oreon, $_GET["id"], $lang, 0);break;//
		case 20: send_cmd($oreon, "ENABLE_ALL_NOTIFICATIONS_BEYOND_HOST", "", "", "", "", "");break;
		case 21: send_cmd($oreon, "DISABLE_ALL_NOTIFICATIONS_BEYOND_HOST", "", "", "", "", "");break;
		case 22: send_cmd($oreon, "ENABLE_NOTIFICATIONS", "", "", "", "", "");break;
		case 23: send_cmd($oreon, "DISABLE_NOTIFICATIONS", "", "", "", "", "");break;
		case 24: send_cmd($oreon, "SHUTDOWN_PROGRAM", time(), "", "", "", "");break;//
		case 25: send_cmd($oreon, "RESTART_PROGRAM", time(), "", "", "", "");break;//
		case 26: send_cmd($oreon, "PROCESS_SERVICE_CHECK_RESULT", "", "", "", "", "");break;//
		case 27: send_cmd($oreon, "SAVE_STATE_INFORMATION", "", "", "", "", "");break;
		case 28: send_cmd($oreon, "READ_STATE_INFORMATION", "", "", "", "", "");break;
		case 29: send_cmd($oreon, "START_EXECUTING_SVC_CHECKS", "", "", "", "", "");break;//
		case 30: send_cmd($oreon, "STOP_EXECUTING_SVC_CHECKS", "", "", "", "", "");break;//
		case 31: send_cmd($oreon, "START_ACCEPTING_PASSIVE_SVC_CHECKS", "", "", "", "", "");break;//
		case 32: send_cmd($oreon, "STOP_ACCEPTING_PASSIVE_SVC_CHECKS", "", "", "", "", "");break;//
		case 33: send_cmd($oreon, "ENABLE_PASSIVE_SVC_CHECKS", "", "", "", "", "");break;
		case 34: send_cmd($oreon, "DISABLE_PASSIVE_SVC_CHECKS", "", "", "", "", "");break;
		case 35: send_cmd($oreon, "ENABLE_EVENT_HANDLERS", "", "", "", "", "");break;//
		case 36: send_cmd($oreon, "DISABLE_EVENT_HANDLERS", "", "", "", "", "");break;//
		case 37: send_cmd($oreon, "START_OBSESSING_OVER_SVC_CHECKS", "", "", "", "", "");break;//
		case 38: send_cmd($oreon, "STOP_OBSESSING_OVER_SVC_CHECKS", "", "", "", "", "");break;//
		case 39: send_cmd($oreon, "ENABLE_FLAP_DETECTION", "", "", "", "", "");break;//
		case 40: send_cmd($oreon, "DISABLE_FLAP_DETECTION", "", "", "", "", "");break;//
		case 41: send_cmd($oreon, "ENABLE_PERFORMANCE_DATA", "", "", "", "", "");break;//
		case 42: send_cmd($oreon, "DISABLE_PERFORMANCE_DATA", "", "", "", "", "");break;//
		case 43: add_host_downtime(& $oreon, $_GET["dtm"], $lang);break;//
		case 44: add_svc_downtime(& $oreon, $_GET["dtm"], $lang);break;//
		case 45: del_host_downtime(& $oreon, $_GET, $lang);break;//
		case 46: del_svc_downtime(& $oreon, $_GET, $lang);break;//
		case 47: host_check(& $oreon, $_GET["id"], $lang, 1);break;//
		case 48: host_check(& $oreon, $_GET["id"], $lang, 0);break;//
		case 49: host_flap_detection(& $oreon, $_GET["id"], $lang, 1);break;//
		case 50: host_flap_detection($oreon, $_GET["id"], $lang, 0);break;//
		case 51: host_event_handler($oreon, $_GET["id"], $lang, 1);break;//
		case 52: host_event_handler($oreon, $_GET["id"], $lang, 0);break;//
		case 53: passive_svc_check($oreon, $_GET["id"], $lang, 1); break;//
		case 54: passive_svc_check($oreon, $_GET["id"], $lang, 0); break;//
		case 55: event_handler($oreon, $_GET["id"], $lang, 1); break;//
		case 56: event_handler($oreon, $_GET["id"], $lang, 0); break;//
		case 57: flapping_enable($oreon, $_GET["id"], $lang, 1); break;//
		case 58: flapping_enable($oreon, $_GET["id"], $lang, 0); break;//
		case 59: add_hostgroup_downtime($oreon, $_GET["dtm"], $lang);break;//
		case 60: add_svc_hostgroup_downtime($oreon, $_GET["dtm"], $lang);break;//
		case 61: notifi_host_hostgroup($oreon, $_GET["id"], $lang, 1);break;//
		case 62: notifi_host_hostgroup($oreon, $_GET["id"], $lang, 0);break;//
		case 63: notifi_svc_host_hostgroup($oreon, $_GET["id"], $lang, 1);break;//
		case 64: notifi_svc_host_hostgroup($oreon, $_GET["id"], $lang, 0);break;//
		case 65: checks_svc_host_hostgroup($oreon, $_GET["id"], $lang, 1);break;//
		case 66: checks_svc_host_hostgroup($oreon, $_GET["id"], $lang, 0);break;//
		case 67: schedule_svc_check($oreon, $_GET["id"], $lang, 1, 1);break;//
	}
?>
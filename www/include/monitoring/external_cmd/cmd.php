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

	include_once("./include/monitoring/external_cmd/functions.php");

	if (isset($_GET["en"]))
		$en = $_GET["en"];
	
	if (isset($_GET["select"])){
		foreach ($_GET["select"] as $key => $value){	
			if (isset($_GET["cmd"]))
				switch ($_GET["cmd"]) {
					
					/*
					 * Re-Schedulde SVC Checks
					 */
					
					case 1: 	schedule_host_svc_checks($key, 0);	break;//
					case 2: 	schedule_host_svc_checks($key, 1);	break;//Forced
					case 3: 	schedule_svc_checks($key, 0);		break;//
					case 4: 	schedule_svc_checks($key, 1);		break;//
					
					/*
					 * Scheduling svc
					 */
					
					case 5: 	host_svc_checks($key, $en);			break;
					case 6: 	host_check($key, $en);				break;//
					case 7: 	svc_check($key, $en);				break;//
					
					/*
					 * Delay notification
					 */
					 		

					
					/*
					 * Notifications
					 */
					
					case 8: 	host_svc_notifications($key, $en);	break;//
					case 9: 	host_notification($key, $en);		break;//
					case 10: 	svc_notifications($key, $en);		break;//
					
					/*
					 * En/Disable passive service check
					 */
					
					case 11: 	passive_svc_check($key, $en); 		break;//
					
					/*
					 * Acknowledge status
					 */
					
					case 14:	acknowledgeHost(); 					break;
					case 15:	acknowledgeService(); 					break;
					
					/*
					 * Submit passiv checks
					 */
					//case 16:	submitPassiveCheck();					break;
					
					/*
					 * Configure nagios Core
					 */
					 
					case 20: 	send_cmd("ENABLE_ALL_NOTIFICATIONS_BEYOND_HOST", "");break;
					case 21: 	send_cmd("DISABLE_ALL_NOTIFICATIONS_BEYOND_HOST", "");break;
					case 22: 	send_cmd("ENABLE_NOTIFICATIONS", "");break;
					case 23: 	send_cmd("DISABLE_NOTIFICATIONS", "");break;
					case 24: 	send_cmd("SHUTDOWN_PROGRAM", time());break;//
					case 25: 	send_cmd(" RESTART_PROGRAM", time());break;//
					case 26: 	send_cmd("PROCESS_SERVICE_CHECK_RESULT", "");break;//
					case 27: 	send_cmd("SAVE_STATE_INFORMATION", "");break;
					case 28: 	send_cmd("READ_STATE_INFORMATION", "");break;
					case 29: 	send_cmd("START_EXECUTING_SVC_CHECKS", "");break;//
					case 30: 	send_cmd("STOP_EXECUTING_SVC_CHECKS", "");break;//
					case 31: 	send_cmd("START_ACCEPTING_PASSIVE_SVC_CHECKS", "");break;//
					case 32: 	send_cmd("STOP_ACCEPTING_PASSIVE_SVC_CHECKS", "");break;//
					case 33: 	send_cmd("ENABLE_PASSIVE_SVC_CHECKS", "");break;
					case 34: 	send_cmd("DISABLE_PASSIVE_SVC_CHECKS", "");break;
					case 35: 	send_cmd("ENABLE_EVENT_HANDLERS", "");break;//
					case 36: 	send_cmd("DISABLE_EVENT_HANDLERS", "");break;//
					case 37: 	send_cmd("START_OBSESSING_OVER_SVC_CHECKS", "");break;//
					case 38:	send_cmd("STOP_OBSESSING_OVER_SVC_CHECKS", "");break;//
					case 39: 	send_cmd("ENABLE_FLAP_DETECTION", "");break;//
					case 40: 	send_cmd("DISABLE_FLAP_DETECTION", "");break;//
					case 41: 	send_cmd("ENABLE_PERFORMANCE_DATA", "");break;//
					case 42: 	send_cmd("DISABLE_PERFORMANCE_DATA", "");break;//
					
					/*
					 * End Configuration Nagios Core
					 */
					
					case 43: host_flapping_enable($key, $en); break;//
					case 44: svc_flapping_enable($key, $en); break;//
					case 45: host_event_handler($key, $en); break;//
					case 46: svc_event_handler($key, $en); break;//
					
					case 49: host_flap_detection($key, 1);break;//
					case 50: host_flap_detection($key, 0);break;//
					case 51: host_event_handler($key, 1);break;//
					case 52: host_event_handler($key, 0);break;//
					
					case 59: add_hostgroup_downtime($_GET["dtm"]);break;//
					case 60: add_svc_hostgroup_downtime($_GET["dtm"]);break;//
					case 61: notifi_host_hostgroup($key, 1);break;//
					case 62: notifi_host_hostgroup($key, 0);break;//
					case 63: notifi_svc_host_hostgroup($key, 1);break;//
					case 64: notifi_svc_host_hostgroup($key, 0);break;//
					case 65: checks_svc_host_hostgroup($key, 1);break;//
					case 66: checks_svc_host_hostgroup($key, 0);break;//
					case 67: schedule_svc_check($key, 1, 1);break;//
					
					/* Auto Aknowledge */
					
					case 70:	autoAcknowledgeServiceStart($key); 					break;
					case 71:	autoAcknowledgeServiceStop($key);  					break;
					case 72:	autoAcknowledgeHostStart($key); 					 	break;
					case 73:	autoAcknowledgeHostStop($key);  						break;
				
					/* Auto Notification */
					
					case 80:	autoNotificationServiceStart($key); 					break;
					case 81:	autoNotificationServiceStop($key);  					break;
					case 82:	autoNotificationHostStart($key); 					 	break;
					case 83:	autoNotificationHostStop($key);  						break;
					
					/* Auto Check */
					
					case 90:	autoCheckServiceStart($key); 					break;
					case 91:	autoCheckServiceStop($key);  					break;
					case 92:	autoCheckHostStart($key); 					 	break;
					case 93:	autoCheckHostStop($key);  						break;
				}
			}
		}
?>
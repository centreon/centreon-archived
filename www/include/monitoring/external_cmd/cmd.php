<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */
 
if (!isset($centreon)) {
	exit();
}

include_once("./include/monitoring/external_cmd/functions.php");

/*
 * Get Parameters
 */
if (!isset($_GET["cmd"]) && isset($_POST["cmd"])) {
	$param = $_POST;
} else {
	$param = $_GET;
}

if (isset($param["en"])) {
	$en = $param["en"];
}

if (isset($param["select"])) {
	foreach ($param["select"] as $key => $value) {	
		if (isset($param["cmd"])) {
			switch ($param["cmd"]) {
				
				/*
				 * Re-Schedulde SVC Checks
				 */
				case 1: 	schedule_host_svc_checks($key, 0);	break;
				case 2: 	schedule_host_svc_checks($key, 1);	break;//Forced
				case 3: 	schedule_svc_checks($key, 0);		break;
				case 4: 	schedule_svc_checks($key, 1);		break;
				
				/*
				 * Scheduling svc
				 */
				case 5: 	host_svc_checks($key, $en);			break;
				case 6: 	host_check($key, $en);				break;
				case 7: 	svc_check($key, $en);				break;
				
				/*
				 * Notifications
				 */
				case 8: 	host_svc_notifications($key, $en);	break;
				case 9: 	host_notification($key, $en);		break;
				case 10: 	svc_notifications($key, $en);		break;
				
<<<<<<< HEAD
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

                    /* Scheduling host */
                    case 94:     schedule_host_checks($key, 0); break;
                    case 95:     schedule_host_checks($key, 1); break;

				}
			}
		}
?>

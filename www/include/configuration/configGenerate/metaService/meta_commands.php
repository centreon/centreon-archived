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
 	
	$str = NULL;
	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_commands.cfg", $oreon->user->get_name());
	
	$str = "define command{\n";
	$str .= print_line("command_name", "check_meta");
	$str .= print_line("command_line", $oreon->optGen["nagios_path_plugins"]."check_meta_service -i \$ARG1\$");
	$str .= "}\n\n";
	
	$str .= "define command{\n";
	$str .= print_line("command_name", "meta_notify");
	$cmd = "/usr/bin/printf \"%b\" \"***** Meta Service Centreon *****\\n\\nNotification Type: \$NOTIFICATIONTYPE\$\\n\\nService: \$SERVICEDESC\$\\nState: \$SERVICESTATE\$\\n\\nDate/Time: \$DATETIME\$\\n\\nAdditional Info:\\n\\n\$OUTPUT\$\" | \/bin\/mail -s \"** \$NOTIFICATIONTYPE\$ \$SERVICEDESC\$ is \$SERVICESTATE\$ **\" \$CONTACTEMAIL\$";
	$str .= print_line("command_line", $cmd);
	$str .= "}\n\n";
	
	write_in_file($handle, $str, $nagiosCFGPath.$tab['id']."/meta_commands.cfg");
	fclose($handle);	
	unset($res);
	unset($str);	
?>
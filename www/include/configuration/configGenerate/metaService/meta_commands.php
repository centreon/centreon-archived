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
 	
        $legacy = "";
        if (isset($oreon->optGen['centstorage']) && $oreon->optGen['centstorage']) {
            $legacy = "--legacy";
        }
        
	$str = NULL;
	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_commands.cfg", $oreon->user->get_name());
	
	$str = "define command{\n";
	$str .= print_line("command_name", "check_meta");        
	$str .= print_line("command_line", $oreon->optGen["nagios_path_plugins"]."check_meta_service -i \$ARG1\$ $legacy");
	$str .= "}\n\n";
	
	$str .= "define command{\n";
	$str .= print_line("command_name", "meta_notify");
	$cmd = "/usr/bin/printf \"%b\" \"***** Meta Service Centreon *****\\n\\nNotification Type: \$NOTIFICATIONTYPE\$\\n\\nService: \$SERVICEDESC\$\\nState: \$SERVICESTATE\$\\n\\nDate/Time: \$DATETIME\$\\n\\nAdditional Info:\\n\\n\$OUTPUT\$\" | \/bin\/mail -s \"** \$NOTIFICATIONTYPE\$ \$SERVICEDESC\$ is \$SERVICESTATE\$ **\" \$CONTACTEMAIL\$";
	$str .= print_line("command_line", $cmd);
	$str .= "}\n\n";
	
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, 'UTF-8'), $nagiosCFGPath.$tab['id']."/meta_commands.cfg");
	fclose($handle);	
	unset($res);
	unset($str);	
?>

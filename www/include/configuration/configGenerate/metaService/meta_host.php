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

	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_host.cfg", $oreon->user->get_name());
	$str = NULL;
	
	# Init
	
	$nb = 0;
	
	# Host Creation
	$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE meta_activate = '1'");
	$nb = $DBRESULT->numRows();
	
	if ($nb){
		$str .= "define host{\n";
		$str .= print_line("host_name", "_Module_Meta");
		$str .= print_line("alias", "Meta Service Calculate Module For Centreon");
		$str .= print_line("address", "127.0.0.1");
		$str .= print_line("check_command", "check_host_alive");
		$str .= print_line("max_check_attempts", "3");
		$str .= print_line("check_interval", "1");
		$str .= print_line("active_checks_enabled", "0");
		$str .= print_line("passive_checks_enabled", "0");
		$str .= print_line("check_period", "meta_timeperiod");
		# Contact Group
		$str .= print_line("contact_groups", "meta_contactgroup");
		$str .= print_line("notification_interval", "60");
		$str .= print_line("notification_period", "meta_timeperiod");
		$str .= print_line("notification_options", "d");
		$str .= print_line("notifications_enabled", "0");
		$str .= print_line("register", "1");
		$str .= "\t}\n\n";
	}	
	write_in_file($handle, $str, $nagiosCFGPath.$tab['id']."/meta_hosts.cfg");
	fclose($handle);
	unset($str);
?>
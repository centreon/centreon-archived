<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	$handle = create_file($nagiosCFGPath."meta_host.cfg", $oreon->user->get_name());
	$str = NULL;
	
	# Host Creation
	$str .= "define host{\n";
	$str .= print_line("host_name", "Meta_Module");
	$str .= print_line("alias", "Meta Service Calculate Module For Oreon");
	$str .= print_line("address", "127.0.0.1");
	$str .= print_line("check_command", "check_host_alive");
	$str .= print_line("max_check_attempts", "3");
	if ($oreon->user->get_version() == 2)	{
		$str .= print_line("check_interval", "1");
		$str .= print_line("active_checks_enabled", "0");
		$str .= print_line("passive_checks_enabled", "0");
		$str .= print_line("check_period", "meta_timeperiod");
	}
	if ($oreon->user->get_version() == 1)
		$str .= print_line("checks_enabled", "1");
	# Contact Group
	if ($oreon->user->get_version() == 2)
		$str .= print_line("contact_groups", "meta_contactgroup");
	$str .= print_line("notification_interval", "60");
	$str .= print_line("notification_period", "meta_timeperiod");
	$str .= print_line("notification_options", "d");
	$str .= print_line("notifications_enabled", "0");
	$str .= print_line("register", "1");
	$str .= "\t}\n\n";
	
	write_in_file($handle, $str, $nagiosCFGPath."meta_hosts.cfg");
	fclose($handle);
	unset($str);
?>
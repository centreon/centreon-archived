<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

	$str = NULL;
	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_contact.cfg", $oreon->user->get_name());
	
	# Host Creation
	$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE meta_activate = '1'");
	$nb = $DBRESULT->numRows();
	
	if ($nb){
		$str .= "define contact{\n";
		$str .= print_line("contact_name", "meta_contact");
		$str .= print_line("alias", "meta_contact");
		# Nagios 2 : Contact Groups in Contact
		if ($oreon->user->get_version() == 2)
			$str .= print_line("contactgroups", "meta_contactgroup");
		$str .= print_line("host_notification_period", "meta_timeperiod");
		$str .= print_line("service_notification_period", "meta_timeperiod");
		$str .= print_line("host_notification_options", "n");
		$str .= print_line("service_notification_options", "n");
		# Host & Service notification command
		$str .= print_line("host_notification_commands", "meta_notify");
		$str .= print_line("service_notification_commands", "meta_notify");
		# Misc
		$str .= print_line("email", "meta_contact_email");
		$str .= "}\n\n";
	}
	write_in_file($handle, $str, $nagiosCFGPath.$tab['id']."/meta_contact.cfg");
	fclose($handle);
	unset($str);
?>
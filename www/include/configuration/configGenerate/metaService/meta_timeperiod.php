<?
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
	$handle = create_file($nagiosCFGPath."meta_timeperiod.cfg", $oreon->user->get_name());
	$str .= "define timeperiod{\n";
	$str .= print_line("timeperiod_name", "meta_timeperiod");
	$str .= print_line("alias", "meta_timeperiod");
	$str .= print_line("sunday", "00:00-24:00");
	$str .= print_line("monday", "00:00-24:00");
	$str .= print_line("wednesday", "00:00-24:00");
	$str .= print_line("tuesday", "00:00-24:00");
	$str .= print_line("thursday", "00:00-24:00");
	$str .= print_line("friday", "00:00-24:00");
	$str .= print_line("saturday", "00:00-24:00");
	$str .= "}\n\n";
	write_in_file($handle, $str, $nagiosCFGPath."meta_timeperiod.cfg");
	fclose($handle);
	unset($str);
?>
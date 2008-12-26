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

	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_hostgroup.cfg", $oreon->user->get_name());
	$str = NULL;

	# Host Creation
	$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE meta_activate = '1'");
	$nb = $DBRESULT->numRows();
	
	if ($nb){
		$str .= "define hostgroup{\n";
		$str .= print_line("hostgroup_name", "meta_hostgroup");
		$str .= print_line("alias", "meta_hostgroup");
		$str .= print_line("members", "_Module_Meta");
		$str .= "\t}\n\n";
	}
	write_in_file($handle, $str, $nagiosCFGPath.$tab['id']."/meta_hostgroup.cfg");
	fclose($handle);
	unset($str);
?>
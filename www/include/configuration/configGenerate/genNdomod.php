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

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	$handle = create_file($nagiosCFGPath.$tab['id']."/ndomod.cfg", $oreon->user->get_name());

	$DBRESULT =& $pearDB->query("SELECT * FROM `cfg_ndomod` WHERE `activate` = '1' AND `ns_nagios_server` = '".$tab['id']."' LIMIT 1");
	
	$DBRESULT->numRows() ? $ndomod = $DBRESULT->fetchRow() : $ndomod = array();

	$str = "";
	foreach ($ndomod as $key => $value)	{
		if ($value && $key != "id" && $key != "description" && $key != "local" && $key != "ns_nagios_server" && $key != "activate")	{	
			$str .= $key."=".$value."\n";
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/ndomod.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
?>